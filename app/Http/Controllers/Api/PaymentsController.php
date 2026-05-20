<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreatePaymentRequest;
use App\Http\Requests\Api\PaymentWebhookRequest;
use App\Services\PaymentService;
use App\Support\ApiResponse;
use InvalidArgumentException;
use Throwable;

class PaymentsController extends Controller
{
    public function __construct(private readonly PaymentService $payments)
    {
    }

    public function create(CreatePaymentRequest $request)
    {
        try {
            $data = $this->payments->createPayment(
                $request->user(),
                (int) $request->integer('credits'),
            );

            return ApiResponse::success($data);
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::error($exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return ApiResponse::error('Falha ao criar pagamento.', 500);
        }
    }

    public function webhook(PaymentWebhookRequest $request)
    {
        try {
            $this->payments->handleWebhook($request->string('payment_id')->toString());

            return ApiResponse::success();
        } catch (Throwable $exception) {
            report($exception);

            return ApiResponse::error('Falha ao processar webhook.', 500);
        }
    }
}
