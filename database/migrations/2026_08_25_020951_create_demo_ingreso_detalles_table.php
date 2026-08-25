<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_ingreso_detalles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('demo_ingreso_id')
                ->constrained('demo_ingresos')
                ->cascadeOnDelete();

            $table->unsignedInteger('numero_caja');

            $table->decimal('peso', 10, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_ingreso_detalles');
    }
};