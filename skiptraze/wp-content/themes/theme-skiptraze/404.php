<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package Skiptraze_WP_Theme
 */

get_header();
?>

	<div class="page-404">
		<div class="page-404-container">
			<h1 class="error-code">404</h1>
			<p class="error-message"><?php the_field('error_message','options');?></p>
			<p class="help-text"><?php the_field('help_text','options');?></p>
			<div class="page-404-btn">
				<a class="primary-btn" href="<?php echo get_home_url(); ?>"><?php the_field('go_to_label','options');?></a>
			</div>
		</div>
	</div>

<?php
get_footer();
