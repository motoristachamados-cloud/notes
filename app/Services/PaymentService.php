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
    ) {}

    /**
     * @return array{
     *     credits: int,
     *     amount_cents: int,
     *     payment_id: string,
     *     qr_code: string,
     *     qr_code_base64: string,
     *     pix_copy_paste: string,
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
        | CRIAÇÃO DE PAGAMENTO PIX (API v1)
        |--------------------------------------------------------------------------
        */

        /** @var \Illuminate\Http\Client\Response $response */
        $response = $this->http
            ->withToken($token)
            ->acceptJson()
            ->post('https://api.mercadopago.com/v1/payments', [
                'transaction_amount' => round($amountCents / 100, 2),
                'description' => "Recarga de {$credits} créditos",
                'payment_method_id' => 'pix',
                'external_reference' => (string) $user->id,

                'notification_url' => $webhookUrl,

                'payer' => [
                    'email' => $user->email,
                    'first_name' => explode(' ', $user->name)[0],
                ],

                'metadata' => [
                    'credits' => $credits,
                    'user_id' => $user->id,
                ],
            ]);

        if ($response->failed()) {
            logger()->error('Erro ao criar pagamento PIX no Mercado Pago.', [
                'status' => $response?->status(),
                'headers' => $response?->headers(),
                'body' => $response?->body(),
                'json' => $response?->json(),
                'credits' => $credits,
                'amount_cents' => $amountCents,
                'user_id' => $user->id,
                'webhook_url' => $webhookUrl,
            ]);

            throw new RuntimeException(sprintf(
                'Mercado Pago retornou erro HTTP %s ao criar pagamento PIX.',
                $response->status()
            ));
        }

        $paymentData = $response->json();

        $paymentId = (string) ($paymentData['id'] ?? '');

        if ($paymentId === '') {
            throw new RuntimeException(
                'O Mercado Pago não retornou um ID de pagamento válido.'
            );
        }

        logger()->info('Pagamento PIX criado com sucesso.', [
            'payment_id' => $paymentId,
            'status' => $response?->status(),
            'user_id' => $user->id,
            'amount_cents' => $amountCents,
        ]);

        $transactionData = $paymentData['point_of_interaction']['transaction_data'] ?? [];

        $qrCode = (string) ($transactionData['qr_code'] ?? '');

        $qrCodeBase64 = (string) ($transactionData['qr_code_base64'] ?? '');

        $ticketUrl = (string) ($transactionData['ticket_url'] ?? '');

        /*
        |--------------------------------------------------------------------------
        | PERSISTÊNCIA NO BANCO (STATUS PENDING)
        |--------------------------------------------------------------------------
        */

        FinancialTransaction::query()->create([
            'user_id' => $user->id,
            'type' => 'credit',
            'amount' => $credits,
            'description' => 'Recarga de créditos (Pix)',
            'mercadopago_payment_id' => $paymentId,
            'status' => 'pending',
            'pix_qr_code' => $qrCode,
            'pix_qr_code_base64' => $qrCodeBase64,
            'external_link' => $ticketUrl,
        ]);

        return [
            'credits' => $credits,
            'amount_cents' => $amountCents,
            'payment_id' => $paymentId,
            'qr_code' => $qrCode,
            'qr_code_base64' => $qrCodeBase64,
            'pix_copy_paste' => $qrCode,
            'checkout_url' => $ticketUrl,
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

        /** @var \Illuminate\Http\Client\Response $response */
        $response = $this->http
            ->withToken($token)
            ->acceptJson()
            ->get(
                'https://api.mercadopago.com/v1/payments/' . $paymentId
            );

        /*
        |--------------------------------------------------------------------------
        | Mercado Pago envia IDs fictícios em testes webhook
        |--------------------------------------------------------------------------
        */

        if ($response->status() === 404) {
            return;
        }

        if ($response->failed()) {
            logger()->error('Erro ao validar webhook Mercado Pago.', [
                'payment_id' => $paymentId,
                'status' => $response?->status(),
                'headers' => $response?->headers(),
                'body' => $response?->body(),
                'json' => $response?->json(),
            ]);

            throw new RuntimeException(sprintf(
                'Mercado Pago HTTP %s: %s',
                $response->status(),
                $response->body()
            ));
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
            | TRANSAÇÃO EXISTENTE
            |--------------------------------------------------------------------------
            */

            $transaction = FinancialTransaction::query()
                ->where('mercadopago_payment_id', $paymentId)
                ->lockForUpdate()
                ->first();

            /*
            |--------------------------------------------------------------------------
            | EVITA DUPLICIDADE
            |--------------------------------------------------------------------------
            */

            if ($transaction && $transaction->status === 'approved') {
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
            | ATUALIZA OU CRIA TRANSAÇÃO
            |--------------------------------------------------------------------------
            */

            if ($transaction) {

                $transaction->update([
                    'status' => 'approved',
                ]);

                return;
            }

            FinancialTransaction::query()->create([
                'user_id' => $userId,
                'type' => 'credit',
                'amount' => $credits,
                'description' => 'Recarga de créditos via Mercado Pago',
                'mercadopago_payment_id' => $paymentId,
                'status' => 'approved',
            ]);
        });
    }
}