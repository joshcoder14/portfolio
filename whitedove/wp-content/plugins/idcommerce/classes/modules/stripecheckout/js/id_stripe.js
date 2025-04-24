var stripe;

// Set up Stripe.js and Elements to use in checkout form
var setupElements = function(data) {
	console.log('SetupData:',data);
	var elements = stripe.elements();
	var style = {
		base: {
			color: "#32325d",
			fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
			fontSmoothing: "antialiased",
			fontSize: "16px",
			"::placeholder": {
				color: "#aab7c4"
			}
		},
		invalid: {
			color: "#fa755a",
			iconColor: "#fa755a"
		}
	};
	var card = elements.create("card", { hidePostalCode: true, style: style });
	card.mount("#stripe-input");
	jQuery('.memberdeck form #stripe-input').show();

	return {
		card,
		clientSecret: data.client_secret
	};
};

jQuery(document).ready(function() {
	jQuery('.checkout-title-bar .currency-symbol sup').html(jQuery('#finaldescStripeCheckout').data('currency-symbol'));

	//stripe = Stripe(stripecheckout_pk);
	if(jQuery('#payment-form').data('txn-type')=='preauth') {
		var customer = idcCheckoutCustomer();
		var fields = idcCheckoutExtraFields();
		var pwywPrice = parseFloat(jQuery('input[name="pwyw-price"]').val());
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
            action: 'id_stripe_checkout_submit', 
            customer: customer, 
            Fields: fields.posts, 
            txnType: txnType, 
            Renewable: renewable, 
            post_id: post_id, 
            customer_id: customer_id, 
            pwyw_price: pwywPrice, 
            current_url: curURL, 
            anonymous_checkout: jQuery('#anonymous_checkout:checked').val(), 
            idc_checkout_comments: jQuery('#idc_checkout_comments').val(),
            checkout_donation: jQuery('#checkout_donation').val(),
            cover_fees: jQuery('#cover_fees_on_checkout:checked').val(),
        };

		jQuery.ajax({
			url: memberdeck_ajaxurl,
			type: 'POST',
			data: data,
			success: function(res) {
				var json = JSON.parse(res);
				if(json.message.connected_account !== undefined) {
					stripe = Stripe(stripecheckout_pk, {stripeAccount: json.message.connected_account});
					console.log('Stripe Checkout Connect Loaded - IDStripecheckout');
                } else {
					stripe = Stripe(stripecheckout_pk);
					console.log('Stripe Checkout Loaded - IDStripecheckout');
                }
                if (json.response=='failure') {
                    jQuery('#id-main-submit').removeAttr('disabled').text('').removeClass('processing');    			
                    jQuery('#id-main-submit').text(idc_localization_strings.pay_with_stripe);
                    jQuery('.payment-errors').text(json.message);
                    setTimeout(function() {
                        jQuery('.payment-errors').text('');
                    }, 10000);
                    return;
                }
				var setup = setupElements(json.message);
				document.querySelector("#id-main-submit").addEventListener("click", function(evt) {
					evt.preventDefault();
					// Initiate payment
					var data = {
						card: setup.card
					};
					// Initiate the payment.
					// If authentication is required, confirmCardPayment will display a modal
					stripe
						.confirmCardPayment(json.message.client_secret, { payment_method: data })
						.then(function(result) {
						if (result.error) {
							jQuery('#id-main-submit').removeAttr('disabled').text('').removeClass('processing');    			
							jQuery('#id-main-submit').text(idc_localization_strings.pay_with_stripe);
							jQuery('.payment-errors').text(result.error.message);
							setTimeout(function() {
								jQuery('.payment-errors').text('');
							}, 10000);
						} else {
							if(json.message.connected_account !== undefined) {
								orderComplete(json.message.client_secret, json.message.connected_account);
							} else {
								orderComplete(json.message.client_secret);
							}
						}
					});
				});
			},
			error: function(error) {
				jQuery('#id-main-submit').removeAttr('disabled').text('').removeClass('processing');    			
    			jQuery('#id-main-submit').text(idc_localization_strings.pay_with_stripe);
				jQuery('.payment-errors').text(error);
			}
		});
	}
});

jQuery(document).bind('idcCheckoutLoaded', function(e) {
	var type = jQuery("#payment-form").data('type');
	var txnType = jQuery("#payment-form").data('txn-type');
	if (type == 'recurring' || txnType == 'preauth') {
		//jQuery('#payment-form #pay-with-stripe-checkout').remove();
		no_methods();
	}
});

jQuery(document).bind('idcPaySelect', function(e, selector) {
	var buttonID = jQuery(selector).attr('id');
	if (buttonID == 'pay-with-stripe-checkout') {
		jQuery('#id-main-submit').text(idc_localization_strings.pay_with_stripe).attr('name', 'submitPaymentStripeCheckout').removeAttr('disabled');
		jQuery('.finaldesc').hide();
		jQuery('#finaldescStripeCheckout').show();
	}
});

jQuery(document).bind('idcCheckoutSubmit', function(e, submitName) {
	if (submitName == 'submitPaymentStripeCheckout' && jQuery('#payment-form').data('txn-type')!='preauth') {
		jQuery(".payment-errors").text('');
		var customer = idcCheckoutCustomer();
		var fields = idcCheckoutExtraFields();
		var pwywPrice = parseFloat(jQuery('input[name="pwyw-price"]').val());
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
            action: 'id_stripe_checkout_submit', 
            customer: customer, 
            Fields: fields.posts, 
            txnType: txnType, 
            Renewable: renewable, 
            post_id: post_id, 
            customer_id: customer_id, 
            pwyw_price: pwywPrice, 
            current_url: curURL, 
            anonymous_checkout: jQuery('#anonymous_checkout:checked').val(), 
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
                    if(json.message.connected_account !== undefined) {
                        stripe = Stripe(stripecheckout_pk, {stripeAccount: json.message.connected_account});
                        console.log('Stripe Checkout Connect Loaded - IDStripecheckout');
                    } else {
                        stripe = Stripe(stripecheckout_pk);
                        console.log('Stripe Checkout Loaded - IDStripecheckout');
                    }
					if (json.response == 'success') {
						//return;
						stripe
						.redirectToCheckout({
							sessionId: json.message.id,
						});
		    		}
					else {
						jQuery('#id-main-submit').removeAttr('disabled').text('').removeClass('processing');    			
    					jQuery('#id-main-submit').text(idc_localization_strings.pay_with_stripe);
						jQuery('.payment-errors').text(json.message);
					}
				}
			},
			error: function(error) {
				jQuery('#id-main-submit').removeAttr('disabled').text('').removeClass('processing');    			
    			jQuery('#id-main-submit').text(idc_localization_strings.pay_with_stripe);
				jQuery('.payment-errors').text(error);
			}
		});
	}
});


/* ------- Post-payment helpers ------- */
/* Shows a success / error message when the payment is complete */
var orderComplete = function(clientSecret, connect_id='') {
	stripe.retrievePaymentIntent(clientSecret).then(function(result) {
		var paymentIntent = result.paymentIntent;
	  	if(paymentIntent.status=='requires_capture') {
			jQuery.ajax({
				url: memberdeck_ajaxurl,
				type: 'POST',
				data: {action: 'id_stripe_checkout_paymentIntent', intent_id:paymentIntent.id, connect_id:connect_id},
				success: function(res) {
					if (typeof res == 'string') {
						var json = JSON.parse(res);
						if (json.response == 'success') {
							window.location.href = json.message;
						} else {
							jQuery('#id-main-submit').removeAttr('disabled').text('').removeClass('processing');    			
							jQuery('#id-main-submit').text(idc_localization_strings.pay_with_stripe);
							jQuery('.payment-errors').text(json.message);
						}
					}
				},
				error: function(error) {
					jQuery('#id-main-submit').removeAttr('disabled').text('').removeClass('processing');    			
					jQuery('#id-main-submit').text(idc_localization_strings.pay_with_stripe);
					jQuery('.payment-errors').text(error);
				}
			});
		}
	});
};
