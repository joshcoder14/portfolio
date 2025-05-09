<?php
/*
Template Name: Project Grid (Home)
*/
?>
<?php 
	global $post;
	$settings = get_option('fivehundred_theme_settings');
	$display_count = ( is_array( $settings ) && array_key_exists( 'home_projects', $settings ) ) ? $settings['home_projects'] : '0';
	$num_projects = wp_count_posts('ignition_product');
	if (!empty($num_projects) && is_object($num_projects)) {
		$num_projects_pub = ((isset($num_projects->publish)) ? $num_projects->publish : 0);
	} else {
		$num_projects_pub = 0;
	}
	if ($display_count < $num_projects_pub) {
		$show_more = 1;
	}
	else {
		$show_more = 0;
	}
	$url = site_url('/');
	$tagline = get_bloginfo('description'); 
	$options = get_option('fivehundred_featured');
	$idsocial_settings = maybe_unserialize(get_option('idsocial_settings'));
	if (!empty($idsocial_settings['theme_500'])) {
		$social_settings = $idsocial_settings['theme_500'];
		if (!empty($social_settings)) {
			$about_us = html_entity_decode($settings['about']);
		}
	}
?>
<?php if (isset($settings['home']) && !empty($settings['home'])) {
	get_header(); ?>
	<div id="container">
		<article id="content" class="ignition_project project-home">
			<?php get_template_part( 'project', 'content-home' ); ?>
		</article>
	<div class="clear"></div>
	</div>
<?php get_footer(); ?>
<?php } else if (is_home()) { ?>
	<?php get_header(); ?>
	<div id="container">
		<div class="ign-project-content ign-project-top"><?php if (dynamic_sidebar('home-top-content-widget-area')) : ?><?php endif; ?></div>
		<?php if (!empty($options)) {?>
		<div class="breakout-out">
			<div class="breakout-in">
				<?php get_template_part('project-featured'); ?>
			</div>
		</div>
		<?php } ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<div id="content">
			<h2 class="entry-title"><?php echo apply_filters('featured_projects_title', __('Featured Projects', 'fivehundred')); ?></h2>
				<?php get_template_part( 'nav', 'above-grid' ); ?>
				<?php do_action('fh_above_grid'); ?>
				<div id="project-grid">
					<?php 
					if (is_front_page()) {
						get_template_part('loop', 'project');
					}
					else {
						$paged = (get_query_var('paged') ? get_query_var('paged') : 1);
						$query = new WP_Query(array('paged' => 'paged', 'posts_per_page' =>1, 'paged' => $paged));

						// Start the loop
						if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();
							get_template_part('entry');
							endwhile;
							endif; 
						wp_reset_postdata();
						?>
						<div class="nav-previous"><?php next_posts_link(); ?></div>
						<div class="nav-next"><?php previous_posts_link(); ?></div>
					<?php } ?>
				</div>
				<div class="ign-more-projects"><a href="<?php echo get_post_type_archive_link('ignition_product'); ?>"><?php _e('View All Projects', 'fivehundred'); ?></a></div>
				<?php do_action('fh_below_grid'); ?>
				<div style="clear: both;"></div>
				<hr class="fancy" />
				<div id="ign-project-content" class="ign-project-content"><?php if (dynamic_sidebar('home-content-widget-area')) : ?></div>
				<?php endif; ?>
				<div id="about-us" class="entry-content">
					<div id="about"><?php echo (isset($about_us) ? $about_us : ''); ?></div>
				</div>
				<div id="home-widget">
					<?php get_sidebar('home'); ?>
				</div>
			</div>
		</div>
		<div class="clear"></div>
	</div>
<?php } else { ?>
	<?php get_header(); ?>
	<div id="container">
		<div id="site-description">
			<h1><?php bloginfo( 'description' ) ?></h1>
		</div>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<div id="content">
				<?php if (have_posts()) {
					while (have_posts()) {
						the_post();
					}
				}
				the_content();
				?>
			</div>
		<div class="clear"></div>
	</div>
<?php } ?>
<?php get_footer(); ?>