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
 * Anlage "Einkünfte neben dem Elterngeld", 1. nicht-selbstständige Arbeit (first + further employment).
 *
 * Using classes define: PERSON, FIELD_PREFIX, $default_sort_order, $conditional_required, introText().
 */
trait AnnexJobsStepTrait
{
    abstract protected function introText(): string;

    public function getFields(): FieldList
    {
        $p = static::FIELD_PREFIX;
        $none = '-- ' . _t(__TRAIT__ . '.JobTypeNone', 'nichts davon (reguläre Beschäftigung)') . ' --';
        $types = [
            'mini' => _t(__TRAIT__ . '.JobType.mini', 'ein Mini-Job (bis 556 Euro brutto monatlich)'),
            'midi' => _t(__TRAIT__ . '.JobType.midi', 'ein Midi-Job (mehr als 556 Euro und höchstens 2.000 Euro brutto monatlich)'),
            'training' => _t(__TRAIT__ . '.JobType.training', 'eine Berufsausbildung'),
            'voluntary' => _t(__TRAIT__ . '.JobType.voluntary', 'ein Freiwilligendienst (z. B. FSJ, FÖJ, BFD)'),
        ];

        return FieldList::create(
            HeaderField::create('IntroText', $this->introText(), 4),
            LiteralField::create($p . 'JobsNotice', '<div class="alert alert-info">' . _t(__TRAIT__ . '.JobsNotice', 'Wie viele Stunden werden Sie voraussichtlich arbeiten, während Sie Elterngeld bekommen? Höchstens 32 Stunden pro Woche. Nachweise: Arbeitsvertrag, Vereinbarung über die Arbeitszeit während der Elternzeit oder Arbeitgeberbescheinigung, außerdem ein Nachweis über die voraussichtlichen Einkünfte.') . '</div>'),
            HeaderField::create($p . 'Job1Header', _t(__TRAIT__ . '.Job1Header', 'Erste Tätigkeit'), 5),
            DateField::create($p . 'Job1From', _t(__TRAIT__ . '.Job1From', 'von')),
            DateField::create($p . 'Job1Until', _t(__TRAIT__ . '.Job1Until', 'bis')),
            TextField::create($p . 'Job1Hours', _t(__TRAIT__ . '.Job1Hours', 'Durchschnittliche Stunden pro Woche'))
                ->setMaxLength(4),
            DropdownField::create($p . 'Job1Type', _t(__TRAIT__ . '.Job1Type', 'Dies ist …'))
                ->setSource($types)
                ->setEmptyString($none),
            CheckboxField::create($p . 'Job2', _t(__TRAIT__ . '.Job2', 'Ich habe eine weitere Tätigkeit')),
            Wrapper::create(
                DateField::create($p . 'Job2From', _t(__TRAIT__ . '.Job2From', 'Weitere Tätigkeit von')),
                DateField::create($p . 'Job2Until', _t(__TRAIT__ . '.Job2Until', 'Weitere Tätigkeit bis')),
                TextField::create($p . 'Job2Hours', _t(__TRAIT__ . '.Job2Hours', 'Weitere Tätigkeit: durchschnittliche Stunden pro Woche'))
                    ->setMaxLength(4),
                DropdownField::create($p . 'Job2Type', _t(__TRAIT__ . '.Job2Type', 'Weitere Tätigkeit: dies ist …'))
                    ->setSource($types)
                    ->setEmptyString($none),
            )
                ->displayIf($p . 'Job2')->isChecked()
                ->end(),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        $p = static::FIELD_PREFIX;

        return RequiredFieldsValidator::create([
            $p . 'Job1From',
            $p . 'Job1Until',
            $p . 'Job1Hours',
        ]);
    }

    public function validateStep($data, $form)
    {
        $p = static::FIELD_PREFIX;
        $valid = $this->validateConditionalRequired($data, $form);
        $valid = $this->validatePatterns(
            $form,
            [$p . 'Job1Hours' => '/^([0-9]|[12][0-9]|3[0-2])([.,]\d{1,2})?$/', $p . 'Job2Hours' => '/^([0-9]|[12][0-9]|3[0-2])([.,]\d{1,2})?$/'],
            _t(__TRAIT__ . '.HOURS_INVALID', 'Bitte eine Stundenzahl von höchstens 32 eingeben.')
        ) && $valid;

        return $valid && parent::validateStep($data, $form);
    }
}
