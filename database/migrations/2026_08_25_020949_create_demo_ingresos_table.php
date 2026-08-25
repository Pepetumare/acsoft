<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_ingresos', function (Blueprint $table) {
            $table->id();

            $table->uuid('demo_session_id')
                ->index();

            $table->foreignId('demo_proveedor_id')
                ->constrained('demo_proveedores')
                ->cascadeOnDelete();

            $table->foreignId('demo_producto_id')
                ->constrained('demo_productos')
                ->cascadeOnDelete();

            $table->date('fecha');

            $table->unsignedInteger('cantidad_cajas');

            $table->decimal('peso_total', 10, 2)
                ->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_ingresos');
    }
};