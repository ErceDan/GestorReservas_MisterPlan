<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\ReservaEvento;

interface ReservaEventoRepository
{
    /** @return list<ReservaEvento> */
    public function listarPorReservaId(int $reservaId): array;

    public function guardar(ReservaEvento $evento): ReservaEvento;
}
