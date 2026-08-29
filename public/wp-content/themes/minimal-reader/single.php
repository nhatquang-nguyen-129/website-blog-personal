<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    $has_group = (bool) get_post_meta(get_the_ID(), '_ml_group', true);
    ?>

    <main class="container container--narrow">
        <article <?php post_class(); ?>>
            <header class="post-hero">
                <h1 class="post-hero__title"><?php the_title(); ?></h1>

                <?php if (has_post_thumbnail()) : ?>
                    <div class="post-hero__image">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>
            </header>

            <?php if ($has_group) : ?>
                <?php echo do_shortcode('[language_switcher]'); ?>
            <?php endif; ?>

            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>

        <?php if (comments_open() || get_comments_number()) : ?>
            <?php comments_template(); ?>
        <?php endif; ?>
    </main>

<?php
endwhile;

get_footer();
