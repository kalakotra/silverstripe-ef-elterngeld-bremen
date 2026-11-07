<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an den anderen Elternteil" (see AnnexProfitStepTrait).
 */
class Form_AnnexProfit2 extends MultiFormStep
{
    use StepTrait, AnnexProfitStepTrait {
        AnnexProfitStepTrait::validateStep insteadof StepTrait;
    }

    public const PERSON = 2;

    public const FIELD_PREFIX = 'P2';

    private static $default_sort_order = 24;

    private static array $conditional_required = [
        'P2Profit2From',
        'P2Profit2Until',
        'P2Profit2Amount',
        'P2Profit2Hours',
    ];

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Anlage Einkünfte: Selbstständigkeit (anderer Elternteil)');
    }
}
