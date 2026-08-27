<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movimientos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('negocio_id')
                ->constrained('negocios')
                ->cascadeOnDelete();

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('tipo', 20);

            $table->decimal('cantidad', 12, 3);

            $table->string('concepto');

            $table->string('origen_tipo', 50)
                ->nullable();

            $table->unsignedBigInteger('origen_id')
                ->nullable();

            $table->text('observacion')
                ->nullable();

            $table->timestamps();

            $table->index([
                'negocio_id',
                'producto_id',
            ]);

            $table->index([
                'origen_tipo',
                'origen_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movimientos');
    }
};