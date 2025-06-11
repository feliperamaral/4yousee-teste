set -eu

SCRIPT_DIR=$(dirname "${BASH_SOURCE[0]}")

source "$SCRIPT_DIR/_run_script_env.sh"

php-fpm -D
nginx
