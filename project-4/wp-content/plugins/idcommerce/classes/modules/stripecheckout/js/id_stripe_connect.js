jQuery(document).ready(function() {
    jQuery(document).on('click','#stripe_connect_enable',function() {
        if(jQuery(this).is(':checked')) {
            jQuery('#sc_display').slideDown()
            //jQuery('#stripe_connect_enable_val').val(1)
        } else {
            jQuery('#sc_display').slideUp()
            //jQuery('#stripe_connect_enable_val').val(0)
        }
    });
});
