<div class="vertical_lines fadeIn">
    <div class="line line-1"></div>
    <div class="line line-2"></div>
    <div class="line line-3"></div>
    <div class="line line-4"></div>
    <div class="line line-5"></div>
</div>
<main>
    <section>
        <div class="container">
            <?php get_template_part('templates/heading'); ?>
            <div class="adsense">
                <div class="ad_banner">
                    <?php echo get_field('ad_banner_homepage', 'option'); ?>
                </div>
            </div>
            <?php get_template_part('templates/offers-list/top-3'); ?>
            <div class="adsense">
                <div class="ad_banner">
                    <?php echo get_field('add_banner_homepage_2', 'option'); ?>
                </div>
            </div>
            <?php get_template_part('templates/offers-list/offers'); ?>
            <div class="adsense">
                <div class="ad_banner">
                    <?php echo get_field('add_banner_homepage_3', 'option'); ?>
                </div>
            </div>
            <?php get_template_part('templates/about-lenders'); ?>
            <div class="adsense">
                <div class="ad_banner">
                    <?php echo get_field('add_banner_homepage_4', 'option'); ?>
                </div>
            </div>
            <?php get_template_part('templates/faq'); ?>
            <div class="adsense">
                <div class="ad_banner">
                    <?php echo get_field('ad_banner_homepage_5', 'option'); ?>
                </div>
            </div>
        </div>
    </section>
</main>