set -eu

env_script="$SCRIPT_DIR/env_script/${APP_ENV}.sh"

if [ -f "$env_script" ]; then
    source "$env_script"
else
    echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
    echo "Sem script para o env: \"${APP_ENV}\""
    echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
fi
