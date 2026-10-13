<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
    <footer class="site-footer">
        <div class="container site-footer__inner">
            <p class="site-footer__meta">
                &copy; <?php echo esc_html(gmdate('Y')); ?> <?php bloginfo('name'); ?>
            </p>

            <?php if (get_privacy_policy_url() || has_nav_menu('footer')) : ?>
                <nav class="site-footer__links" aria-label="<?php esc_attr_e('Footer', 'minimal-reader'); ?>">
                    <ul>
                        <?php if (get_privacy_policy_url()) : ?>
                            <li><?php the_privacy_policy_link(); ?></li>
                        <?php endif; ?>
                        <?php
                        wp_nav_menu(array(
                            'theme_location' => 'footer',
                            'container'      => false,
                            'items_wrap'     => '%3$s',
                            'fallback_cb'    => false,
                        ));
                        ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </footer>

    <?php // Shown/hidden by assets/js/back-to-top.js past a scroll threshold — see
    // that file for why. Fixed overlay, not part of .site-footer's own content
    // flow, so it's placed here rather than inside <footer> above. ?>
    <button type="button" class="back-to-top" data-back-to-top aria-label="<?php esc_attr_e('Back to top', 'minimal-reader'); ?>">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>
    </button>

<?php wp_footer(); ?>
</body>
</html>
