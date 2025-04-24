<div class="wrap ignitiondeck">
	<div class="icon32" id=""></div><h2 class="title"><?php esc_html_e('reCAPTCHA Settings', 'memberdeck'); ?></h2>
	<div class="help">
		<a href="mailto:support@ignitionwp.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large button-primary"><?php esc_html_e('Support', 'memberdeck'); ?></button></a>
		<a href="https://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large button-primary"><?php esc_html_e('Documentation', 'memberdeck'); ?></button></a>
	</div>
	<div class="id-settings-container">
		<div class="postbox-container" style="width:95%; margin-right:5%">
			<div class="metabox-holder">
				<div class="meta-box-sortables" style="min-height:0;">
					<div class="postbox">
						<h3 class="hndle"><span><?php esc_html_e('API Keys', 'memberdeck'); ?></span></h3>
						<div class="inside" style="width: 50%; min-width: 400px;">
							<form action="" method="POST" id="id_recaptcha_settings">
							<?php wp_nonce_field('recaptcha_save_settings', 'recaptcha_nonce'); ?>
								<div class="form-input">
									<label for="id_recaptcha_type"><?php esc_html_e('reCAPTCHA Type', 'memberdeck'); ?></label>
									<div> 
										<div class="form-input inline">
											<input type="radio" name="id_recaptcha_type" id="v2" value="v2" <?php echo (isset($settings['id_recaptcha_type']) && $settings['id_recaptcha_type'] == 'v2') ? 'checked="checked"' : ''; ?>/>
											<label for="v2">Version 2</label>
										</div>
										<div class="form-input inline">
											<input type="radio" name="id_recaptcha_type" id="v3" value="v3" <?php echo (isset($settings['id_recaptcha_type']) && $settings['id_recaptcha_type'] == 'v3') ? 'checked="checked"' : ''; ?>/>
											<label for="v3">Version 3</label>
										</div>
									</div>
								</div>
								<div class="form-input">
									<label for="id_recaptcha_site_id"><?php esc_html_e('Site Key', 'memberdeck'); ?></label>
									<input type="text" name="id_recaptcha_site_id" id="id_recaptcha_site_id" value="<?php echo isset($settings['id_recaptcha_site_id']) ? esc_attr($settings['id_recaptcha_site_id']) : ''; ?>"/>
								</div>
								<div class="form-input">
									<label for="id_recaptcha_secret_key"><?php esc_html_e('Secret Key', 'memberdeck'); ?></label>
									<input type="text" name="id_recaptcha_secret_key" id="id_recaptcha_secret_key" value="<?php echo isset($settings['id_recaptcha_secret_key']) ? esc_attr($settings['id_recaptcha_secret_key']) : ''; ?>"/>
								</div>
								<p><a href="https://www.google.com/recaptcha/admin#list" target="_blank"><?php esc_html_e('Generate API Keys', 'idf'); ?></a></p>
								<div class="form-row">
									<button class="button button-primary" id="submit_id_recaptcha_settings" name="submit_id_recaptcha_settings"><?php esc_html_e('Save', 'memberdeck'); ?></button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>