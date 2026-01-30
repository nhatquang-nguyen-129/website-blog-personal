<?php
defined('ABSPATH') || exit;

add_filter('the_content', function ($content) {
    if (!is_singular()) return $content;

    global $post;
    if (!ml_post_is_enabled($post->ID)) return $content;

    $languages = ml_post_get_languages($post->ID);
    if (count($languages) < 2) return $content;

    ob_start();
    ?>
    <div class="ml-post" data-ml-enabled="1">
        <div class="ml-toggle">
            <?php foreach ($languages as $lang): ?>
                <button type="button" data-lang="<?= esc_attr($lang) ?>">
                    <?= strtoupper(esc_html($lang)) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="ml-body">
            <?= $content ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}, 20);