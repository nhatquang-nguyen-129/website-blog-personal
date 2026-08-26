<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('enqueue_block_editor_assets', 'mlp_enqueue_editor_panel');
function mlp_enqueue_editor_panel() {
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->post_type, array('post', 'page'), true)) {
        return;
    }

    wp_enqueue_script(
        'mlp-editor-panel',
        content_url('mu-plugins/custom-multilingual-post/admin/editor-panel.js'),
        array('wp-plugins', 'wp-edit-post', 'wp-editor', 'wp-element', 'wp-components', 'wp-data', 'wp-core-data', 'wp-api-fetch', 'wp-i18n'),
        filemtime(MLP_PATH . '/admin/editor-panel.js'),
        true
    );

    wp_enqueue_style(
        'mlp-editor-panel',
        content_url('mu-plugins/custom-multilingual-post/admin/editor-panel.css'),
        array('wp-components'),
        filemtime(MLP_PATH . '/admin/editor-panel.css')
    );

    wp_set_script_translations('mlp-editor-panel', 'multilingual-post');

    wp_localize_script('mlp-editor-panel', 'mlpEditorPanel', array(
        'availableLangs' => mlp_available_langs(),
    ));
}
