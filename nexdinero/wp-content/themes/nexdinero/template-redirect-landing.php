<?php

/* Template Name: Redirect page with wall of offers */
get_header();

?>
<div class="vertical_lines fadeIn">
    <div class="line line-1"></div>
    <div class="line line-2"></div>
    <div class="line line-3"></div>
    <div class="line line-4"></div>
    <div class="line line-5"></div>
</div>
<main>
<section>
        <div class="container">
            <?php get_template_part('templates/heading'); ?>
           
            <?php get_template_part('templates/offers-list/direct-wall-of-offers'); ?>
            
            <?php get_template_part('templates/about-lenders'); ?>
           
            <?php get_template_part('templates/faq'); ?>      
        </div>
    </section>

</main>


<?php get_footer(); ?>