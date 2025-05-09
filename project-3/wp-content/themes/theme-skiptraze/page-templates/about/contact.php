<div class="about_contact">
    <div class="contact_container">
        <div class="heading">
            <div class="heading_title">
                <?php the_field('about_contact_heading');?>
            </div>
        </div>
        <div class="divider"></div>
        <div class="headquarter_contact">
            <?php
                if( have_rows('contact_information_list') ): 
                    while( have_rows('contact_information_list') ) : the_row();       
                        ?>
                            <div class="contact">
                                <div class="title">
                                    <?php the_sub_field('title');?>
                                </div>
                                <div class="details">
                                    <?php the_sub_field('link_details');?>
                                </div>
                                <div class="details-2">
                                    <?php the_sub_field('address');?>
                                </div>
                            </div>
                        <?php
                    endwhile;
                endif; 
            ?>
        </div>
    </div>
</div>