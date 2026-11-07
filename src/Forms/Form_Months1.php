<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an Sie" (see MonthsStepTrait).
 */
class Form_Months1 extends MultiFormStep
{
    use StepTrait, MonthsStepTrait {
        MonthsStepTrait::validateStep insteadof StepTrait;
    }

    public const PERSON = 1;

    public const FIELD_PREFIX = 'P1';

    private static $default_sort_order = 15;

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Planung der Elterngeld-Monate: Sie');
    }
}
