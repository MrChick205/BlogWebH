<?php

namespace App\Modules\Auth;

use App\Modules\User\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(private UserRepository $repository)
    {
    }

    public function register(array $data)
    {
        $data['password'] = Hash::make($data['password']);

        return $this->repository->create($data);
    }

    public function login(array $data)
    {
        $user = $this->repository->findByEmail($data['email']);

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return null;
        }

        return $user;
    }

    public function createOrUpdateLoginToken($user): string
    {
        $plainTextToken = Str::random(40);
        $tokenHash = hash('sha256', $plainTextToken);

        $token = $user->tokens()->where('name', 'api-token')->first();

        if ($token) {
            $token->forceFill([
                'token' => $tokenHash,
                'abilities' => ['*'],
                'updated_at' => Date::now(),
            ])->save();
        } else {
            $token = $user->tokens()->create([
                'name' => 'api-token',
                'token' => $tokenHash,
                'abilities' => ['*'],
            ]);
        }

        return $token->id . '|' . $plainTextToken;
    }
}
