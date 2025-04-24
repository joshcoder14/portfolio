<div class="wrap">
	<div class="extension_header">
		<h1><?php echo esc_html__('IgnitionDeck Modules', 'idf'); ?></h1>
		<?php echo wp_kses_post(__('IgnitionDeck Modules allow you to upgrade, modify, and customize the default IgnitionDeck installation in order to achieve additional features sets.', 'idf')); ?>
		<?php if (!is_id_pro()) {
			printf(
				// Translators: %1$s: Opening HTML tag for link, %2$s: Closing HTML tag for link
				wp_kses_post(__('%1$sUpgrade to Enterprise%2$s to fully unlock all available modules.', 'idf')),
				'<p><a href="' . esc_url('https://ignitiondeck.com/id/ignitiondeck-pricing/?utm_source=idf&utm_medium=link&utm_campaign=freemium') . '">',
				'</a></p>'
			);
		} ?>
		<p><?php echo esc_html__('Use the dropdown below to sort by category', 'idf'); ?>.</p>
	</div>
	<div class="extension_subheader form-row">
		<?php if (idf_dev_mode()) : ?>
			<p>
				<button class="bulk_deactivate_modules button left" onclick="non('id_modules')">
					<?php echo esc_html__('Deactivate All Modules', 'idf'); ?>
				</button>
			</p>
		<?php endif; ?>
	</div>
	<div class="extension_subheader form-row inline">
		<select name="module_filter">
			<option value="extension"><?php echo esc_html__('Show All', 'idf'); ?></option>
			<option value="accounts"><?php echo esc_html__('Account Management', 'idf'); ?></option>
			<option value="analytics"><?php echo esc_html__('Analytics', 'idf'); ?></option>
			<option value="commerce"><?php echo esc_html__('Commerce', 'idf'); ?></option>
			<option value="crowdfunding"><?php echo esc_html__('Crowdfunding', 'idf'); ?></option>
			<option value="interface"><?php echo esc_html__('Interface Customizations', 'idf'); ?></option>
			<option value="gateways"><?php echo esc_html__('Payment Gateways', 'idf'); ?></option>
			<option value="security"><?php echo esc_html__('Security', 'idf'); ?></option>
			<option value="social"><?php echo esc_html__('Social', 'idf'); ?></option>
		</select>
		<input type="checkbox" id="hide_locked" name="hide_locked" class="sep" value="1" />
		<label for="hide_locked"><?php echo esc_html__('Hide Locked', 'idf'); ?></label>
	</div>
	<?php
	if (empty($data)) {
		return;
	}
	foreach ($data as $item) {
		$locked = ID_Modules::is_module_locked($item);
		$installed = false;
		$active = false;
		$is_plugin = false;
		$text = __('Get Module', 'idf');
		$type = (isset($item->type) ? $item->type : 'plugin');
		if ($type == 'plugin') {
			# Standardize plugin tags
			$tags = $item->tags;
			if (!empty($tags) && is_array($tags)) {
				$tag_array = array();
				foreach ($tags as $tag) {
					$tag_array[] = strtolower($tag->name);
				}
				$tags = implode(' ', $tag_array);
				$item->tags = $tags;
			}
		}
		$plugin_path = dirname(IDF_PATH).'/'.$item->basename.'/'.$item->basename.'.php';
		if (file_exists($plugin_path)) {
			// is an installed plugin
			$installed = true;
			$is_plugin = true;
			$text = __('Activate Plugin', 'idf');
			if (is_plugin_active($item->basename.'/'.$item->basename.'.php')) {
				$active = true;
				$text = __('Installed', 'idf');
			}
		}
		if (!($is_plugin) && $type == 'module') {
			$new_status = (!empty($active_modules) && in_array($item->basename, $active_modules) ? 0 : 1);
			$module_status_nonce = wp_create_nonce('module_status_nonce');
			$item->link .= '&module_status='.$new_status.'&_wpnonce='.$module_status_nonce;
			switch ($new_status) {
				case 1:
					$text = __('Activate', 'idf');
					break;
				
				case 0:
					$text = __('Deactivate', 'idf');
					break;
			}
		}
		?>
		<div class="<?php echo esc_attr(apply_filters('id_module_list_wrapper_class', $item->tags, $item)); ?>" 
			data-requires="<?php echo esc_attr(apply_filters('id_module_list_requires', isset($item->requires) ? $item->requires : '', $item)); ?>" 
			data-locked="<?php echo esc_attr($locked); ?>">

			<?php if ($locked) { ?>
				<a class="lock-url" 
				href="<?php echo esc_url('https://ignitiondeck.com/id/ignitiondeck-pricing/?utm_source=idf_extensions&utm_medium=link&utm_campaign=freemium'); ?>" 
				target="_blank">
			<?php } ?>
			
			<div class="extension-image" style="background-image: url('<?php echo esc_url($item->thumbnail); ?>');"></div>
			<p class="extension-desc"><?php echo esc_html($item->short_desc); ?></p>

			<?php if ($locked) { ?>
				<div class="extension-lock">
					<i class="fa fa-lock"></i>
				</div>
				</a>
			<?php } else { ?>
				<div class="extension-link">
					<button class="button <?php echo esc_attr(!$active && !$installed ? 'button-primary' : 'active-installed'); ?>" 
							<?php echo !empty($item->link) ? 'onclick="location.href=\'' . esc_url($item->link) . '\'"' : ''; ?> 
							<?php echo $active ? 'disabled="disabled"' : ''; ?> 
							data-extension="<?php echo esc_attr($item->basename); ?>">
						<?php echo esc_html($text); ?>
					</button>
					<?php if (!empty($item->doclink)) { ?>
						<button class="button" onclick="window.open('<?php echo esc_url($item->doclink); ?>')">
							<?php echo esc_html__('Docs', 'idf'); ?>
						</button>
					<?php } ?>
				</div>
			<?php } ?>
		</div>
	<?php } ?>
</div>