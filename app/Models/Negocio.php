<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Negocio extends Model
{
    protected $fillable = [
        'cliente_id',
        'nombre',
        'slug',
        'subdominio',
        'dominio_personalizado',
        'activo',
        'configuracion',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'configuracion' => 'array',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'negocio_user'
        )
            ->withPivot([
                'rol',
                'activo',
            ])
            ->withTimestamps();
    }

    public function modulos(): BelongsToMany
    {
        return $this->belongsToMany(
            Modulo::class,
            'negocio_modulo'
        )
            ->withPivot([
                'activo',
                'configuracion',
            ])
            ->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function modulosActivos(): BelongsToMany
    {
        return $this->belongsToMany(
            Modulo::class,
            'negocio_modulo'
        )
            ->where('modulos.activo', true)
            ->wherePivot('activo', true)
            ->withPivot([
                'activo',
                'configuracion',
            ])
            ->orderBy('orden')
            ->withTimestamps();
    }

    public function tieneModulo(string $slug): bool
    {
        return $this
            ->modulosActivos()
            ->where('slug', $slug)
            ->exists();
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class);
    }

    public function cajas(): HasMany
    {
        return $this->hasMany(Caja::class);
    }

    public function cajaAbierta()
    {
        return $this
            ->cajas()
            ->where('estado', 'abierta')
            ->latest('id')
            ->first();
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function movimientosStock(): HasMany
    {
        return $this->hasMany(
            StockMovimiento::class
        );
    }

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class);
    }
}
