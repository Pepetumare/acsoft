<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaContador extends Model
{
    protected $table = 'venta_contadores';

    protected $fillable = [
        'negocio_id',
        'ultimo_numero',
    ];

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }
}
