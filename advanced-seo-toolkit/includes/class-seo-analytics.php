<?php

if (!class_exists('AST_SEO_Analytics')) {
    class AST_SEO_Analytics
    {

        public function __construct()
        {
            add_action('admin_menu', [$this, 'ast_add_admin_menu']);
        }

        public function ast_add_admin_menu()
        {
            $hook = add_menu_page('SEO Analytics', 'SEO Analytics', 'manage_options', 'seo-analytics', [$this, 'ast_seo_analytics_page']);
            add_action("load-$hook", [$this, 'ast_add_screen_options']);
        }

        public function ast_add_screen_options()
        {
            $screen = get_current_screen();
            if (isset($screen->id) && $screen->id === 'toplevel_page_seo-analytics') {
                add_screen_option('per_page', [
                    'label'   => 'Posts per page',
                    'default' => 10,
                    'option'  => 'posts_per_page',
                ]);

                // Add column visibility screen options
                add_filter('manage_' . $screen->id . '_columns', [$this, 'get_columns']);
            }
        }

        public function ast_seo_analytics_page()
        {
            $seo_analytics_table = new AST_SEO_Analytics_Table();
            $seo_analytics_table->prepare_items();
?>
            <div class="wrap">
                <h1>SEO Analytics</h1>
                <form method="get">
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
                    <?php
                    $seo_analytics_table->search_box('Search Posts', 'search_id');
                    $seo_analytics_table->display();
                    ?>
                </form>
            </div>
<?php
        }

        public function get_columns()
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
            return $columns;
        }
    }
}
