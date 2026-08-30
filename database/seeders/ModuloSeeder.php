<?php

namespace Database\Seeders;

use App\Models\Modulo;
use Illuminate\Database\Seeder;

class ModuloSeeder extends Seeder
{
    public function run(): void
    {
        $modulos = [
            [
                'nombre' => 'Ventas',
                'slug' => 'ventas',
                'categoria' => 'Comercial',
                'descripcion' => 'Registro y gestión de ventas.',
                'ruta' => 'gestion.ventas.index',
                'orden' => 10,
            ],
            [
                'nombre' => 'Gastos',
                'slug' => 'gastos',
                'categoria' => 'Finanzas',
                'descripcion' => 'Registro y control de gastos.',
                'ruta' => 'gestion.gastos.index',
                'orden' => 20,
            ],
            [
                'nombre' => 'Caja',
                'slug' => 'caja',
                'categoria' => 'Finanzas',
                'descripcion' => 'Apertura, movimientos y cierre.',
                'ruta' => 'gestion.caja.index',
                'orden' => 30,
            ],
            [
                'nombre' => 'Productos',
                'slug' => 'productos',
                'categoria' => 'Inventario',
                'descripcion' => 'Gestión de productos.',
                'ruta' => 'gestion.productos.index',
                'orden' => 40,
            ],
            [
                'nombre' => 'Stock',
                'slug' => 'stock',
                'categoria' => 'Inventario',
                'descripcion' => 'Control de existencias.',
                'ruta' => 'gestion.stock.index',
                'orden' => 50,
            ],
            [
                'nombre' => 'Compras',
                'slug' => 'compras',
                'categoria' => 'Inventario',
                'descripcion' => 'Registro de compras y proveedores.',
                'ruta' => 'gestion.compras.index',
                'orden' => 60,
            ],
            [
                'nombre' => 'Reportes',
                'slug' => 'reportes',
                'categoria' => 'Análisis',
                'descripcion' => 'Resumen y análisis del negocio.',
                'ruta' => 'gestion.reportes.index',
                'orden' => 70,
            ],
        ];

        foreach ($modulos as $modulo) {
            Modulo::updateOrCreate(
                [
                    'slug' => $modulo['slug'],
                ],
                $modulo
            );
        }
    }
}
