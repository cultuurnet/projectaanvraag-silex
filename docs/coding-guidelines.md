# Coding Guidelines

This is a legacy project that cannot be upgraded. New code must fit the existing stack and conventions.

## PHP Version

The code runs on **PHP 7.4** (see `docker/php/Dockerfile` and `.github/workflows/ci.yml`).

Do **not** use PHP 8+ features, such as:

- Constructor property promotion
- `match` expressions
- Named arguments
- Union types, `mixed`, `static` return types
- Nullsafe operator (`?->`)
- Enums, readonly properties, attributes
- First-class callable syntax (`foo(...)`)

Scalar type hints, return types, nullable types and typed properties (PHP 7.4) are allowed.

## Pinned Dependencies

The following can **not** be upgraded. Only use APIs available in these versions:

- Silex 2.0 (Pimple container, service providers)
- Symfony 3.x components
- Doctrine ORM 2 / DBAL 2.13, Doctrine MongoDB ODM 1.1
- Guzzle 6
- PHPUnit 7.5

Avoid adding new dependencies without team discussion.

## General

- Follow PSR-4 autoloading under the `CultuurNet\ProjectAanvraag\` namespace
- Keep classes small and focused (Single Responsibility)
- Follow existing patterns over introducing new ones
- Don't refactor or modernise legacy code unrelated to your change

## Architecture

See [architecture.md](architecture.md) for details.

- Controllers accept HTTP requests and call the command bus or a service. They contain **no** business logic.
- Commands live in `src/*/Command/`, handlers in `src/*/CommandHandler/` with a `handle()` method.
- Events live in `src/*/Event/`, listeners in `src/*/EventListener/` with a `handle()` method.
- Register all handlers and listeners in `app/config/messagebus.yml`.
- Events processed via RabbitMQ implement `AsynchronousMessageInterface` (and `DelayableMessageInterface` for delayed execution).
- Services and storages always come with an interface.
- Wire dependencies in the service providers in `app/`. Prefer extending an existing provider over creating a new one.
- Use constructor injection, not service locators.

## Naming

Follow the existing conventions:

- Interfaces use the `Interface` suffix (e.g. `ProjectServiceInterface`)
- Exceptions use the `Exception` suffix (e.g. `CouponInUseException`)

## Testing

- All code in `src/` must be covered by unit tests in `test/`, mirroring the `src/` structure
- `app/` is not unit tested
- Integration tests are excluded by default (see `phpunit.xml`)
- Use PHPUnit 7.5 APIs (`createMock()`, `getMockBuilder()`, `expectException()`)

## Code Style

- Symfony2 coding standard, enforced by `composer phpcs` (`phpcs-ruleset.xml`)
- Static analysis with `composer phpstan` (`phpstan.neon`)
- Run `make ci` (or `composer ci`) before considering work complete

## Security

- Never commit `config.yml`, `integration_types.yml`, `user_roles.yml` with real credentials or any other secrets
- No sensitive data in log statements or error messages
- Validate user-provided input
- Use parameterized queries (Doctrine) instead of building SQL strings

## Restrictions

- **Never** modify files in `vendor/`
- **Never** create commits as an AI assistant; only humans commit code
