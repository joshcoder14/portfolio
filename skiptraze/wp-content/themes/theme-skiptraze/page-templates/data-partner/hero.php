<div class="department data">
    <div class="container">
        <div class="content data_partner">
            <div class="text">
                <div class="heading-overline">
                    <div class="overline">
                        <?php the_field('data_department_heading');?>
                    </div>
                    <div class="heading">
                        <?php the_field('data_department_title');?>
                    </div>
                </div>
                <div class="supporting-text">
                    <?php the_field('data_department_subtext');?>
                </div>
            </div>
            <div class="actions">
                <a href="mailto:<?php the_field('data_button','options');?>" class="button">
                    <span class="label"><?php the_field('data_button_label','options');?></span>
                </a>
            </div>
        </div>
        <div class="features">
            <?php get_template_part('page-templates/page-components/data-feature-list'); ?>
        </div>
    </div>
    <img src="<?php echo get_template_directory_uri(); ?>/images/3.svg" alt="background image" class="left-image-data">
    <img src="<?php echo get_template_directory_uri(); ?>/images/1.svg" alt="background image" class="right-image-data">
</div>