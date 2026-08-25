<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * A "group" links every language version of one piece of content together via
 * a shared _ml_group meta value. Exactly one post per group is flagged
 * _ml_is_original — that post's permalink is the one public URL for the whole
 * group (see core/language-resolver.php).
 */
function mlp_get_or_create_group($post_id) {
    $group = get_post_meta($post_id, '_ml_group', true);

    if (!$group) {
        $group = uniqid('mlg_');
        update_post_meta($post_id, '_ml_group', $group);
        update_post_meta($post_id, '_ml_is_original', 1);
    }

    return $group;
}

/**
 * Fetch every post in a translation group.
 *
 * @param string       $group       The shared _ml_group value.
 * @param string|array $post_status Defaults to 'any' (drafts included) for
 *                                   admin use — pass 'publish' explicitly for
 *                                   anything rendered to a public visitor.
 */
function mlp_get_group_posts($group, $post_status = 'any') {
    if (!$group) {
        return array();
    }

    return get_posts(array(
        'post_type'      => array('post', 'page'),
        'post_status'    => $post_status,
        'posts_per_page' => -1,
        'orderby'        => 'ID',
        'order'          => 'ASC',
        'meta_query'     => array(
            array('key' => '_ml_group', 'value' => $group),
        ),
    ));
}

/**
 * The original post of a group — the canonical public URL for the whole group.
 * Falls back to the oldest post in the group if the flag is somehow missing.
 */
function mlp_get_original_post($group) {
    if (!$group) {
        return null;
    }

    $posts = get_posts(array(
        'post_type'      => array('post', 'page'),
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'meta_query'     => array(
            array('key' => '_ml_group', 'value' => $group),
            array('key' => '_ml_is_original', 'value' => 1),
        ),
    ));

    if ($posts) {
        return $posts[0];
    }

    $all = mlp_get_group_posts($group);
    if (!$all) {
        return null;
    }

    usort($all, function ($a, $b) {
        return $a->ID <=> $b->ID;
    });

    return $all[0];
}

/**
 * A post's language code, falling back to the site default if never set.
 */
function mlp_get_post_lang($post_id) {
    $lang = get_post_meta($post_id, '_ml_lang', true);
    return $lang ? $lang : mlp_default_lang();
}
