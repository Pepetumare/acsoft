<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('negocio_id')
                ->constrained('negocios')
                ->cascadeOnDelete();

            $table->foreignId('user_apertura_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('user_cierre_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('fecha');

            $table->decimal('saldo_inicial', 12, 2)
                ->default(0);

            $table->decimal('saldo_esperado', 12, 2)
                ->nullable();

            $table->decimal('saldo_contado', 12, 2)
                ->nullable();

            $table->decimal('diferencia', 12, 2)
                ->nullable();

            $table->timestamp('abierta_en')
                ->nullable();

            $table->timestamp('cerrada_en')
                ->nullable();

            $table->string('estado', 20)
                ->default('abierta');

            $table->text('observacion_apertura')
                ->nullable();

            $table->text('observacion_cierre')
                ->nullable();

            $table->timestamps();

            $table->index([
                'negocio_id',
                'fecha',
            ]);

            $table->index([
                'negocio_id',
                'estado',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};