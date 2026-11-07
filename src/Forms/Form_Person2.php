<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an den anderen Elternteil" (see PersonStepTrait).
 */
class Form_Person2 extends MultiFormStep
{
    use StepTrait, PersonStepTrait {
        PersonStepTrait::validateStep insteadof StepTrait;
    }

    public const PERSON = 2;

    public const FIELD_PREFIX = 'P2';

    private static $default_sort_order = 5;

    private static array $conditional_required = [
        'P2ResidenceSince',
        'P2ResidenceSinceDate',
        'P2SameAddress',
        'P2Street',
        'P2HouseNumber',
        'P2PostalCode',
        'P2City',
    ];

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Angaben zum anderen Elternteil');
    }
}
