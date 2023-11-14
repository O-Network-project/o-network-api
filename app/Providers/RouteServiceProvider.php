<?php

namespace App\Providers;

use Illuminate\Http\Response;
use Illuminate\Cache\RateLimiting\Limit;
use App\Classes\Invitation\InvitationRepository;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });

        // As invitations are stored in Redis and not in the database, the
        // route-model-binding feature of Eloquent models is not available
        // natively. The below bind function makes it work.
        Route::bind('invitation', function (string $token) {
            $repository = new InvitationRepository();
            $invitation = $repository->find($token);

            if ($invitation === null) {
                abort(Response::HTTP_NOT_FOUND);
            }

            return $invitation;
        });

        // The entities IDs must be integers; it avoids bad URL matching,
        // like /organizations/foo, and so unnecessary database requests
        Route::pattern('organization', '[0-9]+');
        Route::pattern('user', '[0-9]+');
        Route::pattern('post', '[0-9]+');
        Route::pattern('comment', '[0-9]+');
        Route::pattern('reaction', '[0-9]+');
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
