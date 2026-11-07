<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an den anderen Elternteil" (see AbroadStepTrait).
 */
class Form_Abroad2 extends MultiFormStep
{
    use StepTrait, AbroadStepTrait {
        AbroadStepTrait::validateStep insteadof StepTrait;
    }

    public const PERSON = 2;

    public const FIELD_PREFIX = 'P2';

    private static $default_sort_order = 6;

    private static array $conditional_required = [
        'P2StayFrom',
        'P2StayUntil',
        'P2StaySince',
        'P2OtherWorksInGermany',
    ];

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Anlage Wohnsitz im Ausland: anderer Elternteil');
    }
}
