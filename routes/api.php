<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PostController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('/organizations')->group(function () {
    Route::get('/', [OrganizationController::class, 'index'])->name('organizations');
    Route::get('/validation', [OrganizationController::class, 'check'])->name('validate_organization');

    Route::prefix('/{organization}')->group(function () {
        Route::get('/', [OrganizationController::class, 'show'])->name('organization');
        Route::patch('/', [OrganizationController::class, 'update'])->name('update_organization');
        Route::delete('/', [OrganizationController::class, 'destroy'])->name('delete_organization');

        Route::prefix('/users')->group(function () {
            Route::get('/', [UserController::class, 'showOrganizationUsers'])->name('organization_users');
            Route::get('/{user}/posts', [PostController::class, 'showUserPosts'])->name('users_post');
        });

        Route::prefix('/posts')->group(function () {
            Route::get('/', [PostController::class, 'index'])->name('posts');
            Route::post('/', [PostController::class, 'store'])->name('create_post');

            Route::prefix('/{post}')->group(function () {
                Route::get('/', [PostController::class, 'show'])->name('post');
                Route::patch('/', [PostController::class, 'update'])->name('update_post');
            });
        });
    });

    Route::post('/', [OrganizationController::class, 'store'])->name('create_organization');
});

Route::prefix('/users')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('users');
    Route::post('/', [UserController::class, 'store'])->name('create_user');

    Route::prefix('/session')->group(function () {
        Route::post('/', [UserController::class, 'login'])->name('login');
        Route::delete('/', [UserController::class, 'logout'])->name('logout');
    });

    Route::prefix('/{user}')->group(function () {
        Route::get('/', [UserController::class, 'show'])->name('user');
        Route::get('/profile-picture', [UserController::class, 'showProfilePicture'])->name('profile_picture');
        Route::patch('/', [UserController::class, 'update'])->name('update_user');
    });
});
