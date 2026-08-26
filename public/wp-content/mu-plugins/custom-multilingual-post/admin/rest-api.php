<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'mlp_register_post_meta');
function mlp_register_post_meta() {
    foreach (array('post', 'page') as $post_type) {
        register_post_meta($post_type, '_ml_lang', array(
            'type'              => 'string',
            'single'            => true,
            'show_in_rest'      => true,
            'default'           => mlp_default_lang(),
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback'     => function ($allowed, $meta_key, $post_id) {
                return current_user_can('edit_post', $post_id);
            },
        ));
    }
}

add_action('rest_api_init', 'mlp_register_rest_routes');
function mlp_register_rest_routes() {
    register_rest_route('mlp/v1', '/groups/(?P<post_id>\d+)', array(
        'methods'             => 'GET',
        'permission_callback' => function ($request) {
            return current_user_can('edit_post', (int) $request['post_id']);
        },
        'callback'            => 'mlp_rest_get_group',
    ));

    register_rest_route('mlp/v1', '/translations', array(
        'methods'             => 'POST',
        'permission_callback' => function (WP_REST_Request $request) {
            return current_user_can('edit_post', (int) $request->get_param('post_id'));
        },
        'callback' => 'mlp_rest_create_translation',
        'args'     => array(
            'post_id' => array('required' => true, 'type' => 'integer'),
            'lang'    => array('required' => true, 'type' => 'string'),
        ),
    ));
}

/**
 * GET /mlp/v1/groups/{post_id} — every post in this post's translation
 * group (or just itself, if it isn't in one yet), plus the language list,
 * for the editor sidebar panel.
 */
function mlp_rest_get_group(WP_REST_Request $request) {
    $post_id = (int) $request['post_id'];
    $post    = get_post($post_id);
    if (!$post) {
        return new WP_Error('mlp_not_found', __('Post not found.', 'multilingual-post'), array('status' => 404));
    }

    $group = get_post_meta($post_id, '_ml_group', true);
    $posts = $group ? mlp_get_group_posts($group) : array($post);

    $items = array();
    foreach ($posts as $p) {
        $lang     = mlp_get_post_lang($p->ID);
        $items[] = array(
            'id'        => $p->ID,
            'lang'      => $lang,
            'label'     => mlp_lang_label($lang),
            'status'    => get_post_status($p->ID),
            'editUrl'   => get_edit_post_link($p->ID, 'raw'),
            'isCurrent' => (int) $p->ID === $post_id,
        );
    }

    return array(
        'items'          => $items,
        'availableLangs' => mlp_available_langs(),
    );
}

/**
 * POST /mlp/v1/translations {post_id, lang} — spin off a new-language draft.
 */
function mlp_rest_create_translation(WP_REST_Request $request) {
    $post_id = (int) $request->get_param('post_id');
    $lang    = sanitize_text_field($request->get_param('lang'));

    if (!array_key_exists($lang, mlp_available_langs())) {
        return new WP_Error('mlp_bad_lang', __('Unsupported language.', 'multilingual-post'), array('status' => 400));
    }

    $new_id = mlp_duplicate_post($post_id, $lang);
    if (!$new_id) {
        return new WP_Error(
            'mlp_duplicate_failed',
            __('That language already exists for this post, or it could not be created.', 'multilingual-post'),
            array('status' => 400)
        );
    }

    return array(
        'id'      => $new_id,
        'editUrl' => get_edit_post_link($new_id, 'raw'),
    );
}
