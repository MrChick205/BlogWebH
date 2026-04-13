<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Comment\CommentController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('posts/{postId}/comments', [CommentController::class, 'index']);
    Route::post('posts/{postId}/comments', [CommentController::class, 'store']);
    Route::get('comments/{id}', [CommentController::class, 'show']);
    Route::put('comments/{id}', [CommentController::class, 'update']);
    Route::delete('comments/{id}', [CommentController::class, 'destroy']);
});
