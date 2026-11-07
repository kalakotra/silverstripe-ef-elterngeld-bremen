<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an den anderen Elternteil" (see EarningsStepTrait).
 */
class Form_Earnings2 extends MultiFormStep
{
    use StepTrait, EarningsStepTrait {
        EarningsStepTrait::validateStep insteadof StepTrait;
    }

    public const PERSON = 2;

    public const FIELD_PREFIX = 'P2';

    private static $default_sort_order = 13;

    private static array $conditional_required = [
        'P2PensionType1',
        'P2OtherType1',
    ];

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Einkommen vor der Geburt: anderer Elternteil');
    }
}
