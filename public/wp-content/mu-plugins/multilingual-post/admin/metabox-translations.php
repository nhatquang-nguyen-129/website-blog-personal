<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('add_meta_boxes', 'mlp_add_translation_metabox');
function mlp_add_translation_metabox() {
    add_meta_box(
        'mlp_translations',
        __('Multilingual Versions', 'multilingual-post'),
        'mlp_render_translation_metabox',
        array('post', 'page'),
        'side'
    );
}

function mlp_render_translation_metabox($post) {
    // Read-only lookup — opening this screen must never create a group as a side effect.
    $group = get_post_meta($post->ID, '_ml_group', true);
    $posts = $group ? mlp_get_group_posts($group) : array($post);

    $existing_langs = array();
    foreach ($posts as $p) {
        $existing_langs[] = mlp_get_post_lang($p->ID);
    }

    echo '<ul class="mlp-translation-list">';
    foreach ($posts as $p) {
        $lang       = mlp_get_post_lang($p->ID);
        $edit_link  = get_edit_post_link($p->ID);
        $status     = get_post_status($p->ID);
        $is_current = (int) $p->ID === (int) $post->ID;

        echo '<li' . ($is_current ? ' style="font-weight:bold;"' : '') . '>';
        echo '<a href="' . esc_url($edit_link) . '">' . esc_html(mlp_lang_label($lang)) . '</a>';
        echo ' — <em>' . esc_html($status) . '</em>';
        echo '</li>';
    }
    echo '</ul>';

    $remaining = array_diff_key(mlp_available_langs(), array_flip($existing_langs));

    if ($remaining) {
        echo '<select id="mlp-new-lang">';
        echo '<option value="">' . esc_html__('Add translation…', 'multilingual-post') . '</option>';
        foreach ($remaining as $code => $label) {
            echo '<option value="' . esc_attr($code) . '">' . esc_html($label) . '</option>';
        }
        echo '</select>';

        echo '<br><br>';

        wp_nonce_field('mlp_create_translation_' . $post->ID, 'mlp_translation_nonce');

        echo '<button type="button" class="button" onclick="mlpAddTranslation(' . intval($post->ID) . ')">';
        echo esc_html__('Add', 'multilingual-post');
        echo '</button>';
    } else {
        echo '<p><em>' . esc_html__('All supported languages already have a version.', 'multilingual-post') . '</em></p>';
    }
}
