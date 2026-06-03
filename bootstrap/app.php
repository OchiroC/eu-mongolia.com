<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\TrackVisit::class,
            \App\Http\Middleware\EnsureNotBlocked::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Баннерын харагдсан тоолуур beacon-д CSRF шаардахгүй.
        $middleware->validateCsrfTokens(except: ['banners/*/impression']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Алдааны хуудсыг брэндлэгдсэн Inertia хуудсаар харуулна.
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $e, Request $request) {
            $status = $response->getStatusCode();

            if ($request->is('api/*') || $request->expectsJson()) {
                return $response;
            }

            // 404/403/503/429 — бүх орчинд; 500 — зөвхөн debug биш үед (дев дээр debug хуудас үлдээнэ).
            $branded = in_array($status, [403, 404, 503, 429], true)
                || ($status === 500 && ! config('app.debug'));

            if ($branded) {
                return \Inertia\Inertia::render('Error', ['status' => $status])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }

            return $response;
        });
    })->create();
