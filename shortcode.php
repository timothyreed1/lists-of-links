<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_shortcode( 'lists_of_links',       'listlinks_shortcode' );
add_shortcode( 'lists_of_links_table', 'listlinks_table_shortcode' );
add_shortcode( 'lists_of_links_grid',  'listlinks_grid_shortcode' );

/* ------------------------------------------------------------------ */
/* Shared helper: parse and validate the columns attribute             */
/* Returns ordered list of valid columns and a show[] boolean map      */
/* ------------------------------------------------------------------ */

function listlinks_parse_columns( $columns_attr ) {
    $valid = [ 'category', 'title', 'description' ];
    $cols  = array_values( array_filter(
        array_map( 'trim', explode( ',', strtolower( $columns_attr ) ) ),
        function( $c ) use ( $valid ) { return in_array( $c, $valid, true ); }
    ) );

    return [
        'ordered' => $cols,
        'show'    => [
            'category'    => in_array( 'category',    $cols, true ),
            'title'       => in_array( 'title',       $cols, true ),
            'description' => in_array( 'description', $cols, true ),
        ],
    ];
}

/* ------------------------------------------------------------------ */
/* Shared helper: check whether a link matches any of the filter tags  */
/* Whole-word, case-insensitive match against comma-separated tag list */
/* ------------------------------------------------------------------ */

function listlinks_link_matches_tags( $link, $filter_tags ) {
    $link_tags = array_map( 'trim', explode( ',', strtolower( $link->tags ) ) );
    foreach ( $filter_tags as $filter_tag ) {
        if ( in_array( trim( strtolower( $filter_tag ) ), $link_tags, true ) ) {
            return true;
        }
    }
    return false;
}

/* ------------------------------------------------------------------ */
/* Shared helper: fetch and sort links by column order                 */
/* ------------------------------------------------------------------ */

function listlinks_get_sorted( $category_filter, $ordered_cols, $tag_filter = '' ) {
    $links = $category_filter
        ? listlinks_get_by_category( $category_filter )
        : listlinks_get_all();

    // Filter by tag(s) when requested (OR matching).
    if ( $tag_filter !== '' ) {
        $filter_tags = array_filter( array_map( 'trim', explode( ',', $tag_filter ) ) );
        if ( ! empty( $filter_tags ) ) {
            $links = array_values( array_filter( $links, function( $link ) use ( $filter_tags ) {
                return listlinks_link_matches_tags( $link, $filter_tags );
            } ) );
        }
    }

    if ( empty( $links ) ) {
        return [];
    }

    // Map column names to object property names.
    $prop_map = [
        'category'    => 'category',
        'title'       => 'title',
        'description' => 'blurb',
    ];

    $sort_props = array_values( array_filter( array_map(
        function( $c ) use ( $prop_map ) { return $prop_map[ $c ] ?? null; },
        $ordered_cols
    ) ) );

    usort( $links, function( $a, $b ) use ( $sort_props ) {
        foreach ( $sort_props as $prop ) {
            $cmp = strcmp( $a->$prop, $b->$prop );
            if ( 0 !== $cmp ) {
                return $cmp;
            }
        }
        return 0;
    } );

    return $links;
}

/* ------------------------------------------------------------------ */
/* [lists_of_links] — list/paragraph output                           */
/* Supports: category="Health" columns="category,title,description"   */
/* When category is the leftmost column, links are grouped under       */
/* category headings. Otherwise output is a flat sorted list.         */
/* ------------------------------------------------------------------ */

function listlinks_shortcode( $atts ) {
    $atts = shortcode_atts(
        [ 'category' => '', 'columns' => 'category,title,description', 'tag' => '' ],
        $atts,
        'lists_of_links'
    );

    $parsed          = listlinks_parse_columns( $atts['columns'] );
    $show            = $parsed['show'];
    $ordered         = $parsed['ordered'];
    $category_filter = sanitize_text_field( $atts['category'] );
    $tag_filter      = sanitize_text_field( $atts['tag'] );
    $links           = listlinks_get_sorted( $category_filter, $ordered, $tag_filter );

    if ( empty( $links ) ) {
        return '';
    }

    $allowed_tags   = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];
    $allowed_styles = [ 'ul', 'ol', 'dl', 'p' ];

    $category_tag = get_option( 'listlinks_category_tag', 'h2' );
    $category_tag = in_array( $category_tag, $allowed_tags, true ) ? $category_tag : 'h2';

    $item_style = get_option( 'listlinks_item_style', 'ul' );
    $item_style = in_array( $item_style, $allowed_styles, true ) ? $item_style : 'ul';

    // Group by category only when category is the leftmost column.
    $group_by_category = ( $show['category'] && isset( $ordered[0] ) && 'category' === $ordered[0] );

    // Build groups: either real groups or one flat group with key ''.
    if ( $group_by_category ) {
        $groups = [];
        foreach ( $links as $link ) {
            $groups[ $link->category ][] = $link;
        }
    } else {
        $groups = [ '' => $links ];
    }

    ob_start();

    foreach ( $groups as $category => $items ) {

        if ( $group_by_category ) {
            echo '<' . $category_tag . '>' . esc_html( $category ) . '</' . $category_tag . '>' . "\n";
        }

        if ( 'dl' === $item_style ) {
            echo "<dl>\n";
            foreach ( $items as $item ) {
                if ( $show['title'] ) {
                    echo '<dt><a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a></dt>' . "\n";
                }
                if ( $show['description'] && $item->blurb ) {
                    echo '<dd>' . esc_html( $item->blurb ) . '</dd>' . "\n";
                }
            }
            echo "</dl>\n";
        } elseif ( 'p' === $item_style ) {
            foreach ( $items as $item ) {
                $parts = [];
                if ( $show['title'] ) {
                    $parts[] = '<a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a>';
                }
                if ( $show['description'] && $item->blurb ) {
                    $parts[] = esc_html( $item->blurb );
                }
                if ( $parts ) {
                    echo '<p>' . implode( ' ', $parts ) . '</p>' . "\n";
                }
            }
        } else {
            echo '<' . $item_style . ">\n";
            foreach ( $items as $item ) {
                $parts = [];
                if ( $show['title'] ) {
                    $parts[] = '<a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a>';
                }
                if ( $show['description'] && $item->blurb ) {
                    $parts[] = esc_html( $item->blurb );
                }
                if ( $parts ) {
                    echo '<li>' . implode( ' ', $parts ) . '</li>' . "\n";
                }
            }
            echo '</' . $item_style . ">\n";
        }
    }

    return ob_get_clean();
}

/* ------------------------------------------------------------------ */
/* Shared table renderer for table and grid shortcodes                 */
/* ------------------------------------------------------------------ */

function listlinks_render_table( $links, $show, $ordered, $table_style, $th_style, $td_style ) {
    ob_start();

    echo '<table' . ( $table_style ? ' style="' . $table_style . '"' : '' ) . ">\n";
    echo "<thead>\n<tr>";

    foreach ( $ordered as $col ) {
        if ( 'category' === $col ) {
            echo '<th style="' . $th_style . '">' . esc_html__( 'Category',    'lists-of-links' ) . '</th>';
        } elseif ( 'title' === $col ) {
            echo '<th style="' . $th_style . '">' . esc_html__( 'Title',       'lists-of-links' ) . '</th>';
        } elseif ( 'description' === $col ) {
            echo '<th style="' . $th_style . '">' . esc_html__( 'Description', 'lists-of-links' ) . '</th>';
        }
    }

    echo "</tr>\n</thead>\n<tbody>\n";

    foreach ( $links as $item ) {
        echo '<tr>';
        foreach ( $ordered as $col ) {
            if ( 'category' === $col ) {
                echo '<td style="' . $td_style . '">' . esc_html( $item->category ) . '</td>';
            } elseif ( 'title' === $col ) {
                echo '<td style="' . $td_style . '"><a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a></td>';
            } elseif ( 'description' === $col ) {
                echo '<td style="' . $td_style . '">' . esc_html( $item->blurb ) . '</td>';
            }
        }
        echo "</tr>\n";
    }

    echo "</tbody>\n</table>\n";

    return ob_get_clean();
}

/* ------------------------------------------------------------------ */
/* [lists_of_links_table] — plain table output                        */
/* Supports: category="Health" columns="category,title,description"   */
/* ------------------------------------------------------------------ */

function listlinks_table_shortcode( $atts ) {
    $atts = shortcode_atts(
        [ 'category' => '', 'columns' => 'category,title,description', 'tag' => '' ],
        $atts,
        'lists_of_links_table'
    );

    $parsed  = listlinks_parse_columns( $atts['columns'] );
    $links   = listlinks_get_sorted( sanitize_text_field( $atts['category'] ), $parsed['ordered'], sanitize_text_field( $atts['tag'] ) );

    if ( empty( $links ) ) {
        return '';
    }

    return listlinks_render_table( $links, $parsed['show'], $parsed['ordered'], '', '', '' );
}

/* ------------------------------------------------------------------ */
/* [lists_of_links_grid] — bordered table output                      */
/* Supports: category="Health" columns="category,title,description"   */
/* ------------------------------------------------------------------ */

function listlinks_grid_shortcode( $atts ) {
    $atts = shortcode_atts(
        [ 'category' => '', 'columns' => 'category,title,description', 'tag' => '' ],
        $atts,
        'lists_of_links_grid'
    );

    $parsed      = listlinks_parse_columns( $atts['columns'] );
    $links       = listlinks_get_sorted( sanitize_text_field( $atts['category'] ), $parsed['ordered'], sanitize_text_field( $atts['tag'] ) );

    if ( empty( $links ) ) {
        return '';
    }

    $table_style = 'border-collapse: collapse; border: 1px solid;';
    $th_style    = 'border: 1px solid; border-bottom: 2px solid; font-weight: bold; padding: 4px 8px;';
    $td_style    = 'border: 1px solid; padding: 4px 8px;';

    return listlinks_render_table( $links, $parsed['show'], $parsed['ordered'], $table_style, $th_style, $td_style );
}
