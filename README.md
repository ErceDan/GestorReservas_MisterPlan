# Reservas de alojamiento

Prueba técnica para una API y una interfaz de gestión de reservas, desarrollada en PHP nativo.

## Requisitos

- PHP 8.1 o superior
- Composer
- MySQL 8 o MariaDB 10.6 o superior

## Estructura

- `public/`: punto de entrada y recursos estáticos.
- `src/`: aplicación organizada por capas.
- `templates/`: vistas Twig.
- `database/`: esquema y datos de ejemplo.
- `tests/`: pruebas automatizadas.

## Decisiones de diseño (borrador)

- Se usa un front controller y enrutador propio para mantener el alcance de PHP nativo.
- El dominio y la lógica de aplicación no dependen de PDO ni de Twig; los repositorios y controladores son adaptadores externos.
- PDO usará consultas preparadas, excepciones y emulación desactivada.
- Las credenciales viven en `.env`, que no se versiona; `.env.example` documenta las variables necesarias.
- Twig mantiene el autoescaping activado. No se prevé usar `|raw`.

## Pendiente

- Definir el esquema SQL y los datos iniciales.
- Implementar el front controller, HTTP, dominio, persistencia, API y vistas.
- Documentar el arranque local, pruebas, despliegue, escalabilidad y mejoras futuras.
