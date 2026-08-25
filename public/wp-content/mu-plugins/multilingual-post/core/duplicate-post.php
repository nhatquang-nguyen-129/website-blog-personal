<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Duplicate $post_id as a new draft translation in $lang, carrying over the
 * featured image, taxonomies and excerpt so the translator isn't starting
 * from a bare post.
 *
 * Returns the new post ID, or 0 if $lang already exists in the group or the
 * insert fails.
 */
function mlp_duplicate_post($post_id, $lang) {
    $post = get_post($post_id);
    if (!$post) {
        return 0;
    }

    $group = mlp_get_or_create_group($post_id);

    foreach (mlp_get_group_posts($group) as $existing) {
        if (mlp_get_post_lang($existing->ID) === $lang) {
            return 0;
        }
    }

    $new_id = wp_insert_post(array(
        'post_title'   => $post->post_title,
        'post_content' => $post->post_content,
        'post_excerpt' => $post->post_excerpt,
        'post_status'  => 'draft',
        'post_type'    => $post->post_type,
    ), true);

    if (is_wp_error($new_id) || !$new_id) {
        return 0;
    }

    $thumbnail_id = get_post_thumbnail_id($post_id);
    if ($thumbnail_id) {
        set_post_thumbnail($new_id, $thumbnail_id);
    }

    foreach (get_object_taxonomies($post->post_type) as $taxonomy) {
        $terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'ids'));
        if (!is_wp_error($terms) && $terms) {
            wp_set_object_terms($new_id, $terms, $taxonomy);
        }
    }

    update_post_meta($new_id, '_ml_lang', $lang);
    update_post_meta($new_id, '_ml_group', $group);
    update_post_meta($new_id, '_ml_is_original', 0);

    return $new_id;
}
