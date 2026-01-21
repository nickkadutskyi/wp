<?php
/**
 * Configuration file for WordPress
 *
 * Uses symfony/dotenv to manage environment variables to configure WordPress settings.
 *
 * @package WPStarter
 *
 * phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
 * phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped
 * phpcs:disable Generic.Commenting.DocComment.MissingShort
 * phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
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
$dotenv->setProdEnvs( [ 'production', 'prod' ] );
$dotenv->bootEnv( $root_dir . '/.env' );

// Ensure required environment variables are set.
$required_env_vars = [
	'WP_HOME',
	'WP_SITEURL',
];
// If DATABASE_URL is not set, require individual DB settings.
if ( ! isset( $_ENV['DATABASE_URL'] ) ) {
	$required_env_vars = array_merge(
		$required_env_vars,
		[
			'DB_NAME',
			'DB_USER',
			'DB_PASSWORD',
			'DB_HOST',
		]
	);
}
$missing_env_vars = array_filter(
	$required_env_vars,
	function ( $v ) {
		return ! isset( $_ENV[ $v ] );
	}
);
if ( ! empty( $missing_env_vars ) ) {
	throw new RuntimeException( 'Missing required environment variables: ' . implode( ', ', $missing_env_vars ) );
}

// Sets WP's environment (local, development, staging, production).
define( 'APP_ENV', $_ENV['APP_ENV'] ?? 'production' );
if ( ! ( $_ENV['WP_ENVIRONMENT_TYPE'] ?? null ) && in_array( APP_ENV, [ 'production', 'staging', 'development', 'local' ], true ) ) {
	define( 'WP_ENVIRONMENT_TYPE', APP_ENV );
} else {
	define( 'WP_ENVIRONMENT_TYPE', $_ENV['WP_ENVIRONMENT_TYPE'] ?? 'production' );
}

if ( APP_ENV !== 'production' ) {
	// `all` enables all development mode features (core, theme, plugin).
	// Disables theme.json and patterns caching among other things.
	define( 'WP_DEVELOPMENT_MODE', 'all' );
}

/**
 * Define WP_HOME and WP_SITEURL from environment variables
 */
define( 'WP_HOME', $_ENV['WP_HOME'] ?? ( isset( $_SERVER['HTTP_HOST'] ) ? 'https://' . $_SERVER['HTTP_HOST'] : '' ) );
define( 'WP_SITEURL', $_ENV['WP_SITEURL'] ?? ( isset( $_SERVER['HTTP_HOST'] ) ? 'https://' . $_SERVER['HTTP_HOST'] : '' ) );

// Custom content directory.
/** @var string $content_dir */
$content_dir = $_ENV['CONTENT_DIR'] ?? '/content';
define( 'CONTENT_DIR', $content_dir );
/** @var string $wp_content_dir */
$wp_content_dir = $_ENV['WP_CONTENT_DIR'] ?? ( $webroot_dir . $content_dir );
define( 'WP_CONTENT_DIR', $wp_content_dir );
/** @var string $wp_home */
$wp_home = $_ENV['WP_HOME'] ?? '';
define( 'WP_CONTENT_URL', $wp_home . $content_dir );

// // ** Database settings - You can get this info from your web host ** //

/** The name of the database for WordPress */
define( 'DB_NAME', $_ENV['DB_NAME'] ?? '' );
/** Database username */
define( 'DB_USER', $_ENV['DB_USER'] ?? '' );
/** Database password */
define( 'DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '' );
/** Database hostname */
define( 'DB_HOST', $_ENV['DB_HOST'] ?? 'localhost' );
/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4' );
/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', $_ENV['DB_COLLATE'] ?? '' );

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = $_ENV['TABLE_PREFIX'] ?? 'wp_'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

// Got this from roots/bedrock.
if ( isset( $_ENV['DATABASE_URL'] ) ) {
	$dsn = (object) wp_parse_url( $_ENV['DATABASE_URL'] );

	define( 'DB_HOST', isset( $dsn->port ) ? "{$dsn->host}:{$dsn->port}" : $dsn->host );
	define( 'DB_NAME', substr( $dsn->path, 1 ) );
	define( 'DB_USER', $dsn->user );
	define( 'DB_PASSWORD', isset( $dsn->pass ) ? $dsn->pass : null );
}

// Settings.
// Prevent plugin and theme modifications via file editor.
define( 'DISALLOW_FILE_EDIT', filter_var( $_ENV['DISALLOW_FILE_EDIT'] ?? true, FILTER_VALIDATE_BOOL ) );
// Prevent all plugin and theme updates and installation.
define( 'DISALLOW_FILE_MODS', filter_var( $_ENV['DISALLOW_FILE_MODS'] ?? true, FILTER_VALIDATE_BOOL ) );
define( 'AUTOMATIC_UPDATER_DISABLED', filter_var( $_ENV['AUTOMATIC_UPDATER_DISABLED'] ?? true, FILTER_VALIDATE_BOOL ) );
define( 'WP_AUTO_UPDATE_CORE', filter_var( $_ENV['WP_AUTO_UPDATE_CORE'] ?? false, FILTER_VALIDATE_BOOL ) );
// Disable concatenation of scripts.
define( 'CONCATENATE_SCRIPTS', filter_var( $_ENV['CONCATENATE_SCRIPTS'] ?? false, FILTER_VALIDATE_BOOL ) );

// Limit the number of post revisions.
define(
	'WP_POST_REVISIONS',
	filter_var( $_ENV['WP_POST_REVISIONS'] ?? null, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE )
		?? filter_var( $_ENV['WP_POST_REVISIONS'] ?? true, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE )
		?? true
);

define( 'DISABLE_WP_CRON', filter_var( $_ENV['DISABLE_WP_CRON'] ?? false, FILTER_VALIDATE_BOOL ) );

/**
 * Allow WordPress to detect HTTPS when used behind a reverse proxy or load balancer
 * See https://developer.wordpress.org/reference/functions/is_ssl/#more-information
 */
if ( 'https' === ( $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '' )
		|| 'https' === ( $_SERVER['HTTP_CLOUDFRONT_FORWARDED_PROTO'] ?? '' )
		|| 'https' === ( $_SERVER['CloudFront-Forwarded-Proto'] ?? '' )
) {
	$_SERVER['HTTPS'] = 'on';
}

/**
 * Debugging Settings
 */
define( 'SAVEQUERIES', filter_var( $_ENV['SAVEQUERIES'] ?? $_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL ) );
// WP_DEBUG set to `true` enables the debug mode throughout WordPress and shows errors and warnings.
define( 'WP_DEBUG', filter_var( $_ENV['WP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL ) );
define( 'WP_DEBUG_DISPLAY', filter_var( $_ENV['WP_DEBUG_DISPLAY'] ?? $_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL ) );
$wp_debug_log_raw = $_ENV['WP_DEBUG_LOG'] ?? $_ENV['APP_DEBUG'] ?? false;
$wp_debug_log     = filter_var( $wp_debug_log_raw, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE );
if ( null === $wp_debug_log ) {
	$wp_debug_log = is_string( $wp_debug_log_raw ) && '' !== trim( $wp_debug_log_raw )
		? trim( $wp_debug_log_raw )
		: false;
}

define( 'WP_DEBUG_LOG', $wp_debug_log );
define( 'SCRIPT_DEBUG', filter_var( $_ENV['SCRIPT_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL ) );
define( 'WP_DISABLE_FATAL_ERROR_HANDLER', filter_var( $_ENV['WP_DISABLE_FATAL_ERROR_HANDLER'] ?? $_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL ) );

/**
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY', $_ENV['AUTH_KEY'] ?? '' );
define( 'SECURE_AUTH_KEY', $_ENV['SECURE_AUTH_KEY'] ?? '' );
define( 'LOGGED_IN_KEY', $_ENV['LOGGED_IN_KEY'] ?? '' );
define( 'NONCE_KEY', $_ENV['NONCE_KEY'] ?? '' );
define( 'AUTH_SALT', $_ENV['AUTH_SALT'] ?? '' );
define( 'SECURE_AUTH_SALT', $_ENV['SECURE_AUTH_SALT'] ?? '' );
define( 'LOGGED_IN_SALT', $_ENV['LOGGED_IN_SALT'] ?? '' );
define( 'NONCE_SALT', $_ENV['NONCE_SALT'] ?? '' );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', $webroot_dir . '/wp/' );
}
