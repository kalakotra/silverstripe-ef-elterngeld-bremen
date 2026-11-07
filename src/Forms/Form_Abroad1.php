<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an Sie" (see AbroadStepTrait).
 */
class Form_Abroad1 extends MultiFormStep
{
    use StepTrait, AbroadStepTrait {
        AbroadStepTrait::validateStep insteadof StepTrait;
    }

    public const PERSON = 1;

    public const FIELD_PREFIX = 'P1';

    private static $default_sort_order = 4;

    private static array $conditional_required = [
        'P1StayFrom',
        'P1StayUntil',
        'P1StaySince',
        'P1OtherWorksInGermany',
    ];

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Anlage Wohnsitz im Ausland: Sie');
    }
}
