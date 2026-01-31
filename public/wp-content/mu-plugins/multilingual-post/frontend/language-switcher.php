<?php
if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('language_switcher', 'mlp_language_switcher');
function mlp_language_switcher() {
    global $post;
    if (!$post) {
        return '';
    }

    $group = get_post_meta($post->ID, '_ml_group', true);
    if (!$group) {
        return '';
    }

    $posts = mlp_get_group_posts($group);

    $out = '<div class="mlp-language-switcher">';

    foreach ($posts as $p) {
        $lang = get_post_meta($p->ID, '_ml_lang', true);
        $out .= '<a href="' . get_permalink($p->ID) . '">';
        $out .= esc_html($lang);
        $out .= '</a> ';
    }

    $out .= '</div>';

    return $out;
}