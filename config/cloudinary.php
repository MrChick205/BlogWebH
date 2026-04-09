<?php

return [

    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    'api_key' => env('CLOUDINARY_API_KEY'),
    'api_secret' => env('CLOUDINARY_API_SECRET'),

    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),

    'default_upload_options' => [
        'folder' => env('CLOUDINARY_FOLDER', 'blowh'),
        'resource_type' => 'auto',
        'quality' => 'auto',
        'format' => 'auto',
    ],

    'image_upload_options' => [
        'transformation' => [
            ['width' => 800, 'height' => 600, 'crop' => 'limit'],
            ['quality' => 'auto'],
        ],
    ],

    'video_upload_options' => [
        'resource_type' => 'video',
        'transformation' => [
            ['width' => 1280, 'height' => 720, 'crop' => 'limit'],
            ['quality' => 'auto'],
        ],
    ],
];