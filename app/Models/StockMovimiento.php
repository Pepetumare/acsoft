<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovimiento extends Model
{
    protected $fillable = [
        'negocio_id',
        'producto_id',
        'user_id',
        'tipo',
        'cantidad',
        'concepto',
        'origen_tipo',
        'origen_id',
        'observacion',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
    ];

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }
}