<?php

namespace App\Modules\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserService $service)
    {
    }

    public function index()
    {
        return response()->json($this->service->getAllUsers());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:30',
            'avatar_url' => 'nullable|string|max:2048',
        ]);

        $user = $this->service->register($data);

        return response()->json($user, 201);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed',
            'phone' => 'nullable|string|max:30',
            'avatar_url' => 'nullable|string|max:2048',
        ]);

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $updated = $this->service->update($user->id, $data);

        return response()->json($updated);
    }

    public function destroy(User $user)
    {
        $this->service->delete($user->id);

        return response()->json(['message' => 'User deleted']);
    }
}
