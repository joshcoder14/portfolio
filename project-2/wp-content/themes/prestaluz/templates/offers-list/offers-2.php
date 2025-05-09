<?php
    $args = get_query_var('offers-2');
?>   
<div class="card_container">
    <div class="offer_image">
        <img src="<?php echo $args['partner_image'];?>" alt="partner_logo">
    </div>
    <div class="offer_details style-2">
        <div class="_flex">
            <div class="row">
                <div class="details_head">
                    <?php echo $args['offer_card_title'];?>
                </div>
            </div>
            <?php
                if( have_rows('partner_label','options') ):

                while( have_rows('partner_label','options') ) : the_row();       
                        $partner_label_item = get_sub_field('partner_label_item');
                        $partner_label_list = get_sub_field('partner_label_list');
                ?>
                    <div class="row">
                        <div class="label">
                            <?php echo  $partner_label_item ; ?>
                        </div>
                        <div class="value">
                            <?php echo $partner_label_list; ?>
                        </div>
                    </div>
                <?php
            endwhile;
            else :
            endif;    
            ?>
        </div>
    </div>
    <div class="separator"></div>
    <div class="keyword_contents">
        <?php
            if( have_rows('partner_checklist','options') ):

                while( have_rows('partner_checklist','options') ) : the_row();
                    
                        $partner_checklist_item = get_sub_field('partner_checklist_item');
                ?>
                    <div class="content_item">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/check-black.svg" alt="check">
                        <span><?php echo $partner_checklist_item; ?></span>
                    </div>
                <?php
            endwhile;
            else :
            endif;
        
        ?>
    </div>
    <div class="separator"></div>
    <div class="offer_btn style-2">
        <div class="star_rating">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/star-filled.svg" alt="star">
            <div class="rating">
                <span class="rate">
                    <?php echo $args['star_rating'];?>
                </span>
                <span class="overall_rate">
                    /10
                </span>
            </div>
        </div>
        <a target="_blank" href="<?php echo $args['button_label_arrow'];?>" class="apply_now_btn">
            <span>Solicitar ahora</span>
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/arrow-right.svg" alt="arrow right">
        </a>
    </div>
</div>
      
       