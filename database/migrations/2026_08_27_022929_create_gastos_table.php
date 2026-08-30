<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('negocio_id')
                ->constrained('negocios')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('fecha');

            $table->string('concepto');

            $table->decimal('monto', 12, 2);

            $table->string('categoria', 100)
                ->nullable();

            $table->string('metodo_pago', 50)
                ->nullable();

            $table->text('observacion')
                ->nullable();

            $table->timestamps();

            $table->index([
                'negocio_id',
                'fecha',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};