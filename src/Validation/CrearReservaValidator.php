<?php

declare(strict_types=1);

namespace App\Validation;

use App\Exception\ValidationException;
use DateTimeImmutable;

final class CrearReservaValidator
{
    private const MAX_NOMBRE_HUESPED = 120;
    private const MAX_EMAIL = 254;
    private const MAX_NOMBRE_ALOJAMIENTO = 150;
    private const MAX_NOTAS = 2000;

    /**
     * @param array<string, mixed> $input
     * @return array{
     *     nombre_huesped: string,
     *     email_huesped: string,
     *     nombre_alojamiento: string,
     *     fecha_entrada: DateTimeImmutable,
     *     fecha_salida: DateTimeImmutable,
     *     importe: string,
     *     notas: ?string
     * }
     */
    public function validar(array $input): array
    {
        $errors = [];
        $nombreHuesped = $this->textoObligatorio($input, 'nombre_huesped', self::MAX_NOMBRE_HUESPED, $errors);
        $emailHuesped = $this->textoObligatorio($input, 'email_huesped', self::MAX_EMAIL, $errors);
        $nombreAlojamiento = $this->textoObligatorio($input, 'nombre_alojamiento', self::MAX_NOMBRE_ALOJAMIENTO, $errors);
        $fechaEntrada = $this->fecha($input, 'fecha_entrada', $errors);
        $fechaSalida = $this->fecha($input, 'fecha_salida', $errors);
        $importe = $this->importe($input, $errors);
        $notas = $this->notas($input, $errors);

        if ($emailHuesped !== null && filter_var($emailHuesped, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email_huesped'] = 'El email no tiene un formato válido.';
        }

        if ($fechaEntrada !== null && $fechaSalida !== null && $fechaSalida <= $fechaEntrada) {
            $errors['fecha_salida'] = 'La fecha de salida debe ser posterior a la de entrada.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return [
            'nombre_huesped' => $nombreHuesped,
            'email_huesped' => $emailHuesped,
            'nombre_alojamiento' => $nombreAlojamiento,
            'fecha_entrada' => $fechaEntrada,
            'fecha_salida' => $fechaSalida,
            'importe' => $importe,
            'notas' => $notas,
        ];
    }

    /** @param array<string, mixed> $input @param array<string, string> $errors */
    private function textoObligatorio(array $input, string $field, int $maxLength, array &$errors): ?string
    {
        $value = $input[$field] ?? null;

        if (!is_string($value) || trim($value) === '') {
            $errors[$field] = 'Este campo es obligatorio.';

            return null;
        }

        $value = trim($value);

        if (mb_strlen($value) > $maxLength) {
            $errors[$field] = sprintf('No puede superar %d caracteres.', $maxLength);

            return null;
        }

        return $value;
    }

    /** @param array<string, mixed> $input @param array<string, string> $errors */
    private function fecha(array $input, string $field, array &$errors): ?DateTimeImmutable
    {
        $value = $input[$field] ?? null;

        if (!is_string($value)) {
            $errors[$field] = 'Este campo es obligatorio.';

            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $dateErrors = DateTimeImmutable::getLastErrors();

        if ($date === false || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))) {
            $errors[$field] = 'La fecha debe tener el formato AAAA-MM-DD.';

            return null;
        }

        return $date;
    }

    /** @param array<string, mixed> $input @param array<string, string> $errors */
    private function importe(array $input, array &$errors): ?string
    {
        $value = $input['importe'] ?? null;

        if (!is_int($value) && !is_float($value) && !is_string($value)) {
            $errors['importe'] = 'El importe es obligatorio.';

            return null;
        }

        $importe = trim((string) $value);

        if (preg_match('/^(?:0|[1-9]\d{0,7})(?:\.\d{1,2})?$/', $importe) !== 1 || preg_match('/^0(?:\.0{1,2})?$/', $importe) === 1) {
            $errors['importe'] = 'El importe debe ser un número positivo con un máximo de dos decimales.';

            return null;
        }

        [$integer, $decimal] = array_pad(explode('.', $importe, 2), 2, '');

        return $integer . '.' . str_pad($decimal, 2, '0');
    }

    /** @param array<string, mixed> $input @param array<string, string> $errors */
    private function notas(array $input, array &$errors): ?string
    {
        $value = $input['notas'] ?? null;

        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            $errors['notas'] = 'Las notas deben ser texto.';

            return null;
        }

        $value = trim($value);

        if (mb_strlen($value) > self::MAX_NOTAS) {
            $errors['notas'] = sprintf('No puede superar %d caracteres.', self::MAX_NOTAS);

            return null;
        }

        return $value === '' ? null : $value;
    }
}
