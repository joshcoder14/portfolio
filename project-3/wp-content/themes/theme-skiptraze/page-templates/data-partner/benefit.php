<section>
    <div class="benefit">
        <div class="benefit_container">
            <div class="feature_heading">
                <?php the_field('benefit_feature_heading');?>
            </div>
            <div class="main_benefits_heading">
                <?php the_field('benefit_feature_title');?>
            </div>

            <div class="benefits_list">
                <?php if( have_rows('benefit_feature_list') ): ?>
                    <?php while( have_rows('benefit_feature_list') ): the_row(); ?>
                        <div class="item">
                            <div class="icon">
                                <img src="<?php the_field('benefit_feature_icon');?>" class="item-icon" alt="benefit icon">
                            </div>
                            <div class="title">
                                <?php the_sub_field('benefit_feature_item_title');?>
                            </div>
                            <div class="sub-text">
                                <?php the_sub_field('benefit_feature_item_subtext');?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>