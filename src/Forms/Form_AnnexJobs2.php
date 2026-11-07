<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an den anderen Elternteil" (see AnnexJobsStepTrait).
 */
class Form_AnnexJobs2 extends MultiFormStep
{
    use StepTrait, AnnexJobsStepTrait {
        AnnexJobsStepTrait::validateStep insteadof StepTrait;
    }

    public const PERSON = 2;

    public const FIELD_PREFIX = 'P2';

    private static $default_sort_order = 23;

    private static array $conditional_required = [
        'P2Job2From',
        'P2Job2Until',
        'P2Job2Hours',
    ];

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Anlage Einkünfte: nicht-selbstständige Arbeit (anderer Elternteil)');
    }
}
