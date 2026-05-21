<?php

namespace App\Http\Controllers\Api;

use App\DTOs\DownloadRequestData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DownloadRequest;
use App\Jobs\ProcessDownloadJob;
use App\Services\DownloadService;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class DownloadController extends Controller
{
    public function __construct(private readonly DownloadService $downloads)
    {
    }

    public function xml(DownloadRequest $request)
    {
        return $this->handleDownload($request, 'xml');
    }

    public function pdf(DownloadRequest $request)
    {
        return $this->handleDownload($request, 'pdf');
    }

    private function handleDownload(DownloadRequest $request, string $type)
    {
        $data = DownloadRequestData::fromRequest($request, $type);
        $maskedKey = sprintf('%s%s', substr($data->accessKey, 0, 6), str_repeat('*', max(0, strlen($data->accessKey) - 12))) . substr($data->accessKey, -6);

        Log::info('Enfileirando download de nota fiscal.', [
            'user_id' => $request->user()->id,
            'type' => $type,
            'access_key' => $maskedKey,
        ]);

        try {
            $token = (string) Str::uuid();

            // Dispatch job with token to store result in cache when ready.
            ProcessDownloadJob::dispatch(
                $request->user(),
                $data,
                $token,
            );

            return ApiResponse::success([
                'message' => 'Download enfileirado com sucesso. Aguarde a conclusão.',
                'token' => $token,
                'result_url' => url("/download/result/{$token}"),
            ]);
        } catch (RuntimeException $exception) {
            Log::warning('Falha ao enfileirar download de NF-e.', [
                'user_id' => $request->user()->id,
                'type' => $type,
                'access_key' => $maskedKey,
                'message' => $exception->getMessage(),
            ]);

            return ApiResponse::error($exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);
            Log::error('Erro inesperado ao processar download de NF-e.', [
                'user_id' => $request->user()->id,
                'type' => $type,
                'access_key' => $maskedKey,
                'exception' => $exception->getMessage(),
            ]);

            return ApiResponse::error('Falha ao processar download.', 500);
        }
    }

    public function result(string $token)
    {
        $key = "downloads:{$token}";

        $result = Cache::get($key);

        if ($result === null) {
            return ApiResponse::success(['status' => 'pending'], null, 202);
        }

        // One-time retrieval: remove from cache and return binary.
        Cache::forget($key);

        return response($result['content'], 200, [
            'Content-Type' => $result['mime'],
            'Content-Disposition' => 'attachment; filename="'.$result['filename'].'"',
        ]);
    }
}
