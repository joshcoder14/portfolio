<div class="memberdeck checkout-wrapper">
	<div class="checkout-title-bar">
    	<span class="active checkout-payment"><a href="#"><?php _e('Payment', 'memberdeck'); ?></a></span>
        <span class="checkout-confirmation"><a href="#"><?php _e('Confirmation', 'memberdeck'); ?></a></span>
        <span class="checkout-project-title">
        	<span><?php echo wp_trim_words(isset($level_name) ? apply_filters('idc_level_name', $level_name) : '', $num_words = 10, $more = null); ?></span>
        </span>
        <span class="currency-symbol"><sup><?php echo $global_currency_symbol; ?></sup><span class="product-price"><?php echo $info_price; ?></span>
        	<span class="checkout-tooltip"><i class="fa fa-info-circle"></i></span>
        </span>
    </div>
    <div class="tooltip-text">
        <?php include ('_checkoutTooltip.php'); ?>
    </div>
	<form action="" method="POST" id="payment-form" data-currency-code="<?php echo $pp_currency; ?>" data-product="<?php echo (isset($product_id) ? $product_id : ''); ?>" data-type="<?php echo (isset($type) ? $type : ''); ?>" <?php echo (isset($type) && $type == 'recurring' ? 'data-recurring="'.$recurring.'"' : ''); ?> data-free="<?php echo ($level_price == 0 ? 'free' : 'premium'); ?>" data-txn-type="<?php echo (isset($txn_type) ? $txn_type : 'capture'); ?>" data-renewable="<?php echo (isset($renewable) ? $renewable : 0); ?>" data-trial-period="<?php echo (isset($return->trial_period) ? $return->trial_period : ''); ?>" data-trial-length="<?php echo (isset($return->trial_length) ? $return->trial_length : ''); ?>" data-trial-type="<?php echo (isset($return->trial_type) ? $return->trial_type : ''); ?>" data-limit-term="<?php echo (isset($type) && $type == 'recurring' ? $limit_term : 0); ?>" data-term-limit="<?php echo(isset($limit_term) && $limit_term ? $term_length : ''); ?>" data-scpk="<?php echo (isset($sc_pubkey) ? apply_filters('idc_sc_pubkey', $sc_pubkey) : ''); ?>" data-claimedpp="<?php echo (isset($claimed_paypal) ? apply_filters('idc_claimed_paypal', $claimed_paypal) : ''); ?>" <?php echo ((isset($es) && $es == 1 && !is_idc_free()) || isset($_GET['login_failure']) ? 'style="display: none;"' : ''); ?> data-pay-by-credits="<?php echo ((isset($paybycrd) && $paybycrd == 1) ? '1' : '') ?>" data-guest-checkout="<?php echo ($guest_checkout); ?>">
		<h3 class="checkout-header"><?php /* echo (isset($level_name) ? $level_name : ''); ?> <?php _e('Checkout', 'memberdeck'); */?> 
			<?php _e('Select Payment Method', 'memberdeck'); ?></h3>
		<?php if ($level_price !== '' && $level_price > 0) { ?>
		<div class="payment-type-selector">
			<?php if (isset($epp) && $epp == 1) { ?>
			<div><a id="pay-with-paypal" class="pay_selector" href="#">
            	<i class="fa fa-paypal"></i>
				<span><?php _e('Paypal', 'memberdeck'); ?></span>
			</a></div>
			<?php } ?>
			<?php if (isset($eppadap) && $eppadap == 1 && !is_idc_free()) { ?>
			<div><a id="pay-with-ppadaptive" class="pay_selector" href="#">
            	  <i class="fa fa-paypal"></i>
				<span><?php _e('PayPal', 'memberdeck'); ?></span>
			</a></div>
			<?php } ?>
			<?php if (isset($es) && $es == 1 && !is_idc_free()) { ?>
			<div><a id="pay-with-stripe" class="pay_selector" href="#">
           		 <i class="fa fa-credit-card"></i>
				<span><?php _e('Credit Card', 'memberdeck'); ?></span>
			</a></div>
			<?php } ?>
			<?php if (isset($efd) && $efd == 1 && !is_idc_free()) { ?>
			<div><a id="pay-with-fd" class="pay_selector" href="#">
            	<i class="fa fa-credit-card"></i>
				<span><?php _e('Credit Card', 'memberdeck'); ?></span>
			</a></div>
			<?php } ?>
			<?php if (isset($eauthnet) && $eauthnet == 1 && !is_idc_free() && !$guest_checkout) { ?>
			<div><a id="pay-with-authorize" class="pay_selector" href="#">
            	<i class="fa fa-credit-card"></i>
				<span><?php _e('Credit Card', 'memberdeck'); ?></span>
			</a></div>
			<?php } ?>
			<?php do_action('idc_after_credit_card_selectors', $gateways); ?>
			
			<?php if (isset($mc) && $mc == 1) { ?>
			<div><a id="pay-with-mc" class="pay_selector" href="#">
            	 <i class="fa fa-power-off"></i>
				<span><?php _e('Offline Checkout', 'memberdeck'); ?></span>
			</a></div>
			<?php } ?>
			<?php if (isset($paybycrd) && $paybycrd == 1 && !is_idc_free()) { ?>
			<div><a id="pay-with-credits" class="pay_selector" href="#">
            	 <i class="fa fa-usd"></i>
				<span><?php _e(ucwords(apply_filters('idc_credits_label', 'Credits', true)), 'memberdeck'); ?></span>
			</a></div>
			<?php } ?>
			<?php //if (isset($ecb) && $ecb == 1 && !is_idc_free()) {  Disabling coinbase payments even if it's activated from settings ?>
			<?php if ( false ) { ?>
			<div><a id="pay-with-coinbase" class="pay_selector" href="#">
            	<i class="fa fa-btc"></i>
				<span><?php _e('Bitcoin', 'memberdeck'); ?></span>
			</a></div>
			<?php } ?>
		</div>
		<?php } ?>
        <div class="confirm-screen" style="display:none;">
		<?php if (!is_user_logged_in()) { ?>
			<span class="login-help"><a href="#" class="reveal-login"><?php _e('Already have an account?', 'memberdeck'); ?></a></span>
			<div id="logged-input" class="no">
				<div class="form-row third left">
					<label for="first-name"><?php _e('First Name', 'memberdeck'); ?> <span class="starred">*</span></label>
					<input type="text" size="20" class="first-name required" name="first-name" autocomplete="given-name"/>
				</div>
				<div class="form-row twoforth">
					<label for="last-name"><?php _e('Last Name', 'memberdeck'); ?> <span class="starred">*</span></label>
					<input type="text" size="20" class="last-name required" name="last-name" autocomplete="family-name"/>
				</div>
				<div class="form-row">
					<label for="email"><?php _e('Email Address', 'memberdeck'); ?> <span class="starred">*</span></label>
					<input type="email" pattern="[^ @]*@[^ @]*" size="20" class="email required" name="email" autocomplete="email"/>
				</div>
				<?php if (!$guest_checkout) { ?>
					<div class="form-row">
						<label for="pw"><?php _e('Password', 'memberdeck'); ?> <span class="starred">*</span></label>
						<input type="password" size="20" class="pw required" name="pw" autocomplete="current-password"/>
					</div>
					<div class="form-row">
						<label for="cpw"><?php _e('Re-enter Password', 'memberdeck'); ?> <span class="starred">*</span></label>
						<input type="password" size="20" class="cpw required" name="cpw" autocomplete="current-password"/>
					</div>
				<?php }	else { ?>
					<a href="#" class="reveal-account"><?php _e('Create an account', 'memberdeck'); ?></a>
					<div id="create_account" style="display: none">
						<div class="form-row">
							<label for="pw"><?php _e('Password', 'memberdeck'); ?> <span class="starred">*</span></label>
							<input type="password" size="20" class="pw required" name="pw" autocomplete="new-password"/>
						</div>
						<div class="form-row">
							<label for="cpw"><?php _e('Re-enter Password', 'memberdeck'); ?> <span class="starred">*</span></label>
							<input type="password" size="20" class="cpw required" name="cpw" autocomplete="new-password"/>
						</div>
					</div>
				<?php } ?>
			</div>
		<?php }
		else { 
		?>
		<div id="logged-input" class="">
			<div class="form-row third left" style="<?php if(!empty($fname) ||(isset($es) && $es == 1)){ echo "display: none;"; } ?>">
				<label for="first-name"><?php _e('First Name', 'memberdeck'); ?> <span class="starred">*</span></label>
				<input type="text" size="20" class="first-name required" name="first-name" value="<?php echo (isset($fname) ? $fname : ''); ?>" autocomplete="given-name"/>
			</div>
			<div class="form-row twoforth" style="<?php if(!empty($lname) || (isset($es) && $es == 1)){ echo "display: none;"; } ?>">
				<label for="last-name"><?php _e('Last Name', 'memberdeck'); ?> <span class="starred">*</span></label>
				<input type="text" size="20" class="last-name required" name="last-name" value="<?php echo (isset($lname) ? $lname : ''); ?>" autocomplete="family-name"/>
			</div>
			<div class="form-row" style="display: none;">
				<label for="email"><?php _e('Email Address', 'memberdeck'); ?> <span class="starred">*</span></label>
				<input type="email" pattern="[^ @]*@[^ @]*" size="20" class="email required" name="email" value="<?php echo (isset($email) ? $email : ''); ?>" autocomplete="email"/>
			</div>
		</div>
		<?php } ?>
        </div> <!-- confirm screen -->
        <div id="extra_fields" class="form-row">
			<?php echo do_action('md_purchase_extrafields'); ?>
		</div>
       <div id="stripe-input" data-idset="<?php echo (isset($instant_checkout) && $instant_checkout ? 1 : 0); ?>" data-symbol="<?php echo (isset($stripe_symbol) ? $stripe_symbol : ''); ?>" data-customer-id="<?php echo ((isset($customer_id) && !empty($customer_id)) ? $customer_id : '') ?>" style="display:none;">
        	<div class="row">		
            	<h3 class="checkout-header"><?php _e('Credit Card Info', 'memberdeck'); ?></h3>
            </div>
			<div class="form-row">
				<label><?php _e('Card Number', 'memberdeck'); ?> <span class="starred">*</span> <span class="cards"><img src="<?php echo esc_url( plugins_url( '../images/creditcards-full2.png', __FILE__ ) );?>" alt="<?php _e('Credit Cards Accepted', 'memberdeck'); ?>" /></span></label>
				<input type="text" size="20" class="card-number required" autocomplete="cc-number" <?php echo apply_filters('idc_checkout_card_number_misc', null); ?>/><span class="error-info" style="display:none;"><?php _e('Incorrect Number', 'memberdeck'); ?></span>
			</div>
			<div class="form-row third left">
				<label><?php _e('CVC', 'memberdeck'); ?> <span class="starred">*</span></label>
				<input type="text" size="4" maxlength="4" class="card-cvc required" autocomplete="cc-csc" <?php echo apply_filters('idc_checkout_card_cvc_misc', null); ?> /><span class="error-info" style="display:none;"><?php _e('CVC number required', 'memberdeck'); ?></span>
			</div>
			<div class="form-row third left date">
				<label><?php _e('Expiration (MM/YYYY)', 'memberdeck'); ?> <span class="starred">*</span></label>
				<input type="text" size="2" maxlength="2" class="card-expiry-month required" autocomplete="cc-exp-month" <?php echo apply_filters('idc_checkout_card_expiry_month_misc', null); ?>/><span class="card-exp-slash"> / </span></span><input type="text" size="4" maxlength="4" class="card-expiry-year required"  autocomplete="cc-exp-year" <?php echo apply_filters('idc_checkout_card_expiry_year_misc', null); ?>/>
			</div>
			<?php 
			//if ($es == 1 || apply_filters('idc_display_zip', false) == true) { ?>
	          	<div class="form-row third">
					<label><?php _e('Zip Code', 'memberdeck'); ?> <span class="starred">*</span></label>
					<input type="text" size="20" class="zip-code required" <?php echo apply_filters('idc_checkout_zip_code_misc', null); ?> autocomplete="postal-code"/><span class="error-info" style="display:none;"><?php _e('Invalid Zip code', 'memberdeck'); ?></span>
				</div>
            	<?php //} ?>
		</div>
		
		<?php echo apply_filters('idc_checkout_descriptions', '', $return, $level_price, (isset($user_data) ? $user_data : ''), $gateways, $general, $credit_value); ?>
		
		<div><?php echo apply_filters('md_purchase_footer', ''); ?></div>
		<span class="payment-errors"></span>
		<input type="hidden" name="reg-price" value="<?php echo (isset($return->level_price) ? $return->level_price : ''); ?>"/>
		<input type="hidden" name="pwyw-price" value="<?php echo (isset($pwyw_price) && $pwyw_price > 0 ? $pwyw_price : ''); ?>"/>
		<?php if (isset($upgrade_level) && $upgrade_level) { ?>
		<input type="hidden" name="upgrade-level-price" value="<?php echo (isset($level_price) && $level_price > 0 ? $level_price : ''); ?>"/>
		<?php } ?>
        <div class="checkout-terms-wrapper">
        <?php if (isset($general['show_terms']) && $general['show_terms'] == 1 && (isset($terms_content->post_title) || isset($privacy_content->post_title))) { ?>
		<div class="idc-terms-checkbox" style="display:none;">
			<div class="form-row checklist">
				<input type="checkbox" class="terms-checkbox-input required"/>
				<label><?php _e('I agree to the', 'memberdeck'); ?> 
					<?php if (isset($terms_content->post_title)) { ?>
						<span class="link-terms-conditions"><a href="#"><?php echo $terms_content->post_title; ?></a></span> 
					<?php } ?>
					<?php if (isset($privacy_content->post_title)) { ?>
						<?php echo ((isset($terms_content->post_title)) ? '&amp;' : ''); ?> 
						<span class="link-privacy-policy"><a href="#"><?php echo $privacy_content->post_title; ?></a></span>
					<?php } ?>
				</label>
				<input type="hidden" id="idc-hdn-error-terms-privacy" value="<?php echo (isset($terms_content) ? $terms_content->post_title : ''); ?> &amp; <?php echo (isset($privacy_content) ? $privacy_content->post_title : ''); ?>" />
			</div>
		</div>
		<?php } ?>
        <div class="main-submit-wrapper" style="display:none;">
		<button type="submit" id="id-main-submit" class="submit-button"><?php _e('Submit Payment', 'memberdeck'); ?></button>
        </div>
       </div>
	</form>
	<div class="md-requiredlogin login login-form" style="<?php echo (isset($_GET['login_failure']) && $_GET['login_failure'] ? '' : 'display: none;'); ?>">
		<h3 class="checkout-header"><?php //_e('Login', 'memberdeck'); ?></h3>
		<span class="login-help"><a href="#" class="hide-login"><?php _e('Need to register?', 'memberdeck'); ?></a></span>
		<?php echo (isset($_GET['error_code']) ? '<p>' . ucwords(str_replace('_', ' ', $_GET['error_code'])) . '</p>' : ''); ?>
		<?php
		$args = array('redirect' => $url, 'echo' => false);
		echo wp_login_form($args);
		?>
		<p><a class="lostpassword" href="<?php echo wp_lostpassword_url(); ?>"><?php _e('Lost Password', 'memberdeck'); ?></a></p>
	</div>

	<?php if (isset($general['show_terms']) && $general['show_terms'] == 1) { ?>
	<div class="idc-terms-conditions idc_lightbox mfp-hide">
		<div class="idc_lightbox_wrapper">
			<?php echo (isset($terms_content) ? wpautop($terms_content->post_content) : ''); ?>
		</div>
	</div>
	<div class="idc-privacy-policy idc_lightbox mfp-hide">
		<div class="idc_lightbox_wrapper">
			<?php echo (isset($privacy_content) ? wpautop($privacy_content->post_content) : ''); ?>
		</div>
	</div>
	<?php } ?>
</div>
<?php if (!isset($_GET['login_failure'])) { ?>
<!-- 
    The easiest way to indicate that the form requires JavaScript is to show
    the form with JavaScript (otherwise it will not render). You can add a
    helpful message in a noscript to indicate that users should enable JS.
-->
<script>
if (window.Stripe) jQuery("#payment-form").show();
</script>
<noscript><p><?php _e('JavaScript is required for the purchase form', 'memberdeck'); ?>.</p></noscript>
<?php } ?>
<div id="ppload"></div>
<?php if (isset($ecb) && $ecb == 1 && !is_idc_free()) { ?>
<!--<div id="coinbaseload" data-button-loaded="no" style="display:none;">
	<iframe
		id=""
		src=""
		style="width: 460px; height: 350px; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.25);"
		allowtransparency="true"
		frameborder="0"
	></iframe>
</div>-->
<?php } ?>
<?php if (isset($eppadap) && $eppadap == 1 && !is_idc_free()) {
	// For lightbox
	echo '<script src="https://www.paypalobjects.com/js/external/dg.js"></script>';
	// For mini browser
	echo '<script src="https://www.paypalobjects.com/js/external/apdg.js"></script>';
}
?>