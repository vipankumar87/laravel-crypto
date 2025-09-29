# Laravel Crypto App - Development Guide

## Project Overview
This is a Laravel 12 application with best practices implemented for security, logging, and permissions.

## Development Commands

### Testing
```bash
php artisan test
```

### Code Quality
```bash
./vendor/bin/pint # Laravel Pint for code formatting
```

### Database
```bash
php artisan migrate:fresh --seed # Reset and seed database
php artisan migrate:status # Check migration status
```

### Development Server
```bash
php artisan serve # Start development server on http://localhost:8000
```

## Installed Packages

### Security & Permissions
- **spatie/laravel-permission**: Role and permission management
- **spatie/laravel-activitylog**: Activity logging for audit trails

### Built-in Laravel Features
- Authentication system with User model
- Database migrations with SQLite (development)
- Queue system for background jobs
- Cache system for performance
- Mail system for notifications

## Best Practices Implemented

1. **Environment Configuration**: Proper .env setup with secure defaults
2. **Database**: SQLite for development, easily switchable to MySQL/PostgreSQL for production
3. **Security**: Activity logging for user actions, role-based permissions
4. **Code Quality**: Laravel Pint for code formatting
5. **Testing**: PHPUnit setup with Feature and Unit test directories

## Getting Started

1. Install dependencies: `composer install`
2. Set up environment: Copy `.env.example` to `.env` and configure
3. Generate app key: `php artisan key:generate`
4. Run migrations: `php artisan migrate`
5. Start development server: `php artisan serve`

## Architecture

- **Models**: Located in `app/Models/` with proper relationships and traits
- **Controllers**: RESTful controllers in `app/Http/Controllers/`
- **Migrations**: Database schema in `database/migrations/`
- **Routes**: Web routes in `routes/web.php`, API routes in `routes/api.php`