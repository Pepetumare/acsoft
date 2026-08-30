<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venta_contadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')
                ->unique()
                ->constrained('negocios')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('ultimo_numero')->default(0);
            $table->timestamps();
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->unsignedBigInteger('numero_documento_interno')
                ->nullable()
                ->after('user_id');
            $table->uuid('operation_token')
                ->nullable()
                ->after('observacion');

            $table->unique(
                ['negocio_id', 'numero_documento_interno'],
                'ventas_negocio_numero_interno_unique'
            );
            $table->unique(
                ['negocio_id', 'operation_token'],
                'ventas_negocio_operation_token_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropUnique('ventas_negocio_numero_interno_unique');
            $table->dropUnique('ventas_negocio_operation_token_unique');
            $table->dropColumn([
                'numero_documento_interno',
                'operation_token',
            ]);
        });

        Schema::dropIfExists('venta_contadores');
    }
};
