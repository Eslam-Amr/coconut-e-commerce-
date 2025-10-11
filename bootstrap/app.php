<?php

use App\Exceptions\ApiExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        function () {
            require base_path('routes/web.php');

            // Route::group(base_path('routes/web.php'));
            Route::prefix('admin')->middleware('api')->group(base_path('routes/Api/admin.php'));
            Route::prefix('client')->middleware('api')->group(base_path('routes/Api/client.php'));
            Route::prefix('general')->middleware('api')->group(base_path('routes/Api/general.php'));
            Route::prefix('guest')->middleware('api')->group(base_path('routes/Api/guest.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->use( [
            \App\Http\Middleware\SetLocale::class,
        ]);
        
        $middleware->alias([
            // 'auth' => \App\Http\Middleware\AuthenticatedMiddleware::class,

            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'client' => \App\Http\Middleware\ClientMiddleware::class,
            'authenticated' => \App\Http\Middleware\AuthenticatedMiddleware::class,



        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                return ApiExceptionHandler::handle($e, $request);
            }
            return null;
        });
    })->create();
