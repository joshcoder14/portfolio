<?php echo ( isset( $float ) ? '<div class="id-content-wrap id-complete-projcont" data-projectid="' . ( isset( $project_id ) ? $project_id : '' ) . '">' : '<div class="id-content-wrap" data-projectid="' . ( isset( $project_id ) ? $project_id : '' ) . '">' ); ?>
<div class="product-post-output" style="clear: both;">
	<?php
	echo do_action( 'id_content_before', $project_id );
	$video = get_post_meta( $post_id, 'ign_product_video', true );
	?>
	<div class="product-video-container <?php echo ( ! empty( $video ) ? 'hasvideo' : '' ); ?>">
		<div class="aspect-ratio-maker"></div>
		<?php
		$id_project_thumbnail = ID_Project::get_project_thumbnail( $post_id, 'large' );
		if ( empty( $id_project_thumbnail ) ) {
			//$id_project_thumbnail = idcf_project_placeholder_image();
		}
		?>
		<div class="id_thevideo" style="background-image: url(<?php echo $id_project_thumbnail; ?>)"> <?php echo idf_handle_video( $video ); ?></div>
	</div>
	<?php require ID_PATH . 'templates/_socialButtons.php'; ?>
		<div style="clear:both;"></div>
		<?php do_action( 'id_before_content_description', $project_id, $post_id ); ?>
		<div class="long-description"><?php echo $project_long_desc; ?></div>
		<?php do_action( 'id_after_content_description', $project_id, $post_id ); ?>
		<div class="clear"></div>
	<?php
	$product_faq = apply_filters( 'idcf_faqs', get_post_meta( $post_id, 'ign_faqs', true ) );
	if ( $product_faq ) {
		?>
		<h3 class="product-dashed-heading"><?php _e( 'FAQ', 'ignitiondeck' ); ?></h3>
		<div id="prodfaq">
			<?php echo html_entity_decode( stripslashes( $product_faq ) ); ?>
			<div><?php do_action( 'id_faqs', '', array( 'product' => $project_id ) ); ?></div>
		</div>
		<?php
	} elseif ( has_action( 'id_faqs' ) ) {
		?>
		<h3 class="product-dashed-heading"><?php _e( 'FAQ', 'ignitiondeck' ); ?></h3>
		<div id="prodfaq">
		<?php
		do_action( 'id_faqs', '', array( 'product' => $project_id ) );
		?>
		</div>
		<?php
	}

	$product_updates = apply_filters( 'idcf_updates', get_post_meta( $post_id, 'ign_updates', true ), $project_id, $post_id );
	if ( $product_updates ) {
		?>
		<h3 class="product-dashed-heading1"><?php _e( 'Updates', 'ignitiondeck' ); ?></h3>
		<div id="produpdates">
			<?php echo html_entity_decode( stripslashes( $product_updates ) ); ?>
			<div><?php do_action( 'id_updates', '', array( 'product' => $project_id ) ); ?></div>
		</div>
		<?php
	} elseif ( has_action( 'id_updates' ) ) {
		?>
		<h3 class="product-dashed-heading1"><?php _e( 'Updates', 'ignitiondeck' ); ?></h3>
		<div id="produpdates">
		<?php
		do_action( 'id_updates', '', array( 'product' => $project_id ) );
		?>
		</div>
		<?php
	}
	echo do_action( 'id_content_after', $project_id );
	?>
</div>
</div>
