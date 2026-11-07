<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an Sie" (see AssessmentStepTrait).
 */
class Form_Assessment1 extends MultiFormStep
{
    use StepTrait, AssessmentStepTrait {
        AssessmentStepTrait::validateStep insteadof StepTrait;
    }

    public const PERSON = 1;

    public const FIELD_PREFIX = 'P1';

    private static $default_sort_order = 10;

    private static array $conditional_required = [
        'P1ProfitIncome',
        'P1ShiftYear',
        'P1Include1From',
        'P1Include1Until',
        'P1Corona1From',
        'P1Corona1Until',
    ];

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Berechnung und Bemessungszeitraum: Sie');
    }
}
