# Deployment Guide - HomeWarranty Pro

This guide provides step-by-step instructions for deploying the HomeWarranty Pro application to production.

## Prerequisites

- Server with PHP 8.2+ (Apache/Nginx)
- MySQL or PostgreSQL database
- Composer installed
- Node.js and NPM installed
- Git access
- SSL certificate (recommended)

## Option 1: Laravel Forge (Recommended)

Laravel Forge provides the easiest deployment process for Laravel applications.

### Steps:

1. **Create a Forge Account**
   - Visit [forge.laravel.com](https://forge.laravel.com)
   - Connect your DigitalOcean, Linode, or AWS account

2. **Provision a Server**
   - Choose PHP 8.2
   - Select appropriate server size (2GB RAM minimum recommended)
   - Choose your preferred region

3. **Create a New Site**
   - Domain: `yourdomain.com`
   - Project Type: General PHP / Laravel
   - Repository: Connect your Git repository

4. **Configure Environment**
   - Navigate to site → Environment
   - Update `.env` variables:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=homewarranty_pro
   DB_USERNAME=forge
   DB_PASSWORD=your-secure-password
   ```

5. **Enable Quick Deploy**
   - Navigate to site → Apps
   - Enable "Quick Deploy"
   - Push to your main branch to auto-deploy

6. **Run Initial Deployment**
   ```bash
   cd /home/forge/yourdomain.com
   composer install --optimize-autoloader --no-dev
   npm install && npm run build
   php artisan migrate --force
   php artisan storage:link
   ```

7. **Configure SSL**
   - Navigate to site → SSL
   - Click "Let's Encrypt"
   - Enable "Enforce HTTPS"

## Option 2: Manual Deployment (VPS)

### 1. Server Setup

Connect to your server via SSH:

```bash
ssh user@your-server-ip
```

### 2. Install Dependencies

```bash
# Update packages
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2 and extensions
sudo apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-intl php8.2-bcmath

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js and NPM
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Nginx
sudo apt install -y nginx

# Install MySQL
sudo apt install -y mysql-server
```

### 3. Configure MySQL

```bash
sudo mysql_secure_installation

# Create database
sudo mysql -e "CREATE DATABASE homewarranty_pro;"
sudo mysql -e "CREATE USER 'homewarranty'@'localhost' IDENTIFIED BY 'secure_password';"
sudo mysql -e "GRANT ALL PRIVILEGES ON homewarranty_pro.* TO 'homewarranty'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"
```

### 4. Clone Repository

```bash
cd /var/www
sudo git clone https://github.com/yourusername/home-warranty-pro.git
cd home-warranty-pro
```

### 5. Install Application Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node dependencies and build assets
npm install
npm run build
```

### 6. Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit environment file
nano .env
```

Update `.env`:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=homewarranty_pro
DB_USERNAME=homewarranty
DB_PASSWORD=secure_password
```

### 7. Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/home-warranty-pro
sudo chmod -R 755 /var/www/home-warranty-pro
sudo chmod -R 775 /var/www/home-warranty-pro/storage
sudo chmod -R 775 /var/www/home-warranty-pro/bootstrap/cache
```

### 8. Run Migrations

```bash
php artisan migrate --force
php artisan storage:link
```

### 9. Configure Nginx

Create Nginx configuration:

```bash
sudo nano /etc/nginx/sites-available/homewarranty-pro
```

Add configuration:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/home-warranty-pro/public;

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
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/homewarranty-pro /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 10. Install SSL Certificate (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

### 11. Configure Supervisor for Queue Workers (Optional)

```bash
sudo apt install -y supervisor

sudo nano /etc/supervisor/conf.d/homewarranty-worker.conf
```

Add configuration:
```ini
[program:homewarranty-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/home-warranty-pro/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/home-warranty-pro/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start homewarranty-worker:*
```

## Post-Deployment Steps

### 1. Create Admin User

```bash
php artisan tinker
```

```php
$admin = App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@yourdomain.com',
    'password' => bcrypt('secure-password'),
    'role' => 'admin',
    'email_verified_at' => now(),
]);
```

### 2. Create Builder User

```php
$builder = App\Models\User::create([
    'name' => 'Builder Manager',
    'email' => 'builder@yourdomain.com',
    'password' => bcrypt('secure-password'),
    'role' => 'builder',
    'email_verified_at' => now(),
]);
```

### 3. Configure Cron Jobs

```bash
crontab -e
```

Add:
```
* * * * * cd /var/www/home-warranty-pro && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Optimize Application

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Updating the Application

When deploying updates:

```bash
cd /var/www/home-warranty-pro

# Pull latest changes
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

## Monitoring & Maintenance

### Log Files
- Application logs: `storage/logs/laravel.log`
- Nginx access: `/var/log/nginx/access.log`
- Nginx error: `/var/log/nginx/error.log`

### Database Backups

Create a daily backup script:

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u homewarranty -p homewarranty_pro > /backups/db_backup_$DATE.sql
find /backups -name "db_backup_*.sql" -mtime +7 -delete
```

### Health Checks
- Monitor `/up` endpoint for application health
- Set up uptime monitoring (e.g., UptimeRobot, Pingdom)
- Configure error tracking (e.g., Sentry, Bugsnag)

## Troubleshooting

### 500 Internal Server Error
- Check `storage/logs/laravel.log`
- Verify file permissions
- Ensure `.env` is configured correctly
- Run `php artisan config:clear`

### Storage Issues
- Verify symbolic link: `php artisan storage:link`
- Check permissions: `chmod -R 775 storage`

### Database Connection Errors
- Verify database credentials in `.env`
- Check MySQL is running: `sudo systemctl status mysql`
- Test connection: `php artisan tinker` then `DB::connection()->getPdo()`

## Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong database passwords
- [ ] SSL certificate installed and enforced
- [ ] Regular security updates
- [ ] Firewall configured (UFW)
- [ ] Fail2ban installed
- [ ] Regular backups scheduled
- [ ] `.env` file protected (not in git)
- [ ] File permissions properly set

## Support

For deployment issues, contact your system administrator or refer to:
- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)
- [Filament Installation Guide](https://filamentphp.com/docs/installation)
