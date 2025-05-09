<div class="how-it-works">
    <div class="container">
        <div class="frame">
            <div class="text">
                <p class="heading">
                    <?php the_field('how_it_works_title');?>
                </p>
                <?php
                    if( have_rows('how_it_works_list') ):
                        while( have_rows('how_it_works_list') ): the_row();
                            ?>
                                <div class="list-item">
                                    <img src="<?php the_field('how_it_works_item_icon');?>" alt="check icon" class="check-icon">
                                    <p class="text-wrapper"><?php the_sub_field('how_it_works_item');?></p>
                                </div>
                            <?php
                        endwhile;
                    endif;
                ?>
            </div>
        </div>
        <div class="content">
            <?php
                if( have_rows('how_it_works_steps') ):
                    while( have_rows('how_it_works_steps') ): the_row();
                        ?>
                            <div class="item">
                                <img src="<?php the_sub_field('steps_icon');?>" alt="check icon" class="media-icon">
                                <div class="text-content">
                                    <div class="text"><?php the_sub_field('steps_item');?></div>
                                </div>
                            </div>
                        <?php
                    endwhile;
                endif;
            ?>
        </div>
    </div>
</div>
