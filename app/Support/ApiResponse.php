<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(array $data = [], ?string $message = null, int $status = 200): JsonResponse
    {
        $payload = ['success' => true] + $data;

        if ($message !== null) {
            $payload['message'] = $message;
        }

        return response()->json($payload, $status);
    }

    public static function error(string $message, int $status = 422, array $data = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ] + $data, $status);
    }
}
