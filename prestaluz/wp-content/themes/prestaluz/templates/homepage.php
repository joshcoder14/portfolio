
<section class="home_first_section">
    
    <img class="section_image" src="<?php echo get_template_directory_uri(); ?>/assets/images/girl.jpg" alt="">
    <div class="container">
        
        <div class="wrapper">
            <div class="heading">
                <h1 class="title">
                    <?php echo get_field('hero_title');?>
                </h1>
            </div>
            <div class="list">
                <ul>
                <?php
                    if( have_rows('hero_content') ):
                        while( have_rows('hero_content') ) : the_row();
                            $hero_list_item = get_sub_field('hero_list_item');
                            if($hero_list_item){?>
                                <li><?php echo $hero_list_item;?></li>
                            <?php
                            }        
                        endwhile;

                    else :
                    endif;
                ?>
                </ul>
            </div>
            <div class="action_btn">
                <?php 
                    $button_url = get_field('action_button');
                    ?>
                        <a href="<?php echo esc_url( $button_url ); ?>" class="btn">
                            Registro
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/arrow-right.svg" alt="arrow right">
                        </a>
                    <?php
                ?>
            </div>
        </div>
    </div>
</section>

<section class="home_second_section" id="de-nosotros">
    <div class="container position-relative pt-32 w-100 h-100">
        <div class="wrapper">
            <div class="keywords_content">
                <?php
                    if( have_rows('tags') ):
                        while( have_rows('tags') ) : the_row();
                            $keyword_items = get_sub_field('tag_items');
                            if($hero_list_item){?>
                                <div class="keyword_item key_items-<?php echo get_row_index()?> "><?php echo $keyword_items;?></div>
                            <?php
                            }        
                        endwhile;

                    else :
                    endif;
                ?>
            </div>
            <div class="heading">
                <h1 class="title">
                    <span class="highlight_green"><?php echo get_field('section_2_title');?></span>
                </h1>
            </div>
            <div class="desc">
                <?php echo get_field('section_2_desc');?>
            </div>
        </div>
        <div class="wrapper_image">
            <div class="image_bg">
                <img class="image" src="<?php echo get_template_directory_uri(); ?>/assets/images/mens.png" alt="">
            </div>
        </div>
    </div>
</section>

<section class="home_third_section" id="como-funciona">
    <div class="container">
        <div class="wrapper">
            <div class="heading">
                <h1 class="title">
                    <?php echo get_field('section_3_title');?>
                </h1>
            </div>
            <div class="about_list">
                <div class="card-item light_purple">
                    <div class="card_container">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/purple-card-top-right.svg" alt="" class="card_top_right purple">

                        <div class="image_bg">
                            <img class="light_purple" src="<?php echo get_template_directory_uri(); ?>/assets/images/light-purple.svg" alt="light purple">
                            <img class="mobile_purple" src="<?php echo get_template_directory_uri(); ?>/assets/images/mobile-light-purple.svg" alt="light purple">
                        </div>
                        <div class="content">
                            <div class="icon">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/id.svg" alt="">
                            </div>
                            <div class="title">
                                <?php echo get_field('section_sub_title_1');?>
                            </div>
                            <div class="desc">
                               <?php echo get_field('section_sub_desc_1');?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-item light_orange">
                    <div class="card_container">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/orange-card-top-right.svg" alt="" class="card_top_right orange">

                        <div class="image_bg">
                            <img class="light_orange" src="<?php echo get_template_directory_uri(); ?>/assets/images/light-orange.svg" alt="light orange">
                            <img class="mobile_orange" src="<?php echo get_template_directory_uri(); ?>/assets/images/mobile-light-orange.svg" alt="light orange">
                        </div>
                        <div class="content">
                            <div class="icon">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/scale1.svg" alt="">
                            </div>
                            <div class="title">
                                <?php echo get_field('section_sub_title_2');?>
                            </div>
                            <div class="desc">
                                <?php echo get_field('section_sub_desc_2');?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-item light_green">
                    <div class="card_container">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/green-card-top-right.svg" alt="" class="card_top_right green">

                        <div class="image_bg">
                            <img class="light_green" src="<?php echo get_template_directory_uri(); ?>/assets/images/light-green.svg" alt="light green">
                            <img class="mobile_green" src="<?php echo get_template_directory_uri(); ?>/assets/images/mobile-light-green.svg" alt="light green">
                        </div>
                        <div class="content">
                            <div class="icon">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/shield1.svg" alt="shield">
                            </div>
                            <div class="title">
                                <?php echo get_field('section_sub_title_3');?>
                            </div>
                            <div class="desc">
                                <?php echo get_field('section_sub_desc_3');?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="home_fourth_section" id="que-te-ofrecemos">
    <div class="container">
        <div class="wrapper">
            <div class="heading">
                <h1 class="title">
                    <?php echo get_field('section_4_title');?>
                </h1>
            </div>
            <div class="offer_info">
                <div class="info_wrapper">
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
                                            if( $post_thumnail ){
                                                ?>
                                                    <a target="_blank" href="<?php the_permalink();?>" class="arrow">
                                                        <img class="arrow_icon" src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/arrow-right.svg" alt="arrow right" class="arrow_icon">
                                                    </a>
                                                    <div class="image">
                                                        <img class="offer_bg" src="<?php echo get_the_post_thumbnail_url(); ?>" alt="image">
                                                    </div>
                                                <?php
                                            }else{
                                                
                                                ?>
                                                    <div class="image">
                                                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.svg" alt="image">
                                                    </div>
                                                <?php
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
                </div>

                <div class="scrollbar_track">
                    <div class="custom_scrollbar"></div>
                </div>
            </div>
            <div class="help_text">
               <?php echo get_field('section_sub_desc_4');?>
            </div>
        </div>
    </div>
</section>

<section class="home_fifth_section">
    <div class="container">
        <img class="circle_top_left" src="<?php echo get_template_directory_uri(); ?>/assets/images/circle-top-left.svg" alt="circle">
        <img class="circle_bottom_right" src="<?php echo get_template_directory_uri(); ?>/assets/images/circle-bottom-right.svg" alt="circle">
        <div class="wrapper">
            <div class="heading">
                <h1 class="title w-100 h-auto text-center">
                    <?php echo get_field('section_5_title');?>
                </h1>
            </div>
            <div class="subtext w-100 h-auto text-center">
                <?php echo get_field('section_sub_desc_5');?>
            </div>

            <div class="action_btn d-flex justify-content-center w-100 h-auto text-center">
                <?php
                   $button_url = get_field('action_button');
                    ?>
                        <a href="<?php echo esc_url( $button_url ); ?>" class="btn">
                            Registro

                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/arrow-right.svg" alt="arrow right">
                        </a>
                    <?php
                ?>
            </div>
        </div>
    </div>
</section>

<section class="s-perguntas home_sixth_section" id="preguntas-frecuentes">
    <div class="container">
        <div class="wrapper">
            <div class="heading w-100 h-auto text-center">
                <h1 class="title">
                    <span class="highlight_green">Preguntas frecuentes</span>
                </h1>
            </div>
            <div class="all">
                <div class="left"> 
                    <?php
                        if( have_rows('left_accordion') ):
                            while( have_rows('left_accordion') ) : the_row();
                                $item_question = get_sub_field('item_question');
                                $item_answer = get_sub_field('item_answer');
                                if($item_question){
                                    ?>
                                        <div class="item-pergunta js-show-pergunta">
                                            <div class="title">
                                                <span class="fancy fancy--highlight"><?php echo $item_question; ?></span>
                                                <div class="icon"></div>
                                            </div>
                                            <p><?php echo $item_answer; ?></p>
                                        </div>
                                    <?php
                                }              
                            endwhile;
                        else :
                        endif;
                    ?>
                </div>
                <div class="right">     
                <?php
                    if( have_rows('right_accordion') ):
                        while( have_rows('right_accordion') ) : the_row();
                            $item_question = get_sub_field('item_question');
                            $item_answer = get_sub_field('item_answer');
                            if($item_question){
                                ?>
                                <div class="item-pergunta js-show-pergunta">
                                    <div class="title">
                                        <span class="fancy fancy--highlight"><?php echo $item_question; ?></span>
                                        <div class="icon"></div>
                                    </div>
                                    <p><?php echo $item_answer; ?></p>
                                </div>
                        <?php
                            }              
                        endwhile;
                    else :
                    endif;
                ?>      
                </div>
            </div>
        </div>
    </div>
</section>