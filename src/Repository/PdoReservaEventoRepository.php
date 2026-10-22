<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\ReservaEvento;
use DateTimeImmutable;
use PDO;

final class PdoReservaEventoRepository implements ReservaEventoRepository
{
    public function __construct(private readonly PDO $connection)
    {
    }

    /** @return list<ReservaEvento> */
    public function listarPorReservaId(int $reservaId): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, reserva_id, tipo, descripcion, fecha_creacion'
            . ' FROM reserva_evento WHERE reserva_id = :reserva_id ORDER BY fecha_creacion ASC, id ASC',
        );
        $statement->execute(['reserva_id' => $reservaId]);

        /** @var list<array<string, mixed>> $rows */
        $rows = $statement->fetchAll();

        return array_map(fn (array $row): ReservaEvento => $this->hydrate($row), $rows);
    }

    public function guardar(ReservaEvento $evento): ReservaEvento
    {
        $statement = $this->connection->prepare(
            'INSERT INTO reserva_evento (reserva_id, tipo, descripcion, fecha_creacion)'
            . ' VALUES (:reserva_id, :tipo, :descripcion, :fecha_creacion)',
        );
        $statement->execute([
            'reserva_id' => $evento->reservaId,
            'tipo' => $evento->tipo,
            'descripcion' => $evento->descripcion,
            'fecha_creacion' => $evento->fechaCreacion->format('Y-m-d H:i:s'),
        ]);

        return new ReservaEvento(
            (int) $this->connection->lastInsertId(),
            $evento->reservaId,
            $evento->tipo,
            $evento->descripcion,
            $evento->fechaCreacion,
        );
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): ReservaEvento
    {
        return new ReservaEvento(
            (int) $row['id'],
            (int) $row['reserva_id'],
            (string) $row['tipo'],
            (string) $row['descripcion'],
            new DateTimeImmutable((string) $row['fecha_creacion']),
        );
    }
}
