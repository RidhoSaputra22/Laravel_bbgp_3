<?php

namespace App\Observers;

use App\Jobs\SyncValidatorAssignmentsToMongoJob;
use App\Models\ValidatorAssignment;
use App\Models\ValidatorFormSection;

class ValidatorFormSectionObserver
{
    public function saved(ValidatorFormSection $section): void
    {
        $this->dispatchForForm($section->validator_form_id);
    }

    public function deleted(ValidatorFormSection $section): void
    {
        $this->dispatchForForm($section->validator_form_id);
    }

    private function dispatchForForm(?int $formId): void
    {
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
