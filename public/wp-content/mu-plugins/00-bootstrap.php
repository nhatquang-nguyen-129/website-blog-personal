<?php
/**
 * MU Plugins Bootstrap
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', function () {
    // Frontend only
    if (is_admin()) {
        return;
    }

$multilingual = __DIR__ . '/multilingual-post/multilingual-post.php';

if (file_exists($multilingual)) {
    require_once $multilingual;
}

});