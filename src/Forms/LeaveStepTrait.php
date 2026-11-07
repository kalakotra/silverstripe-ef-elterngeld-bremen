<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * 11. Elternzeit and 12.d Bezahlter Urlaub for one column.
 *
 * Using classes define: PERSON, FIELD_PREFIX, $default_sort_order, $conditional_required, introText().
 */
trait LeaveStepTrait
{
    abstract protected function introText(): string;

    public function getFields(): FieldList
    {
        $p = static::FIELD_PREFIX;
        $select = '-- ' . _t(__TRAIT__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --';

        return FieldList::create(
            HeaderField::create('IntroText', $this->introText(), 4),
            LiteralField::create($p . 'LeaveNotice', '<div class="alert alert-info">' . _t(__TRAIT__ . '.LeaveNotice', 'Richten Sie die Elternzeit am besten nach den Lebensmonaten des Kindes aus, sonst wird tageweise Einkommen auf das Elterngeld angerechnet. Die Elternzeit melden Sie spätestens 7 Wochen vor Beginn bei Ihrem Arbeitgeber an. Ohne Elternzeit lassen Sie die Felder leer.') . '</div>'),
            DateField::create($p . 'Leave1From', _t(__TRAIT__ . '.Leave1From', 'Elternzeit von')),
            DateField::create($p . 'Leave1Until', _t(__TRAIT__ . '.Leave1Until', 'Elternzeit bis')),
            DateField::create($p . 'Leave2From', _t(__TRAIT__ . '.Leave2From', 'Weiterer Zeitraum von (optional)')),
            DateField::create($p . 'Leave2Until', _t(__TRAIT__ . '.Leave2Until', 'Weiterer Zeitraum bis (optional)')),
            HeaderField::create($p . 'PaidLeaveHeader', _t(__TRAIT__ . '.PaidLeaveHeader', 'Bezahlter Urlaub'), 5),
            DropdownField::create($p . 'PaidLeave', _t(__TRAIT__ . '.PaidLeave', 'Werden Sie voraussichtlich bezahlten Urlaub nehmen, während Sie Elterngeld bekommen (Resturlaub, bezahlter Sonderurlaub, Bildungsurlaub)?'))
                ->setSource([
                    'no' => _t(__TRAIT__ . '.PaidLeave.no', 'Nein'),
                    'yes' => _t(__TRAIT__ . '.PaidLeave.yes', 'Ja'),
                ])
                ->setEmptyString($select)
                ->setDescription(_t(__TRAIT__ . '.PaidLeaveDescription', 'Bezahlter Urlaub wird so bewertet, als würden Sie arbeiten – das kann das Elterngeld verringern.')),
            Wrapper::create(
                TextField::create($p . 'PaidLeaveDays', _t(__TRAIT__ . '.PaidLeaveDays', 'Wie viele Tage Urlaub werden Sie nehmen?'))
                    ->setMaxLength(3),
                DateField::create($p . 'PaidLeaveFrom', _t(__TRAIT__ . '.PaidLeaveFrom', 'Urlaub von')),
                DateField::create($p . 'PaidLeaveUntil', _t(__TRAIT__ . '.PaidLeaveUntil', 'Urlaub bis')),
                TextField::create($p . 'WeeklyHours', _t(__TRAIT__ . '.WeeklyHours', 'Wöchentliche Arbeitszeit vor der Geburt bei dieser Arbeit (Stunden pro Woche)'))
                    ->setMaxLength(4),
            )
                ->displayIf($p . 'PaidLeave')->isEqualTo('yes')
                ->end(),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            static::FIELD_PREFIX . 'PaidLeave',
        ]);
    }

    public function validateStep($data, $form)
    {
        $p = static::FIELD_PREFIX;
        $valid = $this->validateConditionalRequired($data, $form);
        $valid = $this->validatePatterns(
            $form,
            [$p . 'PaidLeaveDays' => '/^\d{1,3}$/', $p . 'WeeklyHours' => '/^\d{1,2}([.,]\d{1,2})?$/'],
            _t(__TRAIT__ . '.NUMBER_INVALID', 'Bitte eine Zahl eingeben.')
        ) && $valid;

        return $valid && parent::validateStep($data, $form);
    }
}
