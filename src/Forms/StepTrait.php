<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use Kalakotra\EFormElterngeldBremen\Model\FieldConditions;
use Kalakotra\EFormElterngeldBremen\Model\Submission;
use Kalakotra\EFormElterngeldBremen\Model\SubmissionParent;
use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Behaviour shared by all Elterngeld steps (trait instead of an abstract base class,
 * because DbBuild instantiates every DataObject subclass via singleton()).
 *
 * - saveData(): clears values of fields hidden by display-logic
 * - validateStep(): enforces `conditional_required` fields only while they are visible
 * - getNextStep(): one flow table for the whole form (FLOW + isFlowStepActive gates)
 *
 * Parent steps declare PERSON (1 = "Sie", 2 = anderer Elternteil); their field names are "P{n}<SubmissionParent field>"
 * and their rules are SubmissionParent::rulesFor(n) with that prefix.
 * Using classes may declare: private static array $conditional_required = ['FieldName', ...];
 */
trait StepTrait
{
    /**
     * Step order of the complete form. Inactive steps (gates in isFlowStepActive()) are skipped.
     */
    private const FLOW = [
        Form_Child::class,
        Form_Family::class,
        Form_Person1::class,
        Form_Abroad1::class,
        Form_Person2::class,
        Form_Abroad2::class,
        Form_Status1::class,
        Form_Status2::class,
        Form_Siblings::class,
        Form_Assessment1::class,
        Form_Earnings1::class,
        Form_Assessment2::class,
        Form_Earnings2::class,
        Form_Maternity::class,
        Form_Months1::class,
        Form_Months2::class,
        Form_Leave1::class,
        Form_IncomeAfter1::class,
        Form_AnnexJobs1::class,
        Form_AnnexProfit1::class,
        Form_Leave2::class,
        Form_IncomeAfter2::class,
        Form_AnnexJobs2::class,
        Form_AnnexProfit2::class,
        Form_Bank1::class,
        Form_Bank2::class,
        Form_Preview::class,
    ];

    /** Steps of the other parent that are answered even without "Beide Elternteile" (2.b - 2.e). */
    private const P2_BASIC = [Form_Person2::class, Form_Abroad2::class];

    /**
     * @param array<string, mixed>|mixed $data
     */
    public function saveData($data)
    {
        if (is_array($data)) {
            $data = FieldConditions::apply($data, static::fieldConditionRules(), true);
        }

        return parent::saveData($data);
    }

    /**
     * @param array<string, mixed>|mixed $data
     */
    public function validateStep($data, $form)
    {
        return $this->validateConditionalRequired($data, $form) && parent::validateStep($data, $form);
    }

    public function getNextStep()
    {
        $index = array_search(static::class, self::FLOW, true);
        if ($index === false) {
            return false;
        }

        foreach (array_slice(self::FLOW, $index + 1) as $class) {
            if ($this->isFlowStepActive($class)) {
                return $class;
            }
        }

        return false;
    }

    /**
     * Gates of the flow. Values are read from the step that owns them (own data via loadData(),
     * the cross-step cache of multiform could be stale right after saveData()).
     */
    protected function isFlowStepActive(string $class): bool
    {
        $person = defined($class . '::PERSON') ? (int) constant($class . '::PERSON') : 0;

        if ($person === 2) {
            if ($this->isSingleParent()) {
                return false;
            }
            if (!in_array($class, self::P2_BASIC, true) && $this->flowValue(Form_Family::class, 'Applicants') !== 'both') {
                return false;
            }
        }

        $p = 'P' . $person;

        return match ($class) {
            Form_Abroad1::class, Form_Abroad2::class
                => $this->flowValue($person === 1 ? Form_Person1::class : Form_Person2::class, $p . 'ResidenceGermany') === 'no',
            Form_Earnings1::class, Form_Earnings2::class, Form_IncomeAfter1::class, Form_IncomeAfter2::class
                => $this->isCalculated($person),
            Form_AnnexJobs1::class, Form_AnnexJobs2::class
                => $this->isCalculated($person) && (bool) $this->flowValue($this->incomeAfterStep($person), $p . 'AfterEmployed'),
            Form_AnnexProfit1::class, Form_AnnexProfit2::class
                => $this->isCalculated($person) && (bool) $this->flowValue($this->incomeAfterStep($person), $p . 'AfterProfit'),
            default => true,
        };
    }

    protected function isSingleParent(): bool
    {
        foreach (['SingleNotTogether', 'SingleCareImpossible', 'SingleChildWelfare'] as $field) {
            if ($this->flowValue(Form_Family::class, $field)) {
                return true;
            }
        }

        return false;
    }

    /** 5: Elterngeld based on income (not only the Mindestbetrag) -> 6.-8. and 12.a-c apply. */
    protected function isCalculated(int $person): bool
    {
        $step = $person === 1 ? Form_Assessment1::class : Form_Assessment2::class;

        return $this->flowValue($step, 'P' . $person . 'AmountType') === 'calculated';
    }

    private function incomeAfterStep(int $person): string
    {
        return $person === 1 ? Form_IncomeAfter1::class : Form_IncomeAfter2::class;
    }

    protected function flowValue(string $stepClass, string $fieldName): mixed
    {
        if ($stepClass === static::class) {
            $data = $this->loadData();

            return is_array($data) ? ($data[$fieldName] ?? null) : null;
        }

        return $this->valueFromStep($stepClass, $fieldName);
    }

    /**
     * Reads a value saved by another step of the same session.
     * Filters by exact ClassName: a DataList on a step class would also return its subclasses.
     */
    protected function valueFromStep(string $stepClass, string $fieldName): mixed
    {
        if (!$this->SessionID) {
            return null;
        }

        $step = MultiFormStep::get()
            ->filter(['SessionID' => $this->SessionID, 'ClassName' => $stepClass])
            ->first();

        if (!$step) {
            return null;
        }

        $data = $step->loadData();

        return is_array($data) ? ($data[$fieldName] ?? null) : null;
    }

    /**
     * Steps overriding validateStep() call this to keep the conditional-required check.
     *
     * @param array<string, mixed>|mixed $data
     */
    protected function validateConditionalRequired($data, $form): bool
    {
        $required = (array) ($this->config()->get('conditional_required') ?? []);
        if ($required === []) {
            return true;
        }

        // Use the form's normalised data, NOT the raw request ($data): unchecked checkboxes are
        // missing from the request, which made their dependent fields look active (and the
        // resulting error was attached to a hidden field -> silent redirect back to the same step).
        $data = $form->getData();
        $rules = static::fieldConditionRules();
        $missing = [];

        foreach ($required as $fieldName) {
            if (!FieldConditions::isActive($fieldName, $data, $rules)) {
                continue;
            }

            $value = $data[$fieldName] ?? null;
            if (is_scalar($value) && trim((string) $value) !== '') {
                continue;
            }

            $this->addFieldError($form, $fieldName, $missing);
        }

        $this->addRequiredSummary($form, $missing);

        return $missing === [];
    }

    /**
     * Pattern checks for optional or required text fields (empty values are left to the required checks).
     *
     * @param array<string, string> $patterns field => regex
     */
    protected function validatePatterns($form, array $patterns, string $message): bool
    {
        $data = $form->getData();
        $valid = true;

        foreach ($patterns as $fieldName => $pattern) {
            $value = trim((string) ($data[$fieldName] ?? ''));
            if ($value === '' || preg_match($pattern, $value) === 1) {
                continue;
            }

            $valid = false;
            $form->sessionFieldError($message, $fieldName);
        }

        if (!$valid) {
            $form->sessionMessage(_t(__TRAIT__ . '.CHECK_SUMMARY', 'Bitte prüfen Sie die markierten Angaben.'));
        }

        return $valid;
    }

    /**
     * Month/year inputs (MM.JJJJ).
     *
     * @param array<int, string> $fieldNames
     */
    protected function validateMonthFields($form, array $fieldNames): bool
    {
        return $this->validatePatterns(
            $form,
            array_fill_keys($fieldNames, '/^(0[1-9]|1[0-2])\.(19|20)\d\d$/'),
            _t(__TRAIT__ . '.MONTH_INVALID', 'Bitte im Format MM.JJJJ angeben, z. B. 03.2025.')
        );
    }

    /**
     * @param array<int, string> $missing
     */
    protected function addFieldError($form, string $fieldName, array &$missing): void
    {
        $field = $form->Fields()->dataFieldByName($fieldName);
        $label = $field ? strip_tags((string) $field->Title()) : $fieldName;
        $missing[] = $label;

        $form->sessionFieldError(
            _t(__TRAIT__ . '.REQUIRED', '"{name}" ist erforderlich.', ['name' => $label]),
            $fieldName
        );
    }

    /**
     * Form-level summary, so a failed validation is never invisible.
     *
     * @param array<int, string> $missing
     */
    protected function addRequiredSummary($form, array $missing): void
    {
        if ($missing === []) {
            return;
        }

        $form->sessionMessage(_t(
            __TRAIT__ . '.REQUIRED_SUMMARY',
            'Bitte füllen Sie die folgenden Pflichtfelder aus: {fields}',
            ['fields' => implode(', ', $missing)]
        ));
    }

    /**
     * Rules of the main Submission plus, for parent steps (PERSON), the rules of that column with prefixed field names.
     *
     * @return array<int, array<int, mixed>>
     */
    public static function fieldConditionRules(): array
    {
        $rules = (array) Submission::config()->get('field_conditions');
        $personConstant = static::class . '::PERSON';

        if (!defined($personConstant)) {
            return $rules;
        }

        $person = (int) constant($personConstant);

        return array_merge($rules, FieldConditions::prefix(
            SubmissionParent::rulesFor($person),
            SubmissionParent::PREFIXES[$person]
        ));
    }
}
