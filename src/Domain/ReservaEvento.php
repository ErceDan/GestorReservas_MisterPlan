<?php

declare(strict_types=1);

namespace App\Domain;

use DateTimeImmutable;

final class ReservaEvento
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $reservaId,
        public readonly string $tipo,
        public readonly string $descripcion,
        public readonly DateTimeImmutable $fechaCreacion,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'reserva_id' => $this->reservaId,
            'tipo' => $this->tipo,
            'descripcion' => $this->descripcion,
            'fecha_creacion' => $this->fechaCreacion->format('Y-m-d H:i:s'),
        ];
    }
}
