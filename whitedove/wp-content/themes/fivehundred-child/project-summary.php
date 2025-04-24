<?php
	global $post;
	$id = $post->ID;
	$summary = the_project_summary($id);
	do_action('fh_project_summary_before');
?>

<div class="ign-project-summary <?php echo (!empty($summary->successful) ? 'success' : ''); ?> <?php echo 'post-'.$id; ?>">
	<a href="<?php echo the_permalink(); ?>">
	
		<div class="ign-summary-container">
			<div class="ign-summary-image" style="background-image: url(<?php echo $summary->image_url; ?>)">
				<div class="ign-summary-learnmore">
					<span><?php _e('View More', 'fivehundred'); ?></span>
				</div>
			</div>
			<?php if ( !empty( $terms ) ) : ?>
				<div class="project-tag">
					<?php
						$site_url = home_url();
						$cat_name = "";
						foreach($terms as $term){
							if($term->count > 0){
								$cat_name .= $term->name;
								break;
							}
						}
						if($term->count > 0){ echo $cat_name; }
					?>
				</div>
			<?php endif; ?>
			<div class="details">
				<div class="title">
					<h3>
						<?php echo $summary->name; ?>
					</h3>
				</div>
				<span class="ign-summary-desc">
					<?php echo $summary->short_description; ?>
				</span>
				<div class="ign-progress-wrapper">
					<div class="progress">
						<div class="ign-progress-bar" style="width: <?php echo $summary->percentage.'%'; ?>"></div>
						<div class="ign-progress-percentage">
							<?php echo $summary->percentage.'%'; ?>
						</div>
					</div>
					
					<div class="ign-progress-raised">
						<span><?php echo $summary->total; ?></span> <?php _e('RAISED', 'fivehundred'); ?>
					</div>
				</div>
				<?php if (isset($summary->show_dates) && $summary->show_dates == true) { ?>
					<div class="ign-summary-days">
						<strong><?php echo $summary->days_left; ?></strong>
						<?php echo ($summary->days_left < 0 ? '<span> '.__('Days Left', 'fivehundred').'</span>' : '<span> '.__('Days Left', 'fivehundred').'</span>');?>
					</div>
				<?php } ?>
			</div>
		</div>

	</a> 
</div>