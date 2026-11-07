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
 * 5. Antrag nur in Höhe des Mindestbetrags, 6.a bisherige Erwerbstätigkeit, 6.b Bemessungszeitraum für Selbstständige,
 * 6.c Bemessungszeitraum für Nicht-Selbstständige for one column.
 * 6.a and the 6.b option "Gewinneinkünfte unter 35 Euro" are one dropdown (ProfitIncome): none / taxfree / below35
 * lead to 6.c, taxable to 6.b.
 *
 * Using classes define: PERSON, FIELD_PREFIX, $default_sort_order, $conditional_required, introText().
 */
trait AssessmentStepTrait
{
    abstract protected function introText(): string;

    public function getFields(): FieldList
    {
        $p = static::FIELD_PREFIX;
        $select = '-- ' . _t(__TRAIT__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --';

        return FieldList::create(
            HeaderField::create('IntroText', $this->introText(), 4),
            DropdownField::create($p . 'AmountType', _t(__TRAIT__ . '.AmountType', 'Wie soll das Elterngeld berechnet werden?'))
                ->setSource([
                    'calculated' => _t(__TRAIT__ . '.AmountType.calculated', 'Anhand meines Einkommens vor der Geburt'),
                    'minimum' => _t(__TRAIT__ . '.AmountType.minimum', 'Nur in Höhe des Mindestbetrags (300 Euro Basiselterngeld / 150 Euro ElterngeldPlus) – keine Angaben zum Einkommen nötig'),
                ])
                ->setEmptyString($select)
                ->setDescription(_t(__TRAIT__ . '.AmountTypeDescription', 'Beim Mindestbetrag wird das Elterngeld nicht anhand des Einkommens berechnet, auch wenn es dadurch höher sein könnte. Während des Elterngeldbezugs arbeiten Sie dann gar nicht oder höchstens 32 Stunden pro Woche.')),
            Wrapper::create(
                LiteralField::create($p . 'ProfitNotice', '<div class="alert alert-info">' . _t(__TRAIT__ . '.ProfitNotice', 'Betrachten Sie das Kalenderjahr vor der Geburt und die 12 Kalendermonate vor der Geburt. Gewinneinkünfte sind Einkünfte aus einem Gewerbebetrieb, aus selbstständiger Arbeit (auch Nebenberuf oder Fotovoltaik-Anlage) und aus Land- und Forstwirtschaft – auch geringe, negative oder nur zeitweise Einkünfte.') . '</div>'),
                DropdownField::create($p . 'ProfitIncome', _t(__TRAIT__ . '.ProfitIncome', 'Hatten Sie in diesem Zeitraum Gewinneinkünfte?'))
                    ->setSource([
                        'none' => _t(__TRAIT__ . '.ProfitIncome.none', 'Nein, ich hatte überhaupt keine Gewinneinkünfte'),
                        'taxfree' => _t(__TRAIT__ . '.ProfitIncome.taxfree', 'Ja, diese waren jedoch alle steuerfrei'),
                        'below35' => _t(__TRAIT__ . '.ProfitIncome.below35', 'Ja, nicht alle steuerfrei, aber durchschnittlich unter 35 Euro monatlich – ich beantrage, dass sie nicht berücksichtigt werden'),
                        'taxable' => _t(__TRAIT__ . '.ProfitIncome.taxable', 'Ja, diese waren nicht alle steuerfrei (Bemessungszeitraum für Selbstständige)'),
                    ])
                    ->setEmptyString($select)
                    ->setDescription(_t(__TRAIT__ . '.ProfitIncomeDescription', '„Unter 35 Euro“ gilt nur, wenn die Gewinneinkünfte sowohl im Kalenderjahr der Geburt als auch im Kalenderjahr davor durchschnittlich unter 35 Euro monatlich lagen (im Jahr vor der Geburt insgesamt unter 420 Euro).')),
                Wrapper::create(
                    HeaderField::create($p . 'SEHeader', _t(__TRAIT__ . '.SEHeader', 'Bemessungszeitraum für Selbstständige (Kalenderjahr vor der Geburt)'), 5),
                    LiteralField::create($p . 'SENotice', '<p>' . _t(__TRAIT__ . '.SENotice', 'Wenn Sie hier etwas ankreuzen, können Sie beantragen, dass der Bemessungszeitraum auf ein früheres Kalenderjahr verschoben wird. Im Bemessungszeitraum …') . '</p>'),
                    CheckboxField::create($p . 'SEMaternityThis', _t(__TRAIT__ . '.SEMaternityThis', 'war ich für dieses Kind im Mutterschutz oder habe Mutterschaftsgeld oder Krankentagegeld bekommen')),
                    CheckboxField::create($p . 'SEMaternityOlder', _t(__TRAIT__ . '.SEMaternityOlder', 'war ich für ein älteres Kind im Mutterschutz oder habe Mutterschaftsgeld oder Krankentagegeld bekommen')),
                    CheckboxField::create($p . 'SEParentalOlder', _t(__TRAIT__ . '.SEParentalOlder', 'habe ich Elterngeld für ein älteres Kind bekommen, das damals noch keine 14 Monate alt war')),
                    CheckboxField::create($p . 'SEParentalPremature', _t(__TRAIT__ . '.SEParentalPremature', 'habe ich Elterngeld für ein älteres, besonders früh geborenes Kind bekommen')),
                    CheckboxField::create($p . 'SEIllness', _t(__TRAIT__ . '.SEIllness', 'hatte ich eine Erkrankung, die maßgeblich auf meine Schwangerschaft zurückzuführen war')),
                    CheckboxField::create($p . 'SEService', _t(__TRAIT__ . '.SEService', 'habe ich Wehrdienst oder Zivildienst geleistet')),
                    CheckboxField::create($p . 'ShiftRequest', _t(__TRAIT__ . '.ShiftRequest', 'Ich beantrage, dass mein Bemessungszeitraum auf ein früheres Kalenderjahr verschoben wird (auch bei Einkommensverlust durch die Corona-Pandemie zwischen 01.03.2020 und 23.09.2022)')),
                    Wrapper::create(
                        TextField::create($p . 'ShiftYear', _t(__TRAIT__ . '.ShiftYear', 'Kalenderjahr (JJJJ)'))
                            ->setMaxLength(4),
                    )
                        ->displayIf($p . 'ShiftRequest')->isChecked()
                        ->end(),
                    LiteralField::create($p . 'SEEvidence', '<p class="text-muted">' . _t(__TRAIT__ . '.SEEvidence', 'Steuerbescheid für das Kalenderjahr vor der Geburt beifügen (sonst Einnahmen-Überschuss-Rechnung) sowie die Einnahmen-Überschuss-Rechnung für das Jahr der Geburt bis zum Monat vor der Geburt; für jeden angekreuzten Grund den passenden Nachweis.') . '</p>'),
                )
                    ->displayIf($p . 'ProfitIncome')->isEqualTo('taxable')
                    ->end(),
                Wrapper::create(
                    HeaderField::create($p . 'EmpHeader', _t(__TRAIT__ . '.EmpHeader', 'Bemessungszeitraum für Nicht-Selbstständige (12 Kalendermonate vor dem Geburtsmonat)'), 5),
                    LiteralField::create($p . 'EmpNotice', '<p>' . _t(__TRAIT__ . '.EmpNotice', 'Monate mit einem der folgenden Gründe werden „ausgeklammert“ und durch frühere Monate ersetzt. In bestimmten Kalendermonaten im Bemessungszeitraum …') . '</p>'),
                    CheckboxField::create($p . 'EmpMaternityThis', _t(__TRAIT__ . '.EmpMaternityThis', 'war ich für dieses Kind im Mutterschutz oder habe Mutterschaftsgeld oder Krankentagegeld bekommen')),
                    CheckboxField::create($p . 'EmpMaternityOlder', _t(__TRAIT__ . '.EmpMaternityOlder', 'war ich für ein älteres Kind im Mutterschutz oder habe Mutterschaftsgeld oder Krankentagegeld bekommen')),
                    CheckboxField::create($p . 'EmpParentalOlder', _t(__TRAIT__ . '.EmpParentalOlder', 'habe ich Elterngeld für ein älteres Kind bekommen, das damals noch keine 14 Monate alt war')),
                    CheckboxField::create($p . 'EmpParentalPremature', _t(__TRAIT__ . '.EmpParentalPremature', 'habe ich Elterngeld für ein älteres, besonders früh geborenes Kind bekommen')),
                    CheckboxField::create($p . 'EmpIllness', _t(__TRAIT__ . '.EmpIllness', 'hatte ich eine Erkrankung, die maßgeblich auf meine Schwangerschaft zurückzuführen war')),
                    CheckboxField::create($p . 'EmpService', _t(__TRAIT__ . '.EmpService', 'habe ich Wehrdienst oder Zivildienst geleistet')),
                    CheckboxField::create($p . 'IncludeRequest', _t(__TRAIT__ . '.IncludeRequest', 'Ich beantrage, dass ausgeklammerte Kalendermonate trotzdem berücksichtigt werden (sinnvoll, wenn Sie in diesen Monaten mehr Einkommen hatten)')),
                    Wrapper::create(
                        TextField::create($p . 'Include1From', _t(__TRAIT__ . '.Include1From', 'Zeitraum 1 von (MM.JJJJ)'))
                            ->setMaxLength(7),
                        TextField::create($p . 'Include1Until', _t(__TRAIT__ . '.Include1Until', 'Zeitraum 1 bis (MM.JJJJ)'))
                            ->setMaxLength(7),
                        TextField::create($p . 'Include2From', _t(__TRAIT__ . '.Include2From', 'Zeitraum 2 von (MM.JJJJ, optional)'))
                            ->setMaxLength(7),
                        TextField::create($p . 'Include2Until', _t(__TRAIT__ . '.Include2Until', 'Zeitraum 2 bis (MM.JJJJ, optional)'))
                            ->setMaxLength(7),
                    )
                        ->displayIf($p . 'IncludeRequest')->isChecked()
                        ->end(),
                    CheckboxField::create($p . 'Corona', _t(__TRAIT__ . '.Corona', 'Ich beantrage, dass Kalendermonate ausgeklammert werden, in denen ich zwischen 01.03.2020 und 23.09.2022 wegen der Corona-Pandemie weniger Einkommen hatte (Gehaltsabrechnungen und Schreiben des Arbeitgebers beifügen)')),
                    Wrapper::create(
                        TextField::create($p . 'Corona1From', _t(__TRAIT__ . '.Corona1From', 'Zeitraum 1 von (MM.JJJJ)'))
                            ->setMaxLength(7),
                        TextField::create($p . 'Corona1Until', _t(__TRAIT__ . '.Corona1Until', 'Zeitraum 1 bis (MM.JJJJ)'))
                            ->setMaxLength(7),
                        TextField::create($p . 'Corona2From', _t(__TRAIT__ . '.Corona2From', 'Zeitraum 2 von (MM.JJJJ, optional)'))
                            ->setMaxLength(7),
                        TextField::create($p . 'Corona2Until', _t(__TRAIT__ . '.Corona2Until', 'Zeitraum 2 bis (MM.JJJJ, optional)'))
                            ->setMaxLength(7),
                    )
                        ->displayIf($p . 'Corona')->isChecked()
                        ->end(),
                    DropdownField::create($p . 'ChangedProfit', _t(__TRAIT__ . '.ChangedProfit', 'Nur wenn sich Ihr Bemessungszeitraum durch ausgeklammerte Monate geändert hat: Hatten Sie im geänderten Bemessungszeitraum neben Lohn oder Gehalt auch Gewinneinkünfte?'))
                        ->setSource([
                            'none' => _t(__TRAIT__ . '.ChangedProfit.none', 'Nein, überhaupt keine solchen Einkünfte (auch nicht aus Nebenberuf oder Fotovoltaik-Anlage)'),
                            'taxfree' => _t(__TRAIT__ . '.ChangedProfit.taxfree', 'Ja, diese waren jedoch alle steuerfrei'),
                            'taxable' => _t(__TRAIT__ . '.ChangedProfit.taxable', 'Ja, diese waren nicht alle steuerfrei'),
                        ])
                        ->setEmptyString('-- ' . _t(__TRAIT__ . '.ChangedProfitEmpty', 'Bemessungszeitraum hat sich nicht geändert') . ' --')
                        ->setDescription(_t(__TRAIT__ . '.ChangedProfitDescription', 'Bei „nicht alle steuerfrei“ gilt der Bemessungszeitraum für Selbstständige: Wählen Sie dann oben bei den Gewinneinkünften „nicht alle steuerfrei“.')),
                )
                    ->displayIf($p . 'ProfitIncome')->isEqualTo('none')
                    ->orIf($p . 'ProfitIncome')->isEqualTo('taxfree')
                    ->orIf($p . 'ProfitIncome')->isEqualTo('below35')
                    ->end(),
            )
                ->displayIf($p . 'AmountType')->isEqualTo('calculated')
                ->end(),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            static::FIELD_PREFIX . 'AmountType',
        ]);
    }

    public function validateStep($data, $form)
    {
        $p = static::FIELD_PREFIX;
        $valid = $this->validateConditionalRequired($data, $form);
        $valid = $this->validateMonthFields($form, [
            $p . 'Include1From', $p . 'Include1Until', $p . 'Include2From', $p . 'Include2Until',
            $p . 'Corona1From', $p . 'Corona1Until', $p . 'Corona2From', $p . 'Corona2Until',
        ]) && $valid;
        $valid = $this->validatePatterns(
            $form,
            [$p . 'ShiftYear' => '/^(19|20)\d\d$/'],
            _t(__TRAIT__ . '.YEAR_INVALID', 'Bitte ein Jahr mit vier Ziffern eingeben.')
        ) && $valid;

        return $valid && parent::validateStep($data, $form);
    }
}
