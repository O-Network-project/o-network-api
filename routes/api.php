<?php

use App\Http\Controllers\OrganizationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    Route::get('/{organization}', [OrganizationController::class, 'show'])->name('organization');
    Route::post('/', [OrganizationController::class, 'store'])->name('create_organization');
    Route::patch('/{organization}', [OrganizationController::class, 'update'])->name('update_organization');
});
