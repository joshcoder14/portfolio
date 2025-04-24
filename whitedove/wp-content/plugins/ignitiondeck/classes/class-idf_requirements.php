<?php
class IDF_Requirements {

	var $requirements;
	/**
	 * Constructor for IDF_Requirements class.
	 *
	 * Initializes the requirements property with PHP version and extensions.
	 */
	function __construct() {
		$requirements = new stdclass();
		$requirements->php = array(
			'extensions' => array(
				'curl',
				'zip',
			),
			'version' => '5.6'
		);
		$this->requirements = $requirements;
	}

	/**
	 * Check PHP version
	 *
	 * Checks if the current PHP version meets the minimum requirement.
	 *
	 * @return stdClass The status and message of the PHP version check.
	 */
	function php_version_check() {
		$php_valid = new stdclass();
		$php_valid->status = phpversion() >= $this->requirements->php['version'];
		$php_valid->message = __('Your PHP version', 'idf').' '.phpversion().' '.($php_valid->status ? __('meets', 'idf') : __('does not meet', 'idf')).' '.__('the minimum requirement of PHP version', 'idf').': '.$this->requirements->php['version'].'.';
		return $php_valid;
	}

	/**
	 * Check PHP extension requirements
	 *
	 * Checks if the required PHP extensions are installed.
	 *
	 * @return stdClass The status and message of the PHP extension check.
	 */
	function php_extension_check() {
		$ext_valid = new stdclass();
		$ext_valid->status = true;
		$ext_valid->message = '';
		foreach ($this->requirements->php['extensions'] as $extension) {
			if (!extension_loaded($extension)) {
				$ext_valid->status = false;
				$ext_valid->message .= (!empty($ext_valid) ? ' ' : '').__('Missing extension', 'idf').' '.$extension.'.';
			}
			else {
				$ext_valid->message .= (!empty($ext_valid) ? ' ' : '').__('Required extension', 'idf').' '.$extension.' '.__('is installed', 'idf').'.';
			}
		}
		return $ext_valid;
	}

	/**
	 * Get requirements data
	 *
	 * Retrieves the data for PHP version and extension requirements.
	 *
	 * @return stdClass The data for PHP version and extension requirements.
	 */
	function requirements_data() {
		$requirements_met = true;
		$requirements_data = new stdclass();
		$requirements_data->version_data = $this->php_version_check();
		$requirements_data->extension_data = $this->php_extension_check();
		return $requirements_data;
	}

	/**
	 * Check installation requirements
	 *
	 * Checks if the installation meets the minimum requirements for PHP version and extensions.
	 *
	 * @return stdClass The status and message of the installation check.
	 */
	function install_check() {
		$requirements_data = $this->requirements_data();
		$install_data = array('status' => 1);
		foreach ($requirements_data as $data) {
			if (!$data->status) {
				$install_data['status'] = 0;
				$install_data[] = $data;
			}
		}
		return (object) $install_data;
	}
}
?>