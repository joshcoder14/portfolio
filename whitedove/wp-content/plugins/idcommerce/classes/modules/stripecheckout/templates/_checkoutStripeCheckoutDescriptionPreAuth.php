<?php 
global $post;
global $global_currency;
$global_currency_symbol = idc_currency_to_symbol($global_currency);
?>
<div id="finaldescStripeCheckout" class="finaldesc" data-currency="<?=$global_currency?>" data-currency-symbol="<?=$global_currency_symbol?>" data-post-id="<?=$post->ID?>" style="display:none;">
	<?php
	_e('Your card will be billed', 'memberdeck');
	echo ' '.(isset($level_price) ? '<span class="product-price">'.apply_filters('idc_price_format', $level_price).'</span>' : '');
	if (empty($combined_purchase_gateways['cc']) || !$combined_purchase_gateways['cc']) {
		echo ' <span class="currency-symbol">'.$global_currency_symbol.'</span> ';
	}
	echo (isset($type) && $type == 'recurring' && isset($limit_term) && $limit_term == '1' ? __('in ', 'memberdeck').$term_length.' ' : '');
	echo (isset($type) && $type == 'recurring' ? $recurring : '');
	echo (isset($type) && $type == 'recurring' && isset($limit_term) && $limit_term == '1' ? __('installments', 'memberdeck') : '');
	if (isset($combined_purchase_gateways['cc']) && $combined_purchase_gateways['cc']) { 
		echo '<span class="combined-product-desc"> '.__('plus', 'memberdeck').' <span class="product-price">'.apply_filters('idc_price_format', $combined_level->level_price).'</span> <span class="currency-symbol">'.$global_currency_symbol.' '.$combined_level->recurring_type.'</span>';
	}
	echo ' '.__('and will appear on your statement as', 'memberdeck').': <em>'.(isset($coname) ? $coname : '').'</em>.';
	?>
</div>