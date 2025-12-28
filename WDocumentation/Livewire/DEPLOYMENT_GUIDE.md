# Deployment Guide

Complete guide to deploy the Developer Knowledge Snippet Manager to production.

---

## Pre-Deployment Checklist

- [ ] All tests passing: `php test_livewire_e2e.php`
- [ ] Manual testing completed (see PHASE_6_TESTING_GUIDE.md)
- [ ] Code reviewed and merged to main branch
- [ ] Environment variables configured for production
- [ ] Database backup created
- [ ] SSL certificate obtained and configured
- [ ] Domain name configured
- [ ] Monitoring/logging setup complete
- [ ] Backups automated

---

## Deployment Options

### Option 1: Shared Hosting (cPanel)

#### Requirements
- PHP 8.2+ with required extensions
- MySQL 8.0+ or compatible database
- Composer support
- SSH access

#### Steps

1. **Upload Files via SFTP**
   ```bash
   # Using FTP client like FileZilla:
   # Connect to: sftp://your-domain.com
   # Upload contents of project to public_html or subdirectory
   ```

2. **Setup SSH Connection**
   ```bash
   # SSH into server
   ssh user@your-domain.com
   ```

3. **Install Dependencies**
   ```bash
   cd /home/user/public_html
   composer install --no-dev --optimize-autoloader
   npm install
   npm run build
   ```

4. **Configure Environment**
   ```bash
   cp .env.example .env
   # Edit .env with production values
   nano .env
   ```

5. **Generate Key**
   ```bash
   php artisan key:generate
   ```

6. **Run Migrations**
   ```bash
   php artisan migrate --force
   ```

7. **Optimize**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

8. **Set Permissions**
   ```bash
   chmod 775 storage bootstrap/cache
   chmod 644 storage/logs/*.log
   ```

9. **Configure cPanel**
   - Point domain to public_html folder
   - Install SSL certificate (Let's Encrypt recommended)
   - Configure cron job for queue worker (if needed)

---

### Option 2: VPS/Cloud Hosting (DigitalOcean, AWS, etc.)

#### Prerequisites
- Fresh Ubuntu 20.04+ or similar OS
- Root or sudo access
- Domain configured with DNS

#### Step 1: Server Setup

```bash
# Update system
sudo apt-get update
sudo apt-get upgrade -y

# Install PHP 8.2 and extensions
sudo apt-get install -y php8.2-cli php8.2-fpm php8.2-mysql php8.2-redis php8.2-mbstring php8.2-xml php8.2-openssl php8.2-curl php8.2-zip

# Install MySQL
sudo apt-get install -y mysql-server

# Install Redis
sudo apt-get install -y redis-server

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Install Composer
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

# Install Nginx
sudo apt-get install -y nginx
```

#### Step 2: Configure Nginx

```bash
# Create Nginx config
sudo nano /etc/nginx/sites-available/snippet-manager
```

Add this configuration:

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/snippet-manager/public;
    index index.php index.html;

    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /var/www/snippet-manager/public;
    index index.php index.html;

    # SSL Certificates
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Gzip compression
    gzip on;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss;
    gzip_vary on;

    # Security headers
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Static files
    location ~* ^/build/.*\.(?:css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    location ~* ^/images/.*\.(?:jpg|jpeg|png|gif|ico|svg)$ {
        expires 30d;
        add_header Cache-Control "public";
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to dotfiles
    location ~ /\. {
        deny all;
    }

    # Rewrite rules for Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/snippet-manager /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test Nginx config
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

#### Step 3: Setup SSL Certificate

```bash
# Install Certbot
sudo apt-get install -y certbot python3-certbot-nginx

# Get SSL certificate (Let's Encrypt is free)
sudo certbot certonly --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal is enabled by default
```

#### Step 4: Setup Database

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database
mysql -u root -p
CREATE DATABASE snippet_manager;
CREATE USER 'snippet_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON snippet_manager.* TO 'snippet_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Step 5: Deploy Application

```bash
# Create app directory
sudo mkdir -p /var/www/snippet-manager
sudo chown -R $(whoami):www-data /var/www/snippet-manager

# Clone repository
cd /var/www/snippet-manager
git clone https://github.com/yourusername/DeveloperKnowledgeSnippetManager.git .

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Setup environment
cp .env.example .env
# Edit .env with production values
nano .env

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Clear cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Step 6: Setup PHP-FPM

```bash
# Edit PHP-FPM config
sudo nano /etc/php/8.2/fpm/pool.d/www.conf

# Ensure these settings:
# user = www-data
# group = www-data
# listen = /var/run/php/php8.2-fpm.sock

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

#### Step 7: Setup Queue Worker (Optional)

If using job queues, setup Supervisor:

```bash
# Install Supervisor
sudo apt-get install -y supervisor

# Create config
sudo nano /etc/supervisor/conf.d/snippet-manager.conf
```

Add:

```ini
[program:snippet-manager-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/snippet-manager/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/snippet-manager/storage/logs/queue.log
```

```bash
# Start Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

#### Step 8: Setup Monitoring & Logs

```bash
# View error logs
tail -f /var/www/snippet-manager/storage/logs/laravel.log

# View Nginx logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# Setup log rotation
sudo nano /etc/logrotate.d/snippet-manager
```

Add:

```
/var/www/snippet-manager/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
}
```

---

### Option 3: Docker Deployment

#### Dockerfile

```dockerfile
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    mysql-client \
    redis-tools

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application
COPY . /app

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node dependencies and build assets
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && apt-get install -y nodejs
RUN npm install && npm run build

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Expose port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
```

#### Docker Compose

```yaml
version: '3.8'

services:
  app:
    build: .
    container_name: snippet-manager-app
    restart: unless-stopped
    working_dir: /app
    environment:
      - DB_HOST=mysql
      - DB_PORT=3306
      - DB_DATABASE=snippet_manager
      - DB_USERNAME=snippet_user
      - DB_PASSWORD=secure_password
      - CACHE_DRIVER=redis
      - REDIS_HOST=redis
    depends_on:
      - mysql
      - redis
    networks:
      - snippet-network
    volumes:
      - .:/app
      - ./storage/logs:/app/storage/logs

  nginx:
    image: nginx:alpine
    container_name: snippet-manager-nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - .:/app
      - ./nginx.conf:/etc/nginx/nginx.conf:ro
      - ./ssl:/etc/nginx/ssl:ro
    depends_on:
      - app
    networks:
      - snippet-network

  mysql:
    image: mysql:8.0
    container_name: snippet-manager-mysql
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: snippet_manager
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_PASSWORD: secure_password
      MYSQL_USER: snippet_user
    volumes:
      - snippet-mysql:/var/lib/mysql
    networks:
      - snippet-network

  redis:
    image: redis:7-alpine
    container_name: snippet-manager-redis
    restart: unless-stopped
    networks:
      - snippet-network

volumes:
  snippet-mysql:

networks:
  snippet-network:
    driver: bridge
```

#### Deploy with Docker

```bash
# Build and start containers
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate --force

# Cache config
docker-compose exec app php artisan config:cache

# View logs
docker-compose logs -f app
```

---

## Post-Deployment Steps

### 1. Configure Environment Variables

Edit `.env` with production values:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_HOST=localhost
DB_DATABASE=snippet_manager
DB_USERNAME=snippet_user
DB_PASSWORD=your_secure_password

CACHE_DRIVER=redis
SESSION_DRIVER=cookie
QUEUE_CONNECTION=redis

MAIL_MAILER=sendmail
MAIL_FROM_ADDRESS=noreply@your-domain.com

# Livewire
LIVEWIRE_ASSET_URL=/build
```

### 2. Configure Caching

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Setup Database Backups

```bash
# Daily backup via cron
0 2 * * * mysqldump -u snippet_user -p'password' snippet_manager | gzip > /backups/snippet-manager-$(date +\%Y\%m\%d).sql.gz
```

### 4. Configure Monitoring

Setup monitoring for:
- Server uptime
- Disk space usage
- Database size
- Application errors
- Performance metrics

Tools: Datadog, New Relic, or open-source alternatives

### 5. Configure CDN (Optional)

For static assets:
```bash
# Upload build folder to CDN
# Update .env: ASSET_URL=https://cdn.your-domain.com
```

### 6. Setup Automated Backups

```bash
# Backup application code
0 3 * * * tar -czf /backups/app-$(date +\%Y\%m\%d).tar.gz /var/www/snippet-manager

# Backup database
0 2 * * * mysqldump -u snippet_user -p'password' snippet_manager | gzip > /backups/db-$(date +\%Y\%m\%d).sql.gz
```

### 7. Run Tests

```bash
php test_livewire_e2e.php
```

All tests should pass ✓

### 8. Verify Functionality

1. Create test account
2. Create, edit, delete snippet
3. Test all filters
4. Test export functionality
5. Verify dark mode
6. Test on mobile device

---

## Performance Optimization

### 1. Enable Octane (Optional)

```bash
composer require laravel/octane
php artisan octane:install

# Start Octane
php artisan octane:start --host=127.0.0.1 --port=8000
```

### 2. Configure Nginx Caching

```nginx
# Cache static assets 1 year
location ~* ^/build/.*\.(?:css|js)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

### 3. Enable Redis

Ensure Redis is configured in `.env`:
```
CACHE_DRIVER=redis
SESSION_DRIVER=cookie
QUEUE_CONNECTION=redis
```

### 4. Minify Assets

Assets are minified with `npm run build`

### 5. Configure Database Indexing

```sql
-- Ensure indexes exist
CREATE INDEX idx_user_id ON snippets(user_id);
CREATE INDEX idx_is_public ON snippets(is_public);
CREATE INDEX idx_name ON tags(name);
```

---

## Security Hardening

### 1. SSL/TLS

```bash
# Automatic via Let's Encrypt
sudo certbot renew --dry-run
```

### 2. Firewall

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https
sudo ufw enable
```

### 3. SSH Key Authentication

```bash
# Disable password auth
sudo nano /etc/ssh/sshd_config
# Set: PasswordAuthentication no
sudo systemctl restart ssh
```

### 4. Keep Dependencies Updated

```bash
# Check for security updates
composer audit
npm audit

# Update packages
composer update
npm update
```

### 5. Environment Variables

Never commit `.env` file:
```bash
# .gitignore should contain
.env
.env.*.backup
```

---

## Troubleshooting Deployment

### Issue: "500 Internal Server Error"

**Solution:**
1. Check error logs: `tail -f storage/logs/laravel.log`
2. Verify file permissions: `chmod -R 775 storage bootstrap/cache`
3. Check environment variables: `php artisan config:show`
4. Verify database connection: `php artisan tinker` → `DB::connection()->getPdo();`

### Issue: "Composer memory limit"

**Solution:**
```bash
php -d memory_limit=-1 /usr/local/bin/composer install
```

### Issue: "Database migration fails"

**Solution:**
```bash
php artisan migrate --force
# or
php artisan migrate:fresh --force
```

### Issue: "Asset 404 errors"

**Solution:**
```bash
php artisan storage:link
npm run build
php artisan config:cache
```

### Issue: "Redis connection refused"

**Solution:**
```bash
# Check Redis status
sudo systemctl status redis-server

# Start Redis
sudo systemctl start redis-server

# Test connection
redis-cli ping  # Should return PONG
```

---

## Rollback Procedure

If deployment fails:

```bash
# Restore previous version
git revert <commit-hash>
git push origin main

# Restore database
mysql snippet_manager < /backups/db-backup.sql

# Clear cache
php artisan cache:clear
php artisan config:clear
```

---

## Monitoring & Maintenance

### Daily Tasks
- [ ] Check error logs
- [ ] Verify disk space
- [ ] Check database size

### Weekly Tasks
- [ ] Review performance metrics
- [ ] Check for security updates
- [ ] Backup verification

### Monthly Tasks
- [ ] Full system audit
- [ ] Performance optimization review
- [ ] Dependency updates
- [ ] Documentation updates

---

## Support & Help

- **Documentation:** See `LIVEWIRE_QUICK_REFERENCE.md`
- **Testing:** See `PHASE_6_TESTING_GUIDE.md`
- **Setup:** See `SETUP_INSTRUCTIONS.md`
- **Issues:** Check GitHub issues

---

**Last Updated:** December 6, 2025  
**Status:** Production Ready
