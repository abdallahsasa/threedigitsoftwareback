<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\ProjectInquiryController;
use App\Http\Controllers\Api\V1\PostController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::post('/contact', [ContactController::class, 'store']);
    Route::post('/project-inquiries', [ProjectInquiryController::class, 'store']);
    Route::get('/projects', [\App\Http\Controllers\Api\V1\ProjectController::class, 'index']);
    Route::get('/projects/{slug}', [\App\Http\Controllers\Api\V1\ProjectController::class, 'show']);
    
    // Stubs for future endpoints
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{slug}', [PostController::class, 'show']);
});
