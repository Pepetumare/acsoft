<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Modulo extends Model
{
    protected $fillable = [
        'nombre',
        'slug',
        'categoria',
        'descripcion',
        'icono',
        'ruta',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function negocios(): BelongsToMany
    {
        return $this->belongsToMany(
            Negocio::class,
            'negocio_modulo'
        )
        ->withPivot([
            'activo',
            'configuracion',
        ])
        ->withTimestamps();
    }
}