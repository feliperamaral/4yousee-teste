if [ ! -d "vendor" ]; then
    rm -rf public/bundles
    composer install --no-interaction --apcu-autoloader
fi

## Habilite se necessário
#docker-php-ext-enable xdebug

source "$SCRIPT_DIR/_run_migrate.sh"


