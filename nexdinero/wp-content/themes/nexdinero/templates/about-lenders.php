<div class="about_lender">
    <div class="heading">
        <h2>
            <?php the_field('about_title', 'options'); ?>
        </h2>
    </div>
    <!-- Adsense -->
     <?php
        if(!is_page_template('template-redirect-landing.php')){
            ?>
                 <div class="adsense">
                    <div class="ad_banner">
                        <?php echo get_field('ad_banner_homepage_6', 'option'); ?>
                    </div>
                </div>
            <?php
        }
     ?>
   

    <div class="lender_card">

        <?php

            $aboutLender = get_field('lender_cards', 'options');
            $idx = 0;

            if (is_array($aboutLender) && !empty($aboutLender)) {
                foreach ($aboutLender as $lender) :
                    ?>

                        <div class="card_item <?php echo $idx?>">
                            <div class="item_container">
                                <a target="_blank" href="#" class="arrow">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/arrow-right.svg" alt="arrow icon">
                                </a>
                                <div class="lender_name">
                                    <?php echo $lender['lender_name']?>
                                </div>
                                <div class="lender_details">
                                    <div class="_grid col2">
                                        <div class="_column">
                                            <div class="_label">
                                                <?php echo $lender['apr_label']?>
                                            </div>
                                            <div class="_value">
                                                <?php echo $lender['apr_value']?>
                                            </div>
                                        </div>
                                        <div class="_column">
                                            <div class="_label">
                                                <?php echo $lender['period_label']?>
                                            </div>
                                            <div class="_value">
                                                <?php echo $lender['period_value']?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="_grid col2">
                                        <div class="_column">
                                            <div class="_label">
                                                <?php echo $lender['phone_label']?>
                                            </div>
                                            <div class="_value">
                                                <?php echo $lender['phone_value']?>
                                            </div>
                                        </div>
                                        <div class="_column">
                                            <div class="_label">
                                                <?php echo $lender['email_label']?>
                                            </div>
                                            <div class="_value">
                                                <?php echo $lender['email_value']?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="_grid">
                                        <div class="_column">
                                            <div class="_label">
                                                <?php echo $lender['address_label']?>
                                            </div>
                                            <div class="_value">
                                                <?php echo $lender['address_value']?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="_grid">
                                        <div class="_column">
                                            <div class="_label">
                                                <?php echo $lender['description_label']?>
                                            </div>
                                            <div class="_value">
                                                <?php echo $lender['description_value']?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php
                endforeach;
            } else {
                echo 'No lenders information available';
            }

        ?>

    </div>
</div>

