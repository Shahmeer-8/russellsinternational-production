# Production Deployment

This repository contains:

- Frontend at the repository root for Vercel.
- Laravel backend in `russellsinternational-api/` for Railway.

## Vercel Frontend

Use the repository root as the Vercel project root.

Build command:

```bash
npm run build
```

Output directory:

```text
dist
```

Environment variable:

```env
VITE_API_URL=https://YOUR-RAILWAY-BACKEND-DOMAIN
```

## Railway Backend

Create a Railway service from this GitHub repository and set the service root directory to:

```text
russellsinternational-api
```

Add a MySQL service in the same Railway project.

Generate an app key locally:

```bash
cd russellsinternational-api
php artisan key:generate --show
```

Add the variables from `russellsinternational-api/.env.railway.example` to the Railway backend service and replace the placeholder values.

Required Railway backend variables:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=REPLACE_WITH_GENERATED_LARAVEL_APP_KEY
APP_URL=https://YOUR-RAILWAY-BACKEND-DOMAIN
FRONTEND_URL=https://YOUR-VERCEL-FRONTEND-DOMAIN
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
FILESYSTEM_DISK=public
MEDIA_DISK=public
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
NIXPACKS_PHP_ROOT_DIR=/app/public
```

For admin uploads and public media, add a Railway volume to the backend service:

```text
/app/storage/app/public
```

That mount path is the `public` disk root, so uploads survive redeploys. The
container's `public/` directory is rebuilt on every deploy, so `public/storage`
is re-linked to the volume automatically on boot. Never commit files into
`public/storage`: real content there blocks the link, which makes uploads
unreachable over HTTP and breaks every admin thumbnail. Seed media belongs in
`russellsinternational-api/database/seed-media`, which `media:install` copies
onto the disk without overwriting existing uploads.

After the first successful deploy, run:

```bash
railway run php artisan migrate --force
railway run php artisan db:seed --force
railway run php artisan media:install
railway run php artisan optimize:clear
railway run php artisan config:cache
railway run php artisan route:cache
railway run php artisan view:cache
railway run php artisan make:filament-user
```

Test these URLs:

```text
https://YOUR-RAILWAY-BACKEND-DOMAIN
https://YOUR-RAILWAY-BACKEND-DOMAIN/api/v1/settings
https://YOUR-RAILWAY-BACKEND-DOMAIN/api/v1/navigation
https://YOUR-RAILWAY-BACKEND-DOMAIN/admin
```
