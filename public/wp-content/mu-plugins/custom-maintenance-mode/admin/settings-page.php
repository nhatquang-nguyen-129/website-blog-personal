<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'mlmm_add_settings_page');
function mlmm_add_settings_page() {
    add_options_page(
        __('Maintenance Mode', 'maintenance-mode'),
        __('Maintenance Mode', 'maintenance-mode'),
        'manage_options',
        'mlmm-maintenance-mode',
        'mlmm_render_settings_page'
    );
}

add_action('admin_init', 'mlmm_register_settings');
function mlmm_register_settings() {
    register_setting('mlmm_options_group', 'mlmm_options', array(
        'type'              => 'array',
        'sanitize_callback' => 'mlmm_sanitize_options',
        'default'           => array(),
    ));

    add_settings_section('mlmm_main', '', '__return_false', 'mlmm-maintenance-mode');

    add_settings_field('mlmm_enabled', __('Status', 'maintenance-mode'), 'mlmm_field_enabled', 'mlmm-maintenance-mode', 'mlmm_main');
    add_settings_field('mlmm_title', __('Title', 'maintenance-mode'), 'mlmm_field_title', 'mlmm-maintenance-mode', 'mlmm_main');
    add_settings_field('mlmm_message', __('Message', 'maintenance-mode'), 'mlmm_field_message', 'mlmm-maintenance-mode', 'mlmm_main');
}

function mlmm_sanitize_options($input) {
    $defaults = mlmm_get_options();

    return array(
        'enabled' => !empty($input['enabled']),
        'title'   => isset($input['title']) ? sanitize_text_field($input['title']) : $defaults['title'],
        'message' => isset($input['message']) ? sanitize_textarea_field($input['message']) : $defaults['message'],
    );
}

function mlmm_field_enabled() {
    $options = mlmm_get_options();
    echo '<label><input type="checkbox" name="mlmm_options[enabled]" value="1"' . checked(!empty($options['enabled']), true, false) . '> ';
    echo esc_html__('Show the maintenance page to everyone except logged-in administrators.', 'maintenance-mode');
    echo '</label>';
}

function mlmm_field_title() {
    $options = mlmm_get_options();
    echo '<input type="text" class="regular-text" name="mlmm_options[title]" value="' . esc_attr($options['title']) . '">';
}

function mlmm_field_message() {
    $options = mlmm_get_options();
    echo '<textarea class="large-text" rows="4" name="mlmm_options[message]">' . esc_textarea($options['message']) . '</textarea>';
}

function mlmm_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Maintenance Mode', 'maintenance-mode'); ?></h1>

        <?php if (mlmm_is_enabled()) : ?>
            <div class="notice notice-warning">
                <p>
                    <?php esc_html_e('Maintenance mode is currently ON — visitors (other than logged-in administrators) see the maintenance page instead of the site.', 'maintenance-mode'); ?>
                </p>
            </div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php
            settings_fields('mlmm_options_group');
            do_settings_sections('mlmm-maintenance-mode');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
