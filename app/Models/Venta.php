<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $fillable = [
        'negocio_id',
        'user_id',
        'numero_documento_interno',
        'fecha',
        'total',
        'metodo_pago',
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
        return $this->hasMany(VentaDetalle::class);
    }

    public function numeroDocumentoParaMostrar(): string
    {
        if ($this->numero_documento_interno !== null) {
            return str_pad((string) $this->numero_documento_interno, 6, '0', STR_PAD_LEFT);
        }

        return 'LEG-'.str_pad((string) $this->getKey(), 6, '0', STR_PAD_LEFT);
    }
}
