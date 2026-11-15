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
 * Public lists show only a group's original (translations are hidden from
 * them), so an original that stops being published — unpublished, trashed
 * or deleted — would take its still-published translations off every list
 * with it, reachable only by direct link. Hand the "original" flag to the
 * next published post in the group instead. Only that flag moves: each
 * post's own content is untouched, since every language is written and
 * edited by hand. A demoted original that's later republished stays a
 * translation of the new original.
 */
add_action('transition_post_status', 'mlp_promote_on_unpublish', 10, 3);
function mlp_promote_on_unpublish($new_status, $old_status, $post) {
    if ($old_status === 'publish' && $new_status !== 'publish') {
        mlp_hand_over_original($post->ID);
    }
}

// Permanent deletion of a published post doesn't change its status first.
add_action('before_delete_post', 'mlp_promote_on_delete');
function mlp_promote_on_delete($post_id) {
    if (get_post_status($post_id) === 'publish') {
        mlp_hand_over_original($post_id);
    }
}

function mlp_hand_over_original($post_id) {
    if (!get_post_meta($post_id, '_ml_is_original', true)) {
        return;
    }

    $group = get_post_meta($post_id, '_ml_group', true);
    foreach (mlp_get_group_posts($group, 'publish') as $sibling) {
        if ((int) $sibling->ID === (int) $post_id) {
            continue;
        }
        update_post_meta($post_id, '_ml_is_original', 0);
        update_post_meta($sibling->ID, '_ml_is_original', 1);
        return;
    }
}

/**
 * A post's language code, falling back to the site default if never set.
 */
function mlp_get_post_lang($post_id) {
    $lang = get_post_meta($post_id, '_ml_lang', true);
    return $lang ? $lang : mlp_default_lang();
}
