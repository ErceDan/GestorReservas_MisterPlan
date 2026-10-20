<?php

declare(strict_types=1);

namespace Tests\Domain;

use App\Domain\EstadoReserva;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EstadoReservaTest extends TestCase
{
    #[DataProvider('transicionesPermitidas')]
    public function testPermiteLasTransicionesValidas(EstadoReserva $origen, EstadoReserva $destino): void
    {
        self::assertTrue($origen->puedePasarA($destino));
    }

    #[DataProvider('transicionesNoPermitidas')]
    public function testRechazaLasTransicionesInvalidas(EstadoReserva $origen, EstadoReserva $destino): void
    {
        self::assertFalse($origen->puedePasarA($destino));
    }

    /** @return iterable<string, array{EstadoReserva, EstadoReserva}> */
    public static function transicionesPermitidas(): iterable
    {
        yield 'pendiente a confirmada' => [EstadoReserva::Pendiente, EstadoReserva::Confirmada];
        yield 'pendiente a cancelada' => [EstadoReserva::Pendiente, EstadoReserva::Cancelada];
        yield 'confirmada a cancelada' => [EstadoReserva::Confirmada, EstadoReserva::Cancelada];
    }

    /** @return iterable<string, array{EstadoReserva, EstadoReserva}> */
    public static function transicionesNoPermitidas(): iterable
    {
        yield 'pendiente a pendiente' => [EstadoReserva::Pendiente, EstadoReserva::Pendiente];
        yield 'confirmada a pendiente' => [EstadoReserva::Confirmada, EstadoReserva::Pendiente];
        yield 'confirmada a confirmada' => [EstadoReserva::Confirmada, EstadoReserva::Confirmada];
        yield 'cancelada a pendiente' => [EstadoReserva::Cancelada, EstadoReserva::Pendiente];
        yield 'cancelada a confirmada' => [EstadoReserva::Cancelada, EstadoReserva::Confirmada];
        yield 'cancelada a cancelada' => [EstadoReserva::Cancelada, EstadoReserva::Cancelada];
    }
}
