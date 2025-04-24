<?php 
global $post;
global $global_currency;
$global_currency_symbol = idc_currency_to_symbol($global_currency);
?>
<div id="finaldescCoinPayments" class="finaldesc" data-currency="<?=$global_currency?>" data-currency-symbol="<?=$global_currency_symbol?>" data-post-id="<?=$post->ID?>" style="display:none;">
	<div class="form-row">
		<label for="crypto-currency">Select Crypto Currency</label>
		<select name="crypto-currency" id="crypto-currency">
			<?php
			if(!empty($idc_cps_coins) && count($idc_cps_coins)>0) {
				foreach($idc_cps_coins as $k=>$c) {
					$coin = explode('/',$c);
					$selected = $k==0?'selected="selected"':'';
					?>
					<option value="<?=$coin[0]?>" <?=$selected?>><b><?=$coin[0]?></b> [<?=$coin[1]?>]</option>
					<?php
				}
			} else {
				?>
				<option value="">Currencies not available</option>
				<?php
			}
			?>
		</select>
	</div>
	<?php
	echo apply_filters('id_coin_payment_text', 
		sprintf(
			__('You will be redirected to CoinPayments to enter payment in the amount of <span id="crypto-to-pay"><span class="product-price">%s</span> %s</span>. To verify the status of payment, it might take few minutes after successful payment.', 'memberdeck'), 
			$level_price, 
			$global_currency
		)
	);
	?>
	<input type="hidden" id="crypto-amount" value="0" />
</div>