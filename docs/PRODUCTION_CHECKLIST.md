# Nexus — Production Deployment Checklist

## Before deploying to any customer server

### Environment
- [ ] `.env` has `APP_ENV=production` and `APP_DEBUG=false`
- [ ] `APP_KEY` is set and unique per installation
- [ ] `APP_URL` matches the server's actual domain
- [ ] Database credentials are set and tested
- [ ] `QUEUE_CONNECTION` set to `redis` or `database`
- [ ] Mail configuration set and tested
- [ ] `SESSION_DRIVER=database` for multi-server setups

### Security
- [ ] `DevDataSeeder` removed from `DatabaseSeeder::run()` or gated behind `app()->isLocal()`
- [ ] Default super admin password changed from `password`
- [ ] SSL certificate installed and HTTPS enforced
- [ ] `.env` file is not publicly accessible
- [ ] `storage/` and `bootstrap/cache/` are writable

### Build
- [ ] `npm run build` completed without errors
- [ ] `composer install --no-dev --optimize-autoloader` run
- [ ] `php artisan config:cache` run
- [ ] `php artisan route:cache` run
- [ ] `php artisan view:cache` run
- [ ] `php artisan event:cache` run

### Database
- [ ] `php artisan migrate --force` run
- [ ] `php artisan db:seed --force` run (first install only)
- [ ] Database backups scheduled

### Licence
- [ ] Valid licence key file placed at `storage/licence/key.txt`
- [ ] `LICENCE_PUBLIC_KEY` set in `.env` matching your RSA key pair
- [ ] Licence domain matches `APP_URL` hostname

### Queue
- [ ] Queue worker running (`php artisan queue:work`)
- [ ] Supervisor or systemd configured to restart worker on failure
- [ ] Failed jobs monitored

### Post-deploy verification
- [ ] Login works with customer-provided admin credentials
- [ ] All licensed modules appear in sidebar
- [ ] Invoice creation and PDF generation tested
- [ ] Email delivery tested (send a test invoice)
- [ ] Theme customisation saved and persisted

