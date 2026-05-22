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
    public function __construct(
        private readonly PaymentService $payments,
    ) {
    }

    /**
     * Cria pagamento Mercado Pago.
     */
    public function create(CreatePaymentRequest $request)
    {
        try {
            $data = $this->payments->createPayment(
                $request->user(),
                (int) $request->integer('credits'),
            );

            return ApiResponse::success($data);

        } catch (InvalidArgumentException $exception) {
            return ApiResponse::error(
                $exception->getMessage(),
                422,
            );

        } catch (Throwable $exception) {
            report($exception);

            return ApiResponse::error(
                'Falha ao criar pagamento.',
                500,
            );
        }
    }

    /**
     * Webhook oficial Mercado Pago.
     */
    public function webhook(PaymentWebhookRequest $request)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | Mercado Pago envia:
            |--------------------------------------------------------------------------
            |
            | {
            |   "data": {
            |     "id": "123456"
            |   }
            | }
            |
            */

            $paymentId = $request->input('data.id');

            /*
            |--------------------------------------------------------------------------
            | Validação
            |--------------------------------------------------------------------------
            */

            if (
                ! is_string($paymentId)
                && ! is_int($paymentId)
            ) {
                return ApiResponse::error(
                    'Pagamento inválido.',
                    422,
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Processa pagamento
            |--------------------------------------------------------------------------
            */

            $this->payments->handleWebhook(
                (string) $paymentId,
            );

            /*
            |--------------------------------------------------------------------------
            | Resposta webhook
            |--------------------------------------------------------------------------
            |
            | Mercado Pago espera resposta 200.
            |
            */

            return ApiResponse::success([
                'processed' => true,
            ]);

        } catch (Throwable $exception) {

            report($exception);

            return ApiResponse::error(
                'Falha ao processar webhook.',
                500,
            );
        }
    }
}