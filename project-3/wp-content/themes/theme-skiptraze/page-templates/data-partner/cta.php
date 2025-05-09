<div class="cta data">
    <div class="container">
        <div class="heading">
            <div class="heading-wrapper">
                <p class="text">
                    <?php the_field('data_cta_heading');?>
                </p>
            </div>
        </div>
        <div class="action-btn">
            <a href="mailto:<?php the_field('data_button','options');?>" class="button">
                <span class="label">
                    <?php the_field('data_button_label','options');?>
                </span>
            </a>
        </div>
    </div>
    <img src="<?php echo get_template_directory_uri(); ?>/images/3.svg" alt="background image" class="left-image">
    <img src="<?php echo get_template_directory_uri(); ?>/images/2.svg" alt="background image" class="right-image">
</div>