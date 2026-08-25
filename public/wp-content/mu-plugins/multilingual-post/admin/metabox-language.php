<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('add_meta_boxes', 'mlp_add_language_metabox');
function mlp_add_language_metabox() {
    add_meta_box(
        'mlp_language',
        __('Post Language', 'multilingual-post'),
        'mlp_render_language_metabox',
        array('post', 'page'),
        'side'
    );
}

function mlp_render_language_metabox($post) {
    $lang = mlp_get_post_lang($post->ID);

    wp_nonce_field('mlp_save_language_' . $post->ID, 'mlp_language_nonce');

    echo '<select name="mlp_lang">';
    foreach (mlp_available_langs() as $code => $label) {
        echo '<option value="' . esc_attr($code) . '"' . selected($lang, $code, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}

add_action('save_post', 'mlp_save_language_meta');
function mlp_save_language_meta($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!isset($_POST['mlp_language_nonce']) || !wp_verify_nonce($_POST['mlp_language_nonce'], 'mlp_save_language_' . $post_id)) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['mlp_lang'])) {
        update_post_meta(
            $post_id,
            '_ml_lang',
            sanitize_text_field(wp_unslash($_POST['mlp_lang']))
        );
    }
}
