<?php
defined('ABSPATH') || exit;

/**
 * Check if post has multilingual enabled
 */
function ml_post_is_enabled($post_id) {
    return (bool) get_post_meta($post_id, '_ml_languages', true);
}

/**
 * Get declared languages
 * Example value: ['vi', 'en']
 */
function ml_post_get_languages($post_id) {
    $langs = get_post_meta($post_id, '_ml_languages', true);
    return is_array($langs) ? $langs : [];
}