<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * mlp_render_language_picker(): "Available in other languages" — a line
 * above the post that opens a dropdown of its languages. Called from the theme's single.php (guarded by
 * function_exists()); renders nothing on a post with no other published
 * language.
 *
 * The prompt is always English, the most widely read language, so a reader
 * who can't read the post's language can still find their way. Each
 * language in the dropdown is named in itself ("Tiếng Việt", "English",
 * "Deutsch") — see mlp_available_langs(). Links only, no redirect: every
 * language stays at its own URL, for readers and search engines alike.
 *
 * Markup only, with this plugin's own class names; the theme styles it.
 */

/**
 * The languages of the post being viewed, or null if it has no other
 * published language: ['current' => 'vi', 'langs' => ['vi' => url, ...]].
 */
function mlp_language_picker_context() {
    static $context = false;
    if ($context !== false) {
        return $context;
    }

    $context = null;
    if (is_admin() || !is_singular(array('post', 'page'))) {
        return $context;
    }

    $post_id  = get_queried_object_id();
    $group    = get_post_meta($post_id, '_ml_group', true);
    $original = $group ? mlp_get_original_post($group) : null;
    $siblings = $original ? mlp_get_group_posts($group, 'publish') : array();
    if (count($siblings) < 2) {
        return $context;
    }

    $langs = array();
    foreach ($siblings as $sibling) {
        $lang         = mlp_get_post_lang($sibling->ID);
        $langs[$lang] = mlp_lang_url($original, $lang);
    }

    $shown   = mlp_requested_translation();
    $context = array(
        'current' => mlp_get_post_lang($shown ? $shown->ID : $post_id),
        'langs'   => $langs,
    );
    return $context;
}

function mlp_render_language_picker() {
    $context = mlp_language_picker_context();
    if (!$context) {
        return '';
    }

    $items = '';
    foreach ($context['langs'] as $lang => $url) {
        $active = $lang === $context['current'];
        $items .= sprintf(
            '<li><a class="mlp-lang-picker__item%s" href="%s" lang="%s" hreflang="%s"%s>%s</a></li>',
            $active ? ' is-active' : '',
            esc_url($url),
            esc_attr($lang),
            esc_attr($lang),
            $active ? ' aria-current="true"' : '',
            esc_html(mlp_lang_label($lang))
        );
    }

    return sprintf(
        '<div class="mlp-lang-picker" data-mlp-lang-picker>'
            . '<button type="button" class="mlp-lang-picker__toggle" data-mlp-lang-toggle aria-haspopup="true" aria-expanded="false" lang="en">'
            . '%1$s<span class="mlp-lang-picker__text">%2$s</span>%3$s'
            . '</button>'
            . '<ul class="mlp-lang-picker__list" data-mlp-lang-list hidden>%4$s</ul>'
            . '</div>',
        mlp_icon_globe(),
        // Short enough to stay on one line on a phone.
        esc_html('Available in other languages'),
        mlp_icon_chevron(),
        $items
    );
}

function mlp_icon_globe() {
    return '<svg class="mlp-lang-picker__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . '<circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line>'
        . '<path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>';
}

function mlp_icon_chevron() {
    return '<svg class="mlp-lang-picker__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . '<polyline points="6 9 12 15 18 9"></polyline></svg>';
}

add_action('wp_enqueue_scripts', 'mlp_enqueue_language_picker_script');
function mlp_enqueue_language_picker_script() {
    if (!mlp_language_picker_context()) {
        return;
    }

    wp_enqueue_script(
        'mlp-language-picker',
        content_url('mu-plugins/custom-multilingual-post/assets/language-picker.js'),
        array(),
        filemtime(MLP_PATH . '/assets/language-picker.js'),
        true
    );
}
