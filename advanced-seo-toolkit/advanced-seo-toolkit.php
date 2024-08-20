<?php
/*
Plugin Name: Advanced SEO Toolkit
Description: A comprehensive SEO toolkit for WordPress, including schema markup, keyword tracking, and more.
Version: 1.1
Author: J92
*/

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Include necessary files only after plugins have been loaded
function ast_load_plugin_files()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-seo-analytics-table.php';
    require_once plugin_dir_path(__FILE__) . 'includes/class-seo-analytics.php';
    require_once plugin_dir_path(__FILE__) . 'includes/seo-meta-functions.php';
}
add_action('plugins_loaded', 'ast_load_plugin_files');

// Initialize the SEO Analytics class
function ast_init_seo_analytics()
{
    $seo_analytics = new AST_SEO_Analytics(); // Create an instance of the class
}
add_action('plugins_loaded', 'ast_init_seo_analytics');
