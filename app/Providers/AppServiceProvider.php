<?php

namespace App\Providers;

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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        config(['app.locale' => 'id']);
        Carbon::setLocale('id');
        Blade::anonymousComponentPath(resource_path('views/assessment/components'), 'assessment');

        View::composer('assessment.layouts.app', function (ViewInstance $view): void {
            $view->with(app(ValidatorPortalWidgetService::class)->build(request()));
        });
    }
}
