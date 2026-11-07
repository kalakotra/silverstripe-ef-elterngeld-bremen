<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use Kalakotra\EFormElterngeldBremen\Model\FieldConditions;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * 7.a Einkommen im Bemessungszeitraum, 7.b Steuern und Sozialabgaben bei Gewinneinkünften,
 * 8. Einkommensersatzleistungen / Bürgergeld for one column (not with Mindestbetrag).
 *
 * Using classes define: PERSON, FIELD_PREFIX, $default_sort_order, $conditional_required, introText().
 */
trait EarningsStepTrait
{
    abstract protected function introText(): string;

    public function getFields(): FieldList
    {
        $p = static::FIELD_PREFIX;

        return FieldList::create(
            HeaderField::create('IntroText', $this->introText(), 4),
            LiteralField::create($p . 'EarningsNotice', '<div class="alert alert-info">' . _t(__TRAIT__ . '.EarningsNotice', 'Die Fragen beziehen sich auf Ihren Bemessungszeitraum (inklusive der Änderungen aus dem vorherigen Schritt). Geben Sie alle Einkünfte an, egal wie gering, auch zeitweise Einkünfte. Keine Einkünfte: nichts ankreuzen.') . '</div>'),
            CheckboxField::create($p . 'Employed', _t(__TRAIT__ . '.Employed', 'Einkünfte aus nicht-selbstständiger Arbeit (z. B. Lohn, Gehalt, auch aus einem Mini-Job) – Gehaltsabrechnungen aus dem Bemessungszeitraum beifügen')),
            Wrapper::create(
                LiteralField::create($p . 'EmployedNotice', '<p>' . _t(__TRAIT__ . '.EmployedNotice', 'Darunter sind Einkünfte aus:') . '</p>'),
                CheckboxField::create($p . 'MiniJob', _t(__TRAIT__ . '.MiniJob', 'Mini-Job(s) – bis 556 Euro brutto monatlich (vor 2025: 538 Euro, vor 2024: 520 Euro, vor 10/2022: 450 Euro)')),
                CheckboxField::create($p . 'MidiJob', _t(__TRAIT__ . '.MidiJob', 'Midi-Job(s) – mehr als 556 Euro und höchstens 2.000 Euro brutto monatlich')),
                CheckboxField::create($p . 'Training', _t(__TRAIT__ . '.Training', 'einer Berufsausbildung')),
                CheckboxField::create($p . 'Voluntary', _t(__TRAIT__ . '.Voluntary', 'einem Freiwilligendienst (z. B. FSJ, FÖJ, BFD)')),
            )
                ->displayIf($p . 'Employed')->isChecked()
                ->end(),
            CheckboxField::create($p . 'Profit', _t(__TRAIT__ . '.Profit', 'Gewinneinkünfte (auch negative) – Einkommensteuerbescheid für den Bemessungszeitraum beifügen, ersatzweise letzter Bescheid, Bilanz oder Einnahmen-Überschuss-Rechnung')),
            Wrapper::create(
                CheckboxField::create($p . 'Trade', _t(__TRAIT__ . '.Trade', 'aus einem Gewerbebetrieb')),
                CheckboxField::create($p . 'SelfEmployedWork', _t(__TRAIT__ . '.SelfEmployedWork', 'aus selbstständiger Arbeit (auch Nebenberuf oder Fotovoltaik-Anlage)')),
                CheckboxField::create($p . 'Agriculture', _t(__TRAIT__ . '.Agriculture', 'aus Land- und Forstwirtschaft')),
                HeaderField::create($p . 'ContribHeader', _t(__TRAIT__ . '.ContribHeader', 'Mussten Sie im Bemessungszeitraum Kirchensteuern oder Pflichtbeiträge zur Sozialversicherung zahlen?'), 5),
                CheckboxField::create($p . 'ChurchTax', _t(__TRAIT__ . '.ChurchTax', 'Kirchensteuer')),
                CheckboxField::create($p . 'HealthContrib', _t(__TRAIT__ . '.HealthContrib', 'Pflichtbeiträge zur Krankenkasse und Pflegeversicherung (auch Eigenbeiträge zur Künstlersozialkasse)')),
                CheckboxField::create($p . 'PensionContrib', _t(__TRAIT__ . '.PensionContrib', 'Pflichtbeiträge zur Rentenversicherung oder zu einem berufsständischen Versorgungswerk')),
                CheckboxField::create($p . 'UnemploymentContrib', _t(__TRAIT__ . '.UnemploymentContrib', 'Pflichtbeiträge zur Arbeitslosenversicherung')),
                CheckboxField::create($p . 'NoContrib', _t(__TRAIT__ . '.NoContrib', 'nichts davon')),
            )
                ->displayIf($p . 'Profit')->isChecked()
                ->end(),
            HeaderField::create($p . 'BenefitsHeader', _t(__TRAIT__ . '.BenefitsHeader', 'Haben Sie im Bemessungszeitraum eine der folgenden Leistungen bekommen?'), 5),
            CheckboxField::create($p . 'BenUnemployment', _t(__TRAIT__ . '.BenUnemployment', 'Arbeitslosengeld')),
            CheckboxField::create($p . 'BenSickness', _t(__TRAIT__ . '.BenSickness', 'Krankengeld (auch Kinderkrankengeld)')),
            CheckboxField::create($p . 'BenSicknessDaily', _t(__TRAIT__ . '.BenSicknessDaily', 'Krankentagegeld (bei der leiblichen Mutter: außerhalb des Mutterschutzes)')),
            CheckboxField::create($p . 'BenPension', _t(__TRAIT__ . '.BenPension', 'Rente')),
            Wrapper::create(
                TextField::create($p . 'PensionType1', _t(__TRAIT__ . '.PensionType1', 'Art der Rente (z. B. Erwerbsminderungsrente)'))
                    ->setMaxLength(40),
                TextField::create($p . 'PensionType2', _t(__TRAIT__ . '.PensionType2', 'Bei weiterer Rente: Art der Rente (optional)'))
                    ->setMaxLength(40),
            )
                ->displayIf($p . 'BenPension')->isChecked()
                ->end(),
            CheckboxField::create($p . 'BenParental', _t(__TRAIT__ . '.BenParental', 'Elterngeld für ein älteres Kind')),
            CheckboxField::create($p . 'BenOther', _t(__TRAIT__ . '.BenOther', 'andere Einkommensersatzleistungen (z. B. Kurzarbeitergeld, Insolvenzgeld)')),
            Wrapper::create(
                TextField::create($p . 'OtherType1', _t(__TRAIT__ . '.OtherType1', 'Art der Leistung'))
                    ->setMaxLength(40),
                TextField::create($p . 'OtherType2', _t(__TRAIT__ . '.OtherType2', 'Bei weiterer Leistung: Art der Leistung (optional)'))
                    ->setMaxLength(40),
            )
                ->displayIf($p . 'BenOther')->isChecked()
                ->end(),
            CheckboxField::create($p . 'BenCitizen', _t(__TRAIT__ . '.BenCitizen', 'Bürgergeld')),
            LiteralField::create($p . 'BenefitsEvidence', '<p class="text-muted">' . _t(__TRAIT__ . '.BenefitsEvidence', 'Für jede Leistung, die Sie noch bekommen: Bescheid oder anderen Nachweis beifügen (vom Bürgergeld-Bescheid reicht die erste Seite; Elterngeld-Bescheide nur, wenn sie von einer anderen Elterngeldstelle sind).') . '</p>'),
        );
    }

    /**
     * 7.b "nichts davon" excludes the other contributions.
     */
    public function validateStep($data, $form)
    {
        $p = static::FIELD_PREFIX;
        // hidden fields (Gewinneinkünfte not checked) are cleared first, like saveData() does
        $values = FieldConditions::apply($form->getData(), static::fieldConditionRules(), true);
        $valid = $this->validateConditionalRequired($data, $form);

        $contributions = array_filter(
            ['ChurchTax', 'HealthContrib', 'PensionContrib', 'UnemploymentContrib'],
            static fn (string $field): bool => (bool) ($values[$p . $field] ?? false)
        );
        if (($values[$p . 'NoContrib'] ?? false) && $contributions !== []) {
            $message = _t(__TRAIT__ . '.NOCONTRIB_CONFLICT', '„nichts davon“ kann nicht zusammen mit Kirchensteuer oder Pflichtbeiträgen angekreuzt werden.');
            $form->sessionFieldError($message, $p . 'NoContrib');
            $form->sessionMessage($message);
            $valid = false;
        }

        return $valid && parent::validateStep($data, $form);
    }
}
