<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\EstadoReserva;
use App\Domain\Reserva;
use App\Domain\ReservaFiltro;
use DateTimeImmutable;
use PDO;

final class PdoReservaRepository implements ReservaRepository
{
    public function __construct(private readonly PDO $connection)
    {
    }

    /** @return list<Reserva> */
    public function listar(ReservaFiltro $filtro): array
    {
        $conditions = [];
        $parameters = [];

        if ($filtro->estado !== null) {
            $conditions[] = 'estado = :estado';
            $parameters['estado'] = $filtro->estado->value;
        }

        if ($filtro->desde !== null) {
            $conditions[] = 'fecha_entrada >= :desde';
            $parameters['desde'] = $filtro->desde->format('Y-m-d');
        }

        if ($filtro->hasta !== null) {
            $conditions[] = 'fecha_salida <= :hasta';
            $parameters['hasta'] = $filtro->hasta->format('Y-m-d');
        }

        if ($filtro->busqueda !== null && $filtro->busqueda !== '') {
            $conditions[] = 'nombre_huesped LIKE :busqueda';
            $parameters['busqueda'] = '%' . $this->escapeLike($filtro->busqueda) . '%';
        }

        $where = $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);
        $statement = $this->connection->prepare(
            'SELECT id, nombre_huesped, email_huesped, nombre_alojamiento, fecha_entrada, fecha_salida, estado, importe, notas, fecha_creacion'
            . ' FROM reserva'
            . $where
            . ' ORDER BY fecha_entrada ASC, id ASC',
        );
        $statement->execute($parameters);

        /** @var list<array<string, mixed>> $rows */
        $rows = $statement->fetchAll();

        return array_map(fn (array $row): Reserva => $this->hydrate($row), $rows);
    }

    public function buscarPorId(int $id): ?Reserva
    {
        $statement = $this->connection->prepare(
            'SELECT id, nombre_huesped, email_huesped, nombre_alojamiento, fecha_entrada, fecha_salida, estado, importe, notas, fecha_creacion'
            . ' FROM reserva WHERE id = :id',
        );
        $statement->execute(['id' => $id]);

        /** @var array<string, mixed>|false $row */
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    public function guardar(Reserva $reserva): Reserva
    {
        $statement = $this->connection->prepare(
            'INSERT INTO reserva ('
            . 'nombre_huesped, email_huesped, nombre_alojamiento, fecha_entrada, fecha_salida, estado, importe, notas, fecha_creacion'
            . ') VALUES ('
            . ':nombre_huesped, :email_huesped, :nombre_alojamiento, :fecha_entrada, :fecha_salida, :estado, :importe, :notas, :fecha_creacion'
            . ')',
        );
        $statement->execute([
            'nombre_huesped' => $reserva->nombreHuesped,
            'email_huesped' => $reserva->emailHuesped,
            'nombre_alojamiento' => $reserva->nombreAlojamiento,
            'fecha_entrada' => $reserva->fechaEntrada->format('Y-m-d'),
            'fecha_salida' => $reserva->fechaSalida->format('Y-m-d'),
            'estado' => $reserva->estado->value,
            'importe' => $reserva->importe,
            'notas' => $reserva->notas,
            'fecha_creacion' => $reserva->fechaCreacion->format('Y-m-d H:i:s'),
        ]);

        return new Reserva(
            (int) $this->connection->lastInsertId(),
            $reserva->nombreHuesped,
            $reserva->emailHuesped,
            $reserva->nombreAlojamiento,
            $reserva->fechaEntrada,
            $reserva->fechaSalida,
            $reserva->estado,
            $reserva->importe,
            $reserva->notas,
            $reserva->fechaCreacion,
        );
    }

    public function actualizarEstado(int $id, EstadoReserva $estado): void
    {
        $statement = $this->connection->prepare('UPDATE reserva SET estado = :estado WHERE id = :id');
        $statement->execute([
            'id' => $id,
            'estado' => $estado->value,
        ]);
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): Reserva
    {
        return new Reserva(
            (int) $row['id'],
            (string) $row['nombre_huesped'],
            (string) $row['email_huesped'],
            (string) $row['nombre_alojamiento'],
            new DateTimeImmutable((string) $row['fecha_entrada']),
            new DateTimeImmutable((string) $row['fecha_salida']),
            EstadoReserva::from((string) $row['estado']),
            (string) $row['importe'],
            $row['notas'] === null ? null : (string) $row['notas'],
            new DateTimeImmutable((string) $row['fecha_creacion']),
        );
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
