<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an den anderen Elternteil" (see AssessmentStepTrait).
 */
class Form_Assessment2 extends MultiFormStep
{
    use StepTrait, AssessmentStepTrait {
        AssessmentStepTrait::validateStep insteadof StepTrait;
    }

    public const PERSON = 2;

    public const FIELD_PREFIX = 'P2';

    private static $default_sort_order = 12;

    private static array $conditional_required = [
        'P2ProfitIncome',
        'P2ShiftYear',
        'P2Include1From',
        'P2Include1Until',
        'P2Corona1From',
        'P2Corona1Until',
    ];

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Berechnung und Bemessungszeitraum: anderer Elternteil');
    }
}
