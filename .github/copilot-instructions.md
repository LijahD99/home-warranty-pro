# Copilot Instructions - HomeWarranty Pro

## Project Overview

Laravel 12 + Filament 4 application for managing home warranty tickets. Three user roles (homeowner, builder, admin) with strict role-based access and a state machine workflow for ticket status transitions.

## Application Structure

### Homeowner UI
Located at `/` - homeowners manage their tickets and properties
- Controllers in `app/Http/Controllers/`

### Filament Admin Panel
Located at `/admin` - builders/managers manage all system data
- Resources in `app/Filament/Resources/{Entity}/` with subdirs: `Pages/`, `Schemas/`, `Tables/`
- Forms use `Schemas/EntityForm.php` pattern: `Select::make('assigned_to')->relationship('assignedTo', 'name', fn($query) => $query->whereIn('role', ['builder', 'admin']))`
- Status updates in admin must respect state machine transitions

## Architecture & Patterns

We follow Domain-Driven Design (DDD) principles with a layered architecture:
- **Domain Layer**: Rich domain models encapsulating business logic and rules
- - located in `app/Models/`
- **Service Layer**: (lean) application services coordinating domain operations
- - located in `app/Services/`
- - should contain minimal logic;
- - delegate to domain models;
- - cross-cutting concerns like transactions, logging;
- - should not directly manipulate model properties that enforce business rules
- - not needed for simple CRUD operations handled by models
- **Presentation Layer**: Controllers and Filament resources handling HTTP requests and UI
- - located in `app/Http/Controllers/` and `app/Filament/Resources/`
- - should call services/models, not contain business logic
- - does not need to access the service layer for simple operations
- **Data Access Layer**: Eloquent models and repositories for database interactions
- - located in `app/Models/` and `app/Repositories/`

**Be Mindful, Avoid Anti-Patterns**:
- Fat controllers: Keep controllers thin, delegate to services/models
- Anemic models: Encapsulate business logic in models, not just data containers
- Bypassing domain logic: Always use model methods to enforce rules, never set properties directly
- Overusing service layer: Use services only for complex operations, not simple CRUD
- "God classes": Keep models focused on a single aggregate root. Keep service classes focused on specific use cases.
- "architecture sinkhole": Avoid unnecessary layers that add complexity without logic or value. Some operations can be handled directly by models.

## Key Workflows

### Development Setup
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
npm run build
```

### Running Dev Environment
Custom composer script runs everything concurrently:
```bash
composer dev  # Runs: artisan serve, queue:listen, pail (logs), npm run dev
```

### Testing
```bash
php artisan test  # Run tests
./vendor/bin/phpstan analyse  # Static analysis (level 5)
./vendor/bin/php-cs-fixer fix # PSR-12 formatting
```

### Creating Test Users
```php
php artisan tinker
$homeowner = User::factory()->create(['email' => 'test@example.com', 'role' => 'homeowner']);
$builder = User::factory()->create(['email' => 'builder@example.com', 'role' => 'builder']);
// Default password: 'password'
```

## Critical Conventions

### PSR-12 coding standards + project-specific conventions:

#### PSR-12 Casing Standards

**camelCase** for:
methods, functions, and blade component constructor parameters

examples: `assignTo()`, `transitionTo()`, `canManageUsers()`

Blade component constructor parameters use **camelCase**
example:
  In a Blade component class:
  ```php
  public function __construct(
      public User $currentUser,
      public Ticket $ticket
  ) {
      //
  }
  ```
  And in the corresponding Blade view:
  ```php
  <x-some-component :currentUser="$currentUser" :ticket="$ticket" />
  <!-- or short attribute syntax -->
  <x-some-component :$currentUser :$ticket />
  <!-- or -->
  <x-some-component current-user="{{ $currentUser }}" ticket="{{ $ticket }}" />
  ```

**Pascal Case** for:
Classes, Enums, Traits
examples: `UserRole`, `InvalidStatusTransitionException`, `TicketPolicy`

#### if-else Standards

**ternary operator**
only for blade views when assigning simple values:
```blade
{{ $user->isAdmin() ? 'Admin' : 'User' }}
```

**modified K&R style for control structures**
Braces on same line for control structures
```php
if ($condition) {
    // code
}
else {
    // code
}
```

### Model Methods Organization
Models group methods under comment headers:
```php
// ============================================
// Domain Logic - Status Transitions
// ============================================

// ============================================
// Domain Logic - Business Queries
// ============================================
```

### Exception Handling
Domain exceptions in `app/Exceptions/`:
- `InvalidStatusTransitionException::fromTo($from, $to)`
- `InvalidTicketAssignmentException::userNotAuthorized($role)`
- `InvalidPropertyException::hasOpenTickets($count)`

Throw when business rules violated; catch in controllers/Filament resources.

### Testing Strategy
Test Driven Development (TDD) paired with Domain Driven Design (DDD):

- test behaviors over implementation
- tests should use ubiquitous language from domain
- tests should document business rules clearly
- "Chicago style"/inside-out/"classicist" TDD
- Use PHPUnit attributes: `#[Test]` instead of `test_` prefix

### Development Process
Must have code design and planning approved before implementation.
Must follow "Chicago style" TDD approach.

## File Locations

- Models with business logic: `app/Models/{Model}.php`
- Enums with role rules: `app/Enums/UserRole.php`
- Policies: `app/Policies/{Model}Policy.php`
- Controllers (homeowner UI): `app/Http/Controllers/`
- Filament admin: `app/Filament/Resources/`
- Migrations: `database/migrations/`
- Factories: `database/factories/`
- Tests: `tests/Unit/` and `tests/Feature/`
