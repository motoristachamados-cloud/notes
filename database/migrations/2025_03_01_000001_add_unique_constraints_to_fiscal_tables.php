<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Os índices únicos já foram definidos nas migrations de criação das tabelas.
    }

    public function down(): void
    {
    }
};