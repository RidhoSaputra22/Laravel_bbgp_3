<?php

namespace App\Providers;

use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentAttempt;
use App\Models\Guru;
use App\Models\ValidatorAssignment;
use App\Models\ValidatorAssignmentResponse;
use App\Models\ValidatorForm;
use App\Models\ValidatorFormField;
use App\Models\ValidatorFormSection;
use App\Observers\AssessmentAssignmentObserver;
use App\Observers\AssessmentAssignmentTargetObserver;
use App\Observers\AssessmentAttemptObserver;
use App\Observers\GuruObserver;
use App\Observers\ValidatorAssignmentObserver;
use App\Observers\ValidatorAssignmentResponseObserver;
use App\Observers\ValidatorFormFieldObserver;
use App\Observers\ValidatorFormObserver;
use App\Observers\ValidatorFormSectionObserver;
use App\Services\Assessment\MongoAssessmentAssignmentTargetStore;
use App\Services\Assessment\MongoValidatorAssignmentStore;
use App\Services\Assessment\ValidatorPortalWidgetService;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Reuse MongoDB clients/collections across jobs in a long-lived
        // queue worker instead of reconnecting for every batch.
        $this->app->singleton(MongoAssessmentAssignmentTargetStore::class);
        $this->app->singleton(MongoValidatorAssignmentStore::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        AssessmentAssignmentTarget::observe(AssessmentAssignmentTargetObserver::class);
        AssessmentAttempt::observe(AssessmentAttemptObserver::class);
        AssessmentAssignment::observe(AssessmentAssignmentObserver::class);
        Guru::observe(GuruObserver::class);
        ValidatorAssignment::observe(ValidatorAssignmentObserver::class);
        ValidatorAssignmentResponse::observe(ValidatorAssignmentResponseObserver::class);
        ValidatorForm::observe(ValidatorFormObserver::class);
        ValidatorFormSection::observe(ValidatorFormSectionObserver::class);
        ValidatorFormField::observe(ValidatorFormFieldObserver::class);

        Paginator::useBootstrapFive();
        config(['app.locale' => 'id']);
        Carbon::setLocale('id');
        Blade::anonymousComponentPath(resource_path('views/assessment/components'), 'assessment');

        View::composer('assessment.layouts.app', function (ViewInstance $view): void {
            $view->with(app(ValidatorPortalWidgetService::class)->build(request()));
        });
    }
}
