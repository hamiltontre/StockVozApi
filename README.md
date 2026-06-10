# StockVoz API

> Backend Laravel 12 para [StockVoz](https://github.com/hamiltontre/StockVoz) — recibe la sincronización offline de las apps móviles y sirve el dashboard web.

## Stack

- **PHP 8.2** + Laravel 12
- **MySQL** (XAMPP local) / **PostgreSQL** (Hostinger producción)
- **Laravel Sanctum** para tokens API
- **Blade + Chart.js** para el dashboard web

## Endpoints

### Públicos

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/health` | Health check |
| POST | `/api/auth/register` | Crea negocio + admin + token |
| POST | `/api/auth/login` | Login → devuelve token Sanctum |

### Protegidos (requieren `Authorization: Bearer <token>`)

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/auth/me` | Info del usuario autenticado |
| POST | `/api/auth/logout` | Revoca el token actual |
| **POST** | **`/api/sync`** | **Recibe el batch offline de la app** |
| GET | `/api/productos` | Catálogo paginado |
| GET | `/api/productos/stock-bajo` | Productos por debajo del mínimo |
| GET | `/api/reportes/resumen?periodo=7` | KPIs |
| GET | `/api/reportes/ventas-por-dia?periodo=7` | Serie para gráficos |
| GET | `/api/reportes/top-productos?limite=5` | Top productos |

### Dashboard web

| Ruta | Descripción |
|------|-------------|
| `/login` | Login con email + password |
| `/dashboard` | Dashboard con KPIs, gráfico Chart.js, top productos, stock bajo, últimas ventas |

## Modelo de datos

12 migraciones · 18 tablas:

- `negocios` — tenancy (un comercio por fila)
- `usuarios` — admin/invitado con PIN y password
- `categorias`, `productos`, `palabras_clave`
- `ventas`, `detalle_ventas`
- `sync_logs` — auditoría de cada item sincronizado
- `personal_access_tokens` (Sanctum) + tablas internas Laravel

## Setup local

```powershell
composer install
cp .env.example .env
# editar .env con DB_DATABASE=stock_voz, DB_USERNAME=root, DB_PASSWORD=

php artisan key:generate
php artisan migrate:fresh
php artisan serve
```

Luego visita `http://127.0.0.1:8000/login`.

## Sync flow

```
App SQLite local
  → POST /api/sync con { items: [{ tabla, operacion, payload }] }
  → SyncController despacha cada item por tabla
  → Idempotencia por (negocio_id, cliente_id) — re-envíos no duplican
  → Responde { procesados, exitosos, fallidos, resultados }
  → App marca exitosos como sincronizado_en e incrementa intentos en fallidos
```

## Deploy a Hostinger VPS

```bash
# En el VPS Ubuntu 22.04
git clone https://github.com/hamiltontre/StockVozApi.git
cd StockVozApi
composer install --no-dev --optimize-autoloader
cp .env.example .env
nano .env  # ajustar DB_*, APP_URL=https://api.stockvoz.app
php artisan key:generate
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache

# Nginx + Let's Encrypt
# (configuración aparte)
```

## Autor

Hamilton Treminio · UAM Nicaragua · 2026
