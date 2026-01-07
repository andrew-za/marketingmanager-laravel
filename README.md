# MarketPulse - Laravel 12 Marketing Automation Platform

AI-powered marketing automation platform built with Laravel 12.

## Requirements

- PHP 8.3+
- Composer
- MySQL 8.0+ / PostgreSQL 15+
- Redis 7.0+
- Node.js & NPM

## Installation

1. Clone the repository
2. Install dependencies: `composer install`
3. Copy `.env.example` to `.env`: `cp .env.example .env`
4. Generate application key: `php artisan key:generate`
5. Configure database in `.env`
6. Run migrations: `php artisan migrate`
7. Seed database: `php artisan db:seed`
8. Install frontend dependencies: `npm install`
9. Build assets: `npm run build`
10. Start development server: `php artisan serve`

## Project Structure

See SPECIFICATIONS.md for complete feature specifications and architecture details.

## License

MIT


