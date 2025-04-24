<?php
//Check if the active theme is block-based to avoid warning as Block-based themes do not support header.php and footer.php because they render direct HTML
if ( !function_exists( 'wp_is_block_theme' ) || (function_exists( 'wp_is_block_theme' ) && !wp_is_block_theme()) ) {	
	get_header();
}else{
	wp_head();
}

?>
	<div id="container">
		<article id="content" class="ignition_project">
			<?php
			global $post;
			$id = $post->ID;
			$content = get_post($id);
			$project_id = get_post_meta($id, 'ign_project_id', true);
			?>
			<div id="site-description">
				<h1><?php echo $content->post_title; ?> </h1>
				<h2><?php echo html_entity_decode(get_post_meta($id, 'ign_project_description', true)); ?></h2> 
			</div>
			<div class="entry-content">
				<?php echo apply_filters( 'the_content', do_shortcode( '[project_purchase_form]' ) ); ?>
			</div>
		</article>
		<div class="clear"></div>
	</div>
	<?php
if ( !function_exists( 'wp_is_block_theme' ) || (function_exists( 'wp_is_block_theme' ) && !wp_is_block_theme()) ) {
	get_footer();
}else{
	wp_footer();
}?>