<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            $message = "Not found";

            // We need to check if a route matched first, to avoid returning
            // "Organization not found" for every request with a bad URL
            if ($request->route()) {
                if ($request->is('api/organizations/*')) {
                    $message = "Organization record not found.";
                }

                if ($request->is('api/organizations/*/posts/*')) {
                    $message = "Post record not found.";
                }

                if ($request->is('api/users/*') || $request->is('api/organizations/*/users/*')) {
                    $message = "User record not found.";
                }
            }

            return response()->json(['message' => $message], 404);
        });
    }
}
