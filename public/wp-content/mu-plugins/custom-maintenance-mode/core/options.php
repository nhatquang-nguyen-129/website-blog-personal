<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Everything lives in one option (`mlmm_options`) rather than three separate
 * ones, matching how the old Payload "maintenance" global bundled
 * enabled/title/message together.
 */
function mlmm_get_options() {
    $defaults = array(
        'enabled' => false,
        'title'   => __('We’ll be right back', 'maintenance-mode'),
        'message' => __('The site is undergoing scheduled maintenance. Please check back soon.', 'maintenance-mode'),
    );

    $saved = get_option('mlmm_options', array());

    return wp_parse_args(is_array($saved) ? $saved : array(), $defaults);
}

function mlmm_is_enabled() {
    $options = mlmm_get_options();
    return !empty($options['enabled']);
}
