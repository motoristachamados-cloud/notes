<?php

namespace App\Services;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MeuDanfeService
{
    public function __construct(private readonly HttpFactory $http)
    {
    }

    public function addFiscalDocument(string $accessKey): void
    {
        $response = $this->client()->put('/v2/fd/add/'.$accessKey);

        if ($response->failed()) {
            Log::warning('MeuDanfe authentication failed when registering fiscal document.', [
                'status' => $response->status(),
                'body' => $response->body(),
                'access_key' => $this->maskKey($accessKey),
            ]);

            throw new RuntimeException(sprintf(
                'Não autenticado no MeuDanfe. Verifique MEUDANFE_API_KEY e MEUDANFE_BASE_URL. (%s %s)',
                $response->status(),
                $response->body(),
            ));
        }
    }

    public function getXml(string $accessKey): string
    {
        $response = $this->client()->get('/v2/fd/get/xml/'.$accessKey);

        if ($response->failed()) {
            Log::warning('MeuDanfe authentication failed when fetching XML.', [
                'status' => $response->status(),
                'body' => $response->body(),
                'access_key' => $this->maskKey($accessKey),
            ]);

            throw new RuntimeException(sprintf(
                'Não autenticado no MeuDanfe ao baixar XML. Verifique a chave da API. (%s %s)',
                $response->status(),
                $response->body(),
            ));
        }

        return $response->body();
    }

    public function getPdf(string $accessKey): string
    {
        $response = $this->client()->get('/v2/fd/get/da/'.$accessKey);

        if ($response->failed()) {
            Log::warning('MeuDanfe authentication failed when fetching PDF.', [
                'status' => $response->status(),
                'body' => $response->body(),
                'access_key' => $this->maskKey($accessKey),
            ]);

            throw new RuntimeException(sprintf(
                'Não autenticado no MeuDanfe ao baixar PDF. Verifique a chave da API. (%s %s)',
                $response->status(),
                $response->body(),
            ));
        }

        return $response->body();
    }

    private function client()
    {
        $apiKey = (string) config('services.meudanfe.api_key');
        $baseUrl = (string) config('services.meudanfe.base_url');

        if ($apiKey === '' || $baseUrl === '') {
            throw new RuntimeException('MeuDanfe não configurado no ambiente.');
        }

        return $this->http->baseUrl(rtrim($baseUrl, '/'))
            ->withHeaders([
                'Api-Key' => $apiKey,
                'Accept' => 'application/json',
            ]);
    }

    private function maskKey(string $accessKey): string
    {
        if (strlen($accessKey) < 12) {
            return str_repeat('*', strlen($accessKey));
        }

        return sprintf(
            '%s%s%s',
            substr($accessKey, 0, 6),
            str_repeat('*', max(0, strlen($accessKey) - 12)),
            substr($accessKey, -6),
        );
    }
}
