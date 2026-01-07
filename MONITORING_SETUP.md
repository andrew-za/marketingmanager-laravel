# Monitoring Setup Guide

This guide explains how to set up monitoring tools for the MarketPulse application.

## Laravel Telescope

Laravel Telescope provides insight into requests, exceptions, database queries, queued jobs, mail, notifications, cache operations, scheduled tasks, variable dumps, and more.

### Installation

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

### Configuration

Publish config file:

```bash
php artisan vendor:publish --tag=telescope-config
```

Update `config/telescope.php` to control what data Telescope stores.

### Access Telescope

Visit `/telescope` in your application (only accessible in non-production environments by default).

## Sentry Error Tracking

Sentry provides real-time error tracking and performance monitoring.

### Installation

```bash
composer require sentry/sentry-laravel
php artisan vendor:publish --provider="Sentry\Laravel\ServiceProvider"
```

### Configuration

Add to `.env`:

```env
SENTRY_LARAVEL_DSN=https://your-sentry-dsn@sentry.io/project-id
SENTRY_TRACES_SAMPLE_RATE=0.2
```

Update `config/sentry.php` with your DSN and other settings.

## APM Tools

### New Relic

1. Sign up for New Relic account
2. Install New Relic PHP agent
3. Configure in `newrelic.ini`:

```ini
newrelic.appname = "MarketPulse"
newrelic.license = "your-license-key"
```

### Datadog

1. Sign up for Datadog account
2. Install Datadog PHP tracer:

```bash
composer require datadog/dd-trace
```

3. Configure in `.env`:

```env
DD_SERVICE=marketpulse
DD_ENV=production
DD_VERSION=1.0.0
DD_TRACE_AGENT_URL=http://localhost:8126
```

## Performance Monitoring

### Query Performance

Enable query logging in `config/database.php`:

```php
'logging' => [
    'enabled' => env('DB_QUERY_LOG', false),
    'slow_threshold' => env('DB_SLOW_QUERY_THRESHOLD', 1000),
],
```

### API Response Times

Monitor API endpoints using middleware:

```php
// Log slow API requests
if ($request->is('api/*') && $response->getStatusCode() === 200) {
    $duration = microtime(true) - LARAVEL_START;
    if ($duration > 1.0) {
        Log::warning('Slow API request', [
            'url' => $request->url(),
            'duration' => $duration,
        ]);
    }
}
```

## Uptime Monitoring

### External Services

- **UptimeRobot** - Free uptime monitoring
- **Pingdom** - Advanced uptime and performance monitoring
- **StatusCake** - Uptime monitoring with alerts

### Health Check Endpoint

Laravel provides a health check endpoint at `/up`. Configure in `routes/web.php` or use Laravel's built-in health check.

## Log Management

### Log Rotation

Configure log rotation in `/etc/logrotate.d/laravel`:

```
/path/to/project/storage/logs/*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        [ -f /path/to/project/storage/framework/sessions ] && find /path/to/project/storage/framework/sessions -type f -mtime +14 -delete
    endscript
}
```

### Centralized Logging

Consider using:
- **Papertrail** - Cloud-based log management
- **Loggly** - Log management and analytics
- **ELK Stack** - Elasticsearch, Logstash, Kibana

## System Monitoring

### Server Resources

Monitor server resources using:
- **htop** - Interactive process viewer
- **iostat** - CPU and I/O statistics
- **netstat** - Network connections

### Database Monitoring

- **MySQL Workbench** - MySQL monitoring
- **pgAdmin** - PostgreSQL monitoring
- **Redis Commander** - Redis monitoring

## Alerting

Set up alerts for:
- High error rates
- Slow response times
- Queue backlog
- Disk space
- Memory usage
- Database connection issues

