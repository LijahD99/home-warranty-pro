# HomeWarranty Pro - MVP

A secure, efficient, and maintainable web application for managing home warranty tickets, built with Laravel 12 and Filament.

## 🎯 Features

### User Roles & Authentication
- **Homeowners**: Submit and track warranty tickets for their properties
- **Builders/Managers**: Manage all tickets, assign work, update statuses
- **System Admins**: Full system access and user management

### Core Functionality
- ✅ Secure authentication with auto-assigned homeowner role on registration
- ✅ Property/Home management for homeowners
- ✅ Warranty ticket submission with image uploads
- ✅ Role-based access control with Laravel Policies
- ✅ Ticket status workflow (Submitted → Assigned → In Progress → Complete → Closed)
- ✅ Filament admin panel for builders/managers
- ✅ Comment system with public/internal visibility
- ✅ Service layer architecture for business logic
- ✅ Comprehensive unit tests

## 🛠️ Technology Stack

- **Framework**: Laravel 12
- **Admin Panel**: Filament 4
- **Authentication**: Laravel Breeze
- **Database**: SQLite (development) / MySQL/PostgreSQL (production)
- **Code Quality**: PHPStan, PHP-CS-Fixer
- **Testing**: PHPUnit

## 📋 Requirements

- PHP 8.2+
- Composer
- Node.js & NPM
- SQLite/MySQL/PostgreSQL

## 🚀 Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd home-warranty-pro
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`:

```env
DB_CONNECTION=sqlite
# OR for MySQL
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=home_warranty_pro
# DB_USERNAME=root
# DB_PASSWORD=
```

### 4. Run migrations

```bash
php artisan migrate
```

### 5. Create storage link

```bash
php artisan storage:link
```

### 6. Build assets

```bash
npm run build
# OR for development
npm run dev
```

### 7. Start the development server

```bash
php artisan serve
```

Visit `http://localhost:8000` for the homeowner interface.
Visit `http://localhost:8000/admin` for the builder/manager panel.

## 👥 User Roles

### Creating Test Users

```bash
php artisan tinker
```

```php
// Create a homeowner
$homeowner = \App\Models\User::factory()->create([
    'email' => 'homeowner@example.com',
    'role' => 'homeowner'
]);

// Create a builder
$builder = \App\Models\User::factory()->create([
    'email' => 'builder@example.com',
    'role' => 'builder'
]);

// Create an admin
$admin = \App\Models\User::factory()->create([
    'email' => 'admin@example.com',
    'role' => 'admin'
]);
```

Default password for factory users: `password`

## 🧪 Testing

Run the full test suite:

```bash
php artisan test
```

Run specific tests:

```bash
php artisan test --filter=TicketServiceTest
```

## 🔍 Code Quality

### Run PHPStan

```bash
./vendor/bin/phpstan analyse
```

### Run PHP-CS-Fixer

```bash
./vendor/bin/php-cs-fixer fix
```

## 📐 Architecture

### Service Layer
Business logic is encapsulated in service classes:
- `App\Services\TicketService`: Handles ticket creation, status transitions, and assignments

### Policies
Authorization is managed through Laravel Policies:
- `App\Policies\TicketPolicy`: Controls ticket access
- `App\Policies\PropertyPolicy`: Controls property access

### Middleware
- `App\Http\Middleware\EnsureUserHasRole`: Role-based route protection

## 📊 Database Schema

### Users
- id, name, email, password, role, timestamps

### Properties
- id, user_id, address, city, state, zip_code, notes, timestamps

### Tickets
- id, property_id, user_id, assigned_to, area_of_issue, description, image_path, status, timestamps

### Comments
- id, ticket_id, user_id, comment, is_internal, timestamps

## 🌐 Deployment

### Laravel Forge
1. Create a new server on Laravel Forge
2. Connect your repository
3. Configure environment variables
4. Set up database
5. Deploy

### Manual Deployment
1. Upload code to server
2. Run `composer install --optimize-autoloader --no-dev`
3. Run `npm install && npm run build`
4. Configure `.env` file
5. Run `php artisan migrate --force`
6. Set permissions: `chmod -R 755 storage bootstrap/cache`
7. Create symbolic link: `php artisan storage:link`

### Environment Configuration (Production)

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password
```

## 📝 API Endpoints

### Homeowner Routes
- `GET /properties` - List properties
- `POST /properties` - Create property
- `GET /tickets` - List own tickets
- `POST /tickets` - Create ticket
- `GET /tickets/{ticket}` - View ticket
- `POST /tickets/{ticket}/comments` - Add comment

### Builder/Manager Routes (Filament Admin)
- `GET /admin` - Admin dashboard
- `GET /admin/tickets` - Manage all tickets
- `GET /admin/users` - Manage users

## 🔐 Security Features

- Role-based access control (RBAC)
- Laravel Policies for authorization
- CSRF protection
- Password hashing with bcrypt
- Input validation and sanitization
- Secure file uploads

## 📚 Key Features Detail

### Ticket Workflow
The ticket status follows a strict state machine:
1. **Submitted** → Can transition to: Assigned
2. **Assigned** → Can transition to: In Progress
3. **In Progress** → Can transition to: Complete
4. **Complete** → Can transition to: Closed

Invalid transitions will throw an exception.

### Comment Visibility
- **Public comments**: Visible to homeowners and builders
- **Internal comments**: Only visible to builders and admins

## 🤝 Contributing

1. Follow PSR-12 coding standards
2. Use snake_case for variables
3. Write unit tests for new features
4. Run code quality tools before committing

## 📄 License

This project is proprietary and confidential.

## 👨‍💻 Developer

Built to showcase modern Laravel application development practices including:
- Clean Architecture
- Repository/Service Pattern
- Test-Driven Development
- Code Quality Standards
- Role-Based Access Control

