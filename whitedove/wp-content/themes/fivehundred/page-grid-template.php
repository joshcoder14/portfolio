<?php
/*
Template Name: Project Grid (Page)
*/
?>
<?php 
	global $post;
	$settings = get_option('fivehundred_theme_settings');
	$display_count = $settings['home_projects'];
	$num_projects = wp_count_posts('ignition_product');
	$num_projects_pub = $num_projects->publish;
	if ($display_count < $num_projects_pub) {
		$show_more = 1;
	}
	else {
		$show_more = 0;
	}
	$url = site_url('/');
	$tagline = get_bloginfo('description'); 
?>
<?php get_header(); ?>
<div id="container">
	<div id="site-description">
		<h1><?php the_title(); ?></h1>
	</div>
	<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div id="content" class="fullwidth">
		<h2 class="entry-title"><?php _e('Featured Projects', 'fivehundred'); ?></h2>
			<?php get_template_part( 'nav', 'above-grid' ); ?>
			<?php do_action('fh_above_grid'); ?>
			<div id="project-grid">
					<?php get_template_part('loop', 'project'); ?>				
			</div>
			<?php do_action('fh_below_grid'); ?>
			<div style="clear: both;"></div>
			<div class="ign-more-projects front-page">
                <a class="" href="<?php echo get_post_type_archive_link('ignition_product'); ?>"><?php _e('All Projects', 'fivehundredstellar'); ?> <i class="fa fa-arrow-circle-right"></a>
            </div>
			<hr class="fancy" />
			<div id="home-widget">
				<?php get_sidebar('home'); ?>
			</div>
		</div>
	</div>
	<div class="clear"></div>
</div>
<?php get_footer(); ?>