<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Column "Fragen an Sie" (see PersonStepTrait).
 */
class Form_Person1 extends MultiFormStep
{
    use StepTrait, PersonStepTrait {
        PersonStepTrait::validateStep insteadof StepTrait;
    }

    public const PERSON = 1;

    public const FIELD_PREFIX = 'P1';

    private static $default_sort_order = 3;

    private static array $conditional_required = [
        'P1MarriedToOther',
        'P1PartneredToOther',
        'P1ResidenceSince',
        'P1ResidenceSinceDate',
        'P1Street',
        'P1HouseNumber',
        'P1PostalCode',
        'P1City',
        'P1ChildStreet',
        'P1ChildHouseNumber',
        'P1ChildPostalCode',
        'P1ChildCity',
    ];

    protected function introText(): string
    {
        return _t(__CLASS__ . '.IntroText', 'Angaben zu Ihnen');
    }
}
