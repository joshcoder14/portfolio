<section class="testimonial">
    <div class="container">
        <div class="testimonial_heading">
            <!-- Testimonials -->
            <?php the_field('testimonial_title','options');?>
        </div>
        <div class="slide-container swiper">
            <div class="slide-content">
                <div class="card-wrapper swiper-wrapper">
                    <?php
                        if( have_rows('registered_testimonials','options') ):
                            while( have_rows('registered_testimonials','options') ) : the_row();
                                ?>
                                    <div class="card swiper-slide">
                                        <div class="testimonial_text">
                                            <?php the_sub_field('testimonial_text','options');?>
                                        </div>
                                        <div class="testimonial_profile">
                                            <div class="testimonial_image">
                                                <?php
                                                    $testimonial_image_url = get_sub_field('testimonial_photo', 'options');

                                                    if (!$testimonial_image_url) {
                                                        $testimonial_image_url = get_template_directory_uri() . '/images/testimonial/testimonial-default-image.png';
                                                    }
                                                ?>
                                                <img src="<?php echo esc_url($testimonial_image_url); ?>" alt="team image" class="team-image">
                                            </div>
                                            <div class="testinomial_info">
                                                <div class="profile_name">
                                                    <?php the_sub_field('testimonial_name','options');?>
                                                </div>
                                                <div class="profile_designation">
                                                    <?php the_sub_field('testimonial_designation','options');?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                            endwhile;
                        endif;   
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>