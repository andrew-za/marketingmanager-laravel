<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        \App\Providers\LocalizationServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'organization' => \App\Http\Middleware\EnsureOrganizationAccess::class,
            'organization.admin' => \App\Http\Middleware\EnsureOrganizationAdmin::class,
            'not.client' => \App\Http\Middleware\EnsureNotClientRole::class,
            'agency' => \App\Http\Middleware\EnsureAgencyAccess::class,
            'agency.admin' => \App\Http\Middleware\EnsureAgencyAdmin::class,
            'locale' => \App\Http\Middleware\SetLocale::class,
            'tenant' => \App\Http\Middleware\SetTenantContext::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'brand.context' => \App\Http\Middleware\EnsureBrandContext::class,
            'require.confirmation' => \App\Http\Middleware\RequireConfirmation::class,
            'agency.client' => \App\Http\Middleware\EnsureClientAccess::class,
            'api.rate_limit' => \App\Http\Middleware\ApiRateLimit::class,
        ]);
        
        // Add SetLocale middleware to web group by default
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        // Add security headers to all responses
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

