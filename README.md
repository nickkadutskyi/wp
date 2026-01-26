# wp
WordPress boilerplate with Composer

- Manage configs via `.env` files (`symfony/dotenv`)
- WordPress core files are always separate from wp-content and are managed by WP-CLI
- Add your theme to `public/content/themes/`

## Getting Started

### Installation

#### Requirements

- PHP >= 8.3
- Composer
- Apache or Nginx web server
- MySQL

#### Installing

1. Create a project using Composer:

    ```bash
    composer create-project nickkadutskyi/wp my-wordpress-site
    cd my-wordpress-site
    ```

2. Install WordPress core files using WP-CLI:

    Install WP-CLI if you haven't already (see https://make.wordpress.org/cli/handbook/guides/installing/):
    
    ```bash
    curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
    ```

    Install WordPress core files:
    
    ```bash
    bin/wp-install
    ```

    This will install WordPress core version defined in `wp-cli.yml` into the `public/wp` directory.

    See `wp-cli.yml` for configuration details.

3. Configure your environment variables:

    Update `WP_HOME` in `.env.$APP_ENV` files for your environments (development, production, staging)

    Create `.env.$APP_ENV.local` files with sensitive data like DB credentials and SALT keys.
      - DB credentials: `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_HOST`
      - SALT keys: `AUTH_KEY`, `SECURE_AUTH_KEY`, `LOGGED_IN_KEY`, `NONCE_KEY`, `AUTH_SALT`, `SECURE_AUTH_SALT`, `LOGGED_IN_SALT`, `NONCE_SALT`
        - You can generate SALT keys at https://api.wordpress.org/secret-key/1.1/salt/

4. Install WordPress using WP-CLI:

    ```bash
    wp db create
    wp core install --url=https://yourwphome --title="Site Title" --admin_user=yourusername --admin_email=youremail
    # All third-party themes have `vendor-*` prefix to avoid name conflicts
    # So to enable Twenty Twenty-Five theme, use:
    wp theme activate vendor-twentytwentyfive
    ```

5. Configure your web server to point to the `public` directory as the document root.
    And now you can access Admin Dashboard at https://wphomeurl/wp/wp-admin/

6. Configure system scheduler (crontab) for `wp-cron.php` to run periodically 
   (e.g. every 15 minutes) and disable WP-Cron in .env.production:

    ```bash
    */15 * * * * wget --delete-after "https://yourwphome/wp/wp-cron.php"
    ```
    See https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/

#### Multisite Setup
Currently not automated.

### Configuration
To modify configuration, edit `.env` or `.env.$APP_ENV` files.
See `config/app.php` for details on how environment variables are used.

`APP_ENV` variable defines the environment (development, production, staging).
`APP_DEBUG` variable enables debug mode when set to `true` or `APP_ENV` is not `production`.

If you need to add custom configuration, you can do it in `config/app.php` file.

Use `.env.$APP_ENV` to set environment-specific variables.

Use `.env.$APP_ENV.local` to set sensitive data like DB credentials and SALT keys during deployment.

### Deployment
During deployment you will need to run the following commands:

To install Composer dependencies without dev packages and optimize autoloader:
```bash
composer install --no-dev --optimize-autoloader
```

Provide `.env.production.local` file with sensitive data like DB credentials and SALT keys on the server.

To generate `.env.local.php` file for better performance in production:
```bash
composer dump-env production
```

`.env.local.php` will be generated from `.env` `.env.production` and `.env.production.local` files.

Install WordPress core files if not already done:
```bash
bin/wp-install
```

If you updated the core you also need to update the database:
```bash
wp core update-db
```

(I will provide automated deployment scripts later)

#### Database Sync Scripts

Sync databases between environments (local, staging, production, or custom machines):

```bash
# Pull database from remote to local (creates backup automatically)
bin/db-pull production      # Pull from production to local
bin/db-pull staging         # Pull from staging to local
bin/db-pull machine_name    # Pull from another dev machine, need to provide machine config in .env 
                            # e.g. MACHINE_NAME_SSH_HOST and MACHINE_NAME_SSH_PATH

# Push database from local to remote (creates backup automatically)
bin/db-push staging         # Push local DB to staging (requires confirmation)
bin/db-push production      # Push local DB to production (requires stricter confirmation)

# Create backups
bin/db-backup               # Backup local database
bin/db-backup production    # Backup production database
bin/db-backup staging       # Backup staging database
```

Database scripts automatically:
- Create backups before any destructive operations
- Handle URL search-replace for WordPress site URLs
- Store backups in `.db-backups/` directory
- Require confirmation prompts (stricter for production)

#### Uploads Sync Scripts

Sync the uploads directory between environments using rsync:

```bash
# Pull uploads from remote to local
bin/uploads-pull production      # Pull from production to local
bin/uploads-pull staging         # Pull from staging to local
bin/uploads-pull machine_name    # Pull from another dev machine
bin/uploads-pull staging --dry-run   # Preview what would be synced

# Push uploads from local to remote
bin/uploads-push staging         # Push local uploads to staging
bin/uploads-push production      # Push local uploads to production (requires stricter confirmation)
bin/uploads-push machine_name    # Push to another dev machine

# Bidirectional sync between any two environments
bin/uploads-sync production local     # Pull from production to local
bin/uploads-sync local staging        # Push from local to staging
bin/uploads-sync machine_name local   # Pull from machine_name to local
bin/uploads-sync staging production   # Copy staging to production (via local)
```

Uploads scripts:
- Use rsync for efficient transfer (only changed files)
- Support `--dry-run` flag to preview changes
- Exclude system files (.DS_Store, Thumbs.db, .gitignore, index.php)
- Show size information before and after sync
- Delete files on destination that don't exist in source (use `--dry-run` first!)
- Require confirmation prompts (stricter for production)

#### Environment Configuration

Configure your environments in `.env.local`:

```bash
# Production
PRODUCTION_SSH_HOST=user@example.com
PRODUCTION_SSH_PATH=/var/www/example.com

# Staging
STAGING_SSH_HOST=user@staging.example.com
STAGING_SSH_PATH=/var/www/staging.example.com

# Other dev machines (for syncing between computers)
MACHINE_NAME_SSH_HOST=nick@machinesite.com
MACHINE_NAME_SSH_PATH=~/Documents/project_site
```

You can also override these settings via command line:
```bash
bin/db-pull custom user@myserver.com /path/to/site
bin/uploads-pull custom user@myserver.com /path/to/site
```
## Basics

### Using Composer

#### Adding Plugins and Themes with Composer

#### Set Plugins as Must Use (mu-plugins)

#### Updating Plugins and Themes

### Directory Structure

### Server Setup

#### Apache
Set `DocumentRoot` to `public` directory

You may add `.htaccess` in `public/` directory or into `<Directory>` section 
of your Apache config the following content:

```
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
```

#### Nginx

### Packages
- `symfony/dotenv` is used to manage environment variables
- `symfony/flex` is used for `composer dump-env` command to create `.env.local.php` file
  during deployment for better performance. You can remove it and use `.env` files directly if needed.
- `phpstan`, `psalm` with their respective extensions for static analysis
- `wp-coding-standards/wpcs` with phpcs for code standards checking
- `wpackagist-{plugin,theme}` for managing WordPress plugins and themes via Composer

### Local Development

