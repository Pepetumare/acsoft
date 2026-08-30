<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('negocio_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('negocio_id')
                ->constrained('negocios')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('rol')
                ->default('usuario');

            $table->boolean('activo')
                ->default(true);

            $table->timestamps();

            $table->unique(
                ['negocio_id', 'user_id'],
                'negocio_user_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('negocio_user');
    }
};