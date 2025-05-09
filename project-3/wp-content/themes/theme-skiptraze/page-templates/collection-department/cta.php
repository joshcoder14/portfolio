<div class="cta">
    <div class="container">
        <div class="heading">
            <div class="heading-wrapper">
                <p class="text"><?php the_field('cta_heading');?></p>
            </div>
        </div>
        <div class="action-btn">
            <a href="<?php the_field('collection_button_link3');?>" class="button">
                <span class="label">
                    <?php the_field('collection_button_label3');?>
                </span>
            </a>
        </div>
    </div>
    <img src="<?php echo get_template_directory_uri(); ?>/images/bg-sample.svg" alt="background image" class="left-image">
    <img src="<?php echo get_template_directory_uri(); ?>/images/bg-1.png" alt="background image" class="right-image">
</div>