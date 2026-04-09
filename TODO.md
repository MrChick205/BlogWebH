# Debug Post Creation & Cloudinary Upload Issue

## Status: In Progress

### Step 1: [COMPLETED] Add Debug Logging

- Added logs in PostController::store (entry + files)
- Added logs in PostService constructor, createPost, post/media create, upload.
- Test API now to see logs.

### Step 2: [PENDING] Verify Environment Variables

- Check .env for CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY, CLOUDINARY_API_SECRET.

### Step 3: [PENDING] Test Post Creation Without Media

- Use curl or Postman with only 'content' field.
- Check if post created in DB: `php artisan tinker 'App\\Modules\\Post\\Post::count()'`

### Step 4: [PENDING] Test Full Request with Media

- Send 'content' + 'media' file.
- Check logs, DB, Cloudinary dashboard.

### Step 5: [PENDING] Verify Response

- Capture exact HTTP response body/headers.

### Step 6: [PENDING] Fix Based on Logs

- Address any missing env vars, request format issues, etc.

**Next Action: Add debug logging to trace execution.**
