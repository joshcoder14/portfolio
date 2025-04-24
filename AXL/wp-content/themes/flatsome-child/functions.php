<?php
// Add custom Theme Functions here

/**
* Edit my account menu order
*/

function my_account_menu_order() {
    $menuOrder = array(
       'dashboard' => __( 'Dashboard', 'woocommerce' ),
       'orders' => __( 'My Orders', 'woocommerce' ),
       'edit-address' => __( 'Addresses', 'woocommerce' ),
       'edit-account' => __( 'Account Details', 'woocommerce' ),
       'customer-logout' => __( 'Logout', 'woocommerce' ),
    );
    return $menuOrder;
}
add_filter ( 'woocommerce_account_menu_items', 'my_account_menu_order' );


//Reusable packaging option
add_action( 'woocommerce_before_order_notes', 'woocommerce_reusable_packaging_option');
function woocommerce_reusable_packaging_option($checkout) {
    echo '<div class="custom_packaging_class"><h3>'.__('SAVE A PACKAGING WITH US'). '</h3>';
    woocommerce_form_field( 'custom_packaging_checkbox', array(
        'type'    => 'checkbox',
        'label'   => __(' Yes, please reused packaging if available.'),
    ), $checkout->get_value('custom_packaging_checkbox') );

    echo '</div><br />';
}

function action_woocommerce_checkout_create_order( $order, $data) {
    if ( isset($_POST['custom_packaging_checkbox']) && ! empty($_POST['custom_packaging_checkbox']) ) {
        $order->update_meta_data( 'custom_packaging_checkbox', sanitize_text_field( $_POST['custom_packaging_checkbox']) );
    }
}

add_action( 'woocommerce_checkout_create_order', 'action_woocommerce_checkout_create_order', 10, 2 );
function action_woocommerce_admin_order_data_after_shipping_address( $order ) {
    //echo '<p>' . $order->get_meta('custom_packaging_checkbox') . '</p>'; 
    if ( $order->get_meta('custom_packaging_checkbox') == '1' ){
        echo '<p><strong>'.__('Reuse Packaging').':</strong> '.__('Shipping with reused packaging preferred!') . '</p>'; 
    }else{
        echo '<p><strong>'.__('Reuse Packaging').':</strong> '.__('Shipping without reused packaging!') . '</p>'; 
    }
    //echo '<p><strong>'.__('Reuse Packaging').':</strong> '.__('Shipping with reused packaging preferred!') . '</p>'; 
}
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'action_woocommerce_admin_order_data_after_shipping_address', 10, 1 );

//email order to customer
add_action('woocommerce_email_order_details', 'action_after_email_order_details', 25, 4 );
function action_after_email_order_details( $order ) {
    if ( $order->get_meta('custom_packaging_checkbox') == '1' ){
        echo '<p><strong>'.__('Reused Packaging').':</strong> '.__('Thanks for helping us save resources! We will prefer an used shipping packaging to a new one, if available.') . '</p>'; 
    }
}