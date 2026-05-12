<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_shortcode( 'lists_of_links', 'listlinks_shortcode' );

function listlinks_shortcode() {
    $links = listlinks_get_all();

    if ( empty( $links ) ) {
        return '';
    }

    $allowed_tags   = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];
    $allowed_styles = [ 'ul', 'ol', 'dl', 'p' ];

    $category_tag = get_option( 'listlinks_category_tag', 'h2' );
    $category_tag = in_array( $category_tag, $allowed_tags, true ) ? $category_tag : 'h2';

    $item_style = get_option( 'listlinks_item_style', 'ul' );
    $item_style = in_array( $item_style, $allowed_styles, true ) ? $item_style : 'ul';

    // Group by category (already sorted from DB query).
    $grouped = [];
    foreach ( $links as $link ) {
        $grouped[ $link->category ][] = $link;
    }
    ksort( $grouped );

    ob_start();

    foreach ( $grouped as $category => $items ) {
        // Sort items alphabetically within category.
        usort( $items, function( $a, $b ) {
            return strcmp( $a->title, $b->title );
        } );

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
