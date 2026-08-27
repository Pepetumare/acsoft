<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('negocio_id')
                ->constrained('negocios')
                ->cascadeOnDelete();

            $table->string('nombre');

            $table->string('codigo', 100)
                ->nullable();

            $table->string('unidad', 30)
                ->default('unidad');

            $table->decimal('precio_venta', 12, 2)
                ->nullable();

            $table->decimal('stock_minimo', 12, 3)
                ->nullable();

            $table->boolean('activo')
                ->default(true);

            $table->timestamps();

            $table->unique(
                ['negocio_id', 'codigo'],
                'producto_codigo_negocio_unique'
            );

            $table->index([
                'negocio_id',
                'activo',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};