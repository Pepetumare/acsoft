<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caja_movimientos', function (Blueprint $table) {

            $table->string('origen_tipo', 50)
                ->nullable()
                ->after('observacion');

            $table->unsignedBigInteger('origen_id')
                ->nullable()
                ->after('origen_tipo');

            $table->index([
                'origen_tipo',
                'origen_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('caja_movimientos', function (Blueprint $table) {

            $table->dropIndex([
                'origen_tipo',
                'origen_id',
            ]);

            $table->dropColumn([
                'origen_tipo',
                'origen_id',
            ]);
        });
    }
};