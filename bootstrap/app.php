<?php

use App\Exceptions\ExpectedException;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use App\Http\Responses\ApiErrorResponse;
use App\Utils\Logger;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ExpectedException $e, $request) {
            return (new ApiErrorResponse($e->getMessage(), $e, null, Response::HTTP_BAD_REQUEST))->toResponse($request);
        });

        $exceptions->render(function (Throwable $e, $request) {
            Logger::error($e);
            return (new ApiErrorResponse("Something went wrong on our side. Please try again later.", $e, null, Response::HTTP_INTERNAL_SERVER_ERROR))->toResponse($request);
        });
    })->create();
