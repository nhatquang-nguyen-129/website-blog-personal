<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * [language_switcher] — a same-URL language toggle. Every link points back
 * at the group's one canonical (original) permalink, only the ?lang= query
 * arg changes; see core/language-resolver.php for how that arg swaps content.
 */
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

    $original = mlp_get_original_post($group);
    if (!$original) {
        return '';
    }

    $siblings = mlp_get_group_posts($group, 'publish');
    if (count($siblings) < 2) {
        return '';
    }

    $current_lang = mlp_get_post_lang($post->ID);

    $out = '<div class="mlp-language-switcher">';
    foreach ($siblings as $p) {
        $lang   = mlp_get_post_lang($p->ID);
        $url    = add_query_arg('lang', $lang, get_permalink($original->ID));
        $active = $lang === $current_lang;

        $out .= '<a href="' . esc_url($url) . '"';
        $out .= $active ? ' class="mlp-active" aria-current="true"' : '';
        $out .= '>' . esc_html(mlp_lang_label($lang)) . '</a> ';
    }
    $out .= '</div>';

    return $out;
}
