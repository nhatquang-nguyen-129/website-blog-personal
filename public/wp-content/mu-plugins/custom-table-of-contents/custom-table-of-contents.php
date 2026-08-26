<?php
if (!defined('ABSPATH')) {
    exit;
}

define('MLPTOC_PATH', __DIR__);

require_once MLPTOC_PATH . '/core/headings.php';

add_action('init', 'mlptoc_register_block');
function mlptoc_register_block() {
    // Registered explicitly (rather than relying on block.json's bare
    // "editorScript": "file:./editor.js") because that auto-registration
    // only picks up dependencies/version from an editor.asset.php file,
    // which only exists when a build tool (@wordpress/scripts) generates
    // one — without it, WP registers the script with an EMPTY dependency
    // array, so wp.blocks/wp.element/wp.blockEditor/wp.i18n aren't
    // guaranteed to exist yet when the script runs, and it silently fails
    // to register the block (no console crash, it just never appears).
    wp_register_script(
        'mlptoc-editor-script',
        content_url('mu-plugins/custom-table-of-contents/block/editor.js'),
        array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-i18n'),
        filemtime(MLPTOC_PATH . '/block/editor.js'),
        true
    );

    register_block_type(MLPTOC_PATH . '/block', array(
        'editorScript' => 'mlptoc-editor-script',
    ));
}
