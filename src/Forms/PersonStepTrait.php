<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * 2.b Angaben zu den Elternteilen, 2.c Wohnsitz, 2.d Arbeit im Ausland + Steuer-ID for one column.
 * Field names "P{n}<SubmissionParent field>"; labels via __TRAIT__ keys (one translation for both columns).
 * Column 1 ("Sie") also answers Familienstand "mit dem anderen Elternteil?" and the child's address,
 * column 2 can take over the address of column 1.
 *
 * Using classes define: PERSON, FIELD_PREFIX, $default_sort_order, $conditional_required, introText().
 */
trait PersonStepTrait
{
    abstract protected function introText(): string;

    public function getFields(): FieldList
    {
        $p = static::FIELD_PREFIX;
        $first = static::PERSON === 1;
        $select = '-- ' . _t(__TRAIT__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --';
        $yesNo = [
            'yes' => _t(__TRAIT__ . '.YesNo.yes', 'Ja'),
            'no' => _t(__TRAIT__ . '.YesNo.no', 'Nein'),
        ];

        $marital = [
            DropdownField::create($p . 'MaritalStatus', _t(__TRAIT__ . '.MaritalStatus', 'Familienstand'))
                ->setSource([
                    'married' => _t(__TRAIT__ . '.MaritalStatus.married', 'Verheiratet'),
                    'partnership' => _t(__TRAIT__ . '.MaritalStatus.partnership', 'Verpartnert (in eingetragener Lebenspartnerschaft)'),
                    'divorced' => _t(__TRAIT__ . '.MaritalStatus.divorced', 'Geschieden'),
                    'widowed' => _t(__TRAIT__ . '.MaritalStatus.widowed', 'Verwitwet'),
                    'single' => _t(__TRAIT__ . '.MaritalStatus.single', 'Ledig'),
                ])
                ->setEmptyString($select),
        ];
        if ($first) {
            $marital[] = Wrapper::create(
                DropdownField::create($p . 'MarriedToOther', _t(__TRAIT__ . '.MarriedToOther', 'Verheiratet mit dem anderen Elternteil?'))
                    ->setSource($yesNo)
                    ->setEmptyString($select),
            )
                ->displayIf($p . 'MaritalStatus')->isEqualTo('married')
                ->end();
            $marital[] = Wrapper::create(
                DropdownField::create($p . 'PartneredToOther', _t(__TRAIT__ . '.PartneredToOther', 'Verpartnert mit dem anderen Elternteil?'))
                    ->setSource($yesNo)
                    ->setEmptyString($select),
            )
                ->displayIf($p . 'MaritalStatus')->isEqualTo('partnership')
                ->end();
        }

        $address = [
            TextField::create($p . 'Street', _t(__TRAIT__ . '.Street', 'Straße'))
                ->setMaxLength(60),
            TextField::create($p . 'HouseNumber', _t(__TRAIT__ . '.HouseNumber', 'Hausnummer'))
                ->setMaxLength(6),
            TextField::create($p . 'PostalCode', _t(__TRAIT__ . '.PostalCode', 'PLZ'))
                ->setMaxLength(5),
            TextField::create($p . 'City', _t(__TRAIT__ . '.City', 'Ort'))
                ->setMaxLength(40),
            TextField::create($p . 'AddressExtra', _t(__TRAIT__ . '.AddressExtra', 'Adresszusatz (optional)'))
                ->setMaxLength(40),
        ];

        $residenceYes = [
            DropdownField::create($p . 'ResidenceSince', _t(__TRAIT__ . '.ResidenceSince', 'Seit wann leben Sie in Deutschland?'))
                ->setSource([
                    'birth' => _t(__TRAIT__ . '.ResidenceSince.birth', 'Seit meiner Geburt'),
                    'date' => _t(__TRAIT__ . '.ResidenceSince.date', 'Seit einem bestimmten Datum'),
                ])
                ->setEmptyString($select),
            Wrapper::create(
                DateField::create($p . 'ResidenceSinceDate', _t(__TRAIT__ . '.ResidenceSinceDate', 'In Deutschland seit')),
            )
                ->displayIf($p . 'ResidenceSince')->isEqualTo('date')
                ->end(),
            HeaderField::create($p . 'AddressHeader', _t(__TRAIT__ . '.AddressHeader', 'Welche Adresse haben Sie?'), 5),
        ];
        if ($first) {
            array_push($residenceYes, ...$address);
        } else {
            $residenceYes[] = DropdownField::create($p . 'SameAddress', _t(__TRAIT__ . '.SameAddress', 'Wohnen Sie mit dem anderen Elternteil zusammen und haben dieselbe Anschrift?'))
                ->setSource($yesNo)
                ->setEmptyString($select);
            $residenceYes[] = Wrapper::create(...$address)
                ->displayIf($p . 'SameAddress')->isEqualTo('no')
                ->end();
        }

        $fields = [
            HeaderField::create('IntroText', $this->introText(), 4),
            TextField::create($p . 'FirstName', _t(__TRAIT__ . '.FirstName', 'Vorname(n)'))
                ->setMaxLength(42),
            TextField::create($p . 'LastName', _t(__TRAIT__ . '.LastName', 'Nachname'))
                ->setMaxLength(42),
            DateField::create($p . 'BirthDate', _t(__TRAIT__ . '.BirthDate', 'Geburtsdatum')),
            DropdownField::create($p . 'Gender', _t(__TRAIT__ . '.Gender', 'Geschlecht'))
                ->setSource([
                    'female' => _t(__TRAIT__ . '.Gender.female', 'Weiblich'),
                    'male' => _t(__TRAIT__ . '.Gender.male', 'Männlich'),
                    'diverse' => _t(__TRAIT__ . '.Gender.diverse', 'Divers'),
                    'none' => _t(__TRAIT__ . '.Gender.none', 'Ohne Angaben (nach Personenstandsgesetz)'),
                ])
                ->setEmptyString($select),
            ...$marital,
            HeaderField::create($p . 'ResidenceHeader', _t(__TRAIT__ . '.ResidenceHeader', 'Wohnsitz / Aufenthalt'), 5),
            DropdownField::create($p . 'ResidenceGermany', _t(__TRAIT__ . '.ResidenceGermany', 'Haben Sie Ihren dauerhaften Wohnsitz in Deutschland?'))
                ->setSource([
                    'yes' => _t(__TRAIT__ . '.ResidenceGermany.yes', 'Ja'),
                    'no' => _t(__TRAIT__ . '.ResidenceGermany.no', 'Nein, ich lebe im Ausland (die Anlage „Wohnsitz im Ausland“ folgt im nächsten Schritt)'),
                ])
                ->setEmptyString($select),
            Wrapper::create(...$residenceYes)
                ->displayIf($p . 'ResidenceGermany')->isEqualTo('yes')
                ->end(),
        ];

        if ($first) {
            $fields[] = DropdownField::create($p . 'ChildAddress', _t(__TRAIT__ . '.ChildAddress', 'Hat das Kind dieselbe Adresse wie Sie?'))
                ->setSource([
                    'same' => _t(__TRAIT__ . '.ChildAddress.same', 'Ja'),
                    'other_parent' => _t(__TRAIT__ . '.ChildAddress.other_parent', 'Nein, es hat dieselbe Adresse wie der andere Elternteil'),
                    'other' => _t(__TRAIT__ . '.ChildAddress.other', 'Nein, es hat eine andere Adresse'),
                ])
                ->setEmptyString($select);
            $fields[] = Wrapper::create(
                TextField::create($p . 'ChildStreet', _t(__TRAIT__ . '.ChildStreet', 'Straße (Kind)'))
                    ->setMaxLength(76),
                TextField::create($p . 'ChildHouseNumber', _t(__TRAIT__ . '.ChildHouseNumber', 'Hausnummer (Kind)'))
                    ->setMaxLength(6),
                TextField::create($p . 'ChildPostalCode', _t(__TRAIT__ . '.ChildPostalCode', 'PLZ (Kind)'))
                    ->setMaxLength(5),
                TextField::create($p . 'ChildCity', _t(__TRAIT__ . '.ChildCity', 'Ort (Kind)'))
                    ->setMaxLength(60),
            )
                ->displayIf($p . 'ChildAddress')->isEqualTo('other')
                ->end();
        }

        array_push(
            $fields,
            HeaderField::create($p . 'TaxHeader', _t(__TRAIT__ . '.TaxHeader', 'Arbeit im Ausland und Steuer-Identifikationsnummer'), 5),
            TextField::create($p . 'WorkAbroadCountry', _t(__TRAIT__ . '.WorkAbroadCountry', 'Falls Sie im Ausland arbeiten, für einen ausländischen Arbeitgeber oder mit einem Arbeitsvertrag nach ausländischem Recht: Um welchen Staat handelt es sich? (optional)'))
                ->setMaxLength(42),
            TextField::create($p . 'TaxId', _t(__TRAIT__ . '.TaxId', 'Steuer-Identifikationsnummer (11 Ziffern)'))
                ->setMaxLength(11)
                ->setDescription(_t(__TRAIT__ . '.TaxIdDescription', 'Zu finden z. B. auf dem Einkommensteuerbescheid, der Lohnsteuerbescheinigung oder dem Schreiben des Bundeszentralamts für Steuern.')),
            LiteralField::create($p . 'PersonNotice', '<p class="text-muted">' . _t(__TRAIT__ . '.PersonNotice', 'Die Angaben zum anderen Elternteil müssen immer gemacht werden – außer Sie haben bei den Alleinerziehenden etwas angekreuzt.') . '</p>'),
        );

        return FieldList::create(...$fields);
    }

    public function getValidator(): RequiredFieldsValidator
    {
        $p = static::FIELD_PREFIX;
        $required = [$p . 'FirstName', $p . 'LastName', $p . 'BirthDate', $p . 'Gender', $p . 'MaritalStatus', $p . 'ResidenceGermany', $p . 'TaxId'];
        if (static::PERSON === 1) {
            $required[] = $p . 'ChildAddress';
        }

        return RequiredFieldsValidator::create($required);
    }

    public function validateStep($data, $form)
    {
        $p = static::FIELD_PREFIX;
        $valid = $this->validateConditionalRequired($data, $form);

        $valid = $this->validatePatterns(
            $form,
            [$p . 'TaxId' => '/^\d{11}$/'],
            _t(__TRAIT__ . '.TAXID_INVALID', 'Die Steuer-Identifikationsnummer hat 11 Ziffern.')
        ) && $valid;

        $valid = $this->validatePatterns(
            $form,
            [$p . 'PostalCode' => '/^\d{5}$/', $p . 'ChildPostalCode' => '/^\d{5}$/'],
            _t(__TRAIT__ . '.PLZ_INVALID', 'Bitte eine fünfstellige Postleitzahl eingeben.')
        ) && $valid;

        return $valid && parent::validateStep($data, $form);
    }
}
