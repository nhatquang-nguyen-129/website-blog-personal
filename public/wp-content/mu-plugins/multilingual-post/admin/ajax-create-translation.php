<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_footer', 'mlp_admin_js');
function mlp_admin_js() {
    ?>
    <script>
    function mlpAddTranslation(postId) {
        var select = document.getElementById('mlp-new-lang');
        if (!select || !select.value) {
            return;
        }

        var data = new URLSearchParams();
        data.append('action', 'mlp_create_translation');
        data.append('post_id', postId);
        data.append('lang', select.value);

        fetch(ajaxurl, {
            method: 'POST',
            body: data
        })
        .then(function(res){ return res.json(); })
        .then(function(res){
            if (res.edit_url) {
                window.location.href = res.edit_url;
            }
        });
    }
    </script>
    <?php
}

add_action('wp_ajax_mlp_create_translation', 'mlp_create_translation');
function mlp_create_translation() {
    $post_id = intval($_POST['post_id']);
    $lang    = sanitize_text_field($_POST['lang']);

    $new_id = mlp_duplicate_post($post_id, $lang);

    wp_send_json(array(
        'edit_url' => get_edit_post_link($new_id, 'raw'),
    ));
}