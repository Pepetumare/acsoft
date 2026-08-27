<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caja extends Model
{
    protected $fillable = [
        'negocio_id',
        'user_apertura_id',
        'user_cierre_id',
        'fecha',
        'saldo_inicial',
        'saldo_esperado',
        'saldo_contado',
        'diferencia',
        'abierta_en',
        'cerrada_en',
        'estado',
        'observacion_apertura',
        'observacion_cierre',
    ];

    protected $casts = [
        'fecha' => 'date',
        'saldo_inicial' => 'decimal:2',
        'saldo_esperado' => 'decimal:2',
        'saldo_contado' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'abierta_en' => 'datetime',
        'cerrada_en' => 'datetime',
    ];

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function usuarioApertura(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_apertura_id'
        );
    }

    public function usuarioCierre(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_cierre_id'
        );
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(CajaMovimiento::class);
    }

    public function estaAbierta(): bool
    {
        return $this->estado === 'abierta';
    }

    public function totalIngresos(): float
    {
        return (float) $this
            ->movimientos()
            ->where('tipo', 'ingreso')
            ->sum('monto');
    }

    public function totalEgresos(): float
    {
        return (float) $this
            ->movimientos()
            ->where('tipo', 'egreso')
            ->sum('monto');
    }

    public function calcularSaldoEsperado(): float
    {
        return
            (float) $this->saldo_inicial
            + $this->totalIngresos()
            - $this->totalEgresos();
    }
}