<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an Sie" (see AnnexProfitStepTrait).
 */
class Form_AnnexProfit1 extends MultiFormStep
{
    use StepTrait, AnnexProfitStepTrait {
        AnnexProfitStepTrait::validateStep insteadof StepTrait;
    }

    public const PERSON = 1;

    public const FIELD_PREFIX = 'P1';

    private static $default_sort_order = 20;

    private static array $conditional_required = [
        'P1Profit2From',
        'P1Profit2Until',
        'P1Profit2Amount',
        'P1Profit2Hours',
    ];

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Anlage Einkünfte: Selbstständigkeit (Sie)');
    }
}
