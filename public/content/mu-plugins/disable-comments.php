<?php
/**
 * Plugin Name: Disable Comments
 * Description: Closes comments and pingbacks site-wide when DISABLE_COMMENTS is true.
 *
 * @package WPStarter
 */

if ( ! defined( 'DISABLE_COMMENTS' ) || ! DISABLE_COMMENTS ) {
	return;
}

// Closing comments here also rejects submissions via wp-comments-post.php, the REST API, and XML-RPC.
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 20 );

add_action(
	'init',
	function () {
		foreach ( get_post_types() as $post_type ) {
			remove_post_type_support( $post_type, 'comments' );
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	},
	100
);

add_action(
	'admin_menu',
	function () {
		remove_menu_page( 'edit-comments.php' );
	}
);

add_action(
	'admin_bar_menu',
	function ( WP_Admin_Bar $admin_bar ) {
		$admin_bar->remove_node( 'comments' );
	},
	100
);
