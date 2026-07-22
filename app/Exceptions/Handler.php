<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof TokenMismatchException) {
            return redirect()->route('user.index')->with('message', 'session expired');
        }

        if ($this->shouldRedirectAssessmentPortalNotFound($request, $e)) {
            return $this->assessmentPortalDashboardRedirect($request);
        }

        return parent::render($request, $e);
    }

    private function shouldRedirectAssessmentPortalNotFound($request, Throwable $e): bool
    {
        if (! $this->isAssessmentPortalRequest($request)) {
            return false;
        }

        return $e instanceof ModelNotFoundException
            || $e instanceof NotFoundHttpException
            || ($e instanceof HttpExceptionInterface && $e->getStatusCode() === 404);
    }

    private function isAssessmentPortalRequest($request): bool
    {
        $path = trim((string) $request->path(), '/');

        return $path === 'assessment' || str_starts_with($path, 'assessment/');
    }

    private function assessmentPortalDashboardRedirect($request)
    {
        $message = 'Halaman assessment tidak ditemukan. Anda diarahkan kembali ke dashboard.';
        $dashboardUrl = route('assessment.portal.dashboard');

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'not_found',
                'message' => $message,
                'redirect_url' => $dashboardUrl,
            ], 404);
        }

        return redirect()
            ->to($dashboardUrl)
            ->withErrors([
                'portal' => $message,
            ]);
    }
}
