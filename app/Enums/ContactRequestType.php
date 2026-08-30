<?php

namespace App\Enums;

enum ContactRequestType: string
{
    case Contact = 'contacto';
    case Demo = 'demostracion';

    public function label(): string
    {
        return match ($this) {
            self::Contact => 'Contacto',
            self::Demo => 'Demostración',
        };
    }
}
