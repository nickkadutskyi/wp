<?php
/**
 * Configuration file for WordPress
 *
 * Uses symfony/dotenv to manage environment variables to configure WordPress settings.
 *
 * @package WPStarter
 */

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

/**
 * Directory containing all of the site's files
 *
 * @var string
 */
$root_dir = dirname( __DIR__ );

/**
 * Document Root
 *
 * @var non-falsy-string
 */
$webroot_dir = $root_dir . '/public';

/**
 * Use symfony/dotenv to load environment variables from .env files
 */
$dotenv = new Dotenv();
$dotenv->bootEnv( $root_dir . '/.env' );

// Ensure required environment variables are set.
$required_env_vars = array(
    'WP_HOME',
    'WP_SITEURL',
    'DB_NAME',
    'DB_USER',
    'DB_PASSWORD',
    'DB_HOST',
);
$missing_env_vars  = array_filter(
    $required_env_vars,
    function ( $v ) {
        return ! isset( $_ENV[ $v ] );
    }
);
if ( ! empty( $missing_env_vars ) ) {
    throw new RuntimeException( 'Missing required environment variables: ' . implode( ', ', $missing_env_vars ) );
}

// Sets WP's environment (local, development, staging, production).
define( 'WP_ENVIRONMENT_TYPE', $_ENV['WP_ENVIRONMENT_TYPE'] ?? 'development' );

define( 'WP_SITEURL', $_ENV['WP_SITEURL'] );

define( 'WP_CONTENT_DIR', $_ENV['WP_CONTENT_DIR'] ?? dirname( __DIR__ ) . '/public/content' );
