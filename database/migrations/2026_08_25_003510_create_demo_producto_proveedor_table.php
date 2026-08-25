<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_producto_proveedor', function (Blueprint $table) {
            $table->id();

            $table->foreignId('demo_producto_id')
                ->constrained('demo_productos')
                ->cascadeOnDelete();

            $table->foreignId('demo_proveedor_id')
                ->constrained('demo_proveedores')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                [
                    'demo_producto_id',
                    'demo_proveedor_id',
                ],
                'demo_prod_prov_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_producto_proveedor');
    }
};