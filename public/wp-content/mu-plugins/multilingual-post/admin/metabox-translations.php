<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('add_meta_boxes', 'mlp_add_translation_metabox');
function mlp_add_translation_metabox() {
    add_meta_box(
        'mlp_translations',
        'Multilingual Versions',
        'mlp_render_translation_metabox',
        array('post', 'page'),
        'side'
    );
}

function mlp_render_translation_metabox($post) {
    $group = mlp_get_or_create_group($post->ID);
    $posts = mlp_get_group_posts($group);

    echo '<ul>';

    if (!empty($posts)) {
        foreach ($posts as $p) {
            $lang = get_post_meta($p->ID, '_ml_lang', true);
            $edit_link = get_edit_post_link($p->ID);

            echo '<li>';
            echo '<a href="' . esc_url($edit_link) . '">';
            echo esc_html($lang);
            echo '</a>';
            echo '</li>';
        }
    }

    echo '</ul>';

    echo '<select id="mlp-new-lang">';
    echo '<option value="">Add translation…</option>';
    echo '<option value="vi">Vietnamese</option>';
    echo '<option value="en">English</option>';
    echo '</select>';

    echo '<br><br>';

    echo '<button type="button" class="button" onclick="mlpAddTranslation(' . intval($post->ID) . ')">';
    echo 'Add';
    echo '</button>';
}