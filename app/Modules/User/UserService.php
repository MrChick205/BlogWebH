<?php

namespace App\Modules\User;

use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(private UserRepository $repository)
    {
    }

    public function register(array $data)
    {
        $data['password'] = Hash::make($data['password']);

        return $this->repository->create($data);
    }

    public function login(string $email, string $password)
    {
        $user = $this->repository->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    public function getAllUsers()
    {
        return $this->repository->all();
    }

    public function update($id, array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->repository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }
}
