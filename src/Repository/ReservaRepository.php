<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\EstadoReserva;
use App\Domain\Reserva;
use App\Domain\ReservaFiltro;

interface ReservaRepository
{
    /** @return list<Reserva> */
    public function listar(ReservaFiltro $filtro): array;

    public function buscarPorId(int $id): ?Reserva;

    public function guardar(Reserva $reserva): Reserva;

    public function actualizarEstado(int $id, EstadoReserva $estado): void;
}
