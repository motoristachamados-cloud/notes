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
        /**
         * 1. Verificar se o documento já foi pago anteriormente por este usuário.
         */
        $alreadyConsumed = AccessKey::query()
            ->where('user_id', $user->id)
            ->where('access_key', $data->accessKey)
            ->where('type', $data->type)
            ->exists();

        if (! $alreadyConsumed) {
            /**
             * 2. Verificação prévia de saldo (sem lock) para evitar chamadas de API inúteis.
             */
            $balance = Wallet::query()->where('user_id', $user->id)->value('balance') ?? 0;
            if ($balance < 1) {
                throw new RuntimeException('Saldo insuficiente para iniciar o download.');
            }

            /**
             * 3. Chamar a API externa primeiro (Garantir processamento no MeuDanfe).
             * Se a API falhar, o erro sobe, o Job fará retry (se erro temporário)
             * e o débito não ocorre.
             */
            $this->meuDanfe->addFiscalDocument($data->accessKey);
        }

        /**
         * 4. Buscar conteúdo (XML ou PDF).
         */
        $content = match ($data->type) {
            'xml' => $this->meuDanfe->getXml($data->accessKey),
            'pdf' => $this->meuDanfe->getPdf($data->accessKey),
            default => throw new RuntimeException('Tipo de download inválido.'),
        };

        /**
         * 5. Se o download foi bem sucedido e não foi pago anteriormente, realizar débito.
         */
        if (! $alreadyConsumed) {
            DB::transaction(function () use ($user, $data) {
                /**
                 * Re-verificar consumo dentro da transação para lidar com concorrência.
                 */
                $exists = AccessKey::query()
                    ->where('user_id', $user->id)
                    ->where('access_key', $data->accessKey)
                    ->where('type', $data->type)
                    ->exists();

                if ($exists) {
                    return;
                }

                /**
                 * Bloquear carteira para atualização (lockForUpdate).
                 */
                $wallet = Wallet::query()
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if (! $wallet || $wallet->balance < 1) {
                    throw new RuntimeException('Saldo insuficiente para finalizar o download.');
                }

                /**
                 * Efetivar débito e registrar acesso.
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
            });
        }

        return [
            'content' => $content,
            'mime' => $data->type === 'xml' ? 'application/xml' : 'application/pdf',
            'filename' => $data->accessKey . ($data->type === 'xml' ? '.xml' : '.pdf'),
        ];
    }
}
