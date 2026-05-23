<?php

namespace App\Services;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use App\Support\Masker;
use RuntimeException;

class MeuDanfeService
{
    public function __construct(private readonly HttpFactory $http) {}

    public function addFiscalDocument(string $accessKey): void
    {
        $response = $this->client()->put('/v2/fd/add/' . $accessKey);

        if ($response->failed()) {
            Log::warning('Falha ao registrar documento no MeuDanfe.', [
                'status' => $response->status(),
                'body' => $response->body(),
                'access_key' => Masker::mask($accessKey),
            ]);

            $this->handleFailure($response, 'adicionar documento');
        }
    }

    public function getXml(string $accessKey): string
    {
        $response = $this->client()->get('/v2/fd/get/xml/' . $accessKey);

        if ($response->failed()) {
            Log::warning('Falha ao buscar XML no MeuDanfe.', [
                'status' => $response->status(),
                'body' => $response->body(),
                'access_key' => Masker::mask($accessKey),
            ]);

            $this->handleFailure($response, 'baixar XML');
        }

        return $response->body();
    }

    public function getPdf(string $accessKey): string
    {
        $response = $this->client()->get('/v2/fd/get/da/' . $accessKey);

        if ($response->failed()) {
            Log::warning('Falha ao buscar PDF no MeuDanfe.', [
                'status' => $response->status(),
                'body' => $response->body(),
                'access_key' => Masker::mask($accessKey),
            ]);

            $this->handleFailure($response, 'baixar PDF');
        }

        return $response->body();
    }

    /**
     * Centraliza o tratamento de falhas da API.
     */
    private function handleFailure(Response $response, string $action): never
    {
        if ($response->status() === 401) {
            throw new RuntimeException("Erro de autenticação no MeuDanfe ao $action. Verifique a API Key.");
        }

        $message = null;
        try {
            $json = $response->json();
            $message = is_array($json) ? ($json['message'] ?? null) : null;
        } catch (\Throwable) {
            $message = null;
        }
        $message ??= $response->body();

        throw new RuntimeException(sprintf(
            "Erro na API MeuDanfe ao $action: %s (%s)",
            $response->status(),
            $message,
        ));
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
            ]);
    }
}
