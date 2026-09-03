<?php

namespace App\Enums;

enum MetodoPago: string
{
    case Efectivo = 'Efectivo';
    case Debito = 'Débito';
    case Credito = 'Crédito';
    case Transferencia = 'Transferencia';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function esEfectivo(?string $value): bool
    {
        return $value === self::Efectivo->value;
    }
}
