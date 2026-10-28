# PR Review Guidelines

Review pull requests and provide inline feedback using the GitHub review system.

Focus on actionable improvements only. Skip praise and positive feedback.

Reference `CLAUDE.md`, [coding-guidelines.md](coding-guidelines.md) and [architecture.md](architecture.md) for project standards.

## Legacy Project

This is a legacy project that **cannot be upgraded**. Reviews must respect its constraints:

- Do **not** suggest PHP 8+ features (see [coding-guidelines.md](coding-guidelines.md#php-version)).
- Do **not** suggest upgrading or replacing dependencies (Silex, Symfony, Doctrine, Guzzle, PHPUnit, ...), or using APIs that only exist in newer versions of them.
- Do **not** suggest refactoring or modernising code the PR does not touch. Focus on the changed lines.
- Do **not** flag existing legacy conventions (`Interface`/`Exception` suffixes, docblock type hints, `testFoo()` method names) as problems.

## Suggestion Blocks

When suggesting code changes, format them as GitHub suggestion blocks:

- Single-line: ` ```suggestion `
- Multi-line: ` ```suggestion:-0+1 ` (adjust numbers for lines to remove/add)

Only provide suggestion blocks for concrete, actionable fixes. Make sure suggested code is valid PHP 7.4.

## Focus Areas

### Correctness
- Bugs, wrong logic and unhandled edge cases
- Proper error handling

### Architecture
- No business logic in controllers; use the command bus or a service
- New commands/events have a handler/listener registered in `app/config/messagebus.yml`
- Events that should be processed asynchronously implement `AsynchronousMessageInterface`
- Services and storages come with an interface
- Dependencies are wired in the service providers in `app/`

### Security
- Potential security vulnerabilities
- Input validation and sanitization
- Authentication/authorization logic (see `user_roles.yml` and `app/Security/`)
- No secrets or credentials in code or config files that are committed

### Performance
- Database query efficiency (Doctrine ORM / MongoDB ODM)
- Unnecessary calls to external APIs (CultureFeed, Insightly, Search API, ...)

### Testing
- New or changed code in `src/` is covered by unit tests in `test/`
- Test quality and missing edge cases

### Code Style
- Code passes `composer phpcs` (Symfony2 coding standard, see `phpcs-ruleset.xml`) and `composer phpstan`
- Only flag style issues that these tools would not catch

### API Backwards Compatibility
The API is consumed by the projectaanvraag frontend and the widgets. Flag changes that could break them:
- Renaming or removing an endpoint, or changing its HTTP method
- Renaming, removing or changing the type of fields in request or response bodies
- Changing HTTP status codes or error responses for existing scenarios
