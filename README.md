# Gestión de Productos

Mini sistema de gestión de productos con categorías, desarrollado como evaluación técnica para la posición de Programador Full Stack.

Incluye el mismo CRUD de productos implementado de dos formas (Livewire 3 + Alpine.js y Blade + jQuery), reportes con el flujo MVC clásico y la conversión de precios a dólares con el tipo de cambio de Banxico cacheado en Redis.

## Stack

| Tecnología | Versión | Uso |
| --- | --- | --- |
| PHP | 8.2+ | Lenguaje |
| Laravel | 12 | Framework |
| Livewire | 3 | CRUD reactivo |
| Alpine.js | incluido en Livewire 3 | Modales, menú desplegable y avisos |
| jQuery | 4 | CRUD con AJAX en Blade |
| MySQL | 8 | Base de datos |
| Redis | 5+ | Caché del tipo de cambio |
| Tailwind CSS | 4 | Estilos |

## Requisitos

- PHP 8.2 o superior y Composer
- Node.js 20 o superior y npm
- MySQL 8
- Redis (opcional para arrancar, necesario para la caché; ver [Redis](#redis))

No hace falta la extensión `redis` de PHP: el proyecto usa el cliente [Predis](https://github.com/predis/predis), que se instala con Composer.

## Instalación

```bash
git clone https://github.com/AdanchoDev/gestion-productos.git
cd gestion-productos

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Revisa la conexión a MySQL en `.env`. Los valores por defecto corresponden a una instalación local (por ejemplo Laragon):

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestion_productos
DB_USERNAME=root
DB_PASSWORD=
```

Crea las tablas y carga los datos de ejemplo. Si la base de datos no existe, Artisan ofrece crearla:

```bash
php artisan migrate --seed
```

Compila los assets y levanta el servidor:

```bash
npm run build
php artisan serve
```

La aplicación queda en http://localhost:8000.

El seeder carga 5 categorías y 40 productos, y se puede ejecutar varias veces sin duplicar registros. Para empezar de cero: `php artisan migrate:fresh --seed`.

## Credenciales y variables de entorno

La aplicación no tiene inicio de sesión, así que no hay usuarios de prueba. La única credencial externa es el token de Banxico, y es opcional.

### Token de Banxico

El tipo de cambio se obtiene de la [API SIE de Banxico](https://www.banxico.org.mx/SieAPIRest/service/v1/), serie `SF43718` (tipo de cambio FIX, pesos por dólar).

1. Entra a https://www.banxico.org.mx/SieAPIRest/service/v1/token.
2. Resuelve el captcha y pulsa "Generar". El token es gratuito y se genera al momento.
3. Copia el token (64 caracteres) en tu `.env`:

```dotenv
BANXICO_TOKEN=tu_token
```

4. Limpia la configuración: `php artisan config:clear`.

**Sin token la aplicación también funciona:** usa como respaldo la API pública de [Frankfurter](https://frankfurter.dev), que no requiere credenciales. La tarjeta del inicio indica de qué fuente salió el dato.

### Variables del tipo de cambio

| Variable | Valor por defecto | Descripción |
| --- | --- | --- |
| `BANXICO_TOKEN` | vacío | Token de la API de Banxico. Vacío = se usa la API de respaldo. |
| `TIPO_CAMBIO_URL` | `https://api.frankfurter.dev/v1/latest` | API de respaldo. |
| `TIPO_CAMBIO_TIMEOUT` | `5` | Segundos de espera máxima por petición. |
| `TIPO_CAMBIO_CACHE_STORE` | `redis` | Almacén de caché de Laravel donde se guarda. |
| `TIPO_CAMBIO_CACHE_TTL` | `3600` | Segundos que el dato permanece en caché. |

### Redis

```dotenv
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Cómo levantarlo:

- **Laragon:** Menú → Redis → Start.
- **Docker:** `docker run -d -p 6379:6379 redis`.

Si Redis no está disponible la aplicación no falla: consulta la API en cada petición y registra una advertencia en `storage/logs/laravel.log`.

Para comprobar la caché, con `redis-cli`:

```
SELECT 1
KEYS *tipo_cambio*
TTL <clave>
```

Laravel guarda la caché en la base lógica 1. El botón "Actualizar ahora" del inicio borra la clave y vuelve a consultar la API.

## Pantallas y rutas

| Método | URI | Nombre | Acción |
| --- | --- | --- | --- |
| GET | `/` | `home` | `HomeController@index` |
| GET | `/productos` | `productos.index` | Componente Livewire `GestionProductos` |
| GET | `/productos-jquery` | `jquery.productos.index` | `ProductoController@index` |
| GET | `/productos-jquery/datos` | `jquery.productos.datos` | `ProductoController@datos` (JSON) |
| POST | `/productos-jquery` | `jquery.productos.store` | `ProductoController@store` (JSON) |
| PUT | `/productos-jquery/{producto}` | `jquery.productos.update` | `ProductoController@update` (JSON) |
| DELETE | `/productos-jquery/{producto}` | `jquery.productos.destroy` | `ProductoController@destroy` (JSON) |
| GET | `/reportes` | `reportes.index` | `ReporteController@index` |
| GET | `/reportes/categorias/{categoria}` | `reportes.categoria` | `ReporteController@categoria` |
| POST | `/tipo-cambio/actualizar` | `tipo-cambio.actualizar` | `TipoCambioController@actualizar` |

## Dónde está cada bloque de la evaluación

### A. Migraciones y modelos Eloquent

- Migraciones: `database/migrations/*_create_categorias_table.php` y `*_create_productos_table.php`.
- Modelos: `app/Models/Categoria.php` (`hasMany`) y `app/Models/Producto.php` (`belongsTo`).
- Scopes en `Producto`: `activos()`, `estatus()` y `buscar()`, combinables entre sí.
- Datos de ejemplo: `database/seeders/CatalogoSeeder.php`.

### B. Rutas y controladores

- `routes/web.php`: rutas con nombre, agrupadas por controlador y prefijo.
- `HomeController` y `ReporteController` devuelven vistas Blade tradicionales con datos procesados por Eloquent (`withCount`, `withSum`, paginación).

### C. Livewire 3 y Alpine.js

- Componente: `app/Livewire/GestionProductos.php` (`mount`, `save`, `delete`, filtros con `#[Url]`, paginación).
- Formulario y validación en tiempo real: `app/Livewire/Forms/ProductoForm.php`.
- Vista: `resources/views/livewire/gestion-productos.blade.php`. Alpine.js se usa en:
  - Modal del formulario: `x-data`, `x-show`, `$wire.entangle`.
  - Confirmación de borrado: `x-data`, `x-show`, `$wire.delete()`.
  - Menú "Filtros rápidos": `x-data`, `x-show`, `$wire.set()`.
  - Contador de caracteres de la descripción: `x-model`.
  - Aviso de éxito: escucha un evento despachado por el componente.

### D. Blade con jQuery

- Controlador: `app/Http/Controllers/ProductoController.php`, con validación en `app/Http/Requests/ProductoRequest.php`.
- Vista: `resources/views/productos/jquery.blade.php`.
- Script: `resources/js/productos-jquery.js`. Listado, filtros, paginación, alta, edición y baja por AJAX, sin recargar la página. Valida en el navegador y también muestra los errores 422 del servidor bajo cada campo.

### E. Servicio REST externo y Redis

- Servicio: `app/Services/TipoCambioService.php`.
- Configuración: `config/services.php` (claves `banxico` y `tipo_cambio`).
- Se muestra en el inicio y como equivalente en USD en ambos CRUD.

## Decisiones técnicas

- **Productos con precio** en lugar de tareas con prioridad: el precio en MXN da sentido a la integración del tipo de cambio.
- **Llave foránea con `restrictOnDelete`:** la base de datos impide borrar una categoría que todavía tiene productos.
- **Índice en `estatus`:** es la columna por la que filtran los scopes y los reportes.
- **Reglas de validación en un solo lugar:** `Producto::reglasValidacion()` y `Producto::mensajesValidacion()` las comparten el formulario de Livewire y el `FormRequest` del CRUD con jQuery, para que ambos se comporten igual.
- **Reportes sin N+1:** los conteos y sumas por categoría se resuelven con subconsultas (`withCount` / `withSum`) y los listados cargan la categoría con `with()`.
- **Filtros de Livewire en la URL (`#[Url]`):** la búsqueda sobrevive a una recarga y se puede compartir.
- **Estado de interfaz en Alpine, datos en Livewire:** abrir un menú o un modal de confirmación no hace peticiones al servidor; solo se llama cuando hay que leer o guardar datos.
- **jQuery construye las filas con `.text()`:** el contenido capturado por el usuario nunca se interpreta como HTML.
- **Banxico como fuente principal y una API de respaldo:** el FIX es el tipo de cambio oficial, pero requiere token; con el respaldo el proyecto se puede evaluar sin credenciales.
- **Degradación ante fallos:** si Redis no responde se consulta la API directamente, y si la API falla los precios se muestran solo en MXN. Solo se guardan en caché respuestas válidas.
- **Predis en lugar de la extensión `phpredis`:** evita instalar una extensión de PHP para levantar el proyecto.
- **Zona horaria `America/Mexico_City`:** las fechas mostradas corresponden a la hora local.
