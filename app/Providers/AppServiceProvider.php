<?php

namespace App\Providers;

use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentAttempt;
use App\Models\Guru;
use App\Observers\AssessmentAssignmentObserver;
use App\Observers\AssessmentAssignmentTargetObserver;
use App\Observers\AssessmentAttemptObserver;
use App\Observers\GuruObserver;
use App\Services\Assessment\MongoAssessmentAssignmentTargetStore;
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
        // Reuse the MongoDB client/collection across jobs in a long-lived
        // queue worker instead of reconnecting for every target batch.
        $this->app->singleton(MongoAssessmentAssignmentTargetStore::class);
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

        Paginator::useBootstrapFive();
        config(['app.locale' => 'id']);
        Carbon::setLocale('id');
        Blade::anonymousComponentPath(resource_path('views/assessment/components'), 'assessment');

        View::composer('assessment.layouts.app', function (ViewInstance $view): void {
            $view->with(app(ValidatorPortalWidgetService::class)->build(request()));
        });
    }
}
