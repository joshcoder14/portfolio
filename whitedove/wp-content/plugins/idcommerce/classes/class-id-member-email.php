<?php
class ID_Member_Email {
	var $to;
	var $subject;
	var $message;
	var $user_id;
	var $headers;

	function __construct(
		$to = null,
		$subject = '',
		$message = '',
		$user_id = null,
		$headers = null
		)
	{
		// #devnote we should wpautop and stripslashes message here
		$this->to = $to;
		$this->subject = $subject;
		$this->message = $message;
		$this->user_id = $user_id;
		$this->headers = $headers;
	}

	function send_mail() {
		/*
		** Ensure we have company information
		*/

		$settings = get_option('md_receipt_settings');
		if (!empty($settings)) {
			$settings = maybe_unserialize($settings);
			$coname = (!empty($settings['coname']) ? apply_filters('idc_company_name', $settings['coname']) : get_option('blogname', ''));
			$coemail = (!empty($settings['coemail']) ? $settings['coemail'] : get_option('admin_email', ''));
		}
		else {
			$coname = '';
			$coemail = get_option('admin_email', '');
		}

		if (!empty($coemail)) {
			if (!empty($this->user_id)) {
				$user = get_user_by('id', $this->user_id);
				if (isset($user)) {
					$fname = $user->user_firstname;
					$lname = $user->user_lastname;
				}
			}

			if (empty($headers)) {
				$this->headers = 'From: '.$coname.' <'.$coemail.'>' . "\n";
				$this->headers .= 'Reply-To: '.$coemail."\n";
				$this->headers .= "MIME-Version: 1.0\n";
				$this->headers .= "Content-Type: text/html; charset=UTF-8\n";
			}
			$go = wp_mail($this->to, $this->subject, $this->message, $this->headers);
		}
	}
}
?>