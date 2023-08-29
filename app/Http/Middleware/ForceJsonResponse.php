<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponse
{
    /**
     * Force the request to act as if a JSON response was asked. As this Laravel
     * app is an API, it shouldn't return anything than JSON.
     * Without this middleware, some unwanted behaviors could happen, with
     * potential security breaches or long error pages when requests come from a
     * browser navigation, instead of an Ajax/fetch request. For example, the
     * auth middleware redirects the user to the "login" route by default when
     * he/she is not authenticated... which lead to an error because this route
     * doesn't have any GET endpoint in an API!
     * It also avoids manipulating the server from client-side via the Accept
     * header.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
