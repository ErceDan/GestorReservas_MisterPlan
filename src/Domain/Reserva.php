<?php

declare(strict_types=1);

namespace App\Domain;

use DateTimeImmutable;

final class Reserva
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $nombreHuesped,
        public readonly string $emailHuesped,
        public readonly string $nombreAlojamiento,
        public readonly DateTimeImmutable $fechaEntrada,
        public readonly DateTimeImmutable $fechaSalida,
        public readonly EstadoReserva $estado,
        public readonly string $importe,
        public readonly ?string $notas,
        public readonly DateTimeImmutable $fechaCreacion,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre_huesped' => $this->nombreHuesped,
            'email_huesped' => $this->emailHuesped,
            'nombre_alojamiento' => $this->nombreAlojamiento,
            'fecha_entrada' => $this->fechaEntrada->format('Y-m-d'),
            'fecha_salida' => $this->fechaSalida->format('Y-m-d'),
            'estado' => $this->estado->value,
            'importe' => $this->importe,
            'notas' => $this->notas,
            'fecha_creacion' => $this->fechaCreacion->format('Y-m-d H:i:s'),
        ];
    }

    public function conEstado(EstadoReserva $estado): self
    {
        return new self(
            $this->id,
            $this->nombreHuesped,
            $this->emailHuesped,
            $this->nombreAlojamiento,
            $this->fechaEntrada,
            $this->fechaSalida,
            $estado,
            $this->importe,
            $this->notas,
            $this->fechaCreacion,
        );
    }
}
