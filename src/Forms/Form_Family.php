<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * 2.a Alleinerziehende, 2.f Antragstellende, 2.e Gesamteinkommen (household values; the 2.e tax IDs are per parent).
 */
class Form_Family extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 2;

    public function getFields(): FieldList
    {
        $select = '-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --';

        return FieldList::create(
            HeaderField::create('IntroText', _t(__CLASS__ . '.IntroText', 'Eltern und Antragstellung'), 4),
            HeaderField::create('SingleHeader', _t(__CLASS__ . '.SingleHeader', 'Alleinerziehende: Trifft eine der folgenden Aussagen auf Sie zu?'), 5),
            LiteralField::create('SingleNotice', '<p>' . _t(__CLASS__ . '.SingleNotice', 'Wenn Sie hier etwas ankreuzen, sind keine Angaben zum anderen Elternteil notwendig und der andere Elternteil muss den Antrag nicht unterschreiben. Kreuzen Sie nichts an, wenn keine Aussage zutrifft.') . '</p>'),
            CheckboxField::create('SingleNotTogether', _t(__CLASS__ . '.SingleNotTogether', 'Der andere Elternteil wohnt weder mit mir noch dem Kind zusammen, und ich habe Steuerklasse II oder sonst Anspruch auf den Entlastungsbetrag für Alleinerziehende (Nachweis beifügen, z. B. Gehaltsabrechnung oder ELStAM-Auszug)')),
            CheckboxField::create('SingleCareImpossible', _t(__CLASS__ . '.SingleCareImpossible', 'Für den anderen Elternteil ist es unmöglich, das Kind zu betreuen, z. B. wegen Krankheit oder Behinderung (Nachweis beifügen)')),
            CheckboxField::create('SingleChildWelfare', _t(__CLASS__ . '.SingleChildWelfare', 'Die Betreuung durch den anderen Elternteil würde das Wohl des Kindes gefährden (Bescheinigung des Jugendamtes beifügen)')),
            DropdownField::create('Applicants', _t(__CLASS__ . '.Applicants', 'Wer stellt den Antrag?'))
                ->setSource([
                    'both' => _t(__CLASS__ . '.Applicants.both', 'Beide Elternteile'),
                    'me_later' => _t(__CLASS__ . '.Applicants.me_later', 'Ich, der andere Elternteil entscheidet später'),
                    'me_other_applied' => _t(__CLASS__ . '.Applicants.me_other_applied', 'Ich, der andere Elternteil hat bereits Elterngeld beantragt'),
                    'only_me' => _t(__CLASS__ . '.Applicants.only_me', 'Nur ich'),
                ])
                ->setEmptyString($select)
                ->setDescription(_t(__CLASS__ . '.ApplicantsDescription', 'Die Fragen ab Abschnitt 2.g an den anderen Elternteil werden nur bei „Beide Elternteile“ gestellt. Der andere Elternteil muss den Antrag trotzdem immer unterschreiben (außer bei Alleinerziehenden).')),
            DropdownField::create('IncomeLimit', _t(__CLASS__ . '.IncomeLimit', 'Wie hoch war das zu versteuernde Einkommen im Kalenderjahr vor der Geburt insgesamt? (Alleinerziehende: nur Ihr eigenes Einkommen, sonst beide Elternteile zusammen)'))
                ->setSource([
                    'below' => _t(__CLASS__ . '.IncomeLimit.below', 'unter 175.000 Euro oder genau 175.000 Euro, der genaue Betrag steht schon fest'),
                    'above' => _t(__CLASS__ . '.IncomeLimit.above', 'mehr als 175.000 Euro, der genaue Betrag steht schon fest'),
                    'likely_above' => _t(__CLASS__ . '.IncomeLimit.likely_above', 'voraussichtlich mehr als 175.000 Euro, der genaue Betrag steht noch nicht fest'),
                    'likely_below' => _t(__CLASS__ . '.IncomeLimit.likely_below', 'voraussichtlich weniger als 175.000 Euro, der genaue Betrag steht noch nicht fest'),
                ])
                ->setEmptyString($select)
                ->setDescription(_t(__CLASS__ . '.IncomeLimitDescription', 'Einkommensteuerbescheid für das Kalenderjahr vor der Geburt beifügen, falls vorhanden.')),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            'Applicants',
            'IncomeLimit',
        ]);
    }
}
