<?php

namespace App\Services;

use App\DTOs\DownloadRequestData;
use App\Models\AccessKey;
use App\Models\FinancialTransaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DownloadService
{
    public function __construct(private readonly MeuDanfeService $meuDanfe)
    {
    }

    /**
     * @return array{content: string, mime: string, filename: string}
     */
    public function download(User $user, DownloadRequestData $data): array
    {
        return DB::transaction(function () use ($user, $data): array {
            $wallet = Wallet::query()->where('user_id', $user->id)->lockForUpdate()->first();

            if ($wallet === null) {
                $wallet = Wallet::query()->create([
                    'user_id' => $user->id,
                    'balance' => 0,
                ]);
            }

            $alreadyConsumed = AccessKey::query()
                ->where('user_id', $user->id)
                ->where('access_key', $data->accessKey)
                ->where('type', $data->type)
                ->exists();

            if (! $alreadyConsumed) {
                if ($wallet->balance < 1) {
                    throw new RuntimeException('Saldo insuficiente');
                }

                $wallet->decrement('balance', 1);

                FinancialTransaction::query()->create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => 1,
                    'description' => sprintf('Download %s para chave %s', strtoupper($data->type), $data->accessKey),
                ]);

                AccessKey::query()->create([
                    'user_id' => $user->id,
                    'access_key' => $data->accessKey,
                    'type' => $data->type,
                ]);
            }

            $this->meuDanfe->addFiscalDocument($data->accessKey);

            if ($data->type === 'xml') {
                return [
                    'content' => $this->meuDanfe->getXml($data->accessKey),
                    'mime' => 'application/xml',
                    'filename' => $data->accessKey.'.xml',
                ];
            }

            if ($data->type === 'pdf') {
                return [
                    'content' => $this->meuDanfe->getPdf($data->accessKey),
                    'mime' => 'application/pdf',
                    'filename' => $data->accessKey.'.pdf',
                ];
            }

            throw new RuntimeException('Tipo de download inválido.');
        });
    }
}
