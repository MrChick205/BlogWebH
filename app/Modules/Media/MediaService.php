<?php

namespace App\Modules\Media;

use Cloudinary\Api\ApiResponse;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MediaService
{
    private UploadApi $uploadApi;

    public function __construct(private MediaRepository $repository)
    {
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
    }

    public function uploadFile(UploadedFile $file, string $folder = 'uploads'): Media
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('User must be authenticated to upload files');
        }

        try {
            $options = $this->getUploadOptions($file, $folder);

            $result = $this->uploadApi->upload($file->getRealPath(), $options);

            $mediaData = [
                'user_id' => $user->id,
                'public_id' => $result['public_id'],
                'url' => $result['secure_url'],
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'format' => $result['format'],
                'size' => $result['bytes'],
                'width' => $result['width'] ?? null,
                'height' => $result['height'] ?? null,
                'resource_type' => $result['resource_type'],
                'folder' => $folder,
                'metadata' => $result,
            ];

            return $this->repository->create($mediaData);
        } catch (\Exception $e) {
            Log::error('Cloudinary upload failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'user_id' => $user->id,
            ]);

            throw new \Exception('Failed to upload file: ' . $e->getMessage());
        }
    }

    /**
     * Delete file from Cloudinary and database
     */
    public function deleteFile(string $publicId): bool
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('User must be authenticated to delete files');
        }

        // Find media in database
        $media = $this->repository->findByPublicId($publicId);
        if (!$media) {
            return false;
        }

        // Check ownership
        if ($media->user_id !== $user->id) {
            throw new \Exception('You can only delete your own files');
        }

        try {
            // Delete from Cloudinary
            $result = $this->uploadApi->destroy($publicId);
            $cloudinarySuccess = isset($result['result']) && $result['result'] === 'ok';

            // Delete from database
            $this->repository->deleteByPublicId($publicId);

            return $cloudinarySuccess;
        } catch (\Exception $e) {
            Log::error('Cloudinary delete failed', [
                'error' => $e->getMessage(),
                'public_id' => $publicId,
                'user_id' => $user->id,
            ]);

            // Still delete from database even if Cloudinary fails
            $this->repository->deleteByPublicId($publicId);

            return false;
        }
    }

    /**
     * Get user's media files
     */
    public function getUserMedia(int $userId, int $perPage = 20)
    {
        return $this->repository->findByUser($userId, $perPage);
    }

    /**
     * Get user's media statistics
     */
    public function getUserStats(int $userId): array
    {
        return $this->repository->getUserStats($userId);
    }

    /**
     * Get upload options based on file type
     */
    private function getUploadOptions(UploadedFile $file, string $folder): array
    {
        $mimeType = $file->getMimeType();
        $isVideo = str_starts_with($mimeType, 'video/');

        $options = array_merge(config('cloudinary.default_upload_options'), [
            'folder' => $folder,
            'public_id' => uniqid() . '_' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
        ]);

        if ($isVideo) {
            $options = array_merge($options, config('cloudinary.video_upload_options'));
        } else {
            $options = array_merge($options, config('cloudinary.image_upload_options'));
        }

        return $options;
    }

    /**
     * Get file info from Cloudinary
     */
    public function getFileInfo(string $publicId): ?array
    {
        try {
            $result = $this->uploadApi->explicit($publicId, ['type' => 'upload']);
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to get file info', [
                'error' => $e->getMessage(),
                'public_id' => $publicId,
            ]);

            return null;
        }
    }
}