add_action('add_meta_boxes', function () {
    add_meta_box(
        'mlp_language',
        'Post Language',
        'mlp_language_box',
        ['post', 'page'],
        'side'
    );
});

function mlp_language_box($post) {
    $lang = get_post_meta($post->ID, '_ml_lang', true) ?: 'vi';
    ?>
    <select name="mlp_lang">
        <option value="vi" <?= selected($lang, 'vi') ?>>Vietnamese</option>
        <option value="en" <?= selected($lang, 'en') ?>>English</option>
    </select>
    <?php
}

add_action('save_post', function ($post_id) {
    if (isset($_POST['mlp_lang'])) {
        update_post_meta($post_id, '_ml_lang', sanitize_text_field($_POST['mlp_lang']));
    }
});
