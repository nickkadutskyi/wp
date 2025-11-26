# wp
WordPress boilerplate with Composer

- Manage configs via `.env` files (symfony/dotenv)
- WordPress core files are always separate from wp-content and are managed by WP-CLI

## Initial Setup
```bash
composer create-project nickkadutskyi/wp
```

Update .env.$APP_ENV files 

Provide .env.$APP_ENV.local files with sensitive data like DB credentials and SALT keys

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

## Packages
- symfony/dotenv is used to manage environment variables
- symfony/flex is used for `composer dump-env` command to create `.env.local.php` file
  during deployment for better performance
- phpstan, psalm with their respective extensions for static analysis
- wp-coding-standards/wpcs with phpcs for code standards checking
- wpackagist-{plugin,theme} for managing WordPress plugins and themes via Composer
