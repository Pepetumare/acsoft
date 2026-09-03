<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    protected $fillable = [
        'negocio_id',
        'user_id',
        'fecha',
        'proveedor',
        'total',
        'observacion',
        'operation_token',
    ];

    protected $casts = [
        'fecha' => 'date',
        'total' => 'decimal:2',
    ];

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(CompraDetalle::class);
    }
}
