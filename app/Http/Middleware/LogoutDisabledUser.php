<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LogoutDisabledUser
{
    /**
     * Handle the case where the user has been disabled but his/her session is
     * still active.
     * As it's not possible to logout another user than the current one when
     * sessions are stored in files with Laravel, this middleware intercepts any
     * further request from disabled users to log them out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request):
     * (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var ?User $user */
        $user = Auth::user();

        if ($user && $user->disabled) {
            // It's intentional to not launch the AuthController::logout()
            // method here. It invalidates the session and it's not working very
            // well when multiple requests are received at the same time from
            // the same client (which is a normal behavior of our app).
            // Long story short, an additional session would have been created
            // in the file system... that wouldn't have been used. And creating
            // dead resources is not a good practice.
            // The session ID is not renewed here, so no extra session is
            // created.
            Auth::logout();
            $request->session()->flush();

            // Throwing a 401 manually and immediately is required to avoid the
            // request to be treated
            abort(Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
