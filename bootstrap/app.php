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
            // Contrôle d'accès : rôle ADMIN/SUPER ADMIN, et habilitations fines.
            'admin'             => \App\Http\Middleware\EnsureIsAdmin::class,
            'superadmin'        => \App\Http\Middleware\EnsureIsSuperAdmin::class,
            'permission'        => \App\Http\Middleware\CheckPermission::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\CheckBlockedStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

