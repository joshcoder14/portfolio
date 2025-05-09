
<section class="offer_wall">
    <div class="container offer_container">
        <div class="wrapper offer_wrapper">
            <div class="heading">
                <h1 class="title">
                    <?php the_field('title'); ?>
                </h1>
                <div class="subtext">
                    <?php the_field('subtext'); ?>
                </div>
            </div>
            <div class="offers_list">    
                <div class="list_container">
                    <?php 
                        if( have_rows('reunificacion_de_deudas','options') ):
                            while( have_rows('reunificacion_de_deudas','options') ) : the_row();
                            
                                    $args = [

                                        'partner_image' => get_sub_field('partner_logo'),
                                        //'offer_card_title' => get_sub_field('offer_card_title'),
                                        'partner_label' => get_sub_field('partner_label'),
                                        'partner_label_list' => get_sub_field('partner_label_list'),
                                        'partner_checklist' => get_sub_field('partner_checklist'),
                                        'partner_checklist_item' => get_sub_field('partner_checklist_item'),
                                        'star_rating' => get_sub_field('star_rating'),
                                        'button_label_arrow' => get_sub_field('button_label_arrow'),
                                     
                                    ];
                            
                                    set_query_var('offers-3', $args);
                                    //Offers
                                    echo '<div class="offer_card fadeInUp">';
                                        get_template_part('templates/offers-list/offers-3');
                                    echo '</div>';
                                //$row_index_num = get_row_index();     
                            endwhile;
                        else :
                        endif;
                    ?>  
                </div>   
            </div>
        </div>
    </div>
</section>

<!-- Other financial Products -->
<section class="other_financial_products">
    <div class="container">
        <div class="wrapper">
            <div class="heading">
                <h1 class="title">
                    Otros productos financieros
                </h1>
            </div>
            <div class="offer_info other">
                <div class="info_wrapper">
                    <?php get_template_part('templates/financial-products'); ?>
                </div>
                <div class="scrollbar_track">
                    <div class="custom_scrollbar"></div>
                </div>
            </div>
        </div>
    </div>
</section>