<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Authentication routes (public)
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Health check endpoint (protected)
    Route::get('/health', function () {
        $dbStatus = 'disconnected';
        try {
            DB::connection()->getPdo();
            $dbStatus = 'connected';
        } catch (\Throwable $e) {
            $dbStatus = 'error';
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'UTS Task Tracker Backend is running',
            'database' => $dbStatus,
        ]);
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
        ], 200);
    });

    // Task CRUD endpoints
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
});
