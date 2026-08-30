<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_superadmin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_superadmin' => 'boolean',
        ];
    }

    public function negocios(): BelongsToMany
    {
        return $this->belongsToMany(
            Negocio::class,
            'negocio_user'
        )
            ->withPivot([
                'rol',
                'activo',
            ])
            ->withTimestamps();
    }

    public function dashboardUrl(): string
    {
        if ($this->is_superadmin) {
            return route('admin.dashboard');
        }

        $negociosActivos = $this
            ->negocios()
            ->wherePivot('activo', true)
            ->where('negocios.activo', true)
            ->limit(2)
            ->get();

        if ($negociosActivos->isEmpty()) {
            return route('account.no-business');
        }

        if ($negociosActivos->count() === 1) {
            return route(
                'gestion.dashboard',
                $negociosActivos->first()
            );
        }

        return route('business.select');
    }
}
