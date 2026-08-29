<?php
/**
 * wp-config.php for the Docker/local dev environment.
 *
 * This file is baked into the image by the Dockerfile (copied in after the
 * WordPress core rsync step) and reads DB connection info from environment
 * variables set by docker-compose.yml, so it never needs hand-editing.
 */

define('DB_NAME', getenv('DB_NAME') ?: 'blog');
define('DB_USER', getenv('DB_USER') ?: 'admin');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'admin');
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// Local-dev-only keys/salts — not security sensitive since this never runs in production.
define('AUTH_KEY', 'local-dev-auth-key');
define('SECURE_AUTH_KEY', 'local-dev-secure-auth-key');
define('LOGGED_IN_KEY', 'local-dev-logged-in-key');
define('NONCE_KEY', 'local-dev-nonce-key');
define('AUTH_SALT', 'local-dev-auth-salt');
define('SECURE_AUTH_SALT', 'local-dev-secure-auth-salt');
define('LOGGED_IN_SALT', 'local-dev-logged-in-salt');
define('NONCE_SALT', 'local-dev-nonce-salt');

$table_prefix = 'wp_';

define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
