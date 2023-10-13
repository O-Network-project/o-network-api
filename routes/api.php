<?php

use App\Classes\Invitation\Invitation;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\InvitationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReactionController;
use App\Models\Comment;

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

Route::middleware('logout_disabled_user')->group(function () {
    // Organization model routes
    Route::prefix('/organizations')->controller(OrganizationController::class)->group(function () {
        Route::get('/validation', 'check')->name('validate_organization');
        Route::post('/', 'store')->name('create_organization');

        Route::middleware('auth')->group(function () {
            Route::get('/', 'index')->name('organizations');
            Route::get('/{organization}', 'show')->name('organization');
        });
    });

    // User model routes
    Route::controller(UserController::class)->group(function () {
        Route::post('/users', 'store')->name('create_user');

        Route::middleware('auth')->group(function () {
            Route::prefix('/users')->group(function () {
                Route::get('/', 'index')->name('users');

                Route::prefix('/{user}')->group(function () {
                    Route::get('/', 'show')->name('user');
                    Route::get('/profile-picture', 'showProfilePicture')->name('profile_picture');
                    Route::patch('/', 'update')->name('update_user');
                });
            });

            Route::get('/organizations/{organization}/users', 'showOrganizationUsers')->name('organization_users');
        });
    });

    // Authentication
    Route::prefix('/session')->controller(AuthController::class)->group(function () {
        Route::post('/', 'login')->name('login');
        Route::delete('/', 'logout')->name('logout');
    });

    // Invitations
    Route::prefix('/invitations')->controller(InvitationController::class)->group(function () {
        Route::post('/', 'store')
            ->middleware('auth')
            ->can('create', Invitation::class)
            ->name('create_invitation')
        ;
    });

    // Post model routes
    Route::controller(PostController::class)->middleware('auth')->group(function () {
        Route::prefix('/posts')->group(function () {
            Route::get('/', 'index')->name('posts');
            Route::post('/', 'store')->name('create_post');

            Route::prefix('/{post}')->group(function () {
                Route::get('/', 'show')->name('post');
                Route::patch('/', 'update')->name('update_post');
                Route::delete('/', 'destroy')->name('delete_post');
            });
        });

        Route::get('/organizations/{organization}/posts', 'showOrganizationPosts')->name('organization_posts');
        Route::get('/users/{user}/posts', 'showUserPosts')->name('users_post');
    });

    // Comment model routes
    Route::controller(CommentController::class)->middleware('auth')->group(function () {
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
    Route::controller(ReactionController::class)->middleware('auth')->group(function () {
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
});
