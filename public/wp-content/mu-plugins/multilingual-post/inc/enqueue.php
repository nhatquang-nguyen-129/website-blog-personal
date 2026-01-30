<?php
defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', function () {
    if (!is_singular()) return;

    wp_enqueue_script(
        'ml-post-toggle',
        ML_POST_URL . '/assets/js/toggle.js',
        [],
        '1.0',
        true
    );

    wp_enqueue_style(
        'ml-post-toggle',
        ML_POST_URL . '/assets/css/toggle.css',
        [],
        '1.0'
    );
});