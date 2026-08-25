<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('after_setup_theme', 'mlr_theme_setup');
function mlr_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
    add_theme_support('align-wide');
    add_theme_support('custom-logo', array(
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    register_nav_menus(array(
        'primary' => __('Primary Menu', 'minimal-reader'),
        'footer'  => __('Footer Menu', 'minimal-reader'),
    ));
}

add_action('wp_enqueue_scripts', 'mlr_enqueue_assets');
function mlr_enqueue_assets() {
    wp_enqueue_style(
        'mlr-google-fonts',
        'https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400&display=swap',
        array(),
        null
    );

    wp_enqueue_style(
        'mlr-style',
        get_template_directory_uri() . '/assets/css/style.css',
        array(),
        wp_get_theme()->get('Version')
    );

    wp_enqueue_script(
        'mlr-theme-toggle',
        get_template_directory_uri() . '/assets/js/theme-toggle.js',
        array(),
        wp_get_theme()->get('Version'),
        true
    );
}

/**
 * Trim the excerpt used on the post list without WordPress's default "[...]".
 */
add_filter('excerpt_more', function () {
    return '…';
});

add_filter('excerpt_length', function () {
    return 32;
});
