<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * 12.a Einkünfte neben dem Elterngeld, 12.b Einkommensersatzleistungen, 12.c Ausbildung for one column
 * (not with Mindestbetrag). 12.a decides about the Anlage "Einkünfte neben dem Elterngeld" (next steps)
 * and the optional, pre-filled Arbeitgeberbescheinigung.
 *
 * Using classes define: PERSON, FIELD_PREFIX, $default_sort_order, $conditional_required, introText().
 */
trait IncomeAfterStepTrait
{
    abstract protected function introText(): string;

    public function getFields(): FieldList
    {
        $p = static::FIELD_PREFIX;
        $select = '-- ' . _t(__TRAIT__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --';

        return FieldList::create(
            HeaderField::create('IntroText', $this->introText(), 4),
            LiteralField::create($p . 'AfterNotice', '<div class="alert alert-info">' . _t(__TRAIT__ . '.AfterNotice', 'Welche Einkünfte werden Sie voraussichtlich haben, während Sie Elterngeld bekommen? Keine solchen Einkünfte: nichts ankreuzen. Während des Elterngeldbezugs dürfen Sie höchstens 32 Stunden pro Woche arbeiten.') . '</div>'),
            CheckboxField::create($p . 'AfterEmployed', _t(__TRAIT__ . '.AfterEmployed', 'Einkünfte aus nicht-selbstständiger Arbeit (z. B. Lohn, Gehalt, Mini-Job; auch geldwerte Vorteile wie ein privat genutzter Dienstwagen)')),
            Wrapper::create(
                CheckboxField::create($p . 'EmployerCertificate', _t(__TRAIT__ . '.EmployerCertificate', 'Die Anlage „Arbeitgeberbescheinigung“ mit meinen Angaben vorausgefüllt beifügen (der Arbeitgeber füllt den Rest aus und unterschreibt)')),
            )
                ->displayIf($p . 'AfterEmployed')->isChecked()
                ->end(),
            CheckboxField::create($p . 'AfterProfit', _t(__TRAIT__ . '.AfterProfit', 'Gewinneinkünfte (alle Gewinne oder Verluste während des Elterngeldbezugs – unabhängig davon, ob Sie in dieser Zeit dafür arbeiten)')),
            LiteralField::create($p . 'AnnexNotice', '<p class="text-muted">' . _t(__TRAIT__ . '.AnnexNotice', 'Bei Einkünften füllen Sie in den nächsten Schritten die Anlage „Einkünfte neben dem Elterngeld“ aus.') . '</p>'),
            HeaderField::create($p . 'AfterBenefitsHeader', _t(__TRAIT__ . '.AfterBenefitsHeader', 'Werden Sie zeitgleich zum Elterngeld voraussichtlich eine der folgenden Leistungen bekommen?'), 5),
            CheckboxField::create($p . 'AfterUnemployment', _t(__TRAIT__ . '.AfterUnemployment', 'Arbeitslosengeld')),
            CheckboxField::create($p . 'AfterSickness', _t(__TRAIT__ . '.AfterSickness', 'Krankengeld')),
            CheckboxField::create($p . 'AfterSicknessDaily', _t(__TRAIT__ . '.AfterSicknessDaily', 'Krankentagegeld (bei der leiblichen Mutter: außerhalb des Mutterschutzes)')),
            CheckboxField::create($p . 'AfterPension', _t(__TRAIT__ . '.AfterPension', 'Rente')),
            Wrapper::create(
                TextField::create($p . 'AfterPensionType1', _t(__TRAIT__ . '.AfterPensionType1', 'Art der Rente (z. B. Erwerbsminderungsrente)'))
                    ->setMaxLength(40),
                TextField::create($p . 'AfterPensionType2', _t(__TRAIT__ . '.AfterPensionType2', 'Bei weiterer Rente: Art der Rente (optional)'))
                    ->setMaxLength(40),
            )
                ->displayIf($p . 'AfterPension')->isChecked()
                ->end(),
            CheckboxField::create($p . 'AfterParental', _t(__TRAIT__ . '.AfterParental', 'Elterngeld für ein älteres Kind')),
            CheckboxField::create($p . 'AfterOther', _t(__TRAIT__ . '.AfterOther', 'Andere Einkommensersatzleistungen (z. B. Kurzarbeitergeld, Insolvenzgeld)')),
            Wrapper::create(
                TextField::create($p . 'AfterOtherType1', _t(__TRAIT__ . '.AfterOtherType1', 'Art der Leistung'))
                    ->setMaxLength(40),
                TextField::create($p . 'AfterOtherType2', _t(__TRAIT__ . '.AfterOtherType2', 'Bei weiterer Leistung: Art der Leistung (optional)'))
                    ->setMaxLength(40),
            )
                ->displayIf($p . 'AfterOther')->isChecked()
                ->end(),
            CheckboxField::create($p . 'AfterCitizen', _t(__TRAIT__ . '.AfterCitizen', 'Bürgergeld')),
            CheckboxField::create($p . 'AfterForeign', _t(__TRAIT__ . '.AfterForeign', 'Leistungen aus dem Ausland für dieses oder ein älteres Kind, die mit dem Elterngeld oder den Mutterschaftsleistungen vergleichbar sind')),
            CheckboxField::create($p . 'AfterIntl', _t(__TRAIT__ . '.AfterIntl', 'Vergleichbare Leistungen einer zwischenstaatlichen Organisation (z. B. EU oder UN) für dieses oder ein älteres Kind')),
            DropdownField::create($p . 'Retraining', _t(__TRAIT__ . '.Retraining', 'Machen Sie nach der Geburt eine Berufsausbildung, eine berufliche Umschulung oder eine andere Berufsbildung, während Sie Elterngeld bekommen?'))
                ->setSource([
                    'yes' => _t(__TRAIT__ . '.Retraining.yes', 'Ja (Ausbildungsvertrag beifügen)'),
                    'no' => _t(__TRAIT__ . '.Retraining.no', 'Nein'),
                ])
                ->setEmptyString($select),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            static::FIELD_PREFIX . 'Retraining',
        ]);
    }
}
