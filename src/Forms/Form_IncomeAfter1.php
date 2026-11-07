<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an Sie" (see IncomeAfterStepTrait).
 */
class Form_IncomeAfter1 extends MultiFormStep
{
    use StepTrait;
    use IncomeAfterStepTrait;

    public const PERSON = 1;

    public const FIELD_PREFIX = 'P1';

    private static $default_sort_order = 18;

    private static array $conditional_required = [
        'P1AfterPensionType1',
        'P1AfterOtherType1',
    ];

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Einkünfte neben dem Elterngeld: Sie');
    }
}
