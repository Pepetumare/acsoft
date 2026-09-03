<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NegocioInvitacion extends Model
{
    protected $table = 'negocio_invitaciones';

    protected $fillable = [
        'negocio_id', 'email', 'rol', 'token_hash', 'expires_at',
        'accepted_at', 'created_by',
    ];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'accepted_at' => 'datetime'];
    }

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }
}
