#!/bin/bash
set -e

# Default PORT to 80 if not set by host environment
PORT="${PORT:-80}"

# Substitute actual port number into Apache configuration
sed -i "s/Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:.*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

echo "Starting Apache on port ${PORT}..."
exec apache2-foreground
