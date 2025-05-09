<?php
$public_key = (isset($coinpayments_settings['coinpayments_public_key']) ? $coinpayments_settings['coinpayments_public_key'] : '');
$private_key = (isset($coinpayments_settings['coinpayments_private_key']) ? $coinpayments_settings['coinpayments_private_key'] : '');
$coinpayments_merchant_id = (isset($coinpayments_settings['coinpayments_merchant_id']) ? $coinpayments_settings['coinpayments_merchant_id'] : '');
$ipn = (isset($coinpayments_settings['coinpayments_ipn']) ? $coinpayments_settings['coinpayments_ipn'] : '');
$fee = (isset($coinpayments_settings['coinpayments_fee']) ? $coinpayments_settings['coinpayments_fee'] : '');
$enable = (isset($coinpayments_settings['cps_enable']) && $coinpayments_settings['cps_enable']==1 ? 'checked="checked"' : '');
$cps_creator_enable = (isset($coinpayments_settings['cps_creator_enable']) && $coinpayments_settings['cps_creator_enable']==1 ? 'checked="checked"' : '');
?>
<div class="wrap ignitiondeck memberdeck">
	<div class="icon32" id="coinpayment"></div><h2 class="title"><?php _e('CoinPayments Settings', 'memberdeck'); ?></h2>
	<div class="help">
		<a href="mailto:support@ignitionwp.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large button-primary"><?php _e('Support', 'memberdeck'); ?></button></a>
		<a href="https://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large button-primary"><?php _e('Documentation', 'memberdeck'); ?></button></a>
	</div>
	<form action="" method="POST" id="id_coinpayment_admin_settings">
		<div class="id-settings-container">
			<!--MainBox-->
			<div class="postbox-container" style="width:64%; margin-right:1%">
				<div class="metabox-holder">
					<div class="meta-box-sortables" style="min-height:0;">
						<div class="postbox">
							<h3 class="hndle"><span><?php _e('CoinPayments Merchant Settings', 'memberdeck'); ?></span></h3>
							<div class="inside cps-merchant-settings">
								<p><?php printf(__('Generate and edit your key permissions here: %1$sCoinPayments API Keys%2$s', 'memberdeck'), '<a target="_blank" href="https://www.coinpayments.net/acct-api-keys">', '</a>'); ?></p>
								<div class="form-inline">
									<div class="form-check">
										<input type="checkbox" name="cps_enable" id="cps_enable" value="1" <?=$enable?>> 
										<label for="dev_mode">Enable CoinPayments</label>
									</div>
									<?php
									// if licensed
									if ( !get_option( 'is_id_basic', false ) && get_option('is_id_pro') && get_option('is_idc_licensed') ) {
									?>
									<div class="form-check">
										<input type="checkbox" name="cps_creator_enable" id="cps_creator_enable" value="1" <?=$cps_creator_enable?>> 
										<label for="cps_creator_enable">Enable for Creators</label>
										<div class="form-input cps_creator_active" <?=$cps_creator_enable==''?'style="display:none"':''?>>
											<label for="coinpayments_fee"><?php _e('Platform Fee %', 'memberdeck'); ?></label><br/>
											<input type="text" name="coinpayments_fee" id="coinpayments_fee" value="<?php echo $fee; ?>"/>
										</div>
									</div>
									<?php
									}
									?>
								</div>
								<h3><?php _e('API Keys', 'memberdeck'); ?></h3>
								<div class="form-input">
									<label for="coinpayments_public_key"><?php _e('CoinPayments Public Key', 'memberdeck'); ?></label>
									<input type="text" name="coinpayments_public_key" id="coinpayments_public_key" value="<?php echo $public_key; ?>"/>
								</div>
								<div class="form-input">
									<label for="coinpayments_private_key"><?php _e('CoinPayments Private Key', 'memberdeck'); ?></label><br/>
									<input type="password" name="coinpayments_private_key" id="coinpayments_private_key" value="<?php echo $private_key; ?>"/>
								</div>
								<div class="form-input">
									<label for="coinpayments_merchant_id"><?php _e('CoinPayments Merchant ID', 'memberdeck'); ?></label><br/>
									<input type="text" name="coinpayments_merchant_id" id="coinpayments_merchant_id" value="<?php echo $coinpayments_merchant_id; ?>"/>
								</div>
								<div class="form-input">
									<label for="coinpayments_ipn"><?php _e('CoinPayments IPN Secret (If Any)', 'memberdeck'); ?></label><br/>
									<input type="password" name="coinpayments_ipn" id="coinpayments_ipn" value="<?php echo $ipn; ?>"/>
								</div>

								<div class="hooked-form-input">
									<?php do_action('ide_after_cps_settings');?>
								</div>
																
								<div class="form-submit">
									<br>
									<button class="button button-primary" id="id_coinpayments_settings_submit" name="id_coinpayments_settings_submit" value="1"><?php _e('Save & Verify', 'memberdeck'); ?></button>
								</div>
								<div class="api-details">
									<br>
									<?php
									try {
										$information = $cps_api->GetBasicInfo();
									} catch (Exception $e) {
										echo 'Error: ' . $e->getMessage();
										//exit();
									}
									if($information) {
										if($information['error'] == 'ok') {
											?>
											<h3>Merchant Details:</h3>
											<b>Username:</b> <?=$information['result']['username']?><br>
											<b>Email:</b> <?=$information['result']['email']?><br>
											<?php
										} else {
										?>
										<h3>Merchant Details:</h3>
										<b>Error:</b> <?=$information['error']?><br>
										<?php
										}
									}
									?>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="metabox-holder">
					<div class="meta-box-sortables" style="min-height:0;">
						<div class="postbox">
							<h3 class="hndle"><span><?php _e('Coin Acceptance Settings', 'memberdeck'); ?></span></h3>
							<div class="inside" style="min-width: 400px;">
								<h3><?php _e('Accepted Coins List', 'memberdeck'); ?></h3>
								<?php
								// Attempt to unserialize, and ensure the result is always treated as an array.
								$unserialized_idc_cps_coins = maybe_unserialize($coinpayments_settings['cps_accepted_coins']);
								$idc_cps_coins = is_array($unserialized_idc_cps_coins) ? $unserialized_idc_cps_coins : array();
								try {
									$rates = $cps_api->GetRatesWithAccepted();
								} catch (Exception $e) {
									echo 'Error: ' . $e->getMessage();
									//exit();
								}
								?>
								<div class="form-input">
									<div class="coin-list">
									<?php 
									foreach($rates['result'] as $k=>$r):
										$selected = in_array($k.'/'.$r['name'],$idc_cps_coins)?'checked="checked"':'';
										?>
										<span><input type="checkbox" name="cps_accepted_coins[]" class="cps_accepted_coins" value="<?=$k?>/<?=$r['name']?>" <?=$selected?> /> <?='<b>'.$k.'</b> ['.$r['name'].']'?><?=$k=='LTCT'?' (For Testing)':''?></span>
										<?php
									endforeach;
									?>
									</div>
								</div>
								<div class="form-input">
									<button class="button button-primary" id="id_coinpayments_settings_submit_2" name="id_coinpayments_settings_submit_2" value="1"><?php _e('Update', 'memberdeck'); ?></button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!--MainBox-->
			<!--Sidebar-->
			<?php include('sidebar.php');?>
			<!--Sidebar-->
		</div>
	</form>
</div>