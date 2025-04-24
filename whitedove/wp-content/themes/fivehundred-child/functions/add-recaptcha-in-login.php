<?php

class reCAPTCHA_Login_Form {

	/** @type string private key|public key */
	private $public_key, $private_key;

    private $is_login_form = false;

	/** class constructor */
	public function __construct() {
	
		$is_enabled = true;

		if( defined('DEFAULT_PUBLIC_KEY') && defined('DEFAULT_PRIVATE_KEY') &&  DEFAULT_PUBLIC_KEY && DEFAULT_PRIVATE_KEY){

			$this->public_key  = DEFAULT_PUBLIC_KEY;
			$this->private_key = DEFAULT_PRIVATE_KEY;	
			
		} elseif( function_exists('get_field') && get_field('public_key','options') && get_field('private_key','options')  )  {
			
			$this->public_key  = get_field('public_key','options');
			$this->private_key = get_field('private_key','options'); 	
			
		} else {
			$is_enabled = false; // recaptcha will not display if no keys from acf and wp-config set and defined.
		
		}

		if($is_enabled){
			add_action( 'login_form', array( $this, 'captcha_display' ) );
            add_action( 'login_form', array( $this, 'set_login_form_flag' ) );
			// authenticate the captcha answer
			// add_action( 'wp_authenticate_user', array( $this, 'validate_captcha_field' ), 10, 2 );
			// Output edit of login form
			add_action( 'login_enqueue_scripts', array( $this, 'my_custom_login_stylesheet' ) );
		}
	}
  

    public function set_login_form_flag() {
        $this->is_login_form = true;
    }

    public function conditionally_add_authenticate_user() {
        if ( $this->is_login_form ) {
            add_action( 'wp_authenticate_user', array( $this, 'validate_captcha_field' ), 10, 2 );
        }
    }

	/** Output the reCAPTCHA form field. */
	public function captcha_display() {
		?>
			<script src="https://www.google.com/recaptcha/api.js" async defer></script>
			<div class="g-recaptcha" style="padding-bottom: 15px" data-sitekey="<?php echo $this->public_key; ?>"></div>
		<?php
		
	}
	// Edit login form css
	public function my_custom_login_stylesheet(){
		echo '<style type="text/css">
        body.login div#login_error{
			width: 324px;
			color: #d63638;
		}
		body.login div#login .message{
			width: 324px;
		}
		body.login div#login form#loginform{
			width: 360px;
		}
		
    </style>';
        
    
	}
    
	/**
	 * Verify the captcha answer
	 *
	 * @param $user string login username
	 * @param $password string login password
	 *
	 * @return WP_Error|WP_user
	 */
	public function validate_captcha_field($user, $password) {

		if ( ! isset( $_POST['g-recaptcha-response'] ) || empty( $_POST['g-recaptcha-response'] ) ) {
			return new WP_Error( 'empty_captcha', 'CAPTCHA should not be empty');
		}

		if( isset( $_POST['g-recaptcha-response'] ) && $this->get_recaptcha_response() == 'false' ) {
			return new WP_Error( 'invalid_captcha', 'CAPTCHA response was incorrect');
		}

		return $user;
	}

	/**
	 * Send HTTP POST request and return the response.
	 *
	 * @param $url and $response
	 *
	 * @return bool
	 */
	public function get_recaptcha_response(){
		$captcha = $_POST['g-recaptcha-response'];
		$secretKey = $this->private_key;	
        // $ip = $_SERVER['REMOTE_ADDR'];

        // post request to server
        $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) .  '&response=' . urlencode($captcha);
        $response = file_get_contents($url); 
		$responseKeys = json_decode($response,true);

		if($responseKeys['success']){
			return 'true';
		}else{
			return 'false';
		}
	
	}
}

add_action("init", function(){ new reCAPTCHA_Login_Form(); });
