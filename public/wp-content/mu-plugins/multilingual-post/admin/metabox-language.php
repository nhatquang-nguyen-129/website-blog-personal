<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('add_meta_boxes', 'mlp_add_language_metabox');
function mlp_add_language_metabox() {
    add_meta_box(
        'mlp_language',
        'Post Language',
        'mlp_render_language_metabox',
        array('post', 'page'),
        'side'
    );
}

function mlp_render_language_metabox($post) {
    $lang = get_post_meta($post->ID, '_ml_lang', true);
    if (!$lang) {
        $lang = 'vi';
    }

    echo '<select name="mlp_lang">';
    echo '<option value="vi"' . selected($lang, 'vi', false) . '>Vietnamese</option>';
    echo '<option value="en"' . selected($lang, 'en', false) . '>English</option>';
    echo '</select>';
}

add_action('save_post', 'mlp_save_language_meta');
function mlp_save_language_meta($post_id) {
    if (isset($_POST['mlp_lang'])) {
        update_post_meta(
            $post_id,
            '_ml_lang',
            sanitize_text_field($_POST['mlp_lang'])
        );
    }
}