<div class="hero-about">
    <div class="container-2">
        <div class="content-2">
            <div class="text">
                <div class="heading-overline">
                    <div class="overline">
                        <?php the_field('about_us_overline');?>
                    </div>
                    <div class="heading">
                        <?php the_field('about_us_heading');?>
                    </div>
                </div>
                <p class="supporting-text">
                    <?php the_field('about_us_subtext');?>
                </p>
            </div>
        </div>
        <div class="content-3" style="background: url('<?php echo the_field('about_us_image'); ?>') lightgray 50% / cover no-repeat;"></div>
    </div>
</div>
<div class="platform">
    <div class="platform-container">
        <div class="platform-content-1">
            <?php the_field('bold_text');?>
        </div>
        <div class="platform-content-2">
            <?php the_field('platform_text');?>
        </div>
    </div>
</div>

<div class="feature_about">
    <div class="feature_container">
        <?php get_template_part('page-templates/page-components/feature'); ?>
    </div>
</div>