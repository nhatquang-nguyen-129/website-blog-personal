function mlp_get_or_create_group($post_id) {
    $group = get_post_meta($post_id, '_ml_group', true);
    if (!$group) {
        $group = uniqid('mlg_');
        update_post_meta($post_id, '_ml_group', $group);
        update_post_meta($post_id, '_ml_is_original', 1);
    }
    return $group;
}

function mlp_get_group_posts($group) {
    return get_posts([
        'meta_key'   => '_ml_group',
        'meta_value' => $group,
        'post_type'  => 'any',
        'numberposts'=> -1
    ]);
}