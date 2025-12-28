# Developer Knowledge Snippet Manager

A modern, full-featured snippet management system built with Laravel 12 and Livewire 3, featuring real-time validation, code highlighting, and advanced filtering capabilities.

## 🚀 Features

### Core Features
- **Create & Manage Snippets** - Write, edit, and organize code snippets
- **Code Highlighting** - Syntax highlighting for 200+ languages using CodeMirror and Highlight.js
- **Smart Tagging** - Autocomplete tag system for easy categorization
- **Advanced Filtering** - Search, filter by language, tags, and visibility
- **Public/Private Control** - Share snippets publicly or keep them private
- **Export Options** - Download snippets as JSON or PDF

### Technical Highlights
- **Livewire 3.7.1** - Reactive UI components without JavaScript
- **Real-time Validation** - Instant feedback with character counters and visual indicators
- **Dark Mode Support** - Full dark mode implementation across all components
- **Mobile Responsive** - Optimized for all screen sizes
- **Performance Optimized** - Laravel Octane with Swoole integration
- **Database Optimization** - Eager loading, proper indexing, N+1 query prevention

## 📋 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── SnippetController.php    - Snippet CRUD logic
│   │   ├── TagController.php        - Tag autocomplete
│   │   └── ExportController.php     - JSON/PDF exports
│   ├── Middleware/
│   └── Requests/
├── Livewire/
│   ├── SnippetsIndex.php            - List all snippets (250+ lines)
│   ├── MySnippets.php               - User's own snippets (250+ lines)
│   ├── CreateSnippet.php            - Create form with validation (120 lines)
│   ├── EditSnippet.php              - Edit form with delete (150 lines)
│   ├── TagAutocomplete.php          - Reusable tag input (63 lines)
│   └── DeleteSnippet.php            - Delete confirmation modal (45 lines)
└── Models/
    ├── Snippet.php
    ├── Tag.php
    └── User.php

resources/views/
├── livewire/                         - Livewire component views
│   ├── snippets-index.blade.php
│   ├── my-snippets.blade.php
│   ├── create-snippet.blade.php      - Real-time validation UI
│   ├── edit-snippet.blade.php        - Real-time validation UI
│   ├── tag-autocomplete.blade.php    - Autocomplete dropdown
│   └── delete-snippet.blade.php      - Modal confirmation
└── snippets/                         - Wrapper views
    ├── index.blade.php               - Loads SnippetsIndex component
    ├── my.blade.php                  - Loads MySnippets component
    ├── create.blade.php              - Loads CreateSnippet component
    └── edit.blade.php                - Loads EditSnippet component
```

## 🔄 Livewire Components (Phase 1-5)

### 1. **SnippetsIndex** (250+ lines)
Lists all public snippets + user's own snippets with real-time filtering
- Search by title
- Filter by language, tags, visibility
- Pagination (15 per page)
- Export selected snippets
- Delete with confirmation

### 2. **MySnippets** (250+ lines)
Lists only the logged-in user's snippets
- Same filtering as SnippetsIndex
- Quick access to user's collection
- Private snippet management

### 3. **CreateSnippet** (120 lines)
Form to create new snippets with real-time validation
- CodeMirror integration for code input
- Character counters (Title: 255, Description: 1000)
- Tag autocomplete
- Visual validation indicators (green ✓ / red ✕)
- Language selection

### 4. **EditSnippet** (150 lines)
Form to edit existing snippets
- Pre-populated form with existing data
- Same validation as CreateSnippet
- Delete button with confirmation
- Authorization check (owner only)

### 5. **TagAutocomplete** (63 lines)
Reusable tag input component with autocomplete
- Real-time tag suggestions
- Add/remove tags
- Extracted from CRUD components to avoid duplication

### 6. **DeleteSnippet** (45 lines)
Confirmation modal for snippet deletion
- Authorization verification
- Undo-friendly confirmation
- Modal styling with dark mode support

## 🔐 Authorization

All features require authentication. Authorization checks:
- Users can only edit their own snippets
- Users can only delete their own snippets
- Public snippets visible to all authenticated users
- Private snippets only visible to owner
- Tag creation by any authenticated user

## 📊 Database Schema

### snippets table
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

### tags table
```sql
CREATE TABLE tags (
  id bigint PRIMARY KEY,
  name varchar(255) UNIQUE NOT NULL,
  created_at timestamp,
  updated_at timestamp
);
```

### snippet_tag table
```sql
CREATE TABLE snippet_tag (
  snippet_id bigint REFERENCES snippets(id) ON DELETE CASCADE,
  tag_id bigint REFERENCES tags(id) ON DELETE CASCADE,
  PRIMARY KEY (snippet_id, tag_id)
);
```

## 🛠️ Technologies

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

## 🚀 Quick Start

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

## 📝 Routes

### Authenticated Routes
- `GET /snippets` - List all snippets (SnippetsIndex component)
- `GET /snippets/my` - List user's snippets (MySnippets component)
- `GET /snippets/create` - Create snippet form (CreateSnippet component)
- `POST /snippets` - Store new snippet
- `GET /snippets/{id}/edit` - Edit snippet form (EditSnippet component)
- `PATCH /snippets/{id}` - Update snippet
- `DELETE /snippets/{id}` - Delete snippet

### Export Routes
- `GET /snippets/export-all-json` - Export all snippets as JSON
- `GET /snippets/export-all-pdf` - Export all snippets as PDF
- `POST /snippets/export-bulk-json` - Export selected snippets as JSON
- `POST /snippets/export-bulk-pdf` - Export selected snippets as PDF
- `GET /snippets/{id}/export-json` - Export single snippet as JSON
- `GET /snippets/{id}/export-pdf` - Export single snippet as PDF

### Tag Routes
- `GET /tags/autocomplete` - Tag autocomplete endpoint

## 🧪 Testing

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

### Quick Test
Quick 30-minute smoke test:
1. Create a new snippet
2. Edit the snippet
3. Add tags
4. Filter by language and tag
5. Delete the snippet
6. View in dark mode

## 📚 Documentation

### Project Documentation
- **README.md** - This file (project overview)
- **LIVEWIRE_QUICK_REFERENCE.md** - Developer reference guide
- **SETUP_INSTRUCTIONS.md** - Detailed setup and configuration
- **DEPLOYMENT_GUIDE.md** - Production deployment steps

### Testing Documentation (Phase 6)
- **PHASE_6_TESTING_GUIDE.md** - Comprehensive manual testing guide (450+ lines)
- **PHASE_6_TESTING_SUMMARY.md** - Testing coordination (350+ lines)
- **MASTER_TESTING_CHECKLIST.md** - Printable 150+ item checklist
- **test_livewire_e2e.php** - Automated test suite (200+ lines)
- **PHASE_6_COMPLETION_SUMMARY.md** - Phase overview (500+ lines)
- **PHASE_6_TESTING_PACKAGE_INDEX.md** - Testing package index (250+ lines)
- **PHASE_6_FINAL_STATUS.md** - Completion status report (200+ lines)

### Performance Documentation
- **PERFORMANCE_OPTIMIZATION_SUMMARY.md** - Optimization details
- **PERFORMANCE_QUICK_REFERENCE.md** - Performance tips
- **OCTANE_IMPLEMENTATION.md** - Octane configuration

## 🔧 Configuration

### Laravel Octane (Optional)
For production-like performance testing:

```bash
php artisan octane:start --workers=1
```

View Octane config in `config/octane.php`

### Dark Mode
Dark mode is automatically detected from system preferences and can be toggled in the UI.

## 🐛 Troubleshooting

### Common Issues

**Issue:** "Target class [SnippetController] does not exist"
- **Solution:** Ensure `SnippetController` exists in `app/Http/Controllers/`
- **Fix:** Run `php artisan migrate` and check routes

**Issue:** Livewire component not rendering
- **Solution:** Verify Livewire is installed and namespace is correct
- **Fix:** Run `composer update livewire/livewire`

**Issue:** CORS or JavaScript errors
- **Solution:** Clear browser cache, rebuild assets
- **Fix:** Run `npm run build && php artisan view:clear`

**Issue:** Database connection fails
- **Solution:** Check `.env` database credentials
- **Fix:** Verify MySQL is running and credentials are correct

For more troubleshooting, see **LIVEWIRE_QUICK_REFERENCE.md**

## 📈 Performance Metrics

Typical performance with Octane (1 worker):
- **List page load:** < 500ms
- **Create/Edit page load:** < 400ms
- **Real-time validation:** < 100ms response
- **Export small snippet:** < 1s
- **Export all snippets:** < 5s

See **PERFORMANCE_QUICK_REFERENCE.md** for optimization tips.

## 🔄 Development Workflow

### Making Changes
1. Update Livewire component in `app/Livewire/`
2. Update corresponding view in `resources/views/livewire/`
3. Run tests: `php test_livewire_e2e.php`


### Adding New Features
1. Create new Livewire component: `php artisan make:livewire FeatureName`
2. Implement component logic
3. Create/update corresponding blade view
4. Add tests to test suite


## 📦 Deployment

For production deployment:
1. Follow **DEPLOYMENT_GUIDE.md**
2. Set environment to production: `APP_ENV=production`
3. Run migrations: `php artisan migrate --force`
4. Generate API docs if needed
5. Configure SSL certificates
6. Set up monitoring and logging



---

## 🎉 Project Status

**Current Phase:** Phase 7 - Cleanup & Documentation ✅  
**Livewire Migration:** Complete (Phases 1-6)  
**Testing Infrastructure:** Complete (Phase 6)  
**Status:** Production Ready

