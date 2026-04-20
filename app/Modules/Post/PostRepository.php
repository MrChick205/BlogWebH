<?php

namespace App\Modules\Post;

class PostRepository
{
    public function all()
    {
        return Post::with(['user', 'media'])->get();
    }

    public function find($id)
    {
        return Post::with(['user', 'media', 'comments.user'])
            ->withCount([
                'comments',
                'reactions',

                'reactions as like_count' => function ($q) {
                    $q->where('type', 'like');
                },
                'reactions as love_count' => function ($q) {
                    $q->where('type', 'love');
                },
                'reactions as haha_count' => function ($q) {
                    $q->where('type', 'haha');
                },
                'reactions as sad_count' => function ($q) {
                    $q->where('type', 'sad');
                },
                'reactions as angry_count' => function ($q) {
                    $q->where('type', 'angry');
                },
            ])
            ->find($id);
    }

    public function findByUser($userId, $perPage = 20)
    {
        return Post::with(['user', 'media'])
            ->withCount([
                'comments',
                'reactions',

                'reactions as like_count' => function ($q) {
                    $q->where('type', 'like');
                },
                'reactions as love_count' => function ($q) {
                    $q->where('type', 'love');
                },
                'reactions as haha_count' => function ($q) {
                    $q->where('type', 'haha');
                },
                'reactions as sad_count' => function ($q) {
                    $q->where('type', 'sad');
                },
                'reactions as angry_count' => function ($q) {
                    $q->where('type', 'angry');
                },
            ])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getFeed($userId, $perPage = 20)
    {
        return Post::with(['user', 'media'])
            ->withCount([
                'comments',
                'reactions',

                'reactions as like_count' => function ($q) {
                    $q->where('type', 'like');
                },
                'reactions as love_count' => function ($q) {
                    $q->where('type', 'love');
                },
                'reactions as haha_count' => function ($q) {
                    $q->where('type', 'haha');
                },
                'reactions as sad_count' => function ($q) {
                    $q->where('type', 'sad');
                },
                'reactions as angry_count' => function ($q) {
                    $q->where('type', 'angry');
                },
            ])
            ->visibleTo($userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data)
    {
        return Post::create($data);
    }

    public function update($id, array $data)
    {
        $post = $this->find($id);
        if ($post) {
            $post->update($data);
        }
        return $post;
    }

    public function delete($id)
    {
        return Post::destroy($id);
    }

    public function getUserStats($userId)
    {
        return [
            'total_posts' => Post::where('user_id', $userId)->count(),
            'public_posts' => Post::where('user_id', $userId)->where('privacy', 'public')->count(),
            'private_posts' => Post::where('user_id', $userId)->where('privacy', 'private')->count(),
        ];
    }
}