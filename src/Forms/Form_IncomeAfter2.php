<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an den anderen Elternteil" (see IncomeAfterStepTrait).
 */
class Form_IncomeAfter2 extends MultiFormStep
{
    use StepTrait;
    use IncomeAfterStepTrait;

    public const PERSON = 2;

    public const FIELD_PREFIX = 'P2';

    private static $default_sort_order = 22;

    private static array $conditional_required = [
        'P2AfterPensionType1',
        'P2AfterOtherType1',
    ];

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Einkünfte neben dem Elterngeld: anderer Elternteil');
    }
}
