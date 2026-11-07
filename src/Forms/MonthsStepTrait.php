<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;

/**
 * 10. Planung der Elterngeld-Monate for one column: one dropdown per Lebensmonat (Basiselterngeld only in
 * months 1-14, 15-18 for very premature children; ElterngeldPlus / Partnerschaftsbonus 1-32).
 *
 * Using classes define: PERSON, FIELD_PREFIX, $default_sort_order, introText().
 */
trait MonthsStepTrait
{
    abstract protected function introText(): string;

    public const MONTHS = 32;

    /** Last month with a Basiselterngeld column in the PDF (15-18 only for very premature children). */
    public const BASIS_MONTHS = 18;

    public const BASIS_MONTHS_REGULAR = 14;

    public function getFields(): FieldList
    {
        $p = static::FIELD_PREFIX;
        $none = '-- ' . _t(__TRAIT__ . '.MonthNone', 'kein Elterngeld') . ' --';

        $fields = [
            HeaderField::create('IntroText', $this->introText(), 4),
            LiteralField::create($p . 'MonthsNotice', '<div class="alert alert-info">' . _t(__TRAIT__ . '.MonthsNotice', 'Elterngeld wird für Lebensmonate des Kindes gezahlt (Beispiel: Geburt am 10. April – 1. Lebensmonat 10. April bis 9. Mai). Beide Eltern können zusammen 14 Monate Basiselterngeld aufteilen (ein Elternteil allein 12, Alleinerziehende 14); jeder Elternteil mindestens 2 Monate. Ein Monat Basiselterngeld kann gegen 2 Monate ElterngeldPlus getauscht werden. Den Partnerschaftsbonus nutzen beide Eltern gleichzeitig in 2 bis 4 aufeinanderfolgenden Monaten bei 24 bis 32 Wochenstunden. Ab dem 15. Lebensmonat darf der Bezug nicht unterbrochen werden. Für eine gültige Planung nutzen Sie den Elterngeldrechner des Bundesfamilienministeriums.') . '</div>'),
        ];

        for ($month = 1; $month <= self::MONTHS; $month++) {
            $source = [];
            if ($month <= self::BASIS_MONTHS) {
                $source['basis'] = _t(__TRAIT__ . '.Month.basis', 'Basiselterngeld');
            }
            $source['plus'] = _t(__TRAIT__ . '.Month.plus', 'ElterngeldPlus');
            $source['bonus'] = _t(__TRAIT__ . '.Month.bonus', 'Partnerschaftsbonus');

            $fields[] = DropdownField::create($p . 'Month' . $month, _t(__TRAIT__ . '.Month', '{month}. Lebensmonat', ['month' => $month]))
                ->setSource($source)
                ->setEmptyString($none);
        }

        return FieldList::create(...$fields);
    }

    /**
     * At least 2 Lebensmonate; Basiselterngeld after month 14 only for very premature children (1.b).
     */
    public function validateStep($data, $form)
    {
        $p = static::FIELD_PREFIX;
        $values = $form->getData();
        $valid = true;
        $chosen = 0;
        $premature = (bool) $this->flowValue(Form_Child::class, 'Premature');

        for ($month = 1; $month <= self::MONTHS; $month++) {
            $value = (string) ($values[$p . 'Month' . $month] ?? '');
            if ($value === '') {
                continue;
            }
            $chosen++;

            if ($value === 'basis' && $month > self::BASIS_MONTHS_REGULAR && !$premature) {
                $valid = false;
                $form->sessionFieldError(
                    _t(__TRAIT__ . '.BASIS_PREMATURE', 'Basiselterngeld nach dem 14. Lebensmonat ist nur bei besonders früh geborenen Kindern möglich.'),
                    $p . 'Month' . $month
                );
            }
        }

        if ($chosen < 2) {
            $valid = false;
            $form->sessionFieldError(
                _t(__TRAIT__ . '.MIN_MONTHS', 'Jeder Elternteil, der Elterngeld beantragt, muss für mindestens 2 Lebensmonate Elterngeld beziehen.'),
                $p . 'Month1'
            );
        }

        if (!$valid) {
            $form->sessionMessage(_t(__TRAIT__ . '.MONTHS_SUMMARY', 'Bitte prüfen Sie Ihre Planung der Elterngeld-Monate.'));
        }

        return $valid && parent::validateStep($data, $form);
    }
}
