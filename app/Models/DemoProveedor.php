<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemoProveedor extends Model
{
    protected $table = 'demo_proveedores';

    protected $fillable = [
        'demo_session_id',
        'nombre',
    ];
}