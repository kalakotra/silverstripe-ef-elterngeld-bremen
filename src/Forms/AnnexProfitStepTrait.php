<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * Anlage "Einkünfte neben dem Elterngeld", 2. Selbstständigkeit (Gewinneinkünfte, Betriebsausgaben, steuerfreie
 * Gewinneinkünfte, Tagespflege).
 *
 * Using classes define: PERSON, FIELD_PREFIX, $default_sort_order, $conditional_required, introText().
 */
trait AnnexProfitStepTrait
{
    abstract protected function introText(): string;

    public function getFields(): FieldList
    {
        $p = static::FIELD_PREFIX;
        $select = '-- ' . _t(__TRAIT__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --';

        return FieldList::create(
            HeaderField::create('IntroText', $this->introText(), 4),
            LiteralField::create($p . 'ProfitAnnexNotice', '<div class="alert alert-info">' . _t(__TRAIT__ . '.ProfitAnnexNotice', 'Geben Sie alle voraussichtlichen Gewinne (auch Verluste) während des Elterngeldbezugs an – unabhängig davon, ob Sie in dieser Zeit dafür arbeiten. Höchstens 32 Stunden pro Woche. Beifügen: Prognose der erwarteten Einkünfte (z. B. Bescheinigung der Steuerberatung) und eine Erklärung, wie Sie Ihre Arbeitszeit verringern.') . '</div>'),
            HeaderField::create($p . 'ProfitTypesHeader', _t(__TRAIT__ . '.ProfitTypesHeader', 'Welche Gewinneinkünfte werden Sie haben?'), 5),
            CheckboxField::create($p . 'ProfitSelf', _t(__TRAIT__ . '.ProfitSelf', 'Einkünfte aus selbstständiger Arbeit (auch Nebenberuf oder Fotovoltaik-Anlage)')),
            CheckboxField::create($p . 'ProfitTrade', _t(__TRAIT__ . '.ProfitTrade', 'Einkünfte aus einem Gewerbebetrieb')),
            CheckboxField::create($p . 'ProfitAgri', _t(__TRAIT__ . '.ProfitAgri', 'Einkünfte aus Land- und Forstwirtschaft')),
            HeaderField::create($p . 'Profit1Header', _t(__TRAIT__ . '.Profit1Header', 'Erste Gewinneinkünfte'), 5),
            DateField::create($p . 'Profit1From', _t(__TRAIT__ . '.Profit1From', 'von')),
            DateField::create($p . 'Profit1Until', _t(__TRAIT__ . '.Profit1Until', 'bis')),
            TextField::create($p . 'Profit1Amount', _t(__TRAIT__ . '.Profit1Amount', 'Durchschnittliche Gewinneinkünfte in Euro pro Monat (Verluste mit Minuszeichen)'))
                ->setMaxLength(12),
            TextField::create($p . 'Profit1Hours', _t(__TRAIT__ . '.Profit1Hours', 'Durchschnittliche Arbeitszeit in Stunden pro Woche'))
                ->setMaxLength(4),
            CheckboxField::create($p . 'Profit2', _t(__TRAIT__ . '.Profit2', 'Ich habe weitere Gewinneinkünfte')),
            Wrapper::create(
                DateField::create($p . 'Profit2From', _t(__TRAIT__ . '.Profit2From', 'Weitere Gewinneinkünfte von')),
                DateField::create($p . 'Profit2Until', _t(__TRAIT__ . '.Profit2Until', 'Weitere Gewinneinkünfte bis')),
                TextField::create($p . 'Profit2Amount', _t(__TRAIT__ . '.Profit2Amount', 'Weitere Gewinneinkünfte: Euro pro Monat'))
                    ->setMaxLength(12),
                TextField::create($p . 'Profit2Hours', _t(__TRAIT__ . '.Profit2Hours', 'Weitere Gewinneinkünfte: Stunden pro Woche'))
                    ->setMaxLength(4),
            )
                ->displayIf($p . 'Profit2')->isChecked()
                ->end(),
            CheckboxField::create($p . 'ActualExpenses', _t(__TRAIT__ . '.ActualExpenses', 'Ich beantrage, dass anstelle einer Pauschale von 25 % meine tatsächlichen Betriebsausgaben berücksichtigt werden (lohnt sich, wenn die Betriebsausgaben mehr als 25 % der Einnahmen ausmachen)')),
            DropdownField::create($p . 'AllTaxFree', _t(__TRAIT__ . '.AllTaxFree', 'Werden alle Ihre Gewinneinkünfte während des Elterngeldbezugs voraussichtlich steuerfrei sein (z. B. Trinkgelder, Übungsleiterpauschale, oft Fotovoltaik-Anlagen)?'))
                ->setSource([
                    'yes' => _t(__TRAIT__ . '.AllTaxFree.yes', 'Ja'),
                    'no' => _t(__TRAIT__ . '.AllTaxFree.no', 'Nein'),
                ])
                ->setEmptyString($select),
            TextField::create($p . 'DaycareChildren', _t(__TRAIT__ . '.DaycareChildren', 'Falls Sie in der Tagespflege arbeiten: Wie viele Kinder betreuen Sie (ohne eigene Kinder)? (optional)'))
                ->setMaxLength(2),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        $p = static::FIELD_PREFIX;

        return RequiredFieldsValidator::create([
            $p . 'Profit1From',
            $p . 'Profit1Until',
            $p . 'Profit1Amount',
            $p . 'Profit1Hours',
            $p . 'AllTaxFree',
        ]);
    }

    public function validateStep($data, $form)
    {
        $p = static::FIELD_PREFIX;
        $valid = $this->validateConditionalRequired($data, $form);
        $valid = $this->validatePatterns(
            $form,
            [$p . 'Profit1Amount' => '/^-?\d{1,3}(\.?\d{3})*(,\d{1,2})?$/', $p . 'Profit2Amount' => '/^-?\d{1,3}(\.?\d{3})*(,\d{1,2})?$/'],
            _t(__TRAIT__ . '.AMOUNT_INVALID', 'Bitte einen Betrag in Euro eingeben, z. B. 1.250,00 oder -300.')
        ) && $valid;
        $valid = $this->validatePatterns(
            $form,
            [$p . 'Profit1Hours' => '/^([0-9]|[12][0-9]|3[0-2])([.,]\d{1,2})?$/', $p . 'Profit2Hours' => '/^([0-9]|[12][0-9]|3[0-2])([.,]\d{1,2})?$/'],
            _t(__TRAIT__ . '.HOURS_INVALID', 'Bitte eine Stundenzahl von höchstens 32 eingeben.')
        ) && $valid;
        $valid = $this->validatePatterns(
            $form,
            [$p . 'DaycareChildren' => '/^\d{1,2}$/'],
            _t(__TRAIT__ . '.NUMBER_INVALID', 'Bitte eine Zahl eingeben.')
        ) && $valid;

        return $valid && parent::validateStep($data, $form);
    }
}
