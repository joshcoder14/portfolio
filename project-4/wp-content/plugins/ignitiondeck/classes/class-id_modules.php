<?php
class ID_Modules {
	public static $PHP_EXTENSION = '.php';
	var $exdir;
	var $moddir;
	var $custom_moddir;

	/**
	 * Constructor for ID_Modules class.
	 *
	 * Initializes the exdir array and runs necessary functions.
	 */
	function __construct() {
		$this->exdir = array('cgi-bin', '.', '..');
		// run functions
		$this->transientToOption();
		$this->set_moddir();
		$this->set_module_hooks();
		$this->load_modules();
	}

	/**
	 * Transient to Option
	 *
	 * If the 'id_modules' option exists, do nothing. Otherwise, update the 'id_modules'
	 * option with the value of the 'id_modules' transient.
	 */
	function transientToOption() {
		if(get_option('id_modules')) {
			//echo 'Already Done';
		} else {
			//echo 'Update It';
			$modules = get_transient('id_modules');
			update_option('id_modules', $modules);
		}
	}

	/**
	 * Set moddir
	 *
	 * Initializes the moddir and custom_moddir properties with the directory paths for modules and custom modules.
	 */
	function set_moddir() {
		$this->moddir = dirname(__FILE__). '/' . 'modules/';
		$this->custom_moddir = dirname(__FILE__) . '/' . 'custom-modules/';
	}

	/**
	 * Set Module Hooks
	 *
	 * Adds hooks for IDF modules, including adding modules to IDF menu, checking for
	 * module status changes, and filtering active and custom modules.
	 */
	private function set_module_hooks() {
		// add idc modules to idf menu
		add_filter('id_module_list', array($this, 'show_modules'));
		// check for change in module status
		add_action('init', array($this, 'module_status'));
		add_filter('id_active_modules', array($this, 'id_default_modules'));
		add_filter('id_modules', array($this, 'id_custom_modules'), 11);
		add_filter('id_module_list_wrapper_class', array($this, 'module_list_wrapper_class'), 10, 2);
	}

	/**
	 * Show Modules
	 *
	 * Displays the IDF modules in the menu and returns the list of modules.
	 *
	 * @param array $modules The list of modules to display.
	 * @return array The modified list of modules.
	 */
	function show_modules($modules) {
		// show modules in the IDF modules menu
		$id_modules = $this->get_modules();
		if (empty($id_modules)) {
			return $id_modules;
		}
		$site_url = site_url();
		foreach ($id_modules as $module) {
			$thisfile = (is_dir($this->moddir . $module) ? $this->moddir . $module : $this->custom_moddir . $module);
			if (is_dir($thisfile) && !in_array($module, $this->exdir)) {
				if (!file_exists($thisfile . '/' . 'module_info.json')) {
					continue;
				}
				$thisfile_url = (is_dir($this->moddir . $module) ?  $site_url . '/wp-content/plugins/ignitiondeck/classes/modules/' . $module : $site_url . '/wp-content/plugins/ignitiondeck/classes/custom-modules/' . $module);
				$response = wp_remote_get( $thisfile_url . '/' . 'module_info.json' );
				$file_contents = wp_remote_retrieve_body( $response );
				$info = json_decode( $file_contents, true );
				
				$new_module = (object) array(
					'title' => $info['title'],
					'short_desc' => $info['short_desc'],
					'link' => apply_filters('id_module_link', menu_page_url('idf-extensions', false) . '&id_module='.$module),
					'doclink' => $info['doclink'],
					'thumbnail' => plugins_url('modules/' . $module . '/thumbnail.png', __FILE__),
					'basename' => $module,
					'type' => $info['type'],
					'requires' => $info['requires'],
					'priority' => (isset($info['priority']) ? $info['priority'] : ''),
					'category' => (isset($info['category']) ? $info['category'] : ''),
					'tags' => (isset($info['tags']) ? $info['tags'] : ''),
					'status' => (isset($info['status']) ? $info['status'] : '')
				);
				if ($info['status'] == 'test') {
					// allow devs to activate
					if (defined('ID_DEV_MODE') && 'ID_DEV_MODE' == true) {
						$info['status'] = 'live';
						$new_module->short_desc .= ' '.__('(DEV_MODE)', 'idf');
					}
				}
				if ($info['status'] == 'live') {
					$modules[] = $new_module;
				}
			}
		}
		return $modules;
	}

	/**
	 * Get Modules
	 *
	 * Retrieves the list of IDF modules from the specified directory and filters out
	 * excluded modules.
	 *
	 * @return array The list of IDF modules.
	 */
	function get_modules() {
		$modules = array();
		$subfiles = scandir($this->moddir);
		foreach ($subfiles as $file) {
			$thisfile = $this->moddir . $file;
			if (is_dir($thisfile) && !in_array($file, $this->exdir) && substr($file, 0, 1) !== '.') {
				$modules[] = $file;
			}
		}
		return apply_filters('id_modules', $modules);
	}

	/**
	 * Load Modules
	 *
	 * Loads the list of active modules and loads each module.
	 */
	public function load_modules() {
		// Load the list of active modules
		$modules = self::get_active_modules();
		if (!empty($modules)) {
			foreach ($modules as $module) {
				$this->load_module($module);
			}
		}
	}

	/**
	 * Get Module Home
	 *
	 * Retrieves the home location of the specified module.
	 *
	 * @return string The home location of the module.
	 */
	public function get_module_home() {
		// Helps us find where the module is located

	}

	/**
	 * Load Module
	 *
	 * Loads the class file of the specified module. If the class file exists in the
	 * standard module directory, it is required. If not, it checks the custom module
	 * directory and requires the class file from there if it exists.
	 *
	 * @param string $module The name of the module to load.
	 */
	public function load_module($module) {
		// Loading the class file of the module
		if (file_exists($this->moddir . $module . '/' . 'class-' . $module . self::$PHP_EXTENSION)) {
			require_once $this->moddir . $module . '/' . 'class-' . $module . self::$PHP_EXTENSION;
		}
		else if (file_exists($this->custom_moddir . $module . '/' . 'class-' . $module . self::$PHP_EXTENSION)) {
			require_once $this->custom_moddir . $module . '/' . 'class-' . $module . self::$PHP_EXTENSION;
		}
	}

	/**
	 * Module Status
	 *
	 * Checks and sets the status of a module if the user has the capability to manage options.
	 * If the 'id_module' and 'module_status' parameters are set in the URL, it sets the module status
	 * and redirects to the IDF extensions page in the admin area.
	 */
	function module_status() {
		if (is_admin() && current_user_can('manage_options')) {
			if (isset($_GET['id_module']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'module_status_nonce')) {
				$module = $_GET['id_module'];
				if (!empty($module)) {
					if (isset($_GET['module_status'])) {
						$status = $_GET['module_status'];
						do_action('id_set_module_status_before', $module, $status);
						$this->set_module_status($module, $status);
						do_action('id_set_module_status', $module, $status);
						wp_safe_redirect( admin_url('admin.php?page=idf-extensions') );
					}
				}
			}
		}
	}

	/**
	 * ID Default Modules
	 *
	 * Returns the default modules.
	 *
	 * @param array $modules The list of modules.
	 * @return array The modified list of modules.
	 */
	function id_default_modules($modules) {
		return $modules;
	}

	/**
	 * Custom Modules
	 *
	 * Retrieves the list of custom modules from the custom module directory and adds them to the existing modules array.
	 *
	 * @param array $modules The list of modules to add custom modules to.
	 * @return array The modified list of modules with custom modules added.
	 */
	function id_custom_modules($modules = array()) {
		if (file_exists($this->custom_moddir)) {
			$subfiles = scandir($this->custom_moddir);
			if (!empty($subfiles)) {
				foreach ($subfiles as $file) {
					$thisfile = $this->custom_moddir . $file;
					if (is_dir($thisfile) && !in_array($file, $this->exdir) && substr($file, 0, 1) !== '.') {
						$modules[] = $file;
					}
				}
			}
		}
		return apply_filters('id_custom_modules', $modules);
	}

	/**
	 * Module List Wrapper Class
	 *
	 * Adds classes to the module list wrapper based on category and tags.
	 *
	 * @param string $classes The list of classes to add to the module list wrapper.
	 * @param object $item The module item.
	 * @return string The modified list of classes for the module list wrapper.
	 */
	function module_list_wrapper_class($classes, $item) {
		if (!empty($classes)) {
			$classes = explode(' ', $classes);
		}
		else {
			$classes = array();
		}
		if (!empty($item->category)) {
			$cat_list = explode(' ', $item->category);
			foreach ($cat_list as $cat) {
				$classes[] = $cat;
			}
		}
		if (!empty($item->tags)) {
			$tag_list = explode(' ', $item->tags);
			foreach ($tag_list as $tag) {
				$classes[] = $tag;
			}
		}
		$classes[] = 'extension';
		$classes = implode(' ', array_unique($classes));
		return $classes;
	}
	/**
	 * Get Active Modules
	 *
	 * Retrieves the list of active modules from the database and applies filters.
	 *
	 * @return array The list of active modules.
	 */
	public static function get_active_modules() {
		// Get list of active modules
		//$modules = get_transient('id_modules');
		$modules = get_option('id_modules');
		$modules = is_array($modules)?$modules:array();
		return apply_filters('id_active_modules', $modules);
	}

	/**
	 * Check if a module is active
	 *
	 * This function checks if a module is active based on the list of active modules.
	 *
	 * @param string $module The module to check for activation.
	 * @return bool Whether the module is active or not.
	 */
	public static function is_module_active($module) {
		$modules = self::get_active_modules();
		return (in_array($module, $modules));
	}

	/**
	 * Set Module Status
	 *
	 * Sets the status of a module based on the provided module and status. If the
	 * status is true, the module is added to the list of active modules. If the
	 * status is false, the module is removed from the list of active modules.
	 *
	 * @param string $module The module to set the status for.
	 * @param bool $status The status to set for the module.
	 */
	public static function set_module_status($module, $status) {
		$modules = self::get_active_modules();
		switch ($status) {
			case true:
				if (empty($modules)) {
					$modules = array();
					$modules[] = $module;
				}
				else if (!in_array($module, $modules)) {
					$modules[] = $module;
				}
				break;
			default:
				// deactivate
				if (!empty($modules)) {
					if (in_array($module, $modules)) {
						foreach ($modules as $k=>$v) {
							if ($module == $v) {

								$elem = $k;
								break;
							}
						}
						if (isset($elem)) {
							unset($modules[$elem]);
						}
					}
				}
				break;
		}
		self::save_modules($modules);
	}

	/**
	 * Save Modules
	 *
	 * Saves the list of active modules to the database. It updates the option 'id_modules'
	 * with the provided list of modules.
	 *
	 * @param array|null $modules The list of active modules to be saved. Default is null.
	 */
	public static function save_modules($modules = null) {
		//set_transient('id_modules', $modules, 0);
		update_option('id_modules', $modules);
	}

	/**
	 * Check if a module is locked
	 *
	 * Checks if a module is locked based on its requirements and the user's
	 * licensing status or product version.
	 *
	 * @param object $module The module to check.
	 * @return int The lock status of the module (0 for unlocked, 1 for locked).
	 */
	public static function is_module_locked($module) {
		if (empty($module->requires)) {
			return 0;
		}
		$locked = 1;
		switch ($module->requires) {
			case 'idc':
				if (is_idc_licensed()) {
					$locked = 0;
				}
				break;
			case 'ide':
				$pro = get_option('is_id_pro', false);
				if ($pro) {
					$locked = 0;
				}
				break;
			default:
				$locked = 0;
				break;
		}
		return $locked;
	}
}
new ID_Modules();