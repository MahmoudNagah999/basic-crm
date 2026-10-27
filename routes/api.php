<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::apiResource('customers', CustomerController::class);
Route::apiResource('notes', NoteController::class);
Route::apiResource('projects', ProjectController::class);
