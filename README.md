# Gestor de reservas de alojamiento

Prueba técnica para MisterPlan: API en PHP nativo, vistas Twig e interfaz en JavaScript sin framework.

**Demo:** https://ercedev.free.nf
**Repositorio:** https://github.com/ErceDan/GestorReservas_MisterPlan

## Funcionalidades

- API JSON para listar, consultar, crear y cambiar el estado de reservas.
- Filtros combinables por estado, fechas y nombre de huésped.
- Estados `pendiente`, `confirmada` y `cancelada`, con transiciones controladas.
- Historial de eventos creado al registrar o cambiar el estado de una reserva.
- Listado y detalle renderizados en servidor con Twig, plantilla base y autoescaping activo.
- Creación, filtrado, confirmación y cancelación desde JavaScript sin recargar la página.
- Avisos de carga, éxito, validación y errores de red.
- Datos iniciales realistas en distintos estados.

## Requisitos

- PHP 8.1 o superior
- Composer
- MySQL 8 o MariaDB 10.6 o superior

## Arranque local

1. Copia `.env.example` como `.env` y completa las credenciales locales.
2. Ejecuta `composer install`.
3. Crea la base de datos e importa `database/schema.sql` en UTF-8. Por ejemplo:

   ```bash
   mysql --default-character-set=utf8mb4 -u root -p < database/schema.sql
   ```

4. Arranca la aplicación:

   ```bash
   php -S 127.0.0.1:8080 -t public
   ```

5. Abre `http://127.0.0.1:8080`.

No uses `Get-Content ... | mysql` desde PowerShell para importar el SQL, porque puede alterar caracteres acentuados. El fichero incluye DDL e INSERTs y deja la aplicación lista con un único import.

## Pruebas

```bash
composer test
```

Hay pruebas unitarias de las reglas de estado, la entidad de reserva, el validador de creación y el enrutador.

## Organización y decisiones

- `src/Domain`: entidades y reglas de negocio independientes de la infraestructura.
- `src/Service`: casos de uso y transacciones al crear reservas o registrar eventos.
- `src/Repository`: interfaces y adaptadores PDO con consultas preparadas.
- `src/Controller` y `src/Http`: adaptadores HTTP, respuestas JSON y enrutado.
- `templates`: vistas Twig con herencia y formato de fechas/importes en plantilla.

Se utiliza MariaDB/MySQL por ser la opción preferida en el enunciado. PDO tiene las emulaciones desactivadas y los datos de entrada se validan en servidor. Twig mantiene el autoescaping activado y no se usa `|raw`. Las credenciales no se versionan; se cargan desde `.env`.

El esquema usa InnoDB, `utf8mb4`, importes `DECIMAL(10,2)`, restricciones e integridad referencial entre reservas y su historial. El manejo de errores está centralizado: devuelve 400 para validación, 404 para recursos ausentes y 500 genérico para errores inesperados en producción.

## Despliegue

La demo está desplegada en InfinityFree: https://ercedev.free.nf

En el hosting compartido los archivos públicos están en `htdocs`. El fichero `.htaccess` reescribe las rutas de la aplicación hacia el front controller y protege código fuente, dependencias y configuración.

## Con más tiempo

- Añadir autenticación y autorización para separar usuarios y roles.
- Incorporar paginación, ordenación y búsqueda indexada.
- Añadir pruebas de integración HTTP y pruebas end-to-end del flujo JavaScript.
- Mejorar la accesibilidad con mensajes asociados a cada campo y estados ARIA más detallados.
- Externalizar la configuración de despliegue y automatizarlo mediante CI.

## Escalabilidad: 1.000 reservas por hora

Para ese volumen mantendría la API sin estado detrás de varias instancias PHP, usaría un pool de conexiones y añadiría índices para estado, fechas y nombre del huésped. Incorporaría paginación obligatoria, observabilidad (logs estructurados, métricas y alertas) y copias de seguridad verificadas. Las tareas no críticas, como notificaciones, se enviarían a una cola; el historial seguiría guardándose transaccionalmente junto a cada cambio de reserva.
