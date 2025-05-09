<div class="wrap">
	<div class="extension_header">
		<h1><?php esc_html_e('IgnitionDeck Themes', 'idf'); ?></h1>
	</div>
	<?php
	if (!empty($data)) {
		foreach ($data as $item) {
			$title = $item->title;
			$desc = $item->short_desc;
			$link = $item->link;
			$thumbnail = $item->thumbnail;
			$text = __('Get Theme', 'idf');
			$installed = false;
			$active = false;
			if (in_array($title, $name_array) || in_array('500 '.$title, $name_array)) {
				$installed = true;
				$text = __('Activate', 'idf');
				$link = site_url('wp-admin/themes.php?page=theme-settings');
				if ($active_name == $title || $active_name == '500 '.$title) {
					$text = __('Configure', 'idf');
					$active = true;
				}
			}
			?>
			<div class="extension">
				<div class="extension-image" style="background-image: url(<?php echo esc_url($thumbnail); ?>);"></div>
				<p class="extension-desc"><?php echo wp_kses_post($desc); ?></p>
				<div class="extension-link">
					<?php if ($installed && !$active) { ?>
						<button class="button activate_theme" data-theme="<?php echo esc_attr($item->slug); ?>"><?php esc_html_e('Activate', 'idf'); ?></button>
					<?php } else { ?>
						<button class="button <?php echo (!$active ? 'button-primary' : ''); ?>" onclick="window.open('<?php echo esc_url($link); ?>', '_blank')"><?php echo esc_html($text); ?></button>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
	<?php } ?>
</div>