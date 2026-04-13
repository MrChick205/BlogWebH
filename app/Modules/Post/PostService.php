<?php

namespace App\Modules\Post;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostService
{
    private UploadApi $uploadApi;

    public function __construct(private PostRepository $postRepository, private MediaRepository $mediaRepository)
    {
        // Configure Cloudinary
        Configuration::instance([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key' => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => true,
            ],
        ]);

        $this->uploadApi = new UploadApi();
        Log::info('PostService: Cloudinary initialized successfully');
    }

    public function createPost(array $data, ?array $files = null): Post
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('User must be authenticated to create posts');
        }

        DB::beginTransaction();
        try {
            $postData = [
                'user_id' => $user->id,
                'content' => $data['content'] ?? '',
                'privacy' => $data['privacy'] ?? 'public',
            ];

            $post = $this->postRepository->create($postData);

            if ($files && count($files) > 0) {
                $this->uploadMediaFiles($post->id, $files);
            }

            DB::commit();
            return $post->load(['user', 'media']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Post creation failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'data' => $data,
            ]);

            throw new \Exception('Failed to create post: ' . $e->getMessage());
        }
    }

    public function updatePost($postId, array $data, ?array $files = null): ?Post
    {
        $user = Auth::user();
        $post = $this->postRepository->find($postId);

        if (!$post || $post->user_id !== $user->id) {
            return null;
        }

        DB::beginTransaction();
        try {
            $postData = array_filter([
                'content' => $data['content'] ?? $post->content,
                'privacy' => $data['privacy'] ?? $post->privacy,
            ]);

            $updatedPost = $this->postRepository->update($postId, $postData);

            if ($files && count($files) > 0) {
                $this->uploadMediaFiles($postId, $files);
            }

            DB::commit();
            return $updatedPost?->load(['user', 'media']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Post update failed', [
                'error' => $e->getMessage(),
                'post_id' => $postId,
                'user_id' => $user->id,
            ]);

            throw new \Exception('Failed to update post: ' . $e->getMessage());
        }
    }

    public function deletePost($postId): bool
    {
        $user = Auth::user();
        $post = $this->postRepository->find($postId);

        if (!$post || $post->user_id !== $user->id) {
            return false;
        }

        DB::beginTransaction();
        try {
            foreach ($post->media as $media) {
                $this->deleteFromCloudinary($media->url);
            }

            $this->postRepository->delete($postId);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Post deletion failed', [
                'error' => $e->getMessage(),
                'post_id' => $postId,
                'user_id' => $user->id,
            ]);

            return false;
        }
    }

    public function getUserPosts(int $userId, int $perPage = 20)
    {
        return $this->postRepository->findByUser($userId, $perPage);
    }


    public function getFeed(int $userId, int $perPage = 20)
    {
        return $this->postRepository->getFeed($userId, $perPage);
    }


    public function getUserStats(int $userId): array
    {
        return $this->postRepository->getUserStats($userId);
    }


    public function getPostById($postId): ?Post
    {
        return $this->postRepository->find($postId);
    }

    private function uploadMediaFiles(int $postId, array $files): void
    {
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $this->uploadSingleFile($postId, $file);
            }
        }
    }

    private function uploadSingleFile(int $postId, UploadedFile $file): void
    {
        try {
            $mimeType = $file->getMimeType();
            $isVideo = str_starts_with($mimeType, 'video/');
            $isImage = str_starts_with($mimeType, 'image/');

            if (!$isImage && !$isVideo) {
                throw new \Exception('Only image and video files are allowed');
            }

            $type = $isVideo ? 'video' : 'image';

            $options = [
                'folder' => 'posts',
                'resource_type' => $type,
                'public_id' => uniqid() . '_' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            ];

            if ($isImage) {
                $options['transformation'] = [
                    ['width' => 800, 'height' => 600, 'crop' => 'limit'],
                    ['quality' => 'auto'],
                ];
            } elseif ($isVideo) {
                $options['transformation'] = [
                    ['width' => 1280, 'height' => 720, 'crop' => 'limit'],
                    ['quality' => 'auto'],
                ];
            }

            $result = $this->uploadApi->upload($file->getRealPath(), $options);

            $mediaData = [
                'post_id' => $postId,
                'url' => $result['secure_url'],
                'type' => $type,
            ];

            $this->mediaRepository->create($mediaData);
        } catch (\Exception $e) {
            Log::error('Media upload failed', [
                'error' => $e->getMessage(),
                'post_id' => $postId,
                'file' => $file->getClientOriginalName(),
            ]);

            throw new \Exception('Failed to upload media: ' . $e->getMessage());
        }
    }

    private function deleteFromCloudinary(string $url): void
    {
        try {
            $path = parse_url($url, PHP_URL_PATH);
            $pathParts = explode('/', $path);
            $filename = end($pathParts);
            $publicId = 'posts/' . pathinfo($filename, PATHINFO_FILENAME);

            $this->uploadApi->destroy($publicId);
        } catch (\Exception $e) {
            Log::error('Cloudinary delete failed', [
                'error' => $e->getMessage(),
                'url' => $url,
            ]);
        }
    }
}