<?php

declare(strict_types=1);

namespace Tests\Validation;

use App\Exception\ValidationException;
use App\Validation\CrearReservaValidator;
use PHPUnit\Framework\TestCase;

final class CrearReservaValidatorTest extends TestCase
{
    public function testValidaYNormalizaUnaReservaCorrecta(): void
    {
        $data = (new CrearReservaValidator())->validar([
            'nombre_huesped' => ' Lucía Martínez ',
            'email_huesped' => 'lucia@example.com',
            'nombre_alojamiento' => 'Apartamento Sol',
            'fecha_entrada' => '2026-11-12',
            'fecha_salida' => '2026-11-16',
            'importe' => '480.5',
            'notas' => ' Llegada por la tarde. ',
        ]);

        self::assertSame('Lucía Martínez', $data['nombre_huesped']);
        self::assertSame('480.50', $data['importe']);
        self::assertSame('2026-11-12', $data['fecha_entrada']->format('Y-m-d'));
        self::assertSame('Llegada por la tarde.', $data['notas']);
    }

    public function testInformaDeLosErroresDeEntradaCriticos(): void
    {
        try {
            (new CrearReservaValidator())->validar([
                'nombre_huesped' => '',
                'email_huesped' => 'no-es-un-email',
                'nombre_alojamiento' => 'Apartamento Sol',
                'fecha_entrada' => '2026-11-16',
                'fecha_salida' => '2026-11-12',
                'importe' => '0',
            ]);
            self::fail('Se esperaba una excepción de validación.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('nombre_huesped', $exception->errors());
            self::assertArrayHasKey('email_huesped', $exception->errors());
            self::assertArrayHasKey('fecha_salida', $exception->errors());
            self::assertArrayHasKey('importe', $exception->errors());
        }
    }
}
