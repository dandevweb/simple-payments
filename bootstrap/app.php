<?php

use App\Exceptions\TransferException;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (TransferException $exception, Request $request) {
            return response()->json([
                'error'   => class_basename(TransferException::class),
                'message' => $exception->getMessage(),
            ], $exception->getStatusCode());
        })->render(function (NotFoundHttpException $e, Request $request) {
            return response()->json([
                'error'   => class_basename(NotFoundHttpException::class),
                'message' => 'Record not found.'
            ], $e->getStatusCode());
        });
    })->create();
