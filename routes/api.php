<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Task Management API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api automatically by Laravel.
| The "report" route must be declared BEFORE the {task} route-model
| binding so Laravel doesn't mistake "report" for a task ID.
|
*/

// Bonus: Daily Report  — must come before {task} routes
Route::get('/tasks/report', [TaskController::class, 'report']);

// Core CRUD
Route::post('/tasks',                [TaskController::class, 'store']);
Route::get('/tasks',                 [TaskController::class, 'index']);
Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
Route::delete('/tasks/{task}',       [TaskController::class, 'destroy']);
