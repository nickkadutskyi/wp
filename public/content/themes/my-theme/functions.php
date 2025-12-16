<?php
/**
 * Custom Functions for Theme
 *
 * @package MyTheme
 */

// Setup.
define( 'THEME_STYLE_URL', get_stylesheet_uri() );
define( 'THEME_DIR', __DIR__ );
define( 'THEME_URL', get_template_directory_uri() );

// Favicon.
add_action(
    'wp_head',
    static function () {
        echo '<link rel="shortcut icon" href="' . esc_url( THEME_URL . '/assets/images/favicon.png' ) . '" />';
    }
);

// We prevent loading remote block patterns from Block Pattern Directory to
// improve performance and reduce confusion.
add_filter( 'should_load_remote_block_patterns', '__return_false' );
// We disables core block patterns to avoid clutter.
add_action(
    'after_setup_theme',
    function () {
        remove_theme_support( 'core-block-patterns' );
    }
);
// Disable directory blocks in editor to avoid loading external resources.
add_action(
    'admin_init',
    static function () {
        remove_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets' );
    }
);
