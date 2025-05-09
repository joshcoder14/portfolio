<?php

/* Template Name: Terms Policy Template */

get_header('layout');
?>

<section class="s-terms">
    
    <div class="background_cover">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/image1.png" alt="">
    </div>
    <div class="s-terms_container">
        <div class="heading">
            <!-- Pagina de inicio -->
            <div class="goto_home">
                <a href="<?php echo get_home_url(); ?>">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/arrow-right.svg" alt="arrow left">
                    Pagina de inicio
                </a>
            </div>
            <a href="<?php echo get_home_url(); ?>" class="logo">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo/logo.svg" alt="logo">
            </a>
            <div class="area"></div>
        </div>
        <div class="content">
            <?php the_title('<h1>', '</h1>'); ?>
             
            <?php the_content(); ?>
        </div>
    </div>
</section>

<?php get_footer('form'); ?>