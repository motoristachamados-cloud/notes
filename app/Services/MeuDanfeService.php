<?php

namespace App\Services;

use Illuminate\Http\Client\Factory as HttpFactory;
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
            throw new RuntimeException('Falha ao registrar documento no provedor MeuDanfe.');
        }
    }

    public function getXml(string $accessKey): string
    {
        $response = $this->client()->get('/v2/fd/get/xml/'.$accessKey);

        if ($response->failed()) {
            throw new RuntimeException('Falha ao baixar XML no provedor MeuDanfe.');
        }

        return $response->body();
    }

    public function getPdf(string $accessKey): string
    {
        $response = $this->client()->get('/v2/fd/get/da/'.$accessKey);

        if ($response->failed()) {
            throw new RuntimeException('Falha ao baixar PDF no provedor MeuDanfe.');
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
                'Authorization' => 'Bearer '.$apiKey,
                'Accept' => 'application/json',
            ]);
    }
}
