# Queue Worker Setup Guide

This guide explains how to set up and configure Laravel queue workers for the MarketPulse application.

## Prerequisites

- Redis server installed and running
- Laravel application configured with Redis queue driver

## Configuration

### Environment Variables

Add the following to your `.env` file:

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_QUEUE=default
```

## Running Queue Workers

### Development (Local)

Run a single queue worker:

```bash
php artisan queue:work redis --tries=3 --timeout=90
```

Run queue worker in background:

```bash
php artisan queue:work redis --tries=3 --timeout=90 --daemon &
```

### Production (Supervisor)

Install Supervisor:

```bash
sudo apt-get install supervisor
```

Create supervisor configuration file `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

## Queue Jobs

The following queue jobs are configured:

- `PublishScheduledPost` - Publishes scheduled social media posts
- `SendEmailCampaign` - Sends email campaigns
- `ProcessEmailCampaignQueue` - Processes email campaign queue
- `GenerateAIContent` - Generates AI content asynchronously
- `GenerateReport` - Generates analytics reports
- `GenerateAgencyReport` - Generates agency reports
- `RefreshSocialTokens` - Refreshes expired social media tokens
- `MonitorCompetitors` - Monitors competitor activity
- `SendNotifications` - Sends notifications to users
- `ProcessWorkflow` - Executes automation workflows
- `ProcessAnalyticsJob` - Processes analytics data
- `SendInvoiceReminders` - Sends invoice reminders

## Monitoring

Monitor queue status:

```bash
php artisan queue:monitor redis --max=1000
```

View failed jobs:

```bash
php artisan queue:failed
```

Retry failed jobs:

```bash
php artisan queue:retry all
```

## Troubleshooting

### Queue not processing

1. Check Redis connection: `redis-cli ping`
2. Check queue configuration: `php artisan config:show queue`
3. Check worker logs: `tail -f storage/logs/worker.log`

### Jobs failing

1. Check failed jobs table: `php artisan queue:failed`
2. Review job logs in `storage/logs/laravel.log`
3. Check job timeout settings

