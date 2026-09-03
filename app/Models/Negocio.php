<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;

class Negocio extends Model
{
    public const COLOR_PRIMARIO_DEFAULT = '#0F2744';

    public const COLOR_SECUNDARIO_DEFAULT = '#163A63';

    protected $fillable = [
        'cliente_id',
        'nombre',
        'slug',
        'subdominio',
        'dominio_personalizado',
        'activo',
        'color_primario',
        'color_secundario',
        'logo',
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

    public function landingRouteNameFor(User $user): ?string
    {
        $adminOnlyModules = ['productos', 'stock', 'compras'];
        $isAdmin = $user->canManageBusiness($this);

        $modules = $this->modulosActivos()
            ->get()
            ->sortBy(fn (Modulo $module) => match ($module->slug) {
                'analitica' => -20,
                'ventas' => -10,
                default => $module->orden ?? 999,
            });

        foreach ($modules as $module) {
            if (! $module->ruta || ! Route::has($module->ruta)) {
                continue;
            }

            if (! $isAdmin && in_array($module->slug, $adminOnlyModules, true)) {
                continue;
            }

            return $module->ruta;
        }

        return null;
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

    public function colorPrimario(): string
    {
        return $this->colorSeguro(
            $this->color_primario,
            self::COLOR_PRIMARIO_DEFAULT
        );
    }

    public function colorSecundario(): string
    {
        return $this->colorSeguro(
            $this->color_secundario,
            self::COLOR_SECUNDARIO_DEFAULT
        );
    }

    public function contrastePara(string $color): string
    {
        $hex = ltrim($this->colorSeguro($color, self::COLOR_PRIMARIO_DEFAULT), '#');
        $channels = [
            hexdec(substr($hex, 0, 2)) / 255,
            hexdec(substr($hex, 2, 2)) / 255,
            hexdec(substr($hex, 4, 2)) / 255,
        ];

        $channels = array_map(
            fn (float $channel) => $channel <= 0.04045
                ? $channel / 12.92
                : (($channel + 0.055) / 1.055) ** 2.4,
            $channels
        );

        $luminance = 0.2126 * $channels[0]
            + 0.7152 * $channels[1]
            + 0.0722 * $channels[2];

        $whiteContrast = 1.05 / ($luminance + 0.05);
        $darkContrast = ($luminance + 0.05) / 0.056;

        return $whiteContrast >= $darkContrast ? '#FFFFFF' : '#111827';
    }

    public function logoUrl(): ?string
    {
        return $this->logo
            ? Storage::disk('public')->url($this->logo)
            : null;
    }

    private function colorSeguro(?string $color, string $fallback): string
    {
        return is_string($color) && preg_match('/^#[0-9A-Fa-f]{6}$/', $color)
            ? strtoupper($color)
            : $fallback;
    }
}
