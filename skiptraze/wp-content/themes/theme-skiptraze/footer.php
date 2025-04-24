<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Skiptraze_WP_Theme
 */

?>
	<footer>
		<div class="footer_container">
			<div class="footer_logo">
				<?php
					$footer_logo_src = get_field('footer_logo', 'options');
					if (!empty($footer_logo_src)) {
						echo '<img src="' . $footer_logo_src . '" class="footer-logo" alt="logo">';
					}
				?>
			</div>
			<?php wp_nav_menu( array( 'theme_location' => 'footer-menu', 'container_class' => 'new_menu_class' ) ); ?>
			<div class="social_policies">
				<div class="socials">
					<?php
						if( have_rows('social_icon_list','options') ):
							while( have_rows('social_icon_list','options') ) : the_row();
								?>
									<a href="<?php the_sub_field('link','options');?>">
										<img src="<?php the_sub_field('icon','options');?>" class="social-icon" alt="icon">
									</a>
								<?php
							endwhile;
						endif; 
					?>
				</div>
				<div class="policies">	
					<?php wp_nav_menu( array( 'theme_location' => 'footer-right-menu', 'container_class' => 'new_menu_class' ) ); ?>
				</div>
			</div>
		</div>
		
	</footer>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
