<?php

namespace App\Modules\User;

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

    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function update($id, array $data)
    {
        $user = $this->find($id);

        if ($user) {
            $user->update($data);
        }

        return $user;
    }

    public function delete($id)
    {
        return User::destroy($id);
    }
}
