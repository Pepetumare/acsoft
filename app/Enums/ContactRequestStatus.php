<?php

namespace App\Enums;

enum ContactRequestStatus: string
{
    case Pending = 'pendiente';
    case Contacted = 'contactado';
    case DemoCreated = 'demo_creada';
    case Customer = 'cliente';
    case Rejected = 'rechazado';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Contacted => 'Contactado',
            self::DemoCreated => 'Demo creada',
            self::Customer => 'Cliente',
            self::Rejected => 'Rechazado',
        };
    }
}
