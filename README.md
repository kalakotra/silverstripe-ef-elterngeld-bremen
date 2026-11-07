# Elterngeld Bremen

Form module for the e-formulare.de PDF filler (`kalakotra/pdf-editor`).
Fills the **Antrag auf Elterngeld für Geburten ab dem 01.04.2025** of the Amt für Soziale Dienste Bremen
(Antragsversion August 2025) for one or both parents, adds the annexes the answers require and merges everything into
one PDF. The applicant's signature is stamped on page 23 ("Fragen an Sie"); the other parent signs by hand.

Requires the pdf-editor app base classes (`app\forms\basemodel\*`, `app\models\FormSubmission`) and pdftk
(`replacement_font` support: pdftk ≥ 3.0; without it values are transliterated to ASCII).

## Installation

pdf-editor `composer.json`:

```json
"repositories": [
    { "type": "git", "url": "https://github.com/kalakotra/silverstripe-ef-elterngeld-bremen" }
],
"require": {
    "kalakotra/silverstripe-ef-elterngeld-bremen": "^1.0"
}
```

```bash
composer update kalakotra/silverstripe-ef-elterngeld-bremen
vendor/bin/sake db:build --flush
```

`db:build` is mandatory: `DBClassName` is an enum, without it the steps load as plain `MultiFormStep`.

## PDF template (repair first!)

The distributed `Elterngeld_Antrag_HB.pdf` (36 pages) is broken: no `/AcroForm` in the catalog and 51 widgets whose
field dictionaries are lost, so pdftk sees **0 fields**. Repair it once and upload the result:

```bash
pip install pypdf          # >= 4
python3 bin/repair-pdf.py Elterngeld_Antrag_HB.pdf Elterngeld_Antrag_HB_repaired.pdf
# -> "696 fields, 51 orphan widgets repaired"
```

The script rebuilds the AcroForm, names the orphaned widgets (child name/birth date, Geschlecht radio groups, annex
checkboxes), renames the UTF-8 radio state `männlich` to `maennlich` (pdftk reads it as Latin-1) and sets a 10 pt
font (the original 16 pt clips almost every value). The output is deterministic.

## CMS setup (PDFMultiFormPage)

`Form Class Name` = **Elterngeld (Bremen, Geburten ab 01.04.2025, mit Anlagen)**.

| Slot | File |
|---|---|
| `PDFFile` | the **repaired** `Elterngeld_Antrag_HB.pdf` – all documents are page ranges of this one template |

| Document | Template pages | Included | Per |
|---|---|---|---|
| Antrag | 1–23 | always | once (both columns) |
| Anlage Wohnsitz im Ausland | 29–30 | parent lives abroad (2.c) | parent |
| Anlage Staatsangehörigkeit | 32 | not German (2.g, extended parent) | parent |
| Anlage Einkünfte neben dem Elterngeld | 33–35 | income after the birth (12.a) | parent |
| Arbeitgeberbescheinigung | 25–28 | 12.a employed + opt-in; header only, employer fills the rest | parent |

Merge order: Antrag, Wohnsitz (P1, P2), Staatsangehörigkeit (P1, P2), Einkünfte (P1, P2), Arbeitgeberbescheinigung
(P1, P2). Page 24 (blank), page 31 (duplicate of 32) and page 36 (Datenschutz) are dropped.

`SignaturePositionX/Y` – leave empty (module default: page 23, x 23 mm, y 124.6 mm, width 36 mm); YAML override:
`Kalakotra\EFormElterngeldBremen\Model\Submission.documents.antrag.stamps.main`.

## Flow

P1 = "Fragen an Sie", P2 = "Fragen an den anderen Elternteil". One flow table in `StepTrait::FLOW`, gates in
`isFlowStepActive()`.

| Step | Content (Antrag section) | Active when |
|---|---|---|
| Form_Child | 1 child, multiples, premature, disability, 3.c adoption | always |
| Form_Family | 2.a single parents, 2.f who applies, 2.e income limit | always |
| Form_Person1 / 2 | 2.b–2.d person, marital status, residence, address, child address, tax ID | P2: not single |
| Form_Abroad1 / 2 | Anlage Wohnsitz im Ausland | that parent lives abroad |
| Form_Status1 / 2 | 2.g citizenship (+ Anlage), 2.h NATO/diplomats, 2.i health insurance, 3.a/3.b relationship | P2: "Beide Elternteile" |
| Form_Siblings | 4 other children, Geschwisterbonus (max. 3) | always |
| Form_Assessment1 / 2 | 5 Mindestbetrag, 6 Bemessungszeitraum (shift, ausklammern, Corona) | P2: "Beide" |
| Form_Earnings1 / 2 | 7 income, 7.b contributions, 8 benefits before the birth | calculated (not Mindestbetrag); P2 + "Beide" |
| Form_Maternity | 9 Mutterschaftsleistungen | always |
| Form_Months1 / 2 | 10 Lebensmonate 1–32 (Basis / Plus / Bonus) | P2: "Beide" |
| Form_Leave1 / 2 | 11 Elternzeit, 12.d paid leave | P2: "Beide" |
| Form_IncomeAfter1 / 2 | 12.a–12.c income/benefits after the birth | calculated; P2 + "Beide" |
| Form_AnnexJobs1 / 2 | Anlage Einkünfte 1 (employment) | 12.a employed |
| Form_AnnexProfit1 / 2 | Anlage Einkünfte 2 (self-employment) | 12.a profit |
| Form_Bank1 / 2 | 13.a account, 13.b contact, 13.c remarks, 13.d place | P2: "Beide" |
| Form_Preview | e-mail, signature, terms | always |

## Data model

- `Submission` – child, family, siblings, Mutterschaftsleistungen; `has_many` `Parents`.
- `SubmissionParent` – one record per column (`Number` 1/2, `Extended` = questions from 2.g onwards): the Antrag
  column (`antrag_field_mapping[p1|p2]`) and the per-parent annexes (`abroad_`, `citizenship_`, `income_`,
  `employer_field_mapping`). No P2 record for single parents.
- `$field_conditions` (FieldConditions) is the single source for step cleanup, conditional required fields and the
  `onBeforeWrite` safety net – values of hidden fields never reach the PDF.
- `PdfPackage` fills/flattens each document separately, merges with one pdftk `cat` and stamps the signature with
  FPDI (page numbers translated from the template).
- 2.c Straße is word-wrapped over the two street lines (`LineOf`, `line_width` 30).

## Defaults taken (open questions of the form)

- Signature of the other parent, Ort/Datum of 2E and the gesetzliche Vertretung (13.d) stay empty: signed by hand.
- Arbeitgeberbescheinigung: only the header (person, address, child) is prefilled, opt-in per parent.
- 6.a/6.b: "unter 35 Euro" folded into one dropdown (none / taxfree / below35 / taxable); the 6.c loop back to 6.b
  for changed profit income is shown as a notice (choose "nicht alle steuerfrei" in 6.a).
- Lebensmonate: one dropdown per month, at least 2 months per applying parent, Basis after month 14 only for
  premature children; full Elterngeld rules are left to the Elterngeldrechner (link in the step).
- Month/year inputs (6.c periods, Aufenthalt) as `MM.JJJJ` text.

## Development

- `bin/dev/eg_inventory.py <repaired.pdf> <work>` – field inventory per document (`fields_<doc>.tsv`).
- `bin/dev/gen.py <module>` – regenerates `$db`, mappings, `state_map` and `field_conditions` between the
  `GENERATED` markers of both models; `bin/dev/genclasses.py <module>` – the thin P1/P2 step classes.
- Harness: project docs `claude/harness/` (`eg/` variant) – static checks, flow/validation scenarios, fill + render.

## Translations

`lang/` – ar, bg, bs, cs, de, en, es, fr, it, pl, ro, ru, tr, uk. Official German terms (Basiselterngeld,
Bemessungszeitraum, Anlage names …) are kept in parentheses.

## License

BSD-3-Clause
