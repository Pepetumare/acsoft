<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CajaMovimiento extends Model
{
    protected $fillable = [
        'caja_id',
        'user_id',
        'tipo',
        'concepto',
        'monto',
        'observacion',
        'origen_tipo',
        'origen_id',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }
}
