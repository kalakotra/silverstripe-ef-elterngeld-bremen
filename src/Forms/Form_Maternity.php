<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\MultiForm\Models\MultiFormStep;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * 9. Nach der Geburt: Mutterschaftsleistungen (questions to the birth mother, always answered).
 */
class Form_Maternity extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 14;

    private static array $conditional_required = [
        'MatStatutoryFrom',
        'MatStatutoryUntil',
        'MatEmployerFrom',
        'MatEmployerUntil',
        'MatCivilFrom',
        'MatCivilUntil',
        'MatPrivateFrom',
        'MatPrivateUntil',
        'MatForeignFrom',
        'MatForeignUntil',
    ];

    private const CLAIMS = ['MatStatutory', 'MatEmployer', 'MatCivil', 'MatPrivate', 'MatForeign'];

    public function getFields(): FieldList
    {
        return FieldList::create(
            HeaderField::create('IntroText', _t(__CLASS__ . '.IntroText', 'Mutterschaftsleistungen'), 4),
            LiteralField::create('MaternityNotice', '<div class="alert alert-info">' . _t(__CLASS__ . '.MaternityNotice', 'Fragen an die leibliche Mutter des Kindes. Diese Fragen müssen immer beantwortet werden – auch wenn der Antrag nicht für die leibliche Mutter gestellt wird. Hat die leibliche Mutter Anspruch auf Mutterschaftsleistungen für dieses Kind?') . '</div>'),
            CheckboxField::create('MatStatutory', _t(__CLASS__ . '.MatStatutory', 'Anspruch auf Mutterschaftsgeld von der gesetzlichen Krankenversicherung')),
            Wrapper::create(
                DateField::create('MatStatutoryFrom', _t(__CLASS__ . '.MatStatutoryFrom', 'Mutterschaftsgeld von')),
                DateField::create('MatStatutoryUntil', _t(__CLASS__ . '.MatStatutoryUntil', 'Mutterschaftsgeld bis')),
                CheckboxField::create('MatConsent', _t(__CLASS__ . '.MatConsent', 'Bitte rufen Sie die Daten zu meinem Mutterschaftsgeld direkt bei meiner Krankenkasse ab (dann muss ich keine Bescheinigung beilegen). Ich bin einverstanden, dass die Krankenkasse übermittelt, ob, für welchen Zeitraum und in welcher Höhe Mutterschaftsgeld bewilligt wurde (§ 203 Absatz 1 SGB V). Die Einwilligung kann ich jederzeit widerrufen.')),
            )
                ->displayIf('MatStatutory')->isChecked()
                ->end(),
            CheckboxField::create('MatEmployer', _t(__CLASS__ . '.MatEmployer', 'Anspruch auf einen Zuschuss zum Mutterschaftsgeld vom Arbeitgeber (bei Insolvenz von der Krankenkasse)')),
            Wrapper::create(
                DateField::create('MatEmployerFrom', _t(__CLASS__ . '.MatEmployerFrom', 'Zuschuss von')),
                DateField::create('MatEmployerUntil', _t(__CLASS__ . '.MatEmployerUntil', 'Zuschuss bis')),
            )
                ->displayIf('MatEmployer')->isChecked()
                ->end(),
            CheckboxField::create('MatCivil', _t(__CLASS__ . '.MatCivil', 'Beamtin oder Soldatin mit Anspruch auf Dienst- oder Anwärterbezüge oder Zuschüsse')),
            Wrapper::create(
                DateField::create('MatCivilFrom', _t(__CLASS__ . '.MatCivilFrom', 'Bezüge von')),
                DateField::create('MatCivilUntil', _t(__CLASS__ . '.MatCivilUntil', 'Bezüge bis')),
            )
                ->displayIf('MatCivil')->isChecked()
                ->end(),
            CheckboxField::create('MatPrivate', _t(__CLASS__ . '.MatPrivate', 'Anspruch auf Krankentagegeld von der privaten Krankenversicherung')),
            Wrapper::create(
                DateField::create('MatPrivateFrom', _t(__CLASS__ . '.MatPrivateFrom', 'Krankentagegeld von')),
                DateField::create('MatPrivateUntil', _t(__CLASS__ . '.MatPrivateUntil', 'Krankentagegeld bis')),
            )
                ->displayIf('MatPrivate')->isChecked()
                ->end(),
            CheckboxField::create('MatForeign', _t(__CLASS__ . '.MatForeign', 'Anspruch auf vergleichbare Mutterschaftsleistungen aus dem Ausland')),
            Wrapper::create(
                DateField::create('MatForeignFrom', _t(__CLASS__ . '.MatForeignFrom', 'Leistungen von')),
                DateField::create('MatForeignUntil', _t(__CLASS__ . '.MatForeignUntil', 'Leistungen bis')),
            )
                ->displayIf('MatForeign')->isChecked()
                ->end(),
            CheckboxField::create('MatNone', _t(__CLASS__ . '.MatNone', 'Kein Anspruch auf Mutterschaftsleistungen für dieses Kind')),
        );
    }

    /**
     * Exactly one of: at least one claim, or "kein Anspruch".
     */
    public function validateStep($data, $form)
    {
        $valid = $this->validateConditionalRequired($data, $form);
        $values = $form->getData();

        $claims = array_filter(self::CLAIMS, static fn (string $field): bool => (bool) ($values[$field] ?? false));
        $none = (bool) ($values['MatNone'] ?? false);

        if ($claims === [] && !$none) {
            $form->sessionFieldError(_t(__CLASS__ . '.CHOOSE_ONE', 'Bitte kreuzen Sie mindestens einen Anspruch oder „Kein Anspruch“ an.'), 'MatNone');
            $form->sessionMessage(_t(__CLASS__ . '.CHOOSE_ONE', 'Bitte kreuzen Sie mindestens einen Anspruch oder „Kein Anspruch“ an.'));
            $valid = false;
        } elseif ($claims !== [] && $none) {
            $form->sessionFieldError(_t(__CLASS__ . '.NONE_CONFLICT', '„Kein Anspruch“ kann nicht zusammen mit einem Anspruch angekreuzt werden.'), 'MatNone');
            $form->sessionMessage(_t(__CLASS__ . '.NONE_CONFLICT', '„Kein Anspruch“ kann nicht zusammen mit einem Anspruch angekreuzt werden.'));
            $valid = false;
        }

        return $valid && parent::validateStep($data, $form);
    }
}
