<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DemoProducto extends Model
{
    protected $table = 'demo_productos';

    protected $fillable = [
        'demo_session_id',
        'nombre',
        'unidad',
    ];

    public function proveedores(): BelongsToMany
    {
        return $this->belongsToMany(
            DemoProveedor::class,
            'demo_producto_proveedor',
            'demo_producto_id',
            'demo_proveedor_id'
        )->withTimestamps();
    }
}