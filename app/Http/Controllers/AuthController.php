<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Authenticate a user with the credentials provided in the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => "Invalid credentials"], 401);
        }

        $user = Auth::user();

        if ($user->disabled) {
            // If the Auth::attempt method works, the user is authenticated and
            // stored in session. If its disabled, it needs to be logged out to
            // avoid its persistance in the sessions system.
            Auth::logout();
            return response()->json(['message' => "Disabled user"], 403);
        }

        $request->session()->regenerate();
        return new UserResource($user);
    }

    /**
     * Destroy the session of the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
    }

    /**
     * Return the authenticated user in session, or an empty response with a 204
     * No Content status code.
     *
     * @return \Illuminate\Http\Response
     */
    public function showSessionUser()
    {
        $user = Auth::user();

        return $user ?
            new UserResource($user) :
            response()->noContent();
    }
}
