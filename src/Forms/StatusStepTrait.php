<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * 2.g Staatsangehörigkeit (+ Anlage Staatsangehörigkeit), 2.h NATO / Botschaften, 2.i Krankenversicherung,
 * 3.a Betreuung, 3.b Eltern-Kind-Beziehung for one column.
 *
 * Using classes define: PERSON, FIELD_PREFIX, $default_sort_order, $conditional_required, introText().
 */
trait StatusStepTrait
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

        $insurer = [
            TextField::create($p . 'InsurerName', _t(__TRAIT__ . '.InsurerName', 'Name der Krankenkasse'))
                ->setMaxLength(42),
            TextField::create($p . 'InsurerCity', _t(__TRAIT__ . '.InsurerCity', 'Sitz der Krankenkasse (Ort)'))
                ->setMaxLength(42),
        ];
        $statutory = [
            TextField::create($p . 'InsuranceNumber', _t(__TRAIT__ . '.InsuranceNumber', 'Versichertennummer (optional)'))
                ->setMaxLength(10),
        ];
        if ($first) {
            array_push($statutory, ...$insurer);
        } else {
            $statutory[] = DropdownField::create($p . 'SameInsurer', _t(__TRAIT__ . '.SameInsurer', 'Haben Sie dieselbe Krankenkasse wie der andere Elternteil?'))
                ->setSource($yesNo)
                ->setEmptyString($select);
            $statutory[] = Wrapper::create(...$insurer)
                ->displayIf($p . 'SameInsurer')->isEqualTo('no')
                ->end();
        }

        return FieldList::create(
            HeaderField::create('IntroText', $this->introText(), 4),
            DropdownField::create($p . 'Citizenship', _t(__TRAIT__ . '.Citizenship', 'Welche Staatsangehörigkeit haben Sie?'))
                ->setSource([
                    'german' => _t(__TRAIT__ . '.Citizenship.german', 'Deutsch'),
                    'other' => _t(__TRAIT__ . '.Citizenship.other', 'Eine andere, keine oder meine Staatsangehörigkeit ist ungeklärt'),
                ])
                ->setEmptyString($select),
            Wrapper::create(
                DropdownField::create($p . 'CitizenshipType', _t(__TRAIT__ . '.CitizenshipType', 'Anlage Staatsangehörigkeit: Welche Staatsangehörigkeit haben Sie?'))
                    ->setSource([
                        'eu' => _t(__TRAIT__ . '.CitizenshipType.eu', 'die eines EU-Staates, der Schweiz oder eines anderen EWR-Staates'),
                        'other' => _t(__TRAIT__ . '.CitizenshipType.other', 'die Staatsangehörigkeit eines anderen Staates (Nachweis über das Aufenthaltsrecht beifügen)'),
                        'stateless' => _t(__TRAIT__ . '.CitizenshipType.stateless', 'ich bin staatenlos / meine Staatsangehörigkeit ist ungeklärt (Nachweis über das Aufenthaltsrecht beifügen)'),
                    ])
                    ->setEmptyString($select),
                Wrapper::create(
                    TextField::create($p . 'CitizenshipCountry', _t(__TRAIT__ . '.CitizenshipCountry', 'und zwar von (Staat)'))
                        ->setMaxLength(60),
                )
                    ->displayIf($p . 'CitizenshipType')->isEqualTo('eu')
                    ->orIf($p . 'CitizenshipType')->isEqualTo('other')
                    ->end(),
                Wrapper::create(
                    DropdownField::create($p . 'FreedomRevoked', _t(__TRAIT__ . '.FreedomRevoked', 'Wurde Ihnen die Freizügigkeit entzogen oder läuft ein Verfahren, um sie Ihnen zu entziehen?'))
                        ->setSource($yesNo)
                        ->setEmptyString($select),
                )
                    ->displayIf($p . 'CitizenshipType')->isEqualTo('eu')
                    ->end(),
            )
                ->displayIf($p . 'Citizenship')->isEqualTo('other')
                ->end(),
            DropdownField::create($p . 'Nato', _t(__TRAIT__ . '.Nato', 'Sind Sie Mitglied der NATO-Truppe oder des zivilen Gefolges?'))
                ->setSource($yesNo)
                ->setEmptyString($select)
                ->setDescription(_t(__TRAIT__ . '.NatoDescription', 'Bei „Ja“: Nachweis über die sozialversicherungspflichtige Tätigkeit in Deutschland beifügen, falls vorhanden.')),
            DropdownField::create($p . 'Diplomatic', _t(__TRAIT__ . '.Diplomatic', 'Sind Sie Mitglied einer diplomatischen Mission / konsularischen Vertretung oder dort beschäftigt?'))
                ->setSource($yesNo)
                ->setEmptyString($select),
            HeaderField::create($p . 'InsuranceHeader', _t(__TRAIT__ . '.InsuranceHeader', 'Krankenversicherung'), 5),
            DropdownField::create($p . 'HealthInsurance', _t(__TRAIT__ . '.HealthInsurance', 'Wie sind Sie krankenversichert?'))
                ->setSource([
                    'statutory' => _t(__TRAIT__ . '.HealthInsurance.statutory', 'Gesetzlich pflichtversichert'),
                    'voluntary' => _t(__TRAIT__ . '.HealthInsurance.voluntary', 'Freiwillig gesetzlich versichert'),
                    'family' => _t(__TRAIT__ . '.HealthInsurance.family', 'Familienversichert'),
                    'private' => _t(__TRAIT__ . '.HealthInsurance.private', 'Privat versichert'),
                    'free_care' => _t(__TRAIT__ . '.HealthInsurance.free_care', 'In der freien Heilfürsorge'),
                    'none' => _t(__TRAIT__ . '.HealthInsurance.none', 'Nicht (in Deutschland) krankenversichert'),
                ])
                ->setEmptyString($select),
            Wrapper::create(...$statutory)
                ->displayIf($p . 'HealthInsurance')->isEqualTo('statutory')
                ->orIf($p . 'HealthInsurance')->isEqualTo('voluntary')
                ->end(),
            HeaderField::create($p . 'RelationHeader', _t(__TRAIT__ . '.RelationHeader', 'Beziehung zum Kind'), 5),
            DropdownField::create($p . 'Care', _t(__TRAIT__ . '.Care', 'Lebt das Kind mit Ihnen im selben Haushalt und wird von Ihnen betreut und erzogen?'))
                ->setSource($yesNo)
                ->setEmptyString($select),
            DropdownField::create($p . 'Relation', _t(__TRAIT__ . '.Relation', 'Das Kind ist …'))
                ->setSource([
                    'biological' => _t(__TRAIT__ . '.Relation.biological', 'mein leibliches Kind'),
                    'adopted' => _t(__TRAIT__ . '.Relation.adopted', 'mein Adoptivkind (auch bei Stiefkind-Adoptionen und wenn das Adoptionsverfahren noch läuft)'),
                    'spouse' => _t(__TRAIT__ . '.Relation.spouse', 'das Kind meiner Ehefrau / meines Ehemannes oder meiner eingetragenen Lebenspartnerin / meines eingetragenen Lebenspartners'),
                    'relative' => _t(__TRAIT__ . '.Relation.relative', 'ein verwandtes Kind (z. B. Enkelkind, Nichte oder Neffe, Geschwister)'),
                ])
                ->setEmptyString($select)
                ->setDescription(_t(__TRAIT__ . '.RelationDescription', 'Nachweise: leibliches Kind – Vaterschaftsanerkennung, falls Sie nicht auf der Geburtsurkunde stehen; Adoption – Adoptionsurkunde bzw. Bestätigung über die Adoptionspflege; Kind des Ehe-/Lebenspartners – Heirats- oder Lebenspartnerschaftsurkunde; verwandtes Kind – Nachweise zur Verwandtschaft und zur Unmöglichkeit der Betreuung durch die Eltern.')),
            Wrapper::create(
                TextField::create($p . 'RelativeText', _t(__TRAIT__ . '.RelativeText', 'Das Kind ist mein … (Verwandtschaftsverhältnis)'))
                    ->setMaxLength(40),
            )
                ->displayIf($p . 'Relation')->isEqualTo('relative')
                ->end(),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        $p = static::FIELD_PREFIX;

        return RequiredFieldsValidator::create([
            $p . 'Citizenship',
            $p . 'Nato',
            $p . 'Diplomatic',
            $p . 'HealthInsurance',
            $p . 'Care',
            $p . 'Relation',
        ]);
    }

    public function validateStep($data, $form)
    {
        $p = static::FIELD_PREFIX;
        $valid = $this->validateConditionalRequired($data, $form);
        $valid = $this->validatePatterns(
            $form,
            [$p . 'InsuranceNumber' => '/^[A-Za-z]\d{9}$/'],
            _t(__TRAIT__ . '.INSURANCE_INVALID', 'Die Versichertennummer besteht aus einem Buchstaben und 9 Ziffern.')
        ) && $valid;

        return $valid && parent::validateStep($data, $form);
    }
}
