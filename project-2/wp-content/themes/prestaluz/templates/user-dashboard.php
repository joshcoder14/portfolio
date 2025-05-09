<section class="user_dashboard">
    <div class="container dashboard_container">
        <div class="wrapper dashboard_wrapper">
            <div class="heading">
                <h1 class="title w-100 h-auto">
                    <!-- FIX ME: Create the actual user first name -->
                    Hola ((first_name))!
                </h1>
            </div>
            <div class="subtext">
                <p>
                    Aquí tienes la selección de los mejores productos financieros que hay actualmente en el mercado. 
                </p>
                <p>
                    Esta lista se actualiza constantemente y nos esforzamos cada día para que siempre tengas acceso a los mejores productos.
                </p>
            </div>

            <div class="offer_info">
                <div class="info_wrapper">
                    <?php

                        $query = new WP_Query(array(
                            'post_status' => 'publish',
                            'order'    => 'ASC',
                            'posts_per_page' => 5
                        ));
                        while($query->have_posts()){
                            $query->the_post();
                            
                            ?>
                                <div class="offer_items">
                                    <div class="container">
                                        <?php 
                                            $post_thumnail = get_the_post_thumbnail_url();
                                                if( $post_thumnail){
                                                    ?>
                                                        <a href="<?php the_permalink();?>" class="arrow">
                                                            <img class="arrow_icon" src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/arrow-right.svg" alt="arrow right" class="arrow_icon">
                                                        </a>
                                                        <div class="image">
                                                            <img class="offer_bg" src="<?php echo get_the_post_thumbnail_url(); ?>" alt="scale">
                                                        </div>
                                                    <?php
                                                }else{
                                                    echo esc_html("Please set featured image!.");
                                                }
                                                //NOTE: Add functionality for the private post(MUY PRONTO)!
                                        ?>  
                                        <div class="overlay"></div>
                                        <div class="content">
                                            <h3 class="title">
                                                <?php the_title();?>
                                            </h3>
                                            <div class="subtext">
                                                <?php the_excerpt();?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            wp_reset_postdata();
                        }
                    ?>
                </div>
                <div class="scrollbar_track">
                    <div class="custom_scrollbar"></div>
                </div>
            </div>
        </div>
    </div>
</section>