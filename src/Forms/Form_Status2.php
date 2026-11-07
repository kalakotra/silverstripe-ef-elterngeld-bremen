<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an den anderen Elternteil" (see StatusStepTrait).
 */
class Form_Status2 extends MultiFormStep
{
    use StepTrait, StatusStepTrait {
        StatusStepTrait::validateStep insteadof StepTrait;
    }

    public const PERSON = 2;

    public const FIELD_PREFIX = 'P2';

    private static $default_sort_order = 8;

    private static array $conditional_required = [
        'P2CitizenshipType',
        'P2CitizenshipCountry',
        'P2FreedomRevoked',
        'P2SameInsurer',
        'P2InsurerName',
        'P2InsurerCity',
        'P2RelativeText',
    ];

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Staatsangehörigkeit, Krankenversicherung und Beziehung zum Kind: anderer Elternteil');
    }
}
