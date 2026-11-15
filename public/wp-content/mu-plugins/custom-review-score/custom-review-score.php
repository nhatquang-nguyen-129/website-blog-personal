<?php
if (!defined('ABSPATH')) {
    exit;
}

define('MLRS_PATH', __DIR__);

require_once MLRS_PATH . '/core/score.php';

add_action('init', 'mlrs_register_block');
function mlrs_register_block() {
    // Registered explicitly (not via block.json's bare "editorScript") for
    // the same reason as this project's other blocks: without a build tool
    // generating an editor.asset.php, WordPress would register the script
    // with an empty dependency array.
    wp_register_script(
        'mlrs-review-score-editor',
        content_url('mu-plugins/custom-review-score/block/editor.js'),
        array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-core-data', 'wp-data', 'wp-i18n'),
        filemtime(MLRS_PATH . '/block/editor.js'),
        true
    );

    register_block_type(MLRS_PATH . '/block', array(
        'editorScript' => 'mlrs-review-score-editor',
    ));
}

/**
 * Posts only: the score meta is registered for posts, so on a page the
 * block's score would appear to save and then silently vanish (REST drops
 * meta that isn't registered for that post type). Hidden from the inserter
 * everywhere else instead.
 */
add_filter('allowed_block_types_all', 'mlrs_limit_block_to_posts', 10, 2);
function mlrs_limit_block_to_posts($allowed, $context) {
    if (empty($context->post) || $context->post->post_type === 'post') {
        return $allowed;
    }
    if ($allowed === true) {
        $allowed = array_keys(WP_Block_Type_Registry::get_instance()->get_all_registered());
    }
    return is_array($allowed) ? array_values(array_diff($allowed, array(MLRS_BLOCK_NAME))) : $allowed;
}

add_action('wp_enqueue_scripts', 'mlrs_enqueue_frontend_assets');
function mlrs_enqueue_frontend_assets() {
    if (!is_singular() || !has_block(MLRS_BLOCK_NAME)) {
        return;
    }

    wp_enqueue_script(
        'mlrs-review-score',
        content_url('mu-plugins/custom-review-score/assets/review-score.js'),
        array(),
        filemtime(MLRS_PATH . '/assets/review-score.js'),
        true
    );
}
