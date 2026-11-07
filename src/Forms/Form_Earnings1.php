<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an Sie" (see EarningsStepTrait).
 */
class Form_Earnings1 extends MultiFormStep
{
    use StepTrait, EarningsStepTrait {
        EarningsStepTrait::validateStep insteadof StepTrait;
    }

    public const PERSON = 1;

    public const FIELD_PREFIX = 'P1';

    private static $default_sort_order = 11;

    private static array $conditional_required = [
        'P1PensionType1',
        'P1OtherType1',
    ];

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Einkommen vor der Geburt: Sie');
    }
}
