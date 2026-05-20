<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GoogleAuthRequest;
use App\Services\GoogleAuthService;
use App\Support\ApiResponse;
use Throwable;

class AuthController extends Controller
{
    public function __construct(private readonly GoogleAuthService $googleAuth)
    {
    }

    public function google(GoogleAuthRequest $request)
    {
        try {
            $user = $this->googleAuth->authenticate($request->validated());
            $token = $user->createToken('chrome-extension')->plainTextToken;

            return ApiResponse::success([
                'token' => $token,
                'user' => [
                    'uuid' => $user->uuid,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                ],
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return ApiResponse::error('Falha na autenticação Google.', 500);
        }
    }
}
