<?php

namespace App\Http\Controllers\Api;

use App\DTOs\DownloadRequestData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DownloadRequest;
use App\Services\DownloadService;
use App\Support\ApiResponse;
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
        try {
            $result = $this->downloads->download(
                $request->user(),
                DownloadRequestData::fromRequest($request, $type),
            );

            return response($result['content'], 200, [
                'Content-Type' => $result['mime'],
                'Content-Disposition' => 'attachment; filename="'.$result['filename'].'"',
            ]);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return ApiResponse::error('Falha ao processar download.', 500);
        }
    }
}
