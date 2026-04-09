<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Media\MediaController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('media', [MediaController::class, 'index']);
    Route::post('media/upload', [MediaController::class, 'upload']);
    Route::post('media/upload-multiple', [MediaController::class, 'uploadMultiple']);
    Route::get('media/stats', [MediaController::class, 'stats']);
    Route::delete('media/delete', [MediaController::class, 'delete']);
});