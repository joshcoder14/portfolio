<section>
    <div class="hero-home">
        <div class="hero_container">
            <div class="hero_content">
                <div class="content_wrapper">
                    <div class="support_text">
                        <?php the_field('collection_supporting_text');?>
                    </div>
                    <div class="heading_text">
                        <?php the_field('collection_heading_text');?>
                    </div>
                    <div class="sub_heading">
                        <?php the_field('collection_sub_text');?>
                    </div>
                    <a href="<?php the_field('collection_button_link');?>" class="hero_btn">
                        <?php the_field('collection_button_label_homepage');?>
                    </a>
                </div>
            </div>
            <div class="feature_content" style="background-image: url('<?php echo the_field('collection_special_feature_image','options'); ?>')"></div>
        </div>
        <div class="hero_container">
            <div class="hero_content">
                <div class="content_wrapper">
                    <div class="support_text">
                        <?php the_field('data_supporting_text');?>
                    </div>
                    <div class="heading_text">
                        <?php the_field('Data_heading_text');?>
                    </div>
                    <div class="sub_heading">
                        <?php the_field('data_sub_text');?>
                    </div>
                    <a href="<?php the_field('data_button_link');?>" class="hero_btn">
                        <?php the_field('data_button_label','options');?>
                    </a>
                </div>
                <img src="<?php echo get_template_directory_uri(); ?>/images/hero-bg-overlay-2.png" alt="background image" class="gray-image">
            </div>
            <div class="feature_content" style="background-image: url('<?php echo the_field('data_special_feature_image','options'); ?>')"></div>
        </div>
    </div>
</section>