<?php
/**
 * Application Bootstrap (MU Plugin)
 */

defined('ABSPATH') || exit;

/**
 * 1. Không load feature khi WP chưa setup xong
 */
if (!function_exists('get_option')) {
    return;
}

if (!get_option('blogname')) {
    // WP chưa cài đặt
    return;
}

/**
 * 2. Chỉ load khi theme đã active
 */
$theme = wp_get_theme();
if (!$theme || !$theme->exists()) {
    return;
}

/**
 * 3. Define APP root (trỏ ra ngoài wp-content)
 */
define('APP_ROOT', dirname(__DIR__, 3)); 
// repo root

define('FEATURES_ROOT', APP_ROOT . '/features');

/**
 * 4. Load feature theo manifest
 */
$features = [
    'dark-mode/hooks.php',
];

foreach ($features as $feature) {
    $path = FEATURES_ROOT . '/' . $feature;
    if (file_exists($path)) {
        require_once $path;
    }
}