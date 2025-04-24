<section class="section full-width">
    <div class="third-section container">
        <div class="swiper-container foundation_contents mySwiper">
            <div class="wrapper swiper-wrapper">
                <?php
                    $foundation = get_field('foundation_mission_messages');

                    if (is_array($foundation) && !empty($foundation)) {
                        foreach ($foundation as $foundationItem):
                            $image = $foundationItem['image']['url'];
                            $title = $foundationItem['title'];
                            $sutTitle = $foundationItem['subtitle'];
                            $buttonLabel = $foundationItem['button_label'];
                            $buttonUrl = $foundationItem['button_url'];
                            $name = $foundationItem['name'];

                            ?>
                                <div class="item swiper-slide">
                                    <div class="item_container">
                                        <?php if($image):?>
                                            <img src="<?php echo $image; ?>" alt="image">
                                        <?php endif;?>

                                        <div class="details">
                                            <h2 class="title fadeInDown"><?php echo $title; ?></h2>
                                            <div class="subtext">
                                                <div class="text">
                                                    <?php echo $sutTitle; ?>
                                                </div>
                                                <a href="<?php echo $buttonUrl ?? get_home_url() ?>" class="action-btn"><?php echo $buttonLabel; ?></a>
                                            </div>
                                        </div>
                                        
                                        <div class="intro_proj_text">
                                            <?php echo $name; ?>
                                        </div>
                                    </div>

                                </div>
                            <?php
                        endforeach;  
                    } else {
                        echo '<div class="d-flex align-items-center justify-content-center w-100 m-auto fadeInUp">Not data found</div>';
                    }
                ?>
            </div>
            
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>