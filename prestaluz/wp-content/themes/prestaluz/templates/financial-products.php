
<?php

    $query = new WP_Query(array(
        'post_status' => 'publish',
        'order'    => 'ASC',
        'posts_per_page' => -1
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
                                <a target="_blank" href="<?php the_permalink();?>" class="arrow">
                                    <img class="arrow_icon" src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/arrow-right.svg" alt="arrow right" class="arrow_icon">
                                </a>
                                <div class="image">
                                    <img class="offer_bg" src="<?php echo get_the_post_thumbnail_url(); ?>" alt="scale">
                                </div>
                            <?php
                            //NOTE: Add functionality for the private post(MUY PRONTO)!
                        }else{
                            echo esc_html("Please set featured image!.");
                        }
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

<!-- <div class="offer_items">
    <div class="container">
        <div class="coming_soon">
            <img class="lock_icon" src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/lock.svg" alt="arrow right">
            
            <span>Muy Pronto</span>
        </div>
        <div class="image">
            <img class="offer_bg" src="<?php echo get_template_directory_uri(); ?>/assets/images/image7.png" alt="scale">
        </div>
        <div class="overlay"></div>
        <div class="content">
            <h3 class="title">
                Informe de solvencia mensual
            </h3>
            <div class="subtext">
                Tener la capacidad para devolver las deudas y hacer frente a las obligaciones adquiridas es esencial.
            </div>
        </div>
    </div>
</div> -->
