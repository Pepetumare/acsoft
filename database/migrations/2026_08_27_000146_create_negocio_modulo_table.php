<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('negocio_modulo', function (Blueprint $table) {
            $table->id();

            $table->foreignId('negocio_id')
                ->constrained('negocios')
                ->cascadeOnDelete();

            $table->foreignId('modulo_id')
                ->constrained('modulos')
                ->cascadeOnDelete();

            $table->boolean('activo')
                ->default(true);

            $table->json('configuracion')
                ->nullable();

            $table->timestamps();

            $table->unique(
                ['negocio_id', 'modulo_id'],
                'negocio_modulo_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('negocio_modulo');
    }
};