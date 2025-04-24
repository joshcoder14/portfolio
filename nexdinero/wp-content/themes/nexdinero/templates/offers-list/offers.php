<div class="list offers">
    <div class="list_container">
        <?php 
            $partners = get_field('offers', 'options');
            $idx = 0;
            // $leadId = isset($_GET['lead_id']) ? $_GET['lead_id'] : '';

            if (is_array($partners) && !empty($partners)) {
                foreach ($partners as $row) :
                    $idx++;

                    ?>
                        <div class="card_item <?php echo $idx?>">
                            <div class="item_container">
                                <div class="star_rating">

                                    <?php
                                        $star_rating = $row['star_rating'];

                                        $filled_star_img = get_template_directory_uri()."/assets/images/icon/stars/star-fill.svg";
                                        $empty_star_img = get_template_directory_uri()."/assets/images/icon/stars/star-no-fill.svg";
                                        $half_star_img = get_template_directory_uri()."/assets/images/icon/stars/star-half-fill.svg";

                                        // Determine which star to display based on the rating
                                        if ($star_rating >= 1) {
                                            // Display a whole star
                                            echo '<img src="' . $filled_star_img . '" alt="Filled Star">';
                                        } else {
                                            // Display an empty star
                                            echo '<img src="' . $empty_star_img . '" alt="Zero Star">';
                                        }
                                    ?>

                                    <div class="rating">
                                        <span class="rates">
                                            <?php 
                                                $star_rating_text = $row['star_rating'];
                                                echo ($star_rating_text > 0) ? $star_rating_text : ' ';
                                            ?> 
                                        </span>
                                        /5
                                    </div>
                                </div>
                                <div class="partner_logo">
                                    <?php if($row['image']['url']):?>
                                        <img src="<?php echo $row['image']['url']?>" alt="Partner Logo">
                                    <?php endif;?>
                                </div>
                                <div class="offer_details">
                                    <div class="_flex">
                                        <div class="_column">
                                            <div class="_label">
                                                <?php echo $row['amount_label']?>
                                            </div>
                                            <div class="_value">
                                                <?php echo $row['amount_value']?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="offer_actions">
                                    <a target="_blank" href="<?php echo $row['url']; ?>" class="btn">Solicitar ahora</a>
                                </div>

                                <div class="short_text">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/certificate-check.svg" alt="square check">
                                    <span>
                                     <?php echo $row['name']?>
                                     es un prestamista certificado
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php
                    
                endforeach;
            } else {
                echo 'No offers available.';
            }
        ?>
    </div>
</div>