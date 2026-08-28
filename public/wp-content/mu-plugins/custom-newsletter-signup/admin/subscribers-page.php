<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Without some way to actually read the collected emails back out, this
 * whole plugin would just be quietly writing rows nobody can ever reach —
 * a plain list + CSV export is the minimum for the collected leads to be
 * usable at all, not an extra feature bolted on top of "just collect it".
 */
add_action('admin_menu', 'mlns_register_admin_page');
function mlns_register_admin_page() {
    add_options_page(
        __('Newsletter Subscribers', 'newsletter-signup'),
        __('Newsletter Subscribers', 'newsletter-signup'),
        'manage_options',
        'mlns-subscribers',
        'mlns_render_admin_page'
    );
}

function mlns_render_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $subscribers = mlns_get_subscribers();
    $export_url  = wp_nonce_url(admin_url('admin-post.php?action=mlns_export_csv'), 'mlns_export_csv');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Newsletter Subscribers', 'newsletter-signup'); ?></h1>
        <p>
            <?php echo esc_html(sprintf(
                /* translators: %d: number of subscribers */
                _n('%d subscriber collected so far.', '%d subscribers collected so far.', count($subscribers), 'newsletter-signup'),
                count($subscribers)
            )); ?>
        </p>

        <?php if ($subscribers) : ?>
            <p><a href="<?php echo esc_url($export_url); ?>" class="button button-primary"><?php esc_html_e('Export CSV', 'newsletter-signup'); ?></a></p>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Email', 'newsletter-signup'); ?></th>
                        <th><?php esc_html_e('Subscribed', 'newsletter-signup'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscribers as $s) : ?>
                        <tr>
                            <td><?php echo esc_html($s->email); ?></td>
                            <td><?php echo esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $s->created_at)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p><?php esc_html_e('No one has subscribed yet.', 'newsletter-signup'); ?></p>
        <?php endif; ?>
    </div>
    <?php
}

add_action('admin_post_mlns_export_csv', 'mlns_export_csv');
function mlns_export_csv() {
    if (!current_user_can('manage_options') || !check_admin_referer('mlns_export_csv')) {
        wp_die(esc_html__('Not allowed.', 'newsletter-signup'));
    }

    $subscribers = mlns_get_subscribers();

    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=newsletter-subscribers-' . gmdate('Y-m-d') . '.csv');

    $out = fopen('php://output', 'w');
    fputcsv($out, array('email', 'subscribed_at'));
    foreach ($subscribers as $s) {
        fputcsv($out, array($s->email, $s->created_at));
    }
    fclose($out);
    exit;
}
