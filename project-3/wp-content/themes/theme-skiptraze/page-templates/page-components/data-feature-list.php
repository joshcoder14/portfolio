<div class="feature_list">
    <?php
        $counter = 0;
        if( have_rows('special_feature_list','options') ):
            while( have_rows('special_feature_list','options') ) : the_row();
                if($counter >= 3 && $counter < 6) {
                    ?>
                        <div class="feature_items">
                            <img src="<?php the_field('feature_item_icon','options');?>" alt="check icon" class="check-icon">
                            <div class="feature_item">
                                <?php the_sub_field('item_text','options');?>
                            </div>
                        </div>
                    <?php
                }
                $counter++;
                    
            endwhile;
        endif;
    ?>
</div>