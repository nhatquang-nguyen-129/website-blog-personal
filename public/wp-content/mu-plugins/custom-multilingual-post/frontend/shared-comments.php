<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * One discussion per piece of content, whichever language it's read in.
 *
 * Every language version is its own post, so out of the box each one had
 * its own separate comment thread, and custom-post-tabs' "Discussions" tab
 * (ranked by the original's comment_count) never counted comments left on a
 * translation. Here, on a post in a translation group:
 * - new comments are always filed on the group's original;
 * - the thread shows comments from every published post in the group, so
 *   any comments left on a translation before this keep showing;
 * - the count and open/closed state follow the group.
 *
 * Comments only — each post's own content stays as its author wrote it.
 */

/**
 * The translation group of the post being viewed: its original and the IDs
 * of its published posts, or null on a page that isn't part of a group.
 */
function mlp_viewed_comment_group() {
    static $group = false;
    if ($group !== false) {
        return $group;
    }

    $group = null;
    if (is_admin() || !is_singular(array('post', 'page'))) {
        return $group;
    }

    $group_id = get_post_meta(get_queried_object_id(), '_ml_group', true);
    $original = $group_id ? mlp_get_original_post($group_id) : null;
    if (!$original) {
        return $group;
    }

    $group = array(
        'original' => $original,
        'ids'      => array_map('intval', wp_list_pluck(mlp_get_group_posts($group_id, 'publish'), 'ID')),
    );
    return $group;
}

function mlp_is_viewed_group_post($post_id) {
    $group = mlp_viewed_comment_group();
    return $group && in_array((int) $post_id, $group['ids'], true);
}

add_filter('comments_template_query_args', 'mlp_group_comments_query_args');
function mlp_group_comments_query_args($args) {
    $group = mlp_viewed_comment_group();
    if ($group && isset($args['post_id']) && mlp_is_viewed_group_post($args['post_id'])) {
        unset($args['post_id']);
        $args['post__in'] = $group['ids'];
    }
    return $args;
}

add_filter('get_comments_number', 'mlp_group_comments_number', 10, 2);
function mlp_group_comments_number($count, $post_id) {
    if (!mlp_is_viewed_group_post($post_id)) {
        return $count;
    }

    $total = 0;
    foreach (mlp_viewed_comment_group()['ids'] as $id) {
        $total += (int) get_post_field('comment_count', $id);
    }
    return $total;
}

add_filter('comments_open', 'mlp_group_comments_open', 10, 2);
function mlp_group_comments_open($open, $post_id) {
    $group = mlp_viewed_comment_group();
    if (!$group || (int) $post_id === (int) $group['original']->ID || !mlp_is_viewed_group_post($post_id)) {
        return $open;
    }
    return comments_open($group['original']->ID);
}

/**
 * File the comment on the original, whichever language's form it came
 * from (comment_form() otherwise uses the shown post's own ID).
 */
add_filter('comment_id_fields', 'mlp_group_comment_post_id', 10, 2);
function mlp_group_comment_post_id($fields, $post_id) {
    $group = mlp_viewed_comment_group();
    if (!$group || !mlp_is_viewed_group_post($post_id)) {
        return $fields;
    }
    return preg_replace(
        "/(name='comment_post_ID' value=')\d+(')/",
        '${1}' . (int) $group['original']->ID . '${2}',
        $fields
    );
}

/**
 * Remember which language the form was on, so after posting the reader
 * lands back on that language rather than on the original's.
 */
add_action('comment_form', 'mlp_comment_form_lang_field');
function mlp_comment_form_lang_field() {
    $sibling = mlp_requested_translation();
    if ($sibling) {
        printf('<input type="hidden" name="mlp_lang" value="%s" />', esc_attr(mlp_get_post_lang($sibling->ID)));
    }
}

add_filter('comment_post_redirect', 'mlp_comment_redirect_to_lang', 10, 2);
function mlp_comment_redirect_to_lang($location, $comment) {
    if (empty($_POST['mlp_lang'])) {
        return $location;
    }

    $lang     = sanitize_key(wp_unslash($_POST['mlp_lang']));
    $group_id = get_post_meta($comment->comment_post_ID, '_ml_group', true);
    $original = $group_id ? mlp_get_original_post($group_id) : null;
    if (!$original) {
        return $location;
    }

    $langs = array_map('mlp_get_post_lang', wp_list_pluck(mlp_get_group_posts($group_id, 'publish'), 'ID'));
    if (!in_array($lang, $langs, true)) {
        return $location;
    }

    // Keep core's own query args (e.g. unapproved/moderation-hash, which
    // let a commenter see their comment awaiting moderation) and #anchor.
    $parts = wp_parse_url($location);
    $args  = array();
    if (!empty($parts['query'])) {
        wp_parse_str($parts['query'], $args);
    }
    unset($args['lang']);

    $url = add_query_arg($args, mlp_lang_url($original, $lang));
    if (!empty($parts['fragment'])) {
        $url .= '#' . $parts['fragment'];
    }
    return $url;
}
