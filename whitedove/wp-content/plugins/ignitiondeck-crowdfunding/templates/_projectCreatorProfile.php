<div class="ignitiondeck id-creatorprofile">
	<?php
	$usermeta = get_user_meta($profile['author']);
	$idc_avatar = (isset($usermeta['idc_avatar'][0]) ? $usermeta['idc_avatar'][0] : '');
	if (!empty($idc_avatar)) {
		$idc_avatar_data = wp_get_attachment_image_src($usermeta['idc_avatar'][0]);
		$profile['logo'] = $idc_avatar_data[0];
	} else {
		if(!isset($user_id)) $user_id=$profile['author'];
		$idc_avatar_data = get_avatar($user_id);
		$profile['logo'] = get_avatar_url($user_id);
	}
	?>
	<div class="id-creator-avatar"><a href="<?php echo $durl . '?creator_profile=' . $profile['author']; ?>"><img src="<?php echo ( ! empty( $profile['logo'] ) ? $profile['logo'] : '' ); ?>" title="<?php echo ( isset( $profile['name'] ) ? $profile['name'] : '' ); ?>"/></a></div>
	<div class="id-creator-content">
		<div class="id-creator-name"><a href="<?php echo $durl . '?creator_profile=' . $profile['author']; ?>"><?php echo ( isset( $profile['name'] ) ? $profile['name'] : '' ); ?></a></div>
		<div class="id-creator-location"><?php echo ( isset( $profile['location'] ) ? $profile['location'] : '' ); ?></div>
	</div>
	<div class="id-creator-links">
		<?php if ( ! empty( $profile['twitter'] ) ) { ?>
		<a href="<?php echo $profile['twitter']; ?>" class="twitter" title="<?php _e( 'Twitter', 'ignitiondeck' ); ?>"><?php _e( 'Twitter', 'ignitiondeck' ); ?></a>
		<?php } ?>
		<?php if ( ! empty( $profile['facebook'] ) ) { ?>
		<a href="<?php echo $profile['facebook']; ?>" class="facebook" title="<?php _e( 'Facebook', 'ignitiondeck' ); ?>"><?php _e( 'Facebook', 'ignitiondeck' ); ?></a>
		<?php } ?>
		<!--<a href="#" class="googleplus"></a>-->
		<?php if ( ! empty( $profile['url'] ) ) { ?>
		<a href="<?php echo apply_filters( 'ide_company_url', $profile['url'] ); ?>" class="website" title="<?php _e( 'Website', 'ignitiondeck' ); ?>"><?php echo $profile['url']; ?></a>
		<?php } ?>
	</div>
	<div class="cf"></div>
</div>
