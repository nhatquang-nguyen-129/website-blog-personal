<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Full-page takeover, same idea as the old Payload MaintenancePage
 * component (replaces the whole body, no header/footer, bypassed only for
 * a logged-in administrator). Self-styled rather than pulling in the active
 * theme's stylesheet — this needs to render reasonably no matter what theme
 * is active or what state it's in.
 */
add_action('template_redirect', 'mlmm_maybe_show_maintenance_page');
function mlmm_maybe_show_maintenance_page() {
    if (!mlmm_is_enabled()) {
        return;
    }

    if (current_user_can('manage_options')) {
        return;
    }

    mlmm_render_maintenance_page();
    exit;
}

function mlmm_render_maintenance_page() {
    $options = mlmm_get_options();

    status_header(503);
    header('Retry-After: 3600');
    ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html($options['title'] . ' — ' . get_bloginfo('name')); ?></title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fdfaf7;
            color: #2b2622;
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            text-align: center;
            padding: 2rem;
            box-sizing: border-box;
        }
        .mlmm-card {
            max-width: 32rem;
        }
        .mlmm-card__brand {
            margin-bottom: 1.5rem;
            font-size: 0.8125rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #c2703f;
        }
        .mlmm-card h1 {
            margin: 0 0 1rem;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 2rem;
        }
        .mlmm-card p {
            margin: 0;
            font-size: 1.0625rem;
            line-height: 1.6;
            color: #6b6259;
        }
    </style>
</head>
<body>
    <div class="mlmm-card">
        <div class="mlmm-card__brand"><?php bloginfo('name'); ?></div>
        <h1><?php echo esc_html($options['title']); ?></h1>
        <?php echo wp_kses_post(wpautop($options['message'])); ?>
    </div>
</body>
</html>
    <?php
}
