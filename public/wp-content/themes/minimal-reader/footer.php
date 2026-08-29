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

<?php wp_footer(); ?>
</body>
</html>
