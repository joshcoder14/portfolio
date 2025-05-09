<?php
$coinpayments_settings = get_option('id_coinpayments_settings');
$fee = (isset($coinpayments_settings['coinpayments_fee']) ? $coinpayments_settings['coinpayments_fee'] : 0);
?>
<li class="md-box half">
	<div class="md-profile cps-settings">
		<h3><?php _e('CoinPayments', 'memberdeck') ?> <span><strong>Platform Fee:</strong><?=$fee?>%</span></h3>
		<?php echo $cps_message!=''?'<p class="cps-alert"><span>&#10003;</span> '.$cps_message.'</p>':''; ?>
		<?php do_action('ide_above_cps_signup'); ?>
		<p><?php _e('In order to get your <b>Merchant ID</b>, login to your <a href="https://www.coinpayments.net" target="_blank">CoinPayments</a> account and follow <b>Account</b> &#10097;&#10097; <b>Account Settings</b> from main menu.', 'memberdeck') ?></p>
		<div class="form-row <?=$class?>">
			<label>CoinPayment Merchant ID</label>
			<input type="text" class="cps_merchant_id" name="cps_merchant_id" value="<?=$merchant_id?>" placeholder="Enter your merchant ID here" />
		</div>
		<?php do_action('ide_below_cps_signup'); ?>
	</div>
</li>