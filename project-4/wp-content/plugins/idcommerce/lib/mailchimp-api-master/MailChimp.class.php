<?php
/**
 * Super-simple, minimum abstraction MailChimp API v2 wrapper
 * 
 * Requires curl (I know, right?)
 * This probably has more comments than code.
 * 
 * @author Drew McLellan <drew.mclellan@gmail.com>
 * @version 1.0
 */
class MailChimp
{
	private $api_key;
	private $api_endpoint = 'https://<dc>.api.mailchimp.com/3.0/';
	private $verify_ssl   = false;

	/**
	 * Create a new instance
	 * @param string $api_key Your MailChimp API key
	 */
	function __construct($api_key)
	{
		$this->api_key = $api_key;
		list(, $datacentre) = explode('-', $this->api_key);
		$this->api_endpoint = str_replace('<dc>', $datacentre, $this->api_endpoint);
	}

	/**
	 * Call an API method. Every request needs the API key, so that is added automatically -- you don't need to pass it in.
	 * @param  string $route The API route to call, e.g. 'lists/list'
	 * @param  array  $args   An array of arguments to pass to the method. Will be json-encoded for you.
	 * @return array          Associative array of json decoded API response.
	 */
	public function call($route, $args=array())
	{
		return $this->idc_mc_http_post_request($route, $args);
	}

	/**
	 * Performs the underlying HTTP request. Not very exciting
	 * @param  string $route The API route to be called
	 * @param  array  $args   Assoc array of parameters to be passed
	 * @return array|WP_Error Assoc array of decoded result or WP_Error on failure
	 */
	private function idc_mc_http_post_request($route, $args = array())
	{
		$api_endpoint = $this->api_endpoint . '/' . $route;
		$api_headers = array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Basic ' . base64_encode('user:' . $this->api_key),
		);

		$response = wp_remote_post(
			$api_endpoint,
			array(
				'headers'    => $api_headers,
				'body'       => json_encode($args),
				'timeout'    => 10,
				'blocking'   => true,
				'sslverify'  => false,
			)
		);

		// Check for errors
		if (is_wp_error($response)) {
			error_log('HTTP Request Error: ' . $response->get_error_message());
			return false;
		}

		// Parse the response
		$result = json_decode(wp_remote_retrieve_body($response), true);

		return $result;
	}


}
