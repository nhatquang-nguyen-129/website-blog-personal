<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Same-URL language toggling.
 *
 * The "original" post in a group is the one public URL for the whole group.
 * Visiting it with ?lang=xx swaps in a sibling translation's content without
 * changing the URL. Visiting a non-original translation's own permalink
 * directly redirects to the canonical URL + ?lang=, so only one URL is ever
 * shared or indexed per piece of content.
 */

add_action('template_redirect', 'mlp_redirect_translation_permalink_to_canonical');
function mlp_redirect_translation_permalink_to_canonical() {
    if (!is_singular(array('post', 'page'))) {
        return;
    }

    $post_id = get_queried_object_id();
    $group   = get_post_meta($post_id, '_ml_group', true);

    if (!$group) {
        return;
    }

    if ((bool) get_post_meta($post_id, '_ml_is_original', true)) {
        return;
    }

    $original = mlp_get_original_post($group);
    if (!$original || (int) $original->ID === (int) $post_id) {
        return;
    }

    $target = add_query_arg('lang', mlp_get_post_lang($post_id), get_permalink($original->ID));

    wp_safe_redirect($target, 301);
    exit;
}

add_action('the_post', 'mlp_swap_post_content_for_requested_lang', 10, 2);
function mlp_swap_post_content_for_requested_lang($post, $query = null) {
    if (is_admin() || empty($_GET['lang'])) {
        return;
    }

    if ($query && (!$query->is_main_query() || !$query->is_singular())) {
        return;
    }

    $group = get_post_meta($post->ID, '_ml_group', true);
    if (!$group) {
        return;
    }

    $requested_lang = sanitize_text_field(wp_unslash($_GET['lang']));
    if ($requested_lang === mlp_get_post_lang($post->ID)) {
        return;
    }

    foreach (mlp_get_group_posts($group, 'publish') as $sibling) {
        if (mlp_get_post_lang($sibling->ID) === $requested_lang) {
            $GLOBALS['post'] = $sibling;
            setup_postdata($sibling);
            return;
        }
    }
    // No published sibling in that language — keep showing the original.
}

add_action('wp_head', 'mlp_hreflang_tags');
function mlp_hreflang_tags() {
    if (!is_singular(array('post', 'page'))) {
        return;
    }

    $group = get_post_meta(get_queried_object_id(), '_ml_group', true);
    if (!$group) {
        return;
    }

    $original = mlp_get_original_post($group);
    if (!$original) {
        return;
    }

    foreach (mlp_get_group_posts($group, 'publish') as $sibling) {
        $lang = mlp_get_post_lang($sibling->ID);
        $url  = add_query_arg('lang', $lang, get_permalink($original->ID));
        printf(
            '<link rel="alternate" hreflang="%s" href="%s" />' . "\n",
            esc_attr($lang),
            esc_url($url)
        );
    }
}
