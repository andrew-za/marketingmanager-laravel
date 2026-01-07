<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

        $this->renderable(function (HttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'status' => $e->getStatusCode(),
                ], $e->getStatusCode());
            }
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => app()->environment('production') 
                        ? 'An error occurred. Please try again later.' 
                        : $e->getMessage(),
                    'status' => 500,
                ], 500);
            }
        });
    }

    /**
     * Report or log an exception
     */
    public function report(Throwable $e): void
    {
        if ($this->shouldReport($e)) {
            Log::error('Exception occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        parent::report($e);
    }
}

