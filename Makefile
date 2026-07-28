DOCKER_COMPOSE   = docker compose
PHP_CONTAINER    = php
WORKER_CONTAINER = worker
EXEC_PHP         = $(DOCKER_COMPOSE) exec $(PHP_CONTAINER)
SYMFONY_CONSOLE  = $(EXEC_PHP) php bin/console

.DEFAULT_GOAL := help

##
## Project
## -------

.PHONY: help
help: ## Show this help message
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' Makefile | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-20s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m## /[33m/'

.PHONY: build
build: ## Build all Docker images
	$(DOCKER_COMPOSE) build

.PHONY: up
up: ## Start core services
	$(DOCKER_COMPOSE) up -d

.PHONY: down
down: ## Stop all services
	$(DOCKER_COMPOSE) --profile dev down

.PHONY: restart
restart: down up ## Restart all services

.PHONY: logs
logs: ## Follow logs from all services
	$(DOCKER_COMPOSE) logs -f

.PHONY: logs-php
logs-php: ## Follow PHP container logs
	$(DOCKER_COMPOSE) logs -f $(PHP_CONTAINER)

.PHONY: logs-worker
logs-worker: ## Follow worker container logs
	$(DOCKER_COMPOSE) logs -f $(WORKER_CONTAINER)

##
## PHP / Symfony
## -------------

.PHONY: shell
shell: ## Open a shell in the PHP container
	$(EXEC_PHP) sh

.PHONY: composer
composer: ## Run a Composer command (e.g. make composer c="require foo/bar")
	$(EXEC_PHP) composer $(c)

.PHONY: console
console: ## Run a Symfony console command (e.g. make console c="debug:router")
	$(SYMFONY_CONSOLE) $(c)

.PHONY: vendor
vendor: ## Install Composer dependencies
	$(EXEC_PHP) composer install --no-interaction

.PHONY: cache-clear
cache-clear: ## Clear the Symfony cache
	$(SYMFONY_CONSOLE) cache:clear

##
## Database
## --------

.PHONY: db-create
db-create: ## Create the database
	$(SYMFONY_CONSOLE) doctrine:database:create --if-not-exists

.PHONY: db-migrate
db-migrate: ## Run database migrations
	$(SYMFONY_CONSOLE) doctrine:migrations:migrate --no-interaction

.PHONY: db-diff
db-diff: ## Generate a migration by diffing the schema
	$(SYMFONY_CONSOLE) doctrine:migrations:diff

.PHONY: db-schema
db-schema: ## Validate the mapping schema
	$(SYMFONY_CONSOLE) doctrine:schema:validate

##
## Workers & Services
## ------------------

.PHONY: worker-restart
worker-restart: ## Restart the messenger worker container
	$(DOCKER_COMPOSE) restart $(WORKER_CONTAINER)

##
## HTTPS (Local TLS)
## -----------------

CERTS_DIR = .docker/certs

.PHONY: certs
certs: ## Generate local TLS certificates with mkcert
	@command -v mkcert >/dev/null 2>&1 || { echo "Error: mkcert is not installed. Install it from https://github.com/FiloSottile/mkcert"; exit 1; }
	mkcert -install
	mkdir -p $(CERTS_DIR)
	mkcert -cert-file $(CERTS_DIR)/cert.pem -key-file $(CERTS_DIR)/key.pem localhost 127.0.0.1 ::1 gardenmanager.local
	@echo ""
	@echo "Certificates generated in $(CERTS_DIR)/"
	@echo "Ensure '127.0.0.1 gardenmanager.local' is in your hosts file:"
	@echo "  - WSL2:    /etc/hosts"
	@echo "  - Windows: C:\Windows\System32\drivers\etc\hosts"
	@echo ""
	@echo "Run 'make restart' to apply."

.PHONY: certs-trust-windows
certs-trust-windows: ## Import mkcert CA into Windows certificate store (WSL2 only)
	@command -v mkcert >/dev/null 2>&1 || { echo "Error: mkcert is not installed."; exit 1; }
	@test -d /mnt/c || { echo "Error: /mnt/c not found. This target is for WSL2 only."; exit 1; }
	$(eval CA_ROOT := $(shell mkcert -CAROOT))
	@test -f "$(CA_ROOT)/rootCA.pem" || { echo "Error: rootCA.pem not found. Run 'make certs' first."; exit 1; }
	$(eval WIN_USER := $(shell cmd.exe /C "echo %USERNAME%" 2>/dev/null | tr -d '\r'))
	cp "$(CA_ROOT)/rootCA.pem" "/mnt/c/Users/$(WIN_USER)/gardenmanager-ca.pem"
	certutil.exe -addstore -user Root "C:\Users\$(WIN_USER)\gardenmanager-ca.pem"
	@echo ""
	@echo "CA certificate imported into Windows user store."
	@echo ""
	@echo "Firefox users: Firefox uses its own certificate store."
	@echo "Import manually via: Settings > Privacy & Security > Certificates > View Certificates > Import"
	@echo "File: C:\Users\$(WIN_USER)\gardenmanager-ca.pem"

##
## Frontend
## --------

.PHONY: npm
npm: ## Install npm dependencies
	docker run --rm -v "$(PWD):/app" -w /app node:22-alpine npm install --no-bin-links

##
## Testing & QA
## ------------

COVERAGE_FLAGS = $(if $(coverage),--coverage-text,)

.PHONY: test
test: ## Run PHPUnit tests (coverage=1 for coverage)
	$(EXEC_PHP) php bin/phpunit $(COVERAGE_FLAGS)

.PHONY: test-unit
test-unit: ## Run unit tests only (coverage=1 for coverage)
	$(EXEC_PHP) php bin/phpunit --group unit $(COVERAGE_FLAGS)

.PHONY: test-integration
test-integration: ## Run integration tests (coverage=1 for coverage)
	$(EXEC_PHP) php bin/phpunit --group integration $(COVERAGE_FLAGS)

.PHONY: test-functional
test-functional: ## Run functional tests (coverage=1 for coverage)
	$(EXEC_PHP) php bin/phpunit --group functional $(COVERAGE_FLAGS)

.PHONY: phpstan
phpstan: ## Run PHPStan static analysis
	$(EXEC_PHP) vendor/bin/phpstan analyse --memory-limit=512M

.PHONY: phpstan-baseline
phpstan-baseline: ## Regenerate PHPStan baseline
	$(EXEC_PHP) vendor/bin/phpstan analyse --generate-baseline --memory-limit=512M

.PHONY: psalm
psalm: ## Run Psalm static analysis
	$(EXEC_PHP) vendor/bin/psalm

.PHONY: psalm-baseline
psalm-baseline: ## Regenerate Psalm baseline
	$(EXEC_PHP) vendor/bin/psalm --set-baseline=psalm-baseline.xml

.PHONY: cs-check
cs-check: ## Check code style (dry-run)
	$(EXEC_PHP) vendor/bin/php-cs-fixer fix --dry-run --diff

.PHONY: cs-fix
cs-fix: ## Fix code style violations
	$(EXEC_PHP) vendor/bin/php-cs-fixer fix

.PHONY: rector
rector: ## Run Rector in dry-run mode
	$(EXEC_PHP) vendor/bin/rector process --dry-run

.PHONY: rector-fix
rector-fix: ## Run Rector and apply changes
	$(EXEC_PHP) vendor/bin/rector process

.PHONY: lint-twig
lint-twig: ## Lint Twig templates
	$(SYMFONY_CONSOLE) lint:twig templates/

.PHONY: lint-yaml
lint-yaml: ## Lint YAML config files
	$(SYMFONY_CONSOLE) lint:yaml config/ --parse-tags

.PHONY: lint-container
lint-container: ## Lint the Symfony DI container
	$(SYMFONY_CONSOLE) lint:container

.PHONY: composer-audit
composer-audit: ## Audit Composer dependencies for security vulnerabilities
	$(EXEC_PHP) composer audit
