<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an den anderen Elternteil" (see MonthsStepTrait).
 */
class Form_Months2 extends MultiFormStep
{
    use StepTrait, MonthsStepTrait {
        MonthsStepTrait::validateStep insteadof StepTrait;
    }

    public const PERSON = 2;

    public const FIELD_PREFIX = 'P2';

    private static $default_sort_order = 16;

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Planung der Elterngeld-Monate: anderer Elternteil');
    }
}
