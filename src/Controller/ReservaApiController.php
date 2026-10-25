<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\EstadoReserva;
use App\Domain\ReservaFiltro;
use App\Exception\ValidationException;
use App\Http\JsonResponse;
use App\Http\Request;
use App\Service\ReservaService;
use DateTimeImmutable;
use JsonException;

final class ReservaApiController
{
    public function __construct(private readonly ReservaService $service)
    {
    }

    /** @param array<string, string> $parameters */
    public function listar(Request $request, array $parameters): JsonResponse
    {
        $estado = $request->query('estado');

        if ($estado === '') {
            $estado = null;
        }
        $desde = $this->fechaQuery($request->query('desde'), 'desde');
        $hasta = $this->fechaQuery($request->query('hasta'), 'hasta');

        if ($estado !== null && EstadoReserva::tryFrom($estado) === null) {
            throw new ValidationException(['estado' => 'El estado indicado no es válido.']);
        }

        if ($desde !== null && $hasta !== null && $hasta < $desde) {
            throw new ValidationException(['hasta' => 'Debe ser igual o posterior a desde.']);
        }

        $filtro = new ReservaFiltro(
            $estado === null ? null : EstadoReserva::from($estado),
            $desde,
            $hasta,
            $request->query('q'),
        );

        return new JsonResponse([
            'data' => array_map(static fn ($reserva): array => $reserva->toArray(), $this->service->listar($filtro)),
        ]);
    }

    /** @param array<string, string> $parameters */
    public function detalle(Request $request, array $parameters): JsonResponse
    {
        $id = $this->id($parameters);
        $reserva = $this->service->obtener($id);

        return new JsonResponse([
            'data' => $reserva->toArray(),
            'eventos' => array_map(static fn ($evento): array => $evento->toArray(), $this->service->historial($id)),
        ]);
    }

    /** @param array<string, string> $parameters */
    public function crear(Request $request, array $parameters): JsonResponse
    {
        $reserva = $this->service->crear($this->json($request));

        return new JsonResponse(['data' => $reserva->toArray()], 201);
    }

    /** @param array<string, string> $parameters */
    public function cambiarEstado(Request $request, array $parameters): JsonResponse
    {
        $data = $this->json($request);
        $estado = $data['estado'] ?? null;

        if (!is_string($estado)) {
            throw new ValidationException(['estado' => 'El estado es obligatorio.']);
        }

        $reserva = $this->service->cambiarEstado($this->id($parameters), $estado);

        return new JsonResponse(['data' => $reserva->toArray()]);
    }

    private function fechaQuery(?string $value, string $field): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = DateTimeImmutable::getLastErrors();

        if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            throw new ValidationException([$field => 'La fecha debe tener el formato AAAA-MM-DD.']);
        }

        return $date;
    }

    /** @param array<string, string> $parameters */
    private function id(array $parameters): int
    {
        $id = $parameters['id'] ?? '';

        if (filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            throw new ValidationException(['id' => 'El identificador no es válido.']);
        }

        return (int) $id;
    }

    /** @return array<string, mixed> */
    private function json(Request $request): array
    {
        try {
            $data = json_decode($request->body(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new ValidationException(['body' => 'El cuerpo debe ser JSON válido.']);
        }

        if (!is_array($data)) {
            throw new ValidationException(['body' => 'El cuerpo debe ser un objeto JSON.']);
        }

        return $data;
    }
}
