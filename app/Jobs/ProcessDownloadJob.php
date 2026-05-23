<?php

namespace App\Jobs;

use App\DTOs\DownloadRequestData;
use App\Models\User;
use App\Services\DownloadService;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use App\Support\Masker;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessDownloadJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Quantidade máxima de tentativas.
     */
    public int $tries = 8;

    /**
     * Quantidade máxima de exceções antes da falha definitiva.
     */
    public int $maxExceptions = 3;

    /**
     * Timeout máximo do job.
     */
    public int $timeout = 120;

    public function __construct(
        public readonly User $user,
        public readonly DownloadRequestData $data,
        public readonly string $token,
    ) {
        $this->onQueue('downloads');
    }

    /**
     * Middleware de rate limit global.
     */
    public function middleware(): array
    {
        return [
            new RateLimited('meudanfe-downloads'),
        ];
    }

    /**
     * Backoff progressivo.
     */
    public function backoff(): array
    {
        return [
            5,
            10,
            30,
            60,
            120,
        ];
    }

    /**
     * Define tempo máximo total do retry.
     */
    public function retryUntil(): DateTimeInterface
    {
        return now()->addMinutes(10);
    }

    /**
     * Processa o download.
     */
    public function handle(DownloadService $downloadService): void
    {
        $maskedAccessKey = Masker::mask($this->data->accessKey);

        Log::info('Iniciando processamento de download.', [
            'user_id' => $this->user->id,
            'type' => $this->data->type,
            'access_key' => $maskedAccessKey,
        ]);

        /**
         * Lock global por chave fiscal + tipo.
         *
         * Impede downloads concorrentes duplicados
         * da mesma NF-e/CT-e.
         */
        $lockKey = sprintf(
            'download-lock:%s:%s',
            $this->data->type,
            $this->data->accessKey
        );

        $lock = Cache::lock($lockKey, 180);

        /**
         * Se já existir processamento em andamento,
         * apenas libera o job para tentar novamente depois.
         */
        if (! $lock->get()) {
            Log::warning('Download já está sendo processado.', [
                'user_id' => $this->user->id,
                'type' => $this->data->type,
                'access_key' => $maskedAccessKey,
            ]);

            $this->release(5);

            return;
        }

        try {
            /**
             * Executa integração MeuDanfe.
             */
            $result = $downloadService->download(
                $this->user,
                $this->data
            );

            /**
             * Resultado temporário em cache.
             *
             * Conforme ADR:
             * - sem persistência em disco
             * - sem persistência fiscal
             */
            Cache::put(
                "downloads:{$this->token}",
                $result,
                now()->addMinutes(10)
            );

            Log::info('Download processado com sucesso.', [
                'user_id' => $this->user->id,
                'type' => $this->data->type,
                'access_key' => $maskedAccessKey,
            ]);
        } catch (Throwable $exception) {

            /**
             * Falhas permanentes NÃO devem retry.
             */
            $message = mb_strtolower($exception->getMessage());

            $nonRetryableErrors = [
                '404',
                'not found',
                'não encontrado',
                'invalid',
                'inválido',
                'access key',
                'chave',
                'xml inexistente',
                'pdf inexistente',
            ];

            foreach ($nonRetryableErrors as $error) {
                if (str_contains($message, $error)) {

                    Log::error('Falha permanente detectada.', [
                        'user_id' => $this->user->id,
                        'type' => $this->data->type,
                        'message' => $exception->getMessage(),
                    ]);

                    $this->fail($exception);

                    return;
                }
            }

            /**
             * Falha temporária:
             * deixa Laravel retry automaticamente.
             */
            Log::warning('Falha temporária no download.', [
                'user_id' => $this->user->id,
                'type' => $this->data->type,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        } finally {

            /**
             * Liberação obrigatória do lock.
             */
            optional($lock)->release();
        }
    }

    /**
     * Executado após falha definitiva.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Falha definitiva ao processar download.', [
            'user_id' => $this->user->id,
            'type' => $this->data->type,
            'message' => $exception->getMessage(),
        ]);

        /**
         * Cache temporário de erro para frontend consultar.
         */
        Cache::put(
            "downloads:{$this->token}",
            [
                'success' => false,
                'message' => 'Falha ao processar download.',
            ],
            now()->addMinutes(10)
        );
    }
}
