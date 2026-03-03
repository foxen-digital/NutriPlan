# Deployment Guide - NutriPlan

This guide covers deploying the NutriPlan application to production.

## Infrastructure Requirements

### Minimum Requirements

- **PHP:** 8.2 or higher
- **Memory:** 512MB RAM (1GB recommended)
- **Disk:** 1GB free space
- **Database:** SQLite, MySQL 5.7+, or PostgreSQL 10+

### External Dependencies

- **OpenAI API** (or compatible) for recipe import
- **Barcode API** for product lookup
- **Pusher** (optional) for real-time broadcasting

---

## Environment Configuration

### Production .env Settings

```env
APP_NAME="NutriPlan"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database (MySQL example)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nutriplan
DB_USERNAME=nutriplan_user
DB_PASSWORD=secure_password

# External Services
OPENAI_API_KEY=your_openai_key
OPENAI_API_BASE=https://api.openai.com/v1
BARCODE_API_KEY=your_barcode_api_key

# Broadcasting (optional)
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_key
PUSHER_APP_SECRET=your_pusher_secret
PUSHER_HOST=api.pusherapp.com
PUSHER_PORT=443
PUSHER_SCHEME=https

# Queue
QUEUE_CONNECTION=database
```

---

## Deployment Methods

### 1. Traditional VPS Deployment

#### Server Setup (Ubuntu/Debian)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-sqlite3 php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath unzip git -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js 18+
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Clone repository
cd /var/www
git clone https://github.com/yourusername/nutriplan.git
cd nutriplan

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### Configure Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/nutriplan/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

### 2. Docker Deployment (Laravel Sail)

The project includes `docker-compose.yml` for local development.

```bash
# Build containers
./vendor/bin/sail up -d

# Run migrations
./vendor/bin/sail artisan migrate

# Seed database
./vendor/bin/sail artisan db:seed
```

**Note:** Sail is intended for development. For production, consider using a dedicated Docker setup.

---

### 3. Laravel Forge

Deploy via Laravel Forge:

1. Connect repository to Forge
2. Configure server (Provisioning)
3. Set environment variables
4. Enable Quick Deploy or deploy manually
5. Configure SSL certificate (Let's Encrypt)
6. Set up queue worker
7. Configure scheduler (cron)

---

### 4. Platform.sh / Vapor

For Laravel Vapor (AWS Lambda):

1. Install Vapor CLI
2. Initialize project
3. Configure `vapor.yml`
4. Deploy: `vapor deploy`

---

## Build Process

### Production Build

```bash
# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci

# Build frontend
npm run build

# Optimize Laravel
php artisan optimize
php artisan view:cache
php artisan route:cache
php artisan config:cache
```

### Assets

- **Compiled assets:** `public/build/` (Vite)
- **Uploaded files:** `storage/app/public/` (use symbolic link)

---

## Database Setup

### MySQL

```sql
CREATE DATABASE nutriplan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nutriplan_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON nutriplan.* TO 'nutriplan_user'@'localhost';
FLUSH PRIVILEGES;
```

### PostgreSQL

```sql
CREATE DATABASE nutriplan;
CREATE USER nutriplan_user WITH PASSWORD 'secure_password';
GRANT ALL PRIVILEGES ON DATABASE nutriplan TO nutriplan_user;
```

---

## Queue Workers

### Supervisor Configuration

```ini
[program:nutriplan-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/nutriplan/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/nutriplan/storage/logs/worker.log
stopwaitsecs=3600
```

---

## SSL/TLS Configuration

### Let's Encrypt (Certbot)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

---

## Monitoring

### Application Monitoring

1. **Laravel Telescope** - Debug and monitoring (dev only)
2. **Laravel Pulse** - Performance monitoring (production ready)
3. **Sentry** - Error tracking
4. **Laravel Horizon** - Queue monitoring (Redis required)

### Log Monitoring

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Worker logs
tail -f storage/logs/worker.log
```

---

## Backup Strategy

### Database Backups

```bash
# MySQL
mysqldump -u nutriplan_user -p nutriplan > backup_$(date +%Y%m%d).sql

# PostgreSQL
pg_dump nutriplan > backup_$(date +%Y%m%d).sql

# SQLite
cp database/database.sqlite backup/database_$(date +%Y%m%d).sqlite
```

### Automated Backups (cron)

```bash
# Daily database backup
0 2 * * * mysqldump -u user -ppassword nutriplan | gzip > /backups/db_$(date +\%Y\%m\%d).sql.gz

# Keep last 30 days
0 3 * * * find /backups -name "db_*.sql.gz" -mtime +30 -delete
```

---

## Security Considerations

1. **Always set `APP_DEBUG=false` in production**
2. **Use strong `APP_KEY`** (generated via `php artisan key:generate`)
3. **Configure CORS** properly if needed
4. **Enable HTTPS** with valid SSL certificate
5. **Keep dependencies updated**
6. **Use environment variables** for sensitive data
7. **Configure firewall** to restrict access
8. **Regular security audits** (`composer audit`)

---

## Scaling Considerations

### Horizontal Scaling

- Use Redis for cache and queue
- Use load balancer for multiple app servers
- Shared storage for file uploads (S3, etc.)

### Vertical Scaling

- Increase PHP memory limit
- Add more queue workers
- Use database read replicas

---

## CI/CD

### GitHub Actions

The project includes CI/CD workflows in `.github/workflows/`:

- **lint.yml** - Code quality checks
- **tests.yml** - Automated testing
- **init-badges.yml** - Badge initialization

**Note:** These are for code quality checks. For deployment, integrate with your deployment platform.

---

## Post-Deployment Checklist

- [ ] Environment variables configured
- [ ] Database migrations run
- [ ] `php artisan optimize` executed
- [ ] SSL certificate installed
- [ ] Queue workers running
- [ ] Scheduler configured (cron: `* * * * * cd /path-to-project && php artisan schedule:run`)
- [ ] Backups configured
- [ ] Monitoring set up
- [ ] Error tracking configured (optional)
- [ ] CDN configured for assets (optional)
