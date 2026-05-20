<?php

namespace App\DTOs;

use App\Http\Requests\Api\DownloadRequest;

readonly class DownloadRequestData
{
    public function __construct(
        public string $accessKey,
        public string $type,
    ) {
    }

    public static function fromRequest(DownloadRequest $request, string $type): self
    {
        return new self(
            accessKey: $request->string('access_key')->toString(),
            type: $type,
        );
    }
}
