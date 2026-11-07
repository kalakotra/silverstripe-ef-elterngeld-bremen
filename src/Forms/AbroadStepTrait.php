<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * Anlage "Wohnsitz im Ausland" (zu Abschnitt 2.c) for one parent; only when 2.c = "Nein, ich lebe im Ausland".
 *
 * Using classes define: PERSON, FIELD_PREFIX, $default_sort_order, $conditional_required, introText().
 */
trait AbroadStepTrait
{
    abstract protected function introText(): string;

    public function getFields(): FieldList
    {
        $p = static::FIELD_PREFIX;
        $select = '-- ' . _t(__TRAIT__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --';
        $yesNo = [
            'yes' => _t(__TRAIT__ . '.YesNo.yes', 'Ja'),
            'no' => _t(__TRAIT__ . '.YesNo.no', 'Nein'),
        ];

        return FieldList::create(
            HeaderField::create('IntroText', $this->introText(), 4),
            TextField::create($p . 'AbroadCountry', _t(__TRAIT__ . '.AbroadCountry', 'Ich lebe in (Staat)'))
                ->setMaxLength(60),
            TextField::create($p . 'AbroadStreet', _t(__TRAIT__ . '.AbroadStreet', 'Straße'))
                ->setMaxLength(80),
            TextField::create($p . 'AbroadHouseNumber', _t(__TRAIT__ . '.AbroadHouseNumber', 'Hausnummer'))
                ->setMaxLength(4),
            TextField::create($p . 'AbroadPostalCode', _t(__TRAIT__ . '.AbroadPostalCode', 'PLZ'))
                ->setMaxLength(12),
            TextField::create($p . 'AbroadCity', _t(__TRAIT__ . '.AbroadCity', 'Ort'))
                ->setMaxLength(60),
            TextField::create($p . 'AbroadAddressExtra', _t(__TRAIT__ . '.AbroadAddressExtra', 'Adresszusatz (optional)'))
                ->setMaxLength(80),
            DropdownField::create($p . 'StayType', _t(__TRAIT__ . '.StayType', 'Mein Aufenthalt ist …'))
                ->setSource([
                    'temporary' => _t(__TRAIT__ . '.StayType.temporary', 'befristet'),
                    'permanent' => _t(__TRAIT__ . '.StayType.permanent', 'unbefristet'),
                ])
                ->setEmptyString($select),
            Wrapper::create(
                TextField::create($p . 'StayFrom', _t(__TRAIT__ . '.StayFrom', 'Befristet von (MM.JJJJ)'))
                    ->setMaxLength(7),
                TextField::create($p . 'StayUntil', _t(__TRAIT__ . '.StayUntil', 'Befristet bis (MM.JJJJ)'))
                    ->setMaxLength(7),
                LiteralField::create($p . 'StayEvidence', '<p>' . _t(__TRAIT__ . '.StayEvidence', 'Falls vorhanden beifügen: A1-Bescheinigung, Bescheinigung des Arbeitgebers oder Dienstherrn über Entsendung, Abordnung, Versetzung oder Kommandierung, Nachweis der zwischen- oder überstaatlichen Einrichtung, des Missionswerks oder des Trägers des Entwicklungsdienstes.') . '</p>'),
            )
                ->displayIf($p . 'StayType')->isEqualTo('temporary')
                ->end(),
            Wrapper::create(
                TextField::create($p . 'StaySince', _t(__TRAIT__ . '.StaySince', 'Unbefristet seit (MM.JJJJ)'))
                    ->setMaxLength(7),
            )
                ->displayIf($p . 'StayType')->isEqualTo('permanent')
                ->end(),
            DropdownField::create($p . 'WorksInGermany', _t(__TRAIT__ . '.WorksInGermany', 'Arbeiten Sie in Deutschland (nicht-selbstständig oder selbstständig) oder haben Sie einen Arbeitsvertrag nach deutschem Recht?'))
                ->setSource($yesNo)
                ->setEmptyString($select),
            Wrapper::create(
                DropdownField::create($p . 'OtherWorksInGermany', _t(__TRAIT__ . '.OtherWorksInGermany', 'Arbeitet der andere Elternteil in Deutschland oder hat er einen Arbeitsvertrag nach deutschem Recht?'))
                    ->setSource($yesNo)
                    ->setEmptyString($select),
            )
                ->displayIf($p . 'WorksInGermany')->isEqualTo('no')
                ->end(),
            HeaderField::create($p . 'EmployerHeader', _t(__TRAIT__ . '.EmployerHeader', 'Falls Sie oder der andere Elternteil einen deutschen Arbeitgeber haben: Wo hat dieser seinen Sitz in Deutschland?'), 5),
            TextField::create($p . 'EmployerPostalCode', _t(__TRAIT__ . '.EmployerPostalCode', 'PLZ des Arbeitgebers'))
                ->setMaxLength(5),
            TextField::create($p . 'EmployerCity', _t(__TRAIT__ . '.EmployerCity', 'Ort des Arbeitgebers'))
                ->setMaxLength(60),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        $p = static::FIELD_PREFIX;

        return RequiredFieldsValidator::create([
            $p . 'AbroadCountry',
            $p . 'AbroadStreet',
            $p . 'AbroadCity',
            $p . 'StayType',
            $p . 'WorksInGermany',
        ]);
    }

    public function validateStep($data, $form)
    {
        $p = static::FIELD_PREFIX;
        $valid = $this->validateConditionalRequired($data, $form);
        $valid = $this->validateMonthFields($form, [$p . 'StayFrom', $p . 'StayUntil', $p . 'StaySince']) && $valid;
        $valid = $this->validatePatterns(
            $form,
            [$p . 'EmployerPostalCode' => '/^\d{5}$/'],
            _t(__TRAIT__ . '.PLZ_INVALID', 'Bitte eine fünfstellige Postleitzahl eingeben.')
        ) && $valid;

        return $valid && parent::validateStep($data, $form);
    }
}
