<?php

namespace App\Exceptions;

use Throwable;
use Http\Client\Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Sentry\Laravel\Integration;
use Illuminate\Http\JsonResponse;
use Zendesk\API\Exceptions\CustomException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */

    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            Integration::captureUnhandledException($e);
        });
    }

    public function render($request, Throwable $exception )
    {
        // Bearer Token checking for Api's
        // if (!$request->expectsJson()) {
        //     return response()->json(['status' => false,'message' => 'authentication token is missing'], 401);
        // }
           // ends Bearer Token checking for Api's

        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'error' => 'Method not allowed for this route.',
            ], 405);
        }

        if ($this->isHttpException($exception)) {
            $statusCode = $exception->getResponse()->getStatusCode();

            if ($statusCode === 500) {
                return response()->view('errors.500page', [], 500);
            }
        }


        return parent::render($request, $exception);
       
    }
}
