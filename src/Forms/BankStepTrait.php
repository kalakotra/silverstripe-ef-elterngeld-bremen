<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * 13.a Bankverbindung, 13.b Kontakt, 13.c Weitere Mitteilungen and (column 1) the place of signature (13.d).
 * Column 2 can use the account of column 1.
 *
 * Using classes define: PERSON, FIELD_PREFIX, $default_sort_order, $conditional_required, introText().
 */
trait BankStepTrait
{
    abstract protected function introText(): string;

    public function getFields(): FieldList
    {
        $p = static::FIELD_PREFIX;
        $first = static::PERSON === 1;
        $select = '-- ' . _t(__TRAIT__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --';

        $account = [
            TextField::create($p . 'BankName', _t(__TRAIT__ . '.BankName', 'Name der Bank'))
                ->setMaxLength(42),
            TextField::create($p . 'IBAN', _t(__TRAIT__ . '.IBAN', 'IBAN'))
                ->setMaxLength(42),
            TextField::create($p . 'BIC', _t(__TRAIT__ . '.BIC', 'BIC (nur bei Konto außerhalb Deutschlands)'))
                ->setMaxLength(11),
            DropdownField::create($p . 'AccountOwner', _t(__TRAIT__ . '.AccountOwner', 'Ist das Ihr eigenes Konto?'))
                ->setSource([
                    'own' => _t(__TRAIT__ . '.AccountOwner.own', 'Ja'),
                    'other_parent' => _t(__TRAIT__ . '.AccountOwner.other_parent', 'Nein, das ist das Konto des anderen Elternteils'),
                    'third' => _t(__TRAIT__ . '.AccountOwner.third', 'Nein, das ist das Konto einer anderen Person'),
                ])
                ->setEmptyString($select),
            Wrapper::create(
                TextField::create($p . 'HolderFirstName', _t(__TRAIT__ . '.HolderFirstName', 'Vorname(n) der Kontoinhaberin / des Kontoinhabers'))
                    ->setMaxLength(40),
                TextField::create($p . 'HolderLastName', _t(__TRAIT__ . '.HolderLastName', 'Nachname der Kontoinhaberin / des Kontoinhabers'))
                    ->setMaxLength(40),
            )
                ->displayIf($p . 'AccountOwner')->isEqualTo('third')
                ->end(),
        ];

        $fields = [
            HeaderField::create('IntroText', $this->introText(), 4),
            HeaderField::create($p . 'BankHeader', _t(__TRAIT__ . '.BankHeader', 'Auf welches Konto soll das Elterngeld gezahlt werden?'), 5),
        ];
        if ($first) {
            array_push($fields, ...$account);
        } else {
            $fields[] = DropdownField::create($p . 'SameAccount', _t(__TRAIT__ . '.SameAccount', 'Auf dasselbe Konto wie das Elterngeld des anderen Elternteils?'))
                ->setSource([
                    'yes' => _t(__TRAIT__ . '.SameAccount.yes', 'Ja'),
                    'no' => _t(__TRAIT__ . '.SameAccount.no', 'Nein, auf folgendes Konto'),
                ])
                ->setEmptyString($select);
            $fields[] = Wrapper::create(...$account)
                ->displayIf($p . 'SameAccount')->isEqualTo('no')
                ->end();
        }

        array_push(
            $fields,
            HeaderField::create($p . 'ContactHeader', _t(__TRAIT__ . '.ContactHeader', 'Kontakt für Rückfragen (Angaben freiwillig)'), 5),
            TextField::create($p . 'Phone', _t(__TRAIT__ . '.Phone', 'Telefonnummer'))
                ->setMaxLength(30),
            TextField::create($p . 'Email', _t(__TRAIT__ . '.Email', 'E-Mail-Adresse'))
                ->setMaxLength(44),
            TextareaField::create($p . 'Remarks', _t(__TRAIT__ . '.Remarks', 'Möchten Sie Ihrer Elterngeldstelle noch etwas mitteilen? (freiwillig)'))
                ->setRows(4),
        );

        if ($first) {
            array_push(
                $fields,
                HeaderField::create($p . 'SignatureHeader', _t(__TRAIT__ . '.SignatureHeader', 'Unterschrift'), 5),
                TextField::create($p . 'SignaturePlace', _t(__TRAIT__ . '.SignaturePlace', 'Ort der Unterschrift'))
                    ->setMaxLength(42),
                LiteralField::create($p . 'SignatureNotice', '<p class="text-muted">' . _t(__TRAIT__ . '.SignatureNotice', 'Sie unterschreiben am Ende digital. Der Antrag ist immer von beiden Elternteilen zu unterschreiben – der andere Elternteil (und ggf. Ihre gesetzliche Vertretung) unterschreibt im ausgedruckten Antrag auf Seite 23 von Hand. Nur Alleinerziehende (Abschnitt 2.a) brauchen keine zweite Unterschrift.') . '</p>'),
            );
        }

        return FieldList::create(...$fields);
    }

    public function getValidator(): RequiredFieldsValidator
    {
        $p = static::FIELD_PREFIX;
        $required = static::PERSON === 1
            ? [$p . 'BankName', $p . 'IBAN', $p . 'AccountOwner', $p . 'SignaturePlace']
            : [$p . 'SameAccount'];

        return RequiredFieldsValidator::create($required);
    }

    public function validateStep($data, $form)
    {
        $p = static::FIELD_PREFIX;
        $valid = $this->validateConditionalRequired($data, $form);
        $values = $form->getData();

        $iban = trim((string) ($values[$p . 'IBAN'] ?? ''));
        if ($iban !== '' && !self::isValidIban($iban)) {
            $valid = false;
            $form->sessionFieldError(_t(__TRAIT__ . '.IBAN_INVALID', 'Bitte prüfen Sie die IBAN.'), $p . 'IBAN');
            $form->sessionMessage(_t(__TRAIT__ . '.CHECK_SUMMARY', 'Bitte prüfen Sie die markierten Angaben.'));
        }

        $valid = $this->validatePatterns(
            $form,
            [$p . 'BIC' => '/^[A-Za-z]{6}[A-Za-z0-9]{2}([A-Za-z0-9]{3})?$/'],
            _t(__TRAIT__ . '.BIC_INVALID', 'Bitte prüfen Sie den BIC (8 oder 11 Zeichen).')
        ) && $valid;
        $valid = $this->validatePatterns(
            $form,
            [$p . 'Email' => '/^[^@\s]+@[^@\s]+\.[^@\s]+$/'],
            _t(__TRAIT__ . '.EMAIL_INVALID', 'Bitte prüfen Sie die E-Mail-Adresse.')
        ) && $valid;

        return $valid && parent::validateStep($data, $form);
    }

    public static function isValidIban(string $iban): bool
    {
        $iban = strtoupper((string) preg_replace('/\s+/', '', $iban));
        if (!preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{11,30}$/', $iban)) {
            return false;
        }

        $rearranged = substr($iban, 4) . substr($iban, 0, 4);
        $numeric = '';
        foreach (str_split($rearranged) as $char) {
            $numeric .= ctype_alpha($char) ? (string) (ord($char) - 55) : $char;
        }

        $remainder = 0;
        foreach (str_split($numeric, 7) as $chunk) {
            $remainder = (int) (($remainder . $chunk) % 97);
        }

        return $remainder === 1;
    }
}
