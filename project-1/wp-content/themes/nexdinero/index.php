<?php get_header(); ?>

<main class="navbar-offset">
    <section class="container">
        <?php
        while (have_posts()) : the_post(); ?>

            <!-- <h3><?php the_title(); ?></h3> -->

            <?php the_content(); ?>
            <!-- <?php wp_link_pages(); ?> -->

        <?php endwhile; ?>

        <?php
        if (get_next_posts_link()) {
            next_posts_link();
        }
        ?>
        <?php
        if (get_previous_posts_link()) {
            previous_posts_link();
        }
        ?>
    </section>
</main>

<?php get_footer(); ?>