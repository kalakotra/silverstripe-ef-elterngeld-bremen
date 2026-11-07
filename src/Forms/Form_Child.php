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
 * 1. Angaben zum Kind (1.a, 1.b) + 3.c Adoption.
 */
class Form_Child extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 1;

    private static array $conditional_required = [
        'ChildrenBorn',
        'DueDate',
        'AdoptionDate',
    ];

    public function getFields(): FieldList
    {
        $select = '-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --';

        return FieldList::create(
            HeaderField::create('IntroText', _t(__CLASS__ . '.IntroText', 'Angaben zum Kind'), 4),
            LiteralField::create('DeadlineNotice', '<div class="alert alert-info">' . _t(__CLASS__ . '.DeadlineNotice', 'Frist: Falls Sie ab der Geburt Elterngeld bekommen möchten, stellen Sie den Antrag spätestens, wenn Ihr Kind 3 Monate alt ist. Die spezielle Geburtsurkunde zur Beantragung von Elterngeld legen Sie im Original bei, alle anderen Nachweise als Kopie.') . '</div>'),
            TextField::create('ChildFirstName', _t(__CLASS__ . '.ChildFirstName', 'Vorname(n) des Kindes'))
                ->setMaxLength(42),
            TextField::create('ChildLastName', _t(__CLASS__ . '.ChildLastName', 'Nachname des Kindes'))
                ->setMaxLength(42),
            CheckboxField::create('Multiple', _t(__CLASS__ . '.Multiple', 'Zwillinge, Drillinge oder weitere Mehrlinge (oben nur den Namen des ersten Kindes eintragen)')),
            Wrapper::create(
                DropdownField::create('ChildrenBorn', _t(__CLASS__ . '.ChildrenBorn', 'Wie viele Kinder wurden insgesamt geboren?'))
                    ->setSource([
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        '5' => '5',
                    ])
                    ->setEmptyString($select),
            )
                ->displayIf('Multiple')->isChecked()
                ->end(),
            DateField::create('ChildBirthDate', _t(__CLASS__ . '.ChildBirthDate', 'Geburtsdatum des Kindes')),
            CheckboxField::create('Premature', _t(__CLASS__ . '.Premature', 'Mein Kind wurde besonders früh geboren, das heißt mindestens 6 Wochen vor dem errechneten Geburtstermin (ärztliches Zeugnis oder Zeugnis einer Hebamme beifügen)')),
            Wrapper::create(
                DateField::create('DueDate', _t(__CLASS__ . '.DueDate', 'Welcher Geburtstermin wurde ursprünglich errechnet?')),
            )
                ->displayIf('Premature')->isChecked()
                ->end(),
            CheckboxField::create('ChildDisability', _t(__CLASS__ . '.ChildDisability', 'Mein Kind hat eine Behinderung (ärztliche Bescheinigung beifügen)')),
            CheckboxField::create('Adopted', _t(__CLASS__ . '.Adopted', 'Das Kind ist ein Adoptivkind oder in Adoptionspflege')),
            Wrapper::create(
                DateField::create('AdoptionDate', _t(__CLASS__ . '.AdoptionDate', 'Wann haben Sie das Kind in Ihren Haushalt aufgenommen, um es zu adoptieren?')),
            )
                ->displayIf('Adopted')->isChecked()
                ->end(),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            'ChildFirstName',
            'ChildLastName',
            'ChildBirthDate',
        ]);
    }
}
