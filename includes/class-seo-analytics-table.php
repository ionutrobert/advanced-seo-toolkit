<?php

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class AST_SEO_Analytics_Table extends WP_List_Table
{

    function __construct()
    {
        parent::__construct([
            'singular' => 'seo_analytic',
            'plural'   => 'seo_analytics',
            'ajax'     => false,
        ]);
    }

    function get_columns()
    {
        $columns = [
            'cb'             => '<input type="checkbox" />',
            'post_title'     => 'Post Title',
            'total_views'    => 'Total Views',
            'unique_views'   => 'Unique Views',
            'keywords'       => 'Keywords',
            'description'    => 'Description',
            'og_title'       => 'Open Graph Title',
            'twitter_title'  => 'Twitter Title',
        ];

        $screen_columns = get_user_option('manage' . $this->screen->id . 'columnshidden');
        if (!empty($screen_columns)) {
            foreach ($columns as $key => $value) {
                if (in_array($key, $screen_columns)) {
                    unset($columns[$key]);
                }
            }
        }

        return $columns;
    }

    function get_sortable_columns()
    {
        return [
            'post_title'    => ['post_title', true],
            'total_views'   => ['total_views', false],
            'unique_views'  => ['unique_views', false],
        ];
    }

    function prepare_items()
    {
        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        $args = [
            'posts_per_page' => $this->get_items_per_page('posts_per_page', 10),
            'post_type'      => 'post',
            'post_status'    => 'publish',
            's'              => !empty($_REQUEST['s']) ? $_REQUEST['s'] : '',
            'orderby'        => !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'title',
            'order'          => !empty($_REQUEST['order']) ? $_REQUEST['order'] : 'asc',
        ];

        $posts = get_posts($args);
        $this->items = $posts;
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'post_title':
                return '<a href="' . get_permalink($item->ID) . '" target="_blank">' . esc_html($item->post_title) . '</a>';
            case 'total_views':
                return esc_html(ast_get_post_views($item->ID));
            case 'unique_views':
                return esc_html(get_post_meta($item->ID, 'ast_post_unique_views_count', true));
            case 'keywords':
                return esc_html(get_post_meta($item->ID, 'keywords', true));
            case 'description':
                return esc_html(get_the_excerpt($item->ID));
            case 'og_title':
            case 'twitter_title':
                return esc_html(get_the_title($item->ID));
            default:
                return print_r($item, true); // For debugging purposes
        }
    }

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />',
            $item->ID
        );
    }

    function get_bulk_actions()
    {
        return [
            'bulk-delete' => 'Delete',
        ];
    }
}
