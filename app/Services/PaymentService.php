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
     * @return array{credits: int, amount_cents: int, checkout_url: string|null}
     */
    public function createPayment(User $user, int $credits): array
    {
        $minimum = $this->settings->getInt('minimum_purchase', 50);

        if ($credits < $minimum) {
            throw new InvalidArgumentException('Quantidade mínima de créditos não atendida.');
        }

        $creditPrice = $this->settings->getInt('credit_price', 6);

        $amountCents = $credits * $creditPrice;
        $token = (string) config('services.mercadopago.access_token');

        if ($token === '') {
            throw new RuntimeException('Mercado Pago não configurado no ambiente.');
        }

        $response = $this->http->withToken($token)
            ->acceptJson()
            ->post('https://api.mercadopago.com/checkout/preferences', [
                'items' => [
                    [
                        'title' => 'Créditos EBD SYSTEMS',
                        'quantity' => $credits,
                        'unit_price' => round($creditPrice / 100, 2),
                        'currency_id' => 'BRL',
                    ],
                ],
                'external_reference' => (string) $user->id,
                'metadata' => [
                    'credits' => $credits,
                ],
                'notification_url' => config('services.mercadopago.webhook_url', rtrim(config('app.url'), '/') . '/api/payments/webhook'),
                'back_urls' => [
                    'success' => rtrim(config('app.url'), '/') . '/',
                    'pending' => rtrim(config('app.url'), '/') . '/',
                    'failure' => rtrim(config('app.url'), '/') . '/',
                ],
                'auto_return' => 'approved',
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Falha ao criar preferências de pagamento no Mercado Pago.');
        }

        $mode = config('services.mercadopago.mode', env('MERCADO_PAGO_MODE', 'production'));
        $checkoutUrl = $mode !== 'production'
            ? $response->json('sandbox_init_point')
            : $response->json('init_point');

        if (! is_string($checkoutUrl) || $checkoutUrl === '') {
            throw new RuntimeException('URL de checkout do Mercado Pago não foi retornada.');
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
            throw new RuntimeException('Mercado Pago não configurado no ambiente.');
        }

        $response = $this->http->withToken($token)
            ->acceptJson()
            ->get('https://api.mercadopago.com/v1/payments/'.$paymentId);

        if ($response->failed()) {
            throw new RuntimeException('Não foi possível validar pagamento no Mercado Pago.');
        }

        $status = (string) $response->json('status');

        if ($status !== 'approved') {
            return;
        }

        $userId = (int) $response->json('external_reference');
        $credits = (int) $response->json('metadata.credits', 0);

        if ($userId <= 0 || $credits <= 0) {
            return;
        }

        DB::transaction(function () use ($userId, $credits, $paymentId): void {
            $wallet = Wallet::query()->where('user_id', $userId)->lockForUpdate()->first();

            if ($wallet === null) {
                $wallet = Wallet::query()->create([
                    'user_id' => $userId,
                    'balance' => 0,
                ]);
            }

            $alreadyCredited = FinancialTransaction::query()
                ->where('mercadopago_payment_id', $paymentId)
                ->where('type', 'credit')
                ->exists();

            if ($alreadyCredited) {
                return;
            }

            $wallet->increment('balance', $credits);

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
