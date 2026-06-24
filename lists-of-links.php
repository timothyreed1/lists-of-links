<?php
/**
 * Plugin Name: Lists of Links
 * Plugin URI:  https://github.com/timothyreed1/lists-of-links
 * Description: Manage and display links grouped by category with a shortcode.
 * Version:     1.2.1
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author:      Tim Reed
 * License:     GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: lists-of-links
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'LISTLINKS_VERSION',    '1.2.1' );
define( 'LISTLINKS_DB_VERSION', 3 );
define( 'LISTLINKS_TABLE',      'lists_of_links' );

require_once plugin_dir_path( __FILE__ ) . 'includes/db.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcode.php';

register_activation_hook( __FILE__, 'listlinks_create_table' );

add_action( 'wp_head', 'listlinks_maybe_noindex' );

function listlinks_maybe_noindex() {
    $raw = get_option( 'listlinks_noindex_paths', '' );
    if ( ! $raw ) {
        return;
    }
    $raw_uri      = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '/';
    $parsed_path  = parse_url( $raw_uri, PHP_URL_PATH );
    $request_path = '/' . ltrim( $parsed_path ?? '/', '/' );
    foreach ( array_filter( array_map( 'trim', explode( "\n", $raw ) ) ) as $path ) {
        if ( strpos( $request_path, $path ) === 0 ) {
            echo '<meta name="robots" content="noindex,nofollow">' . "\n";
            return;
        }
    }
}

// Run schema upgrades whenever the db schema version changes.
add_action( 'plugins_loaded', 'listlinks_upgrade' );

function listlinks_upgrade() {
    if ( (int) get_option( 'listlinks_db_version', 0 ) !== LISTLINKS_DB_VERSION ) {
        listlinks_create_table();
        listlinks_apply_migrations();
        update_option( 'listlinks_db_version', LISTLINKS_DB_VERSION );
    }
}

function listlinks_apply_migrations() {
    global $wpdb;
    $table = listlinks_table_name();

    // v2: add tags column if dbDelta did not add it.
    $col = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'tags'",
        DB_NAME,
        $table
    ) );
    if ( ! $col ) {
        $wpdb->query( "ALTER TABLE `{$table}` ADD COLUMN `tags` VARCHAR(255) NOT NULL DEFAULT ''" );
    }
}
