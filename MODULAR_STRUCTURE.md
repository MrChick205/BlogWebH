# Cấu Trúc Dự Án Modular

Dự án đã được tổ chức lại theo cấu trúc modular. Mỗi module chứa tất cả các thành phần liên quan đến nó.

## Cấu Trúc Thư Mục

```
app/
  Modules/
    User/
      Controllers/           # Controllers của User module
        UserController.php
      Models/               # Models của User module
        User.php
      Services/             # Business logic
        UserService.php
      Repositories/         # Data access layer
        UserRepository.php
      Routes/               # Routes định nghĩa cho module
        api.php

    Product/
      Controllers/
        ProductController.php
      Models/
        Product.php
      Services/
        ProductService.php
      Repositories/
        ProductRepository.php
      Routes/
        api.php
```

## Cách Sử Dụng

### 1. Khởi Tạo Controller

```php
<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Models\User;
use App\Modules\User\Services\UserService;

class UserController extends Controller
{
    public function __construct(private UserService $userService)
    {}

    public function index()
    {
        // Sử dụng service
    }
}
```

### 2. Tạo Service

```php
<?php

namespace App\Modules\User\Services;

use App\Modules\User\Repositories\UserRepository;

class UserService
{
    public function __construct(private UserRepository $repository)
    {}

    public function getAll()
    {
        return $this->repository->all();
    }
}
```

### 3. Sử Dụng Repository

```php
<?php

namespace App\Modules\User\Repositories;

use App\Modules\User\Models\User;

class UserRepository
{
    public function all()
    {
        return User::all();
    }

    public function find($id)
    {
        return User::find($id);
    }
}
```

## Các Files Đã Được Cập Nhật

### 1. routes/api.php

Routes chính của dự án đã được cập nhật để include routes từ các modules:

```php
require base_path('app/Modules/User/Routes/api.php');
require base_path('app/Modules/Product/Routes/api.php');
```

### 2. composer.json (Không cần thay đổi)

Namespace `App\` đã được map đến `app/` folder, vì vậy tất cả namespaces mới sẽ tự động hoạt động.

## Có Thể Cần Cập Nhật

### 1. config/auth.php

Nếu sử dụng authentication, hãy cập nhật model location:

```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => \App\Modules\User\Models\User::class,
    ],
],
```

### 2. Migrations

Nếu có migration liên quan đến models trong modules, hãy đảm bảo các migration được cập nhật hoặc tạo migration mới.

## Lợi Ích Của Cấu Trúc Modular

- **Dễ bảo trì**: Mỗi module chứa tất cả code liên quan
- **Dễ mở rộng**: Thêm module mới mà không ảnh hưởng đến code cũ
- **Dễ test**: Có thể test từng module độc lập
- **Tái sử dụng**: Services và Repositories có thể dùng cho nhiều Controllers
- **Tỉnh rõ ràng**: Cấu trúc code rõ ràng và dễ hiểu

## Thêm Module Mới

Để thêm module mới (ví dụ: Post module):

1. Tạo folder `app/Modules/Post/` với các subfolder: Controllers, Models, Services, Repositories, Routes
2. Tạo các files cần thiết
3. Tạo file `app/Modules/Post/Routes/api.php`
4. Thêm vào `routes/api.php`:
    ```php
    require base_path('app/Modules/Post/Routes/api.php');
    ```

---

**Cấu trúc đã được tổ chức lại thành công!** ✓
