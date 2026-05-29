<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function listlinks_table_name() {
    global $wpdb;
    return $wpdb->prefix . LISTLINKS_TABLE;
}

function listlinks_create_table() {
    global $wpdb;
    $table      = listlinks_table_name();
    $charset    = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS {$table} (
        id        BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        category  VARCHAR(100)        NOT NULL DEFAULT '',
        title     VARCHAR(255)        NOT NULL DEFAULT '',
        url       TEXT                NOT NULL,
        blurb     TEXT                NOT NULL,
        tags      VARCHAR(255)        NOT NULL DEFAULT '',
        PRIMARY KEY (id)
    ) {$charset};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}

function listlinks_get_all() {
    global $wpdb;
    $table = listlinks_table_name();
    return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY category ASC, title ASC" );
}

function listlinks_get_by_category( $category ) {
    global $wpdb;
    $table = listlinks_table_name();
    return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE category = %s ORDER BY title ASC", $category ) );
}

function listlinks_get_row( $id ) {
    global $wpdb;
    $table = listlinks_table_name();
    return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
}

function listlinks_insert( $data ) {
    global $wpdb;
    $wpdb->insert( listlinks_table_name(), $data, [ '%s', '%s', '%s', '%s', '%s' ] );
    return $wpdb->insert_id;
}

function listlinks_update( $id, $data ) {
    global $wpdb;
    $wpdb->update( listlinks_table_name(), $data, [ 'id' => $id ], [ '%s', '%s', '%s', '%s', '%s' ], [ '%d' ] );
}

function listlinks_delete( $id ) {
    global $wpdb;
    $wpdb->delete( listlinks_table_name(), [ 'id' => $id ], [ '%d' ] );
}
