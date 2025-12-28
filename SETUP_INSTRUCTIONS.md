# Setup Instructions

Complete step-by-step guide to set up the Developer Knowledge Snippet Manager locally.

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.2+** ([Download](https://www.php.net/downloads))
  - Required extensions: OpenSSL, PDO, Mbstring, Tokenizer, XML
- **Composer** ([Download](https://getcomposer.org/download/))
- **MySQL 8.0+** ([Download](https://dev.mysql.com/downloads/mysql/))
- **Node.js 16+** ([Download](https://nodejs.org/))
- **npm** (comes with Node.js)
- **Git** ([Download](https://git-scm.com/))

### Verify Installations

```bash
# Check PHP version
php --version

# Check Composer version
composer --version

# Check MySQL version
mysql --version

# Check Node.js version
node --version

# Check npm version
npm --version
```

All should show version 8.2+ (PHP), 2.x (Composer), 8.0+ (MySQL), 16+ (Node.js)

---

## Step 1: Clone the Repository

```bash
# Clone the repository
git clone https://github.com/yourusername/DeveloperKnowledgeSnippetManager.git

# Navigate to the project directory
cd DeveloperKnowledgeSnippetManager
```

---

## Step 2: Install PHP Dependencies

```bash
composer install
```

This will install all required Laravel packages and dependencies.

**Expected output:** "Installing dependencies from lock file" followed by a list of installed packages.

---

## Step 3: Install Node.js Dependencies

```bash
npm install
```

This installs frontend dependencies including Tailwind CSS, Vite, and other assets.

**Expected output:** Multiple packages installed successfully.

---

## Step 4: Environment Configuration

### Create .env File

```bash
# Copy the example environment file
cp .env.example .env
```

### Generate Application Key

```bash
php artisan key:generate
```

**Expected output:** "Application key set successfully."

### Edit .env File

Open `.env` in your editor and configure:

```env
# Application
APP_NAME="Snippet Manager"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=snippet_manager
DB_USERNAME=root
DB_PASSWORD=

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=cookie
QUEUE_CONNECTION=redis

# Mail (optional, for email features)
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@snippetmanager.test

# Livewire
LIVEWIRE_ASSET_URL=/build
```

**Important Fields:**
- `DB_DATABASE`: Change to `snippet_manager` (or your preferred name)
- `DB_USERNAME`: Usually `root` on local machine
- `DB_PASSWORD`: Leave blank if no password set on root user
- `APP_URL`: Keep as `http://localhost:8000` for local development

---

## Step 5: Create Database

### Option A: Using MySQL Command Line

```bash
# Connect to MySQL
mysql -u root -p

# Create database (at MySQL prompt)
CREATE DATABASE snippet_manager;
EXIT;
```

### Option B: Using phpMyAdmin

1. Open phpMyAdmin (usually at http://localhost/phpmyadmin)
2. Click "New"
3. Enter database name: `snippet_manager`
4. Click "Create"

---

## Step 6: Run Database Migrations

```bash
php artisan migrate
```

**Expected output:** Multiple tables created (users, snippets, tags, etc.)

**If you get errors:**
- Ensure MySQL is running
- Check database credentials in `.env`
- Try `php artisan migrate --force` if needed

---

## Step 7: Build Frontend Assets

```bash
# For development (includes hot reload)
npm run dev

# OR for production-like build
npm run build
```

**Expected output:** Compilation completed successfully, assets built in `/public/build`

Keep `npm run dev` running in a separate terminal for hot reload during development.

---

## Step 8: Start the Development Server

Open a new terminal and run:

```bash
php artisan serve
```

**Expected output:**
```
Server running on [http://127.0.0.1:8000]
```

Visit `http://localhost:8000` in your browser.

---

## Step 9: Create User Account

1. Click "Register" or go to `http://localhost:8000/register`
2. Fill in email and password
3. Click "Register"
4. You'll be logged in automatically

---

## Step 10: Verify Installation

### Check Dashboard Access

1. Go to `http://localhost:8000/dashboard`
2. You should see a welcome message

### Test Snippet Creation

1. Navigate to "Snippets" or go to `http://localhost:8000/snippets`
2. Click "+ New Snippet"
3. Fill in the form:
   - **Title:** "Hello World"
   - **Description:** "My first snippet"
   - **Language:** Select "JavaScript" or "Python"
   - **Code:** Enter some sample code
   - Click "Create"
4. Your snippet should appear in the list

### Test Filtering

1. Create another snippet with different language
2. Try filtering by language
3. Search by title
4. Verify filtering works

---

## Optional: Setup Redis (for Caching)

Redis improves performance for caching and sessions.

### Install Redis

#### Windows
1. Download Redis from [Microsoft Windows Port](https://github.com/microsoftarchive/redis/releases)
2. Run installer
3. Verify: `redis-cli.exe`

#### macOS
```bash
brew install redis
brew services start redis
```

#### Linux (Ubuntu)
```bash
sudo apt-get install redis-server
sudo systemctl start redis-server
```

### Verify Redis Connection

```bash
redis-cli ping
```

**Expected output:** `PONG`

---

## Optional: Setup Laravel Octane (Performance)

Octane provides superfast performance via Swoole:

### Install Octane

```bash
composer require laravel/octane
php artisan octane:install
```

### Start Octane Server

```bash
php artisan octane:start --workers=1
```

Visit `http://localhost:8000` and enjoy 10x performance improvement!

### Stop Octane

```bash
php artisan octane:stop
```

---

## Step 11: Run Tests

### Run Automated Tests

```bash
php test_livewire_e2e.php
```

**Expected output:** All tests passed ✓

### Run Manual Tests

1. Read `PHASE_6_TESTING_GUIDE.md`
2. Follow test scenarios
3. Mark tests as completed

---

## Troubleshooting

### Issue: "Connection refused" when connecting to database

**Solution:**
1. Ensure MySQL is running
   - Windows: Check Services for "MySQL80" (or your version)
   - macOS: `brew services list` should show redis and mysql running
   - Linux: `sudo systemctl status mysql`
2. Verify credentials in `.env`
3. Try `php artisan migrate --seed`

### Issue: "Call to undefined function openssl_..."

**Solution:**
1. Uncomment `extension=openssl` in `php.ini`
2. Restart PHP
3. Verify: `php -m | grep openssl`

### Issue: "No application encryption key has been specified"

**Solution:**
```bash
php artisan key:generate
```

### Issue: Node modules not found

**Solution:**
```bash
rm -rf node_modules package-lock.json
npm install
npm run build
```

### Issue: Livewire components not loading

**Solution:**
```bash
php artisan livewire:publish --assets
php artisan view:clear
php artisan cache:clear
```

### Issue: Port 8000 already in use

**Solution:**
```bash
# Use different port
php artisan serve --port=8001

# Or find and kill process using port 8000
# Windows: netstat -ano | findstr :8000
# macOS/Linux: lsof -i :8000
```

### Issue: "SQLSTATE[HY000]: General error"

**Solution:**
```bash
php artisan migrate:fresh
# or
php artisan migrate --force
```

---

## Development Workflow

### Daily Startup

```bash
# Terminal 1: Frontend build with hot reload
npm run dev

# Terminal 2: Backend server
php artisan serve

# Terminal 3 (optional): Queue worker (if using queues)
php artisan queue:work
```

### Making Changes

1. **Update Livewire component** → `app/Livewire/ComponentName.php`
2. **Update blade view** → `resources/views/livewire/component-name.blade.php`
3. **Run tests** → `php test_livewire_e2e.php`
4. **Test manually** in browser
5. **Commit changes** → `git commit -m "Description"`

### Adding New Features

1. Create new Livewire component:
   ```bash
   php artisan make:livewire NewFeatureName
   ```
2. Implement component logic in `app/Livewire/NewFeatureName.php`
3. Update blade view in `resources/views/livewire/new-feature-name.blade.php`
4. Add tests to `test_livewire_e2e.php`
5. Test thoroughly
6. Document in README and LIVEWIRE_QUICK_REFERENCE.md

---

## Database Seeding (Optional)

Create sample data for testing:

```bash
# Create seeder
php artisan make:seeder SnippetSeeder

# Run seeder
php artisan db:seed

# Or reset and seed
php artisan migrate:fresh --seed
```

---

## Environment Variables Reference

| Variable | Default | Purpose |
|----------|---------|---------|
| APP_ENV | local | Application environment |
| APP_DEBUG | true | Debug mode (disable in production) |
| DB_CONNECTION | mysql | Database type |
| DB_HOST | 127.0.0.1 | Database host |
| DB_PORT | 3306 | Database port |
| DB_DATABASE | snippet_manager | Database name |
| DB_USERNAME | root | Database user |
| DB_PASSWORD | (empty) | Database password |
| CACHE_DRIVER | redis | Cache driver (redis or file) |
| SESSION_DRIVER | cookie | Session driver |
| QUEUE_CONNECTION | redis | Queue driver |
| MAIL_MAILER | log | Mail driver |

---

## Performance Tips

1. **Use Octane** for 10x performance improvement
2. **Enable Redis** for better caching
3. **Build assets with `npm run build`** in production
4. **Run `php artisan config:cache`** for faster boot
5. **Use Supervisor** to manage queue workers

---

## Next Steps

1. ✅ Complete setup
2. 📝 Read `LIVEWIRE_QUICK_REFERENCE.md` for project overview
3. 🧪 Run `php test_livewire_e2e.php` to verify everything works
4. 📚 Review `PHASE_6_TESTING_GUIDE.md` for testing procedures
5. 🚀 Start creating and managing snippets!

---

## Getting Help

- **Documentation:** See `LIVEWIRE_QUICK_REFERENCE.md`
- **Testing:** See `PHASE_6_TESTING_GUIDE.md`
- **Troubleshooting:** See section above
- **Deployment:** See `DEPLOYMENT_GUIDE.md`
- **Issues:** Check GitHub issues or contact support

---

**Last Updated:** December 6, 2025  
**Status:** Production Ready
