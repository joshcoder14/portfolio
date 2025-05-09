<?php
$idsocial_settings = maybe_unserialize(get_option('idsocial_settings'));
if (isset($idsocial_settings['theme_500'])) {
	$social_settings = $idsocial_settings['theme_500'];
	if (!empty($social_settings)) {
		$insta = (isset($social_settings['instagram']) ? $social_settings['instagram'] : '');
		$twitter = (isset($social_settings['twitter']) ? $social_settings['twitter'] : 0);
		$fb = (isset($social_settings['fb']) ? $social_settings['fb'] : 0);
		$google = (isset($social_settings['google']) ? $social_settings['google'] : 0);
		$li = (isset($social_settings['li']) ? $social_settings['li'] : 0);
		$insta_via = (isset($social_settings['instagram_via']) ? $social_settings['instagram_via'] : '');
		$twitter_via = (isset($social_settings['twitter']) ? $social_settings['twitter_via'] : '');
		$fb_via = (isset($social_settings['fb']) ? $social_settings['fb_via'] : '');
		$g_via = (isset($social_settings['google']) ? $social_settings['g_via'] : '');
		$li_via = (isset($social_settings['li']) ? $social_settings['li_via'] : '');
	}
}
?>
</div>
</div>
<footer>
	<ul class="footer_widgets">
		<?php if (dynamic_sidebar('footer-widget-area')) : ?><?php endif; ?>
	</ul>
	<div class="footerright">
		<nav id="menu-footer">
		
			<?php
			if ( has_nav_menu( 'footer-menu' ) ) {
			// Using wp_nav_menu() to display menu
			wp_nav_menu( array( 
				'menu' => 'footer-menu', // Select the menu to show by Name
				'container' => false, // Remove the navigation container div
				'theme_location' => 'footer-menu' 
				)
			);
			}
			?>
		</nav>
	</div>
	<div id="copyright">
		<?php if (fivehundred_show_credits()) { ?>
		<span class="themelink"><?php _e('Theme 500 is a', 'fivehundred'); ?> <a rel="nofollow" target="_blank" href="https://ignitiondeck.com/id/themes?utm_source=500%20footer&utm_medium=link&utm_campaign=product%20placement&utm_content=500%20Framework" title="crowdfunding theme for wordpress" alt="Wordpress crowdfunding theme">
		<?php _e('Crowdfunding Theme for WordPress', 'fivehundred'); ?></a></span>
		<?php } ?>
	</div>
	<div id="home-sharing">
		<ul>
			<?php echo (!empty($insta) ? '<li class="insta-btn"><a href="http://instagram.com/'.$insta_via.'" target="_blank" ><i class="fa fa-instagram"></i></a></li>' : ''); ?>
			<?php echo (!empty($twitter) ? '<li class="twitter-btn"><a href="http://twitter.com/'.$twitter_via.'" target="_blank" ><i class="fa fa-twitter-square"></i></a></li>' : ''); ?>
			<?php echo (!empty($fb)  ? '<li class="facebook-btn"><a href="http://www.facebook.com/'.$fb_via.'" target="_blank"><i class="fa fa-facebook-square"></i></a></li>' : ''); ?>
			<?php echo (!empty($google) ? '<li class="gplus-btn"><a href="https://plus.google.com/'.$g_via.'" target="_blank"><i class="fa fa-google-plus-square"></i></a></li>' : ''); ?>
			<?php echo (!empty($li) ? '<li class="linkedin-btn"><a href="'.$li_via.'" target="_blank"><i class="fa fa-linkedin"></i></a></li>' : ''); ?>
			<!-- prob want to get category here -->
		</ul>
	</div>
	<div class="clear"></div>
</footer>
<?php wp_footer(); ?>
</body>
</html>