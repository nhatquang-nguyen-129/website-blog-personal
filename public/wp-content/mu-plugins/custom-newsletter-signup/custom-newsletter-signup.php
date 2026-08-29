<?php
if (!defined('ABSPATH')) {
    exit;
}

define('MLNS_PATH', __DIR__);

require_once MLNS_PATH . '/core/db.php';
require_once MLNS_PATH . '/core/render.php';
require_once MLNS_PATH . '/admin/subscribers-page.php';

add_action('rest_api_init', 'mlns_register_rest_routes');
function mlns_register_rest_routes() {
    register_rest_route('mlns/v1', '/subscribe', array(
        'methods'             => 'POST',
        // Public — anyone can submit an email to a signup form, same trust
        // level as a comment form. The honeypot + consent checks below are
        // the actual guards, not auth.
        'permission_callback' => '__return_true',
        'callback'            => 'mlns_rest_subscribe',
        'args'                => array(
            'email'   => array('required' => true, 'type' => 'string'),
            'consent' => array('required' => true, 'type' => 'boolean'),
            'hp'      => array('default' => '', 'type' => 'string'),
        ),
    ));
}

function mlns_rest_subscribe(WP_REST_Request $request) {
    // Honeypot: a real visitor never fills this (it's visually hidden, not
    // just a hidden input, so field-scanning bots that skip display:none
    // still catch it), a bot blindly filling every field on the form will.
    // Report a fake success rather than an error, so a bot doesn't learn
    // this field is being checked.
    if ($request->get_param('hp') !== '') {
        return array('status' => 'subscribed');
    }

    if (!$request->get_param('consent')) {
        return new WP_Error('mlns_consent_required', __('Please agree to the terms before subscribing.', 'newsletter-signup'), array('status' => 400));
    }

    $result = mlns_add_subscriber($request->get_param('email'));
    if (is_wp_error($result)) {
        return $result;
    }

    return array('status' => $result);
}

add_action('wp_enqueue_scripts', 'mlns_enqueue_frontend_assets');
function mlns_enqueue_frontend_assets() {
    // Unconditional — no has_block() check, since there's no block any
    // more. The theme's header.php renders this button on every page
    // simply because this MU-plugin is active (see mlns_render_signup()),
    // so the script always needs to be there too.
    wp_enqueue_script(
        'mlns-newsletter-signup',
        content_url('mu-plugins/custom-newsletter-signup/assets/signup.js'),
        array(),
        filemtime(MLNS_PATH . '/assets/signup.js'),
        true
    );

    // Plain fetch() in signup.js, not wp.apiFetch — avoids pulling in
    // wp-api-fetch's own wp-i18n/wp-hooks dependencies just for one public
    // POST on the frontend. rest_url() itself already adapts to whatever
    // permalink structure the site is using, pretty or plain.
    wp_localize_script('mlns-newsletter-signup', 'mlnsSettings', array(
        'restUrl' => esc_url_raw(rest_url('mlns/v1/subscribe')),
    ));
}
