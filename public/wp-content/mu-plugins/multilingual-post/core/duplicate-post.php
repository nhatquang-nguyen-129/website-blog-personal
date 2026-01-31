<?php
if (!defined('ABSPATH')) {
    exit;
}

function mlp_duplicate_post($post_id, $lang) {
    $post = get_post($post_id);
    if (!$post) {
        return 0;
    }

    $new_id = wp_insert_post(array(
        'post_title'   => $post->post_title,
        'post_content' => $post->post_content,
        'post_status'  => 'draft',
        'post_type'    => $post->post_type,
    ));

    if (!$new_id) {
        return 0;
    }

    $group = mlp_get_or_create_group($post_id);

    update_post_meta($new_id, '_ml_lang', $lang);
    update_post_meta($new_id, '_ml_group', $group);
    update_post_meta($new_id, '_ml_is_original', 0);

    return $new_id;
}