<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('updater/files/check', \App\Http\Controllers\UpdaterFilesCheckController::class);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
