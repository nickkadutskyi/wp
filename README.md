# wp
WordPress boilerplate with Composer

- Manage configs via `.env` files (`symfony/dotenv`)
- WordPress core files are always separate from wp-content and are managed by WP-CLI

## Getting Started

### Installation

#### Requirements

- PHP >= 8.3
- Composer
- Apache or Nginx web server
- MySQL

#### Installing

1. Create a project using Composer:

    ```
    composer create-project nickkadutskyi/wp my-wordpress-site
    cd my-wordpress-site
    ```

2. Install WordPress core files using WP-CLI:

    Install WP-CLI if you haven't already (see https://make.wordpress.org/cli/handbook/guides/installing/):
    
    ```
    curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
    ```

    Install WordPress core files:
    
    ```
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

    ```
    wp db create
    wp core install --url=https://yourwphome --title="Site Title" --admin_user=yourusername --admin_email=youremail
    ```

5. 

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
- `symfony/dotenv` is used to manage environment variables
- `symfony/flex` is used for `composer dump-env` command to create `.env.local.php` file
  during deployment for better performance. You can remove it and use `.env` files directly if needed.
- `phpstan`, `psalm` with their respective extensions for static analysis
- `wp-coding-standards/wpcs` with phpcs for code standards checking
- `wpackagist-{plugin,theme}` for managing WordPress plugins and themes via Composer
