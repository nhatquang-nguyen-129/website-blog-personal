<?php
if (!defined('ABSPATH')) {
    exit;
}

define('MLFC_PATH', __DIR__);

add_action('init', 'mlfc_register_block');
function mlfc_register_block() {
    // Registered explicitly (not via block.json's bare "editorScript") for
    // the same reason as custom-table-of-contents: without a build tool
    // generating an editor.asset.php, WordPress would register the script
    // with an empty dependency array, and it would risk running before
    // wp.blocks/wp.element/etc. are guaranteed to exist.
    wp_register_script(
        'mlfc-featured-carousel-editor',
        content_url('mu-plugins/custom-featured-carousel/block/editor.js'),
        array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-api-fetch', 'wp-i18n'),
        filemtime(MLFC_PATH . '/block/editor.js'),
        true
    );

    register_block_type(MLFC_PATH . '/block', array(
        'editorScript' => 'mlfc-featured-carousel-editor',
    ));
}

add_action('enqueue_block_editor_assets', 'mlfc_enqueue_editor_style');
function mlfc_enqueue_editor_style() {
    // The block's InspectorControls panel renders in the outer editor
    // chrome (the sidebar), not inside the content iframe, so it can't rely
    // on the theme's add_editor_style() — that only reaches the iframe.
    wp_enqueue_style(
        'mlfc-editor-style',
        content_url('mu-plugins/custom-featured-carousel/block/editor.css'),
        array(),
        filemtime(MLFC_PATH . '/block/editor.css')
    );
}

add_action('wp_enqueue_scripts', 'mlfc_enqueue_frontend_assets');
function mlfc_enqueue_frontend_assets() {
    if (!has_block('minimal-reader/featured-carousel')) {
        return;
    }

    wp_enqueue_script(
        'mlfc-carousel',
        content_url('mu-plugins/custom-featured-carousel/assets/carousel.js'),
        array(),
        filemtime(MLFC_PATH . '/assets/carousel.js'),
        true
    );
}
