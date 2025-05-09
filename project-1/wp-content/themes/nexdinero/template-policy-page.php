<?php

/* Template Name: Terms and Policy page  */
get_header();

?>

<div class="adsense">
  <div class="ad_banner">
    <?php echo get_field('ad_banner_redirect_page', 'option'); ?>
  </div>
</div>
<section class="wall-of-offer-guideline-style-2">
  <div class="guideline-container">
    <?php
        if(have_posts()):
            while(have_posts()): the_post();?>
                <h1><?php the_title(); ?></h1>
            <?php
                
                the_content(); 
            endwhile;
        endif;
    ?>
  </div>
</section>

<div class="adsense">
  <div class="ad_banner">
    <?php echo get_field('ad_banner_redirect_page', 'option'); ?>
  </div>
</div>
<?php get_footer(); ?>