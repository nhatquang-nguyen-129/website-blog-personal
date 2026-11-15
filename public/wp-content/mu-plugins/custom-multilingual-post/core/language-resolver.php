<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Same-URL language toggling.
 *
 * The "original" post in a group owns the group's URL. Its own language is
 * served at that plain permalink; every other language is served at the same
 * permalink + ?lang=xx, which swaps in that sibling translation's content
 * without changing the path. Visiting a non-original translation's own
 * permalink directly redirects there, so each language has exactly one URL.
 */

/**
 * The public URL for one language of a group: the original's plain
 * permalink for the original's own language, + ?lang=xx for any other.
 * hreflang, canonical, the switcher and the redirect all build URLs here,
 * so they can never disagree about which URL a language lives at.
 */
function mlp_lang_url($original, $lang) {
    $url = get_permalink($original->ID);
    return $lang === mlp_get_post_lang($original->ID) ? $url : add_query_arg('lang', $lang, $url);
}

/**
 * The published translation this request is showing in place of the
 * original (via ?lang=), or null when the original itself is shown — no
 * ?lang, ?lang= the original's own language, or a language this group has
 * no published version in. Resolved once per request and shared by the
 * content swap and the <head> tags below, so they always agree.
 */
function mlp_requested_translation() {
    static $resolved = false;
    if ($resolved !== false) {
        return $resolved;
    }

    $resolved = null;
    if (is_admin() || empty($_GET['lang']) || !is_singular(array('post', 'page'))) {
        return $resolved;
    }

    $post_id = get_queried_object_id();
    $group   = get_post_meta($post_id, '_ml_group', true);
    $lang    = sanitize_key(wp_unslash($_GET['lang']));
    if (!$group || $lang === mlp_get_post_lang($post_id)) {
        return $resolved;
    }

    foreach (mlp_get_group_posts($group, 'publish') as $sibling) {
        if (mlp_get_post_lang($sibling->ID) === $lang) {
            $resolved = $sibling;
            break;
        }
    }

    return $resolved;
}

add_action('template_redirect', 'mlp_redirect_translation_permalink_to_canonical');
function mlp_redirect_translation_permalink_to_canonical() {
    // A preview (the author checking their translation, draft or unsaved
    // changes) must render that translation itself — redirecting it to
    // original?lang=xx would show the original, or the last published
    // version, instead.
    if (!is_singular(array('post', 'page')) || is_preview()) {
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

    wp_safe_redirect(mlp_lang_url($original, mlp_get_post_lang($post_id)), 301);
    exit;
}

add_action('the_post', 'mlp_swap_post_content_for_requested_lang', 10, 2);
function mlp_swap_post_content_for_requested_lang($post, $query = null) {
    if ($query && (!$query->is_main_query() || !$query->is_singular())) {
        return;
    }

    $sibling = mlp_requested_translation();
    // Only swap the original itself — setup_postdata() below fires the_post
    // again with the sibling, which this check lets pass untouched.
    if (!$sibling || (int) $post->ID !== get_queried_object_id()) {
        return;
    }

    $GLOBALS['post'] = $sibling;
    setup_postdata($sibling);
}

/**
 * Without this, every language's page declared the original's plain URL as
 * canonical — contradicting the hreflang tags, which list ?lang=xx as its
 * own version. Search engines then treat the translation as a duplicate of
 * the original and leave it out of the index.
 */
add_filter('get_canonical_url', 'mlp_canonical_url_for_requested_lang', 10, 2);
function mlp_canonical_url_for_requested_lang($canonical_url, $post) {
    $sibling = mlp_requested_translation();
    if (!$sibling || (int) $post->ID !== get_queried_object_id()) {
        return $canonical_url;
    }

    return add_query_arg('lang', mlp_get_post_lang($sibling->ID), $canonical_url);
}

/**
 * The browser tab / search-result title follows the language being shown,
 * not the original's — core builds it from the queried object, which the
 * content swap above doesn't change.
 */
add_filter('document_title_parts', 'mlp_document_title_for_requested_lang');
function mlp_document_title_for_requested_lang($parts) {
    $sibling = mlp_requested_translation();
    if ($sibling) {
        $parts['title'] = get_the_title($sibling);
    }
    return $parts;
}

/**
 * <html lang> matches the language actually shown on a translated post
 * (either version), rather than always the site's own language setting.
 */
add_filter('language_attributes', 'mlp_html_lang_for_post');
function mlp_html_lang_for_post($output) {
    if (is_admin() || !is_singular(array('post', 'page'))) {
        return $output;
    }

    $post_id = get_queried_object_id();
    if (!get_post_meta($post_id, '_ml_group', true)) {
        return $output;
    }

    $sibling = mlp_requested_translation();
    $lang    = mlp_get_post_lang($sibling ? $sibling->ID : $post_id);

    return preg_replace('/\blang="[^"]*"/', 'lang="' . esc_attr($lang) . '"', $output);
}

/**
 * Translation posts (_ml_is_original = 0) are an editing convenience, not a
 * second public entry — they must never show up as their own item in a list
 * view (home, blog, archives, search), only the group's original post does.
 * Any custom WP_Query built for a public list should merge this in too.
 */
function mlp_exclude_translations_meta_query() {
    return array(
        'relation' => 'OR',
        array('key' => '_ml_is_original', 'compare' => 'NOT EXISTS'),
        array('key' => '_ml_is_original', 'value' => '0', 'compare' => '!='),
    );
}

// Search is the one list that keeps translations — see mlp_dedupe_search_results().
add_action('pre_get_posts', 'mlp_hide_translations_from_list_views');
function mlp_hide_translations_from_list_views($query) {
    if (is_admin() || !$query->is_main_query() || $query->is_singular() || $query->is_search()) {
        return;
    }

    $meta_query   = $query->get('meta_query') ?: array();
    $meta_query[] = mlp_exclude_translations_meta_query();
    $query->set('meta_query', $meta_query);
}

/**
 * The core Query Loop block runs its own WP_Query, not the main query, so
 * pre_get_posts above never reaches it. A Query Loop set to inherit the
 * main query already goes through that, so this covers the rest.
 */
add_filter('query_loop_block_query_vars', 'mlp_hide_translations_from_query_loop');
function mlp_hide_translations_from_query_loop($query) {
    $meta_query   = isset($query['meta_query']) ? $query['meta_query'] : array();
    $meta_query[] = mlp_exclude_translations_meta_query();
    $query['meta_query'] = $meta_query;
    return $query;
}

/**
 * Search includes translations, so a reader can find a post by words from
 * any of its languages. Each group still appears only once: the original
 * if it matched (a term shared by every language, like a game's name,
 * shouldn't send a reader to a translation), otherwise the first matching
 * translation, linking (via mlp_translation_link()) straight to that
 * language's URL.
 */
add_filter('the_posts', 'mlp_dedupe_search_results', 10, 2);
function mlp_dedupe_search_results($posts, $query) {
    if (is_admin() || !$query->is_main_query() || !$query->is_search()) {
        return $posts;
    }

    $originals_matched = array();
    foreach ($posts as $post) {
        $group = get_post_meta($post->ID, '_ml_group', true);
        if ($group && get_post_meta($post->ID, '_ml_is_original', true)) {
            $originals_matched[$group] = true;
        }
    }

    $seen   = array();
    $unique = array();
    foreach ($posts as $post) {
        $group = get_post_meta($post->ID, '_ml_group', true);
        if ($group) {
            $is_original = (bool) get_post_meta($post->ID, '_ml_is_original', true);
            if (isset($seen[$group]) || (!$is_original && isset($originals_matched[$group]))) {
                continue;
            }
            $seen[$group] = true;
        }
        $unique[] = $post;
    }
    return $unique;
}

/**
 * A published translation's permalink, everywhere on the frontend, is its
 * language's real URL (original + ?lang=xx) rather than its own slug,
 * which would only 301 there. This is also what puts the ?lang=xx URLs —
 * not redirecting ones — into core's /wp-sitemap.xml, which lists posts by
 * get_permalink(). Skipped in wp-admin, in REST requests (the block editor
 * builds its View/Preview links from the REST response, and a preview has to
 * open the translation's own URL to show its unsaved changes) and for
 * unpublished translations, so editing, slug changes and previews keep
 * working on the post's own URL.
 */
add_filter('post_link', 'mlp_translation_link', 10, 2);
function mlp_translation_link($permalink, $post) {
    $url = mlp_public_translation_url($post);
    return $url ? $url : $permalink;
}

add_filter('page_link', 'mlp_translation_page_link', 10, 2);
function mlp_translation_page_link($link, $post_id) {
    $url = mlp_public_translation_url(get_post($post_id));
    return $url ? $url : $link;
}

function mlp_public_translation_url($post) {
    $is_rest = defined('REST_REQUEST') && REST_REQUEST;
    if (is_admin() || $is_rest || !$post || $post->post_status !== 'publish' || get_post_meta($post->ID, '_ml_is_original', true) !== '0') {
        return null;
    }

    $original = mlp_get_original_post(get_post_meta($post->ID, '_ml_group', true));
    if (!$original || (int) $original->ID === (int) $post->ID || $original->post_status !== 'publish') {
        return null;
    }

    return mlp_lang_url($original, mlp_get_post_lang($post->ID));
}

/**
 * One hreflang per published language at its mlp_lang_url(), plus
 * x-default pointing at the original's plain URL for any other reader.
 */
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

    $siblings = mlp_get_group_posts($group, 'publish');
    if (count($siblings) < 2) {
        return;
    }

    foreach ($siblings as $sibling) {
        $lang = mlp_get_post_lang($sibling->ID);
        printf(
            '<link rel="alternate" hreflang="%s" href="%s" />' . "\n",
            esc_attr($lang),
            esc_url(mlp_lang_url($original, $lang))
        );
    }
    printf('<link rel="alternate" hreflang="x-default" href="%s" />' . "\n", esc_url(get_permalink($original->ID)));
}
