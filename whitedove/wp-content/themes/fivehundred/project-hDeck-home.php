<?php
$options = get_option('fivehundred_theme_settings');
if (!empty($options['home'])) {
	$project_id = $options['home'];
	$new_hdeck = new Deck($project_id);
	$hDeck = $new_hdeck->hDeck();
	$id = getPostbyProductID($project_id);
	$permalinks = get_option('permalink_structure');
	$summary = the_project_summary($id);
	do_action('fh_hDeck_before');
	$video = the_project_video($id);
}
$prefix = idf_get_querystring_prefix();
?>
<?php if (isset($hDeck)) { ?>
<div id="ign-hDeck-wrapper">
	<div id="ign-hdeck-wrapperbg">
		<div id="ign-hDeck-header">
			<div id="ign-hDeck-left">
				<div class="video <?php echo (!empty($video) ? 'hasvideo' : ''); ?>" style="background-image: url(<?php echo $summary->image_url; ?>)"><?php echo $video; ?> </div>
				<div id="ign-hDeck-social">
					<?php do_action('idf_general_social_buttons', $id, $project_id); ?>
                    <!-- Hook to insert social sharing tools -->
                    <div id="ignitiondeck_share_project_page">
                        <?php do_action( 'ignitiondeck_share_public_project_page', $id); ?>
                    </div>
				</div>
			</div>
			<div id="ign-hDeck-right">
				<div class="internal">
					<div class="ign-product-goal" style="clear: both;">
						<div class="ign-goal"><?php _e('Goal', 'fivehundred'); ?></div> <strong><?php echo $hDeck->goal; ?> </strong>
					</div>
					<?php //if (isset($hDeck->show_dates) && $hDeck->show_dates == true) { ?>
					<?php //if (isset($hDeck->days_left) && $hDeck->days_left > 0) { ?>
					<div class="ign-days-left">
						<strong><?php echo $hDeck->days_left; ?> <?php _e('Days Left', 'fivehundred'); ?></strong>
					</div>
					<?php //} ?>
					<div class="ign-progress-wrapper" style="clear: both;">
						<div class="ign-progress-percentage">
										<?php echo $hDeck->percentage; ?>%
						</div> <!-- end progress-percentage -->
						<div style="width: <?php echo $hDeck->percentage; ?>%" class="ign-progress-bar">
						
						</div><!-- end progress bar -->
					</div>
					
					<div class="ign-progress-raised">
						<strong><?php echo $hDeck->total; ?></strong>
						<div class="ign-raised">
							<?php _e('Raised', 'fivehundred'); ?>
						</div>
					</div>
					<div class="ign-product-supporters" style="clear: both;">
						<strong><?php echo $hDeck->pledges; ?></strong>
						<div class="ign-supporters">
							<?php _e('Supporters', 'fivehundred'); ?>
						</div>
					</div>
					<div id="hDeck-right-bottom">
						<div class="ign-supportnow" data-projectid="<?php echo $project_id; ?>">
						<?php if ($hDeck->end_type == 'closed' && $hDeck->days_left <= 0) { ?>
							<a href=""><?php _e('Project Closed', 'fivehundred'); ?></a>
						<?php } else { ?>
							<a href="<?php echo get_permalink($id).$prefix; ?>&purchaseform=500&amp;prodid=<?php echo (isset($project_id) ? $project_id : ''); ?>"><?php _e('Support Now', 'fivehundred'); ?></a>
						<?php } ?>
						</div>
						<?php //if (isset($hDeck->show_dates) && $hDeck->show_dates == true) { ?>
						<div class="ign-product-proposed-end"><span><?php _e('Project Ends', 'fivehundred'); ?>:</span>
							<div id="ign-widget-date">
								<div id="ign-widget-month"><?php echo $hDeck->month; ?></div>
								<div id="ign-widget-day"><?php echo $hDeck->day; ?></div>
								<div id="ign-widget-year"><?php echo $hDeck->year; ?></div>
							</div>
							<div class="clear"></div>
						</div>
						<?php //} ?>
					</div>
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>
<?php } 
else { ?>
<div id="ign-hDeck-wrapper">
	<div id="ign-hdeck-wrapperbg">
		<div id="ign-hDeck-header">
			<div id="ign-hDeck-left">
			</div>
			<div id="ign-hDeck-right">
				<div class="internal">
				</div>
			</div>
			<div class="clear"></div>
		</div>
	</div>
</div>
<?php } ?>