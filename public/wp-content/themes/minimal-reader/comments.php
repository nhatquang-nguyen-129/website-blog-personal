<?php
if (!defined('ABSPATH')) {
    exit;
}

// A post with password protection never shows its comments either, same
// as it never shows its content — matches WordPress's own convention.
if (post_password_required()) {
    return;
}

$commenter = wp_get_current_commenter();
?>

<div id="comments" class="comments-area">

    <?php if (have_comments()) : ?>
        <h2 class="comments-title">
            <?php
            $comment_count = get_comments_number();
            printf(
                /* translators: %s: number of comments */
                esc_html(_n('%s comment', '%s comments', $comment_count, 'minimal-reader')),
                esc_html(number_format_i18n($comment_count))
            );
            ?>
        </h2>

        <ol class="comment-list">
            <?php
            wp_list_comments(array(
                'style'      => 'ol',
                'short_ping' => true,
                'avatar_size' => 40,
            ));
            ?>
        </ol>

        <?php the_comments_pagination(); ?>
    <?php endif; ?>

    <?php if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')) : ?>
        <p class="no-comments"><?php esc_html_e('Comments are closed.', 'minimal-reader'); ?></p>
    <?php endif; ?>

    <?php
    comment_form(array(
        'class_form'    => 'comment-form',
        'title_reply'   => __('Leave a comment', 'minimal-reader'),
        // No separate "Website" field — rarely used, and keeping the form
        // to just Comment/Name/Email is closer to the social-style comment
        // boxes this is modeled on.
        'fields'        => array(
            'author' => '<p class="comment-form-author"><label for="author" class="screen-reader-text">' . __('Name', 'minimal-reader') . '</label>'
                . '<input id="author" name="author" type="text" placeholder="' . esc_attr__('Name', 'minimal-reader') . '" value="' . esc_attr($commenter['comment_author']) . '" size="30" maxlength="245" required /></p>',
            'email'  => '<p class="comment-form-email"><label for="email" class="screen-reader-text">' . __('Email', 'minimal-reader') . '</label>'
                . '<input id="email" name="email" type="email" placeholder="' . esc_attr__('Email', 'minimal-reader') . '" value="' . esc_attr($commenter['comment_author_email']) . '" size="30" maxlength="100" required /></p>',
        ),
        'comment_field' => '<p class="comment-form-comment"><label for="comment" class="screen-reader-text">' . __('Comment', 'minimal-reader') . '</label>'
            . '<textarea id="comment" name="comment" placeholder="' . esc_attr__('Add a comment…', 'minimal-reader') . '" cols="45" rows="4" maxlength="65525" required></textarea></p>',
        'label_submit'  => __('Post comment', 'minimal-reader'),
        'class_submit'  => 'comment-form__submit',
    ));
    ?>

</div>
