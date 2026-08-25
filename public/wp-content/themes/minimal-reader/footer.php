<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
    <footer class="site-footer">
        <div class="container site-footer__inner">
            <div class="site-footer__brand">
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    <?php bloginfo('name'); ?>
                </a>
                <p class="site-footer__meta">
                    &copy; <?php echo esc_html(gmdate('Y')); ?> <?php bloginfo('name'); ?>
                </p>
            </div>

            <nav aria-label="<?php esc_attr_e('Footer', 'minimal-reader'); ?>">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer',
                    'container'      => false,
                    'items_wrap'     => '<ul>%3$s</ul>',
                    'fallback_cb'    => false,
                ));
                ?>
            </nav>
        </div>
    </footer>

<?php wp_footer(); ?>
</body>
</html>
