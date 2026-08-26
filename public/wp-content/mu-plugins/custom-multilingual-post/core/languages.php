<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Central place to add/remove supported languages. Filterable so a future
 * feature/theme can extend the list without editing this file.
 */
function mlp_available_langs() {
    return apply_filters('mlp_available_langs', array(
        'vi' => 'Tiếng Việt',
        'en' => 'English',
    ));
}

function mlp_lang_label($code) {
    $langs = mlp_available_langs();
    return isset($langs[$code]) ? $langs[$code] : strtoupper($code);
}

function mlp_default_lang() {
    return apply_filters('mlp_default_lang', 'vi');
}
