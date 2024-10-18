<?php

use App\Http\Controllers\Api\TimeslotController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('timeslots', TimeslotController::class);
    Route::post('/timeslots/batch', [TimeslotController::class, 'handleBatchRequests']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::get('/user', function (Request $request) {
    return response()->json($request->user());
})->middleware('auth:sanctum');

Route::get('/test', function () {
    return response()->json(['message' => 'Test successful']);
});


