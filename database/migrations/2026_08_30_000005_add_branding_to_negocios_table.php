<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->string('color_primario', 7)->nullable()->after('activo');
            $table->string('color_secundario', 7)->nullable()->after('color_primario');
            $table->string('logo')->nullable()->after('color_secundario');
        });
    }

    public function down(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->dropColumn([
                'color_primario',
                'color_secundario',
                'logo',
            ]);
        });
    }
};
