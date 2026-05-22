<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class PaymentService
{
    public function __construct(
        private readonly SystemSettingsService $settings,
        private readonly HttpFactory $http,
    ) {
    }

    /**
     * @return array{
     *     credits: int,
     *     amount_cents: int,
     *     checkout_url: string
     * }
     */
    public function createPayment(User $user, int $credits): array
    {
        $minimum = $this->settings->getInt('minimum_purchase', 50);

        if ($credits < $minimum) {
            throw new InvalidArgumentException(
                'Quantidade mínima de créditos não atendida.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PREÇO EM CENTAVOS
        |--------------------------------------------------------------------------
        |
        | Exemplo:
        | 100 = R$ 1,00
        | 500 = R$ 5,00
        |
        */

        $creditPrice = $this->settings->getInt('credit_price', 6);

        $amountCents = $credits * $creditPrice;

        $token = (string) config('services.mercadopago.access_token');

        if ($token === '') {
            throw new RuntimeException(
                'Mercado Pago não configurado no ambiente.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | URL OFICIAL DO SISTEMA
        |--------------------------------------------------------------------------
        */

        $appUrl = rtrim(
            (string) config('app.url'),
            '/'
        );

        /*
        |--------------------------------------------------------------------------
        | WEBHOOK OFICIAL
        |--------------------------------------------------------------------------
        */

        $webhookUrl = (string) (
            config('services.mercadopago.webhook_url')
            ?: $appUrl . '/webhooks/mercado-pago'
        );

        /*
        |--------------------------------------------------------------------------
        | URL DE RETORNO
        |--------------------------------------------------------------------------
        */

        $returnUrl = $appUrl . '/billing';

        $response = $this->http
            ->withToken($token)
            ->acceptJson()
            ->post('https://api.mercadopago.com/checkout/preferences', [
                'items' => [
                    [
                        'title' => 'Créditos EBD SYSTEMS',
                        'quantity' => $credits,

                        /*
                        |--------------------------------------------------------------------------
                        | Mercado Pago trabalha em reais
                        |--------------------------------------------------------------------------
                        */

                        'unit_price' => round($creditPrice / 100, 2),

                        'currency_id' => 'BRL',
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | REFERÊNCIA DO USUÁRIO
                |--------------------------------------------------------------------------
                */

                'external_reference' => (string) $user->id,

                /*
                |--------------------------------------------------------------------------
                | METADATA
                |--------------------------------------------------------------------------
                */

                'metadata' => [
                    'credits' => $credits,
                    'user_id' => $user->id,
                ],

                /*
                |--------------------------------------------------------------------------
                | WEBHOOK
                |--------------------------------------------------------------------------
                */

                'notification_url' => $webhookUrl,

                /*
                |--------------------------------------------------------------------------
                | REDIRECTS
                |--------------------------------------------------------------------------
                */

                'back_urls' => [
                    'success' => $returnUrl,
                    'pending' => $returnUrl,
                    'failure' => $returnUrl,
                ],

                'auto_return' => 'approved',
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Falha ao criar preferência de pagamento no Mercado Pago.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | AMBIENTE
        |--------------------------------------------------------------------------
        */

        $mode = (string) (
            config('services.mercadopago.mode')
            ?: 'production'
        );

        $checkoutUrl = $mode !== 'production'
            ? $response->json('sandbox_init_point')
            : $response->json('init_point');

        if (! is_string($checkoutUrl) || $checkoutUrl === '') {
            throw new RuntimeException(
                'URL de checkout do Mercado Pago não foi retornada.'
            );
        }

        return [
            'credits' => $credits,
            'amount_cents' => $amountCents,
            'checkout_url' => $checkoutUrl,
        ];
    }

    public function handleWebhook(string $paymentId): void
    {
        $token = (string) config('services.mercadopago.access_token');

        if ($token === '') {
            throw new RuntimeException(
                'Mercado Pago não configurado no ambiente.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CONSULTA PAGAMENTO
        |--------------------------------------------------------------------------
        */

        $response = $this->http
            ->withToken($token)
            ->acceptJson()
            ->get(
                'https://api.mercadopago.com/v1/payments/' . $paymentId
            );

        if ($response->failed()) {

    /*
    |--------------------------------------------------------------------------
    | Mercado Pago envia IDs fictícios em testes webhook.
    |--------------------------------------------------------------------------
    */

    if ($response->status() === 404) {
        return;
    }

    throw new RuntimeException(
        'Não foi possível validar pagamento no Mercado Pago.'
    );

        }

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        $status = (string) $response->json('status');

        if ($status !== 'approved') {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | DADOS PAGAMENTO
        |--------------------------------------------------------------------------
        */

        $userId = (int) $response->json('external_reference');

        $credits = (int) $response->json(
            'metadata.credits',
            0
        );

        if ($userId <= 0 || $credits <= 0) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | TRANSAÇÃO
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $userId,
            $credits,
            $paymentId
        ): void {

            $wallet = Wallet::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            /*
            |--------------------------------------------------------------------------
            | CRIA CARTEIRA
            |--------------------------------------------------------------------------
            */

            if ($wallet === null) {
                $wallet = Wallet::query()->create([
                    'user_id' => $userId,
                    'balance' => 0,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | EVITA DUPLICIDADE
            |--------------------------------------------------------------------------
            */

            $alreadyCredited = FinancialTransaction::query()
                ->where('mercadopago_payment_id', $paymentId)
                ->where('type', 'credit')
                ->exists();

            if ($alreadyCredited) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | ADICIONA SALDO
            |--------------------------------------------------------------------------
            */

            $wallet->increment('balance', $credits);

            /*
            |--------------------------------------------------------------------------
            | REGISTRA TRANSAÇÃO
            |--------------------------------------------------------------------------
            */

            FinancialTransaction::query()->create([
                'user_id' => $userId,
                'type' => 'credit',
                'amount' => $credits,
                'description' => 'Recarga de créditos via Mercado Pago',
                'mercadopago_payment_id' => $paymentId,
            ]);
        });
    }
}