<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caja_movimientos', function (Blueprint $table) {
            $table->uuid('operation_token')->nullable()->after('origen_id');
            $table->unique(
                ['caja_id', 'operation_token'],
                'caja_movimientos_caja_operation_token_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('caja_movimientos', function (Blueprint $table) {
            $table->dropUnique('caja_movimientos_caja_operation_token_unique');
            $table->dropColumn('operation_token');
        });
    }
};
