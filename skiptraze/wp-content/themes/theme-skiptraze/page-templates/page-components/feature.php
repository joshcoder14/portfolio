
<div class="feature_container">
    <div class="feature-list">
        <?php if( have_rows('feature','options') ): ?>
            <?php while( have_rows('feature','options') ): the_row(); ?>
                <div class="item">
                    <div class="icon">
                        <img src="<?php the_sub_field('icon','options');?>" class="item-icon" alt="mission icon">
                    </div>
                    <div class="title">
                        <?php the_sub_field('title','options');?>
                    </div>
                    <div class="sub-text">
                        <?php the_sub_field('sub-text','options');?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>