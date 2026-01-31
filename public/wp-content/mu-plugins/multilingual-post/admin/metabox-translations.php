add_action('add_meta_boxes', function () {
    add_meta_box(
        'mlp_translations',
        'Multilingual Versions',
        'mlp_translations_box',
        ['post', 'page'],
        'side'
    );
});

function mlp_translations_box($post) {
    $group = mlp_get_or_create_group($post->ID);
    $posts = mlp_get_group_posts($group);

    echo '<ul>';
    foreach ($posts as $p) {
        $lang = get_post_meta($p->ID, '_ml_lang', true);
        echo "<li><a href='".get_edit_post_link($p->ID)."'>$lang</a></li>";
    }
    echo '</ul>';

    ?>
    <select id="mlp_new_lang">
        <option value="">Add translation…</option>
        <option value="en">English</option>
        <option value="vi">Vietnamese</option>
    </select>
    <button class="button" id="mlp_add_translation">Add</button>

    <script>
    jQuery('#mlp_add_translation').on('click', function () {
        fetch(ajaxurl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'mlp_create_translation',
                post_id: <?= $post->ID ?>,
                lang: document.getElementById('mlp_new_lang').value
            })
        }).then(r => r.json()).then(res => {
            if (res.edit_url) window.location = res.edit_url;
        });
    });
    </script>
    <?php
}