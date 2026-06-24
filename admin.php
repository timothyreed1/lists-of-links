<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'listlinks_admin_menu' );
add_action( 'admin_init', 'listlinks_handle_edit_post' );

function listlinks_handle_edit_post() {
    if ( ! isset( $_POST['listlinks_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['listlinks_nonce'], 'listlinks_save' ) ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $id   = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
    $data = [
        'category' => sanitize_text_field( wp_unslash( $_POST['category'] ) ),
        'title'    => sanitize_text_field( wp_unslash( $_POST['title'] ) ),
        'url'      => esc_url_raw( wp_unslash( $_POST['url'] ) ),
        'blurb'    => sanitize_text_field( wp_unslash( $_POST['blurb'] ) ),
        'tags'     => sanitize_text_field( wp_unslash( $_POST['tags'] ?? '' ) ),
    ];

    if ( $id ) {
        listlinks_update( $id, $data );
    } else {
        $id = listlinks_insert( $data );
    }

    $save_action = isset( $_POST['save_action'] ) ? $_POST['save_action'] : 'save';
    $redirect_id = $id;

    if ( in_array( $save_action, [ 'prev', 'next' ], true ) ) {
        $all = listlinks_get_all();
        $ids = array_column( (array) $all, 'id' );
        $pos = array_search( $id, $ids );
        if ( $pos !== false ) {
            if ( 'prev' === $save_action && $pos > 0 ) {
                $redirect_id = $ids[ $pos - 1 ];
            } elseif ( 'next' === $save_action && $pos < count( $ids ) - 1 ) {
                $redirect_id = $ids[ $pos + 1 ];
            }
        }
    }

    $url = admin_url( 'admin.php?page=lists-of-links-edit&id=' . $redirect_id );
    if ( $redirect_id === $id ) {
        $url = add_query_arg( 'saved', '1', $url );
    }
    wp_safe_redirect( $url );
    exit;
}

function listlinks_admin_menu() {
    add_menu_page(
        __( 'Lists of Links', 'lists-of-links' ),
        __( 'Lists of Links', 'lists-of-links' ),
        'manage_options',
        'lists-of-links',
        'listlinks_page_list',
        'dashicons-admin-links',
        80
    );
    add_submenu_page(
        'lists-of-links',
        __( 'All Links', 'lists-of-links' ),
        __( 'All Links', 'lists-of-links' ),
        'manage_options',
        'lists-of-links',
        'listlinks_page_list'
    );
    add_submenu_page(
        'lists-of-links',
        __( 'Add / Edit Link', 'lists-of-links' ),
        __( 'Add New', 'lists-of-links' ),
        'manage_options',
        'lists-of-links-edit',
        'listlinks_page_edit'
    );
    add_submenu_page(
        'lists-of-links',
        __( 'Settings', 'lists-of-links' ),
        __( 'Settings', 'lists-of-links' ),
        'manage_options',
        'lists-of-links-settings',
        'listlinks_page_settings'
    );
}

/* ------------------------------------------------------------------ */
/* List page                                                          */
/* ------------------------------------------------------------------ */

function listlinks_page_list() {
    // Handle delete action.
    if (
        isset( $_GET['action'], $_GET['id'] ) &&
        'delete' === $_GET['action'] &&
        current_user_can( 'manage_options' ) &&
        check_admin_referer( 'listlinks_delete_' . absint( $_GET['id'] ) )
    ) {
        listlinks_delete( absint( $_GET['id'] ) );
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Link deleted.', 'lists-of-links' ) . '</p></div>';
    }

    $links = listlinks_get_all();
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php esc_html_e( 'Lists of Links', 'lists-of-links' ); ?></h1>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=lists-of-links-edit' ) ); ?>" class="page-title-action">
            <?php esc_html_e( 'Add New', 'lists-of-links' ); ?>
        </a>
        <hr class="wp-header-end">

        <style>
            .listlinks-table-wrap { overflow-x: auto; width: 100%; }
            .listlinks-table { table-layout: fixed; min-width: 860px; width: 100%; }
            .listlinks-table .col-actions { width: 140px; white-space: nowrap; }
            .listlinks-table .col-category { width: 120px; }
            .listlinks-table .col-title { width: 160px; }
            .listlinks-table .col-blurb { width: 200px; }
            .listlinks-table .col-tags { width: 140px; }
            .listlinks-table .col-url { min-width: 160px; }
            .listlinks-table td { word-break: break-all; vertical-align: top; }
        </style>
        <div class="listlinks-table-wrap">
        <table class="widefat striped listlinks-table">
            <colgroup>
                <col class="col-actions">
                <col class="col-category">
                <col class="col-title">
                <col class="col-blurb">
                <col class="col-tags">
                <col class="col-url">
            </colgroup>
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Actions', 'lists-of-links' ); ?></th>
                    <th><?php esc_html_e( 'Category', 'lists-of-links' ); ?></th>
                    <th><?php esc_html_e( 'Title', 'lists-of-links' ); ?></th>
                    <th><?php esc_html_e( 'Blurb', 'lists-of-links' ); ?></th>
                    <th><?php esc_html_e( 'Tags', 'lists-of-links' ); ?></th>
                    <th><?php esc_html_e( 'URL', 'lists-of-links' ); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ( $links ) : ?>
                <?php foreach ( $links as $link ) : ?>
                <tr>
                    <td>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=lists-of-links-edit&id=' . $link->id ) ); ?>">
                            <?php esc_html_e( 'Edit', 'lists-of-links' ); ?>
                        </a>
                        &nbsp;|&nbsp;
                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=lists-of-links&action=delete&id=' . $link->id ), 'listlinks_delete_' . $link->id ) ); ?>"
                           onclick="return confirm('<?php esc_attr_e( 'Delete this link?', 'lists-of-links' ); ?>');"
                           style="color:#b32d2e;">
                            <?php esc_html_e( 'Delete', 'lists-of-links' ); ?>
                        </a>
                    </td>
                    <td><?php echo esc_html( $link->category ); ?></td>
                    <td><?php echo esc_html( $link->title ); ?></td>
                    <td><?php echo esc_html( $link->blurb ); ?></td>
                    <td><?php echo esc_html( $link->tags ); ?></td>
                    <td><a href="<?php echo esc_url( $link->url ); ?>" target="_blank"><?php echo esc_html( $link->url ); ?></a></td>
                </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="6"><?php esc_html_e( 'No links found. Add one above.', 'lists-of-links' ); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php
}

/* ------------------------------------------------------------------ */
/* Add / Edit page                                                    */
/* ------------------------------------------------------------------ */

function listlinks_page_edit() {
    $id    = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
    $link  = $id ? listlinks_get_row( $id ) : null;
    $saved = isset( $_GET['saved'] ) && '1' === $_GET['saved'];

    $category = $link ? $link->category : '';
    $title    = $link ? $link->title    : '';
    $url      = $link ? $link->url      : '';
    $blurb    = $link ? $link->blurb    : '';
    $tags     = $link ? $link->tags     : '';

    // Find prev/next links in the same sorted order as the list page.
    $prev_id = null;
    $next_id = null;
    if ( $id ) {
        $all  = listlinks_get_all();
        $ids  = array_column( (array) $all, 'id' );
        $pos  = array_search( $id, $ids );
        if ( $pos !== false ) {
            $prev_id = $pos > 0                  ? $ids[ $pos - 1 ] : null;
            $next_id = $pos < count( $ids ) - 1  ? $ids[ $pos + 1 ] : null;
        }
    }

    $edit_base = admin_url( 'admin.php?page=lists-of-links-edit&id=' );
    ?>
    <div class="wrap">
        <h1><?php echo $id ? esc_html__( 'Edit Link', 'lists-of-links' ) : esc_html__( 'Add New Link', 'lists-of-links' ); ?></h1>

        <?php if ( $saved ) : ?>
            <div class="notice notice-success"><p><?php esc_html_e( 'Link saved.', 'lists-of-links' ); ?></p></div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field( 'listlinks_save', 'listlinks_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="category"><?php esc_html_e( 'Category', 'lists-of-links' ); ?></label></th>
                    <td><input type="text" id="category" name="category" value="<?php echo esc_attr( $category ); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="title"><?php esc_html_e( 'Title', 'lists-of-links' ); ?></label></th>
                    <td><input type="text" id="title" name="title" value="<?php echo esc_attr( $title ); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="url"><?php esc_html_e( 'URL', 'lists-of-links' ); ?></label></th>
                    <td><input type="url" id="url" name="url" value="<?php echo esc_attr( $url ); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="blurb"><?php esc_html_e( 'Blurb', 'lists-of-links' ); ?></label></th>
                    <td><input type="text" id="blurb" name="blurb" value="<?php echo esc_attr( $blurb ); ?>" class="large-text" required></td>
                </tr>
                <tr>
                    <th><label for="tags"><?php esc_html_e( 'Tags', 'lists-of-links' ); ?></label></th>
                    <td>
                        <input type="text" id="tags" name="tags" value="<?php echo esc_attr( $tags ); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e( 'Comma-separated. Example: health, fitness, diet', 'lists-of-links' ); ?></p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <?php if ( $prev_id ) : ?>
                    <button type="submit" name="save_action" value="prev" class="button">&larr; <?php esc_html_e( 'Save & Previous', 'lists-of-links' ); ?></button>
                <?php else : ?>
                    <button type="submit" class="button" disabled>&larr; <?php esc_html_e( 'Save & Previous', 'lists-of-links' ); ?></button>
                <?php endif; ?>

                <button type="submit" name="save_action" value="save" class="button button-primary"><?php esc_html_e( 'Save Link', 'lists-of-links' ); ?></button>

                <?php if ( $next_id ) : ?>
                    <button type="submit" name="save_action" value="next" class="button"><?php esc_html_e( 'Save & Next', 'lists-of-links' ); ?> &rarr;</button>
                <?php else : ?>
                    <button type="submit" class="button" disabled><?php esc_html_e( 'Save & Next', 'lists-of-links' ); ?> &rarr;</button>
                <?php endif; ?>
            </p>
        </form>

        <p>
            <?php if ( $prev_id ) : ?>
                <a href="<?php echo esc_url( $edit_base . $prev_id ); ?>" class="button">&larr; <?php esc_html_e( 'Previous', 'lists-of-links' ); ?></a>
            <?php else : ?>
                <button class="button" disabled>&larr; <?php esc_html_e( 'Previous', 'lists-of-links' ); ?></button>
            <?php endif; ?>

            <?php if ( $next_id ) : ?>
                <a href="<?php echo esc_url( $edit_base . $next_id ); ?>" class="button"><?php esc_html_e( 'Next', 'lists-of-links' ); ?> &rarr;</a>
            <?php else : ?>
                <button class="button" disabled><?php esc_html_e( 'Next', 'lists-of-links' ); ?> &rarr;</button>
            <?php endif; ?>

            &nbsp;&nbsp;<a href="<?php echo esc_url( admin_url( 'admin.php?page=lists-of-links' ) ); ?>"><?php esc_html_e( 'Back to all links', 'lists-of-links' ); ?></a>
        </p>
    </div>
    <?php
}

/* ------------------------------------------------------------------ */
/* Settings page                                                      */
/* ------------------------------------------------------------------ */

function listlinks_page_settings() {
    if (
        isset( $_POST['listlinks_settings_nonce'] ) &&
        wp_verify_nonce( $_POST['listlinks_settings_nonce'], 'listlinks_settings_save' ) &&
        current_user_can( 'manage_options' )
    ) {
        update_option( 'listlinks_category_tag', sanitize_text_field( $_POST['category_tag'] ) );
        update_option( 'listlinks_item_style',   sanitize_text_field( $_POST['item_style'] ) );
        $raw_paths = isset( $_POST['noindex_paths'] ) ? wp_unslash( $_POST['noindex_paths'] ) : '';
        update_option( 'listlinks_noindex_paths', sanitize_textarea_field( $raw_paths ) );
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved.', 'lists-of-links' ) . '</p></div>';
    }

    $category_tag = get_option( 'listlinks_category_tag', 'h2' );
    $item_style   = get_option( 'listlinks_item_style',   'ul' );
    $noindex_paths = get_option( 'listlinks_noindex_paths', '' );

    $heading_options = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];
    $list_options    = [
        'ul' => __( 'Bulleted list (ul)', 'lists-of-links' ),
        'ol' => __( 'Numbered list (ol)', 'lists-of-links' ),
        'dl' => __( 'Definition list (dl)', 'lists-of-links' ),
        'p'  => __( 'Paragraphs (p)', 'lists-of-links' ),
    ];
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Lists of Links Settings', 'lists-of-links' ); ?></h1>
        <form method="post">
            <?php wp_nonce_field( 'listlinks_settings_save', 'listlinks_settings_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="category_tag"><?php esc_html_e( 'Category heading tag', 'lists-of-links' ); ?></label></th>
                    <td>
                        <select id="category_tag" name="category_tag">
                            <?php foreach ( $heading_options as $tag ) : ?>
                                <option value="<?php echo esc_attr( $tag ); ?>" <?php selected( $category_tag, $tag ); ?>>
                                    <?php echo esc_html( strtoupper( $tag ) ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'Default: H2', 'lists-of-links' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="item_style"><?php esc_html_e( 'Item list style', 'lists-of-links' ); ?></label></th>
                    <td>
                        <select id="item_style" name="item_style">
                            <?php foreach ( $list_options as $val => $label ) : ?>
                                <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $item_style, $val ); ?>>
                                    <?php echo esc_html( $label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'Default: Bulleted list', 'lists-of-links' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="noindex_paths"><?php esc_html_e( 'Noindex paths', 'lists-of-links' ); ?></label></th>
                    <td>
                        <textarea id="noindex_paths" name="noindex_paths" rows="4" class="large-text"><?php echo esc_textarea( $noindex_paths ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'One path per line. Pages whose URL starts with any of these paths will get a noindex,nofollow meta tag. Example: /links', 'lists-of-links' ); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button( __( 'Save Settings', 'lists-of-links' ) ); ?>
        </form>
    </div>
    <?php
}
