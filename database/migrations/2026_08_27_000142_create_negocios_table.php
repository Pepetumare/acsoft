<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('negocios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->cascadeOnDelete();

            $table->string('nombre');

            $table->string('slug')
                ->unique();

            $table->string('subdominio')
                ->nullable()
                ->unique();

            $table->string('dominio_personalizado')
                ->nullable()
                ->unique();

            $table->boolean('activo')
                ->default(true);

            $table->json('configuracion')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('negocios');
    }
};