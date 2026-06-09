#!/bin/sh
set -e

cd /var/www/html

# Ensure an .env file exists so generated secrets can be persisted.
# Runtime config (DB, CORS, URLs) still comes from the container environment,
# which takes precedence over .env values in Laravel.
if [ ! -f .env ]; then
  cp .env.example .env
fi

# Generate app key if missing
if ! grep -q "^APP_KEY=base64" .env 2>/dev/null; then
  php artisan key:generate --force || true
fi

# Ensure a JWT secret exists
if ! grep -q "^JWT_SECRET=." .env 2>/dev/null; then
  php artisan jwt:secret --force || true
fi

# Wait for the database to accept connections
echo "Waiting for database ${DB_HOST}:${DB_PORT}..."
until php -r "exit(@fsockopen(getenv('DB_HOST') ?: 'db', (int)(getenv('DB_PORT') ?: 3306)) ? 0 : 1);"; do
  sleep 2
done

php artisan migrate --force

# Seed only once (when there is no employee yet) to avoid duplicating demo data.
NEEDS_SEED=$(php artisan tinker --execute="echo \App\Models\Employee::count();" 2>/dev/null | tail -n1 | tr -dc '0-9')
if [ "${NEEDS_SEED:-0}" = "0" ]; then
  echo "Seeding initial data..."
  php artisan db:seed --force || true
fi

php artisan l5-swagger:generate || true

exec "$@"
