<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class UserService
{
    public static function getAllUsers()
    {
        return User::all();
    }

    public static function getUserById($id)
    {
        return User::find($id);
    }

    public static function createUser($data)
    {
        $data = self::normalizePhone($data);

        if (isset($data['email']) && User::where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'The email has already been taken.',
            ]);
        }

        if (isset($data['phone']) && User::where('phone', $data['phone'])->exists()) {
            throw ValidationException::withMessages([
                'phone' => 'The phone has already been taken.',
            ]);
        }

        return User::create($data);
    }

    public static function updateUser($id, $data)
    {
        $user = User::find($id);
        if ($user) {
            $user->update(self::normalizePhone($data));
        }

        return $user;
    }

    public static function deleteUser($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
        }

        return $user;
    }

    public static function normalizePhone(array $data): array
    {
        if (! isset($data['phone'])) {
            return $data;
        }

        if (! is_string($data['phone'])) {
            throw ValidationException::withMessages([
                'phone' => 'The phone must be a string.',
            ]);
        }

        $phone = preg_replace('/[^0-9]/', '', $data['phone']);

        if ($phone === '') {
            throw ValidationException::withMessages([
                'phone' => 'The phone must contain at least one digit.',
            ]);
        }

        $data['phone'] = $phone;

        return $data;
    }

    public static function loginUser($credentials)
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! password_verify($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        return $user;
    }
}
