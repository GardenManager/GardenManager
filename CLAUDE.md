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

Entities go in `src/<Module>/Domain/Entity/`, repository interfaces in `src/<Module>/Domain/Persistence/`, and Doctrine implementations in `src/<Module>/Infrastructure/Persistence/` (named `*DoctrineRepository`). Doctrine uses the `underscore_number_aware` naming strategy; entities use ULID identities generated in the domain (`GeneratedValue: NONE`).

## Architecture

The codebase is a modular monolith. Each bounded context under `src/` (`Auth`, `CustomAttribute`, `Permission`, `Plant`, `Seller`, `Tenant`, plus `Shared`) follows this canonical shape:

```
src/<Module>/
  Application/
    Command/   <Verb><Noun>Command.php + <Verb><Noun>CommandHandler.php
    Query/     <Verb><Noun>Query.php + <Verb><Noun>QueryHandler.php
    View/      Read models returned by query handlers
    Dto/       Form/input DTOs
  Domain/
    Entity/       Doctrine entities (final, ULID identity)
    Persistence/  Repository interfaces
    Enum/  Exception/  ValueObject/
    <Module>Permissions.php + <Module>PermissionProvider.php (Domain root)
  Infrastructure/
    Persistence/  <Entity>DoctrineRepository.php
    Form/  Http/Web/  Http/Api/
```

Tests mirror `src/` paths under `tests/` with the same structure. Messages (commands/queries) are `final readonly` and carry `tenantId`/`actorUserId`; authorization is declared with `#[RequiresPermission]` on the message and enforced by `PermissionCheckMiddleware` on both buses.

- **Namespace:** `GardenManager\` (PSR-4 from `src/`)
- **Routing:** Attribute-based routes on invokable single-action controllers (`#[Route]`)
- **Services:** Autowired and autoconfigured by default (`config/services.yaml`)
- **CQRS:** `command.bus` (permission → validation → doctrine_transaction middleware) and `query.bus`, dispatched via `Shared\Infrastructure\Bus\CommandDispatcher`/`QueryDispatcher`. Handlers register with `#[AsMessageHandler(bus: ...)]`.
- **Frontend:** AssetMapper (no Webpack/Vite) with Stimulus controllers, Hotwire Turbo, and Tailwind CSS
  - JS entrypoint: `assets/app.js`
  - Stimulus controllers: `assets/controllers/`
  - Styles: `assets/styles/app.css`
  - Import map: `importmap.php`
- **Twig Components:** PHP classes in `src/<Module>/Infrastructure/Twig/Components/` with templates in `templates/components/<module>/` (namespaces registered in `config/packages/twig_component.yaml`).
- **Messenger:** Async transport via Valkey. Mailer and Notifier messages route to `async`; app commands/queries run sync. Worker runs in a separate container managed by supervisord (`.docker/worker/supervisord.conf`). The worker container also runs `tailwind:build --watch`.
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
- PHP classes are `final` by convention, including entities; messages (commands/queries) are `final readonly`

## Git Workflow

- NEVER commit without explicit user approval — always ask first, for every commit.
- Do NOT add `Co-Authored-By` trailers or any AI attribution to commit messages.
