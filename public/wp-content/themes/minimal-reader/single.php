<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    ?>

    <main class="container container--narrow">
        <article <?php post_class(); ?>>
            <?php // "Available in other languages" picker from
            // custom-multilingual-post — renders nothing on a post without
            // another published language. ?>
            <?php if (function_exists('mlp_render_language_picker')) : ?>
                <?php echo mlp_render_language_picker(); ?>
            <?php endif; ?>

            <header class="post-hero">
                <h1 class="post-hero__title"><?php the_title(); ?></h1>

                <?php if (has_post_thumbnail()) : ?>
                    <div class="post-hero__image">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>
            </header>

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
