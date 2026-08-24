<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_proveedores', function (Blueprint $table) {
            $table->id();

            $table->uuid('demo_session_id')
                ->index();

            $table->string('nombre');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_proveedores');
    }
};