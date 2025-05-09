<?php
$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
if ( !in_array( 'idsocial/idsocial.php', $active_plugins ) ) return;
global $post;
$post_id = !empty($post_id) ? $post_id : $post->ID;
do_action('id_social_sharing_before');
if (isset($social_settings['social_checks']['prod_page_fb'])) {
	echo '<iframe src="//www.facebook.com/plugins/share_button.php?href='.(!empty($post_id) ? get_permalink($post_id) : get_permalink($post->ID)).'&layout=button&size=small&width=78&height=28&appId" width="78" height="28" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>';
}
if (isset($social_settings['social_checks']['prod_page_twitter'])) {
	echo '<a class="twitter-share-button" href="https://twitter.com/intent/tweet?text='.(!empty($post_id) ? get_the_title($post_id) : get_the_title($post->ID)).'"></a>';
	echo '<script>window.twttr = (function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0],
		  t = window.twttr || {};
		if (d.getElementById(id)) return t;
		js = d.createElement(s);
		js.id = id;
		js.src = "https://platform.twitter.com/widgets.js";
		fjs.parentNode.insertBefore(js, fjs);
	  
		t._e = [];
		t.ready = function(f) {
		  t._e.push(f);
		};
	  
		return t;
	  }(document, "script", "twitter-wjs"));</script>';
}
if (isset($social_settings['social_checks']['prod_page_linkedin'])) {
	echo '<script src="//platform.linkedin.com/in.js" type="text/javascript">lang: en_US</script><script type="IN/Share" data-url="'.get_permalink($post_id).'"></script>';
}
if (isset($social_settings['social_checks']['prod_page_google'])) {
	
	echo '<div id="share-google" class="social-share social-button"><span class="g-plus" data-action="share" data-width="67" data-href="'.(!empty($post_id) ? get_permalink($post_id) : get_permalink($post->ID)).'" data-annotation="none"><script src="https://apis.google.com/js/platform.js" async defer></script></span></div>';
}
if (isset($social_settings['social_checks']['prod_page_pinterest'])) {
	echo '<div id="share-pinterest" class="social-share social-button"><a style="display:none" href="https://www.pinterest.com/pin/create/button/?url='.(!empty($post_id) ? get_permalink($post_id) : get_permalink($post->ID)).'&media='.(get_the_post_thumbnail(isset($post_id) ? $post_id : $post->ID)).'&description='.(isset($description) ? urlencode($description) : null).'" data-pin-do="buttonPin" data-pin-save="true"></a><script async defer src="//assets.pinterest.com/js/pinit.js"></script></div>';
}

do_action('id_social_sharing_after');
?>