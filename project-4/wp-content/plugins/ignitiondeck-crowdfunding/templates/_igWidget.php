<?php if ( isset( $show_mini ) && $show_mini == true ) {
	include '_miniWidget.php';
} else { ?>
	<?php echo ( isset( $float ) ? '<div class="id-widget-wrap id-complete-deck">' : '<div class="id-widget-wrap nofloat">' ); ?>
	<div class="ignitiondeck id-widget id-full" data-projectid="<?php echo ( isset( $project_id ) ? $project_id : '' ); ?>">
		<div class="id-product-infobox">
			<div class="product-wrapper">
				<?php echo do_action( 'id_widget_before', $project_id, $the_deck ); ?>
                <!-- Hook to insert social sharing tools -->
                <div id="ignitiondeck_share_public_project_page">
                    <?php 
                        global $post;
                        echo do_action( 'ignitiondeck_share_public_project_page', $post->ID );
                    ?>
				</div>
				<div class="pledge">
					<?php if ( ! $custom || ( $custom && isset( $attrs['project_title'] ) ) ) { ?>
						<h2 class="id-product-title"><a href="<?php echo getProjectURLfromType( $project_id ); ?>"><?php echo stripslashes( get_the_title( $the_deck->post_id ) ); ?></a></h2>
					<?php } ?>
					<?php if ( ! $custom || ( $custom && isset( $attrs['project_bar'] ) ) ) { ?>
					<div class="progress-wrapper">
						<div class="progress-percentage"> <?php echo $the_deck->rating_per; ?>% </div>
						<div class="progress-bar" style="width: <?php echo $the_deck->rating_per; ?>%"> 
						</div>
						<!-- end progress bar --> 
					</div>
					<!-- end progress wrapper --> 
					<?php } ?>
				</div>
				
				<!-- end pledge -->
				
				<div class="clearing"></div>
				<?php if ( ! $custom || ( $custom && isset( $attrs['project_pledged'] ) ) ) { ?>
					<div class="id-progress-raised"> <?php echo $the_deck->p_current_sale; ?> </div>
				<?php } ?>
				<?php if ( ! $custom || ( $custom && isset( $attrs['project_goal'] ) ) ) { ?>
					<div class="id-product-funding"><?php _e( 'Pledged of', 'ignitiondeck' ); ?> <?php echo $the_deck->item_fund_goal; ?> <?php _e( 'Goal', 'ignitiondeck' ); ?></div>
				<?php } ?>
				<?php if ( ! $custom || ( $custom && isset( $attrs['project_pledgers'] ) ) ) { ?>
					<div class="id-product-total"><?php echo $the_deck->p_count->p_number; ?></div>
					<div class="id-product-pledges"><?php _e( 'Pledgers', 'ignitiondeck' ); ?></div>
				<?php } ?>
				<?php if ( ! $custom || ( $custom && isset( $attrs['days_left'] ) ) ) { ?>
					<?php if ( isset( $the_deck->days_left ) && $the_deck->days_left > 0 ) { ?>
						<div class="id-product-days"><?php echo ( ( $the_deck->days_left !== '' || $the_deck->days_left !== 0 ) ? $the_deck->days_left : '0' ); ?></div>
						<div class="id-product-days-to-go"><?php _e( 'Days Left', 'ignitiondeck' ); ?></div>
					<?php } ?>
				<?php } ?>
			</div>
			
			<!-- end product-wrapper -->	
			<?php if ( ! $custom || ( $custom && isset( $attrs['project_end'] ) ) ) { ?>
				<?php if ( $the_deck->item_fund_end !== '' ) { ?>	
				<div class="id-product-proposed-end"><?php echo ( $the_deck->days_left > 0 ? __( 'Crowdfunding ends on', 'ignitiondeck' ) : __( 'Ended On', 'ignitiondeck' ) ); ?>
					<div class="id-widget-date">
						<div class="id-widget-month"><?php echo $the_deck->month; ?></div>
						<div class="id-widget-day"><?php echo $the_deck->day; ?></div>
						<div class="id-widget-year"><?php echo $the_deck->year; ?></div>
					</div>
				</div>
				<?php } ?>
			<?php } ?>
			<div class="separator">&nbsp;</div>
			<?php if ( ! $custom || ( $custom && isset( $attrs['project_button'] ) ) ) { ?>
				<?php /*?><div class="btn-container">
					<?php if ( isset( $the_deck->end_type ) && $the_deck->end_type == 'open' && is_id_licensed() ) { ?>
						<a href="<?php echo ( isset( $_GET['ig_embed_widget'] ) ? getProjectURLfromType( $project_id ) : getPurchaseURLfromType( $project_id, 'purchaseform' ) ); ?>" class="main-btn"><?php echo ( isset( $_GET['ig_embed_widget'] ) ? __( 'Learn More', 'ignitiondeck' ) : __( 'Support Now', 'ignitiondeck' ) ); ?></a>
						<?php
					} elseif ( isset( $the_deck->days_left ) && $the_deck->days_left > 0 && is_id_licensed() ) {
						?>
					<a href="<?php echo ( isset( $_GET['ig_embed_widget'] ) ? getProjectURLfromType( $project_id ) : getPurchaseURLfromType( $project_id, 'purchaseform' ) ); ?>" class="main-btn"><?php echo ( isset( $_GET['ig_embed_widget'] ) ? __( 'Learn More', 'ignitiondeck' ) : __( 'Support Now', 'ignitiondeck' ) ); ?></a>
					<?php } ?>
				</div>
				<?php */ ?>
				<div class="ign-supportnow" data-projectid="<?php echo ( isset( $project_id ) ? $project_id : '' ); ?>">
					<?php
					if ($the_deck->end_type == 'closed' && $the_deck->days_left <= 0) {
						?>
						<a href="" class=""><?php __('Project Closed', 'idc');?></a>
						<?php
					} else {
						if (function_exists('is_id_licensed') && is_id_licensed()) {
							if (empty($permalinks) || $permalinks == '') {
								echo '<a href="'.get_permalink($project_id).'&purchaseform=500&amp;prodid='.( isset( $project_id ) ? $project_id : '' ).'">'.__('Support Now', 'idc').'</a>';
							} else {
								echo '<a href="'.get_permalink($project_id).'?purchaseform=500&amp;prodid='.( isset( $project_id ) ? $project_id : '' ).'">'.__('Support Now', 'idc').'</a>';
							}
						}
					}
					?>
				</div>
			<?php } ?>
			
			<?php if ( ! $custom || ( $custom && isset( $attrs['project_description'] ) ) ) { ?>
				<!-- Project description -->
				<div class="id-product-description"><?php echo $the_deck->project_desc; ?></div>
				<!-- end id product description -->
			<?php } ?>
			<?php
			if ( ! $custom || ( $custom && isset( $attrs['project_levels'] ) ) ) {
				$permalink_structure = get_option('permalink_structure');
				if (empty($permalink_structure)) {
					$url_suffix = '&';
				}
				else {
					$url_suffix = '?';
				}
				global $post;
				$url           = get_permalink($post->ID).$url_suffix.'purchaseform=500&prodid='.$project_id;//getPurchaseURLFromType( $project_id, 'purchaseform' );
				$level_invalid = getLevelLimitReached( $project_id, $the_deck->post_id, 1 );
				?>
			<!--Product Levels-->
				<div class="id-product-levels">
					<?php
					foreach ( $the_deck->level_data as $level ) {
						if ( ! is_id_licensed() ) {
							$level->level_invalid = 1;
						}
						/*if ( isset( $the_deck->end_type ) && $the_deck->end_type == 'closed' ) {
							if ( isset( $the_deck->days_left ) && $the_deck->days_left > 0 ) {
								?>
								<a class="level-binding" <?php echo ( ! isset( $level->level_invalid ) || $level->level_invalid ? '' : 'href="' . apply_filters( 'id_level_' . $level->id . '_link', $url . '&level=' . $level->id, $project_id ) . '"' ); ?>>
								<?php
							} else {
								?>
								<a class="level-binding" <?php echo ( isset( $level->level_invalid ) && $level->level_invalid ? '' : '' ); ?>>
								<?php
							}
						} else {
							?>
							<a class="level-binding" <?php echo ( ! isset( $level->level_invalid ) || $level->level_invalid ? '' : 'href="' . apply_filters( 'id_level_' . $level->id . '_link', $url . '&level=' . $level->id, $project_id ) . '"' ); ?>>
						<?php }*/
						if ($the_deck->end_type == 'closed' && $the_deck->days_left <= '0') { ?>
							<a class="level-binding">
							<?php
							} 
							else {
							?>
								<a class="level-binding" <?php echo (isset($level->level_invalid) && $level->level_invalid ? '' : 'href="'.apply_filters('id_level_'.$level->id.'_link', $url.'&level='.$level->id, $project_id).'"'); ?>>
							<?php 
							}
						?>
						<div class="level-group">
							<div class="id-level-title"><span><?php echo ( isset( $level->meta_title ) ? strip_tags( stripslashes( $level->meta_title ) ) : __( 'Level', 'ignitiondeck' ) . ' ' . ( $level->id ) ); ?>:</span> <?php echo ( isset( $level->meta_price ) && $level->meta_price > 0 ? apply_filters( 'id_price_selection', $level->meta_price, $the_deck->post_id ) : '' ); ?></div>
							<div class="id-level-desc"><?php echo html_entity_decode( stripslashes( $level->meta_desc ) ); ?></div>
						<?php echo ( ! empty( $level->meta_limit ) ? '<div class="id-level-counts"><span>' . __( 'Limit', 'ignitiondeck' ) . ': ' . $level->meta_count . ' ' . __( 'of', 'ignitiondeck' ) . ' ' . $level->meta_limit . ' ' . __( 'Taken', 'ignitiondeck' ) . '</span></div>' : '' ); ?>
						<?php echo do_action( 'id_after_level', $level ); ?>
						</div>
							</a>
					<?php } ?>
				</div>
				<!-- end product levels -->
				<?php echo do_action( 'id_after_levels', $project_id, $the_deck ); ?>
			<?php } ?>
			<?php
			if ( $the_deck->settings->id_widget_logo_on ) {
				?>
			<div class="poweredbyID"><span><a href="<?php echo $the_deck->affiliate_link; ?>" title="<?php _e( 'Crowdfunding by IgnitionDeck', 'ignitiondeck' ); ?>"><?php _e( 'Powered By IgnitionDeck', 'ignitiondeck' ); ?></a></span></div>
			<?php } ?>
		</div>
		<!-- end product-infobox -->
		<?php echo do_action( 'id_widget_after', $project_id, $the_deck ); ?>
	</div>
	<!-- end id-widget -->
	<?php echo ( isset( $float ) ? '</div>' : '</div>' ); ?>
<?php } ?>
