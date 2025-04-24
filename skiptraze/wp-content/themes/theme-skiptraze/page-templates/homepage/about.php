<section>
    <div class="about_feature">
        <div class="about_feature-container">
            <div class="about">
                <div class="about_content">
                    <div class="content">
                        <div class="about_header"><?php the_field('about_section_header');?></div>
                        <div class="about_title"><?php the_field('about_section_title');?></div>
                        <div class="about_subtext">
                            <?php the_field('about_section_subtext');?>
                        </div>
                    </div>
                    <a href="<?php the_field('read_more_link');?>" class="about_read-more">
                        <?php the_field('read_more_label');?>
                    </a>
                </div>
                <div class="about_image">
                    <img src="<?php the_field('device_image');?>" class="about-img" alt="about image">
                </div>
            </div>
            <?php get_template_part('page-templates/page-components/feature'); ?>
        </div>
    </div>
</section>