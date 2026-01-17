<?php
/**
 * Mail configuration for WordPress
 *
 * @package WPStarter
 *
 * phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
 * phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped
 * phpcs:disable Universal.Operators.DisallowShortTernary.Found
 */

/**
 * WP Offload SES Lite Plugin https://wordpress.org/plugins/wp-ses/
 * To install `composer require wpackagist-plugin/wp-ses`
 *
 * Docs: https://deliciousbrains.com/amazon-ses-tutorial/
 *
 * Use this plugin if you need AWS SES because WP Mail SMTP has it only in the Pro version.
 */

// TODO: Implement WP Offload SES Lite settings via environment variables.

/**
 * WP Mail SMTP Plugin https://wordpress.org/plugins/wp-mail-smtp/
 * To install `composer require wpackagist-plugin/wp-mail-smtp`
 *
 * Docs: https://wpmailsmtp.com/docs/how-to-secure-smtp-settings-by-using-constants/
 */

/** Enable WP Mail SMTP Constants. This disables settings in the admin area. */
define( 'WPMS_ON', filter_var( $_ENV['WPMS_ON'] ?? false, FILTER_VALIDATE_BOOL ) );

/** Defining General Settings Constants. */
define( 'WPMS_LICENSE_KEY', $_ENV['WPMS_LICENSE_KEY'] ?? '' );
define( 'WPMS_MAIL_FROM', $_ENV['WPMS_MAIL_FROM'] ?? 'noreply@example.com' );
define( 'WPMS_MAIL_FROM_FORCE', filter_var( $_ENV['WPMS_MAIL_FROM_FORCE'] ?? true, FILTER_VALIDATE_BOOL ) ); // True turns it on, false turns it off.
define( 'WPMS_MAIL_FROM_NAME', $_ENV['WPMS_MAIL_FROM_NAME'] ?? 'Example Name' );
define( 'WPMS_MAIL_FROM_NAME_FORCE', filter_var( $_ENV['WPMS_MAIL_FROM_NAME_FORCE'] ?? true, FILTER_VALIDATE_BOOL ) ); // True turns it on, false turns it off.
$wpms_mailer_raw = $_ENV['WPMS_MAILER'] ?? '';
$wpms_mailer     = is_string( $wpms_mailer_raw ) ? trim( $wpms_mailer_raw ) : '';
if ( '' === $wpms_mailer ) {
	$wpms_mailer = 'smtp';
}
$wpms_mailer_options = [ 'mail', 'gmail', 'mailgun', 'sendgrid', 'smtp', 'amazonses', 'sendlayer', 'smtpcom', 'sendinblue', 'outlook', 'postmark', 'sparkpost', 'zoho' ];
if ( filter_var( $_ENV['WPMS_ON'] ?? false, FILTER_VALIDATE_BOOL )
	&& ! in_array( $wpms_mailer, $wpms_mailer_options, true )
) {
	throw new RuntimeException( 'Invalid WPMS_MAILER value: ' . $wpms_mailer );
}
define( 'WPMS_MAILER', $wpms_mailer );
define( 'WPMS_SET_RETURN_PATH', filter_var( $_ENV['WPMS_SET_RETURN_PATH'] ?? true, FILTER_VALIDATE_BOOL ) ); // Sets $phpmailer->Sender if true.
define( 'WPMS_DO_NOT_SEND', filter_var( $_ENV['WPMS_DO_NOT_SEND'] ?? true, FILTER_VALIDATE_BOOL ) ); // Possible values: true, false.

/** Logging. */
define( 'WPMS_LOGS_ENABLED', filter_var( $_ENV['WPMS_LOGS_ENABLED'] ?? true, FILTER_VALIDATE_BOOL ) ); // True turns it on, false turns it off.
define( 'WPMS_LOGS_LOG_EMAIL_CONTENT', filter_var( $_ENV['WPMS_LOGS_LOG_EMAIL_CONTENT'] ?? true, FILTER_VALIDATE_BOOL ) ); // True turns it on and stores email content, false turns it off.
define(
	'WPMS_LOGS_LOG_RETENTION_PERIOD',
	filter_var( $_ENV['WPMS_LOGS_LOG_RETENTION_PERIOD'] ?? 0, FILTER_VALIDATE_INT ) ?: 0
); // How long email logs should be retained before they are deleted, in seconds. To disable the log retention period and keep logs forever, set to 0.

/** Defining  Amazon SES Constants. */
if ( 'amazonses' === WPMS_MAILER ) {
	define( 'WPMS_AMAZONSES_CLIENT_ID', $_ENV['WPMS_AMAZONSES_CLIENT_ID'] ?? '' );
	define( 'WPMS_AMAZONSES_CLIENT_SECRET', $_ENV['WPMS_AMAZONSES_CLIENT_SECRET'] ?? '' );
	// Possible values for region: 'us-east-1', 'us-east-2', 'us-west-1', 'us-west-2', 'eu-west-1',
	// 'eu-west-2', 'eu-west-3', 'eu-central-1', 'eu-north-1', 'ap-south-1', 'ap-northeast-1',
	// 'ap-northeast-2', 'ap-southeast-1', 'ap-southeast-2', 'ca-central-1', 'sa-east-1'.
	define( 'WPMS_AMAZONSES_REGION', $_ENV['WPMS_AMAZONSES_REGION'] ?? '' );
}

/** Defining Google Mailer Constants. */
if ( 'gmail' === WPMS_MAILER ) {
	define( 'WPMS_GMAIL_CLIENT_ID', $_ENV['WPMS_GMAIL_CLIENT_ID'] ?? '' );
	define( 'WPMS_GMAIL_CLIENT_SECRET', $_ENV['WPMS_GMAIL_CLIENT_SECRET'] ?? '' );
}

/** Defining SMTP Constants. */
if ( 'smtp' === WPMS_MAILER ) {
	define( 'WPMS_SMTP_HOST', $_ENV['WPMS_SMTP_HOST'] ?? '' ); // The SMTP mail host.
	define(
		'WPMS_SMTP_PORT',
		filter_var( $_ENV['WPMS_SMTP_PORT'] ?? 587, FILTER_VALIDATE_INT ) ?: 587
	); // The SMTP server port number.
	$wpms_ssl_raw = $_ENV['WPMS_SSL'] ?? '';
	$wpms_ssl     = is_string( $wpms_ssl_raw ) ? trim( $wpms_ssl_raw ) : '';
	if ( ! in_array( $wpms_ssl, [ '', 'ssl', 'tls' ], true ) ) {
		throw new RuntimeException( 'Invalid WPMS_SSL value: ' . $wpms_ssl );
	}
	define( 'WPMS_SSL', $wpms_ssl ); // Possible values '', 'ssl', 'tls' - note TLS is not STARTTLS.
	define( 'WPMS_SMTP_AUTH', filter_var( $_ENV['WPMS_SMTP_AUTH'] ?? true, FILTER_VALIDATE_BOOL ) ); // True turns it on, false turns it off.
	define( 'WPMS_SMTP_USER', $_ENV['WPMS_SMTP_USER'] ?? '' ); // SMTP authentication username, only used if WPMS_SMTP_AUTH is true.
	define( 'WPMS_SMTP_PASS', $_ENV['WPMS_SMTP_PASS'] ?? '' ); // SMTP authentication password, only used if WPMS_SMTP_AUTH is true.
	define( 'WPMS_SMTP_AUTOTLS', filter_var( $_ENV['WPMS_SMTP_AUTOTLS'] ?? true, FILTER_VALIDATE_BOOL ) ); // True turns it on, false turns it off.
}
