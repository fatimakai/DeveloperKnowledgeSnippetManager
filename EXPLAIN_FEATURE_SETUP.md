# Code Explanation Feature Setup Guide

## Required Environment Variables

Add these to your `.env` file:

```env
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_MODEL=gpt-4
QUEUE_CONNECTION=database
```

## OpenAI API Key

1. Go to https://platform.openai.com/api-keys
2. Create a new API key
3. Add it to your `.env` file as `OPENAI_API_KEY`

## Installation Steps

### 1. Install OpenAI PHP Package

```bash
composer require openai-php/laravel
```

### 2. Run Migrations

```bash
php artisan migrate
```

This creates:
- `snippet_explanations` table
- Stores code explanations with relationships

### 3. Configure Queue

The feature uses Laravel's queue system. You can use:

**Database Queue (simplest, no external services needed):**
```bash
php artisan queue:table
php artisan migrate
```

Set in `.env`:
```env
QUEUE_CONNECTION=database
```

**Or use other queue drivers (Redis, SQS, etc.)**

### 4. Start Queue Worker

For development:
```bash
php artisan queue:work
```

For production (use supervisor or similar):
```bash
php artisan queue:work --tries=3 --timeout=120
```

## Features

### Generate Explanations
- Public snippets only
- One-click "Explain This Code" button
- Non-blocking (uses queued job)
- Real-time polling with Livewire (5 second intervals)
- Shows loading state while generating

### Rate Limiting
- Maximum 10 explanations per user per day
- Daily reset at midnight
- Shows remaining attempts to user
- Uses Laravel Cache for rate limiting

### UI/UX
- Shows error messages if generation fails
- Displays loading spinner while generating
- Renders explanations as Markdown
- "Regenerate" button to create new explanations
- Delete old explanation before generating new one

## Architecture

### Components
1. **ExplainSnippet** Livewire component
   - Handles UI and user interaction
   - Dispatches job and polls for results
   - Manages loading and error states

2. **GenerateSnippetExplanation** Job
   - Queued job for async processing
   - Calls service to generate explanation
   - Stores result in database

3. **AIExplanationService** Service Class
   - Handles all OpenAI API communication
   - Stores explanation in database
   - Manages explanation lifecycle

4. **ExplanationRateLimiter** Service Class
   - Tracks daily explanation count per user
   - Prevents abuse
   - Provides remaining count info

### Database
- `snippet_explanations` table
  - Links snippets to their explanations
  - One-to-one relationship with snippets
  - Stores user_id for audit trail

### Models
- `Snippet::explanation()` relationship
- `SnippetExplanation` model with relationships

## Testing

### Manual Testing
1. Log in as a user
2. Go to any public snippet
3. Click "Explain This Code"
4. Watch loading state
5. Explanation appears in ~10-30 seconds
6. Click "Regenerate" to create new explanation

### View Queue Status
```bash
php artisan queue:work
# In another terminal
php artisan queue:monitor
```

## Troubleshooting

### Explanations not generating
1. Check queue worker is running: `php artisan queue:work`
2. Check OpenAI API key is valid
3. Check API has available credits
4. Check logs: `tail -f storage/logs/laravel.log`

### Rate limit not working
- Clear cache: `php artisan cache:clear`
- Reset for user: Use `ExplanationRateLimiter::reset($userId)`

### Timeout errors
- Increase timeout in `GenerateSnippetExplanation` job
- Increase API timeout in `AIExplanationService`

## Costs

OpenAI API usage incurs charges:
- GPT-4: ~$0.03 per 1K input tokens, $0.06 per 1K output tokens
- Estimated: ~$0.10-0.20 per explanation

Monitor usage at: https://platform.openai.com/account/billing/overview

## Future Enhancements

- [ ] Cache explanations across identical code
- [ ] Support multiple AI models
- [ ] Admin dashboard for rate limit management
- [ ] Email notifications when explanation is ready
- [ ] Batch explanation generation
- [ ] Different explanation styles (beginner, advanced, etc.)
