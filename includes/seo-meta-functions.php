<?php

// Function to generate a dynamic meta description based on post content
function ast_generate_meta_description($post_id)
{
    $content = get_post_field('post_content', $post_id);
    $content = wp_strip_all_tags($content);
    $content = strip_shortcodes($content);
    $content = substr($content, 0, 155); // Limit to 155 characters
    return esc_attr($content);
}

// Function to dynamically add meta tags
function ast_add_dynamic_meta_tags()
{
    if (is_single() || is_home() || is_front_page()) {
        global $post;
        $description = ast_generate_meta_description($post->ID);
        $keywords = get_post_meta($post->ID, 'keywords', true) ?: 'default, keywords, here'; // Default keywords if none are set

        echo '<meta name="description" content="' . esc_attr($description) . '" />';
        echo '<meta name="keywords" content="' . esc_attr($keywords) . '" />';
    }
}
add_action('wp_head', 'ast_add_dynamic_meta_tags', 1);

// Function to add schema markup dynamically
function ast_add_schema_markup()
{
    if (is_single()) {
        $schema = [
            "@context" => "https://schema.org",
            "@type" => "BlogPosting",
            "mainEntityOfPage" => get_permalink(),
            "headline" => get_the_title(),
            "datePublished" => get_the_date('c'),
            "author" => [
                "@type" => "Person",
                "name" => get_the_author()
            ],
            "description" => ast_generate_meta_description(get_the_ID()) // Include dynamic description
        ];
        echo '<script type="application/ld+json">' . json_encode($schema) . '</script>';
    }
}
add_action('wp_head', 'ast_add_schema_markup');

// Function to add Open Graph tags for social media sharing
function ast_add_open_graph_tags()
{
    if (is_single() || is_home() || is_front_page()) {
        echo '<meta property="og:title" content="' . get_the_title() . '" />';
        echo '<meta property="og:description" content="' . ast_generate_meta_description(get_the_ID()) . '" />';
        echo '<meta property="og:url" content="' . get_permalink() . '" />';
        echo '<meta property="og:type" content="article" />';
    }
}
add_action('wp_head', 'ast_add_open_graph_tags');

// Function to add Twitter Card tags for Twitter sharing
function ast_add_twitter_card_tags()
{
    if (is_single() || is_home() || is_front_page()) {
        echo '<meta name="twitter:card" content="summary_large_image">';
        echo '<meta name="twitter:title" content="' . get_the_title() . '">';
        echo '<meta name="twitter:description" content="' . ast_generate_meta_description(get_the_ID()) . '">';
    }
}
add_action('wp_head', 'ast_add_twitter_card_tags');

// Function to create an SEO meta box for keyword entry
function ast_add_custom_meta_box()
{
    add_meta_box('ast_keywords_meta', 'SEO Keywords', 'ast_keywords_meta_box_callback', 'post', 'side');
}
add_action('add_meta_boxes', 'ast_add_custom_meta_box');

// Callback function for the SEO meta box
function ast_keywords_meta_box_callback($post)
{
    $value = get_post_meta($post->ID, 'keywords', true);
    echo '<label for="ast_keywords_field">Enter SEO Keywords: </label>';
    echo '<input type="text" id="ast_keywords_field" name="ast_keywords_field" value="' . esc_attr($value) . '" size="25" />';
}

// Save the custom meta field value
function ast_save_keywords_meta($post_id)
{
    if (array_key_exists('ast_keywords_field', $_POST)) {
        update_post_meta($post_id, 'keywords', sanitize_text_field($_POST['ast_keywords_field']));
    }
}
add_action('save_post', 'ast_save_keywords_meta');

// Function to generate a sitemap upon publishing a post
function ast_generate_sitemap()
{
    $posts = get_posts(['numberposts' => -1, 'post_type' => 'post', 'post_status' => 'publish']);
    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    foreach ($posts as $post) {
        $sitemap .= '<url>';
        $sitemap .= '<loc>' . get_permalink($post->ID) . '</loc>';
        $sitemap .= '<lastmod>' . get_the_modified_time('c', $post->ID) . '</lastmod>';
        $sitemap .= '<changefreq>weekly</changefreq>';
        $sitemap .= '<priority>0.8</priority>';
        $sitemap .= '</url>';
    }

    $sitemap .= '</urlset>';
    file_put_contents(ABSPATH . 'sitemap.xml', $sitemap);
}
add_action('publish_post', 'ast_generate_sitemap');

// Function to track post views
function ast_track_post_views()
{
    if (!is_single()) return;

    global $post;
    $post_id = $post->ID;

    $count_key = 'ast_post_views_count';
    $unique_count_key = 'ast_post_unique_views_count';

    // Retrieve the current count and unique count
    $count = get_post_meta($post_id, $count_key, true);
    $unique_count = get_post_meta($post_id, $unique_count_key, true);

    if ($count == '') {
        $count = 0;
        delete_post_meta($post_id, $count_key);
        add_post_meta($post_id, $count_key, '0');
    }

    if ($unique_count == '') {
        $unique_count = 0;
        delete_post_meta($post_id, $unique_count_key);
        add_post_meta($post_id, $unique_count_key, '0');
    }

    // Increment total views
    $count++;
    update_post_meta($post_id, $count_key, $count);

    // Check if the visitor's IP is already stored for this post
    $visitor_ip = $_SERVER['REMOTE_ADDR'];
    $stored_ips = get_post_meta($post_id, '_visitor_ips', true);

    if (empty($stored_ips)) {
        $stored_ips = [];
    }

    if (!in_array($visitor_ip, $stored_ips)) {
        // If the IP is not found, count it as a unique view
        $unique_count++;
        update_post_meta($post_id, $unique_count_key, $unique_count);

        // Add the IP to the list of stored IPs for this post
        $stored_ips[] = $visitor_ip;
        update_post_meta($post_id, '_visitor_ips', $stored_ips);
    }

    // Debugging line
    error_log("View count function executed for post ID: " . $post_id);
}
add_action('wp_head', 'ast_track_post_views');

// Function to retrieve post views
function ast_get_post_views($post_id)
{
    $count_key = 'ast_post_views_count';
    $count = get_post_meta($post_id, $count_key, true);

    if ($count == '') {
        return "0 Views";
    }

    return $count . ' Views';
}
