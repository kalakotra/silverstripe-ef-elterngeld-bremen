<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an Sie" (see AnnexJobsStepTrait).
 */
class Form_AnnexJobs1 extends MultiFormStep
{
    use StepTrait, AnnexJobsStepTrait {
        AnnexJobsStepTrait::validateStep insteadof StepTrait;
    }

    public const PERSON = 1;

    public const FIELD_PREFIX = 'P1';

    private static $default_sort_order = 19;

    private static array $conditional_required = [
        'P1Job2From',
        'P1Job2Until',
        'P1Job2Hours',
    ];

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Anlage Einkünfte: nicht-selbstständige Arbeit (Sie)');
    }
}
