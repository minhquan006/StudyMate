"""Keep Docker connection settings while isolating disposable QA configuration."""
import os


APP_VARIABLES = {
    'WEB_PORT', 'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD',
    'DB_DRIVER', 'MYSQL_DATABASE', 'MYSQL_USER', 'MYSQL_PASSWORD',
    'MYSQL_ROOT_PASSWORD', 'DEMO_PASSWORD', 'DB_WAIT_TIMEOUT', 'HTTPS_ENABLED',
    'AI_ENABLED', 'GEMINI_API_KEY', 'GEMINI_MODEL', 'AI_MODEL',
    'STUDYMATE_LIVE_MODEL',
}
COMPOSE_SELECTION = {
    'COMPOSE_FILE', 'COMPOSE_PROJECT_NAME', 'COMPOSE_PROFILES', 'COMPOSE_ENV_FILES',
}


def compose_environment():
    # Preserve PATH, HOME, DOCKER_HOST/CONTEXT/CONFIG and TLS/plugin discovery.
    # Explicit --env-file, -f and -p select this runner's own QA deployment.
    return {name: value for name, value in os.environ.items()
            if name not in APP_VARIABLES | COMPOSE_SELECTION}
