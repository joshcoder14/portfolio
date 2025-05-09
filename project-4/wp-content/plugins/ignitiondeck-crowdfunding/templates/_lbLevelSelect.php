<?php
	// we need to hide/invalidate sold out levels
if ( isset( $level ) ) {
	$level_invalid = getLevelLimitReached( $project_id, $post_id, $level );
	if ( $level_invalid ) {
		$level = 0;
	}
}
	$level_data = apply_filters( 'idcf_dropdown_level', $level_data, $project_id );
?>
<div class="ignitiondeck idc_lightbox mfp-hide">
	<div class="project_image" style="background-image: url(<?php echo $image; ?>);"><div class="aspect_ratio_maker"></div></div>
	<div class="lb_wrapper">
		<div class="form_header">
			<strong><?php _e( 'Step 1:', 'ignitiondeck' ); ?></strong> <?php _e( 'Specify your contribution amount for', 'ignitiondeck' ); ?> <em><?php echo get_the_title( $post_id ); ?></em>
		</div>
		<div class="form">
		<?php
			global $post;
			if(get_post_meta($post->ID,'ign_option_purchase_url',true) && get_post_meta($post->ID,'ign_option_purchase_url',true)=='external_url') {
				$action = get_post_meta($post->ID,'purchase_project_URL',true);
				?>
				<form action="<?php echo ( isset( $action ) ? $action : '' ); ?>" method="POST">
					<div class="form-row inline left twothird">
						<label for="level_select"><?php _e( 'Contribution Level', 'ignitiondeck' ); ?></label>
						<span class="idc-dropdown">
							<select name="level_select" class="idc-dropdown__select level_select">
								<?php
									// would like to move this to the Deck class
									$i = 0;
								foreach ( $level_data as $level ) {
									if ( empty( $level->level_invalid ) || ! $level->level_invalid ) {
										echo '<option value="' . $level->id . '" data-price="' . ( isset( $level->meta_price ) ? apply_filters('idc_price_format', $level->meta_price) : '' ) . '" data-desc="' . htmlspecialchars( $level->meta_short_desc ) . '" data-order="' . ( ! empty( $level->meta_order ) ? $level->meta_order : $i ) . '" ' . apply_filters( 'idcf_dropdown_option_attributes', '', $level ) . '>' . $level->meta_title . '</option>';
									}
									$i++;
								}
								?>
							</select>
						</span>
					</div>
					<div class="form-row inline third total">
						<label for="total"><?php _e( 'Total', 'ignitiondeck' ); ?></label>
						<!-- <div class="id-currency-symbol"><?php echo apply_filters( 'id_lightbox_currency_symbol', $the_deck->cCode, $post_id, $the_deck ); ?></div> -->
						<?php if ( isset( $pwyw ) && $pwyw ) { ?>
							<input type="text" class="total" name="total" id="total" value="<?php // echo total; ?>" placeholder="<?php echo apply_filters( 'id_price_format', ( isset( $level_data[0] ) ? $level_data[0]->meta_price : 0 ), $post_id ); ?>" />
						<?php } else { ?>
							<span name="total" class="total" data-value=""></span>
						<?php } ?>
					</div>
					<div class="form-row text">
						<p>
							<?php // echo description; ?>
						</p>
					</div>
					<div class="form-hidden">
						<input type="hidden" name="post_id" value="<?php echo $post->ID; ?>"/>
						<input type="hidden" name="project_id" value="<?php echo $project_id; ?>"/>
						<input type="hidden" name="project_title" value="<?php echo stripslashes( get_the_title( $the_deck->post_id ) ); ?>"/>
						<input type="hidden" name="project_description" value="<?php echo $the_deck->project_desc; ?>"/>
					</div>
					<div class="form-row submit">
						<input type="submit" name="level_submit" class="btn" value="<?php _e( 'Continue', 'ignitiondeck' ); ?>"/>
					</div>
				</form>
				<?php
			} else {
			?>
			<form action="<?php echo ( isset( $action ) ? $action : '' ); ?>" method="POST" name="idcf_level_select">
				<div class="form-row inline left twothird">
					<label for="level_select"><?php _e( 'Contribution Level', 'ignitiondeck' ); ?></label>
					<span class="idc-dropdown">
						<select name="level_select" class="idc-dropdown__select level_select">
							<?php
								// would like to move this to the Deck class
								$i = 0;
							foreach ( $level_data as $level ) {
								if ( empty( $level->level_invalid ) || ! $level->level_invalid ) {
									echo '<option value="' . $level->id . '" data-price="' . ( isset( $level->meta_price ) ? apply_filters('idc_price_format', $level->meta_price) : '' ) . '" data-desc="' . htmlspecialchars( $level->meta_short_desc ) . '" data-order="' . ( ! empty( $level->meta_order ) ? $level->meta_order : $i ) . '" ' . apply_filters( 'idcf_dropdown_option_attributes', '', $level ) . '>' . $level->meta_title . '</option>';
								}
								$i++;
							}
							?>
						</select>
					</span>
				</div>
				<div class="form-row inline third total">
					<label for="total"><?php _e( 'Total', 'ignitiondeck' ); ?></label>
					<!-- <div class="id-currency-symbol"><?php echo apply_filters( 'id_lightbox_currency_symbol', $the_deck->cCode, $post_id, $the_deck ); ?></div> -->
					<?php if ( isset( $pwyw ) && $pwyw ) { ?>
						<input type="text" class="total" name="total" id="total" value="<?php // echo total; ?>" placeholder="<?php echo apply_filters( 'id_price_format', ( isset( $level_data[0] ) ? $level_data[0]->meta_price : 0 ), $post_id ); ?>" />
					<?php } else { ?>
						<span name="total" class="total" data-value=""></span>
					<?php } ?>
				</div>
				<div class="form-row text">
					<p>
						<?php // echo description; ?>
					</p>
				</div>
				<div class="form-hidden">
					<input type="hidden" name="project_id" value="<?php echo $project_id; ?>"/>
				</div>
				<div class="form-row submit">
					<input type="submit" name="lb_level_submit" class="btn lb_level_submit" value="<?php _e( 'Next Step', 'ignitiondeck' ); ?>"/>
				</div>
			</form>
			<?php } ?>
		</div>
	</div>
</div>
