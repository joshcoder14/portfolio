<div class="memberdeck ignitiondeck">
	<?php include IDC_PATH.'templates/_mdProfileTabs.php'; //changed include_once to include for Issue #17 b ?>
	<ul class="md-box-wrapper full-width cf">
	<?php echo (isset($error) ? '<p class="error">'.$error.'</p>' : ''); ?>
	<?php echo (isset($success) ? '<p class="success">'.$success.'</p>' : ''); ?>
	<form action="?edit-profile=<?php echo (isset($current_user->ID) ? $current_user->ID : ''); ?>&amp;edited=1" method="POST" id="edit-profile" name="edit-profile" enctype="multipart/form-data">
		<li class="md-box">
			<div class="md-profile">
				<div id="logged-input" class="no">
					<p><h2 class="border-bottom"><?php _e('Profile Information', 'memberdeck'); ?></h2></p>
					<p class="desc-note"><strong><?php _e('Note: ', 'memberdeck'); ?></strong><?php _e('Fields marked with <span class="starred">*</span> may display publicly.', 'memberdeck'); ?></p>
					<div class="form-row third left wp-media-buttons">
						<label for="idc_avatar"><?php _e('Upload Avatar', 'memberdeck'); ?></label>
						<button type="button" name="idc_avatar_selector" id="idc_avatar_selector" class="button insert-media add_media" data-input="idc_avatar"><?php _e('Select Image', 'memberdeck'); ?></button>
						<input type="hidden" id="idc_avatar" name="idc_avatar" class="main-setting" value="<?php echo (!empty($idc_avatar) ? $idc_avatar : ''); ?>" />
					</div>
					<div class="form-row wp_media_image idc_avatar_image twothird">
						<img src="<?php echo ((!empty($idc_avatar_data)) ? $idc_avatar_url : ''); ?>" style="max-width:100%; <?php echo (!empty($idc_avatar_data) ? '' : 'display:none;') ?>" />
					</div>
					<div class="form-row half left">
						<label for="nicename"><?php _e('Display Name', 'memberdeck'); ?> <span class="starred">*</span></label>
						<input type="text" size="20" class="nicename" name="nicename" value="<?php echo (isset($nicename) ? $nicename : ''); ?>"/>
					</div>
					<div class="form-row quarter left">
						<label for="first-name"><?php _e('First Name', 'memberdeck'); ?></label>
						<input type="text" size="20" class="first-name" name="first-name" value="<?php echo (isset($user_firstname) ? $user_firstname : ''); ?>"/>
					</div>
					<div class="form-row quarter">
						<label for="last-name"><?php _e('Last Name', 'memberdeck'); ?></label>
						<input type="text" size="20" class="last-name" name="last-name" value="<?php echo (isset($user_lastname) ? $user_lastname : ''); ?>"/>
					</div>
					<div class="form-row full">
						<label for="email"><?php _e('Email Address', 'memberdeck'); ?></label>
						<input type="email" size="20" class="email" name="email" value="<?php echo (isset($email) ? $email : ''); ?>"/>
					</div>
                    	
					<div class="form-row full">
						<label for="description"><?php _e('Bio', 'memberdeck'); ?> <span class="starred">*</span></label>
						<textarea row="10" class="description" name="description"><?php echo (isset($description) ? stripslashes(html_entity_decode($description)) : ''); ?></textarea>
					</div>

					<div class="form-row half left">
						<label for="location"><?php _e('Company Location', 'memberdeck'); ?> <span class="starred">*</span></label>
						<input type="location" size="20" class="location" name="location" value="<?php echo (isset($location) ? $location : ''); ?>"/>
					</div>
                    
					<div class="form-row half">
						<label for="url"><?php _e('Website URL', 'memberdeck'); ?> <span class="starred">*</span></label>
						<input type="url" size="20" class="url" name="url" value="<?php echo (isset($url) ? $url : ''); ?>"/>
					</div>
				
					<div class="form-row third left">
						<label for="twitter"><?php _e('Twitter URL', 'memberdeck'); ?> <span class="starred">*</span></label>
						<input type="twitter" size="20" class="twitter" name="twitter" value="<?php echo (isset($twitter) ? $twitter : ''); ?>"/>
                    </div>
                    <div class="form-row third left">
						<label for="facebook"><?php _e('Facebook URL', 'memberdeck'); ?> <span class="starred">*</span></label>
						<input type="facebook" size="20" class="facebook" name="facebook" value="<?php echo (isset($facebook) ? $facebook : ''); ?>"/>
                    </div>
                    <div class="form-row third">
						<label for="google"><?php _e('Google URL', 'memberdeck'); ?> <span class="starred">*</span></label>
						<input type="google" size="20" class="google" name="google" value="<?php echo (isset($google) ? $google : ''); ?>"/>
					</div>
					<?php echo do_action('idc_profile_social_after'); ?>

					<p>
						<h2 class="border-bottom"><?php _e('Password Modification', 'memberdeck'); ?></h2></p>
						<p class="desc-note"><strong><?php _e('Note:', 'memberdeck'); ?></strong> <?php _e('changing your password will clear login cookies. You will need to login again after saving.', 'memberdeck'); ?></p>
					<div class="form-row half left">
						<label for="pw"><?php _e('Password', 'memberdeck'); ?></label>
						<input type="password" size="20" class="pw" name="pw"/>
					</div>
					<div class="form-row half">
						<label for="cpw"><?php _e('Re-enter Password', 'memberdeck'); ?></label>
						<input type="password" size="20" class="cpw" name="cpw"/>
					</div>
					
					<?php echo do_action('md_profile_extrafields'); ?>
					<?php if (!is_idc_free()) { ?>
					<h2 id="instantcheckout" class="border-bottom"><?php _e('Payment Settings', 'memberdeck'); ?></h2>
					<?php if ($show_subscriptions) { ?>
					<strong><?php _e('Subscriptions', 'memberdeck'); ?></strong>
					<p class="desc-note"><?php _e('Manage active subscriptions', 'memberdeck'); ?></p>
					<div class="form-row">
						<p class="sub_response"></p>
					</div>
					<div class="form-row half">
						<span class="idc-dropdown">
							<select name="sub_list" class="idc-dropdown__select .idc-dropdown__select--white" data-userid="<?php echo $user_id; ?>">
								<option value="0"><?php _e('Select Subscription', 'memberdeck'); ?></option>
								<?php if (isset($plans)) {
									foreach ($plans as $plan) {
										echo '<option value="'.$plan['id'].'" data-gateway="'.$plan['gateway'].'">'.$plan['plan_id'].'</option>';
									}
								}
								?>
							</select>
						</span>
					</div>
					<div class="form-row half">
						&nbsp;<button name="cancel_sub" class="hidden invert inline" disabled><?php _e('Cancel Subscription', 'memberdeck'); ?></button>
					</div>
					<?php } ?>
					<h2><?php _e('Instant Checkout', 'memberdeck'); ?></h2>
					<p class="desc-note">	<?php _e('With instant checkout enabled, you can pay with your credit card without re-entering information. To enable, simply use your credit card to checkout once, and then select \'enable instant checkout\' from this screen, and click \'Update Profile\' below.', 'memberdeck'); ?><br><br>
						<?php _e('Your credit card information is never stored on our servers, and is always processed securely.', 'memberdeck'); ?>
					</p>
					<?php if (!empty($show_icc)) { ?>
					<p class="form-check" style="margin-left: 0;">
						<input type="checkbox" class="instant_checkout" name="instant_checkout" <?php echo (isset($instant_checkout) && $instant_checkout == 1 ? 'checked="checked"' : ''); ?> value="1"/>
						&nbsp;
						<label for="instant_checkout"><?php _e('Enable Instant Checkout', 'memberdeck'); ?></label>
					</p>
				<?php do_action('md_profile_extrasettings'); ?>
				<?php } ?>
				<?php } ?>
                <div class="border-bottom"></div>
					<button type="submit" id="edit-profile-submit" class="submit-button" name="edit-profile-submit"><?php _e('Update Profile', 'memberdeck'); ?></button>
				</div>
			</div>
		</li>
	</form>
	</ul>
</div>
<script>
	jQuery(document).on('idfMediaPopup', function(e, inputID) {
		jQuery('.wp_media_image').data('active', 0);
		jQuery('.' + inputID + '_image').data('active', 1);
	});
	jQuery(document).on('idfMediaSelected', function(e, attachment) {
		if (jQuery('.idc_avatar_image').data('active') === 1) {
			jQuery('.idc_avatar_image').data('active', 0);
   			jQuery('.idc_avatar_image').children('img').attr('src', attachment.url).show();
   		}
   	});
</script>