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

        /*
        |--------------------------------------------------------------------------
        | CRIAÇÃO DE PAGAMENTO PIX (API v1)
        |--------------------------------------------------------------------------
        */

        $response = $this->http->withToken($token)->acceptJson()->post('https://api.mercadopago.com/v1/payments', [
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
            throw new RuntimeException('Falha ao gerar pagamento Pix no Mercado Pago: ' . $response->body());
        }

        $paymentData = $response->json();
        $paymentId = (string) $paymentData['id'];
        $qrCode = $paymentData['point_of_interaction']['transaction_data']['qr_code'] ?? '';
        $qrCodeBase64 = $paymentData['point_of_interaction']['transaction_data']['qr_code_base64'] ?? '';
        $ticketUrl = $paymentData['point_of_interaction']['transaction_data']['ticket_url'] ?? '';

        /*
        |--------------------------------------------------------------------------
        | PERSISTÊNCIA NO BANCO (STATUS PENDING)
        |--------------------------------------------------------------------------
        */
        FinancialTransaction::query()->create([
            'user_id' => $user->id,
            'type' => 'credit',
            'amount' => $credits,
            'description' => "Recarga de créditos (Pix)",
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
            'copy_link' => $qrCode,
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
        DB::transaction(function () use ($userId, $credits, $paymentId): void {
            $wallet = Wallet::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if ($wallet === null) {
                $wallet = Wallet::query()->create([
                    'user_id' => $userId,
                    'balance' => 0,
                ]);
            }

            // Busca a transação existente criada no createPayment
            $transaction = FinancialTransaction::query()
                ->where('mercadopago_payment_id', $paymentId)
                ->lockForUpdate()
                ->first();

            // Se já estiver aprovada, não faz nada
            if ($transaction && $transaction->status === 'approved') {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | ADICIONA SALDO
            |--------------------------------------------------------------------------
            */
            $wallet->increment('balance', $credits);

            if ($transaction) {
                $transaction->update(['status' => 'approved']);
            } else {
                FinancialTransaction::query()->create([
                    'user_id' => $userId,
                    'type' => 'credit',
                    'amount' => $credits,
                    'description' => 'Recarga de créditos via Mercado Pago',
                    'mercadopago_payment_id' => $paymentId,
                    'status' => 'approved',
                ]);
            }
        });
    }
}
