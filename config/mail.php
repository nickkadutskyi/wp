<?php
/**
 * Mail configuration for WordPress
 *
 * @package WPStarter
 *
 * phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
 * phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped
 * phpcs:disable Universal.Operators.DisallowShortTernary.Found
 * phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
 */

/**
 * WP Offload SES Lite Plugin https://wordpress.org/plugins/wp-ses/
 * To install `composer require wpackagist-plugin/wp-ses`
 *
 * Docs: https://deliciousbrains.com/amazon-ses-tutorial/
 * Settings constants: https://deliciousbrains.com/wp-offload-ses/doc/settings-constants/
 *
 * Use this plugin if you need AWS SES because WP Mail SMTP has it only in the Pro version.
 */

/** Enable WP Offload SES settings constants. */
$wposes_on = filter_var( $_ENV['WPOSES_ON'] ?? false, FILTER_VALIDATE_BOOL );
if ( $wposes_on ) {
	$wposes_health_report_frequency_raw = $_ENV['WPOSES_HEALTH_REPORT_FREQUENCY'] ?? '';
	$wposes_health_report_frequency     = is_string( $wposes_health_report_frequency_raw )
		? trim( $wposes_health_report_frequency_raw )
		: '';
	if ( '' !== $wposes_health_report_frequency
		&& ! in_array( $wposes_health_report_frequency, [ 'daily', 'weekly', 'monthly' ], true )
	) {
		throw new RuntimeException( 'Invalid WPOSES_HEALTH_REPORT_FREQUENCY value: ' . $wposes_health_report_frequency );
	}

	$wposes_health_report_recipients_raw = $_ENV['WPOSES_HEALTH_REPORT_RECIPIENTS'] ?? 'site-admins';
	$wposes_health_report_recipients     = is_string( $wposes_health_report_recipients_raw )
		? trim( $wposes_health_report_recipients_raw )
		: 'site-admins';
	if ( '' !== $wposes_health_report_recipients
		&& ! in_array( $wposes_health_report_recipients, [ 'site-admins', 'custom' ], true )
	) {
		throw new RuntimeException( 'Invalid WPOSES_HEALTH_REPORT_RECIPIENTS value: ' . $wposes_health_report_recipients );
	}

	$wposes_log_duration_raw = $_ENV['WPOSES_LOG_DURATION'] ?? '';
	$wposes_log_duration     = filter_var( $wposes_log_duration_raw, FILTER_VALIDATE_INT );
	$wposes_log_duration     = false === $wposes_log_duration ? '' : (string) $wposes_log_duration;

	define( 'WPOSES_AWS_ACCESS_KEY_ID', $_ENV['WPOSES_AWS_ACCESS_KEY_ID'] ?? '' );
	define( 'WPOSES_AWS_SECRET_ACCESS_KEY', $_ENV['WPOSES_AWS_SECRET_ACCESS_KEY'] ?? '' );
	define( 'WPOSES_LICENCE', $_ENV['WPOSES_LICENCE'] ?? '' );

	define(
		'WPOSES_SETTINGS',
		serialize(
			[
				// Send site emails via Amazon SES.
				'send-via-ses'                    => filter_var( $_ENV['WPOSES_SEND_VIA_SES'] ?? true, FILTER_VALIDATE_BOOL ),
				// Queue email, but do not send it.
				'enqueue-only'                    => filter_var( $_ENV['WPOSES_ENQUEUE_ONLY'] ?? false, FILTER_VALIDATE_BOOL ),
				// Enable open tracking.
				'enable-open-tracking'            => filter_var( $_ENV['WPOSES_ENABLE_OPEN_TRACKING'] ?? false, FILTER_VALIDATE_BOOL ),
				// Enable click tracking.
				'enable-click-tracking'           => filter_var( $_ENV['WPOSES_ENABLE_CLICK_TRACKING'] ?? false, FILTER_VALIDATE_BOOL ),
				// Enable subsite settings (if on multisite).
				'enable-subsite-settings'         => filter_var( $_ENV['WPOSES_ENABLE_SUBSITE_SETTINGS'] ?? false, FILTER_VALIDATE_BOOL ),
				// Amazon SES region (e.g. 'us-east-1' - leave blank for default region).
				'region'                          => $_ENV['WPOSES_REGION'] ?? '',
				// Changes the default email address used by WordPress.
				'default-email'                   => $_ENV['WPOSES_DEFAULT_EMAIL'] ?? '',
				// Changes the default email name used by WordPress.
				'default-email-name'              => $_ENV['WPOSES_DEFAULT_EMAIL_NAME'] ?? '',
				// Sets the "Reply-To" header for all outgoing emails.
				'reply-to'                        => $_ENV['WPOSES_REPLY_TO'] ?? '',
				// Sets the "Return-Path" header used by Amazon SES.
				'return-path'                     => $_ENV['WPOSES_RETURN_PATH'] ?? '',
				// Amount of days to keep email logs (e.g. 30, 60, 90, 180, 365, 730).
				'log-duration'                    => $wposes_log_duration,
				// Enable instantly deleting a successfully sent email from the log.
				'delete-successful'               => filter_var( $_ENV['WPOSES_DELETE_SUCCESSFUL'] ?? false, FILTER_VALIDATE_BOOL ),
				// Enable instantly deleting successfully re-sent failed emails from the log (Pro only).
				'delete-re-sent-failed'           => filter_var( $_ENV['WPOSES_DELETE_RE_SENT_FAILED'] ?? false, FILTER_VALIDATE_BOOL ),
				// Enables the health report.
				'enable-health-report'            => filter_var( $_ENV['WPOSES_ENABLE_HEALTH_REPORT'] ?? false, FILTER_VALIDATE_BOOL ),
				// Frequency of the health report (daily, weekly, monthly).
				'health-report-frequency'         => $wposes_health_report_frequency,
				// Recipients of the health report (site-admins, custom).
				'health-report-recipients'        => $wposes_health_report_recipients,
				// If using custom recipients for health report, use comma separated list of recipients.
				'health-report-custom-recipients' => $_ENV['WPOSES_HEALTH_REPORT_CUSTOM_RECIPIENTS'] ?? '',
			]
		)
	);
}

/**
 * WP Mail SMTP Plugin https://wordpress.org/plugins/wp-mail-smtp/
 * To install `composer require wpackagist-plugin/wp-mail-smtp`
 *
 * Docs: https://wpmailsmtp.com/docs/how-to-secure-smtp-settings-by-using-constants/
 */

/** Enable WP Mail SMTP Constants. This disables settings in the admin area. */
define( 'WPMS_ON', filter_var( $_ENV['WPMS_ON'] ?? false, FILTER_VALIDATE_BOOL ) );

if ( WPMS_ON ) {
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

	/** Defining Postmark Constants. */
	if ( 'postmark' === WPMS_MAILER ) {
		define( 'WPMS_POSTMARK_SERVER_API_TOKEN', $_ENV['WPMS_POSTMARK_SERVER_API_TOKEN'] ?? '' );
		define( 'WPMS_POSTMARK_MESSAGE_STREAM', $_ENV['WPMS_POSTMARK_MESSAGE_STREAM'] ?? '' );
	}
}
