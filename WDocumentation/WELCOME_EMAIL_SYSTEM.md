# Welcome Email System Documentation

## Overview

The Developer Knowledge Snippet Manager includes a queued welcome email system that sends a personalized welcome email to new users upon registration. This system uses Laravel's queue infrastructure with Redis as the backend for reliable asynchronous email delivery.

## Architecture

### Components

1. **WelcomeMail Mailable** (`app/Mail/WelcomeMail.php`)
   - Laravel Mailable class that defines the welcome email
   - Uses Blade templating for email content
   - Automatically serializes the User model for queueing

2. **SendWelcomeEmailJob** (`app/Jobs/SendWelcomeEmailJob.php`)
   - Queued job that handles email sending
   - Configured to use Redis queue connection
   - Implements `ShouldQueue` interface for async execution
   - Receives User model and dispatches the WelcomeMail

3. **RegisteredUserController** (`app/Http/Controllers/Auth/RegisteredUserController.php`)
   - Updated to dispatch the welcome email job instead of sending directly
   - Ensures non-blocking registration flow

4. **Email Template** (`resources/views/emails/welcome.blade.php`)
   - Laravel mail component template
   - Personalized greeting with user's name
   - Feature overview and getting started guide

## Configuration

### Queue Setup

**Default Connection:** Redis
- Configured in `config/queue.php`
- Default queue: `default`
- Retry after: 90 seconds

```php
'default' => env('QUEUE_CONNECTION', 'redis'),

'redis' => [
    'driver' => 'redis',
    'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
    'queue' => env('REDIS_QUEUE', 'default'),
    'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 90),
    'block_for' => null,
    'after_commit' => false,
],
```

### Mail Configuration

**Mail Driver:** Log (configurable via `.env`)
- Configured in `config/mail.php`
- Default mailer: `log` (can be changed to `smtp`, `mailgun`, etc.)
- From address: Configured via `MAIL_FROM_ADDRESS` and `MAIL_FROM_NAME`

## Flow

```
User Registration
        ↓
RegisteredUserController::store()
        ↓
User Created
        ↓
SendWelcomeEmailJob::dispatch($user)
        ↓
Job Queued to Redis
        ↓
Queue Worker Processes Job
        ↓
SendWelcomeEmailJob::handle()
        ↓
Mail::send(new WelcomeMail($user))
        ↓
Email Sent
```

## Usage

### For Users

When a new user registers:
1. User fills in registration form (name, email, password)
2. User account is created
3. Welcome email job is automatically dispatched to the queue
4. User is logged in and redirected to dashboard
5. Queue worker processes the job and sends the welcome email asynchronously

### For Developers

#### Dispatch Welcome Email Job Manually

```php
use App\Jobs\SendWelcomeEmailJob;
use App\Models\User;

$user = User::find(1);
SendWelcomeEmailJob::dispatch($user);
```

#### Send Welcome Email Synchronously (Testing)

```php
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;

$user = User::find(1);
Mail::send(new WelcomeMail($user));
```

#### Customize the Welcome Email

Edit `resources/views/emails/welcome.blade.php` to modify the email template.

## Running Queue Workers

### Development (Process Jobs Synchronously)

For testing without a queue worker, set `QUEUE_CONNECTION=sync` in `.env`:

```bash
QUEUE_CONNECTION=sync
php artisan serve
```

### Production (With Queue Workers)

Start a queue worker to process jobs from Redis:

```bash
php artisan queue:work redis --queue=default
```

Or with Docker (included in sail):

```bash
docker compose exec laravel.test php artisan queue:work redis --queue=default
```

### Monitor Queue

View failed jobs:

```bash
php artisan queue:failed
```

Retry failed jobs:

```bash
php artisan queue:retry all
```

## Testing

### Test Classes

- `tests/Feature/WelcomeEmailTest.php` - Comprehensive tests for the welcome email system

### Running Tests

```bash
# Run welcome email tests
php artisan test tests/Feature/WelcomeEmailTest.php

# Run specific test
php artisan test tests/Feature/WelcomeEmailTest.php --filter=test_welcome_email_is_queued_on_registration

# Run with detailed output
php artisan test tests/Feature/WelcomeEmailTest.php --testdox
```

### Test Coverage

The test suite includes:

1. **test_welcome_email_is_queued_on_registration** - Verifies job is queued after registration
2. **test_welcome_email_is_sent_to_correct_user** - Validates correct user receives email
3. **test_welcome_mail_renders_correctly** - Checks email renders with correct subject
4. **test_welcome_mail_contains_user_name** - Verifies personalization with user name
5. **test_send_welcome_email_job_sends_mail** - Tests job execution and mail sending
6. **test_job_uses_redis_queue** - Confirms Redis queue connection

## Environment Variables

Add these to your `.env` file for full configuration:

```env
# Queue Configuration
QUEUE_CONNECTION=redis

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="Snippet Manager"

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Troubleshooting

### Jobs Not Being Processed

1. Check if queue worker is running:
   ```bash
   ps aux | grep queue:work
   ```

2. Verify Redis connection:
   ```bash
   php artisan tinker
   Redis::ping()
   ```

3. Check job logs:
   ```bash
   tail storage/logs/laravel.log
   ```

### Email Not Sending

1. Check mail configuration in `.env`
2. Verify `MAIL_MAILER` is set to a valid driver (smtp, mailgun, etc.)
3. For testing, check log driver output:
   ```bash
   tail storage/logs/laravel.log
   ```

### Queue Connection Issues

1. Ensure Redis is running:
   ```bash
   docker compose ps | grep redis
   ```

2. Test Redis connection:
   ```bash
   php artisan tinker
   Redis::set('test', 'value')
   Redis::get('test')
   ```

## Performance Considerations

- **Async Processing:** Email sending doesn't block user registration
- **Retry Logic:** Failed jobs automatically retry after 90 seconds
- **Queue Workers:** Scale workers based on email volume
- **Redis:** Highly performant queue backend suitable for production

## Future Enhancements

- Email templates for other events (password reset, email verification)
- Email scheduling and batching
- Welcome email series (multiple emails over time)
- Configurable email subject and content
- Email unsubscribe management
