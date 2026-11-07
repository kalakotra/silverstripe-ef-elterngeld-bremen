<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an Sie" (see BankStepTrait).
 */
class Form_Bank1 extends MultiFormStep
{
    use StepTrait, BankStepTrait {
        BankStepTrait::validateStep insteadof StepTrait;
    }

    public const PERSON = 1;

    public const FIELD_PREFIX = 'P1';

    private static $default_sort_order = 25;

    private static array $conditional_required = [
        'P1HolderFirstName',
        'P1HolderLastName',
    ];

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Bankverbindung, Kontakt und Unterschrift: Sie');
    }
}
