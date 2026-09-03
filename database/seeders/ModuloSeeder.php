<?php

namespace Database\Seeders;

use App\Models\Modulo;
use App\Models\Negocio;
use Illuminate\Database\Seeder;

class ModuloSeeder extends Seeder
{
    public function run(): void
    {
        $modulos = [
            [
                'nombre' => 'Analítica',
                'slug' => 'analitica',
                'categoria' => 'Análisis',
                'descripcion' => 'Indicadores y métricas principales del negocio.',
                'ruta' => 'gestion.analitica',
                'orden' => 0,
                'activo' => true,
            ],
            [
                'nombre' => 'Ventas',
                'slug' => 'ventas',
                'categoria' => 'Comercial',
                'descripcion' => 'Registro y gestión de ventas.',
                'ruta' => 'gestion.ventas.index',
                'orden' => 10,
                'activo' => true,
            ],
            [
                'nombre' => 'Gastos',
                'slug' => 'gastos',
                'categoria' => 'Finanzas',
                'descripcion' => 'Registro y control de gastos.',
                'ruta' => 'gestion.gastos.index',
                'orden' => 20,
                'activo' => true,
            ],
            [
                'nombre' => 'Caja',
                'slug' => 'caja',
                'categoria' => 'Finanzas',
                'descripcion' => 'Apertura, movimientos y cierre.',
                'ruta' => 'gestion.caja.index',
                'orden' => 30,
                'activo' => true,
            ],
            [
                'nombre' => 'Productos',
                'slug' => 'productos',
                'categoria' => 'Inventario',
                'descripcion' => 'Gestión de productos.',
                'ruta' => 'gestion.productos.index',
                'orden' => 40,
                'activo' => true,
            ],
            [
                'nombre' => 'Stock',
                'slug' => 'stock',
                'categoria' => 'Inventario',
                'descripcion' => 'Control de existencias.',
                'ruta' => 'gestion.stock.index',
                'orden' => 50,
                'activo' => true,
            ],
            [
                'nombre' => 'Compras',
                'slug' => 'compras',
                'categoria' => 'Inventario',
                'descripcion' => 'Registro de compras y proveedores.',
                'ruta' => 'gestion.compras.index',
                'orden' => 60,
                'activo' => true,
            ],
            [
                'nombre' => 'Reportes',
                'slug' => 'reportes',
                'categoria' => 'Análisis',
                'descripcion' => 'Resumen y análisis del negocio.',
                'ruta' => 'gestion.reportes.index',
                'orden' => 70,
                'activo' => true,
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

        $negocioPrueba = Negocio::query()
            ->where('slug', 'negocio-prueba')
            ->first();

        if ($negocioPrueba) {
            $modulosPorAsignar = Modulo::query()
                ->whereIn('slug', array_column($modulos, 'slug'))
                ->pluck('id')
                ->mapWithKeys(fn (int $id) => [$id => ['activo' => true]])
                ->all();

            $negocioPrueba->modulos()->syncWithoutDetaching($modulosPorAsignar);
        }
    }
}
