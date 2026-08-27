<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $fillable = [
        'negocio_id',
        'nombre',
        'codigo',
        'unidad',
        'precio_venta',
        'stock_minimo',
        'activo',
    ];

    protected $casts = [
        'precio_venta' => 'decimal:2',
        'stock_minimo' => 'decimal:3',
        'activo' => 'boolean',
    ];

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function movimientosStock(): HasMany
    {
        return $this->hasMany(
            StockMovimiento::class
        );
    }

    public function stockActual(): float
    {
        $entradas = (float) $this
            ->movimientosStock()
            ->where('tipo', 'entrada')
            ->sum('cantidad');

        $salidas = (float) $this
            ->movimientosStock()
            ->where('tipo', 'salida')
            ->sum('cantidad');

        $ajustes = (float) $this
            ->movimientosStock()
            ->where('tipo', 'ajuste')
            ->sum('cantidad');

        return $entradas - $salidas + $ajustes;
    }

    public function detallesVenta(): HasMany
    {
        return $this->hasMany(
            VentaDetalle::class
        );
    }
}
