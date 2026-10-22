# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A Silex 2.0 microframework application that serves as the API backend for the projectaanvraag (project application/request) system. Uses Command/Event-based architecture with SimpleBus.

## Tech Stack

- **Framework**: Silex 2.0 microframework
- **Database**: Doctrine ORM (MySQL) + MongoDB ODM
- **Messaging**: RabbitMQ for asynchronous event handling
- **Architecture**: CQRS with SimpleBus (Command Bus + Event Bus)
- **PHP**: 7.4 (no PHP 8+ features, see `docs/coding-guidelines.md`)

## Essential Commands

### Development (Docker-based)
```bash
make up                  # Start Docker containers
make down                # Stop Docker containers
make install             # Install composer dependencies
make bash                # Enter PHP container shell
make config              # Set up configuration files
make init                # Full setup: install + migrate
make ci                  # Run full CI suite in the container (composer ci)
make stan                # Run static analysis in the container (composer phpstan)
make test                # Run tests in the container (composer test)
make cache-clear         # Clear application cache in the container
```

### Testing & Quality
```bash
composer test                          # Run all tests (PHPUnit)
composer test:integrations:insightly   # Run Insightly integration tests
composer phpcs                         # Check code style
composer phpstan                       # Run static analysis
composer ci                            # Run full CI suite (validate + phpcs + phpstan + test)
```

### Database Migrations
```bash
./bin/console orm:schema-tool:create          # Create initial DB schema
./bin/console orm:schema-tool:update --force  # Update DB schema
make migrate                                  # Docker: create schema
make migrate-update                           # Docker: update schema, note this requires a cache clear
```

### Console Commands
```bash
./bin/console projectaanvraag:consumer     # Run RabbitMQ consumer (required for async events)
./bin/console projectaanvraag:cache-clear  # Clear application cache
./bin/console projectaanvraag:sync-consumers live test     # Sync local consumers with CultureFeed API
```

## Architecture

### Command/Event Bus Pattern
The application uses SimpleBus for CQRS architecture:

1. **Controllers** (`app/*/`)
   - Accept HTTP requests
   - Call command bus or services
   - Contain NO business logic

2. **Commands** (`src/*/Command/`)
   - Represent actions to perform
   - Plain data objects

3. **CommandHandlers** (`src/*/CommandHandler/`)
   - Execute business logic for commands
   - Must have a `handle()` method accepting the command type
   - Registered in `app/config/messagebus.yml`

4. **Events** (`src/*/Event/`)
   - Represent things that happened
   - Implement `AsynchronousMessageInterface` for RabbitMQ processing
   - Implement `DelayableMessageInterface` for delayed execution

5. **EventListeners** (`src/*/EventListener/`)
   - React to events
   - Must have a `handle()` method accepting the event type
   - Registered in `app/config/messagebus.yml`

### Example: Adding a Command
```php
// 1. Create Command: src/Project/Command/CreateProject.php
// 2. Create Handler: src/Project/CommandHandler/CreateProjectCommandHandler.php
// 3. Register in app/config/messagebus.yml:
handlers:
  create_project_handler:
    command: CultuurNet\ProjectAanvraag\Project\Command\CreateProject
    class: \CultuurNet\ProjectAanvraag\Project\CommandHandler\CreateProjectCommandHandler
    arguments:
      - event_bus
      - orm.em
```

### Services and Storages
- Services/Storages load data from APIs or local storage
- Always provide an interface with implementation
- Examples: `IntegrationTypeStorage`, `ProjectService`

### Entities
- ORM entities in `src/Entity/`
- Load/save via Entity Manager:
```php
$entity = $this->entityManager->getRepository('ProjectAanvraag:Coupon')->find($id);
$entity->setUsed(true);
$this->entityManager->persist($entity);
$this->entityManager->flush();
```

## Directory Structure

- **`app/`** - Silex application bootstrap, service providers, controller providers (not unit tested)
- **`src/`** - Main source code (domain & infrastructure layers, fully tested)
- **`test/`** - Unit tests mirroring `src/` structure
- **`web/`** - Public document root (contains `index.php` front controller)
- **`bin/`** - Console scripts
- **`vendor/`** - Composer dependencies (never commit)
- **`config.yml`** - Main configuration (copy from `config.dist.yml`)

## Key Modules in src/

- **`Project/`** - Core project request functionality
- **`Insightly/`** - Insightly CRM integration (client, parsers, result handlers)
- **`IntegrationType/`** - Integration type management
- **`User/`** - User management
- **`Coupon/`** - Coupon system
- **`Widget/`** - Widget functionality
- **`Entity/`** - Doctrine ORM entities
- **`Core/`** - Core shared functionality

## Configuration

- **Main config**: `config.yml` (copy from `config.dist.yml` for setup)
- **Message bus**: `app/config/messagebus.yml` - register all command/event handlers here
- **Integration types**: `integration_types.yml` (copy from `integration_types.dist.yml` for setup)
- **User roles**: `user_roles.yml` (copy from `user_roles.dist.yml` for setup)
- **Facilities**: `facilities.yml`

## RabbitMQ Integration

- Asynchronous events are processed via RabbitMQ
- Consumer must run: `./bin/console projectaanvraag:consumer`
- Use supervisor in production to keep consumer running
- Events implementing `AsynchronousMessageInterface` are queued automatically
- Requires RabbitMQ with delayed message exchange plugin

## Testing

- Run specific test file: `vendor/bin/phpunit test/Path/To/TestFile.php`
- Run specific test method: `vendor/bin/phpunit --filter testMethodName`
- Tests are in `test/` directory, mirroring `src/` structure
- Integration tests excluded by default (see `phpunit.xml`)

## Insightly Integration

- Client available via dependency injection: `insightly_client`
- Each Insightly API method has a public method in the client
- Result handlers parse responses using parsers in `src/Insightly/Parser/`

## Code Standards

- Follow Symfony2 coding standards (enforced by phpcs)
- Ruleset: `phpcs-ruleset.xml`
- Static analysis: PHPStan with `phpstan.neon`
- All `src/` code must be covered by tests in `test/`
