<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GoogleAuthService
{
    /**
     * @param array{name: string, email: string, google_id: string, avatar: string|null} $payload
     */
    public function authenticate(array $payload): User
    {
        return User::query()->updateOrCreate(
            ['email' => $payload['email']],
            [
                'name' => $payload['name'],
                'google_id' => $payload['google_id'],
                'avatar' => $payload['avatar'],
                'password' => Hash::make(Str::password(32)),
            ],
        );
    }
}
