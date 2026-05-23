<?php

namespace App\Support;

class Masker
{
    /**
     * Mascara uma chave de acesso para exibição segura em logs.
     */
    public static function mask(?string $accessKey): string
    {
        if (!$accessKey) {
            return 'null';
        }

        $length = strlen($accessKey);

        if ($length < 12) {
            return str_repeat('*', $length);
        }

        return substr($accessKey, 0, 6) . '...' . substr($accessKey, -6);
    }
}