# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

GardenManager is a Symfony 8.0 application running on PHP 8.5+ with a fully Dockerized development environment. It uses PostgreSQL for persistence, Valkey (Redis-compatible) for Messenger transport, and Tailwind CSS for styling.

## Development Environment

Everything runs in Docker. The app is served via nginx on **port 80** (HTTP) or **port 443** (HTTPS) when local TLS is configured. Mailpit UI is at **port 8025**.

```bash
make up          # Start core services (php, nginx, postgres, valkey, worker)
make down        # Stop all services (includes --profile dev)
make mailpit     # Start services including Mailpit (--profile dev)
make shell       # Open a shell in the PHP container
make npm         # Install npm dependencies (one-off Node container)
```

All PHP/Symfony commands run inside the PHP container via `make`:

```bash
make console c="<command>"     # Run bin/console commands
make composer c="<command>"    # Run composer commands
make vendor                    # Install composer dependencies
make test                      # Run PHPUnit tests
```

## Build & Test Commands

```bash
make build                     # Build Docker images
make test                      # Run full test suite (PHPUnit 12, executes inside PHP container)
make cache-clear               # Clear Symfony cache
```

PHPUnit is configured in `phpunit.dist.xml`. Tests live in `tests/` with the `GardenManager\Tests\` namespace. To run a single test:

```bash
docker compose exec php php bin/phpunit --filter=TestClassName
```

## Database

PostgreSQL 17 with Doctrine ORM. Entity mappings use PHP attributes.

```bash
make db-create    # Create database (if not exists)
make db-migrate   # Run migrations
make db-diff      # Generate migration from entity changes
make db-schema    # Validate schema mapping
```

Entities go in `src/Entity/`, repositories in `src/Repository/`. Doctrine uses `underscore_number_aware` naming strategy and `identity` generation for PostgreSQL.

## Architecture

- **Namespace:** `GardenManager\` (PSR-4 from `src/`)
- **Routing:** Attribute-based routes on controllers (`#[Route]`)
- **Services:** Autowired and autoconfigured by default (`config/services.yaml`)
- **Frontend:** AssetMapper (no Webpack/Vite) with Stimulus controllers, Hotwire Turbo, and Tailwind CSS
  - JS entrypoint: `assets/app.js`
  - Stimulus controllers: `assets/controllers/`
  - Styles: `assets/styles/app.css`
  - Import map: `importmap.php`
- **Twig Components:** PHP classes in `src/Twig/Components/` with templates in `templates/components/`. Registered under `GardenManager\Twig\Components\` namespace.
- **Messenger:** Async transport via Valkey. Mailer and Notifier messages route to `async`. Worker runs in a separate container managed by supervisord (`.docker/worker/supervisord.conf`). The worker container also runs `tailwind:build --watch`.
- **Templates:** Twig templates in `templates/`, base layout in `templates/base.html.twig`

## Local HTTPS

HTTPS is optional and uses [mkcert](https://github.com/FiloSottile/mkcert) for trusted local certificates. Without certs, nginx serves HTTP only.

```bash
make certs                # Generate certs (requires mkcert installed)
make certs-trust-windows  # Import CA into Windows cert store (WSL2 only)
make certs-clean          # Remove certs, revert to HTTP-only
```

Setup steps:
1. Install mkcert: `sudo apt install mkcert` (or see mkcert docs)
2. Run `make certs` to generate certificates
3. Run `make certs-trust-windows` to trust the CA in Windows browsers
4. Add `127.0.0.1 gardenmanager.local` to both `/etc/hosts` (WSL2) and `C:\Windows\System32\drivers\etc\hosts` (Windows)
5. Run `make restart` — the app is now at `https://gardenmanager.local`

The nginx entrypoint auto-detects certs in `.docker/certs/` and switches between HTTP-only and HTTPS mode.

## Code Style

- 4 spaces indentation, UTF-8, LF line endings (`.editorconfig`)
- 2 spaces for YAML compose files
- PHP classes are `final` by convention, except entities (see existing controllers/components)
