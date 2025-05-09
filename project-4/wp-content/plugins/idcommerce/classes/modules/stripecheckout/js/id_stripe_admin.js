console.log('Stripe Check Status:'+stripecheckout_enable);
jQuery(document).ready(function() {
	if(stripecheckout_enable) {
		jQuery('.cc-gateway-chkbox').prop('disabled',true);
	}
});
