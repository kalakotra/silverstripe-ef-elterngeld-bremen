<?php

declare(strict_types=1);

namespace Kalakotra\EFormElterngeldBremen\Forms;

use app\forms\basemodel\BaseMultiForm;
use app\models\FormSubmission;
use Kalakotra\EFormElterngeldBremen\Model\FieldConditions;
use Kalakotra\EFormElterngeldBremen\Model\Submission;
use Kalakotra\EFormElterngeldBremen\Model\SubmissionParent;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\SingleSelectField;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\MultiForm\Models\MultiFormStep;
use SilverStripe\ORM\DB;

/**
 * Antrag auf Elterngeld für Geburten ab dem 01.04.2025 (Bremen, Amt für Soziale Dienste, Antragsversion August 2025)
 * with the Anlagen Wohnsitz im Ausland, Staatsangehörigkeit, Einkünfte neben dem Elterngeld and
 * Arbeitgeberbescheinigung.
 */
class Form extends BaseMultiForm
{
    private static $start_step = Form_Child::class;

    public static function getFormName(): string
    {
        return 'Elterngeld (Bremen, Geburten ab 01.04.2025, mit Anlagen)';
    }

    public function finish($data, $form)
    {
        if (parent::finish($data, $form) === false) {
            return false;
        }

        $allData = $this->mergeStepData($this->getActiveSteps());
        if ($allData === []) {
            return $this->controller->redirectBack();
        }

        $connection = DB::get_conn();

        try {
            $connection->transactionStart();

            $submission = self::buildSubmission($allData);

            $formSubmission = FormSubmission::create();
            $formSubmission->Email = $submission->ConfirmationEmail;
            $formSubmission->SubmissionID = $submission->ID;
            $formSubmission->Status = 0; // pending
            $formSubmission->ParentPageID = $this->controller->data()->ID;
            $formSubmission->write();

            $connection->transactionEnd();

            $this->controller->getRequest()->getSession()->set('FormSubmissionID', $formSubmission->ID);
        } catch (\Throwable $e) {
            $connection->transactionRollback();
            Injector::inst()->get(LoggerInterface::class)->error(
                'Elterngeld submission failed: ' . $e->getMessage(),
                ['exception' => $e]
            );
            $form->sessionError(_t(__CLASS__ . '.SAVE_ERROR', 'Es gab ein Problem beim Speichern Ihrer Daten. Bitte versuchen Sie es erneut.'));

            return $this->controller->redirectBack();
        }

        return $this->controller->redirect($this->controller->Link('/finished'));
    }

    /**
     * Main submission + one SubmissionParent per column ("P1" = Sie, "P2" = anderer Elternteil), from the merged data
     * of the ACTIVE steps. No P2 record for single parents (2.a); P2 answers 2.g onwards only with 2.f "Beide".
     *
     * @param array<string, mixed> $allData
     */
    public static function buildSubmission(array $allData): Submission
    {
        $main = [];
        foreach ($allData as $field => $value) {
            if (preg_match('/^P[12][A-Z]/', (string) $field)) {
                continue;
            }
            $main[(string) $field] = $value;
        }

        $submission = Submission::create();
        $submission->update($main);
        $submission->write();

        foreach (self::parentColumns($submission) as $number => $extended) {
            $record = SubmissionParent::fromStepData(
                $number,
                FieldConditions::unprefix($allData, SubmissionParent::PREFIXES[$number]),
                $extended
            );
            $record->SubmissionID = $submission->ID;
            $record->write();
        }

        return $submission;
    }

    /**
     * @return array<int, bool> parent number => Extended (questions from 2.g onwards)
     */
    public static function parentColumns(Submission $submission): array
    {
        if ($submission->isSingleParent()) {
            return [1 => true];
        }

        return [1 => true, 2 => $submission->bothApply()];
    }

    /**
     * Preview only shows steps on the currently active path (e.g. the other parent disappears for single parents).
     */
    public function PreviewSteps(): ArrayList
    {
        $activeClasses = array_map(
            static fn (MultiFormStep $step): string => (string) $step->ClassName,
            $this->getActiveSteps()
        );

        return parent::PreviewSteps()->filterByCallback(
            static fn ($item): bool => in_array((string) $item->ClassName, $activeClasses, true)
        );
    }

    /**
     * Labels come from the step's own field titles (already translated for the current locale).
     * Required for the parent steps: their labels are trait keys, so the base lookup
     * "<StepClass>.<Field>" would find nothing.
     */
    public function resolveFieldLabel(string $stepClass, string $fieldKey): string
    {
        return $this->stepMeta($stepClass)['titles'][$fieldKey] ?? parent::resolveFieldLabel($stepClass, $fieldKey);
    }

    public function resolveStepTitle(MultiFormStep $step): string
    {
        return $this->stepMeta((string) $step->ClassName, $step)['titles']['IntroText']
            ?? parent::resolveStepTitle($step);
    }

    /**
     * Type-aware preview values based on the $db of the model that stores the field
     * (SubmissionParent for "P1*" / "P2*", Submission otherwise).
     */
    public function formatPreviewValue(string $stepClass, string $key, $value, ?MultiFormStep $step = null): string
    {
        $type = self::fieldType($key);

        if ($type === 'Date' && is_string($value) && $value !== '') {
            return $this->DateFormatting($value);
        }

        if ($type === 'Boolean') {
            if ($value === null || $value === '' || !$value) {
                return ''; // unchecked or cleared because hidden -> skipped in preview
            }

            return _t(__CLASS__ . '.BOOLEAN_YES', 'Ja');
        }

        // option label straight from the field source: translated if a lang file exists,
        // German default otherwise (base only resolves options that exist in a lang file)
        if (is_scalar($value) && $value !== '') {
            $label = $this->stepMeta($stepClass, $step)['options'][$key][(string) $value] ?? null;
            if ($label !== null) {
                return $label;
            }
        }

        return parent::formatPreviewValue($stepClass, $key, $value, $step);
    }

    /**
     * $db type of a step field in the model that stores it.
     */
    public static function fieldType(string $key): ?string
    {
        if (preg_match('/^P[12]([A-Z].*)$/', $key, $m)) {
            return SubmissionParent::config()->get('db')[$m[1]] ?? null;
        }

        return Submission::config()->get('db')[$key] ?? null;
    }

    /** @var array<string, array{titles: array<string, string>, options: array<string, array<string, string>>}> */
    private array $stepMetaCache = [];

    /**
     * Field titles and dropdown sources of a step class.
     *
     * @return array{titles: array<string, string>, options: array<string, array<string, string>>}
     */
    private function stepMeta(string $stepClass, ?MultiFormStep $step = null): array
    {
        if (isset($this->stepMetaCache[$stepClass])) {
            return $this->stepMetaCache[$stepClass];
        }

        $meta = ['titles' => [], 'options' => []];
        $instance = $step ?? (is_a($stepClass, MultiFormStep::class, true) ? $stepClass::singleton() : null);

        if ($instance) {
            foreach ($instance->getFields()->flattenFields() as $field) {
                if (!$field instanceof FormField) {
                    continue;
                }

                $title = trim(strip_tags((string) $field->Title()));
                if ($title !== '') {
                    $meta['titles'][$field->getName()] = $title;
                }

                if ($field instanceof SingleSelectField) {
                    $meta['options'][$field->getName()] = array_map('strval', $field->getSource());
                }
            }
        }

        return $this->stepMetaCache[$stepClass] = $meta;
    }

    /**
     * Walks the step chain from the start step using each step's getNextStep(),
     * so branches the user abandoned (but which still exist in the session) are excluded.
     *
     * @return array<int, MultiFormStep>
     */
    public function getActiveSteps(): array
    {
        $stepsByClass = [];
        $steps = MultiFormStep::get()->filter('SessionID', $this->getMultiFormSession()->ID);

        foreach ($steps as $step) {
            $step->setForm($this);
            $stepsByClass[$step->ClassName] = $step;
        }

        $active = [];
        $class = $this->config()->get('start_step');

        while (is_string($class) && isset($stepsByClass[$class]) && !isset($active[$class])) {
            $step = $stepsByClass[$class];
            $active[$class] = $step;

            if ($step->isFinalStep()) {
                break;
            }

            $class = $step->getNextStep();
        }

        return array_values($active);
    }
}
