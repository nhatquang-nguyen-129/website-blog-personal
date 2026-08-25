<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    $categories = get_the_category();
    $has_group  = (bool) get_post_meta(get_the_ID(), '_ml_group', true);
    ?>

    <main class="container container--narrow">
        <article <?php post_class(); ?>>
            <header class="post-hero">
                <?php if (!empty($categories)) : ?>
                    <div class="post-hero__category">
                        <?php
                        echo esc_html(implode(' · ', wp_list_pluck($categories, 'name')));
                        ?>
                    </div>
                <?php endif; ?>

                <h1 class="post-hero__title"><?php the_title(); ?></h1>

                <div class="post-hero__meta">
                    <span class="author"><?php the_author(); ?></span>
                    <span aria-hidden="true">&middot;</span>
                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                        <?php echo esc_html(get_the_date()); ?>
                    </time>
                </div>

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
    </main>

<?php
endwhile;

get_footer();
