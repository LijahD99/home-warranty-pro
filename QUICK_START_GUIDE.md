# Quick Start Guide - HomeWarranty Pro Demo

This guide helps you quickly set up and run the HomeWarranty Pro application.

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
php artisan migrate:fresh --seed

# 5. Create storage link
php artisan storage:link

# 6. Build frontend assets
npm run build

# 7. Start server (or use composer dev for all services)
php artisan serve
# OR
composer dev  # Runs artisan serve, queue:listen, pail, and npm run dev concurrently
```

Visit: http://localhost:8000

## 👥 Demo Accounts

**✅ Database is automatically seeded with demo users**

### Login Credentials

**Homeowners**:
- Email: `homeowner1@example.com` | Password: `password`
- Email: `homeowner2@example.com` | Password: `password`
- Email: `homeowner3@example.com` | Password: `password`

**Builders/Managers**:
- Email: `builder1@homewarranty.com` | Password: `password`
- Email: `builder2@homewarranty.com` | Password: `password`

**Admin**:
- Email: `admin@homewarranty.com` | Password: `password`

**Demo Data Includes**:
- 5 properties across 3 homeowners
- 15 tickets in various states
- 30+ comments (mix of public and internal)
- Realistic warranty scenarios

## 🎯 Current Status

**✅ Demo Ready!** The application is fully functional for demonstration purposes.

**Architecture**: DDD with Rich Domain Models + Lean Service Layer

### What Works ✅
- ✅ User authentication and registration
- ✅ Complete homeowner interface (properties, tickets, dashboard)
- ✅ Rich domain models with business logic (Ticket, Property, Comment, User)
- ✅ Role-based access control via UserRole enum and policies
- ✅ Filament admin panel for builders/admins
- ✅ Image upload for tickets with display
- ✅ State machine for ticket workflow with validation
- ✅ Comment system with internal/public visibility
- ✅ Database seeders with realistic demo data
- ✅ Comprehensive test coverage (144 tests passing)
- ✅ Form validation with custom request classes

## 📱 Application Tour

### Homeowner Dashboard (/)
After logging in as a homeowner, you can:
1. **View Dashboard** - See property count, open tickets, and quick actions
2. **Manage Properties** - Create, edit, view, and delete properties
3. **Submit Tickets** - Create warranty tickets with descriptions and images
4. **Track Tickets** - View status, comments, and assigned builders
5. **Add Comments** - Communicate about ticket issues

### Builder/Admin Panel (/admin)
Builders and admins access via Filament at `/admin`:
1. **View All Tickets** - See tickets from all homeowners
2. **Assign Tickets** - Assign tickets to builders
3. **Update Status** - Transition tickets through workflow
4. **Internal Notes** - Add comments hidden from homeowners
5. **Manage Users** - Admin can manage all users

## 🔧 Development Commands

```bash
# Run all tests (144 tests)
php artisan test

# Run specific test suite
php artisan test --filter=PropertyManagementTest
php artisan test --filter=TicketWorkflowTest

# Code quality
./vendor/bin/phpstan analyse --level=5
./vendor/bin/php-cs-fixer fix

# Development mode (all services)
composer dev  # Runs: serve, queue, pail, npm dev

# Database refresh with demo data
php artisan migrate:fresh --seed

# Clear caches
php artisan optimize:clear
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
- **Domain Layer**: Models with business logic (`app/Models/`)
- **Service Layer**: Lean - only for complex operations (currently none needed)
- **Presentation**: Thin controllers (`app/Http/Controllers/`)
- **Authorization**: Policies enforcing role-based rules (`app/Policies/`)
- **Validation**: Form Request classes (`app/Http/Requests/`)

**User Roles** (UserRole enum):
- **Homeowner**: Creates properties and tickets
- **Builder**: Manages tickets, adds internal comments
- **Admin**: Full system access

**Ticket Workflow** (State machine in Ticket model):
```
Submitted → Assigned → In Progress → Complete → Closed
```

## 🧪 Testing

**Test Coverage**: 144 tests passing (299 assertions)

**Test Types**:
- ✅ Unit tests for all models (using `#[Test]` attributes)
- ✅ Feature tests for property management
- ✅ Feature tests for ticket workflow
- ✅ Feature tests for authentication
- ✅ Authorization tests for role-based access

```bash
# Run all tests
php artisan test

# Run with coverage report
php artisan test --coverage

# Run specific tests
php artisan test --filter=PropertyManagementTest
```

## 📚 Documentation

- **.github/copilot-instructions.md** - Architecture guidelines and conventions
- **PROJECT_REVIEW.md** - Comprehensive analysis and implementation notes
- **README.md** - Project overview and features
- **DEPLOYMENT.md** - Production deployment guide
- **DOCUMENTATION_INDEX.md** - Documentation directory

## ⚠️ Important Notes

### Architecture Decisions
- **No Service Layer for Simple CRUD** - Business logic lives in rich domain models
- **Thin Controllers** - Controllers delegate to models and policies
- **PSR-12 + Project Conventions** - Modified K&R braces, camelCase methods
- **TDD with DDD** - Tests use ubiquitous language and document business rules

### File Structure
```
app/
├── Enums/UserRole.php          # Role-based permission rules
├── Exceptions/                 # Domain-specific exceptions
├── Http/
│   ├── Controllers/            # Thin controllers
│   └── Requests/               # Form validation
├── Models/                     # Rich domain models
└── Policies/                   # Authorization logic

resources/views/
├── dashboard.blade.php         # Homeowner dashboard
├── properties/                 # Property CRUD views
└── tickets/                    # Ticket management views

database/seeders/
└── DemoSeeder.php             # Comprehensive demo data
```

## 🆘 Troubleshooting

### Database errors
```bash
php artisan migrate:fresh --seed
```

### Asset build errors
```bash
rm -rf node_modules package-lock.json
npm install
npm run build
```

### Storage link issues
```bash
php artisan storage:link
```

### Permission errors on uploads
```bash
chmod -R 775 storage bootstrap/cache
```

## 🎉 Next Steps

The application is **demo-ready**! You can:

1. **Try Different Roles** - Login as homeowner, builder, or admin
2. **Create Tickets** - Submit warranty requests with images
3. **Test Workflow** - Transition tickets through the state machine
4. **Explore Admin Panel** - Visit `/admin` as builder or admin

**Optional Enhancements**:
- Email notifications for ticket updates
- Advanced search and filtering
- Dashboard analytics widgets
- PDF export for tickets
- Mobile app integration

## 📞 Support

For detailed information:
1. **PROJECT_REVIEW.md** - Complete analysis and notes
2. **README.md** - Feature overview
3. **Laravel Docs** - https://laravel.com/docs

---

**Status**: ✅ **Demo Ready**
**Architecture**: DDD with Rich Domain Models
**Completion**: ~95% (Core features complete)
**Tests**: 144 passing (299 assertions)
**Demo Data**: Pre-seeded with 6 users, 5 properties, 15 tickets
