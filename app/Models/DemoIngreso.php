<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DemoIngreso extends Model
{
    protected $table = 'demo_ingresos';

    protected $fillable = [
        'demo_session_id',
        'demo_proveedor_id',
        'demo_producto_id',
        'fecha',
        'cantidad_cajas',
        'peso_total',
    ];

    protected $casts = [
        'fecha' => 'date',
        'peso_total' => 'decimal:2',
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(
            DemoProveedor::class,
            'demo_proveedor_id'
        );
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(
            DemoProducto::class,
            'demo_producto_id'
        );
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(
            DemoIngresoDetalle::class,
            'demo_ingreso_id'
        )->orderBy('numero_caja');
    }
}