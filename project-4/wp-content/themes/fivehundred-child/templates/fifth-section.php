<section class="section full-width">
    <div class="fifth-section container">
        <div class="foundation_contents swiper">
            <div class="wrapper swiper-wrapper">

                <?php
                    $foundationReference = get_field('foundation_reference');

                    if (is_array($foundationReference) && !empty($foundationReference)) {
                        foreach ($foundationReference as $foundRef):
                            $image = $foundRef['image']['url'];
                            $title = $foundRef['title'];
                            $subtitle = $foundRef['subtitle'];
                            $subtext = $foundRef['subtext'];

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
                                                    <?php echo $subtitle; ?>
                                                </div>
                                                <div class="text">
                                                    <?php echo $subtext; ?>
                                                </div>
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