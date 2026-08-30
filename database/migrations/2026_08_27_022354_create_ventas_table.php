<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('negocio_id')
                ->constrained('negocios')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('fecha');

            $table->decimal('total', 12, 2)
                ->default(0);

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
        Schema::dropIfExists('ventas');
    }
};