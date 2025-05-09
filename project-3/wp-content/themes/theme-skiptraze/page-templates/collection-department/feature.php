<div class="container-wrapper">
    <div class="container-3">
        <div class="image">
            <img src="<?php echo the_field('collection_special_feature_image','options'); ?>" alt="background image" class="">
        </div>
        <div class="content-3">
            <div class="text-wrapper">
                <?php the_field('content_title');?>
            </div>
            <div class="div-4">
                <div class="paragraph">
                    <?php the_field('content_subtext');?>
                </div>
                <div class="text-wrapper-2">
                    <?php the_field('content_following');?>
                </div>
                <div class="paragraph-2">
                    <?php the_field('connect_text');?>
                </div>
            </div>
        </div>
    </div>
</div>

