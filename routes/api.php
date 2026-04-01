<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SocialAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\LeaveApprovalController;
use App\Http\Controllers\Api\Employee\LeaveRequestController;

//Login konvensional
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');

//login Oauth
Route::get('/auth/google', [SocialAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [SocialAuthController::class, 'callback']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ── Employee Routes ─────────────────────────────────────
    Route::middleware('role:employee')
        ->prefix('employee')
        ->name('employee.')
        ->group(function () {

            Route::get('leaves/quota', [LeaveRequestController::class, 'quota'])
                ->name('leaves.quota');

            Route::apiResource('leaves', LeaveRequestController::class)
                ->only(['index', 'store', 'show']);
        });

    // ── Admin Routes ─────────────────────────────────────────
    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::get('leaves', [LeaveApprovalController::class, 'index'])
                ->name('leaves.index');

            Route::get('leaves/{id}', [LeaveApprovalController::class, 'show'])
                ->name('leaves.show');

            Route::put('leaves/{id}/review', [LeaveApprovalController::class, 'review'])
                ->name('leaves.review');
        });
});
