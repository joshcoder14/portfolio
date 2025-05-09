jQuery(document).bind('idcPaySelect', function(e, selector) {
	var buttonID = jQuery(selector).attr('id');
	if (buttonID == 'pay-with-cps') {
		//jQuery('#id-main-submit').text(idc_localization_strings.pay_with_cps).attr('name', 'submitPaymentCoinPayments').removeAttr('disabled');
		var currency = jQuery('#crypto-currency option:selected').val();
		jQuery('#id-main-submit').text('Pay With '+currency).attr('name', 'submitPaymentCoinPayments').removeAttr('disabled');
		jQuery('.finaldesc').hide();
		jQuery('#finaldescCoinPayments').show();
		if(typeof toggleDonationForm === "function"){
			toggleDonationForm(true);
		}
	}
});

jQuery(document).ready(function() {
	if(jQuery('#cps_creator_enable').length) {
		jQuery('body').on('click','#cps_creator_enable',function() {
			if(jQuery(this).is(':checked')) {
				jQuery('.cps_creator_active').slideDown();
			} else {
				jQuery('.cps_creator_active').slideUp();
			}
		});
	}


	/*
	if(jQuery('#crypto-currency').length) {
		jQuery('body').on('change','#crypto-currency',function() {
			cryptoConversion();
		});
		cryptoConversion();
	}
	*/

	if(jQuery('#crypto-currency').length) {
		jQuery('body').on('change','#crypto-currency',function() {
			cryptoPayButton();
		});
		cryptoPayButton();
	}

	//Check order details
	if(jQuery('.cps-order-details').length) {
		setInterval(checkOrderStatus, 15000);
	}
});

/*
function cryptoConversion() {
	if(typeof memberdeck_ajaxurl === 'undefined') {
		console.log('"memberdeck_ajaxurl" is not defined. [coinpayment.js:19]');
		return;
	}
	jQuery('.memberdeck.checkout-wrapper').addClass('loading');
	var base_currency = jQuery('#finaldescCoinPayments').data('currency');
	//var amount = parseFloat(jQuery('input[name="reg-price"]').val());
	var amount = parseFloat(jQuery('input[name="pwyw-price"]').val());
	var currency = jQuery('#crypto-currency option:selected').val();
	jQuery.ajax({
		url: memberdeck_ajaxurl,
		type: 'POST',
		data: {action: 'cps_currency_conversion', base_currency: base_currency, currency: currency, amount: amount},
		success: function(res) {
			var r = JSON.parse(res);
			if(r.success) {
				jQuery('#crypto-to-pay').html(r.result);
				jQuery('#crypto-amount').val(r.amount);
			} else {
				jQuery('.payment-errors').html(r.message);
			}
			jQuery('.memberdeck.checkout-wrapper').removeClass('loading');
		}
	});
}
*/

function cryptoPayButton() {
	var currency = jQuery('#crypto-currency option:selected').val();
	jQuery('#id-main-submit').html('Pay With '+currency);
}

function checkOrderStatus() {
	if(typeof memberdeck_ajaxurl === 'undefined') {
		console.log('"memberdeck_ajaxurl" is not defined. [coinpayment.js:19]');
		return;
	}
	var order_id = jQuery('.cps-order-details').data('order_id');
	var order_status = jQuery('.cps-order-details').data('order_status');
	var cps_status = jQuery('.cps-order-details').data('cps_status');
	if(cps_status >= 0 && cps_status < 100) {
		jQuery.ajax({
			url: memberdeck_ajaxurl,
			type: 'POST',
			data: {action: 'cps_order_status', order_id: order_id},
			success: function(result) {
				if(result != 'false') {
					jQuery('.cps-order-details').html(result);
				}
			}
		});
	}
}

jQuery(document).bind('idcCheckoutSubmit', function(e, submitName) {
	if (submitName == 'submitPaymentCoinPayments' && jQuery('#payment-form').data('txn-type')!='preauth') {
		if(typeof memberdeck_ajaxurl === 'undefined') {
			console.log('"memberdeck_ajaxurl" is not defined. [coinpayment.js:43]');
			return;
		}
		jQuery(".payment-errors").text('');
		var customer = idcCheckoutCustomer();
		var fields = idcCheckoutExtraFields();
		//var pwywPrice = parseFloat(jQuery('#crypto-amount').val());
		var pwywPrice = parseFloat(jQuery('input[name="pwyw-price"]').val());
		var pwywCurrency = jQuery('#crypto-currency option:selected').val();
		var currency = jQuery("#payment-form").data('currency-code');
		var txnType = jQuery("#payment-form").data('txn-type');
		var renewable = jQuery('#payment-form').data('renewable');
		var customer_id = jQuery('#stripe-input').data('customer-id');
		var post_id = jQuery('#finaldescStripeCheckout').data('post-id');
		var curURL = window.location.href;
		var queryString = '';
		jQuery.each(fields.posts, function() {
			queryString = queryString + '&' + this.name + '=' + this.value;
		});

		var data = {
			action: 'id_coinpayments_submit',
			customer: customer,
			Fields: fields.posts,
			txnType: txnType,
			Renewable: renewable,
			post_id: post_id,
			customer_id: customer_id,
			pwyw_price: pwywPrice,
			cryptoCurrency: pwywCurrency,
			currency:currency,
			current_url: curURL,
			anonymous_checkout: jQuery('#anonymous_checkout').val(), 
            idc_checkout_comments: jQuery('#idc_checkout_comments').val(),
            checkout_donation: jQuery('#checkout_donation').val(),
            cover_fees: jQuery('#cover_fees_on_checkout:checked').val(),
		};

		jQuery.ajax({
			url: memberdeck_ajaxurl,
			type: 'POST',
			data: data,
			success: function(res) {
				if (typeof res == 'string') {
					var json = JSON.parse(res);
                    if (json.response == 'success') {
						var win = window.open(json.result.checkout_url, '_blank');
						if (win) {
							//Redirect to Order confirmation
							window.location.href = json.redirectTo;

							//Browser has allowed it to be opened
							win.focus();
						} else {
							//Browser has blocked it
							jQuery('#id-main-submit').removeAttr('disabled').text('').removeClass('processing');
							jQuery('#id-main-submit').text(idc_localization_strings.pay_with_cps);
							jQuery('.payment-errors').text('Please allow popups for this website.');
						}		
		    		} else {
						jQuery('#id-main-submit').removeAttr('disabled').text('').removeClass('processing');
    					jQuery('#id-main-submit').text(idc_localization_strings.pay_with_cps);
						jQuery('.payment-errors').text(json.message);
					}
				}
			},
			error: function(error) {
				jQuery('#id-main-submit').removeAttr('disabled').text('').removeClass('processing');		
    			jQuery('#id-main-submit').text(idc_localization_strings.pay_with_cps);
				jQuery('.payment-errors').text(error);
			}
		});
	}
});