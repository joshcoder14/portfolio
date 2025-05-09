console.log('reCAPTCHA version :',id_recaptcha_version);
// For version 2
if(id_recaptcha_version=='v2') {
    function idRecaptchaLoad() {
        if(id_recaptcha_site_id == null) {
            console.log('reCAPTCHA Site ID undefined.');
        } else {
            if (jQuery('.id_recaptcha_placeholder').length > 0) {
                jQuery('input[name="wp-submit"], button.idc_reg_submit').attr('disabled', 'disabled');
                jQuery.each(jQuery('.id_recaptcha_placeholder'), function(i, el) {
                    jQuery(this).attr('id', 'id_recaptcha_placeholder-' + i);
                    var thisForm = jQuery(this).closest('form').attr('id');
                    var size = 'normal';
                    grecaptcha.render('id_recaptcha_placeholder-' + i, {
                        'sitekey' : id_recaptcha_site_id,
                        'size' : size
                    });
                });
            }
        }
    }

    function idRecaptchaCallback() {
        jQuery('input[name="wp-submit"], button.idc_reg_submit').removeAttr('disabled');
    }
}

// For version 3
jQuery(document).ready(function() {

    if (jQuery('.md-requiredlogin').length > 0) {
		jQuery('.md-requiredlogin input[name="wp-submit"]').on('click', function(e) {
			e.preventDefault();
            grecaptcha.ready(function() {
                grecaptcha.execute(id_recaptcha_site_id, {action: 'submit'}).then(function(token) {
                    //console.log('Submit Login', token);
                    jQuery('#loginform #wp-submit').val('Logging In...').css({'opacity':'0.5'});
                    jQuery('#loginform').append('<input type="hidden" name="g-recaptcha-response" value="'+token+'" />');
                    jQuery('#loginform').submit();
                });
            });
            return false;
		});
	}

    if (jQuery("form[name='reg-form']").length > 0) {
        jQuery("form[name='reg-form'] #id-reg-submit").attr('type','button');
        jQuery("form[name='reg-form'] #id-reg-submit").on('click', function(e) {
            e.preventDefault();
            grecaptcha.ready(function() {
                grecaptcha.execute(id_recaptcha_site_id, {action: 'submit'}).then(function(token) {
                    //console.log('Register Login', token);
                    jQuery("form[name='reg-form'] #id-reg-submit").css({'opacity':'0.5'}).html('Registering...');
                    jQuery("#registration-form-extra-fields").append('<input type="hidden" name="g-recaptcha-response" value="'+token+'" />');
                    jQuery("form[name='reg-form']").submit();
                });
            });
            return false;
        });
    }
});

