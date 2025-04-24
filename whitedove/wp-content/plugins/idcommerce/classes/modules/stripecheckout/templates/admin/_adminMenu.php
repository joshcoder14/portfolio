	
<div class="wrap ignitiondeck memberdeck">
	<div class="icon32" id=""></div><h2 class="title"><?php _e('Stripe Checkout Settings', 'memberdeck'); ?></h2>
	<div class="help">
		<a href="mailto:support@ignitionwp.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large button-primary"><?php _e('Support', 'memberdeck'); ?></button></a>
		<a href="https://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large button-primary"><?php _e('Documentation', 'memberdeck'); ?></button></a>
	</div>
	<form action="" method="POST" id="id_square_admin_settings">
		<div class="id-settings-container">
			<!--MainBox-->
			<div class="postbox-container" style="width:64%; margin-right:1%">
				<div class="metabox-holder">
					<div class="meta-box-sortables" style="min-height:0;">
						<div class="postbox">
							<h3 class="hndle"><span><?php _e('Stripe Merchant Settings', 'memberdeck'); ?></span></h3>
							<div class="inside" style="width: 50%; min-width: 400px;">
								<!--<form action="" method="POST" id="id_square_admin_settings">-->
									<p><?php printf(__('Please note that this gateway uses test mode settings found in the %1$sGateways menu%2$s', 'memberdeck'), '<a href="'.menu_page_url('idc-gateways', 0).'">', '</a>'); ?>.</p>
									<h3><?php _e('Staging Details', 'memberdeck'); ?></h3>
									<div class="form-input">
										<label for="stripe_staging_publishable_key"><?php _e('Stripe Publishable Key', 'memberdeck'); ?></label>
										<input type="text" name="stripe_staging_publishable_key" id="stripe_staging_publishable_key" value="<?php echo (isset($sc_settings['stripe_staging_publishable_key']) ? $sc_settings['stripe_staging_publishable_key'] : ''); ?>"/>
									</div>
									<div class="form-input">
										<label for="stripe_staging_secret_key"><?php _e('Stripe Secret Key', 'memberdeck'); ?></label><br/>
										<input type="text" name="stripe_staging_secret_key" id="stripe_staging_secret_key" value="<?php echo (isset($sc_settings['stripe_staging_secret_key']) ? $sc_settings['stripe_staging_secret_key'] : ''); ?>"/>
									</div>
									<h3><?php _e('Production Details', 'memberdeck'); ?></h3>
									<div class="form-input">
										<label for="stripe_publishable_key"><?php _e('Stripe Publishable Key', 'memberdeck'); ?></label>
										<input type="text" name="stripe_publishable_key" id="stripe_publishable_key" value="<?php echo (isset($sc_settings['stripe_publishable_key']) ? $sc_settings['stripe_publishable_key'] : ''); ?>"/>
									</div>
									<div class="form-input">
										<label for="stripe_secret_key"><?php _e('Stripe Secret Key', 'memberdeck'); ?></label><br/>
										<input type="text" name="stripe_secret_key" id="stripe_secret_key" value="<?php echo (isset($sc_settings['stripe_secret_key']) ? $sc_settings['stripe_secret_key'] : ''); ?>"/>
									</div>
									<br>
									<div class="form-input">
										<button class="button button-primary" id="id_stripe_settings_submit" name="id_stripe_settings_submit"><?php _e('Save', 'memberdeck'); ?></button>
									</div>
								<!--</form>-->
							</div>
						</div>
					</div>
				</div>
				<div class="form-check">
					<input type="checkbox" name="stripe_connect_enable" id="stripe_connect_enable" value="1" <?=isset($sc_settings['stripe_connect_enable']) && $sc_settings['stripe_connect_enable']==1?'checked="checked"':'';?>> 
					<label for="dev_mode">Enable Stripe Connect For Project Creators</label>
				</div>
			</div>
			<!--MainBox-->
			<!--Sidebar-->
			<div class="postbox-container" style="width:35%;">
				<div class="metabox-holder">
					<div class="meta-box-sortables" style="min-height:0;">
						<div class="postbox">
							<h3 class="hndle"><span>Important Gateway notes:</span></h3>
							<div class="inside">
								<strong>Stripe Webhook URL:</strong>
								<br>
								<p>In order to receive notifications of Stripe subscription payments, a production webhook under Developer &#10097;&#10097; Webhooks &#10097;&#10097; <b>Accounts</b> need to be created with the following format:</p>
								<p>https://[mydomain.com]/?memberdeck_notify=stripe</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!--Sidebar-->

			<div id="sc_display" style="<?=isset($sc_settings['stripe_connect_enable']) && $sc_settings['stripe_connect_enable']==1 ? '' : 'display:none';?>">
				<!--MainBox-->
				<div class="postbox-container" style="width:64%; margin:2em 1% 0 0">
					<div class="icon32" id="icon-options-general"></div><h2 class="title"><?php _e('Stripe Connect', 'memberdeck'); ?></h2>
					<div class="metabox-holder">
						<div class="meta-box-sortables" style="min-height:0;">
							<?php do_action('ide_above_sc_settings'); ?>
							<div class="postbox">
								<h3 class="hndle"><span><?php _e('Application Settings', 'memberdeck'); ?></span></h3>
								<div class="inside">
									<!--<form method="POST" action="" id="idsc_settings" name="idsc_settings">-->
										<!--<input type="hidden" name="stripe_connect_enable_val" id="stripe_connect_enable_val" value="<?=isset($sc_settings['stripe_connect_enable_val']) ? $sc_settings['stripe_connect_enable_val'] : '';?>">-->
										<?php do_action('ide_before_sc_settings'); ?>
										<div class="form-input">
											<p>
												<label for="client_id"><?php _e('Production Client ID', 'memberdeck'); ?></label><br/>
												<input type="text" name="client_id" id="client_id" value="<?=isset($sc_settings['client_id']) ? $sc_settings['client_id'] : '';?>"/>
											</p>
										</div>
										<div class="form-input">
											<p>
												<label for="dev_client_id"><?php _e('Development Client ID', 'memberdeck'); ?></label><br/>
												<input type="text" name="dev_client_id" id="dev_client_id" value="<?=isset($sc_settings['dev_client_id']) ? $sc_settings['dev_client_id'] : '';?>"/>
											</p>
										</div>
										<div class="form-select">
											<p>
												<label for="fee_type"><?php _e('Fee Type', 'memberdeck'); ?></label><br/>
												<select name="fee_type" id="fee_type">
													<option value="flat" <?=isset($sc_settings['fee_type']) && $sc_settings['fee_type'] == 'flat' ? 'selected="selected"' : '';?>><?php _e('Flat Fee (in cents)', 'memberdeck'); ?></option>
													<option value="percentage" <?=isset($sc_settings['fee_type']) && $sc_settings['fee_type'] == 'percentage' ? 'selected="selected"' : '';?>><?php _e('Percentage', 'memberdeck'); ?></option>
												</select>
											</p>
										</div>
										<div class="form-input">
											<p>
												<label for="app_fee"><?php _e('Fee Amount (numeric characters only)', 'memberdeck'); ?></label><br/>
												<input type="text" name="app_fee" id="app_fee" value="<?=isset($sc_settings['app_fee']) ? $sc_settings['app_fee'] : '';?>"/>
											</p>
										</div>
										<div class="form-input">
											<p>
												<label for="button-style"><?php _e('Button Style', 'memberdeck'); ?></label><br/>
												<select id="button-style" name="button-style">
													<option value="stripe-connect" <?=isset($sc_settings['button-style']) && $sc_settings['button-style'] == 'stripe-connect' ? 'selected="selected"' : '';?>><?php _e('Blue on Light', 'memberdeck'); ?></option>
													<option value="stripe-connect dark" <?=isset($sc_settings['button-style']) && $sc_settings['button-style'] == 'stripe-connect dark' ? 'selected="selected"' : '';?>><?php _e('Blue on Dark', 'memberdeck'); ?></option>
													<option value="stripe-connect light-blue" <?=isset($sc_settings['button-style']) && $sc_settings['button-style'] == 'stripe-connect light-blue' ? 'selected="selected"' : '';?>><?php _e('Light on Light', 'memberdeck'); ?></option>
													<option value="stripe-connect light-blue dark" <?=isset($sc_settings['button-style']) && $sc_settings['button-style'] == 'stripe-connect light-blue dark' ? 'selected="selected"' : '';?>><?php _e('Light on Dark', 'memberdeck'); ?></option>
												</select><br/>
												<span id="button-display">
													<a class="stripe-connect"><span><?php _e('Connect with Stripe', 'memberdeck'); ?></span></a>
												</span>
											</p>
										</div>
										<div class="form-check">
											<input type="checkbox" name="dev_mode" id="dev_mode" value="1" <?=isset($sc_settings['dev_mode']) && $sc_settings['dev_mode']==1 ? 'checked="checked"':'';?>/> 
											<label for="dev_mode"><?php _e('Enable Development Mode', 'memberdeck'); ?></label>
										</div>
										<?php do_action('ide_after_ssc_settings'); ?>
										<div class="submit">
											<input type="submit" name="id_sc_settings_submit" id="submit" value="Submit" class="button button-primary"/>
										</div>
									<!--</form>-->
								</div>
							</div>
							<div class="postbox">
								<h3 class="hndle"><span><?php _e('User Management', 'memberdeck'); ?></span></h3>
								<div class="inside">
									<!--<form method="POST" action="" id="idsc_users" name="idsc_users">-->
										<div class="form-input">
											<p><?php _e('Use this option to revoke or clear credentials of Stripe connected users.', 'memberdeck'); ?></p>
											<label for="clear_creds"><?php _e('Revoke Credentials', 'memberdeck'); ?></label><br/>
											<select id="clear_creds" name="clear_creds">
												<option value=""><?php _e('Select User', 'memberdeck'); ?></option>
											</select>
										</div>
										<div class="submit">
											<input type="submit" name="sc_revoke" id="sc_revoke" class="button" value="<?php _e('Revoke Credentials', 'memberdeck'); ?>"/>
										</div>
									<!--</form>-->
								</div>
							</div>
							<?php do_action('ide_below_sc_settings'); ?>
						</div>
					</div>
				</div>
				<!--MainBox-->
				<!--Sidebar-->
				<div class="postbox-container" style="width:35%;margin-top:3em">
					<h2 class="title">&nbsp;</h2>
					<div class="metabox-holder">
						<div class="meta-box-sortables" style="min-height:0;">
							<div class="postbox">
								<h3 class="hndle"><span>Important Stripe Connect notes:</span></h3>
								<div class="inside">
									<strong>Stripe Connect Return URI:</strong>
									<br>
									<p>In order to successfully connect a creator's Stripe account and return the connection to the site, set up a Return URI within the Stripe Dashboard. This is located under Settings &#10097;&#10097; Connect Settings &#10097;&#10097; Integration &#10097;&#10097; Redirect and should be formatted as:</p>
									<p>https://yourdomain.com/[your-dashboard-link]/?payment_settings=1&ipn_handler=sc_return</p>
									<br><br>
									<strong>Stripe Connect Webhooks:</strong>
									<br>
									<p>In order to receive notifications of Stripe Connect events, a production webhook under Development &#10097;&#10097; Webhooks &#10097;&#10097; <b>Connect</b> needs to be created with the following format:</p>
									<p>https://yourdomain.com/?ipn_handler=connect</p>
									<br>
									<p>In order to receive notifications of Stripe subscription payment, a production webhook under Development &#10097;&#10097; Webhooks &#10097;&#10097; <b>Connect</b> needs to be created with the following format:</p>
									<p>https://yourdomain.com/?memberdeck_notify=stripe</p>
									<br>
									<p>* Creators receiving subscription funding must also create the <b>Connect</b> webhook in their own Stripe account with the format:</p>
									<p>https://yourdomain.com/?memberdeck_notify=stripe</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!--Sidebar-->
			</div>
		</div>
	</form>
</div>