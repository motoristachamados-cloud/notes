<?php

namespace App\Services;

use App\DTOs\DownloadRequestData;
use App\Models\AccessKey;
use App\Models\User;
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
            $alreadyConsumed = AccessKey::query()
                ->where('user_id', $user->id)
                ->where('access_key', $data->accessKey)
                ->where('type', $data->type)
                ->exists();

            if (! $alreadyConsumed) {
                try {
                    AccessKey::query()->create([
                        'user_id' => $user->id,
                        'access_key' => $data->accessKey,
                        'type' => $data->type,
                    ]);
                } catch (\Illuminate\Database\QueryException $exception) {
                    if (! str_contains($exception->getMessage(), 'uq_access_key_user_type')) {
                        throw $exception;
                    }
                }
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
