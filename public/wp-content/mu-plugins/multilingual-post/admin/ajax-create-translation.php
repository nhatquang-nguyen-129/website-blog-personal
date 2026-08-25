<?php
if (!defined('ABSPATH')) {
    exit;
}

// Scoped to the post-edit screens only — no reason to load this on every wp-admin page.
add_action('admin_footer-post.php', 'mlp_admin_js');
add_action('admin_footer-post-new.php', 'mlp_admin_js');
function mlp_admin_js() {
    ?>
    <script>
    function mlpAddTranslation(postId) {
        var select = document.getElementById('mlp-new-lang');
        var nonceField = document.getElementById('mlp_translation_nonce');
        if (!select || !select.value || !nonceField) {
            return;
        }

        var data = new URLSearchParams();
        data.append('action', 'mlp_create_translation');
        data.append('post_id', postId);
        data.append('lang', select.value);
        data.append('nonce', nonceField.value);

        fetch(ajaxurl, {
            method: 'POST',
            body: data
        })
        .then(function (res) { return res.json(); })
        .then(function (res) {
            if (res.success && res.data && res.data.edit_url) {
                window.location.href = res.data.edit_url;
            } else if (res.data && res.data.message) {
                alert(res.data.message);
            }
        });
    }
    </script>
    <?php
}

add_action('wp_ajax_mlp_create_translation', 'mlp_create_translation');
function mlp_create_translation() {
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $lang    = isset($_POST['lang']) ? sanitize_text_field(wp_unslash($_POST['lang'])) : '';
    $nonce   = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

    if (!$post_id || !wp_verify_nonce($nonce, 'mlp_create_translation_' . $post_id)) {
        wp_send_json_error(array('message' => __('Security check failed — please reload the page and try again.', 'multilingual-post')), 403);
    }

    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error(array('message' => __('You are not allowed to edit this post.', 'multilingual-post')), 403);
    }

    if (!array_key_exists($lang, mlp_available_langs())) {
        wp_send_json_error(array('message' => __('Unsupported language.', 'multilingual-post')), 400);
    }

    $new_id = mlp_duplicate_post($post_id, $lang);

    if (!$new_id) {
        wp_send_json_error(array('message' => __('That language already exists for this post, or it could not be created.', 'multilingual-post')), 400);
    }

    wp_send_json_success(array(
        'edit_url' => get_edit_post_link($new_id, 'raw'),
    ));
}
