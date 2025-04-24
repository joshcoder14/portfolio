<?php
    
    $ourStories = get_field('stories_and_articles');
    $limitedStories = is_array($ourStories) ? array_slice($ourStories, 0, 6) : []; // Limit to 6 items

    // Check the conditions for showing the Load More button
    $showLoadMore = false;

    if (is_array($ourStories) && !empty($ourStories)) {
        $storyCount = count($ourStories);

        // Show Load More if there are more than 6 stories
        if ($storyCount > 6) {
            $showLoadMore = true;
        }
    }

?>

<section class="section stories-section" id="articles">
    <div class="container">
        <h2 class="entry-title"><?php the_field('heading_title');?></h2>
        <!-- <span class="hr-role"></span> -->
        <div class="stories-tab">
            <ul id="filters" class="clearfix">
                <li class="filter" data-filter=".articles, .paintings, .projects">All</li>
                <li class="filter" data-filter=".articles">Articles</li>
                <li class="filter" data-filter=".paintings">Paintings</li>
                <li class="filter" data-filter=".projects">Projects</li>
            </ul>
        </div>
        <div class="stories-grid">

            <?php
                if (is_array($limitedStories) && !empty($limitedStories)) {
                    foreach ($limitedStories as $story) :
                        $category = strtolower($story['category']);
                        $image = $story['image']['url'];
                        $title = $story['title'];
                        $button_url = $story['button_url'];
                        $button_label = $story['button_label'];

                        ?>
                            <div class="stories <?php echo $category; ?>" data-cat="<?php echo $category; ?>">
                                <div class="stories-wrapper first">

                                    <?php if($image):?>
                                        <img src="<?php echo $image; ?> ?>" alt="image" class="stories-bg">
                                    <?php endif;?>

                                    <div class="label">
                                        <div class="label-text">
                                            <h4><?php echo $title; ?></h4>
                                            <span class="text-category"><?php echo $category; ?></span>
                                            <a href="<?php echo $button_url; ?>" class="template-btn" target="_blank"><?php echo $button_label; ?></a>
                                        </div>
                                        <div class="label-bg"> </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                    endforeach;
                } else {
                    echo '<div class="no-stories">No stories or articles found</div>';
                }
            ?>

        </div>

        <?php if ($showLoadMore) : ?>
            <div class="load-more-wrapper">
                <div class="load-more" id="load-more">
                    Load More
                </div>
            </div>
        <?php endif; ?>

    </div>
</section>

<a href="#articles"></a>