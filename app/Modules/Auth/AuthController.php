<?php

namespace App\Modules\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\AuthRegisterRequest;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }

    public function register(AuthRegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());

        return response()->json([
            'user' => $user,
        ], 201);
    }

    public function login(AuthLoginRequest $request)
    {
        $user = $this->authService->login($request->validated());

        if (! $user) {
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        $token = $this->authService->createOrUpdateLoginToken($user);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
