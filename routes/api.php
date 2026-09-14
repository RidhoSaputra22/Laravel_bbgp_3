<?php

use App\Http\Controllers\Api\AssessmentApiAuthController;
use App\Http\Controllers\Api\AssessmentConfigurationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('/auth/token', [AssessmentApiAuthController::class, 'token'])
        ->middleware('throttle:6,1')
        ->name('auth.token');

    Route::middleware(['auth:sanctum', 'abilities:assessment:read'])->group(function () {
        Route::delete('/auth/token', [AssessmentApiAuthController::class, 'revoke'])->name('auth.revoke');
        Route::get('/assessments', [AssessmentConfigurationController::class, 'index'])->name('assessments.index');
        Route::get('/assessments/{identifier}', [AssessmentConfigurationController::class, 'show'])->name('assessments.show');
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
