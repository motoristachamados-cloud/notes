<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(private readonly WalletService $walletService)
    {
    }

    public function __invoke(Request $request)
    {
        $balance = $this->walletService->getCurrentBalance($request->user());

        return ApiResponse::success([
            'balance' => $balance,
        ]);
    }
}
