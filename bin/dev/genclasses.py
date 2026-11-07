import sys
M = sys.argv[1] + '/src/Forms/'
# (base, trait, has_validate, sort1, sort2, intro1, intro2, cr1, cr2)
STEPS = [
 ('Person', 'PersonStepTrait', True, 3, 5, 'Angaben zu Ihnen', 'Angaben zum anderen Elternteil',
  ['MarriedToOther', 'PartneredToOther', 'ResidenceSince', 'ResidenceSinceDate', 'Street', 'HouseNumber', 'PostalCode', 'City',
   'ChildStreet', 'ChildHouseNumber', 'ChildPostalCode', 'ChildCity'],
  ['ResidenceSince', 'ResidenceSinceDate', 'SameAddress', 'Street', 'HouseNumber', 'PostalCode', 'City']),
 ('Abroad', 'AbroadStepTrait', True, 4, 6, 'Anlage Wohnsitz im Ausland: Sie', 'Anlage Wohnsitz im Ausland: anderer Elternteil',
  ['StayFrom', 'StayUntil', 'StaySince', 'OtherWorksInGermany'], ['StayFrom', 'StayUntil', 'StaySince', 'OtherWorksInGermany']),
 ('Status', 'StatusStepTrait', True, 7, 8, 'Staatsangehörigkeit, Krankenversicherung und Beziehung zum Kind: Sie',
  'Staatsangehörigkeit, Krankenversicherung und Beziehung zum Kind: anderer Elternteil',
  ['CitizenshipType', 'CitizenshipCountry', 'FreedomRevoked', 'InsurerName', 'InsurerCity', 'RelativeText'],
  ['CitizenshipType', 'CitizenshipCountry', 'FreedomRevoked', 'SameInsurer', 'InsurerName', 'InsurerCity', 'RelativeText']),
 ('Assessment', 'AssessmentStepTrait', True, 10, 12, 'Berechnung und Bemessungszeitraum: Sie', 'Berechnung und Bemessungszeitraum: anderer Elternteil',
  ['ProfitIncome', 'ShiftYear', 'Include1From', 'Include1Until', 'Corona1From', 'Corona1Until'],
  ['ProfitIncome', 'ShiftYear', 'Include1From', 'Include1Until', 'Corona1From', 'Corona1Until']),
 ('Earnings', 'EarningsStepTrait', True, 11, 13, 'Einkommen vor der Geburt: Sie', 'Einkommen vor der Geburt: anderer Elternteil',
  ['PensionType1', 'OtherType1'], ['PensionType1', 'OtherType1']),
 ('Months', 'MonthsStepTrait', True, 15, 16, 'Planung der Elterngeld-Monate: Sie', 'Planung der Elterngeld-Monate: anderer Elternteil', [], []),
 ('Leave', 'LeaveStepTrait', True, 17, 21, 'Elternzeit und Urlaub: Sie', 'Elternzeit und Urlaub: anderer Elternteil',
  ['PaidLeaveDays', 'PaidLeaveFrom', 'PaidLeaveUntil', 'WeeklyHours'], ['PaidLeaveDays', 'PaidLeaveFrom', 'PaidLeaveUntil', 'WeeklyHours']),
 ('IncomeAfter', 'IncomeAfterStepTrait', False, 18, 22, 'Einkünfte neben dem Elterngeld: Sie', 'Einkünfte neben dem Elterngeld: anderer Elternteil',
  ['AfterPensionType1', 'AfterOtherType1'], ['AfterPensionType1', 'AfterOtherType1']),
 ('AnnexJobs', 'AnnexJobsStepTrait', True, 19, 23, 'Anlage Einkünfte: nicht-selbstständige Arbeit (Sie)',
  'Anlage Einkünfte: nicht-selbstständige Arbeit (anderer Elternteil)', ['Job2From', 'Job2Until', 'Job2Hours'], ['Job2From', 'Job2Until', 'Job2Hours']),
 ('AnnexProfit', 'AnnexProfitStepTrait', True, 20, 24, 'Anlage Einkünfte: Selbstständigkeit (Sie)', 'Anlage Einkünfte: Selbstständigkeit (anderer Elternteil)',
  ['Profit2From', 'Profit2Until', 'Profit2Amount', 'Profit2Hours'], ['Profit2From', 'Profit2Until', 'Profit2Amount', 'Profit2Hours']),
 ('Bank', 'BankStepTrait', True, 25, 26, 'Bankverbindung, Kontakt und Unterschrift: Sie', 'Bankverbindung und Kontakt: anderer Elternteil',
  ['HolderFirstName', 'HolderLastName'], ['BankName', 'IBAN', 'AccountOwner', 'HolderFirstName', 'HolderLastName']),
]
for base, trait, hv, s1, s2, i1, i2, c1, c2 in STEPS:
    for n, sort, intro, cr in ((1, s1, i1, c1), (2, s2, i2, c2)):
        cls = f'Form_{base}{n}'
        use = f'    use StepTrait, {trait} {{\n        {trait}::validateStep insteadof StepTrait;\n    }}' if hv else f'    use StepTrait;\n    use {trait};'
        crs = ''.join(f"        'P{n}{f}',\n" for f in cr)
        crblock = f"\n    private static array $conditional_required = [\n{crs}    ];\n" if cr else ''
        who = '"Fragen an Sie"' if n == 1 else '"Fragen an den anderen Elternteil"'
        open(M + cls + '.php', 'w').write(f"""<?php

declare(strict_types=1);

namespace Kalakotra\\EFormElterngeldBremen\\Forms;

use SilverStripe\\MultiForm\\Models\\MultiFormStep;

/**
 * Column {who} (see {trait}).
 */
class {cls} extends MultiFormStep
{{
{use}

    public const PERSON = {n};

    public const FIELD_PREFIX = 'P{n}';

    private static $default_sort_order = {sort};
{crblock}
    protected function introText(): string
    {{
        return _t(__CLASS__ . '.IntroText', '{intro}');
    }}
}}
""")
print('ok')
