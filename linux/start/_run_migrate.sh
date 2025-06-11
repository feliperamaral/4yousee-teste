
echo "Executando doctrine:migrations:migrate..."
# Condição para ser executado automaticamente apenas uma vez
if [ ! -f "/tmp/migrate_ok" ]; then
    bin/console doctrine:migrations:migrate --no-interaction
    echo "ok" > "/tmp/migrate_ok"
fi

