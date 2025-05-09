<section class="section full-width">
    <div class="first-section container" id="top-section">
        <!-- First Section -->
        <div class="swiper foundation_contents fadeInUp">
            <div class="wrapper swiper-wrapper">
                <!-- Slides -->

                <?php
                    $heroSection = get_field('hero_section');

                    if (is_array($heroSection) && !empty($heroSection)) {
                        foreach ($heroSection as $item):
                            $image = $item['image']['url'];
                            $title = $item['title'];
                            $sutTitle = $item['subtitle'];
                            $buttonLabel = $item['button_label'];
                            $buttonUrl = $item['button_url'];

                            ?>
                                <div class="item swiper-slide">
                                    <div class="item_container">
                                        <?php if($image):?>
                                            <img src="<?php echo $image; ?>" alt="image">
                                        <?php endif;?>

                                        <div class="details">
                                            <h2 class="title"><?php echo $title; ?></h2>
                                            <div class="subtext">
                                                <div class="text">
                                                    <?php echo $sutTitle; ?>
                                                </div>
                                                <a href="<?php echo $buttonUrl ?? get_home_url() ?>" class="action-btn"><?php echo $buttonLabel; ?></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php
                        endforeach;  
                    } else {
                        echo '<div class="d-flex align-items-center justify-content-center w-100 m-auto">Not data found</div>';
                    }
                ?>
                 
                 
            </div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>