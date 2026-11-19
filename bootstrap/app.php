<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'check.blocked'     => \App\Http\Middleware\CheckBlockedStatus::class,
            'company.session'   => \App\Http\Middleware\CompanySession::class,
            'exercice.context'  => \App\Http\Middleware\ExerciceContextMiddleware::class,
            'verify.hub.token'  => \App\Http\Middleware\VerifyHubToken::class,
            'cle.entreprise'    => \App\Http\Middleware\VerifieCleEntreprise::class,
            'integration.auth'  => \App\Http\Middleware\VerifieApplicationOuCleEntreprise::class,
            // Contrôle d'accès : rôle ADMIN/SUPER ADMIN, et habilitations fines.
            'admin'             => \App\Http\Middleware\EnsureIsAdmin::class,
            'superadmin'        => \App\Http\Middleware\EnsureIsSuperAdmin::class,
            'permission'        => \App\Http\Middleware\CheckPermission::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\CheckBlockedStatus::class,
            \App\Http\Middleware\EnforcePageVisibility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $exception, \Illuminate\Http\Request $request) {
            if (config('app.debug')) {
                return null;
            }

            if ($exception instanceof \Illuminate\Auth\AuthenticationException
                || $exception instanceof \Illuminate\Validation\ValidationException
                || $exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException
                || $exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return null;
            }

            if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                && $exception->getStatusCode() < 500) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Le service est momentanément indisponible. Veuillez réessayer plus tard ou contacter le support DC-KNOWING.'], 500);
            }

            return response()->view('errors.500', [], 500);
        });
    })->create();

