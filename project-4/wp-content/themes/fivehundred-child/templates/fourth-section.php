<section class="section" id="gallery">
    <div class="fourth-section container">
        <div class="section_heading">
            <?php if (get_field('short_description')): ?>
                <div class="short-intro">
                    <?php the_field('short_description'); ?>
                </div>
            <?php endif; ?>
            <h2 class="entry-title"><?php the_field('gallery_title');?></h2>
        </div>

        <div class="project-lists">
            <?php
                $gallery = get_field('gallery_section');

                if (is_array($gallery) && !empty($gallery)) {
                    foreach ($gallery as $galleryItem):
                        $image = $galleryItem['image']['url'];
                        $title = $galleryItem['intro_title'];
                        $subtext = $galleryItem['intro_subtext'];
                        $additional_details = $galleryItem['additional_details'];
                        $artist_name = $galleryItem['artist_name'];
                        $artwork_price = $galleryItem['artwork_price'];
                        $button_label = $galleryItem['button_label'];

                        ?>
                            <div class="item">
                                <div class="item_container">
                                    <div class="image">
                                        <?php if ($image): ?>
                                            <img src="<?php echo $image; ?>" alt="image">
                                        <?php endif; ?>
                                    </div>

                                    <div class="short_details">
                                        <div class="intro">
                                            <?php echo $title; ?>
                                        </div>
                                        <div class="art_purchase">
                                            <?php echo $subtext; ?>
                                        </div>
                                    </div>

                                    <div class="add_details">
                                        <div class="text">
                                            <?php echo $additional_details; ?>
                                        </div>
                                        <div class="artwork_info">
                                            <div class="artwork_name">
                                                <?php echo $artist_name; ?>
                                            </div>
                                            <div class="artwork_price">
                                                <?php echo $galleryItem['artwork_price']; ?>
                                            </div>
                                        </div>
                                        <div class="view" data-image="<?php echo $image; ?>">
                                            <?php echo $button_label; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                    endforeach;
                } else {
                    echo '<div class="d-flex align-items-center justify-content-center w-100 m-auto fadeInUp">No data found</div>';
                }
            ?>
        </div>
    </div>

    <!-- Modal Structure -->
    <div id="modal" class="modal">
        <div class="modal_container">
            <div class="close">
                <i class="fa fa-times" aria-hidden="true"></i>
            </div>
            <div class="modal_image">
                <img src="" alt="modal-image">
            </div>
        </div>
    </div>
</section>
