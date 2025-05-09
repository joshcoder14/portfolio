<?php

/* Template Name: Redirect page */
get_header();

?>

<div class="adsense">
  <div class="ad_banner">
    <?php echo get_field('ad_banner_redirect_page', 'option'); ?>
  </div>
</div>
<div class="continue-btn" style="margin-top: 20px;">
  <a href="#" class="continue-to-offer-wall">
    Continuar a la oferta
  </a>
</div>
<section class="wall-of-offer-guideline-style-2">
  <div class="guideline-container">
    <?php
        if(have_posts()):
            while(have_posts()): the_post();
                the_content(); 
            endwhile;
        endif;
    ?>
  </div>
</section>

<div class="continue-btn">
  <a href="#" class="continue-to-offer-wall">
    Continuar a la oferta
  </a>
</div>

<script>
  jQuery(document).ready(function ($) {
    $('.continue-to-offer-wall').on('click', function (e) {
      e.preventDefault();
      document.cookie = "user_continue=true; path=/; max-age=" + 60 * 60 * 24 * 365; // 1 year expiry
      window.location.href = "<?php echo esc_url(home_url('?sub1=1')); ?>";
    });
  });
</script>


<div class="adsense">
  <div class="ad_banner">
    <?php echo get_field('ad_banner_redirect_page', 'option'); ?>
  </div>
</div>
<?php //get_footer(); ?>