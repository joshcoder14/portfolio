<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Skiptraze_WP_Theme
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<header class="header">
		<div class="header_container">
			<div class="logo_area">
				<a href="<?php echo get_home_url(); ?>" class="menu_logo">
					<img src="<?php echo get_template_directory_uri(); ?>/images/logo/logo.svg" class="logo" alt="skiptraze logo">
				</a>
			</div>
			<div class="navigation">
				<?php wp_nav_menu( array( 'theme_location' => 'main-menu', 'container_class' => 'new_menu_class' ) ); ?>
				<?php 
					if( !is_404()){
						get_template_part('page-templates/page-components/language-dropdown'); 
					}
				?>
				<?php
					$login_url = get_field('login_url', 'options');
					
					if (!empty($login_url)) :
						?>
							<div class="login_btn">
								<a href="<?php echo esc_url($login_url); ?>" class="btn_">
									<?php the_field('login_label', 'options'); ?>
								</a>
							</div>
						<?php
					endif;
				?>
			</div>
			<div class="header_mobile_menu">

				<?php
					$login_url = get_field('login_url', 'options');
					
					if (!empty($login_url)) :
						?>
							<div class="login_btn">
								<a href="<?php echo esc_url($login_url); ?>" class="btn_">
									<?php the_field('login_label', 'options'); ?>
								</a>
							</div>
						<?php
					endif;
				?>
				<div class="mobile_menu">
					<img src="<?php echo get_template_directory_uri(); ?>/images/icons/menu.svg" class="menu-bar" alt="menu bar">

					<img src="<?php echo get_template_directory_uri(); ?>/images/icons/close.svg" class="close-bar" alt="close bar">
				</div>
			</div>
		</div>
	</header>