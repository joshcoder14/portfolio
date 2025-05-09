<h2 class="title"><?php _e('IgnitionDeck Commerce Troubleshooting', 'memberdeck'); ?></h2>
<p><?php _e('Use the button below to synchronize and fix any discrepancies in IDC Orders and IDCF Orders (values in IDC Orders will take priority).', 'memberdeck'); ?></p>
<p><?php _e('Uncaptured PRE-ORDERS, IDCF Orders manually added using the ADD NEW function, and any orders within CLOSED projects will not be synced.', 'memberdeck'); ?></p>
<p><?php _e('<strong>Warning:</strong> The sync process can not be reversed once initiated.', 'memberdeck'); ?></p>
<?php
// Retrieve valid orders for update and list their IDs.
$valid_orders = idc_get_valid_orders_for_troubleshoot_fix();
$is_fix_sync_idcf_orders_disabled = false;
if ( ! empty( $valid_orders ) ) {
	$order_ids = wp_list_pluck($valid_orders, 'id');
	echo '<p>' . esc_html__('Orders to be updated: ', 'memberdeck') . esc_html(implode(', ', $order_ids)) . '</p>';
} else {
	echo '<p>' . esc_html__('Orders to be updated: ALL ORDERS CORRECT&mdash;SYNC NOT NEEDED', 'memberdeck') . '</p>';
	$is_fix_sync_idcf_orders_disabled = true;
}
?>
<form method="post" action="">
	<?php wp_nonce_field('fix_sync_idcf_orders_action', 'fix_sync_idcf_orders_nonce'); ?>
	<input type="submit" name="fix_sync_idcf_orders" class="button button-primary"
	       value="<?php _e('Fix & Sync IDCF Orders', 'memberdeck'); ?>"
		<?php if ($is_fix_sync_idcf_orders_disabled) echo 'disabled'; ?>/>
	<?php
	// Check if the process has completed and display a green checkmark if it has.
	if (isset($_GET['fixed_sync_complete']) && '1' === $_GET['fixed_sync_complete']) {
		echo '<span class="dashicons dashicons-yes" style="color: green;"></span>';
	}
	?>
</form>
