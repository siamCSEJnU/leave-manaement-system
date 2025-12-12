<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\CommentController;


Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::post('auth/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    

Route::middleware('auth:api')->group(function () {

    Route::get('users/me', function () {
        return auth('api')->user();
    });
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class);
    });
    
});


Route::middleware('auth:api')->group(function () {
    Route::middleware('role:employee,manager,admin')->group(function () {
        Route::apiResource('leaves', LeaveRequestController::class);
        Route::patch('leaves/{leaf}/approve', [LeaveRequestController::class, 'approve'])
                ->middleware('role:manager,admin');

        Route::patch('leaves/{leaf}/reject', [LeaveRequestController::class, 'reject'])
                ->middleware('role:manager,admin');
    });
});


Route::middleware('auth:api')->group(function () {
    Route::get('leaves/{leave}/comments', [CommentController::class, 'index']);
    Route::post('leaves/{leave}/comments', [CommentController::class, 'store']);
    Route::put('comments/{comment}', [CommentController::class, 'update']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);
});