<?php
    add_action( 'wp_ajax_nopriv_submit_contact', 'submit_contact' );
    add_action( 'wp_ajax_submit_contact', 'submit_contact' );

    function submit_contact(){
        $contact = $_POST['data'];
        $contact = (object)$contact;

        $to = get_field('email_receiver','options');
        $subject = get_field('subject','options');
        $body = "
            <h4>First name: $contact->first_name </h4>
            <h4>Last Name: $contact->last_name</h4>
            <h4>Company: $contact->company</h4>
            <h4>Message: </h4>
            <p>$contact->message</p>
        ";
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        $result = wp_mail( $to, $subject, $body, $headers );

        if($result){
            echo json_encode( array(
                "status" => 'success',
                "response" => get_field('success_response','options')
            ));
        }
        else{
            error_log("Emails in contact form not sent.");
            echo json_encode( array(
                "status" => 'Failed!',
                "response" => get_field('error_response','options')
            ));
        }
        
        die();
    }

?>