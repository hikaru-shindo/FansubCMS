# shellcheck shell=bash
# Shared compose detection for the mise tasks in .mise/tasks. Source it, don't run it.
#
# Picks a compose implementation:
#   1. $COMPOSE_CMD, if set (e.g. COMPOSE_CMD="podman-compose")
#   2. docker compose (v2 plugin), if the Docker daemon is reachable
#   3. docker-compose (standalone), if the Docker daemon is reachable
#   4. podman-compose
#   5. podman compose
#
# Afterwards these are set:
#   compose    array with the compose command, e.g. (docker compose)
#   engine     docker | podman
#   APP_UID, APP_GID (exported) and user_note
#
# It also changes into the project root.

# mise sets MISE_PROJECT_ROOT; fall back to this file's location (.mise/lib) for direct runs.
cd "${MISE_PROJECT_ROOT:-$(dirname "${BASH_SOURCE[0]}")/../..}"

engine=""
compose=()

# Docker counts only if its daemon actually answers; an installed CLI is not enough.
docker_running() {
    command -v docker >/dev/null 2>&1 && docker info >/dev/null 2>&1
}

if [[ -n ${COMPOSE_CMD:-} ]]; then
    read -r -a compose <<<"$COMPOSE_CMD"
    case ${compose[0]} in
        podman*) engine=podman ;;
        *) engine=docker ;;
    esac
elif docker_running && docker compose version >/dev/null 2>&1; then
    compose=(docker compose)
    engine=docker
elif docker_running && command -v docker-compose >/dev/null 2>&1; then
    compose=(docker-compose)
    engine=docker
elif command -v podman-compose >/dev/null 2>&1; then
    compose=(podman-compose)
    engine=podman
elif command -v podman >/dev/null 2>&1 && podman compose version >/dev/null 2>&1; then
    compose=(podman compose)
    engine=podman
else
    echo "error: no compose implementation found (docker compose, docker-compose, podman-compose, podman compose)" >&2
    exit 1
fi

# The docker CLI may be podman's compatibility shim (podman-docker).
if [[ $engine == docker ]] && docker --version 2>/dev/null | grep -qi podman; then
    engine=podman
fi

# File ownership on the bind mount:
#   docker:          run as the host user so written files belong to you
#   rootless podman: container root already maps to the host user
if [[ $engine == podman ]] && [[ $(id -u) -ne 0 ]]; then
    export APP_UID=0 APP_GID=0
    user_note="container root = your user $(id -un) via rootless podman"
else
    APP_UID=$(id -u)
    APP_GID=$(id -g)
    export APP_UID APP_GID
    user_note="container runs as $(id -un)"
fi
