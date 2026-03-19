DOCKER_COMPOSE = docker compose --env-file .env --env-file .env.dev
PHP_CONT = php

define symfony.console
	$(DOCKER_COMPOSE) exec $(PHP_CONT) php bin/console $(1)
endef

.PHONY: help install app.database app.fixtures app.cache app.test \
        docker.build docker.up docker.down \
        docker.logs.php docker.logs.db docker.logs.caddy docker.shell.php \
        app.watch.theme app.build.theme

help: ## Affiche cette aide
	@grep -E '^[a-zA-Z._-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-22s\033[0m %s\n", $$1, $$2}'

install: ## Installe le projet (build, up, composer, theme, bdd, fixtures)
	$(MAKE) docker.build
	$(MAKE) docker.up
	$(DOCKER_COMPOSE) exec $(PHP_CONT) composer install
	$(MAKE) app.build.theme
	$(MAKE) app.database
	$(MAKE) app.fixtures

## ── Application ────────────────────────────────────────────────────────────

app.database: ## Réinitialise la base de données (drop, create, migrate)
	$(call symfony.console,doctrine:database:drop --if-exists --force)
	$(call symfony.console,doctrine:database:create --if-not-exists)
	$(call symfony.console,doctrine:migrations:migrate -n)

app.fixtures: ## Charge les fixtures de données
	$(call symfony.console,doctrine:fixtures:load -n)

app.cache: ## Vide le cache Symfony
	$(call symfony.console,cache:clear)

app.test: ## Lance la suite de tests PHPUnit
	$(DOCKER_COMPOSE) exec $(PHP_CONT) php bin/phpunit

app.watch.theme: ## Lance le watch Tailwind (mode développement)
	$(call symfony.console,tailwind:build --watch)

app.build.theme: ## Compile le thème Tailwind (production)
	$(call symfony.console,tailwind:build)

## ── Docker ─────────────────────────────────────────────────────────────────

docker.build: ## Construit les images Docker
	$(DOCKER_COMPOSE) build

docker.up: ## Démarre les conteneurs en arrière-plan
	$(DOCKER_COMPOSE) up -d

docker.down: ## Arrête et supprime les conteneurs
	$(DOCKER_COMPOSE) down

docker.logs.php: ## Affiche les logs du conteneur PHP
	$(DOCKER_COMPOSE) logs -f $(PHP_CONT)

docker.logs.db: ## Affiche les logs du conteneur DB
	$(DOCKER_COMPOSE) logs -f db

docker.logs.caddy: ## Affiche les logs du conteneur Caddy
	$(DOCKER_COMPOSE) logs -f caddy

docker.shell.php: ## Ouvre un terminal dans le conteneur PHP
	$(DOCKER_COMPOSE) exec $(PHP_CONT) sh
app.queue: ## Consomme la file de messages (Messenger) en réinitialisant les messages bloqués
	$(DOCKER_COMPOSE) exec db mariadb -u$${DB_USER:-app} -p$${DB_PASSWORD:-app} $${DB_NAME:-app} -e "UPDATE messenger_messages SET delivered_at = NULL WHERE delivered_at IS NOT NULL;"
	$(call symfony.console,messenger:consume async -vv)