# Quick Start Guide - HomeWarranty Pro Demo

This guide helps you quickly set up and understand the HomeWarranty Pro application.

## 📖 What is HomeWarranty Pro?

A web application for managing home warranty tickets between homeowners and builders/contractors.

**Key Features**:
- Homeowners submit warranty tickets for their properties
- Builders/managers review and resolve tickets via admin panel
- Role-based access control (Homeowner, Builder, Admin)
- Ticket workflow with status tracking
- Comment system with internal/public visibility

## 🚀 Quick Setup (5 minutes)

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & NPM
- SQLite (or MySQL/PostgreSQL)

### Installation Steps

```bash
# 1. Clone and navigate
git clone <repository-url>
cd home-warranty-pro

# 2. Install dependencies
composer install
npm install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Database setup
touch database/database.sqlite
php artisan migrate

# 5. Build frontend assets
npm run build

# 6. Start server
php artisan serve
```

Visit: http://localhost:8000

## 👥 Current Status

**⚠️ Important**: The application is ~70% complete. See `PROJECT_REVIEW.md` for detailed analysis.

**Architecture**: DDD with Rich Domain Models + Lean Service Layer (services only when needed)

### What Works ✅
- User authentication and registration
- Database schema and migrations
- Rich domain models with business logic (Ticket, Property, Comment, User)
- Role-based access control via UserRole enum
- Filament admin panel (basic)
- Unit tests for models (using PHPUnit attributes)
- State machine for ticket workflow

### What's Missing ❌
- Homeowner user interface (no views)
- Database seeders for demo
- Feature tests
- Image upload functionality
- Email notifications

### Known Issues 🔧
- `tests/Unit/TicketServiceTest.php` is obsolete (delete this file)
  - Service was removed in favor of rich domain models
  - Business logic lives in `Ticket`, `Property`, `Comment` models

## 📝 Test Accounts

Currently, you need to create test users manually:

```bash
php artisan tinker
```

```php
// Create homeowner
$homeowner = App\Models\User::factory()->create([
    'email' => 'homeowner@example.com',
    'role' => 'homeowner'
]);

// Create builder
$builder = App\Models\User::factory()->create([
    'email' => 'builder@example.com',
    'role' => 'builder'
]);

// Create admin
$admin = App\Models\User::factory()->create([
    'email' => 'admin@example.com',
    'role' => 'admin'
]);
```

Default password: `password`

## 🎯 Next Steps

To make this a fully functional demo, follow the action plan in `PROJECT_REVIEW.md`:

**Phase 1 (Critical - 2 days)**:
1. Delete obsolete TicketServiceTest
2. Build homeowner views (properties, tickets)
3. Complete controllers (thin, delegate to models)
4. Add database seeders

**Phase 2 (Features - 3 days)**:
1. Implement image uploads
2. Customize Filament admin
3. Add search/filtering
4. Basic notifications

**Phase 3 (Polish - 2 days)**:
1. Feature tests
2. UI/UX improvements
3. Documentation
4. Security review

**Total**: 7 days to demo-ready

## 📚 Documentation

- **.github/copilot-instructions.md** - Architecture guidelines and conventions
- **PROJECT_REVIEW.md** - Comprehensive analysis and action plan
- **README.md** - Project overview and features
- **DEPLOYMENT.md** - Production deployment guide

## 🔧 Development Commands

```bash
# Run tests
php artisan test

# Code quality
./vendor/bin/phpstan analyse
./vendor/bin/php-cs-fixer fix

# Watch frontend changes
npm run dev

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 🏗️ Architecture

**Domain-Driven Design with Rich Domain Models**

```
Homeowners → Properties → Tickets → Comments
                              ↓
                          Assigned to
                              ↓
                      Builders/Managers
```

**Layers**:
- **Domain Layer**: Models with business logic (Ticket, Property, Comment, User)
- **Service Layer**: Lean - only for complex cross-cutting operations
- **Presentation**: Thin controllers delegating to domain models
- **Authorization**: Policies enforcing role-based rules

**User Roles** (UserRole enum):
- **Homeowner**: Creates properties and tickets
- **Builder**: Manages tickets, adds internal comments
- **Admin**: Full system access

**Ticket Workflow** (State machine in Ticket model):
```
Submitted → Assigned → In Progress → Complete → Closed
```

## ⚠️ Known Issues

See `PROJECT_REVIEW.md` Section 2 for detailed list. Critical issues:

1. **Orphaned TicketServiceTest** - Delete this file (service intentionally removed)
2. **No Homeowner UI** - No views for properties/tickets management
3. **Controllers** - Need verification they're thin and delegate to models
4. **No Demo Data** - No seeders for quick testing

## 🆘 Troubleshooting

### "Class TicketService not found"
This is expected - the service was intentionally deleted. The app uses rich domain models instead. Delete `tests/Unit/TicketServiceTest.php` to remove this error.

### "No views found"
Homeowner views haven't been created yet. Admin panel works at `/admin`.

### Database errors
```bash
php artisan migrate:fresh
```

### Asset build errors
```bash
npm install
npm run build
```

## 📞 Support

For detailed implementation guidance, refer to:
1. `PROJECT_REVIEW.md` - Complete analysis
2. `README.md` - Feature overview
3. Laravel Documentation - https://laravel.com/docs

---

**Status**: 🟡 In Development  
**Architecture**: DDD with Rich Domain Models  
**Completion**: ~70%  
**Estimated to Demo-Ready**: 7 days (see PROJECT_REVIEW.md)
