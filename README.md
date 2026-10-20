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
- El esquema usa InnoDB, `utf8mb4`, importes `DECIMAL(10,2)`, restricciones `CHECK` y una clave foránea para preservar la integridad del historial. `database/schema.sql` es un inicializador de desarrollo: al reimportarlo recrea las dos tablas de la aplicación y sus datos de ejemplo.
- La capa HTTP centraliza el enrutado por método y ruta, devuelve JSON con cabeceras de seguridad y registra internamente los errores inesperados sin mostrar trazas al cliente.

- Los estados se modelan con un enum respaldado por `string`: una reserva pendiente puede confirmarse o cancelarse; una confirmada puede cancelarse; una cancelada es terminal.

- Las entidades de dominio son inmutables: cambiar el estado crea una nueva instancia. El importe se conserva como texto decimal para no introducir imprecisiones de `float`.

## Pendiente

- Definir el esquema SQL y los datos iniciales.
- Implementar el front controller, HTTP, dominio, persistencia, API y vistas.
- Documentar el arranque local, pruebas, despliegue, escalabilidad y mejoras futuras.
