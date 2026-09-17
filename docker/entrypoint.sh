#!/bin/sh
# Aplica migraciones pendientes y luego inicia Apache.
# Si la migración falla, la app arranca igual (el error queda en `docker logs`).

if [ "${AUTO_MIGRATE:-1}" = "1" ]; then
    php /var/www/html/database/migrate.php || echo "[migrate] ADVERTENCIA: las migraciones fallaron, revisa el log."
fi

exec "$@"
