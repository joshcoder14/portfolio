<?php
// This is an ID native function
global $post;
$post_id = $post->ID;
$project_id = get_post_meta($post_id, 'ign_project_id', true);
if (empty($project_id)) {
	$settings = get_option('fivehundred_theme_settings');
	if (isset($settings['home']) && $settings['home'] > 0) {
		$project_id = $settings['home'];
	}
}
// Social settings from IDSocial
$social_settings = get_option('idsocial_settings');
if (!empty($social_settings)) {
	if (isset($social_settings['social_checks'])) {
		$social_checks = $social_settings['social_checks'];
		$settings = (object) $social_checks;
		if (isset($settings->prod_page_fb) && $settings->prod_page_fb) {
			echo '<a class="social-share facebook-button" href="https://www.facebook.com/sharer.php?u='.get_permalink($post->ID).'" target="_blank"><img src="'.plugins_url('idsocial/images/share-fb.png').'" /></a>';
		}
		if (isset($settings->prod_page_twitter) && $settings->prod_page_twitter) {
			echo '<a class="social-share twitter-button" href="https://twitter.com/share?url='.get_permalink($post->ID).'&text='.get_the_title($post_id).'" target="_blank"><img src="'.plugins_url('idsocial/images/share-tw.png').'" /></a>';
		}
		if (isset($settings->prod_page_linkedin) && $settings->prod_page_linkedin) {
			echo '<a class="social-share linkedin-button" href="https://www.linkedin.com/shareArticle?url='.get_permalink($post->ID).'&title='.get_the_title($post_id).'" target="_blank"><img src="'.plugins_url('idsocial/images/share-ln.png').'" /></a>';
		}
		if (isset($settings->prod_page_google) && $settings->prod_page_google) {
			echo '<a class="social-share google-button" href="https://plus.google.com/share?url='.get_permalink($post->ID).'" target="_blank"><img src="'.plugins_url('idsocial/images/share-gp.png').'" /></a>';
		}
		if (isset($settings->prod_page_pinterest) && $settings->prod_page_pinterest) {
			echo '<a class="social-share pinterest-button" href="https://pinterest.com/pin/create/bookmarklet/?media='.get_the_post_thumbnail_url($post->ID).'&url='.get_permalink($post->ID).'&description='.get_the_excerpt($post->ID).'" target="_blank"><img src="'.plugins_url('idsocial/images/share-pt.png').'" /></a>';
		}
	}
}
?>
<div id="share-link" class="social-share" data-input="share-link-input"><i class="fa fa-link" title="<?php echo __('Copy Link', 'idsocial');?>"></i></div>
<div id="share-link-input-wrapper"><input id="share-link-input" type="text" value="<?php echo get_permalink($post->ID);?>" /></div>

<div id="share-embed" class="social-share"><i class="fa fa-code"></i></div>
<div class="embed-box social-share" style="display: none;">
	<code>&#60;iframe frameBorder="0" scrolling="no" src="<?php echo home_url(); ?>/?ig_embed_widget=1&product_no=<?php echo (isset($project_id) ? $project_id : ''); ?>" width="214" height="366"&#62;&#60;/iframe&#62;</code>
</div>
<div class="clear"></div>