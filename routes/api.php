<?php

use App\Http\Controllers\CommentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReactionController;

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
    });

    Route::post('/', [OrganizationController::class, 'store'])->name('create_organization');
});

// User model routes
Route::controller(UserController::class)->group(function () {
    Route::prefix('/users')->group(function () {
        Route::get('/', 'index')->name('users');
        Route::post('/', 'store')->name('create_user');

        Route::prefix('/{user}')->group(function () {
            Route::get('/', 'show')->name('user');
            Route::get('/profile-picture', 'showProfilePicture')->name('profile_picture');
            Route::patch('/', 'update')->name('update_user');
        });

        Route::prefix('/session')->group(function () {
            Route::post('/', 'login')->name('login');
            Route::delete('/', 'logout')->name('logout');
        });
    });

    Route::get('/organizations/{organization}/users', 'showOrganizationUsers')->name('organization_users');
});

// Post model routes
Route::controller(PostController::class)->group(function () {
    Route::prefix('/posts')->group(function () {
        Route::get('/', 'index')->name('posts');

        Route::prefix('/{post}')->group(function () {
            Route::get('/', 'show')->name('post');
            Route::patch('/', 'update')->name('update_post');
            Route::delete('/', 'destroy')->name('delete_post');
        });
    });

    Route::prefix('/organizations/{organization}/posts')->group(function () {
        Route::get('/', 'showOrganizationPosts')->name('organization_posts');
        Route::post('/', 'store')->name('create_post');
    });

    Route::get('/users/{user}/posts', 'showUserPosts')->name('users_post');
});

// Comment model routes
Route::controller(CommentController::class)->group(function () {
    Route::prefix('/comments')->group(function () {
        Route::get('/', 'index')->name('comments');

        Route::prefix('/{comment}')->group(function () {
            Route::get('/', 'show')->name('comment');
            Route::patch('/', 'update')->name('update_comment');
            Route::delete('/', 'destroy')->name('delete_comment');
        });
    });

    Route::prefix('/posts/{post}/comments')->group(function () {
        Route::get('/', 'showPostComments')->name('post_comments');
        Route::post('/', 'store')->name('create_comment');
    });
});

// Reaction model routes
Route::controller(ReactionController::class)->group(function () {
    Route::prefix('/reactions')->group(function () {
        Route::get('/', 'index')->name('reactions');

        Route::prefix('/{reaction}')->group(function () {
            Route::get('/', 'show')->name('reaction');
            Route::patch('/', 'update')->name('update_reaction');
            Route::delete('/', 'destroy')->name('delete_reaction');
        });
    });

    Route::prefix('/posts/{post}/reactions')->group(function () {
        Route::get('/', 'showPostReactions')->name('post_reactions');
        Route::post('/', 'store')->name('create_reaction');
    });
});
