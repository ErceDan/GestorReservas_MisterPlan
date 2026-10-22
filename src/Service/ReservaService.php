<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\EstadoReserva;
use App\Domain\Reserva;
use App\Domain\ReservaEvento;
use App\Domain\ReservaFiltro;
use App\Exception\NotFoundException;
use App\Exception\TransicionInvalidaException;
use App\Exception\ValidationException;
use App\Repository\ReservaEventoRepository;
use App\Repository\ReservaRepository;
use App\Validation\CrearReservaValidator;
use DateTimeImmutable;
use PDO;
use Throwable;

final class ReservaService
{
    public function __construct(
        private readonly ReservaRepository $reservas,
        private readonly ReservaEventoRepository $eventos,
        private readonly CrearReservaValidator $validator,
        private readonly PDO $connection,
    ) {
    }

    /** @return list<Reserva> */
    public function listar(ReservaFiltro $filtro): array
    {
        return $this->reservas->listar($filtro);
    }

    public function obtener(int $id): Reserva
    {
        return $this->reservas->buscarPorId($id)
            ?? throw new NotFoundException('Reserva no encontrada.');
    }

    /** @return list<ReservaEvento> */
    public function historial(int $id): array
    {
        $this->obtener($id);

        return $this->eventos->listarPorReservaId($id);
    }

    /** @param array<string, mixed> $input */
    public function crear(array $input): Reserva
    {
        $data = $this->validator->validar($input);
        $reserva = new Reserva(
            null,
            $data['nombre_huesped'],
            $data['email_huesped'],
            $data['nombre_alojamiento'],
            $data['fecha_entrada'],
            $data['fecha_salida'],
            EstadoReserva::Pendiente,
            $data['importe'],
            $data['notas'],
            new DateTimeImmutable(),
        );

        $this->connection->beginTransaction();

        try {
            $guardada = $this->reservas->guardar($reserva);
            $this->eventos->guardar(new ReservaEvento(
                null,
                $guardada->id ?? throw new \LogicException('La reserva creada no tiene id.'),
                'reserva_creada',
                'Reserva creada con estado pendiente.',
                $guardada->fechaCreacion,
            ));
            $this->connection->commit();

            return $guardada;
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw $exception;
        }
    }

    public function cambiarEstado(int $id, string $estadoDestino): Reserva
    {
        $destino = EstadoReserva::tryFrom($estadoDestino)
            ?? throw new ValidationException(['estado' => 'El estado indicado no es válido.']);
        $actual = $this->obtener($id);

        if (!$actual->estado->puedePasarA($destino)) {
            throw new TransicionInvalidaException('La transición de estado no está permitida.');
        }

        $actualizada = $actual->conEstado($destino);
        $this->connection->beginTransaction();

        try {
            $this->reservas->actualizarEstado($id, $destino);
            $this->eventos->guardar(new ReservaEvento(
                null,
                $id,
                $destino === EstadoReserva::Confirmada ? 'reserva_confirmada' : 'reserva_cancelada',
                $destino === EstadoReserva::Confirmada ? 'Reserva confirmada.' : 'Reserva cancelada.',
                new DateTimeImmutable(),
            ));
            $this->connection->commit();

            return $actualizada;
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw $exception;
        }
    }
}
