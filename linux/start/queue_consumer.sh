set -eu

SCRIPT_DIR=$(dirname "${BASH_SOURCE[0]}")

source "$SCRIPT_DIR/_run_script_env.sh"


bin/console rabbitmq:consumer video_infos_consumer
