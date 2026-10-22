<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\ReservaFiltro;
use App\Http\HtmlResponse;
use App\Http\Request;
use App\Service\ReservaService;
use Twig\Environment;

final class ReservaPageController
{
    public function __construct(private readonly ReservaService $service, private readonly Environment $twig) {}

    public function listado(Request $request, array $parameters): HtmlResponse
    {
        return new HtmlResponse($this->twig->render('listado.twig', ['reservas' => $this->service->listar(new ReservaFiltro())]));
    }

    public function detalle(Request $request, array $parameters): HtmlResponse
    {
        $id = (int) ($parameters['id'] ?? 0);
        $reserva = $this->service->obtener($id);

        return new HtmlResponse($this->twig->render('detalle.twig', ['reserva' => $reserva, 'eventos' => $this->service->historial($id)]));
    }
}
