<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        if (array_key_exists('stock_actual', $this->attributes)) {
            return (float) $this->attributes['stock_actual'];
        }

        return (float) $this
            ->movimientosStock()
            ->selectRaw(self::stockExpression().' as stock_actual')
            ->value('stock_actual');
    }

    public function scopeWithStockActual(Builder $query): Builder
    {
        return $query->addSelect([
            'stock_actual' => StockMovimiento::query()
                ->selectRaw(self::stockExpression())
                ->whereColumn('producto_id', 'productos.id'),
        ]);
    }

    public static function stockExpression(): string
    {
        return "COALESCE(SUM(CASE
            WHEN tipo = 'entrada' THEN cantidad
            WHEN tipo = 'salida' THEN -cantidad
            WHEN tipo = 'ajuste' THEN cantidad
            ELSE 0
        END), 0)";
    }

    public function detallesVenta(): HasMany
    {
        return $this->hasMany(
            VentaDetalle::class
        );
    }
}
