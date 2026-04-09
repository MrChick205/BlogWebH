# Tính năng Upload Ảnh và Video lên Cloudinary

## Cài đặt

1. **Cài đặt package Cloudinary:**

    ```bash
    composer require cloudinary-labs/cloudinary-laravel
    ```

2. **Publish config (tùy chọn):**
    ```bash
    php artisan vendor:publish --provider="CloudinaryLabs\CloudinaryLaravel\CloudinaryServiceProvider" --tag="cloudinary-config"
    ```

## Cấu hình Environment Variables

Thêm vào file `.env` hoặc environment variables trên Render:

```env
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
CLOUDINARY_UPLOAD_PRESET=your_upload_preset
CLOUDINARY_FOLDER=blowh
```

## API Endpoints

### Upload file đơn

```
POST /api/media/upload
Content-Type: multipart/form-data

- file: (required) File ảnh/video
- folder: (optional) Thư mục lưu trên Cloudinary
```

**Response:**

```json
{
    "success": true,
    "data": {
        "public_id": "blowh/abc123_image",
        "url": "https://res.cloudinary.com/.../image/upload/v123/abc123_image.jpg",
        "format": "jpg",
        "width": 800,
        "height": 600,
        "bytes": 123456,
        "resource_type": "image",
        "folder": "uploads",
        "uploaded_at": "2026-04-08T10:00:00.000000Z"
    }
}
```

### Upload nhiều files

```
POST /api/media/upload-multiple
Content-Type: multipart/form-data

- files[]: (required) Mảng files ảnh/video
- folder: (optional) Thư mục lưu trên Cloudinary
```

### Xóa file

```
DELETE /api/media/delete
Content-Type: application/json

{
  "public_id": "blowh/abc123_image"
}
```

## Hỗ trợ định dạng

- **Ảnh:** JPEG, JPG, PNG, GIF, WebP
- **Video:** MP4, MOV, AVI
- **Kích thước tối đa:** 50MB per file
- **Số files tối đa:** 10 files per request

## Tính năng

- ✅ Tự động detect loại file (ảnh/video)
- ✅ Resize và optimize chất lượng
- ✅ Tạo public_id unique
- ✅ Lưu vào thư mục tùy chỉnh
- ✅ Xử lý lỗi và logging
- ✅ Validation file type và size

## Sử dụng trong code

```php
use App\Modules\Media\MediaService;

$mediaService = app(MediaService::class);
$result = $mediaService->uploadFile($uploadedFile, 'posts');
```

## Lưu ý cho Production

- Đảm bảo các environment variables được set đúng trên Render
- Monitor usage trên Cloudinary dashboard
- Cấu hình CORS nếu cần access từ frontend khác domain
