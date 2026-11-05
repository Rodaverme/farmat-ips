#!/bin/sh
set -e

echo "Esperando a que la base de datos PostgreSQL esté lista..."
until nc -z -v -w3 postgres 5432; do
  echo "Base de datos no disponible aún, reintentando en 2 segundos..."
  sleep 2
fi

echo "¡Base de datos disponible! Ejecutando migraciones y seeders..."
php artisan migrate:fresh --seed --force

echo "Iniciando PHP-FPM..."
exec php-fpm