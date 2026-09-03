<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemoSession extends Model
{
    protected $fillable = ['token', 'expires_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function expired(): bool
    {
        return $this->expires_at->isPast();
    }
}
