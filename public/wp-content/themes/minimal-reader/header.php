<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
    (function () {
        try {
            var stored = localStorage.getItem('mlr-theme');
            var theme = stored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
        } catch (e) {
            document.documentElement.setAttribute('data-theme', 'light');
        }
    })();
    </script>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
    <div class="container site-header__top">
        <a class="site-title" href="<?php echo esc_url(home_url('/')); ?>">
            <?php if (has_custom_logo()) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <?php bloginfo('name'); ?>
            <?php endif; ?>
        </a>

        <div class="site-header__actions">
            <div class="site-header__search">
                <div class="site-header__search-field" data-search-panel>
                    <?php get_search_form(); ?>
                </div>

                <button type="button" class="site-header__icon-btn" data-search-toggle aria-haspopup="true" aria-expanded="false" aria-label="<?php esc_attr_e('Search', 'minimal-reader'); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
            </div>

            <div class="site-header__share">
                <button type="button" class="site-header__icon-btn" data-share-toggle aria-haspopup="true" aria-expanded="false" aria-label="<?php esc_attr_e('Share', 'minimal-reader'); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg>
                </button>

                <div class="site-header__share-menu" data-share-menu role="menu" hidden>
                    <button type="button" class="site-header__share-item" data-share-action="copy" role="menuitem">
                        <span class="site-header__share-icon" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                        </span>
                        <span class="site-header__share-label"><?php esc_html_e('Copy link', 'minimal-reader'); ?></span>
                    </button>

                    <button type="button" class="site-header__share-item" data-share-action="email" role="menuitem">
                        <span class="site-header__share-icon" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 6-10 7L2 6"></path></svg>
                        </span>
                        <span class="site-header__share-label"><?php esc_html_e('Send as email', 'minimal-reader'); ?></span>
                    </button>

                    <div class="site-header__share-divider" role="separator"></div>

                    <button type="button" class="site-header__share-item" data-share-action="facebook" role="menuitem">
                        <span class="site-header__share-icon site-header__share-icon--facebook" aria-hidden="true">f</span>
                        <span class="site-header__share-label"><?php esc_html_e('Share to Facebook', 'minimal-reader'); ?></span>
                    </button>

                    <button type="button" class="site-header__share-item" data-share-action="linkedin" role="menuitem">
                        <span class="site-header__share-icon site-header__share-icon--linkedin" aria-hidden="true">in</span>
                        <span class="site-header__share-label"><?php esc_html_e('Share to LinkedIn', 'minimal-reader'); ?></span>
                    </button>

                    <button type="button" class="site-header__share-item" data-share-action="bluesky" role="menuitem">
                        <span class="site-header__share-icon site-header__share-icon--bluesky" aria-hidden="true">🦋</span>
                        <span class="site-header__share-label"><?php esc_html_e('Share to Bluesky', 'minimal-reader'); ?></span>
                    </button>

                    <button type="button" class="site-header__share-item" data-share-action="x" role="menuitem">
                        <span class="site-header__share-icon site-header__share-icon--x" aria-hidden="true">𝕏</span>
                        <span class="site-header__share-label"><?php esc_html_e('Share to X', 'minimal-reader'); ?></span>
                    </button>
                </div>
            </div>

            <a class="site-header__subscribe" href="<?php echo esc_url(get_feed_link()); ?>">
                <?php esc_html_e('Subscribe', 'minimal-reader'); ?>
            </a>

            <?php if (!is_user_logged_in()) : ?>
                <a class="site-header__signin" href="<?php echo esc_url(wp_login_url()); ?>">
                    <?php esc_html_e('Sign in', 'minimal-reader'); ?>
                </a>
            <?php endif; ?>

            <button type="button" class="theme-toggle" data-theme-toggle aria-label="<?php esc_attr_e('Toggle dark mode', 'minimal-reader'); ?>">
                <span aria-hidden="true">&#9788;</span>
            </button>
        </div>
    </div>

    <?php if (has_nav_menu('primary')) : ?>
        <nav class="site-header__nav container" aria-label="<?php esc_attr_e('Primary', 'minimal-reader'); ?>">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'container'      => false,
                'items_wrap'     => '<ul>%3$s</ul>',
                'fallback_cb'    => false,
            ));
            ?>
        </nav>
    <?php endif; ?>
</header>
