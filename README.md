# Gestión de Productos

Mini sistema de gestión de productos con categorías, desarrollado como evaluación técnica para la posición de Programador Full Stack.

Incluye el mismo CRUD de productos implementado de dos formas (Livewire 3 + Alpine.js y Blade + jQuery), un CRUD de categorías con el flujo MVC clásico y la conversión de precios a dólares con el tipo de cambio de Banxico cacheado en Redis.

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
| GET | `/categorias` | `categorias.index` | `CategoriaController@index` |
| GET | `/categorias/create` | `categorias.create` | `CategoriaController@create` |
| POST | `/categorias` | `categorias.store` | `CategoriaController@store` |
| GET | `/categorias/{categoria}/edit` | `categorias.edit` | `CategoriaController@edit` |
| PUT | `/categorias/{categoria}` | `categorias.update` | `CategoriaController@update` |
| DELETE | `/categorias/{categoria}` | `categorias.destroy` | `CategoriaController@destroy` |
| POST | `/tipo-cambio/actualizar` | `tipo-cambio.actualizar` | `TipoCambioController@actualizar` |

## Dónde está cada bloque de la evaluación

### A. Migraciones y modelos Eloquent

- Migraciones: `database/migrations/*_create_categorias_table.php`, `*_create_productos_table.php` y `*_add_soft_deletes_to_categorias_and_productos.php`.
- Modelos: `app/Models/Categoria.php` (`hasMany`) y `app/Models/Producto.php` (`belongsTo`), ambos con eliminado lógico (`SoftDeletes`).
- Scopes en `Producto`: `activos()`, `estatus()` y `buscar()`, combinables entre sí.
- Datos de ejemplo: `database/seeders/CatalogoSeeder.php`.

### B. Rutas y controladores

- `routes/web.php`: rutas con nombre, agrupadas por controlador y prefijo, y un `Route::resource` para categorías.
- `HomeController` devuelve una vista Blade tradicional con indicadores calculados con Eloquent.
- `CategoriaController` es un CRUD clásico: cada acción es una petición completa que devuelve una vista o redirige con un mensaje. Valida con `app/Http/Requests/CategoriaRequest.php` y el listado muestra cuántos productos tiene cada categoría con `withCount`. Vistas en `resources/views/categorias/`.

Validaciones del CRUD de categorías (en el servidor, al guardar):

| Campo | Reglas |
| --- | --- |
| `nombre` | Obligatorio, de 3 a 100 caracteres y sin duplicados entre las categorías vigentes. Al editar se ignora la propia categoría. |
| `descripcion` | Opcional, máximo 1000 caracteres. |

Si la validación falla, el formulario se vuelve a mostrar con el mensaje bajo el campo y conserva lo capturado. Una categoría con productos vigentes no se puede eliminar: el controlador lo comprueba y lo avisa con un mensaje. A diferencia de los CRUD de productos, aquí no hay validación en tiempo real, porque es un formulario clásico que recarga la página.

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

## Pruebas

El proyecto no incluye pruebas automatizadas. Es un tema en el que todavía me falta profundizar, y preferí no entregar pruebas que no pudiera explicar y mantener con seguridad. Para esta evaluación la verificación fue manual, en el navegador, sobre MySQL y Redis locales.

Lo que se revisó manualmente:

- **CRUD de productos (Livewire y jQuery):** alta, edición y baja; validaciones con datos vacíos e inválidos; errores devueltos por el servidor; buscador, filtros por categoría y estatus, y paginación.
- **CRUD de categorías:** alta, edición y baja; nombre duplicado; intento de eliminar una categoría con productos.
- **Eliminado lógico:** al eliminar desde las tres pantallas, el registro desaparece de los listados y la fila permanece en la tabla con fecha en `deleted_at`; se puede volver a crear una categoría con el nombre de una eliminada.
- **Tipo de cambio:** respuesta real de Banxico, lectura desde Redis en la segunda consulta, y comportamiento con Redis apagado, sin token y con la API sin responder.
- **Interfaz:** ancho de escritorio y de celular (375 px), y modo oscuro del sistema operativo.


## Decisiones técnicas

- **Productos con precio** en lugar de tareas con prioridad: el precio en MXN da sentido a la integración del tipo de cambio.
- **Eliminado lógico en lugar de físico:** `Categoria` y `Producto` usan `SoftDeletes`. Eliminar no borra la fila: llena la columna `deleted_at`, y Eloquent omite esos registros en todas las consultas. Así se conserva el historial y un borrado accidental se puede revertir con `restore()`. Se agregó con una migración nueva, sin modificar las originales.
- **Nombre único validado en la aplicación:** con eliminado lógico, una categoría eliminada conserva su nombre en la tabla y un índice único impediría reutilizarlo. Por eso la migración cambia ese índice por uno normal y `CategoriaRequest` valida la unicidad solo entre las categorías vigentes (`withoutTrashed`). Los productos tampoco pueden asignarse a una categoría eliminada.
- **Categorías con productos no se eliminan:** el controlador lo comprueba antes de eliminar. La llave foránea con `restrictOnDelete` se conserva como protección ante un borrado físico directo en la base de datos.
- **Sin pantalla de papelera:** los registros eliminados se pueden consultar y restaurar desde código (`withTrashed()`, `restore()`), pero no hay interfaz para ello; sería el siguiente paso.
- **Índice en `estatus`:** es la columna por la que filtran los scopes y los listados.
- **Categorías con MVC clásico, no con Livewire:** el CRUD de categorías usa a propósito un controlador de recursos (`Route::resource`), un `FormRequest` y vistas Blade con formularios que recargan la página. Livewire + Alpine (bloque C) y jQuery (bloque D) ya quedan demostrados con los dos CRUD de productos; hacer categorías de la forma tradicional cubre el bloque B con un CRUD completo y no solo con la pantalla de inicio. Así el proyecto muestra las tres formas de resolver un CRUD en Laravel.
- **`Route::resource` para categorías:** una sola línea registra las rutas estándar de un CRUD con las URL, verbos y nombres que Laravel usa por convención, y el controlador sigue esos mismos nombres de método (`index`, `create`, `store`, `edit`, `update`, `destroy`). Se excluye `show` porque no hay página de detalle. Las rutas del CRUD con jQuery, en cambio, están escritas una por una dentro de un grupo, porque incluyen una que no es estándar (`/datos`); así el archivo de rutas muestra las dos formas.
- **Un CRUD de categorías en lugar de reportes:** la primera versión tenía un `ReporteController` de solo lectura con el resumen por categoría. Se reemplazó por el CRUD porque las categorías solo podían crearse desde el seeder; el listado muestra cuántos productos tiene cada categoría, con enlace al listado filtrado.
- **Reglas de validación en un solo lugar:** `Producto::reglasValidacion()` y `Producto::mensajesValidacion()` las comparten el formulario de Livewire y el `FormRequest` del CRUD con jQuery, para que ambos se comporten igual.
- **Listados sin N+1:** el número de productos de cada categoría se obtiene con una subconsulta (`withCount`) y los listados de productos cargan su categoría con `with()`.
- **Filtros de Livewire en la URL (`#[Url]`):** la búsqueda sobrevive a una recarga y se puede compartir.
- **Estado de interfaz en Alpine, datos en Livewire:** abrir un menú o un modal de confirmación no hace peticiones al servidor; solo se llama cuando hay que leer o guardar datos.
- **jQuery construye las filas con `.text()`:** el contenido capturado por el usuario nunca se interpreta como HTML.
- **Banxico como fuente principal y una API de respaldo:** el FIX es el tipo de cambio oficial, pero requiere token; con el respaldo el proyecto se puede evaluar sin credenciales.
- **Degradación ante fallos:** si Redis no responde se consulta la API directamente, y si la API falla los precios se muestran solo en MXN. Solo se guardan en caché respuestas válidas.
- **Predis en lugar de la extensión `phpredis`:** evita instalar una extensión de PHP para levantar el proyecto.
- **Zona horaria `America/Mexico_City`:** las fechas mostradas corresponden a la hora local.

### Interfaz

- **Iconos sin dependencias:** el componente `resources/views/components/icono.blade.php` reúne los SVG y se usa como `<x-icono nombre="editar" />`. Los iconos de interfaz son de [Heroicons](https://heroicons.com) (licencia MIT) y los logos de Livewire y jQuery del menú son de [Simple Icons](https://simpleicons.org) (CC0). Como las filas del CRUD con jQuery las arma JavaScript, la vista deja los iconos en etiquetas `<template>` y el script los clona. Los botones que solo tienen icono llevan un texto oculto para lectores de pantalla.
- **Diseño adaptable a celular:** en pantallas chicas el menú pasa debajo del título y las tablas de los CRUD ocultan las columnas secundarias, mostrando ese dato bajo el nombre para no tener que desplazarse de lado.
- **Paginación en español:** Laravel no incluye traducciones; los textos del paginador están en `lang/es/pagination.php` y `lang/es.json`.
- **Solo tema claro:** el paginador de Laravel trae estilos para modo oscuro que Tailwind activa según el sistema operativo, mientras el resto de la aplicación no los tiene. Se desactivó esa detección en `resources/css/app.css` para que la interfaz se vea igual en cualquier equipo.
