<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an Sie" (see StatusStepTrait).
 */
class Form_Status1 extends MultiFormStep
{
    use StepTrait, StatusStepTrait {
        StatusStepTrait::validateStep insteadof StepTrait;
    }

    public const PERSON = 1;

    public const FIELD_PREFIX = 'P1';

    private static $default_sort_order = 7;

    private static array $conditional_required = [
        'P1CitizenshipType',
        'P1CitizenshipCountry',
        'P1FreedomRevoked',
        'P1InsurerName',
        'P1InsurerCity',
        'P1RelativeText',
    ];

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Staatsangehörigkeit, Krankenversicherung und Beziehung zum Kind: Sie');
    }
}
