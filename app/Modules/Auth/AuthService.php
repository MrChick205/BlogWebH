<?php

namespace App\Modules\Auth;

use App\Modules\User\UserRepository;
use Illuminate\Support\Facades\Hash;

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
}
