<?php

namespace App\Models;

use App\Enums\ContactRequestStatus;
use App\Enums\ContactRequestType;
use Illuminate\Database\Eloquent\Model;

class ContactRequest extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'business',
        'contact',
        'message',
        'type',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'type' => ContactRequestType::class,
            'status' => ContactRequestStatus::class,
        ];
    }

    public function statusEnum(): ?ContactRequestStatus
    {
        $value = $this->getRawOriginal('status');

        return is_string($value)
            ? ContactRequestStatus::tryFrom($value)
            : null;
    }

    public function typeEnum(): ?ContactRequestType
    {
        $value = $this->getRawOriginal('type');

        return is_string($value)
            ? ContactRequestType::tryFrom($value)
            : null;
    }

    public function statusLabel(): string
    {
        if ($status = $this->statusEnum()) {
            return $status->label();
        }

        $rawStatus = $this->getRawOriginal('status');

        return filled($rawStatus)
            ? "Valor inválido: {$rawStatus}"
            : ContactRequestStatus::Pending->label();
    }

    public function typeLabel(): string
    {
        if ($type = $this->typeEnum()) {
            return $type->label();
        }

        $rawType = $this->getRawOriginal('type');

        return filled($rawType)
            ? "Valor inválido: {$rawType}"
            : ContactRequestType::Contact->label();
    }

    public function statusStyleValue(): string
    {
        return $this->statusEnum()?->value ?? (
            filled($this->getRawOriginal('status'))
                ? 'invalido'
                : ContactRequestStatus::Pending->value
        );
    }
}
