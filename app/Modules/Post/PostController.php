<?php

namespace App\Modules\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;

class PostController extends Controller
{
    public function __construct(private PostService $postService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        Log::info('PostController: store method entered', ['user_id' => Auth::id()]);

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:5000',
            'privacy' => 'nullable|in:public,friends,private',
            'media' => 'nullable|array|max:10',
            'media.*' => 'file|mimes:jpeg,jpg,png,gif,webp,mp4,mov,avi|max:51200', // 50MB max per file
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $errors = $this->enhanceMediaErrors($request, $errors);
            $this->logMediaValidationIssues($request, $errors, 'store');

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors,
            ], 422);
        }

        $data = [
            'content' => $request->input('content'),
            'privacy' => $request->input('privacy', 'public'),
        ];

        $files = $request->file('media');

        $post = $this->postService->createPost($data, $files);

        return response()->json([
            'success' => true,
            'data' => $post,
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->input('per_page', 20);

        $posts = $this->postService->getFeed($user->id, $perPage);

        return response()->json([
            'success' => true,
            'data' => $posts,
        ]);
    }

    public function show($id): JsonResponse
    {
        $user = Auth::user();
        $post = $this->postService->getPostById($id);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        if (!$post->isPublic() && $post->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot view this post',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $post,
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'sometimes|required|string|max:5000',
            'privacy' => 'nullable|in:public,friends,private',
            'media' => 'nullable|array|max:10',
            'media.*' => 'file|mimes:jpeg,jpg,png,gif,webp,mp4,mov,avi|max:51200',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $errors = $this->enhanceMediaErrors($request, $errors);
            $this->logMediaValidationIssues($request, $errors, 'update');

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors,
            ], 422);
        }

        $data = $request->only(['content', 'privacy']);
        $files = $request->file('media');

        $post = $this->postService->updatePost($id, $data, $files);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found or you do not have permission to update it',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $post,
        ]);
    }

    /**
     * Delete post
     */
    public function destroy($id): JsonResponse
    {
        $result = $this->postService->deletePost($id);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found or you do not have permission to delete it',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully',
        ]);
    }

    /**
     * Get user's own posts
     */
    public function myPosts(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->input('per_page', 20);

        $posts = $this->postService->getUserPosts($user->id, $perPage);

        return response()->json([
            'success' => true,
            'data' => $posts,
        ]);
    }

    /**
     * Get user's post statistics
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();
        $stats = $this->postService->getUserStats($user->id);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    private function logMediaValidationIssues(Request $request, array $errors, string $action): void
    {
        $mediaFiles = $request->file('media');
        $mediaFiles = is_array($mediaFiles) ? $mediaFiles : [];

        $fileDiagnostics = [];
        foreach ($mediaFiles as $index => $file) {
            if (!$file instanceof UploadedFile) {
                $fileDiagnostics[] = [
                    'index' => $index,
                    'error' => 'Invalid uploaded file object',
                ];
                continue;
            }

            $uploadErrorCode = $file->getError();
            $fileDiagnostics[] = [
                'index' => $index,
                'original_name' => $file->getClientOriginalName(),
                'client_mime' => $file->getClientMimeType(),
                'size_bytes' => $file->getSize(),
                'is_valid' => $file->isValid(),
                'upload_error_code' => $uploadErrorCode,
                'upload_error_message' => $this->mapUploadErrorCode($uploadErrorCode),
            ];
        }

        Log::warning('PostController: media validation failed', [
            'action' => $action,
            'user_id' => Auth::id(),
            'errors' => $errors,
            'upload_limits' => [
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'max_file_uploads' => ini_get('max_file_uploads'),
            ],
            'files' => $fileDiagnostics,
        ]);
    }

    private function enhanceMediaErrors(Request $request, array $errors): array
    {
        $mediaFiles = $request->file('media');
        $mediaFiles = is_array($mediaFiles) ? $mediaFiles : [];

        $fallbackMessage = sprintf(
            'Upload failed before server could process files. Check limits: upload_max_filesize=%s, post_max_size=%s, max_file_uploads=%s',
            ini_get('upload_max_filesize'),
            ini_get('post_max_size'),
            ini_get('max_file_uploads')
        );

        foreach ($errors as $field => $messages) {
            if (!str_starts_with($field, 'media')) {
                continue;
            }

            if (!is_array($messages)) {
                continue;
            }

            foreach ($messages as $messageIndex => $message) {
                if (!str_contains($message, 'failed to upload')) {
                    continue;
                }

                $fileIndex = null;
                if (preg_match('/^media\.(\d+)$/', $field, $matches)) {
                    $fileIndex = (int) $matches[1];
                }

                if ($fileIndex !== null) {
                    $file = $mediaFiles[$fileIndex] ?? null;
                    if ($file instanceof UploadedFile) {
                        $messages[$messageIndex] = sprintf(
                            'File #%d (%s): %s',
                            $fileIndex + 1,
                            $file->getClientOriginalName(),
                            $this->mapUploadErrorCode($file->getError())
                        );
                    } else {
                        $messages[$messageIndex] = sprintf(
                            'File #%d: %s',
                            $fileIndex + 1,
                            $fallbackMessage
                        );
                    }
                } else {
                    $detailed = [];
                    foreach ($mediaFiles as $index => $file) {
                        if ($file instanceof UploadedFile) {
                            $detailed[] = sprintf(
                                'File #%d (%s): %s',
                                $index + 1,
                                $file->getClientOriginalName(),
                                $this->mapUploadErrorCode($file->getError())
                            );
                        }
                    }

                    $messages[$messageIndex] = !empty($detailed)
                        ? implode('; ', $detailed)
                        : $fallbackMessage;
                }
            }

            $errors[$field] = $messages;
        }

        return $errors;
    }

    private function mapUploadErrorCode(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE specified in form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary upload folder on server',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
            UPLOAD_ERR_OK => 'Upload completed successfully',
            default => 'Unknown upload error',
        };
    }
}