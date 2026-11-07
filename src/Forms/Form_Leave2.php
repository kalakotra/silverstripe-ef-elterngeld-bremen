<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an den anderen Elternteil" (see LeaveStepTrait).
 */
class Form_Leave2 extends MultiFormStep
{
    use StepTrait, LeaveStepTrait {
        LeaveStepTrait::validateStep insteadof StepTrait;
    }

    public const PERSON = 2;

    public const FIELD_PREFIX = 'P2';

    private static $default_sort_order = 21;

    private static array $conditional_required = [
        'P2PaidLeaveDays',
        'P2PaidLeaveFrom',
        'P2PaidLeaveUntil',
        'P2WeeklyHours',
    ];

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Elternzeit und Urlaub: anderer Elternteil');
    }
}
