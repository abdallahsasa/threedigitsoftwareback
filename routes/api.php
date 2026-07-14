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
    
    Route::get('/services', [\App\Http\Controllers\Api\V1\ServiceController::class, 'index']);
    Route::get('/services/{slug}', [\App\Http\Controllers\Api\V1\ServiceController::class, 'show']);
    
    // Blog Posts
    Route::get('/posts', [\App\Http\Controllers\Api\V1\PostController::class, 'index']);
    Route::get('/posts/{slug}', [\App\Http\Controllers\Api\V1\PostController::class, 'show']);
    
    // Industries
    Route::get('/industries', [\App\Http\Controllers\Api\V1\IndustryController::class, 'index']);
});
