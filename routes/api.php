<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FarmController;
use App\Http\Controllers\Api\V1\FieldController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::prefix('auth')->middleware('throttle:6,1')->group(function (): void {
        Route::post('/register', [AuthController::class, 'register'])->name('api.v1.auth.register');
        Route::post('/login', [AuthController::class, 'login'])->name('api.v1.auth.login');
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
        Route::get('/auth/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
        Route::apiResource('farms', FarmController::class);

        Route::prefix('farms/{farm}/fields')->name('api.v1.farms.fields.')->group(function (): void {
            Route::get('/', [FieldController::class, 'index'])->name('index');
            Route::post('/', [FieldController::class, 'store'])->name('store');
            Route::get('/{field}', [FieldController::class, 'show'])->name('show');
            Route::match(['put', 'patch'], '/{field}', [FieldController::class, 'update'])->name('update');
            Route::delete('/{field}', [FieldController::class, 'destroy'])->name('destroy');
        });
    });
});
