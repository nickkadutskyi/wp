<?php
/**
 * Analytics Configuration for WordPress
 *
 * @package WPStarter
 *
 * phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
 */

/**
 * Independent Analytics Plugin https://wordpress.org/plugins/independent-analytics/
 */

/** See: https://independentwp.com/knowledgebase/developer/pantheon-compatibility-fix-bladeone-error/ */
define( 'IAWP_TEMP_DIR', $_ENV['IAWP_TEMP_DIR'] ?? WP_CONTENT_DIR . '/cache/iawp/' );
