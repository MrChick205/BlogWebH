<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

require base_path('app/Modules/Auth/routes.php');
require base_path('app/Modules/User/routes.php');
require base_path('app/Modules/Post/routes.php');
require base_path('app/Modules/Comment/routes.php');
require base_path('app/Modules/Reaction/Routes.php');
require base_path('app/Modules/Media/routes.php');
