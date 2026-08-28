<?php
if (!defined('ABSPATH')) {
    exit;
}

define('MLNS_DB_VERSION', '1.0');

function mlns_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'mlns_subscribers';
}

/**
 * MU-plugins never fire register_activation_hook() — they're not "activated"
 * through the normal plugin mechanism, they just always load — so the usual
 * "create the table on activation" pattern doesn't apply here. Checking a
 * stored version number on every request is the standard workaround: cheap
 * (one options lookup) on every load after the first, and only actually
 * runs dbDelta() when the version doesn't match (a fresh install, or a
 * future schema change that bumps MLNS_DB_VERSION).
 */
add_action('init', 'mlns_maybe_create_table');
function mlns_maybe_create_table() {
    if (get_option('mlns_db_version') === MLNS_DB_VERSION) {
        return;
    }

    global $wpdb;
    $table           = mlns_table_name();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        email VARCHAR(255) NOT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY email (email)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    update_option('mlns_db_version', MLNS_DB_VERSION);
}

/**
 * Returns 'subscribed', 'already_subscribed', or a WP_Error on invalid
 * input. Never exposes whether an email exists via a different error vs.
 * success shape — 'already_subscribed' still reads as a success to the
 * visitor (see assets/signup.js), it just doesn't insert a second row.
 */
function mlns_add_subscriber($email) {
    $email = sanitize_email($email);
    if (!$email || !is_email($email)) {
        return new WP_Error('mlns_invalid_email', __('That email address doesn’t look right.', 'newsletter-signup'), array('status' => 400));
    }

    global $wpdb;
    $table = mlns_table_name();

    $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE email = %s", $email));
    if ($exists) {
        return 'already_subscribed';
    }

    $inserted = $wpdb->insert(
        $table,
        array(
            'email'      => $email,
            'created_at' => current_time('mysql'),
        ),
        array('%s', '%s')
    );

    if (!$inserted) {
        return new WP_Error('mlns_db_error', __('Something went wrong — try again in a moment.', 'newsletter-signup'), array('status' => 500));
    }

    return 'subscribed';
}

function mlns_get_subscribers() {
    global $wpdb;
    $table = mlns_table_name();
    return $wpdb->get_results("SELECT email, created_at FROM $table ORDER BY created_at DESC");
}

function mlns_count_subscribers() {
    global $wpdb;
    $table = mlns_table_name();
    return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
}
