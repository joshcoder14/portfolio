<div class="team">
    <div class="team_container">
        <div class="heading">
            <div class="heading_text">
                <?php the_field('team_heading');?>
            </div>
        </div>

        <div class="team_details swiper">
            <div class="team_slider_desktop">
                <div class="row swiper-wrapper">
                    <?php
                        if( have_rows('team_list') ):
                            while( have_rows('team_list') ) : the_row();
                                ?>
                                    <div class="employee_details swiper-slide">
                                        <div class="image">
                                            <?php
                                                $image_url = get_field('image');

                                                if(!$image_url) {
                                                    $image_url = get_template_directory_uri() . '/images/team/image-21.png';
                                                }
                                            ?>
                                            <img src="<?php echo esc_url($image_url); ?>" alt="team image" class="team-image">
                                        </div>
                                        <div class="information">
                                            <div class="employee">
                                                <div class="name">
                                                    <?php the_sub_field('name');?>
                                                </div>
                                                <div class="designation">
                                                    <?php the_sub_field('designation');?>
                                                </div>
                                            </div>
                                            <div class="text_wrapper">
                                                <?php the_sub_field('description');?>
                                            </div>
                                            <div class="link">
                                                <a href="mailto:<?php the_sub_field('email');?>"><?php the_sub_field('email');?></a>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                            endwhile;
                        endif; 
                    ?>
                </div>
            </div>
            <div class="team_slider_mobile">
                <div class="row swiper-wrapper">
                    <?php
                        if( have_rows('team_list') ):
                            while( have_rows('team_list') ) : the_row();
                                ?>
                                    <div class="employee_details swiper-slide">
                                        <div class="image">
                                            <?php
                                                $image_url = get_field('image');

                                                if(!$image_url) {
                                                    $image_url = get_template_directory_uri() . '/images/team/image-21.png';
                                                }
                                            ?>
                                            <img src="<?php echo esc_url($image_url); ?>" alt="team image" class="team-image">
                                        </div>
                                        <div class="information">
                                            <div class="employee">
                                                <div class="name">
                                                    <?php the_sub_field('name');?>
                                                </div>
                                                <div class="designation">
                                                    <?php the_sub_field('designation');?>
                                                </div>
                                            </div>
                                            <div class="text_wrapper">
                                                <?php the_sub_field('description');?>
                                            </div>
                                            <div class="link">
                                                <a href="mailto:<?php the_sub_field('email');?>"><?php the_sub_field('email');?></a>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                            endwhile;
                        endif; 
                    ?>
                </div>
            </div>
            <div class="arrow_button">
                <div class="prev">
                    <img src="<?php the_field('previous_arrow');?>" alt="prev icon" class="prev-icon">
                </div>
                <div class="next">
                    <img src="<?php the_field('next_arrow');?>" alt="next icon" class="next-icon">
                </div>
            </div>
        </div>
    </div>
</div>