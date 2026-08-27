<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caja_movimientos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('caja_id')
                ->constrained('cajas')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('tipo', 20);

            $table->string('concepto');

            $table->decimal('monto', 12, 2);

            $table->text('observacion')
                ->nullable();

            $table->timestamps();

            $table->index([
                'caja_id',
                'tipo',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_movimientos');
    }
};