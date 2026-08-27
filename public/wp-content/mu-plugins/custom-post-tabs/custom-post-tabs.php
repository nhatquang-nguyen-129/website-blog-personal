<?php
if (!defined('ABSPATH')) {
    exit;
}

define('MLPT_PATH', __DIR__);

add_action('after_setup_theme', 'mlpt_register_image_size');
function mlpt_register_image_size() {
    // Hard-cropped so every row's thumbnail is a consistent shape regardless
    // of each post's own featured-image aspect ratio — same reasoning as
    // custom-featured-carousel's own image size.
    add_image_size('mlpt-thumb', 320, 200, true);
}

add_action('init', 'mlpt_register_block');
function mlpt_register_block() {
    // Registered explicitly (not via block.json's bare "editorScript") for
    // the same reason as this project's other blocks: without a build tool
    // generating an editor.asset.php, WordPress would register the script
    // with an empty dependency array, and it would risk running before
    // wp.blocks/wp.element/etc. are guaranteed to exist.
    wp_register_script(
        'mlpt-post-tabs-editor',
        content_url('mu-plugins/custom-post-tabs/block/editor.js'),
        array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n'),
        filemtime(MLPT_PATH . '/block/editor.js'),
        true
    );

    register_block_type(MLPT_PATH . '/block', array(
        'editorScript' => 'mlpt-post-tabs-editor',
    ));
}

add_action('wp_enqueue_scripts', 'mlpt_enqueue_frontend_assets');
function mlpt_enqueue_frontend_assets() {
    if (!has_block('minimal-reader/post-tabs')) {
        return;
    }

    wp_enqueue_script(
        'mlpt-post-tabs',
        content_url('mu-plugins/custom-post-tabs/assets/tabs.js'),
        array(),
        filemtime(MLPT_PATH . '/assets/tabs.js'),
        true
    );
}
