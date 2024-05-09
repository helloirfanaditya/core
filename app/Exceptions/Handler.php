<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use PDOException;
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

    public function render($request, Throwable $e)
    {
        $responseCode = $this->isHttpException($e) ? $e->getStatusCode() : 500;
        $message = $e->getMessage();
        $showTrace = true;
        $data = null;
        $meta = null;

        if ($e instanceof AuthenticationException) {
            $responseCode = 401;
            $message = $e->getMessage();
            $showTrace = false;
        }

        if ($e instanceof ValidationException) {
            $responseCode = 400;
            $message = $e->validator->messages()->first();
            $showTrace = false;

            if(is_null(request()->header('X-Version'))) {
                $message = $e->validator->messages()->first();
            } else {
                if(request()->header('X-Version') == 1) {
                    $message = $e->validator->messages()->first();
                } else {
                    $err = [];
                    foreach ($e->validator->errors()->messages() as $key => $value) {
                        $err[$key] = $value[0];
                    }
                    $message = 'Bad Request';
                    $data = $err;
                }
            }
        }

        if ($e instanceof ModelNotFoundException) {
            $responseCode = 500;
            $ids = '';
            if (count($e->getIds()) > 0) {
                $ids = sprintf('ID: %s', implode(', ', $e->getIds()));
            }
            $model = explode('\\', $e->getModel());
            $message = sprintf('%s row not found. %s', end($model), $ids);
            $showTrace = false;
        }

        if ($e instanceof ThrottleRequestsException) {
            $responseCode = 429;
            // $message = $e->getMessage();
            $meta = $e->getHeaders();
            $showTrace = false;
        }

        if ($e instanceof PDOException && strstr($e->getMessage(), 'SQLSTATE[')) {
            preg_match('/SQLSTATE\[(\w+)\]: (.*)/', $e->getMessage(), $matches);
            if (count($matches) >= 3) {
                $code = $matches[1];
                $message = sprintf('%s: %s', $code, $matches[2]);
                $response['query'] = $e->getMessage();
            } else if (strpos($e->getMessage(), 'SQLSTATE[08006]') !== false) {
                $code = 500;
                $message = 'Cannot connect Database';
                $showTrace = false;
            }
        }

        if ($responseCode == 404) {
            $message = 'Path not found';
            $showTrace = false;
        }

        if (config('app.env') == 'production') {
            $showTrace = false;
        }

        $response = [
            "code" => (string) $responseCode,
            "status" => false,
            "message" => $message,
            "data" => $data,
        ];

        if ($showTrace) {
            $response['meta'] = sprintf('[%d] : %s -> %s', $e->getLine(), basename($e->getFile()), $e->getFile());
            $response['trace'] = $e->getTrace();
        }
        if ($meta != null) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $responseCode);
    }
}
