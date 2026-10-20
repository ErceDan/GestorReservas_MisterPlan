<?php

declare(strict_types=1);

namespace App\Domain;

use DateTimeImmutable;

final class ReservaFiltro
{
    public function __construct(
        public readonly ?EstadoReserva $estado = null,
        public readonly ?DateTimeImmutable $desde = null,
        public readonly ?DateTimeImmutable $hasta = null,
        public readonly ?string $busqueda = null,
    ) {
    }
}
