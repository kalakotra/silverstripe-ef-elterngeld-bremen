<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an Sie" (see LeaveStepTrait).
 */
class Form_Leave1 extends MultiFormStep
{
    use StepTrait, LeaveStepTrait {
        LeaveStepTrait::validateStep insteadof StepTrait;
    }

    public const PERSON = 1;

    public const FIELD_PREFIX = 'P1';

    private static $default_sort_order = 17;

    private static array $conditional_required = [
        'P1PaidLeaveDays',
        'P1PaidLeaveFrom',
        'P1PaidLeaveUntil',
        'P1WeeklyHours',
    ];

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Elternzeit und Urlaub: Sie');
    }
}
