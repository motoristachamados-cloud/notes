<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['uuid', 'user_id', 'access_key', 'type'])]
class AccessKey extends Model
{
    protected static function booted(): void
    {
        static::creating(function (self $accessKey): void {
            if (blank($accessKey->uuid)) {
                $accessKey->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
