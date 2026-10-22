# Reservas de alojamiento

Prueba técnica para una API y una interfaz de gestión de reservas, desarrollada en PHP nativo.

## Requisitos

- PHP 8.1 o superior
- Composer
- MySQL 8 o MariaDB 10.6 o superior

## Arranque local

1. Copia `.env.example` como `.env` y completa las credenciales locales.
2. Ejecuta `composer install`.
3. Importa `database/schema.sql` con el cliente MySQL configurado en UTF-8. Por ejemplo: `mysql --default-character-set=utf8mb4 -u root -e "source database/schema.sql"`.
   No uses `Get-Content ... | mysql` en PowerShell: puede alterar caracteres acentuados al importar.
4. Arranca la aplicación con `php -S 127.0.0.1:8080 -t public` y abre `http://127.0.0.1:8080`.

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

- La validación de creación se ejecuta en servidor, limita el tamaño de los campos y devuelve errores por campo. Los importes se validan como decimales de hasta dos posiciones, sin convertirlos a `float`.

- La creación y los cambios de estado se ejecutan dentro de transacciones PDO: reserva y evento se confirman juntos o se revierten juntos.

## Pendiente

- Definir el esquema SQL y los datos iniciales.
- Implementar el front controller, HTTP, dominio, persistencia, API y vistas.
- Documentar el arranque local, pruebas, despliegue, escalabilidad y mejoras futuras.
