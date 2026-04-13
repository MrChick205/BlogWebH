<?php
use Illuminate\Support\Facades\Route;
use App\Modules\Reaction\ReactionController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('posts/{postId}/reactions', [ReactionController::class, 'index']);
    Route::post('posts/{postId}/reactions', [ReactionController::class, 'store']);
    Route::delete('posts/{postId}/reactions', [ReactionController::class, 'destroy']);
});