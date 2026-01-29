<?php
/**
 * Feature: Dark Mode
 * Status: Dormant until theme + feature flag enabled
 */

/**
 * Safety guards
 */
if (!defined('ABSPATH')) {
    return;
}

if (!function_exists('add_action')) {
    return;
}

/**
 * Disable feature unless explicitly enabled
 */
if (!defined('FEATURE_DARK_MODE') || FEATURE_DARK_MODE !== true) {
    return;
}

/**
 * Disable if no active theme
 */
if (!function_exists('wp_get_theme')) {
    return;
}

$theme = wp_get_theme();
if (!$theme || !$theme->exists()) {
    return;
}

/**
 * Register assets
 */
add_action('wp_enqueue_scripts', function () {

    $base_url = get_site_url() . '/features/dark-mode';

    wp_enqueue_style(
        'dark-mode-style',
        $base_url . '/dark-mode.css',
        [],
        '1.0.0'
    );

    wp_enqueue_script(
        'dark-mode-script',
        $base_url . '/dark-mode.js',
        [],
        '1.0.0',
        true
    );
});

/**
 * Inject toggle button
 */
add_action('wp_footer', function () {
    echo '<button id="dark-mode-toggle" aria-label="Toggle dark mode">🌙</button>';
});