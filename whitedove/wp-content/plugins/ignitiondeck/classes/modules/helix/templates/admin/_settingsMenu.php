<div class="wrap ignitiondeck">
	<div class="icon32" id=""></div><h2 class="title"><?php esc_html_e('Helix Settings', 'idhelix'); ?></h2>
	<div class="help">
		<a href="mailto:support@ignitionwp.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large"><?php esc_html_e('Support', 'idhelix'); ?></button></a>
		<a href="https://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large"><?php esc_html_e('Documentation', 'idhelix'); ?></button></a>
	</div>
	<div class="id-settings-container">
		<div class="postbox-container" style="width:45%;">
			<div class="metabox-holder">
				<div class="meta-box-sortables" style="min-height:0;">
					<div class="postbox">
						<h3 class="hndle"><span><?php esc_html_e('Helix Settings', 'idhelix'); ?></span></h3>
						<div class="inside" style="width: 50%; min-width: 400px;">
							<form action="" method="POST" id="idhelix_settings">
								<h4><?php esc_html_e('Menu Settings', 'idhelix'); ?></h4>
								<?php wp_nonce_field('helix_save_settings', 'helix_nonce'); ?>
								<div class="form-input half left">
									<label for="menu_position"><?php esc_html_e('Menu Position', 'idhelix'); ?></label><br />
									<select id="menu_position" name="menu_position">
										<option value="left" <?php echo (empty($settings['menu_position']) || $settings['menu_position'] == "left" ? 'selected="selected"' : '') ?>>Left</option>
										<option value="right" <?php echo (empty($settings['menu_position']) || $settings['menu_position'] == "right" ? 'selected="selected"' : '') ?>>Right</option>
									</select>
								</div>
								<div class="form-input half">
									<label for="menu_style"><?php esc_html_e('Menu Style', 'idhelix'); ?></label><br />
									<select id="menu_style" name="menu_style">
										<option value="light" <?php echo (empty($settings['menu_style']) ||  $settings['menu_style'] == "light" ? 'selected="selected"' : '') ?>>Light</option>
										<option value="dark" <?php echo (empty($settings['menu_style']) || $settings['menu_style'] == "dark" ? 'selected="selected"' : '') ?>>Dark</option>
									</select>
								</div>
								<br />
								<div class="form-row">
									<button class="button button-primary button-large" id="submit_helix_settings" name="submit_helix_settings"><?php esc_html_e('Save', 'idhelix'); ?></button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>