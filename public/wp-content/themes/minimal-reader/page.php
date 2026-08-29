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
            <header class="post-hero">
                <h1 class="post-hero__title"><?php the_title(); ?></h1>
            </header>

            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>
    </main>

<?php
endwhile;

get_footer();
