add_action('wp_ajax_mlp_create_translation', function () {
    $post_id = intval($_POST['post_id']);
    $lang = sanitize_text_field($_POST['lang']);

    $new_id = mlp_duplicate_post($post_id, $lang);

    wp_send_json([
        'edit_url' => get_edit_post_link($new_id, 'raw')
    ]);
});