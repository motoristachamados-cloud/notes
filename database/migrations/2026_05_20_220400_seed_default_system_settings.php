<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('system_settings')->upsert([
            ['key' => 'credit_price', 'value' => '6', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'provider_cost', 'value' => '3', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'minimum_purchase', 'value' => '50', 'created_at' => now(), 'updated_at' => now()],
        ], ['key'], ['value', 'updated_at']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_settings')->whereIn('key', [
            'credit_price',
            'provider_cost',
            'minimum_purchase',
        ])->delete();
    }
};
