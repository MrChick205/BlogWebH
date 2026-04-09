<?php

namespace App\Modules\Media;

class MediaRepository
{
    public function all()
    {
        return Media::all();
    }

    public function find($id)
    {
        return Media::find($id);
    }

    public function findByPublicId(string $publicId)
    {
        return Media::where('public_id', $publicId)->first();
    }

    public function findByUser($userId, $perPage = 20)
    {
        return Media::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data)
    {
        return Media::create($data);
    }

    public function update($id, array $data)
    {
        $media = $this->find($id);
        if ($media) {
            $media->update($data);
        }
        return $media;
    }

    public function delete($id)
    {
        return Media::destroy($id);
    }

    public function deleteByPublicId(string $publicId)
    {
        return Media::where('public_id', $publicId)->delete();
    }

    public function getUserStats($userId)
    {
        return [
            'total_files' => Media::where('user_id', $userId)->count(),
            'total_size' => Media::where('user_id', $userId)->sum('size'),
            'images_count' => Media::where('user_id', $userId)->where('resource_type', 'image')->count(),
            'videos_count' => Media::where('user_id', $userId)->where('resource_type', 'video')->count(),
        ];
    }
}