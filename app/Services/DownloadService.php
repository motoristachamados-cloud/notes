<?php

namespace App\Services;

use App\DTOs\DownloadRequestData;
use App\Models\FinancialTransaction;
use App\Models\AccessKey;
use App\Models\Wallet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DownloadService
{
    public function __construct(
        private readonly MeuDanfeService $meuDanfe
    ) {}

    /**
     * @return array{content: string, mime: string, filename: string}
     */
    public function download(User $user, DownloadRequestData $data): array
    {
        return DB::transaction(function () use ($user, $data): array {
            /**
             * 1. Verificar se o documento já foi pago anteriormente.
             */
            $alreadyConsumed = AccessKey::query()
                ->where('user_id', $user->id)
                ->where('access_key', $data->accessKey)
                ->where('type', $data->type)
                ->exists();

            if (! $alreadyConsumed) {
                /**
                 * 2. Bloquear carteira para atualização (lockForUpdate).
                 * Evita concorrência e saldo negativo.
                 */
                $wallet = Wallet::query()
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if (!$wallet || $wallet->balance < 1) {
                    throw new RuntimeException('Saldo insuficiente para realizar o download.');
                }

                /**
                 * 3. Registrar o consumo e realizar o débito.
                 */
                $wallet->decrement('balance', 1);

                FinancialTransaction::query()->create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => 1,
                    'description' => "Download de documento fiscal ({$data->type})",
                ]);

                AccessKey::query()->create([
                    'user_id' => $user->id,
                    'access_key' => $data->accessKey,
                    'type' => $data->type,
                ]);
            }

            /**
             * 4. Chamar a API externa.
             */
            $this->meuDanfe->addFiscalDocument($data->accessKey);

            if ($data->type === 'xml') {
                return [
                    'content' => $this->meuDanfe->getXml($data->accessKey),
                    'mime' => 'application/xml',
                    'filename' => $data->accessKey . '.xml',
                ];
            }

            if ($data->type === 'pdf') {
                return [
                    'content' => $this->meuDanfe->getPdf($data->accessKey),
                    'mime' => 'application/pdf',
                    'filename' => $data->accessKey . '.pdf',
                ];
            }

            throw new RuntimeException('Tipo de download inválido.');
        });
    }
}
