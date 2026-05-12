<?php
/**
 * Plugin Name: Lists of Links
 * Plugin URI:  https://github.com/timothyreed1/lists-of-links
 * Description: Manage and display links grouped by category with a shortcode.
 * Version:     1.0.0
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

define( 'LISTLINKS_VERSION', '1.0.0' );
define( 'LISTLINKS_TABLE', 'lists_of_links' );

require_once plugin_dir_path( __FILE__ ) . 'includes/db.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcode.php';

register_activation_hook( __FILE__, 'listlinks_create_table' );
