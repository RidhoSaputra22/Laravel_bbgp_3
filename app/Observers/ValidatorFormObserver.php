<?php

namespace App\Observers;

use App\Jobs\SyncValidatorAssignmentsToMongoJob;
use App\Models\ValidatorAssignment;
use App\Models\ValidatorForm;

class ValidatorFormObserver
{
    public function saved(ValidatorForm $form): void
    {
        $this->dispatchForForm($form);
    }

    public function deleted(ValidatorForm $form): void
    {
        $this->dispatchForForm($form);
    }

    private function dispatchForForm(ValidatorForm $form): void
    {
        SyncValidatorAssignmentsToMongoJob::dispatchIds(
            ValidatorAssignment::query()
                ->where('validator_form_id', $form->getKey())
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all()
        );
    }
}
