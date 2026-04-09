# Tính năng Upload Ảnh và Video lên Cloudinary

## Cài đặt

1. **Cài đặt package Cloudinary:**

    ```bash
    composer require cloudinary-labs/cloudinary-laravel
    ```

2. **Chạy migration:**
    ```bash
    php artisan migrate
    ```

## Cấu hình Environment Variables

Thêm vào environment variables trên Render:

```env
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
CLOUDINARY_UPLOAD_PRESET=your_upload_preset
CLOUDINARY_FOLDER=blowh
```

## API Endpoints

**Tất cả endpoints đều yêu cầu authentication (Bearer token)**

### Lấy danh sách media của user

```
GET /api/media
```

### Upload file đơn

```
POST /api/media/upload
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

### Upload nhiều files

```
POST /api/media/upload-multiple
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

### Lấy thống kê media của user

```
GET /api/media/stats
Authorization: Bearer {token}
```

### Xóa file

```
DELETE /api/media/delete
Authorization: Bearer {token}
Content-Type: application/json
```

## Database Schema

Table `media` lưu thông tin files đã upload với quan hệ đến users table.

## Bảo mật

- ✅ Yêu cầu authentication cho tất cả endpoints
- ✅ Chỉ user sở hữu mới có thể xóa file của mình
- ✅ Validation đầy đủ file type và size

## Lưu ý cho Production

- Đảm bảo các environment variables được set đúng trên Render
- Monitor usage trên Cloudinary dashboard
- Backup database thường xuyên
