<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main class="container container--narrow">
    <?php if (is_home() && !is_front_page()) : ?>
        <h1 class="archive-title"><?php single_post_title(); ?></h1>
    <?php elseif (is_archive()) : ?>
        <h1 class="archive-title"><?php the_archive_title(); ?></h1>
    <?php elseif (is_search()) : ?>
        <h1 class="archive-title">
            <?php printf(esc_html__('Search results for: %s', 'minimal-reader'), '<em>' . get_search_query() . '</em>'); ?>
        </h1>
    <?php endif; ?>

    <?php if (have_posts()) : ?>
        <div class="post-list">
            <?php while (have_posts()) : the_post();
                $has_thumb = has_post_thumbnail();
                ?>
                <article class="post-list__item<?php echo $has_thumb ? ' has-thumb' : ''; ?>">
                    <div class="post-list__body">
                        <h2 class="post-list__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        <div class="post-list__meta">
                            <?php echo esc_html(get_the_date()); ?>
                        </div>
                        <div class="post-list__excerpt"><?php the_excerpt(); ?></div>
                    </div>

                    <?php if ($has_thumb) : ?>
                        <a class="post-list__thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
                            <?php the_post_thumbnail('mlr-list-thumb', array('class' => 'post-list__image', 'alt' => '')); ?>
                        </a>
                    <?php endif; ?>
                </article>
            <?php endwhile; ?>
        </div>

        <nav class="container pagination">
            <div><?php next_posts_link(esc_html__('&larr; Older posts', 'minimal-reader')); ?></div>
            <div><?php previous_posts_link(esc_html__('Newer posts &rarr;', 'minimal-reader')); ?></div>
        </nav>
    <?php else : ?>
        <p><?php esc_html_e('Nothing here yet.', 'minimal-reader'); ?></p>
    <?php endif; ?>
</main>

<?php
get_footer();
