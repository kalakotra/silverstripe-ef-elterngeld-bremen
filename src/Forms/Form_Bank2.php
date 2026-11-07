<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an den anderen Elternteil" (see BankStepTrait).
 */
class Form_Bank2 extends MultiFormStep
{
    use StepTrait, BankStepTrait {
        BankStepTrait::validateStep insteadof StepTrait;
    }

    public const PERSON = 2;

    public const FIELD_PREFIX = 'P2';

    private static $default_sort_order = 26;

    private static array $conditional_required = [
        'P2BankName',
        'P2IBAN',
        'P2AccountOwner',
        'P2HolderFirstName',
        'P2HolderLastName',
    ];

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Bankverbindung und Kontakt: anderer Elternteil');
    }
}
