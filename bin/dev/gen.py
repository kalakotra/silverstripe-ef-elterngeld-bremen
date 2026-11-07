#!/usr/bin/env python3
"""Generates the repetitive config arrays of Submission / SubmissionParent (db, field mappings, state_map,
field_conditions) for kalakotra/silverstripe-ef-elterngeld-bremen. Output: gen_parent.php.inc / gen_main.php.inc,
pasted between the GENERATED markers of the models (python3 gen.py <module dir>)."""
import re
import sys

MOD = sys.argv[1]

# ------------------------------------------------------------------------------------------------ helpers
YN = lambda p_yes, p_no: {'yes': (p_yes, 'Ja'), 'no': (p_no, 'Ja')}


def opts(*pairs, state='Ja'):
    return {v: (pdf, state) for v, pdf in pairs}


# ------------------------------------------------------------------------------------------------ parent
# (field, db, kind, {role: pdf | {value: (pdf, state)}})  role 1 = "Sie" (AS), 2 = anderer Elternteil (2E)
PARENT = []
def add(field, db, kind, m):
    PARENT.append((field, db, kind, m))

# 2b
add('FirstName', 'Varchar(60)', 'tx', {1: '2b Vorname(n) AS', 2: '2b Vorname(n) 2E'})
add('LastName', 'Varchar(60)', 'tx', {1: '2b Nachname AS', 2: '2b Nachname 2E'})
add('BirthDate', 'Date', 'date', {1: '2b Geburtsdatum AS Jahr', 2: '2b Geburtsdatum 2E Jahr'})
G = lambda f: {'female': (f, 'weiblich'), 'male': (f, 'maennlich'), 'diverse': (f, 'divers'), 'none': (f, 'ohne Angaben')}
add('Gender', 'Varchar(8)', 'st', {1: G('2b Geschlecht AS'), 2: G('2b Geschlecht 2E')})
MS = lambda a, b, c, d, e: opts(('married', f'2b Familienstand {a}'), ('partnership', f'2b Familienstand {b}'),
                                ('divorced', f'2b Familienstand {c}'), ('widowed', f'2b Familienstand {d}'),
                                ('single', f'2b Familienstand {e}'))
add('MaritalStatus', 'Varchar(12)', 'st', {1: MS(1, 4, 7, 8, 9), 2: MS(10, 11, 12, 13, 14)})
add('MarriedToOther', 'Varchar(3)', 'st', {1: YN('2b Familienstand 2', '2b Familienstand 3')})
add('PartneredToOther', 'Varchar(3)', 'st', {1: YN('2b Familienstand 5', '2b Familienstand 6')})
# 2c
add('ResidenceGermany', 'Varchar(3)', 'st', {1: YN('2b Familienstand 15', '2b Familienstand 18'),
                                             2: YN('2b Familienstand 19', '2b Familienstand 22')})
add('ResidenceSince', 'Varchar(5)', 'st', {1: opts(('birth', '2b Familienstand 16'), ('date', '2b Familienstand 17')),
                                           2: opts(('birth', '2b Familienstand 20'), ('date', '2b Familienstand 21'))})
add('ResidenceSinceDate', 'Date', 'date', {1: '2c AS in D seit Jahr', 2: '2c 2E in D seit Jahr'})
add('SameAddress', 'Varchar(3)', 'st', {2: {'yes': ('2c Adr E2 wie AS', 'gleiche Adr wie AS')}})
add('Street', 'Varchar(60)', 'lines', {1: ['2c AS Str_1', '2c AS Str_2'], 2: ['2c 2E Str_1', '2c 2E Str_2']})
add('HouseNumber', 'Varchar(6)', 'tx', {1: '2c AS Hausnr', 2: '2c 2E Hausnr'})
add('PostalCode', 'Varchar(5)', 'tx', {1: '2c AS PLZ', 2: '2c 2E PLZ 1'})
add('City', 'Varchar(60)', 'tx', {1: '2c AS Ort_2', 2: '2c 2E Ort_2'})
add('AddressExtra', 'Varchar(60)', 'tx', {1: '2c AS AdrZusatz', 2: '2c 2E AdrZusatz'})
add('ChildAddress', 'Varchar(12)', 'st', {1: opts(('same', '2e Familienstand 23'), ('other_parent', '2e Familienstand 24'),
                                                  ('other', '2e Familienstand 25'))})
add('ChildStreet', 'Varchar(80)', 'tx', {1: '2c Adr Kind Str'})
add('ChildHouseNumber', 'Varchar(6)', 'tx', {1: '2c Adr Kind Hausnr'})
add('ChildPostalCode', 'Varchar(5)', 'tx', {1: '2c Adr Kind PLZ'})
add('ChildCity', 'Varchar(60)', 'tx', {1: '2c Adr Kind Ort'})
# 2d
add('WorkAbroadCountry', 'Varchar(60)', 'tx', {1: '2d AS Arbeit Ausland', 2: '2d 2E Arbeit Ausland'})
add('TaxId', 'Varchar(11)', 'tx', {1: '2d AS Steuer-ID', 2: '2d 2E Steuer-ID'})
# 2g / 2h / 2i
add('Citizenship', 'Varchar(6)', 'st', {1: opts(('german', '2g Staatsangehörigkeit 1'), ('other', '2g Staatsangehörigkeit 2')),
                                        2: opts(('german', '2g Staatsangehörigkeit 3'), ('other', '2g Staatsangehörigkeit 4'))})
add('Nato', 'Varchar(3)', 'st', {1: YN('2h Nato 1', '2h Nato 2'), 2: YN('2h Nato 5', '2h Nato 6')})
add('Diplomatic', 'Varchar(3)', 'st', {1: YN('2h Nato 3', '2h Nato 4'), 2: YN('2h Nato 7', '2h Nato 8')})
KV = lambda o: opts(*[(v, f'2i Krankenversicherung {o + i}') for i, v in
                      enumerate(['statutory', 'voluntary', 'family', 'private', 'free_care', 'none'])])
add('HealthInsurance', 'Varchar(10)', 'st', {1: KV(1), 2: KV(7)})
add('InsuranceNumber', 'Varchar(10)', 'tx', {1: '2i Versichertennummer AS', 2: '2i Versichertennummer 2E'})
add('SameInsurer', 'Varchar(3)', 'st', {2: {'yes': ('2i gleiche KK wie AS', 'gleiche KK wie AS')}})
add('InsurerName', 'Varchar(60)', 'tx', {1: '2i Name KK AS', 2: '2i Name KK 2E'})
add('InsurerCity', 'Varchar(60)', 'tx', {1: '2i Ort KK AS', 2: '2i Ort KK 2E'})
# 3a / 3b
add('Care', 'Varchar(3)', 'st', {1: YN('3a Betreuung 1', '3a Betreuung 2'), 2: YN('3a Betreuung 3', '3a Betreuung 4')})
REL = lambda a, b, c, d: opts(('biological', f'3b Eltern {a}'), ('adopted', f'3b Eltern {b}'), ('spouse', f'3b Eltern {c}'),
                              ('relative', f'3b Eltern {d}'))
add('Relation', 'Varchar(10)', 'st', {1: REL(1, 2, 5, 6), 2: REL(3, 4, 7, 8)})
add('RelativeText', 'Varchar(60)', 'tx', {1: '3b andere Beziehung AS', 2: '3b andere Beziehung 2E'})
# 5 / 6a / 6b / 6c
add('AmountType', 'Varchar(10)', 'st', {1: {'minimum': ('5 Mindestbetrag AS', 'Ja')}, 2: {'minimum': ('5 Mindestbetrag 2E', 'Ja')}})


def profit(r):
    a = 1 if r == 1 else 4
    return {'none': (f'6a Ihre {a}', 'Ja'), 'taxfree': (f'6a Ihre {a + 1}', 'Ja'),
            'below35': (f'6a Ihre {a + 2}', 'Ja'), 'taxable': (f'6a Ihre {a + 2}', 'Ja')}


add('ProfitIncome', 'Varchar(8)', 'st', {1: profit(1), 2: profit(2)})
# second PDF field of the same dropdown (6b "unter 35 Euro") -> separate mapping key, see EXTRA_STATE
REASONS = [('MaternityThis', 'Mutterschutz dieses Kind'), ('MaternityOlder', 'Mutterschutz älteres Kind'),
           ('ParentalOlder', 'Elterngeld älteres Kind'), ('ParentalPremature', 'Elterngeld älteres K Frühgeburt'),
           ('Illness', 'Erkrankung'), ('Service', 'Wehr- o Zivildienst')]
for f, label in REASONS:
    add('SE' + f, 'Boolean', 'st', {1: {'1': (f'6b {label} AS', 'Ja')}, 2: {'1': (f'6b {label} 2E', 'Ja')}})
add('ShiftRequest', 'Boolean', 'st', {1: {'1': ('6b Antrag Verschiebung B-Zeitraum AS', 'Ja')},
                                      2: {'1': ('6b Antrag Verschiebung B-Zeitraum 2E', 'Ja')}})
add('ShiftYear', 'Varchar(4)', 'tx', {1: '6b B-Zeitraum verschieben auf AS', 2: '6b B-Zeitraum verschieben auf 2E'})
for f, label in REASONS:
    add('Emp' + f, 'Boolean', 'st', {1: {'1': (f'6c {label} AS', 'Ja')}, 2: {'1': (f'6c {label} 2E', 'Ja')}})
add('IncludeRequest', 'Boolean', 'st', {1: {'1': ('6c Antrag Berücksichtigung AS', 'Ja')},
                                        2: {'1': ('6c Antrag Berücksichtigung 2E', 'Ja')}})
for n in (1, 2):
    for part, de in (('From', 'von'), ('Until', 'bis')):
        add(f'Include{n}{part}', 'Varchar(7)', 'tx', {1: f'6c Berücksichtigung{n} {de} Jahr AS', 2: f'6c Berücksichtigung{n} {de} Jahr 2E'})
add('Corona', 'Boolean', 'st', {1: {'1': ('6c Antrag Ausklammern AS', 'Ja')}, 2: {'1': ('6c Antrag Ausklammern 2E', 'Ja')}})
for n in (1, 2):
    for part, de in (('From', 'von'), ('Until', 'bis')):
        add(f'Corona{n}{part}', 'Varchar(7)', 'tx', {1: f'6c Ausklammern{n} {de} Jahr AS', 2: f'6c Ausklammern{n} {de} Jahr 2E'})
add('ChangedProfit', 'Varchar(8)', 'st', {
    1: opts(('none', '6c Antrag Ausklammern 2E1'), ('taxfree', '6c Antrag Ausklammern 2E2'), ('taxable', '6c Antrag Ausklammern 2E3')),
    2: opts(('none', '6c Antrag Ausklammern 2E4'), ('taxfree', '6c Antrag Ausklammern 2E5'), ('taxable', '6c Antrag Ausklammern 2E6'))})
# 7a / 7b
for f, label in [('Employed', 'Einkünfte nicht-selbständig'), ('MiniJob', 'Mini-Job'), ('MidiJob', 'Midi-Job'),
                 ('Training', 'Berufsausbildung'), ('Voluntary', 'Freiwilligendienst'), ('Profit', 'Gewinneinkünfte'),
                 ('Trade', 'Gewerbebetrieb'), ('SelfEmployedWork', 'selbstständige Arbeit'),
                 ('Agriculture', 'Land-Forstwirtschaft')]:
    add(f, 'Boolean', 'st', {1: {'1': (f'7a {label} AS', 'Ja')}, 2: {'1': (f'7a {label} 2E', 'Ja')}})
for f, as_, e2 in [('ChurchTax', '7b  Kirchensteuer AS', '7b Kirchensteuer 2E'), ('HealthContrib', '7b KV PV AS', '7b KV PV 2E'),
                   ('PensionContrib', '7b RV Versorgungswerk AS', '7b RV Versorgungswerk 2E'),
                   ('UnemploymentContrib', '7b ALV AS', '7b ALV 2E'),
                   ('NoContrib', '7b keine Pflichtbeiträge AS', '7b keine Pflichtbeiträge 2E')]:
    add(f, 'Boolean', 'st', {1: {'1': (as_, 'Ja')}, 2: {'1': (e2, 'Ja')}})
# 8
for f, label in [('BenUnemployment', 'Arbeitslosengeld'), ('BenSickness', 'Krankengeld'), ('BenSicknessDaily', 'Krankentagegeld'),
                 ('BenPension', 'Rente')]:
    add(f, 'Boolean', 'st', {1: {'1': (f'8 {label} AS', 'Ja')}, 2: {'1': (f'8 {label} 2E', 'Ja')}})
add('PensionType1', 'Varchar(50)', 'tx', {1: '8 Rentenart1 AS', 2: '8 Rentenart1 2E'})
add('PensionType2', 'Varchar(50)', 'tx', {1: '8 Rentenart2 AS', 2: '8 Rentenart2 2E'})
add('BenParental', 'Boolean', 'st', {1: {'1': ('8 Elterngeld f älteres Kind AS', 'Ja')}, 2: {'1': ('8 Elterngeld f älteres Kind 2E', 'Ja')}})
add('BenOther', 'Boolean', 'st', {1: {'1': ('8 Eink-Ersatzleistungen AS', 'Ja')}, 2: {'1': ('8 Eink-Ersatzleistungen 2E', 'Ja')}})
add('OtherType1', 'Varchar(50)', 'tx', {1: '8 ArtErsatzleistung1 AS', 2: '8 ArtErsatzleistung1 2E'})
add('OtherType2', 'Varchar(50)', 'tx', {1: '8 ArtErsatzleistung2 AS', 2: '8 ArtErsatzleistung2 2E'})
add('BenCitizen', 'Boolean', 'st', {1: {'1': ('8 Bürgergeld AS', 'Ja')}, 2: {'1': ('8 Bürgergeld 2E', 'Ja')}})
# 10
for m in range(1, 33):
    def mon(role):
        r = 'AS' if role == 1 else '2E'
        o = {'plus': (f'10 Elterngeld Plus {r} Monat {m}', 'Ja'), 'bonus': (f'10 P-Bonus {r} Monat {m}', 'Ja')}
        if m <= 18:
            o = {'basis': (f'10 Basiselterngeld {r} Monat {m}', 'Ja'), **o}
        return o
    add(f'Month{m}', 'Varchar(5)', 'st', {1: mon(1), 2: mon(2)})
# 11
for n in (1, 2):
    add(f'Leave{n}From', 'Date', 'date', {1: f'11 Anfang Elternzeit{n} Jahr AS', 2: f'11 Anfang Elternzeit{n} Jahr 2E'})
    add(f'Leave{n}Until', 'Date', 'date', {1: f'11 Ende Elternzeit{n} Jahr AS', 2: f'11 Ende Elternzeit{n} Jahr 2E'})
# 12a / 12b / 12c
add('AfterEmployed', 'Boolean', 'st', {1: {'1': ('12a Einkünfte 1', 'Ja')}, 2: {'1': ('12a Einkünfte 4', 'Ja')}})
add('AfterProfit', 'Boolean', 'st', {1: {'1': ('12a Einkünfte 2', 'Ja')}, 2: {'1': ('12a Einkünfte 5', 'Ja')}})
add('EmployerCertificate', 'Boolean', None, {})
for f, label in [('AfterUnemployment', 'ALG'), ('AfterSickness', 'Krankengeld'), ('AfterSicknessDaily', 'Krankentagegeld'),
                 ('AfterPension', 'Rente')]:
    add(f, 'Boolean', 'st', {1: {'1': (f'12b {label} AS', 'Ja')}, 2: {'1': (f'12b {label} 2E', 'Ja')}})
add('AfterPensionType1', 'Varchar(50)', 'tx', {1: '12b Rentenart1 AS', 2: '12b Rentenart1 2E'})
add('AfterPensionType2', 'Varchar(50)', 'tx', {1: '12b Rentenart2 AS', 2: '12b Rentenart2 2E'})
add('AfterParental', 'Boolean', 'st', {1: {'1': ('12b Elterngelt älteres Kind AS', 'Ja')}, 2: {'1': ('12b Elterngelt älteres Kind 2E', 'Ja')}})
add('AfterOther', 'Boolean', 'st', {1: {'1': ('12b Ersatzleistungen AS', 'Ja')}, 2: {'1': ('12b Ersatzleistungen 2E', 'Ja')}})
add('AfterOtherType1', 'Varchar(50)', 'tx', {1: '12b Leistungsart1 AS', 2: '12b Leistungsart1 2E'})
add('AfterOtherType2', 'Varchar(50)', 'tx', {1: '12b Leistungsart2 AS', 2: '12b Leistungsart2 2E'})
for f, label in [('AfterCitizen', 'Bürgergeld'), ('AfterForeign', 'Leistungen Ausland'), ('AfterIntl', 'Leistungen zwStaatl Org')]:
    add(f, 'Boolean', 'st', {1: {'1': (f'12b {label} AS', 'Ja')}, 2: {'1': (f'12b {label} 2E', 'Ja')}})
add('Retraining', 'Varchar(3)', 'st', {1: YN('12c Ausbildung Umschulung 1', '12c Ausbildung Umschulung 2'),
                                       2: YN('12c Ausbildung Umschulung 3', '12c Ausbildung Umschulung 4')})
# 12d (PDF names say 12c)
add('PaidLeave', 'Varchar(3)', 'st', {1: YN('12d bez Urlaub 2', '12d bez Urlaub 1'), 2: YN('12d bez Urlaub 4', '12d bez Urlaub 3')})
add('PaidLeaveDays', 'Varchar(3)', 'tx', {1: '12c Anzahl Urlaubstage AS', 2: '12c Anzahl Urlaubstage 2E'})
add('PaidLeaveFrom', 'Date', 'date', {1: '12c Urlaub von Jahr AS', 2: '12c Urlaub von Jahr 2E'})
add('PaidLeaveUntil', 'Date', 'date', {1: '12c Urlaub bis Jahr AS', 2: '12c Urlaub bis Jahr 2E'})
add('WeeklyHours', 'Varchar(4)', 'tx', {1: '12c Arbeitszeit AS', 2: '12c Arbeitszeit 2E'})
# 13a / 13b / 13c / 13d
add('SameAccount', 'Varchar(3)', 'st', {2: {'yes': ('13a Konto wie AS', 'Ja')}})
add('BankName', 'Varchar(60)', 'tx', {1: '13a Bankname AS', 2: '13a Bankname 2E'})
add('IBAN', 'Varchar(42)', 'tx', {1: '13a IBAN AS', 2: '13a IBAN 2E'})
add('BIC', 'Varchar(11)', 'tx', {1: '13a BIC AS', 2: '13a BIC 2E'})
ACC = lambda a: opts(('own', f'13a Bankverbindung {a}'), ('other_parent', f'13a Bankverbindung {a + 1}'),
                     ('third', f'13a Bankverbindung {a + 2}'))
add('AccountOwner', 'Varchar(12)', 'st', {1: ACC(1), 2: ACC(4)})
add('HolderFirstName', 'Varchar(60)', 'tx', {1: '13a KtoInhaber Vorname AS', 2: '13a KtoInhaber Vorname 2E'})
add('HolderLastName', 'Varchar(60)', 'tx', {1: '13a KtoInhaber Nachname AS', 2: '13a KtoInhaber Nachname 2E'})
add('Phone', 'Varchar(30)', 'tx', {1: '13b Telefon AS', 2: '13b Telefon 2E'})
add('Email', 'Varchar(80)', 'tx', {1: '13b E-Mail AS', 2: '13b E-Mail 2E'})
add('Remarks', 'Text', 'tx', {1: '13c Weiteres AS', 2: '13c Weiteres 2E'})
add('SignaturePlace', 'Varchar(60)', 'tx', {1: '13c Ort AS'})

# second PDF field driven by the same dropdown value
EXTRA_STATE = {
    1: [('6b Gewinneinkünfte unter 35 Euro AS', 'ProfitIncome', {'below35': 'Ja'})],
    2: [('6b Gewinneinkünfte unter 35 Euro 2E', 'ProfitIncome', {'below35': 'Ja'})],
}
# computed / constant PDF fields of the antrag per role
COMPUTED = {
    1: {'12a Einkünfte 3': 'AfterNone:AfterEmployed:1', '13c Datum AS': 'Document:Number:SignatureDate'},
    2: {'12a Einkünfte 6': 'AfterNone:AfterEmployed:1'},
}

# ------------------------------------------------------------------------------------------------ annexes (both roles)
ABROAD = [
    ('AbroadCountry', 'Varchar(60)', 'tx', '2c Staat'),
    ('AbroadStreet', 'Varchar(80)', 'tx', '2c Str'),
    ('AbroadHouseNumber', 'Varchar(4)', 'tx', '2c Hausnr'),
    ('AbroadPostalCode', 'Varchar(15)', 'tx', '2c PLZ'),
    ('AbroadCity', 'Varchar(60)', 'tx', '2c Ort'),
    ('AbroadAddressExtra', 'Varchar(80)', 'tx', '2c AdrZusatz'),
    ('StayType', 'Varchar(9)', 'st', opts(('temporary', '2cKontrollkästchen1'), ('permanent', '2cKontrollkästchen2'))),
    ('StayFrom', 'Varchar(7)', 'tx', '2c Aufenth befristet von'),
    ('StayUntil', 'Varchar(7)', 'tx', '2c Aufenth befristet bis'),
    ('StaySince', 'Varchar(7)', 'tx', '2c Aufenth unbefristet seit'),
    ('WorksInGermany', 'Varchar(3)', 'st', YN('2cKontrollkästchen3', '2cKontrollkästchen4')),
    ('OtherWorksInGermany', 'Varchar(3)', 'st', YN('2cKontrollkästchen5', '2cKontrollkästchen6')),
    ('EmployerPostalCode', 'Varchar(5)', 'tx', '2c AG D PLZ'),
    ('EmployerCity', 'Varchar(60)', 'tx', '2c AG D Ort'),
]
ABROAD_COMPUTED = {
    'AS Vorname#Wohnsitz': 'Document:FirstName:PersonFirstName',
    'AS Nachname#Wohnsitz': 'Document:LastName:PersonLastName',
    'AS Geburtsdatum#Wohnsitz/Ausland': 'Document:BirthDate:PersonBirthDate',
    'Kind Vorname#Wohnsitz': 'Document:Number:ChildFirstName',
    'Kind Nachname#Wohnsitz': 'Document:Number:ChildLastName',
    'Kind Geburtsdatum#Wohnsitz': 'Document:Number:ChildBirthDate',
}
CITIZENSHIP = [
    ('CitizenshipType', 'Varchar(9)', 'st', opts(('eu', '2gKontrollkästchen6'), ('other', '2gKontrollkästchen9'),
                                                ('stateless', '2gKontrollkästchen10'))),
    ('FreedomRevoked', 'Varchar(3)', 'st', YN('2gKontrollkästchen7', '2gKontrollkästchen8')),
]
CITIZENSHIP_COMPUTED = {
    '2g Staatsangehörigkeit3': 'CountryFor:CitizenshipCountry:eu',
    '2g Staatsangehörigkeit4': 'CountryFor:CitizenshipCountry:other',
    'AS Vorname#Staat2': 'Document:FirstName:PersonFirstName',
    'AS Nachname#Staat2': 'Document:LastName:PersonLastName',
    'AS Geburtsdatum#Staat2': 'Document:BirthDate:PersonBirthDate',
    'Kind Vorname#Staat2': 'Document:Number:ChildFirstName',
    'Kind Nachname#Staat2': 'Document:Number:ChildLastName',
    'Kind Geburtsdatum#Staat2': 'Document:Number:ChildBirthDate',
}
JOB = lambda a, b, c, d: opts(('mini', a), ('midi', b), ('training', c), ('voluntary', d))
INCOME = [
    ('Job1From', 'Date', 'date', '1 erste Tätigkeit von'),
    ('Job1Until', 'Date', 'date', '1 erste Tätigkeit bis'),
    ('Job1Hours', 'Varchar(4)', 'tx', '1 erste Tätigkeit Stunden'),
    ('Job1Type', 'Varchar(9)', 'st', JOB('1Kontrollkästchen1', '1 erste Taetigkeit Midi-Job', '1 erste Taetigkeit Berufsausbildung',
                                         '1 erste Taetigkeit Freiwilligendienst')),
    ('Job2', 'Boolean', None, None),
    ('Job2From', 'Date', 'date', '1 weitere Tätigkeit von'),
    ('Job2Until', 'Date', 'date', '1 weitere Tätigkeit bis'),
    ('Job2Hours', 'Varchar(4)', 'tx', '1 weitere Tätigkeit Stunden'),
    ('Job2Type', 'Varchar(9)', 'st', JOB('1Kontrollkästchen5', '1Kontrollkästchen6', '1Kontrollkästchen7', '1Kontrollkästchen8')),
    ('ProfitSelf', 'Boolean', 'st', {'1': ('2Kontrollkästchen1', 'Ja')}),
    ('ProfitTrade', 'Boolean', 'st', {'1': ('2Kontrollkästchen2', 'Ja')}),
    ('ProfitAgri', 'Boolean', 'st', {'1': ('2Kontrollkästchen3', 'Ja')}),
    ('Profit1From', 'Date', 'date', '2a erste Gewinneinkünfte von'),
    ('Profit1Until', 'Date', 'date', '2a erste Gewinneinkünfte bis'),
    ('Profit1Amount', 'Varchar(12)', 'tx', '2a erste Gewinneinkünfte Betrag'),
    ('Profit1Hours', 'Varchar(4)', 'tx', '2a erste Gewinneinkünfte Stunden'),
    ('Profit2', 'Boolean', None, None),
    ('Profit2From', 'Date', 'date', '2a weitere Gewinneinkünfte von'),
    ('Profit2Until', 'Date', 'date', '2a weitere Gewinneinkünfte bis'),
    ('Profit2Amount', 'Varchar(12)', 'tx', '2a weitere Gewinneinkünfte Betrag'),
    ('Profit2Hours', 'Varchar(4)', 'tx', '2a weitere Gewinneinkünfte Stunden'),
    ('ActualExpenses', 'Boolean', 'st', {'1': ('2a tatsaechliche Betriebsausgaben', 'Ja')}),
    ('AllTaxFree', 'Varchar(3)', 'st', YN('2b steuerfrei ja', '2b steuerfrei nein')),
    ('DaycareChildren', 'Varchar(2)', 'tx', '2c Kinder Tagespflege'),
]
INCOME_COMPUTED = {
    'AS Vorname#Einkünfte': 'Document:FirstName:PersonFirstName',
    'AS Nachname#Einkünfte': 'Document:LastName:PersonLastName',
    'AS Geburtsdatum#Einkünfte': 'Document:BirthDate:PersonBirthDate',
    'Kind Vorname#Einkuenfte': 'Document:Number:ChildFirstName',
    'Kind Nachname#Einkuenfte': 'Document:Number:ChildLastName',
    'Kind Geburtsdatum#Einkuenfte': 'Document:Number:ChildBirthDate',
}
EMPLOYER_COMPUTED = {
    'AS Vorname#ArbG-Bescheinigung': 'Document:FirstName:PersonFirstName',
    'AS Nachname#ArbG-Bescheinigung': 'Document:LastName:PersonLastName',
    'AS Geburtsdatum Tag#ArbG-Bescheinigung': 'Document:BirthDate:PersonBirthDate',
    'AS Str': 'Document:Street:AddressStreet',
    'AS Hausnr': 'Document:HouseNumber:AddressHouseNumber',
    'AS PLZ': 'Document:PostalCode:AddressPostalCode',
    'AS Ort': 'Document:City:AddressCity',
    'AS AdrZusatz': 'Document:AddressExtra:AddressExtra',
    'Kind Vorname#ArbG-Bescheinigung': 'Document:Number:ChildFirstName',
    'Kind Nachname#ArbG-Bescheinigung': 'Document:Number:ChildLastName',
    'Kind Geburtsdatum#ArbG-Bescheinigung': 'Document:Number:ChildBirthDate',
}

# extra parent columns without PDF field of their own
PARENT_EXTRA_DB = [('Number', 'Int'), ('Extended', 'Boolean'), ('CitizenshipCountry', 'Varchar(60)')]

# ------------------------------------------------------------------------------------------------ main submission
MAIN = []
def madd(field, db, kind, pdf):
    MAIN.append((field, db, kind, pdf))

madd('ChildFirstName', 'Varchar(60)', 'tx', '1a Vorname Kind')
madd('ChildLastName', 'Varchar(60)', 'tx', '1a Nachname Kind')
madd('Multiple', 'Boolean', None, None)
madd('ChildrenBorn', 'Varchar(1)', 'tx', '1a Anzahl der Kinder')
madd('ChildBirthDate', 'Date', 'date', '1b Geburtsdatum Kind')
madd('Premature', 'Boolean', 'st', {'1': ('1b Früh geboren', 'Ja')})
madd('DueDate', 'Date', 'date', '1b Errechneter Geburtstermin Jahr')
madd('ChildDisability', 'Boolean', 'st', {'1': ('1b Behinderung', 'Ja')})
madd('Adopted', 'Boolean', None, None)
madd('AdoptionDate', 'Date', 'date', '3c Aufnahmedatum Jahr')
madd('SingleNotTogether', 'Boolean', 'st', {'1': ('2a Nicht zusammenlebend', 'Ja')})
madd('SingleCareImpossible', 'Boolean', 'st', {'1': ('2a unmöglich', 'Ja')})
madd('SingleChildWelfare', 'Boolean', 'st', {'1': ('2a Kindeswohl', 'Ja')})
madd('IncomeLimit', 'Varchar(16)', 'st', opts(('below', '2e Familienstand 26'), ('above', '2e Familienstand 27'),
                                              ('likely_above', '2e Familienstand 28'), ('likely_below', '2e Familienstand 29')))
madd('Applicants', 'Varchar(16)', 'st', opts(('both', '2f Antragstellende 1'), ('me_later', '2f Antragstellende 2'),
                                             ('me_other_applied', '2f Antragstellende 3'), ('only_me', '2f Antragstellende 4')))
madd('OtherChildren', 'Varchar(4)', 'st', opts(('none', '4a Anzahl 1'), ('some', '4a Anzahl 2')))
madd('OtherChildrenCount', 'Varchar(2)', 'tx', '4a weitere Kinder Anzahl')
madd('BonusChildren', 'Varchar(1)', None, None)
for n in (1, 2, 3):
    madd(f'Sibling{n}FirstName', 'Varchar(60)', 'tx', f'4b Vorname{n}')
    madd(f'Sibling{n}LastName', 'Varchar(60)', 'tx', f'4b Nachname{n}')
    madd(f'Sibling{n}BirthDate', 'Date', 'date', f'4b Geburtsdatum{n} Jahr')
    madd(f'Sibling{n}AdoptionDate', 'Date', 'date', f'4b Aufnahmedatum{n} Jahr')
    madd(f'Sibling{n}Disability', 'Boolean', 'st', {'1': (f'4b Behinderung{n}', 'Ja')})
    madd(f'Sibling{n}BiologicalP1', 'Boolean', 'st', {'1': (f'4b Beziehung{n} AS', 'leibliches Kind')})
    madd(f'Sibling{n}BiologicalP2', 'Boolean', 'st', {'1': (f'4b Beziehung{n} 2E', 'leibliches Kind')})
for f, cb, pdf in [('MatStatutory', '9 Anspruch Mutterschaftsgeld', '9 Mutterschaftsgeld KV'),
                   ('MatEmployer', '9 Anspruch Zuschuss AG', '9 Zuschuss AG'),
                   ('MatCivil', '9 Beamtin-Soldatin', '9 Beamtin-Soldatin'),
                   ('MatPrivate', '9 Anspruch Krankentagegeld', '9 Krankentagegeld'),
                   ('MatForeign', '9 Anspruch Ausland', '9 Ausland')]:
    madd(f, 'Boolean', 'st', {'1': (cb, 'Ja')})
    madd(f + 'From', 'Date', 'date', f'{pdf} von Jahr')
    madd(f + 'Until', 'Date', 'date', f'{pdf} bis Jahr')
    if f == 'MatStatutory':
        madd('MatConsent', 'Boolean', 'st', {'1': ('9 Datenabruf Mutterschaftsgeld', 'Ja')})
madd('MatNone', 'Boolean', 'st', {'1': ('9 kein Anspruch', 'Ja')})


# ------------------------------------------------------------------------------------------------ rules
# SubmissionParent::$field_conditions (parents before children)
REASON_SE = ['SE' + f for f, _ in REASONS]
REASON_EMP = ['Emp' + f for f, _ in REASONS]
CALC = {'AmountType': 'calculated'}
PARENT_RULES = [
    # whole-step gates (values from abandoned branches never reach the PDF)
    ([f for f, *_ in ABROAD], {'ResidenceGermany': 'no'}),
    # extended part (2g onwards) only for "Sie" or when both parents apply
    (['Citizenship', 'Nato', 'Diplomatic', 'HealthInsurance', 'Care', 'Relation', 'AmountType',
      *[f'Month{m}' for m in range(1, 33)], 'Leave1From', 'Leave1Until', 'Leave2From', 'Leave2Until', 'PaidLeave',
      'SameAccount', 'BankName', 'IBAN', 'BIC', 'AccountOwner', 'Phone', 'Email', 'Remarks'], {'Extended': '1'}),
    (['ProfitIncome', 'Employed', 'Profit', 'BenUnemployment', 'BenSickness', 'BenSicknessDaily', 'BenPension', 'BenParental',
      'BenOther', 'BenCitizen', 'AfterEmployed', 'AfterProfit', 'AfterUnemployment', 'AfterSickness', 'AfterSicknessDaily',
      'AfterPension', 'AfterParental', 'AfterOther', 'AfterCitizen', 'AfterForeign', 'AfterIntl', 'Retraining'], CALC),
    # 2b / 2c
    (['MarriedToOther'], {'MaritalStatus': 'married'}),
    (['PartneredToOther'], {'MaritalStatus': 'partnership'}),
    (['ResidenceSince', 'SameAddress'], {'ResidenceGermany': 'yes'}),
    (['ResidenceSinceDate'], {'ResidenceSince': 'date'}),
    (['Street', 'HouseNumber', 'PostalCode', 'City', 'AddressExtra'], {'ResidenceGermany': 'yes'}),
    (['ChildStreet', 'ChildHouseNumber', 'ChildPostalCode', 'ChildCity'], {'ChildAddress': 'other'}),
    # Anlage Wohnsitz im Ausland
    (['StayFrom', 'StayUntil'], {'StayType': 'temporary'}),
    (['StaySince'], {'StayType': 'permanent'}),
    (['OtherWorksInGermany'], {'WorksInGermany': 'no'}),
    # 2g / 2i / 3b
    (['CitizenshipType'], {'Citizenship': 'other'}),
    (['CitizenshipCountry'], {'CitizenshipType': ['eu', 'other']}),
    (['FreedomRevoked'], {'CitizenshipType': 'eu'}),
    (['InsuranceNumber', 'SameInsurer'], {'HealthInsurance': ['statutory', 'voluntary']}),
    (['InsurerName', 'InsurerCity'], {'HealthInsurance': ['statutory', 'voluntary']}),
    (['RelativeText'], {'Relation': 'relative'}),
    # 6b / 6c
    ([*REASON_SE, 'ShiftRequest'], {'ProfitIncome': 'taxable'}),
    (['ShiftYear'], {'ShiftRequest': '1'}),
    ([*REASON_EMP, 'IncludeRequest', 'Corona', 'ChangedProfit'], {'ProfitIncome': ['none', 'taxfree', 'below35']}),
    (['Include1From', 'Include1Until', 'Include2From', 'Include2Until'], {'IncludeRequest': '1'}),
    (['Corona1From', 'Corona1Until', 'Corona2From', 'Corona2Until'], {'Corona': '1'}),
    # 7a / 7b / 8
    (['MiniJob', 'MidiJob', 'Training', 'Voluntary'], {'Employed': '1'}),
    (['Trade', 'SelfEmployedWork', 'Agriculture', 'ChurchTax', 'HealthContrib', 'PensionContrib', 'UnemploymentContrib',
      'NoContrib'], {'Profit': '1'}),
    (['PensionType1', 'PensionType2'], {'BenPension': '1'}),
    (['OtherType1', 'OtherType2'], {'BenOther': '1'}),
    # 12a / 12b / 12d
    (['EmployerCertificate', 'Job1From', 'Job1Until', 'Job1Hours', 'Job1Type', 'Job2'], {'AfterEmployed': '1'}),
    (['Job2From', 'Job2Until', 'Job2Hours', 'Job2Type'], {'Job2': '1'}),
    (['ProfitSelf', 'ProfitTrade', 'ProfitAgri', 'Profit1From', 'Profit1Until', 'Profit1Amount', 'Profit1Hours', 'Profit2',
      'ActualExpenses', 'AllTaxFree', 'DaycareChildren'], {'AfterProfit': '1'}),
    (['Profit2From', 'Profit2Until', 'Profit2Amount', 'Profit2Hours'], {'Profit2': '1'}),
    (['AfterPensionType1', 'AfterPensionType2'], {'AfterPension': '1'}),
    (['AfterOtherType1', 'AfterOtherType2'], {'AfterOther': '1'}),
    (['PaidLeaveDays', 'PaidLeaveFrom', 'PaidLeaveUntil', 'WeeklyHours'], {'PaidLeave': 'yes'}),
    # 13a
    (['HolderFirstName', 'HolderLastName'], {'AccountOwner': 'third'}),
]
# extra rules for the other parent only (fields that exist in column 2E only)
PARENT_ROLE_RULES = {
    'p1': [],
    'p2': [
        (['Street', 'HouseNumber', 'PostalCode', 'City', 'AddressExtra'], {'SameAddress': 'no'}),
        (['InsurerName', 'InsurerCity'], {'SameInsurer': 'no'}),
        (['BankName', 'IBAN', 'BIC', 'AccountOwner'], {'SameAccount': 'no'}),
    ],
}
MAIN_RULES = [
    (['ChildrenBorn'], {'Multiple': '1'}),
    (['DueDate'], {'Premature': '1'}),
    (['AdoptionDate'], {'Adopted': '1'}),
    (['OtherChildrenCount', 'BonusChildren'], {'OtherChildren': 'some'}),
]
for n in (1, 2, 3):
    MAIN_RULES.append(([f'Sibling{n}{f}' for f in ('FirstName', 'LastName', 'BirthDate', 'AdoptionDate', 'Disability',
                                                   'BiologicalP1', 'BiologicalP2')], {'BonusChildren': [str(v) for v in range(n, 4)]}))
for f in ('MatStatutory', 'MatEmployer', 'MatCivil', 'MatPrivate', 'MatForeign'):
    extra = ['MatConsent'] if f == 'MatStatutory' else []
    MAIN_RULES.append(([f + 'From', f + 'Until', *extra], {f: '1'}))


# ------------------------------------------------------------------------------------------------ emit
def q(s):
    return "'" + str(s).replace('\\', '\\\\').replace("'", "\\'") + "'"


def emit_db(items, extra=()):
    out = []
    for f, db in extra:
        out.append(f'        {q(f)} => {q(db)},')
    for f, db, *_ in items:
        out.append(f'        {q(f)} => {q(db)},')
    return '\n'.join(out)


def mapping_for(items, role=None):
    mp, sm = {}, {}
    for f, db, kind, m in items:
        if kind is None:
            continue
        spec = m.get(role) if role is not None else m
        if spec is None:
            continue
        if kind == 'tx':
            mp[spec] = f
        elif kind == 'date':
            mp[spec] = f'DateFormating:{f}:1'
        elif kind == 'lines':  # one value over several single-line PDF fields (word wrap, see LineOf())
            for i, pdf in enumerate(spec, 1):
                mp[pdf] = f'LineOf:{f}:{i}'
        elif kind == 'st':
            for value, (pdf, state) in spec.items():
                mp[pdf] = f'StateValue:{f}:{pdf}'
                sm.setdefault(pdf, {})[value] = state
    return mp, sm


def emit_map(mp, indent=8):
    pad = ' ' * indent
    return '\n'.join(f'{pad}{q(k)} => {q(v)},' for k, v in mp.items())


def emit_state(sm):
    out = []
    for pdf, vals in sm.items():
        inner = ', '.join(f'{q(v)} => {q(s)}' for v, s in vals.items())
        out.append(f'        {q(pdf)} => [{inner}],')
    return '\n'.join(out)


def emit_rules(rules):
    out = []
    for fields, cond in rules:
        fs = ', '.join(q(f) for f in fields)
        cs = ', '.join(f'{q(k)} => ' + ('[' + ', '.join(q(x) for x in v) + ']' if isinstance(v, list) else q(v)) for k, v in cond.items())
        out.append(f'        [[{fs}], [{cs}]],')
    return '\n'.join(out)


# parent
annex_items = [(f, db, kind, m) for f, db, kind, m in ABROAD + CITIZENSHIP + INCOME]
db_parent = emit_db(PARENT + annex_items, PARENT_EXTRA_DB)
state = {}
maps = {}
for role in (1, 2):
    mp, sm = mapping_for(PARENT, role)
    for pdf, field, vals in EXTRA_STATE[role]:
        mp[pdf] = f'StateValue:{field}:{pdf}'
        sm[pdf] = vals
    mp.update(COMPUTED[role])
    maps[role] = mp
    state.update(sm)
annex_maps = {}
for key, items, comp in (('abroad', ABROAD, ABROAD_COMPUTED), ('citizenship', CITIZENSHIP, CITIZENSHIP_COMPUTED),
                         ('income', INCOME, INCOME_COMPUTED), ('employer', [], EMPLOYER_COMPUTED)):
    mp, sm = mapping_for(items)
    mp.update(comp)
    annex_maps[key] = mp
    state.update(sm)

parent_php = f"""    // GENERATED by gen.py - do not edit by hand
    private static array $db = [
{db_parent}
    ];

    /**
     * Antrag (pages 1-23): one column per parent. Number 1 = "Fragen an Sie" (AS), 2 = "an den anderen Elternteil" (2E).
     *
     * @var array<string, array<string, string>>
     */
    private static array $antrag_field_mapping = [
        'p1' => [
{emit_map(maps[1], 12)}
        ],
        'p2' => [
{emit_map(maps[2], 12)}
        ],
    ];

    /** Anlage Wohnsitz im Ausland (pages 29-30). */
    private static array $abroad_field_mapping = [
{emit_map(annex_maps['abroad'])}
    ];

    /** Anlage Staatsangehörigkeit (page 32; page 31 is an identical copy and stays unused). */
    private static array $citizenship_field_mapping = [
{emit_map(annex_maps['citizenship'])}
    ];

    /** Anlage Einkünfte neben dem Elterngeld (pages 33-35). */
    private static array $income_field_mapping = [
{emit_map(annex_maps['income'])}
    ];

    /** Anlage Arbeitgeberbescheinigung (pages 25-28): header only, the employer fills in the rest. */
    private static array $employer_field_mapping = [
{emit_map(annex_maps['employer'])}
    ];

    /** PDF checkbox / radio field => [stored value => on-state]. */
    private static array $state_map = [
{emit_state(state)}
    ];

    /**
     * Dependency rules (FieldConditions), parents before children. Mirrors the display-logic of the steps;
     * the first three rules are whole-step gates (Form_Abroad*, extended part, Mindestbetrag).
     */
    private static array $field_conditions = [
{emit_rules(PARENT_RULES)}
    ];

    /** Additional rules per parent column (appended after $field_conditions). */
    private static array $role_field_conditions = [
        'p1' => [],
        'p2' => [
{emit_rules(PARENT_ROLE_RULES['p2']).replace(chr(10) + '        ', chr(10) + '            ').replace('        [[', '            [[', 1)}
        ],
    ];
    // END GENERATED
"""

mp_main, sm_main = mapping_for(MAIN)
main_php = f"""    // GENERATED by gen.py - do not edit by hand
    private static array $db = [
{emit_db(MAIN)}
    ];

    /** Antrag pages 1-23, fields of the main record (parent columns: SubmissionParent::$antrag_field_mapping). */
    private static array $field_mapping = [
{emit_map(mp_main)}
    ];

    /** PDF checkbox / radio field => [stored value => on-state]. */
    private static array $state_map = [
{emit_state(sm_main)}
    ];

    /** Dependency rules (FieldConditions), parents before children. */
    private static array $field_conditions = [
{emit_rules(MAIN_RULES)}
    ];
    // END GENERATED
"""


def splice(path, block):
    src = open(path).read()
    new = re.sub(r'    // GENERATED by gen\.py.*?    // END GENERATED\n', lambda _: block, src, flags=re.S)
    assert new != src or block in src, f'no GENERATED markers in {path}'
    open(path, 'w').write(new)


splice(f'{MOD}/src/Model/SubmissionParent.php', parent_php)
splice(f'{MOD}/src/Model/Submission.php', main_php)

# inventory helpers for the harness: role -> pdf fields
import json
json.dump({'p1': list(maps[1]), 'p2': list(maps[2]), **{k: list(v) for k, v in annex_maps.items()}, 'main': list(mp_main)},
          open('mapped_fields.json', 'w'), ensure_ascii=False, indent=1)
print('parent fields', len(PARENT) + len(annex_items) + len(PARENT_EXTRA_DB), 'main fields', len(MAIN),
      'p1', len(maps[1]), 'p2', len(maps[2]), {k: len(v) for k, v in annex_maps.items()}, 'main map', len(mp_main))
