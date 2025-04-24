<?php 
global $post;
global $global_currency;
$global_currency_symbol = idc_currency_to_symbol($global_currency);
?>
<div id="finaldescStripeCheckout" class="finaldesc" data-currency="<?=$global_currency?>" data-currency-symbol="<?=$global_currency_symbol?>" data-post-id="<?=$post->ID?>" style="display:none;">
	<?php
	echo apply_filters('id_stripe_checkout_text', sprintf(__('You will be redirected to Stripe to enter payment in the amount of <span class="product-price">%s</span> %s. Payment is not complete until you have been redirected back to this website.', 'memberdeck'), (isset($level_price) ? apply_filters('idc_price_format', $level_price) : ''), '<span class="currency-symbol">'.$global_currency_symbol.'</span>'));
	?>
</div>