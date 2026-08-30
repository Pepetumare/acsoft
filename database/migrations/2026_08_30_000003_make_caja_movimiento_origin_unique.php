<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caja_movimientos', function (Blueprint $table) {
            $table->unique(
                ['origen_tipo', 'origen_id'],
                'caja_movimientos_origen_unique'
            );
            $table->dropIndex(['origen_tipo', 'origen_id']);
        });
    }

    public function down(): void
    {
        Schema::table('caja_movimientos', function (Blueprint $table) {
            $table->index(['origen_tipo', 'origen_id']);
            $table->dropUnique('caja_movimientos_origen_unique');
        });
    }
};
