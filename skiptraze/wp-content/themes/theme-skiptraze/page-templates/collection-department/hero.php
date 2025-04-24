<div class="department">
    <div class="container">
        <div class="content">
            <div class="text">
                <div class="heading-overline">
                    <div class="overline">
                        <?php the_field('collection_department_heading');?>
                    </div>
                    <div class="heading">
                        <?php the_field('collection_department_title');?>
                    </div>
                </div>
                <div class="supporting-text">
                    <?php the_field('collection_department_subtext');?>
                </div>
            </div>
            <div class="actions">
                <a href="<?php the_field('collection_button_link1');?>" class="button">
                    <span class="label"><?php the_field('collection_button_label1');?></span>
                </a>
            </div>
        </div>
        <div class="features"> 
            <?php get_template_part('page-templates/page-components/collection-feature-list'); ?>
        </div>
    </div>
    <img src="<?php echo get_template_directory_uri(); ?>/images/bg-1.png" alt="background image" class="left-image-collect">
    <img src="<?php echo get_template_directory_uri(); ?>/images/bg-sample.svg" alt="background image" class="right-image-collect">
</div>