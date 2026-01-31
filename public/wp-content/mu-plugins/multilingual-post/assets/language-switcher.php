add_shortcode('language_switcher', function () {
    global $post;
    $group = get_post_meta($post->ID, '_ml_group', true);
    if (!$group) return '';

    $posts = mlp_get_group_posts($group);

    $out = '';
    foreach ($posts as $p) {
        $lang = get_post_meta($p->ID, '_ml_lang', true);
        $out .= "<a href='".get_permalink($p->ID)."'>$lang</a> ";
    }
    return $out;
});