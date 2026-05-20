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

        return [
            'credits' => $credits,
            'amount_cents' => $credits * $creditPrice,
            'checkout_url' => null,
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
