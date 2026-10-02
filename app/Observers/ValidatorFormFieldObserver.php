<?php

namespace App\Observers;

use App\Jobs\SyncValidatorAssignmentsToMongoJob;
use App\Models\ValidatorAssignment;
use App\Models\ValidatorFormField;
use App\Models\ValidatorFormSection;

class ValidatorFormFieldObserver
{
    public function saved(ValidatorFormField $field): void
    {
        $this->dispatchForSection($field->validator_form_section_id);
    }

    public function deleted(ValidatorFormField $field): void
    {
        $this->dispatchForSection($field->validator_form_section_id);
    }

    private function dispatchForSection(?int $sectionId): void
    {
        if (! $sectionId) {
            return;
        }

        $formId = ValidatorFormSection::query()
            ->whereKey($sectionId)
            ->value('validator_form_id');

        if (! $formId) {
            return;
        }

        SyncValidatorAssignmentsToMongoJob::dispatchIds(
            ValidatorAssignment::query()
                ->where('validator_form_id', $formId)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all()
        );
    }
}
