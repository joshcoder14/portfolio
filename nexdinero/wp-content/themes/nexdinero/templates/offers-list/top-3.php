<div class="list top_3">
    <div class="list_container">

        <?php
            $topThree = get_field('top_3_offers', 'options');

            if (is_array($topThree) && !empty($topThree)) {
                foreach($topThree as $top):
                    $checks = explode(',', $top['advertising']);
                    
                    ?>
                        <div class="card_item">
                            <div class="item_container">
                                <div class="star_rating">
                                    <?php
                                        $star_rating = $top['star_rating'];

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
                                                $star_rating_text = $top['star_rating'];
                                                echo ($star_rating_text > 0) ? $star_rating_text : ' ';
                                            ?> 
                                        </span>
                                        /5
                                    </div>
                                </div>
                                <div class="partner_logo">
                                    <?php if($top['image']['url']):?>
                                        <img src="<?php echo $top['image']['url']?>" alt="Partner Logo">
                                    <?php endif;?>
                                </div>
                                <div class="tag">
                                    <?php
                                        foreach($checks as $check):
                                            ?>
                                                <?php echo sanitize_text_field($check); ?>
                                            <?php
                                        endforeach
                                    ?>
                                </div>
                                <div class="offer_details">
                                    <div class="_flex">
                                        <div class="_column">
                                            <div class="_label">
                                                <?php echo $top['amount_label']?>
                                            </div>
                                            <div class="_value">
                                                <?php echo $top['amount_value']?>
                                            </div>
                                        </div>
                                        <div class="_column">
                                            <div class="_label">
                                                <?php echo $top['approval_rate_label']?>
                                            </div>
                                            <div class="_value">
                                                <?php echo $top['approval_value']?>
                                            </div>
                                        </div>
                                        <div class="_column">
                                            <div class="_label">
                                                <?php echo $top['age_label']?>
                                            </div>
                                            <div class="_value">
                                                <?php echo $top['age_value']?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="offer_actions">
                                    <a target="_blank" href="<?php echo $top['url']; ?>" class="btn">Solicitar ahora</a>
                                </div>

                                <div class="short_text">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/certificate-check.svg" alt="square check">
                                    <span>
                                     <?php echo $top['name']?>
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

