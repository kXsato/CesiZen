DOCKER_COMPOSE = docker compose --env-file .env --env-file .env.dev
PHP_CONT = php

define symfony.console
	$(DOCKER_COMPOSE) exec $(PHP_CONT) php bin/console $(1)
endef

.PHONY: help install app.database app.fixtures app.cache app.test \
        app.analyse app.cs-fix app.cs-check app.security app.lint \
        docker.build docker.up docker.down \
        docker.logs.php docker.logs.db docker.logs.caddy docker.shell.php \
        app.watch.theme app.build.theme app.queue

help: ## Affiche cette aide
	@grep -E '^[a-zA-Z._-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-22s\033[0m %s\n", $$1, $$2}'

install: ## Installe le projet (build, up, composer, theme, bdd, fixtures)
	@if [ ! -f .env.dev ]; then \
		cp .env.dev.dist .env.dev; \
		echo "\033[33mATTENTION : .env.dev créé depuis .env.dev.dist — définissez APP_SECRET avant de continuer.\033[0m"; \
		exit 1; \
	fi
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

app.analyse: ## Lance PHPStan (analyse statique)
	$(DOCKER_COMPOSE) exec $(PHP_CONT) php vendor/bin/phpstan analyse --memory-limit=256M

app.cs-fix: ## Corrige le style de code (PHP CS Fixer)
	$(DOCKER_COMPOSE) exec $(PHP_CONT) php vendor/bin/php-cs-fixer fix

app.cs-check: ## Vérifie le style sans modifier les fichiers
	$(DOCKER_COMPOSE) exec $(PHP_CONT) php vendor/bin/php-cs-fixer fix --dry-run --diff

app.security: ## Vérifie les vulnérabilités des dépendances
	$(DOCKER_COMPOSE) exec $(PHP_CONT) composer audit

app.lint: ## Lance lint:twig, lint:yaml, lint:container
	$(call symfony.console,lint:twig templates/)
	$(call symfony.console,lint:yaml config/)
	$(call symfony.console,lint:container)

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