<div class="questions data">
    <div class="accordion">
        <?php
            if( have_rows('dp_faq_accordion','options') ):
                while( have_rows('dp_faq_accordion','options') ) : the_row();
                    ?>
                        <div class="accordion-card">
                            <div class="accordion-header">
                                <p><?php the_sub_field('dp_faq_question','options');?></p>
                                <img src="<?php the_field('accordion_icon','options');?>" class="arrow-up up" alt="">
                            </div>
                            <div class="accordion-body">
                                <div class="content">
                                    <?php the_sub_field('dp_faq_answer','options');?>
                                </div>
                            </div>
                        </div>
                    <?php
                endwhile;
            endif;
        ?>
    </div>
</div>