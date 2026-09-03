<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\DemoSession;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('demo:cleanup-expired', function () {
    $deleted = 0;

    DemoSession::query()
        ->where('expires_at', '<=', now())
        ->orderBy('id')
        ->chunkById(100, function ($sessions) use (&$deleted): void {
            foreach ($sessions as $demoSession) {
                DB::transaction(function () use ($demoSession, &$deleted): void {
                    $token = $demoSession->token;
                    DB::table('demo_ingresos')->where('demo_session_id', $token)->delete();
                    DB::table('demo_producto_proveedor')->whereIn(
                        'demo_producto_id',
                        DB::table('demo_productos')->where('demo_session_id', $token)->select('id')
                    )->delete();
                    DB::table('demo_productos')->where('demo_session_id', $token)->delete();
                    DB::table('demo_proveedores')->where('demo_session_id', $token)->delete();
                    $demoSession->delete();
                    $deleted++;
                });
            }
        });

    $this->info("Sesiones demo expiradas eliminadas: {$deleted}.");
})->purpose('Elimina exclusivamente datos de sesiones demo expiradas');
