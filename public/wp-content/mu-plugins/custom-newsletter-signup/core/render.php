<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Called from the theme's header.php — a fixed button in the header's
 * action row, next to Search, on every page. Always there just because
 * this MU-plugin is active; there's no Gutenberg block for it (there used
 * to be one, letting it also be dropped into a page's own content — removed
 * once the header button covered the need on its own, since a block that
 * can't actually be placed *inside* header.php was more confusing than
 * useful once that became the only place this was ever wanted).
 *
 * Renders a compact trigger button; clicking it (assets/signup.js) opens a
 * centered modal with the actual form. $args: heading, buttonLabel,
 * consentText override the defaults below; wrapperAttrs overrides the
 * outer <div>'s attribute string (default `class="mlns-signup"`).
 */
function mlns_render_signup($args = array()) {
    $args = wp_parse_args($args, array(
        'heading'      => __('What’s your email?', 'newsletter-signup'),
        'buttonLabel'  => __('Subscribe', 'newsletter-signup'),
        'consentText'  => __('By subscribing, you agree to receive emails from this site.', 'newsletter-signup'),
        'wrapperAttrs' => 'class="mlns-signup"',
    ));

    $heading_id = wp_unique_id('mlns-heading-');
    $field_id   = wp_unique_id('mlns-email-');

    ob_start();
    ?>
    <div <?php echo $args['wrapperAttrs']; /* phpcs:ignore -- already-escaped attribute string */ ?> data-newsletter-signup>
        <button type="button" class="mlns-trigger" data-newsletter-trigger aria-haspopup="dialog">
            <?php echo esc_html($args['buttonLabel']); ?>
        </button>

        <div class="mlns-modal" data-newsletter-modal hidden>
            <div class="mlns-modal__backdrop" data-newsletter-close></div>
            <div class="mlns-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr($heading_id); ?>">
                <button type="button" class="mlns-modal__close" data-newsletter-close aria-label="<?php esc_attr_e('Close', 'newsletter-signup'); ?>">&times;</button>

                <?php if ($args['heading']) : ?>
                    <h3 id="<?php echo esc_attr($heading_id); ?>" class="mlns-signup__heading"><?php echo wp_kses_post($args['heading']); ?></h3>
                <?php endif; ?>

                <form class="mlns-signup__form" data-newsletter-form novalidate>
                    <div class="mlns-signup__row">
                        <label class="screen-reader-text" for="<?php echo esc_attr($field_id); ?>">
                            <?php esc_html_e('Email', 'newsletter-signup'); ?>
                        </label>
                        <input
                            type="email"
                            id="<?php echo esc_attr($field_id); ?>"
                            name="email"
                            class="mlns-signup__input"
                            placeholder="<?php esc_attr_e('Type your email…', 'newsletter-signup'); ?>"
                            required
                            data-newsletter-email
                        >
                        <button type="submit" class="mlns-signup__button" disabled data-newsletter-submit>
                            <?php echo esc_html($args['buttonLabel']); ?>
                        </button>
                    </div>

                    <label class="mlns-signup__consent">
                        <input type="checkbox" data-newsletter-consent>
                        <span><?php echo wp_kses_post($args['consentText']); ?></span>
                    </label>

                    <!-- Honeypot: invisible to a real visitor (hidden via CSS,
                         not `hidden`/`display:none`, since some bots skip
                         fields a page itself marks non-visible) but a bot
                         that blindly fills every field will fill this one
                         too — non-empty means reject. -->
                    <p class="mlns-signup__hp" aria-hidden="true">
                        <label>
                            <?php esc_html_e('Leave this field empty', 'newsletter-signup'); ?>
                            <input type="text" name="mlns_hp" tabindex="-1" autocomplete="off" data-newsletter-honeypot>
                        </label>
                    </p>

                    <p class="mlns-signup__message" data-newsletter-message hidden></p>
                </form>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
