<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrganizationController;

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
        Route::get('/users', [UserController::class, 'showOrganizationUsers'])->name('organization_users');
        Route::patch('/', [OrganizationController::class, 'update'])->name('update_organization');
        Route::delete('/', [OrganizationController::class, 'destroy'])->name('delete_organization');
    });

    Route::post('/', [OrganizationController::class, 'store'])->name('create_organization');
});

Route::prefix('/users')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('users');
    Route::get('/{user}', [UserController::class, 'show'])->name('user');
});
