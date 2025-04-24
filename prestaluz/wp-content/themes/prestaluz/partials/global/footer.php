<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package prestaluz_WP_Theme
 */

?>

    <footer>
        
		<div class="footer_container">
			<img class="footer_left" src="<?php echo get_template_directory_uri(); ?>/assets/images/footer-left.svg" alt="">
			<img class="footer_top_right" src="<?php echo get_template_directory_uri(); ?>/assets/images/footer-top-right.svg" alt="">
			<img class="mobile_footer_bottom_left" src="<?php echo get_template_directory_uri(); ?>/assets/images/mobile-footer-bottom-left.svg" alt="">
			<img class="mobile_footer_top_right" src="<?php echo get_template_directory_uri(); ?>/assets/images/mobile-footer-top-right.svg" alt="">

			<div class="footer_logo">
				<img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo/footer-logo.svg" alt="logo">
				<!-- <h1>LOGO</h1> -->
				<div class="card_icon">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/mastercard-logo.svg" alt="master card">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/visa.svg" alt="visa icon">
				</div>
			</div>
			<div class="footer_contact">
				<div class="heading">
					<h2>Contacto</h2>
				</div>
				<div class="contact_info">
					<div class="cancel">
						Cancelar o Reemobolso
					</div>
					<a href="mailto:<?php echo get_field('contact_email','option')?>" class="email_add">
						<?php echo get_field('contact_email','option')?>
					</a>
					<div class="address">
						<span class="company_name"><?php echo get_field('contact_name','option')?></span>
						<span><?php echo get_field('contact_street_address','option');?></span>
					</div>
				</div>
			</div>

			<!-- FIX ME: hide footer information if user is logged in -->
			<div class="footer_information">
				<div class="heading">
					<h2>Información</h2>
				</div>
				<?php
					if ( has_nav_menu( 'footer-menu' ) ) {
						wp_nav_menu( array(
							'theme_location'  => 'footer-menu',
							'menu_id'         => 'footer-menu',
							'menu_class'      => 'footer_menu',
						) );
					}
				?>     
			</div>

			<!-- FIX ME: Add class center on class policies if use is logged in -->
			<div class="policies"> <!-- add class "center" -->
                <?php
                    if ( has_nav_menu( 'footer-menu' ) ) {
                        wp_nav_menu( array(
                            'theme_location'  => 'policy-menu',
                            'menu_id'         => 'footer-menu',
                            'menu_class'      => 'footer_policies',
                        ) );
                    }
                ?>     
			</div>

			<div class="card_icon_mobile">
				<img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/mastercard-logo.svg" alt="master card">
				<img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/visa.svg" alt="visa icon">
			</div>
		</div>
		
	</footer>
<?php wp_footer(); ?>
</body>
</html>