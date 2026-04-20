<?php

namespace App\Modules\Media;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MediaController extends Controller
{
    public function __construct(private MediaService $mediaService)
    {
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,jpg,png,gif,webp,mp4,mov,avi|max:51200',
            'folder' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $folder = $request->input('folder', 'uploads');

        $media = $this->mediaService->uploadFile($file, $folder);

        return response()->json([
            'success' => true,
            'data' => $media,
        ], 201);
    }

    public function uploadMultiple(Request $request): JsonResponse
    {
        $request->validate([
            'files' => 'required|array|min:1|max:10',
            'files.*' => 'file|mimes:jpeg,jpg,png,gif,webp,mp4,mov,avi|max:51200',
            'folder' => 'nullable|string|max:255',
        ]);

        $files = $request->file('files');
        $folder = $request->input('folder', 'uploads');

        $results = [];
        foreach ($files as $file) {
            $results[] = $this->mediaService->uploadFile($file, $folder);
        }

        return response()->json([
            'success' => true,
            'data' => $results,
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->input('per_page', 20);

        $media = $this->mediaService->getUserMedia($user->id, $perPage);

        return response()->json([
            'success' => true,
            'data' => $media,
        ]);
    }

    public function stats(): JsonResponse
    {
        $user = Auth::user();
        $stats = $this->mediaService->getUserStats($user->id);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'public_id' => 'required|string',
        ]);

        $publicId = $request->input('public_id');

        $result = $this->mediaService->deleteFile($publicId);

        return response()->json([
            'success' => $result,
            'message' => $result ? 'File deleted successfully' : 'Failed to delete file',
        ]);
    }
}