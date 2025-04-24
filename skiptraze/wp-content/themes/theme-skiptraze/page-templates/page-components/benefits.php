<section>
    <div class="benefit">
        <div class="benefit_container">
            <div class="feature_heading">
                <?php the_field('feature_heading','options');?>
            </div>
            <div class="main_benefits_heading">
                <?php the_field('benefits_heading','options');?> 
            </div>

            <div class="benefits_list">
                <?php if( have_rows('benefits_list','options') ): ?>
                    <?php while( have_rows('benefits_list','options') ): the_row(); ?>
                        <div class="item">
                            <div class="icon">
                                <img src="<?php the_field('benefit_icon','options');?>" class="item-icon" alt="benefit icon">
                            </div>
                            <div class="title">
                                <?php the_sub_field('benefits_item_title','options');?>
                            </div>
                            <div class="sub-text">
                                <?php the_sub_field('benefit_subtext','options');?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>