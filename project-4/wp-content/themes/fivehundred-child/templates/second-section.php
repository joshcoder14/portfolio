<section class="section">
    <div class="second-section container">
        <div class="project-container">
            <div class="heading">
                <h2 class="entry-title"><?php echo apply_filters('featured_projects_title', __('Featured Projects', 'fivehundred-child')); ?></h2>
                <div class="ign-more-projects">
                    <a href="<?php echo get_post_type_archive_link('ignition_product'); ?>">
                        <?php _e('View All Projects', 'fivehundred'); ?>
                    </a>
                </div>
            </div>
            <div class="project-lists">
                <?php 
					if (is_front_page()) {
						get_template_part('loop', 'project');
					}
					else {
						$paged = (get_query_var('paged') ? get_query_var('paged') : 1);
						$query = new WP_Query(array('paged' => 'paged', 'posts_per_page' =>1, 'paged' => $paged));

						if ( $query->have_posts() ) : 
                            while ( $query->have_posts() ) : $query->the_post();
							    get_template_part('entry');
							endwhile;
						endif; 
						wp_reset_postdata();
						?>
					<?php } 
                ?>
            </div>   
        </div>
    </div>
</section>