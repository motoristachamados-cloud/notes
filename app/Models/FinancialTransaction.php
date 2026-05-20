<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['uuid', 'user_id', 'type', 'amount', 'description', 'mercadopago_payment_id'])]
class FinancialTransaction extends Model
{
    protected static function booted(): void
    {
        static::creating(function (self $transaction): void {
            if (blank($transaction->uuid)) {
                $transaction->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
