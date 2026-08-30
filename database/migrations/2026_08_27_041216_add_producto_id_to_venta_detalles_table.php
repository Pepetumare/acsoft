<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venta_detalles', function (Blueprint $table) {

            $table->foreignId('producto_id')
                ->nullable()
                ->after('venta_id')
                ->constrained('productos')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('venta_detalles', function (Blueprint $table) {

            $table->dropConstrainedForeignId(
                'producto_id'
            );
        });
    }
};