<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->uuid('operation_token')
                ->nullable()
                ->after('observacion');

            $table->unique(
                ['negocio_id', 'operation_token'],
                'compras_negocio_operation_token_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropUnique('compras_negocio_operation_token_unique');
            $table->dropColumn('operation_token');
        });
    }
};
