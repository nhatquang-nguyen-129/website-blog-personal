<?php
if (!defined('ABSPATH')) {
    exit;
}

define('MLFC_PATH', __DIR__);

add_action('after_setup_theme', 'mlfc_register_image_size');
function mlfc_register_image_size() {
    // Hard-cropped to the carousel's own aspect ratio (32:9 desktop, cropped
    // further to 21:9 on mobile via object-fit: cover in the theme's CSS) at
    // a resolution wide enough not to visibly upscale in a full-bleed hero.
    // WordPress's default 'large' size (1024px max, uncropped) was being
    // used before — fine for single.php's narrower reading column, but
    // stretched blurry here and cropped a different amount per post
    // depending on that post's original image aspect ratio.
    add_image_size('mlfc-carousel', 1600, 500, true);
}

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
