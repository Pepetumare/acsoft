<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemoIngresoDetalle extends Model
{
    protected $table = 'demo_ingreso_detalles';

    protected $fillable = [
        'demo_ingreso_id',
        'numero_caja',
        'peso',
    ];

    protected $casts = [
        'peso' => 'decimal:2',
    ];

    public function ingreso(): BelongsTo
    {
        return $this->belongsTo(
            DemoIngreso::class,
            'demo_ingreso_id'
        );
    }
}