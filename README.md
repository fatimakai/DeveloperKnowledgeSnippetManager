# Developer Knowledge Snippet Manager

A modern, full-featured code snippet management system built with Laravel 12 and Livewire 3, designed to help developers store, organize, search, and reuse knowledge efficiently with a fast, reactive user experience.

# Why This Project Exists

Developers constantly save useful code snippets across notes, chats, and scattered files, making them difficult to search, reuse, or share later.

This project solves that problem by providing a centralized, searchable, and tag-based snippet manager with real-time validation, syntax highlighting, strong authorization rules, and performance-focused architecture.

It was built to explore complex Livewire-driven UIs, clean backend architecture, and production-ready Laravel patterns.
# Features

## Core Features

- Create & Manage Snippets – Write, edit, and organize code snippets
- Code Highlighting – Syntax highlighting for 200+ languages using CodeMirror and Highlight.js
- Smart Tagging – Autocomplete-based tag system for easy categorization
- Advanced Filtering – Search by title, language, tags, and visibility
- Public / Private Control – Share snippets publicly or keep them private
- Export Options – Download snippets as JSON or PDF

## Technical Highlights

- Livewire 3 – Reactive UI without custom JavaScript
- Real-time Validation – Instant feedback with visual indicators
- Dark Mode Support – Fully implemented across all components
- Mobile Responsive – Optimized for all screen sizes
- Performance Optimized – Laravel Octane with Swoole
- Database Optimization – Indexing, eager loading, and N+1 query prevention

## Screenshots

## Screenshots

![Dashboard](assets/screenshots/allsnippets.png)
![Snippet Editor](assets/screenshots/my.png)


<img src="assets/screenshots/allsnippets.png" width="600" />
<img src="assets/screenshots/my.png" width="600" />

<p float="left">
  <img src="assets/screenshots/allsnippets.png" width="45%" />
  <img src="assets/screenshots/my.png" width="45%" />
</p>


# Project Structure
```
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Livewire/
│   ├── SnippetsIndex.php
│   ├── MySnippets.php
│   ├── CreateSnippet.php
│   ├── EditSnippet.php
│   ├── TagAutocomplete.php
│   └── DeleteSnippet.php
└── Models/
    ├── Snippet.php
    ├── Tag.php
    └── User.php

resources/views/
├── livewire/
│   ├── snippets-index.blade.php
│   ├── my-snippets.blade.php
│   ├── create-snippet.blade.php
│   ├── edit-snippet.blade.php
│   ├── tag-autocomplete.blade.php
│   └── delete-snippet.blade.php
└── snippets/
    ├── index.blade.php
    ├── my.blade.php
    ├── create.blade.php
    └── edit.blade.php
```

# Livewire Components Overview

1. **SnippetsIndex**
   - Lists all public snippets and the authenticated user's snippets
   - Search by title
   - Filtering by language, tags, and visibility
   - Pagination
   - Bulk export
   - Delete with confirmation

2. **MySnippets**
   - Displays only the authenticated user's snippets
   - Same filtering and pagination
   - Private snippet management

3. **CreateSnippet**
   - Snippet creation form featuring:
   - CodeMirror integration
   - Real-time validation with visual indicators
   - Tag autocomplete
   - Language selection

4. **EditSnippet**
   - Edit existing snippets
   - Pre-filled form
   - Authorization checks
   - Delete with confirmation

5. **TagAutocomplete**
   - Reusable component providing:
   - Real-time tag suggestions
   - Add/remove tag functionality

6. **DeleteSnippet**
   - Confirmation modal for deletion
   - Authorization verification
   - Dark mode compatible UI

# Authorization

All features require authentication. Authorization checks:
- Users can edit and delete only their own snippets
- Public snippets are visible to all authenticated users
- Private snippets are visible only to their owner
- Any authenticated user can create tags

# Database Schema

## snippets table
```sql
CREATE TABLE snippets (
  id bigint PRIMARY KEY,
  user_id bigint REFERENCES users(id),
  title varchar(255) NOT NULL,
  description text,
  code longtext NOT NULL,
  language varchar(50),
  is_public boolean DEFAULT false,
  created_at timestamp,
  updated_at timestamp
);
CREATE INDEX idx_user_id ON snippets(user_id);
CREATE INDEX idx_is_public ON snippets(is_public);
```

## tags table
```sql
CREATE TABLE tags (
  id bigint PRIMARY KEY,
  name varchar(255) UNIQUE NOT NULL,
  created_at timestamp,
  updated_at timestamp
);
```

## snippet_tag table
```sql
CREATE TABLE snippet_tag (
  snippet_id bigint REFERENCES snippets(id) ON DELETE CASCADE,
  tag_id bigint REFERENCES tags(id) ON DELETE CASCADE,
  PRIMARY KEY (snippet_id, tag_id)
);
```

# Technologies

- **Framework:** Laravel 12
- **Reactive UI:** Livewire 3.7.1
- **Code Editor:** CodeMirror 5.65.2
- **Syntax Highlighting:** Highlight.js 11.9.0
- **Export:** DOMPDF 3.1.1
- **Performance:** Laravel Octane + Swoole
- **Database:** MySQL 8.0+
- **Cache:** Redis
- **Styling:** Tailwind CSS
- **Frontend:** Alpine.js (via Livewire)

## Quick Start

### Prerequisites
- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js & npm

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd DeveloperKnowledgeSnippetManager
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database** (in `.env`)
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=snippet_manager
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

8. **Start Octane (optional, for production-like performance)**
   ```bash
   php artisan octane:start --workers=1
   ```

Visit `http://localhost:8000` and register a new account.


## Testing

### Manual Testing
Comprehensive testing documentation is available:
- **PHASE_6_TESTING_GUIDE.md** - 70+ test scenarios with step-by-step procedures
- **MASTER_TESTING_CHECKLIST.md** - 150+ items organized by feature
- **PHASE_6_TESTING_SUMMARY.md** - Test matrices and execution plan

### Automated Testing
Run automated test suite:
```bash
php test_livewire_e2e.php
```

Or via Tinker:
```bash
php artisan tinker < test_livewire_e2e.php
```


## Documentation

### Project Documentation
- **README.md** - This file (project overview)
- **LIVEWIRE_QUICK_REFERENCE.md** - Developer reference guide
- **SETUP_INSTRUCTIONS.md** - Detailed setup and configuration
- **DEPLOYMENT_GUIDE.md** - Production deployment steps

### Performance Documentation
- **PERFORMANCE_OPTIMIZATION_SUMMARY.md** - Optimization details
- **PERFORMANCE_QUICK_REFERENCE.md** - Performance tips
- **OCTANE_IMPLEMENTATION.md** - Octane configuration

## Configuration

### Laravel Octane (Optional)
For production-like performance testing:

```bash
php artisan octane:start --workers=1
```

View Octane config in `config/octane.php`

### Dark Mode
Dark mode is automatically detected from system preferences and can be toggled in the UI.


## Performance Metrics

Typical performance with Octane (1 worker):
- **List page load:** < 500ms
- **Create/Edit page load:** < 400ms
- **Real-time validation:** < 100ms response
- **Export small snippet:** < 1s
- **Export all snippets:** < 5s

See **PERFORMANCE_QUICK_REFERENCE.md** for optimization tips.

## Development Workflow

### Making Changes
1. Update Livewire component in `app/Livewire/`
2. Update corresponding view in `resources/views/livewire/`
3. Run tests: `php test_livewire_e2e.php`


### Adding New Features
1. Create new Livewire component: `php artisan make:livewire FeatureName`
2. Implement component logic
3. Create/update corresponding blade view
4. Add tests to test suite


## Deployment

For production deployment:
1. Follow **DEPLOYMENT_GUIDE.md**
2. Set environment to production: `APP_ENV=production`
3. Run migrations: `php artisan migrate --force`
4. Generate API docs if needed
5. Configure SSL certificates
6. Set up monitoring and logging


## Project Status

**Current Phase:** Phase 7 - Cleanup & Documentation   
**Livewire Migration:** Complete (Phases 1-6)  
**Testing Infrastructure:** Complete (Phase 6)  
**Status:** Production Ready

