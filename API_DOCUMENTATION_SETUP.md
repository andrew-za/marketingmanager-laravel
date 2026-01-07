# API Documentation Setup

This document outlines how to set up API documentation for the MarketPulse API.

## Option 1: Laravel Scribe

Laravel Scribe is a popular API documentation generator for Laravel.

### Installation

```bash
composer require --dev knuckleswtf/scribe
php artisan vendor:publish --tag=scribe-config
```

### Configuration

Edit `config/scribe.php` to configure:
- API title and description
- Base URL
- Authentication (Sanctum tokens)
- Route groups
- Response examples

### Generate Documentation

```bash
php artisan scribe:generate
```

Documentation will be available at `/docs` route.

## Option 2: Laravel Scramble

Laravel Scramble is another excellent option for API documentation.

### Installation

```bash
composer require --dev dedoc/scramble
```

### Configuration

Scramble automatically generates documentation from your routes and controllers. No additional configuration needed.

### Access Documentation

Documentation will be available at `/api-docs` route.

## Recommended: Laravel Scramble

For this project, Laravel Scramble is recommended because:
- Zero configuration required
- Automatically generates from existing code
- Supports OpenAPI 3.0 specification
- Better integration with Laravel 12

## API Endpoints Documentation

All API endpoints are documented in `routes/api.php` with:
- Route definitions
- Middleware (authentication, rate limiting, permissions)
- Controller methods
- Request/Response formats

## API Response Format

All API responses follow this structure:

```json
{
  "success": true,
  "data": {},
  "message": "Operation successful",
  "meta": {
    "pagination": {}
  }
}
```

## Error Response Format

```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Error message",
    "details": {}
  }
}
```

## Authentication

API uses Laravel Sanctum for token-based authentication:
- Register: `POST /api/auth/register`
- Login: `POST /api/auth/login`
- Use token: `Authorization: Bearer {token}`

## Rate Limiting

API is rate-limited to 60 requests per minute per user/IP.

