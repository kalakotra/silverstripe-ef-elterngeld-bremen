<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use SilverStripe\MultiForm\Models\MultiFormStep;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * 4. Weitere Kinder im Haushalt (4.a Anzahl, 4.b Geschwisterbonus: up to three children).
 */
class Form_Siblings extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 9;

    private static array $conditional_required = [
        'OtherChildrenCount',
        'BonusChildren',
        'Sibling1FirstName',
        'Sibling1LastName',
        'Sibling1BirthDate',
        'Sibling2FirstName',
        'Sibling2LastName',
        'Sibling2BirthDate',
        'Sibling3FirstName',
        'Sibling3LastName',
        'Sibling3BirthDate',
    ];

    public function getFields(): FieldList
    {
        $select = '-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --';

        return FieldList::create(
            HeaderField::create('IntroText', _t(__CLASS__ . '.IntroText', 'Weitere Kinder im Haushalt'), 4),
            DropdownField::create('OtherChildren', _t(__CLASS__ . '.OtherChildren', 'Leben weitere Kinder bis 18 Jahre in Ihrem Haushalt (egal, ob mit Ihnen verwandt oder nicht)?'))
                ->setSource([
                    'none' => _t(__CLASS__ . '.OtherChildren.none', 'Keine weiteren Kinder'),
                    'some' => _t(__CLASS__ . '.OtherChildren.some', 'Ja, weitere Kinder'),
                ])
                ->setEmptyString($select),
            Wrapper::create(
                TextField::create('OtherChildrenCount', _t(__CLASS__ . '.OtherChildrenCount', 'Anzahl der weiteren Kinder'))
                    ->setMaxLength(2),
                LiteralField::create('BonusNotice', '<div class="alert alert-info">' . _t(__CLASS__ . '.BonusNotice', 'Geschwisterbonus: Geben Sie nur Kinder an, die mindestens einen dieser Punkte erfüllen – ein weiteres Kind unter 3 Jahren, mindestens 2 weitere Kinder unter 6 Jahren oder ein weiteres Kind mit Behinderung unter 14 Jahren. Höchstens drei Kinder, beginnend mit dem jüngsten.') . '</div>'),
                DropdownField::create('BonusChildren', _t(__CLASS__ . '.BonusChildren', 'Wie viele Kinder erfüllen diese Punkte?'))
                    ->setSource([
                        '0' => _t(__CLASS__ . '.BonusChildren.0', 'Keines'),
                        '1' => '1',
                        '2' => '2',
                        '3' => _t(__CLASS__ . '.BonusChildren.3', '3 oder mehr'),
                    ])
                    ->setEmptyString($select),
                Wrapper::create(
                    HeaderField::create('Sibling1Header', _t(__CLASS__ . '.Sibling1Header', '1. Geschwisterkind'), 5),
                    TextField::create('Sibling1FirstName', _t(__CLASS__ . '.Sibling1FirstName', 'Vorname(n)'))
                        ->setMaxLength(42),
                    TextField::create('Sibling1LastName', _t(__CLASS__ . '.Sibling1LastName', 'Nachname'))
                        ->setMaxLength(42),
                    DateField::create('Sibling1BirthDate', _t(__CLASS__ . '.Sibling1BirthDate', 'Geburtsdatum')),
                    DateField::create('Sibling1AdoptionDate', _t(__CLASS__ . '.Sibling1AdoptionDate', 'Tag der Haushaltsaufnahme (nur bei Adoption)')),
                    CheckboxField::create('Sibling1Disability', _t(__CLASS__ . '.Sibling1Disability', 'Das Kind hat einen Grad der Behinderung von 20 oder höher')),
                    CheckboxField::create('Sibling1BiologicalP1', _t(__CLASS__ . '.Sibling1BiologicalP1', 'Das Kind ist mein leibliches Kind')),
                    CheckboxField::create('Sibling1BiologicalP2', _t(__CLASS__ . '.Sibling1BiologicalP2', 'Das Kind ist leibliches Kind des anderen Elternteils')),
                )
                    ->displayIf('BonusChildren')->isEqualTo('1')
                    ->orIf('BonusChildren')->isEqualTo('2')
                    ->orIf('BonusChildren')->isEqualTo('3')
                    ->end(),
                Wrapper::create(
                    HeaderField::create('Sibling2Header', _t(__CLASS__ . '.Sibling2Header', '2. Geschwisterkind'), 5),
                    TextField::create('Sibling2FirstName', _t(__CLASS__ . '.Sibling2FirstName', 'Vorname(n)'))
                        ->setMaxLength(42),
                    TextField::create('Sibling2LastName', _t(__CLASS__ . '.Sibling2LastName', 'Nachname'))
                        ->setMaxLength(42),
                    DateField::create('Sibling2BirthDate', _t(__CLASS__ . '.Sibling2BirthDate', 'Geburtsdatum')),
                    DateField::create('Sibling2AdoptionDate', _t(__CLASS__ . '.Sibling2AdoptionDate', 'Tag der Haushaltsaufnahme (nur bei Adoption)')),
                    CheckboxField::create('Sibling2Disability', _t(__CLASS__ . '.Sibling2Disability', 'Das Kind hat einen Grad der Behinderung von 20 oder höher')),
                    CheckboxField::create('Sibling2BiologicalP1', _t(__CLASS__ . '.Sibling2BiologicalP1', 'Das Kind ist mein leibliches Kind')),
                    CheckboxField::create('Sibling2BiologicalP2', _t(__CLASS__ . '.Sibling2BiologicalP2', 'Das Kind ist leibliches Kind des anderen Elternteils')),
                )
                    ->displayIf('BonusChildren')->isEqualTo('2')
                    ->orIf('BonusChildren')->isEqualTo('3')
                    ->end(),
                Wrapper::create(
                    HeaderField::create('Sibling3Header', _t(__CLASS__ . '.Sibling3Header', '3. Geschwisterkind'), 5),
                    TextField::create('Sibling3FirstName', _t(__CLASS__ . '.Sibling3FirstName', 'Vorname(n)'))
                        ->setMaxLength(42),
                    TextField::create('Sibling3LastName', _t(__CLASS__ . '.Sibling3LastName', 'Nachname'))
                        ->setMaxLength(42),
                    DateField::create('Sibling3BirthDate', _t(__CLASS__ . '.Sibling3BirthDate', 'Geburtsdatum')),
                    DateField::create('Sibling3AdoptionDate', _t(__CLASS__ . '.Sibling3AdoptionDate', 'Tag der Haushaltsaufnahme (nur bei Adoption)')),
                    CheckboxField::create('Sibling3Disability', _t(__CLASS__ . '.Sibling3Disability', 'Das Kind hat einen Grad der Behinderung von 20 oder höher')),
                    CheckboxField::create('Sibling3BiologicalP1', _t(__CLASS__ . '.Sibling3BiologicalP1', 'Das Kind ist mein leibliches Kind')),
                    CheckboxField::create('Sibling3BiologicalP2', _t(__CLASS__ . '.Sibling3BiologicalP2', 'Das Kind ist leibliches Kind des anderen Elternteils')),
                )
                    ->displayIf('BonusChildren')->isEqualTo('3')
                    ->end(),
                LiteralField::create('SiblingEvidence', '<p>' . _t(__CLASS__ . '.SiblingEvidence', 'Nachweise je Kind: bei Behinderung Feststellungsbescheid oder Schwerbehindertenausweis, bei Adoption Adoptionsurkunde bzw. Bestätigung über die Adoptionspflege.') . '</p>'),
            )
                ->displayIf('OtherChildren')->isEqualTo('some')
                ->end(),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            'OtherChildren',
        ]);
    }

    public function validateStep($data, $form)
    {
        $valid = $this->validateConditionalRequired($data, $form);

        return $this->validatePatterns(
            $form,
            ['OtherChildrenCount' => '/^[1-9]\d?$/'],
            _t(__CLASS__ . '.COUNT_INVALID', 'Bitte eine Zahl eingeben.')
        ) && $valid && parent::validateStep($data, $form);
    }
}
