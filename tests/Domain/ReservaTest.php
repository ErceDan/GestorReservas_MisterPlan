<?php

declare(strict_types=1);

namespace Tests\Domain;

use App\Domain\EstadoReserva;
use App\Domain\Reserva;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ReservaTest extends TestCase
{
    public function testCambiaElEstadoSinModificarLaReservaOriginal(): void
    {
        $reserva = new Reserva(
            10,
            'Lucía Martínez',
            'lucia@example.com',
            'Apartamento Sol',
            new DateTimeImmutable('2026-11-12'),
            new DateTimeImmutable('2026-11-16'),
            EstadoReserva::Pendiente,
            '480.00',
            null,
            new DateTimeImmutable('2026-10-15 09:30:00'),
        );

        $confirmada = $reserva->conEstado(EstadoReserva::Confirmada);

        self::assertSame(EstadoReserva::Pendiente, $reserva->estado);
        self::assertSame(EstadoReserva::Confirmada, $confirmada->estado);
        self::assertSame($reserva->id, $confirmada->id);
    }
}
