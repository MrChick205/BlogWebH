<?php

namespace App\Modules\Post;

use App\Modules\Media\Media;

class MediaRepository
{
    public function create(array $data)
    {
        return Media::create($data);
    }

    public function findByPost($postId)
    {
        return Media::where('post_id', $postId)->get();
    }

    public function deleteByPost($postId)
    {
        return Media::where('post_id', $postId)->delete();
    }
}