<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_shortcode( 'lists_of_links',       'listlinks_shortcode' );
add_shortcode( 'lists_of_links_table', 'listlinks_table_shortcode' );
add_shortcode( 'lists_of_links_grid',  'listlinks_grid_shortcode' );

/* ------------------------------------------------------------------ */
/* Shared helper: fetch and group links, optionally filtered           */
/* ------------------------------------------------------------------ */

function listlinks_get_grouped( $category_filter = '' ) {
    $links = $category_filter
        ? listlinks_get_by_category( $category_filter )
        : listlinks_get_all();

    if ( empty( $links ) ) {
        return [];
    }

    $grouped = [];
    foreach ( $links as $link ) {
        $grouped[ $link->category ][] = $link;
    }
    ksort( $grouped );

    foreach ( $grouped as $category => &$items ) {
        usort( $items, function( $a, $b ) {
            return strcmp( $a->title, $b->title );
        } );
    }
    unset( $items );

    return $grouped;
}

/* ------------------------------------------------------------------ */
/* [lists_of_links] — list/paragraph output                           */
/* Supports: category="Health"                                        */
/* ------------------------------------------------------------------ */

function listlinks_shortcode( $atts ) {
    $atts = shortcode_atts( [ 'category' => '' ], $atts, 'lists_of_links' );
    $category_filter = sanitize_text_field( $atts['category'] );

    $grouped = listlinks_get_grouped( $category_filter );

    if ( empty( $grouped ) ) {
        return '';
    }

    $allowed_tags   = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];
    $allowed_styles = [ 'ul', 'ol', 'dl', 'p' ];

    $category_tag = get_option( 'listlinks_category_tag', 'h2' );
    $category_tag = in_array( $category_tag, $allowed_tags, true ) ? $category_tag : 'h2';

    $item_style = get_option( 'listlinks_item_style', 'ul' );
    $item_style = in_array( $item_style, $allowed_styles, true ) ? $item_style : 'ul';

    ob_start();

    foreach ( $grouped as $category => $items ) {
        echo '<' . $category_tag . '>' . esc_html( $category ) . '</' . $category_tag . '>' . "\n";

        if ( 'dl' === $item_style ) {
            echo "<dl>\n";
            foreach ( $items as $item ) {
                echo '<dt><a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a></dt>' . "\n";
                if ( $item->blurb ) {
                    echo '<dd>' . esc_html( $item->blurb ) . '</dd>' . "\n";
                }
            }
            echo "</dl>\n";
        } elseif ( 'p' === $item_style ) {
            foreach ( $items as $item ) {
                $text = '<a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a>';
                if ( $item->blurb ) {
                    $text .= ' ' . esc_html( $item->blurb );
                }
                echo '<p>' . $text . '</p>' . "\n";
            }
        } else {
            echo '<' . $item_style . ">\n";
            foreach ( $items as $item ) {
                $text = '<a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a>';
                if ( $item->blurb ) {
                    $text .= ' ' . esc_html( $item->blurb );
                }
                echo '<li>' . $text . '</li>' . "\n";
            }
            echo '</' . $item_style . ">\n";
        }
    }

    return ob_get_clean();
}

/* ------------------------------------------------------------------ */
/* [lists_of_links_table] — table output                              */
/* Supports: category="Health"                                        */
/* ------------------------------------------------------------------ */

function listlinks_table_shortcode( $atts ) {
    $atts = shortcode_atts( [ 'category' => '' ], $atts, 'lists_of_links_table' );
    $category_filter = sanitize_text_field( $atts['category'] );

    $grouped = listlinks_get_grouped( $category_filter );

    if ( empty( $grouped ) ) {
        return '';
    }

    ob_start();

    echo "<table>\n";
    echo "<thead>\n<tr>";
    echo '<th>' . esc_html__( 'Category', 'lists-of-links' ) . '</th>';
    echo '<th>' . esc_html__( 'Title', 'lists-of-links' ) . '</th>';
    echo '<th>' . esc_html__( 'Description', 'lists-of-links' ) . '</th>';
    echo "</tr>\n</thead>\n<tbody>\n";

    foreach ( $grouped as $category => $items ) {
        foreach ( $items as $item ) {
            echo '<tr>';
            echo '<td>' . esc_html( $item->category ) . '</td>';
            echo '<td><a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a></td>';
            echo '<td>' . esc_html( $item->blurb ) . '</td>';
            echo "</tr>\n";
        }
    }

    echo "</tbody>\n</table>\n";

    return ob_get_clean();
}

/* ------------------------------------------------------------------ */
/* [lists_of_links_grid] — bordered table output                      */
/* Supports: category="Health"                                        */
/* ------------------------------------------------------------------ */

function listlinks_grid_shortcode( $atts ) {
    $atts = shortcode_atts( [ 'category' => '' ], $atts, 'lists_of_links_grid' );
    $category_filter = sanitize_text_field( $atts['category'] );

    $grouped = listlinks_get_grouped( $category_filter );

    if ( empty( $grouped ) ) {
        return '';
    }

    $table_style = 'border-collapse: collapse; border: 1px solid;';
    $th_style    = 'border: 1px solid; border-bottom: 2px solid; font-weight: bold; padding: 4px 8px;';
    $td_style    = 'border: 1px solid; padding: 4px 8px;';

    ob_start();

    echo '<table style="' . $table_style . '">' . "\n";
    echo '<thead>' . "\n" . '<tr>';
    echo '<th style="' . $th_style . '">' . esc_html__( 'Category',    'lists-of-links' ) . '</th>';
    echo '<th style="' . $th_style . '">' . esc_html__( 'Title',       'lists-of-links' ) . '</th>';
    echo '<th style="' . $th_style . '">' . esc_html__( 'Description', 'lists-of-links' ) . '</th>';
    echo '</tr>' . "\n" . '</thead>' . "\n" . '<tbody>' . "\n";

    foreach ( $grouped as $category => $items ) {
        foreach ( $items as $item ) {
            echo '<tr>';
            echo '<td style="' . $td_style . '">' . esc_html( $item->category ) . '</td>';
            echo '<td style="' . $td_style . '"><a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a></td>';
            echo '<td style="' . $td_style . '">' . esc_html( $item->blurb ) . '</td>';
            echo '</tr>' . "\n";
        }
    }

    echo '</tbody>' . "\n" . '</table>' . "\n";

    return ob_get_clean();
}
