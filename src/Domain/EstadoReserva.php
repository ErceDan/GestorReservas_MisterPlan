<?php

declare(strict_types=1);

namespace App\Domain;

enum EstadoReserva: string
{
    case Pendiente = 'pendiente';
    case Confirmada = 'confirmada';
    case Cancelada = 'cancelada';

    public function puedePasarA(self $destino): bool
    {
        return match ($this) {
            self::Pendiente => $destino === self::Confirmada || $destino === self::Cancelada,
            self::Confirmada => $destino === self::Cancelada,
            self::Cancelada => false,
        };
    }
}
