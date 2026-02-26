# Code Explanation Feature Implementation Guide

## Overview

The "Explain This Code" feature automatically generates AI-powered explanations for public code snippets using OpenAI's GPT-4 model. The feature is fully asynchronous, rate-limited, and follows clean architecture principles.

## Architecture

```
User (UI) 
  ↓
ExplainSnippet (Livewire Component)
  ↓
GenerateSnippetExplanation (Queued Job)
  ↓
AIExplanationService (Service Layer)
  ↓
OpenAI API
  ↓
SnippetExplanation (Model/Database)
```

## What Was Created

### 1. Database Migration
**File:** `database/migrations/2026_02_26_create_snippet_explanations_table.php`

- Creates `snippet_explanations` table
- Fields: id, snippet_id (unique), user_id, content (longText), timestamps
- Foreign key relationship with snippets table
- Ensures one explanation per snippet

### 2. Models
**Files:**
- `app/Models/SnippetExplanation.php` - New model for explanations
- `app/Models/Snippet.php` - Updated with `explanation()` relationship

**Relationships:**
```php
// In Snippet model
public function explanation()
{
    return $this->hasOne(SnippetExplanation::class);
}

// In SnippetExplanation model
public function snippet(): BelongsTo
public function user(): BelongsTo
```

### 3. Service Classes

#### AIExplanationService
**File:** `app/Services/AIExplanationService.php`

Handles all OpenAI communication:
```php
// Main method
public function generateExplanation(Snippet $snippet, int $userId): string

// Helper methods
public function hasExplanation(Snippet $snippet): bool
public function getExplanation(Snippet $snippet): ?SnippetExplanation
public function deleteExplanation(Snippet $snippet): bool
```

**System Prompt:**
```
Explains code clearly and concisely
1. Overview of what code does
2. Step-by-step explanation
3. Highlights important concepts
4. Suggests improvements
```

#### ExplanationRateLimiter
**File:** `app/Services/ExplanationRateLimiter.php`

Rate limiting using Laravel Cache:
```php
// Max 10 explanations per user per day
public function canGenerateExplanation(int $userId): bool
public function incrementCount(int $userId): int
public function getRemainingExplanations(int $userId): int
public function reset(int $userId): void
```

### 4. Job Queue
**File:** `app/Jobs/GenerateSnippetExplanation.php`

Implements `ShouldQueue` for async processing:
- Timeout: 120 seconds
- Backoff: 5 seconds
- Max exceptions: 3
- Logs failures and successes
- Includes failure handler

### 5. Livewire Component
**File:** `app/Livewire/ExplainSnippet.php`

Handles user interaction:
```php
public function generateExplanation() // Dispatch job + rate limit
public function regenerateExplanation() // Delete old + create new
public function checkExplanation() // Called via polling every 5s
```

**Features:**
- Rate limit checking
- Authentication verification
- Loading state management
- Error handling
- Polling for job completion

### 6. Blade Views

#### Component View
**File:** `resources/views/livewire/explain-snippet.blade.php`

- Shows "Explain This Code" button
- Displays loading spinner with message
- Renders explanation as Markdown
- Shows regenerate button
- Error messages
- Daily limit display
- Login prompt for unauthenticated users

#### Integration Views
Added to 3 snippet listing pages:
- `resources/views/livewire/snippets-index.blade.php` (Public snippets)
- `resources/views/livewire/my-snippets.blade.php` (Your snippets)
- `resources/views/livewire/saved-snippets-index.blade.php` (Saved snippets)

### 7. Configuration
**Files:**
- `config/services.php` - Added OpenAI configuration

```php
'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
    'model' => env('OPENAI_MODEL', 'gpt-4'),
],
```

## Installation & Setup

### Prerequisites
- Laravel 12
- Livewire 3
- OpenAI API account

### Step-by-Step Setup

#### 1. Install OpenAI PHP Library
```bash
composer require openai-php/laravel
```

#### 2. Configure Environment
Add to `.env`:
```env
OPENAI_API_KEY=sk-...your-key-here...
OPENAI_MODEL=gpt-4
QUEUE_CONNECTION=database
```

#### 3. Run Migrations
```bash
php artisan migrate
```

Creates:
- `snippet_explanations` table

#### 4. Setup Queue

**Option A: Database Queue (Recommended for Development)**
```bash
php artisan queue:table
php artisan migrate
```

Set in `.env`:
```env
QUEUE_CONNECTION=database
```

**Option B: Redis Queue**
Set in `.env`:
```env
QUEUE_CONNECTION=redis
```

**Option C: Sync (Synchronous - for testing only)**
Set in `.env`:
```env
QUEUE_CONNECTION=sync
```

#### 5. Start Queue Worker
```bash
# Development
php artisan queue:work

# With options
php artisan queue:work --tries=3 --timeout=120

# Production (background, restart on failure)
# Use supervisor or similar process manager
```

## Usage

### For Users

1. **Navigate to a public snippet**
   - All Snippets page
   - My Snippets page
   - Saved Snippets page

2. **Click "Explain This Code" button**
   - Shows remaining daily explanations
   - Button disabled if limit reached

3. **Wait for explanation**
   - Loading spinner appears
   - Livewire polls every 5 seconds
   - Explanation displays as formatted Markdown

4. **Regenerate if needed**
   - Click "Regenerate" button
   - Old explanation deleted
   - New job dispatched
   - Decrements daily limit again

### For Developers

**Check explanation status:**
```php
$snippet = Snippet::find(1);
$explanation = $snippet->explanation;

if ($explanation) {
    echo $explanation->content;
}
```

**Manually generate explanation:**
```php
$service = app(AIExplanationService::class);
$explanation = $service->generateExplanation($snippet, auth()->id());
```

**Reset rate limit for testing:**
```php
$limiter = app(ExplanationRateLimiter::class);
$limiter->reset(auth()->id());
```

## UI/UX Flow

```
Initial State
    ↓
User clicks "Explain This Code"
    ↓
Validation
  ├─ Not logged in? → Show login prompt
  └─ Rate limited? → Show limit message
    ↓
Job dispatched
    ↓
Loading state shown
    ↓
Livewire polls every 5 seconds
    ↓
Job completes (OpenAI → Database)
    ↓
Component detects explanation exists
    ↓
Loading ends, explanation displays
    ↓
"Regenerate" button shown
```

## Rate Limiting

**Default:** 10 explanations per user per day

**How it works:**
1. User clicks button → Check if under limit
2. If yes → Increment counter (stored in cache)
3. Cache key includes date: `explanation_count_{user_id}_{YYYY-MM-DD}`
4. Expires at midnight (24 hours)
5. Cache reset at midnight automatically

**Monitor limits:**
```php
$limiter = app(ExplanationRateLimiter::class);
$remaining = $limiter->getRemainingExplanations(auth()->id());
echo "Remaining: $remaining";
```

**Reset for user (admin only):**
```php
$limiter->reset($user->id);
```

## Error Handling

### User-Facing Errors

1. **Rate limit exceeded**
   - Message: "You have reached your daily limit..."
   - Action: Try again tomorrow

2. **API failure**
   - Message: "Failed to generate explanation..."
   - Action: Retry or report

3. **Job timeout (30+ seconds)**
   - Shows loading
   - Will retry up to 3 times automatically
   - Eventual error if persistent

### Logging

All errors logged to `storage/logs/laravel.log`:
- Job dispatch failures
- API errors
- Job failures
- Rate limit issues

Watch logs:
```bash
tail -f storage/logs/laravel.log
```

## Performance Considerations

### Database
- `snippet_explanations` table with indexes on:
  - `snippet_id` (unique)
  - `user_id`
  - `snippet_id` + `user_id`

### Caching
- Rate limits use Redis/Cache (very fast)
- No database queries for rate limiting

### Queue Processing
- Async job processing (non-blocking)
- Typical generation time: 10-30 seconds
- Can handle many concurrent explanations

### API Costs

Estimated costs per explanation:
```
Input tokens: ~500-1000 tokens → $0.015-0.030
Output tokens: ~500-1500 tokens → $0.030-0.090
Total per explanation: ~$0.05-0.12
```

**Monitor usage:**
- OpenAI Dashboard: https://platform.openai.com/account/billing/overview
- Set usage limits in account settings

## Testing

### Manual Testing Checklist

- [ ] Click "Explain This Code" on a public snippet
- [ ] See loading spinner
- [ ] Wait for explanation to appear (~10-30 seconds)
- [ ] Verify explanation is formatted markdown
- [ ] Click "Regenerate" button
- [ ] See new explanation after regeneration
- [ ] Check daily limit counter decrements
- [ ] Exhaust daily limit and verify disable message
- [ ] Log out and verify login prompt shown
- [ ] Queue worker running: `php artisan queue:work`
- [ ] Check logs for no errors

### Automated Testing

```php
// Test rate limiting
$limiter = app(ExplanationRateLimiter::class);
$this->assertTrue($limiter->canGenerateExplanation($user->id));
$limiter->incrementCount($user->id);
$this->assertFalse($limiter->canGenerateExplanation($user->id)); // After 10

// Test service
$service = app(AIExplanationService::class);
$explanation = $service->generateExplanation($snippet, $user->id);
$this->assertNotEmpty($explanation);
$this->assertTrue($service->hasExplanation($snippet));
```

## Troubleshooting

### Queue not processing jobs

**Check 1: Is queue worker running?**
```bash
ps aux | grep "queue:work"
```

**Check 2: Queue connection in `.env`**
```env
QUEUE_CONNECTION=database  # Or redis, sqs, etc.
```

**Check 3: Check failed jobs**
```bash
php artisan queue:failed
php artisan queue:retry {id}
```

### Explanations not generating

**Check 1: OpenAI API key valid**
```php
echo config('services.openai.api_key');
```

**Check 2: API has credits**
- Check: https://platform.openai.com/account/billing/overview

**Check 3: Check logs**
```bash
tail -f storage/logs/laravel.log | grep -i openai
```

### Markdown not rendering

**Issue:** Explanation shows raw markdown instead of formatted

**Solution:** Ensure `Str::markdown()` is called in view:
```blade
{!! Str::markdown($explanation->content) !!}
```

### Rate limiting not working

**Reset cache:**
```bash
php artisan cache:clear
php artisan cache:forget 'explanation_count_{user_id}_{date}'
```

## Future Enhancements

1. **Multiple AI Models**
   - Support GPT-3.5, Claude, etc.

2. **Different Explanation Styles**
   - Beginner, Intermediate, Advanced
   - Code review style

3. **Caching Identical Code**
   - Hash code and reuse explanations

4. **Admin Dashboard**
   - View user explanations
   - Manage rate limits
   - Monitor API usage

5. **Batch Explanations**
   - Generate for multiple snippets

6. **Notifications**
   - Email when explanation is ready
   - In-app notifications

7. **Cost Tracking**
   - Per-user API cost tracking
   - Budget alerts

## Files Modified/Created

### New Files
- `app/Models/SnippetExplanation.php`
- `app/Services/AIExplanationService.php`
- `app/Services/ExplanationRateLimiter.php`
- `app/Jobs/GenerateSnippetExplanation.php`
- `app/Livewire/ExplainSnippet.php`
- `resources/views/livewire/explain-snippet.blade.php`
- `database/migrations/2026_02_26_create_snippet_explanations_table.php`
- `EXPLAIN_FEATURE_SETUP.md`
- `IMPLEMENTATION_GUIDE.md` (this file)

### Modified Files
- `app/Models/Snippet.php` - Added `explanation()` relationship
- `config/services.php` - Added OpenAI configuration
- `resources/views/livewire/snippets-index.blade.php` - Added component
- `resources/views/livewire/my-snippets.blade.php` - Added component
- `resources/views/livewire/saved-snippets-index.blade.php` - Added component

## Support & Questions

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Review this guide
3. Check OpenAI API documentation: https://platform.openai.com/docs/
4. Check Livewire documentation: https://livewire.laravel.com/
