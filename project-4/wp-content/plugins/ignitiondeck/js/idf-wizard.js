jQuery(document).ready(function() {
    jQuery('body').on('click','.wizard-tab li a',function(e) {
        e.preventDefault();
        //window.location.hash = jQuery(this).attr('href');
        idWizardScreen(jQuery(this).attr('href'));
    });
    checkHash();

    jQuery('body').on('keyup','.register-email',function(e) {
        jQuery(this).removeClass('registered');
        jQuery('#wiz-register .wiz-button').prop('disabled',false);
        jQuery('#wiz-register .wiz-button').html('Register');
    });

    jQuery('body').on('click','.payment-platform input',function(e) {
        let val = jQuery(this).val();
        if(val=='wc') {
            if(jQuery(this).attr('notice')=='true'){
                if(confirm('IgnitionDeck Enterprise require the IgnitionDeck Commerce platform. Using WooCommerce as your commerce platform will make Enterprise functionality such as creator accounts, front end project creation, etc. unavailable.')) {
                    wizard_action('save_payment');
                } else {
                    jQuery('.payment-platform input[value="idc"]').prop('checked',true);
                }
            } else {
                wizard_action('save_payment');
            }
        } else {
            wizard_action('save_payment');
        }
    });

    wizCheckConfig();

    jQuery('.wiz-notice-close').on('click', function(e) {
        wizClosePopup();
    });
    jQuery('.wiz-notice').on('click', function(e) {
        if (e.target !== this)
          return;
        wizClosePopup();
    });

    // Function to close the modal when clicking outside of it
    jQuery(document).on('click', function(event) {
        // Check if the clicked element is not a descendant of .wiz-notice-box
        if (!jQuery(event.target).closest('.wiz-notice-box').length && !jQuery(event.target).closest('.ign-tools_delete_sampleproject').length) {
            // Hide the modal with the ID wiz-notice
            wizClosePopup();
        }
    });

});
function idWizardScreen(id='#wiz-register') {
    history.pushState({}, "", id);
    jQuery('html,body').animate({
        scrollTop: jQuery(id).position().top -= 100
    });
    jQuery('.wizard-tab li').removeClass('active');
    jQuery('.wizard-tab li a[href="'+id+'"]').parent().addClass('active');

    jQuery('.wizard-tabs .wizard-tab-content').removeClass('active');
    jQuery(id).addClass('active');
    if(id=='#wiz-install') {
        checkInstallConditions();
    }
}

function checkHash() {
    if(window.location.hash != '') {
        idWizardScreen(window.location.hash);
    }
}

function checkInstallConditions() {
    jQuery('#wiz-install ul li').each(function() {
        if(jQuery(this).data('status')!='active') {
            jQuery('#wiz-install .wiz-button.install').prop('disabled',false);
            jQuery('#wiz-install .wiz-button.continue').prop('disabled',true);
        }
    })
}

function wizard_action(action,ele=null) {
    switch(action) {
        case 'register_email':
            jQuery('#wiz-register .wiz-button').prop('disabled',true);
            jQuery('#wiz-register .wiz-button').html('Registering<em></em>');
            var data = {
                action: 'idf_wizard_register',
                email: jQuery('.register-email').val(),
                security: jQuery('input[name="idf_activate_plugins_nonce"]').val() // Include the nonce
            };
            jQuery.post( idf_ajaxurl, data, function(response) {
                var r = jQuery.parseJSON(response);
                console.log('Registration Response:',response);
                if(r.error==true) {
                    alert(r.message);
                } else if(r.status=='subscribed') {
                    alert('Registered successfully.');
                    idWizardScreen('#wiz-install');
                } else {
                    alert(jQuery('.register-email').val()+' is already a list member.');
                    idWizardScreen('#wiz-install');
                }
                jQuery('.register-email').addClass('registered');
                jQuery('#wiz-register .wiz-button').html('Registered');
            });
            break;
        case 'plugin_install':
            var plugins = new Array();
            jQuery('#wiz-install .wiz-button.install').prop('disabled',true);
            var i=0;
            jQuery('#wiz-install ul li').each(function() {
                if(jQuery(this).data('status')!='active') {
                    plugins.push(i);
                }
                i++;
            });
            i=0;
            plugin_install(i, plugins);
            break;
        case 'verify_license':
            jQuery(ele).html('Validating<em></em>');
            var data = {
                action: 'idf_wizard_verify_license',
                license: jQuery(ele).parent().find('.wiz-control-inline').val(),
                security: jQuery('input[name="idf_activate_plugins_nonce"]').val()
            };

            // First AJAX request
            jQuery.post(idf_ajaxurl, data, function(response) {
                var content = jQuery(response).filter("#license-details");
                var filtered = content.contents();
                jQuery('#license-details').html(filtered);

                var content = jQuery(response).filter("#configure-details");
                var filtered = content.contents();
                // jQuery('#configure-details').html(filtered);
                // jQuery(ele).html('Activated');
                jQuery('#wiz-upgrade .button-group .wiz-button').removeClass('hidden');

                // Second AJAX request after the first one
                jQuery.ajax({
                    url: idf_ajaxurl,
                    type: 'post',
                    data: {
                        action: 'idf_wizard_validate_themes_access',
                    },
                    success: function (response) {
                        if (response.success) {
                            // Update HTML on success
                            jQuery('#wiz-themes').html(response.data);
                        } else {
                            // Show an error message if there's an issue with the request or action
                            jQuery('#wiz-themes').html('<p>No themes available.</p>');
                        }
                    },
                    error: function () {
                        // Show an error message if there's an issue with the AJAX request
                        jQuery('#wiz-themes').html('<p>Error loading themes.</p>');
                    },
                });
            });

            break;
        case 'save_payment':
            jQuery(ele).html('Saving<em></em>');
            var data = {
                action: 'idf_wizard_save_payment',
                payment: jQuery('.payment-platform input:checked').val(),
                security: jQuery('input[name="idf_activate_plugins_nonce"]').val()
            };
            jQuery.post( idf_ajaxurl, data, function(response) {
                var content = jQuery(response).filter("#license-details");
                var filtered = content.contents();
                jQuery('#license-details').html(filtered);
                
                var content = jQuery(response).filter("#configure-details");
                var filtered = content.contents();
                jQuery('#configure-details').html(filtered);
                // Check if the payment platform is WooCommerce
                if (data.payment === 'wc') {
                    // Check if an element with class "ign-dashboard-receipt-settings" exists
                    if (jQuery('.ign-dashboard-receipt-settings').length > 0) {
                        // Hide the receipt settings
                        jQuery('.ign-dashboard-receipt-settings').hide();
                    } else {
                        // Show the receipt settings
                        jQuery('.ign-dashboard-receipt-settings').show();
                    }
                }
            });
            break;
        case 'theme_install':
            if(jQuery(ele).hasClass('locked')) {
                window.open(jQuery(ele).data('url'), '_blank').focus();
            } else {
                jQuery(ele).html('Installing<em></em>');
                var data = {
                    action: 'idf_wizard_install_themes',
                    url: jQuery(ele).data('url'),
                    slug: jQuery(ele).data('slug'),
                    security: jQuery('input[name="idf_activate_plugins_nonce"]').val()
                };
                jQuery.post( idf_ajaxurl, data, function(response) {
                    jQuery(ele).html(response);
                    alert('Theme is installed. Visit Appearance > Themes to activate.');
                });
            }
            break;
        default:
            break;
    }
}

function wizard_call(data) {
    jQuery.post( idf_ajaxurl, data, function(response) {
        if( data.action=='idf_wizard_install_plugins' ) {
            jQuery('.wizard-tabs .active ul').html( response );
        }
        jQuery('.wizard-tab-content').removeClass('wiz-loader');
    });
}

function plugin_install(i, plugins) {
    if( i >= jQuery('#wiz-install ul li').length ) {
        jQuery('#wiz-loader').fadeIn();
        let pageUrl = window.location.href;
        jQuery.get( pageUrl, {}, function(response) {
            var content = jQuery(response).filter("#wpwrap");
            var filtered = content.contents();
            jQuery('#wpwrap').html(filtered);

            jQuery('#wiz-install .wiz-button.install').html('Installed and Activated');
            jQuery('#wiz-install .wiz-button.continue').prop('disabled',false);
            checkHash();
            jQuery('#wiz-loader').fadeOut();
        });
    } else {
        var ele = jQuery('#wiz-install ul li:eq('+i+')');
        var idfActivatePluginsNonce = jQuery('input[name="idf_activate_plugins_nonce"]').val();
        if(ele.data('status') == 'not') {
            ele.find('span').html('Installing<em></em>');
            var data = {
                action: 'idf_wizard_install_plugins',
                name: ele.data('name'),
                slug: ele.data('slug'),
                url: ele.data('url'),
                security: jQuery('input[name="idf_activate_plugins_nonce"]').val()
            };
            jQuery.post( idf_ajaxurl, data, function(response) {
                ele.find('span').html('Activating<em></em>');
                var data = {
                    action: 'idf_wizard_activate_plugins',
                    idf_security: idfActivatePluginsNonce,
                    name: ele.data('name'),
                    slug: ele.data('slug'),
                    url: ele.data('url'),
                };
                jQuery.post( idf_ajaxurl, data, function(response) {
                    ele.find('span').html('Installed and Activated');
                    i++;
                    plugin_install(i, plugins);
                });
            });
        } else if(ele.data('status') == 'installed') {
            ele.find('span').html('Activating<em></em>');
            var data = {
                action: 'idf_wizard_activate_plugins',
                idf_security: idfActivatePluginsNonce,
                name: ele.data('name'),
                slug: ele.data('slug'),
                url: ele.data('url'),
            };
            jQuery.post( idf_ajaxurl, data, function(response) {
                ele.find('span').html('Installed and Activated');
                i++;
                plugin_install(i, plugins);
            });
        } else {
            i++;
            plugin_install(i, plugins);
        }
    }
}
let x = 0;
let elipsis = '';
setInterval(function () {
    x++;
    if(x>3) x = 1;
    elipsis = '';
    for(y=0;y<x;y++) {
        elipsis += '.';
    }
    jQuery('.wizard-tabs em, #wiz-loader em').each(function() {
        jQuery(this).html(elipsis);
    });
}, 500);

function wizCheckConfig() {
    var data = {
        action: 'idf_wizard_check_config',
    };
    jQuery.post( idf_ajaxurl, data, function(response) {
        var r = JSON.parse(response);
        var i=0;
        Object.keys(r).forEach(function(key) {
            if(r[key]) {
                jQuery('.ign-tools li:eq('+i+') .check').addClass('checked');
            }
            i++;
        });
    });
}

function wizCreateMyDashboard(ele) {
    jQuery(ele).addClass('loading');
    var data = {
        action: 'idf_wizard_create_dashboard',
    };
    jQuery.post( idf_ajaxurl, data, function(response) {
        var r = jQuery.parseJSON(response);
        jQuery(ele).removeClass('loading');
        jQuery(ele).parent().find('.check').addClass('checked');
        showWizPopup(r.result.heading, r.result.content);
    });
}

function wizCreateCheckoutPage(ele) {
    jQuery(ele).addClass('loading');
    var data = {
        action: 'idf_wizard_create_checkout',
    };
    jQuery.post( idf_ajaxurl, data, function(response) {
        var r = jQuery.parseJSON(response);
        jQuery(ele).removeClass('loading');
        jQuery(ele).parent().find('.check').addClass('checked');
        showWizPopup(r.result.heading, r.result.content);
    });
}

function wizSetTimezone(ele) {
    jQuery(ele).addClass('loading');
    var data = {
        action: 'idf_wizard_get_timezone_html',
    };
    jQuery.post( idf_ajaxurl, data, function(response) {
        var r = jQuery.parseJSON(response);
        jQuery(ele).removeClass('loading');
        showWizPopup(r.result.heading, r.result.content);
    });
}

function wizSaveTimezone(ele) {
    jQuery(ele).addClass('loading');
    var data = {
        action: 'idf_wizard_save_timezone',
        wiz_timezone: jQuery('#wiz-notice #timezone_string option:selected').val(),
        security: jQuery('input[name="idf_activate_plugins_nonce"]').val()
    };
    jQuery.post( idf_ajaxurl, data, function(response) {
        var r = jQuery.parseJSON(response);
        jQuery(ele).removeClass('loading');
        //jQuery('.ign-tools li:eq(2) .check').addClass('checked');
        jQuery('.button[onclick="wizSetTimezone(this);"]').parent().find('.check').addClass('checked');
        showWizPopup(r.result.heading, r.result.content);
    });
}

function wizSetPermalink(ele) {
    jQuery(ele).addClass('loading');
    var data = {
        action: 'idf_wizard_set_permalink',
    };
    jQuery.post( idf_ajaxurl, data, function(response) {
        var r = jQuery.parseJSON(response);
        jQuery(ele).removeClass('loading');
        jQuery(ele).parent().find('.check').addClass('checked');
        showWizPopup(r.result.heading, r.result.content);
    });
}

function wizReceiptSettings(ele) {
    jQuery(ele).addClass('loading');
    var data = {
        action: 'idf_wizard_get_receipt_html',
    };
    jQuery.post( idf_ajaxurl, data, function(response) {
        var r = jQuery.parseJSON(response);
        jQuery(ele).removeClass('loading');
        showWizPopup(r.result.heading, r.result.content);
    });
}

function saveReceiptSettings(ele) {
    jQuery(ele).addClass('loading');
    var coName = jQuery('#wiz-notice #co-name');
    var coEmail = jQuery('#wiz-notice #co-email');

    // Reset error messages
    coName.next('.ign-receipt-settings-error-message').hide();
    coEmail.next('.ign-receipt-settings-error-message').hide();

    var coNameValue = coName.val().trim();
    var coEmailValue = coEmail.val().trim();

    if (coNameValue === '') {
        coName.next('.ign-receipt-settings-error-message').show();
    }

    if (coEmailValue === '') {
        coEmail.next('.ign-receipt-settings-error-message').show();
    }

    if (coNameValue === '' || coEmailValue === '') {
        jQuery(ele).removeClass('loading');
        return; // Don't proceed if there are empty fields
    }

    var data = {
        action: 'idf_wizard_save_receipt_settings',
        co_name: coNameValue,
        co_email: coEmailValue,
        security: jQuery('input[name="idf_activate_plugins_nonce"]').val()
    };

    jQuery.post(idf_ajaxurl, data, function(response) {
        var r = jQuery.parseJSON(response);
        jQuery(ele).removeClass('loading');
        //jQuery('.ign-tools li:eq(4) .check').addClass('checked');
        jQuery('.button[onclick="wizReceiptSettings(this);"]').parent().find('.check').addClass('checked');
        showWizPopup(r.result.heading, r.result.content);
    });
}

function wizPaymentGateway(ele) {
    jQuery(ele).addClass('loading');
    var data = {
        action: 'idf_wizard_payment_gateway'
    };
    jQuery.post( idf_ajaxurl, data, function(response) {
        var r = jQuery.parseJSON(response);
        jQuery(ele).removeClass('loading');
        jQuery(ele).parent().find('.check').addClass('checked');
        //showWizPopup(r.result.heading, r.result.content);
    });
}

function wizCurrencyPreference(ele) {
    jQuery(ele).addClass('loading');
    var data = {
        action: 'idf_wizard_get_currency_html',
    };
    jQuery.post( idf_ajaxurl, data, function(response) {
        var r = jQuery.parseJSON(response);
        jQuery(ele).removeClass('loading');
        showWizPopup(r.result.heading, r.result.content);
    });
}

function saveGlobalCurrency(ele) {
    jQuery(ele).addClass('loading');
    var data = {
        action: 'idf_wizard_save_global_currency',
        global_currency: jQuery('#wiz-notice #global-currency').val(),
        security: jQuery('input[name="idf_activate_plugins_nonce"]').val()
    };
    jQuery.post( idf_ajaxurl, data, function(response) {
        var r = jQuery.parseJSON(response);
        jQuery(ele).removeClass('loading');
        //jQuery('.ign-tools li:eq(6) .check').addClass('checked');
        jQuery('.button[onclick="wizCurrencyPreference(this);"]').parent().find('.check').addClass('checked');
        showWizPopup(r.result.heading, r.result.content);
    });
}

function wizCreatePrivacyPolicy(ele) {
    jQuery(ele).addClass('loading');
    var data = {
        action: 'idf_wizard_create_privacy_policy',
    };
    jQuery.post( idf_ajaxurl, data, function(response) {
        var r = jQuery.parseJSON(response);
        jQuery(ele).removeClass('loading');
        jQuery(ele).parent().find('.check').addClass('checked');
        showWizPopup(r.result.heading, r.result.content);
    });
}

function wizTermsofUse(ele) {
    jQuery(ele).addClass('loading');
    var data = {
        action: 'idf_wizard_create_terms_of_use',
    };
    jQuery.post( idf_ajaxurl, data, function(response) {
        var r = jQuery.parseJSON(response);
        jQuery(ele).removeClass('loading');
        jQuery(ele).parent().find('.check').addClass('checked');
        showWizPopup(r.result.heading, r.result.content);
    });
}

function wizCreateSampleProject(ele) {
    jQuery(ele).addClass('loading');
    var data = {
        action: 'idf_wizard_create_sample_project',
    };
    jQuery.post( idf_ajaxurl, data, function(response) {
        var r = jQuery.parseJSON(response);
        jQuery(ele).removeClass('loading');
        jQuery(ele).parent().find('.check').addClass('checked');
        showWizPopup(r.result.heading, r.result.content);
    });
}

function wizDeleteSampleProject(ele) {
    var html = `<form method="POST" action="" class="confirmation">
			<div class="form-input">
                <p>It will delete the <b>Sample Project</b>, the products, the product→reward level connection, and any orders in IgnitionDeck » IDCF Orders and IDC » Orders permanently.</p>
			</div>
			<div class="submit">
				<input type="button" class="button button-secondary" value="Close" onclick="wizClosePopup()">
				<input type="button" class="button button-primary" value="Delete" onclick="wizConfirmDelete(this)">
			</div>
		</form>`;
    showWizPopup('Confirm', html);
}

function wizConfirmDelete(ele) {
    jQuery(ele).addClass('loading');
    var data = {
        action: 'idf_wizard_delete_sample_project',
    };
    jQuery.post( idf_ajaxurl, data, function(response) {
        var r = jQuery.parseJSON(response);
        jQuery(ele).removeClass('loading');
        jQuery('.button[onclick="wizCreateSampleProject(this);"]').parent().find('.check').removeClass('checked');
        showWizPopup(r.result.heading, r.result.content);
    });
}

function showWizPopup(heading = 'Notice', content='') {
    jQuery('#wiz-notice-title').html(heading);
    jQuery('#wiz-notice-content').html(content);
    jQuery('#wiz-notice').fadeIn();
}

function wizClosePopup() {
    jQuery('#wiz-notice').fadeOut();
}
