<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<?php get_template_part('head'); ?>

<body <?php body_class(); ?> id="fivehundred">
	
	<div id="wrapper" class="hfeed">
		<header id="header" class="<?php echo apply_filters('fh_header_class', ''); ?>">
			<nav>
				<div class="main-menu">
					<!-- Menu -->
					<div class="main-menu-dropdown">
						<div class="menu-dropdown">
							Menüü
						</div>
						<div class="menu-dropdown-lists">
							<?php
								// Display the custom menu
								wp_nav_menu(array(
									'menu_class'           => 'menu-list-item',
									'theme_location'       => 'main-menu',
									'container_class'      => 'menu-list',
								));
							?>
						</div>
					</div>
					<!-- Logo -->
					<a class="logo" href="<?php echo get_home_url();?>">
						<div class="logo-whitedove">
							<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/Whitedove-art-logo.svg" alt="">
						</div>
					</a>
					<!-- Language -->
					<div class="language-selector">
						<ul class="language-list">
							<li class="language-item active-lang">
								<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/flag/ee.svg" class="lang-flag" alt="">
								<span class="lang-name">estonian</span>
							</li>
							<li class="language-item">
								<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/flag/us.svg" class="lang-flag" alt="">
								<span class="lang-name">english</span>
							</li>
						</ul>
					</div>

				</div>
            </nav>
		</header>
	<?php if (isset($post) && $post->post_type == 'post' && is_home()) { ?>
		<div id="containerwrapper" class="<?php echo (isset($post) ? $post->post_type : ''); ?> containerwrapper-home">
	<?php } else { ?>
	<div id="containerwrapper" class="<?php echo (isset($post) ? $post->post_type : ''); ?> containerwrapper">
	<?php } ?>