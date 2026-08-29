<?php
if (!defined('ABSPATH')) {
    exit;
}

define('MLPT_PATH', __DIR__);

require_once MLPT_PATH . '/core/query.php';

add_action('rest_api_init', 'mlpt_register_rest_routes');
function mlpt_register_rest_routes() {
    register_rest_route('mlpt/v1', '/posts', array(
        'methods'             => 'GET',
        // Public and read-only — same trust level as core's own /wp/v2/posts,
        // just pre-shaped into this block's own list/pagination HTML instead
        // of raw post JSON, so the frontend doesn't need to duplicate
        // render.php's card markup in JS.
        'permission_callback' => '__return_true',
        'callback'            => 'mlpt_rest_get_posts',
        'args'                => array(
            'tab'      => array('required' => true, 'type' => 'string'),
            'page'     => array('default' => 1, 'type' => 'integer'),
            'per_page' => array('default' => 5, 'type' => 'integer'),
        ),
    ));
}

function mlpt_rest_get_posts(WP_REST_Request $request) {
    $tab = sanitize_key($request->get_param('tab'));
    if (!isset(mlpt_valid_tabs()[$tab])) {
        return new WP_Error('mlpt_invalid_tab', __('Unknown tab.', 'post-tabs'), array('status' => 400));
    }

    $page     = max(1, (int) $request->get_param('page'));
    $per_page = max(1, min(10, (int) $request->get_param('per_page')));
    $query    = mlpt_run_tab_query($tab, $page, $per_page);

    return array(
        'tab'            => $tab,
        'page'           => $page,
        'maxPages'       => (int) $query->max_num_pages,
        'listHtml'       => mlpt_render_list_html($query),
        'paginationHtml' => mlpt_render_pagination_html($tab, $page, $query->max_num_pages),
    );
}

add_action('after_setup_theme', 'mlpt_register_image_size');
function mlpt_register_image_size() {
    // Hard-cropped so every row's thumbnail is a consistent shape regardless
    // of each post's own featured-image aspect ratio — same reasoning as
    // custom-featured-carousel's own image size.
    add_image_size('mlpt-thumb', 320, 200, true);
}

add_action('init', 'mlpt_register_block');
function mlpt_register_block() {
    // Registered explicitly (not via block.json's bare "editorScript") for
    // the same reason as this project's other blocks: without a build tool
    // generating an editor.asset.php, WordPress would register the script
    // with an empty dependency array, and it would risk running before
    // wp.blocks/wp.element/etc. are guaranteed to exist.
    wp_register_script(
        'mlpt-post-tabs-editor',
        content_url('mu-plugins/custom-post-tabs/block/editor.js'),
        array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n'),
        filemtime(MLPT_PATH . '/block/editor.js'),
        true
    );

    register_block_type(MLPT_PATH . '/block', array(
        'editorScript' => 'mlpt-post-tabs-editor',
    ));
}

add_action('wp_enqueue_scripts', 'mlpt_enqueue_frontend_assets');
function mlpt_enqueue_frontend_assets() {
    if (!has_block('minimal-reader/post-tabs')) {
        return;
    }

    wp_enqueue_script(
        'mlpt-post-tabs',
        content_url('mu-plugins/custom-post-tabs/assets/tabs.js'),
        array(),
        filemtime(MLPT_PATH . '/assets/tabs.js'),
        true
    );

    // Plain fetch() against this, not wp.apiFetch — pulling in wp-api-fetch
    // (+ its own wp-i18n/wp-hooks dependencies) just for one public GET
    // isn't worth it for anonymous frontend visitors.
    wp_localize_script('mlpt-post-tabs', 'mlptSettings', array(
        'restUrl' => esc_url_raw(rest_url('mlpt/v1/posts')),
    ));
}
