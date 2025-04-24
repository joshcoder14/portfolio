<?php
class IDF_wizard {
	function __construct() {
		add_filter( 'plugin_action_links_ignitiondeck/idf.php', array( $this, 'idf_settings_link' ) );
		add_filter( 'plugin_row_meta', array( $this, 'idf_plugin_row_meta' ), 10, 2 );

		add_action( 'wp_ajax_idf_wizard_register', array( $this, 'register' ) );
		add_action( 'wp_ajax_idf_wizard_install_plugins', array( $this, 'install_plugins' ) );
		add_action( 'wp_ajax_idf_wizard_activate_plugins', array( $this, 'activate_plugins' ) );
		add_action( 'wp_ajax_idf_wizard_verify_license', array( $this, 'verify_license' ) );
		add_action( 'wp_ajax_idf_wizard_save_payment', array( $this, 'save_payment' ) );
		add_action( 'wp_ajax_idf_wizard_install_themes', array( $this, 'install_themes' ) );
        // action for themes validation licence
        add_action( 'wp_ajax_idf_wizard_validate_themes_access', array( $this, 'validate_themes_access' ) );

        add_action( 'wp_ajax_idf_wizard_check_config', array( $this, 'check_config' ) );
		add_action( 'wp_ajax_idf_wizard_create_dashboard', array( $this, 'create_dashboard' ) );
		add_action( 'wp_ajax_idf_wizard_create_checkout', array( $this, 'create_checkout' ) );
		add_action( 'wp_ajax_idf_wizard_get_timezone_html', array( $this, 'get_timezone_html' ) );
		add_action( 'wp_ajax_idf_wizard_save_timezone', array( $this, 'save_timezone' ) );
		add_action( 'wp_ajax_idf_wizard_set_permalink', array( $this, 'set_permalink' ) );
		add_action( 'wp_ajax_idf_wizard_get_receipt_html', array( $this, 'get_receipt_html' ) );
		add_action( 'wp_ajax_idf_wizard_save_receipt_settings', array( $this, 'save_receipt_settings' ) );
		add_action( 'wp_ajax_idf_wizard_payment_gateway', array( $this, 'payment_gateway' ) );
		add_action( 'wp_ajax_idf_wizard_get_currency_html', array( $this, 'get_currency_html' ) );
		add_action( 'wp_ajax_idf_wizard_save_global_currency', array( $this, 'save_global_currency' ) );
		add_action( 'wp_ajax_idf_wizard_create_privacy_policy', array( $this, 'create_privacy_policy' ) );
		add_action( 'wp_ajax_idf_wizard_create_terms_of_use', array( $this, 'create_terms_of_use' ) );
		add_action( 'wp_ajax_idf_wizard_create_sample_project', array( $this, 'create_sample_project' ) );
		add_action( 'wp_ajax_idf_wizard_delete_sample_project', array( $this, 'delete_sample_project' ) );
	}

	/**
	 * Add settings link to plugin action links.
	 *
	 * Adds a settings link to the plugin action links on the WordPress plugins page.
	 *
	 * @param array $links An array of plugin action links.
	 * @return array An array of plugin action links with the added settings link.
	 */
	function idf_settings_link( $links ) {
		$url           = esc_url(
			add_query_arg(
				'page',
				'idf',
				get_admin_url() . 'admin.php?page=idf#wiz-register'
			)
		);
		$settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
		array_unshift(
			$links,
			$settings_link
		);
		return $links;
	}

	/**
	 * Add plugin row meta for IgnitionDeck plugin.
	 *
	 * Adds additional row meta to the IgnitionDeck plugin on the WordPress plugins page.
	 *
	 * @param array $links An array of plugin action links.
	 * @param string $file The plugin file path.
	 * @return array An array of plugin action links with the added row meta.
	 */
	function idf_plugin_row_meta( $links, $file ) {
		if ( 'ignitiondeck/idf.php' !== $file ) {
			return $links;
		}
		$docs_url            = 'https://docs.ignitiondeck.com/';
		$getting_started_url = 'https://docs.ignitiondeck.com/category/15-getting-started';
		$edition_url         = 'https://docs.ignitiondeck.com/article/47-which-plugin-package-is-right-for-me';
		$valet_url           = 'https://docs.ignitiondeck.com/article/154-how-to-request-the-valet-setup-service';

		$row_meta = array(
			'docs'            => '<a href="' . esc_url( $docs_url ) . '" aria-label="' . esc_attr__( 'View IgnitionDeck documentation', 'idf' ) . '">' . esc_html__( 'Documentation Site', 'idf' ) . '</a>',
			'getting_started' => '<a href="' . esc_url( $getting_started_url ) . '" aria-label="' . esc_attr__( 'View Getting Started', 'idf' ) . '">' . esc_html__( 'Getting Started', 'idf' ) . '</a>',
			'support'         => '<a href="' . esc_url( $edition_url ) . '" aria-label="' . esc_attr__( 'Which Edition Is Right For Me?', 'idf' ) . '">' . esc_html__( 'Which Edition Is Right For Me?', 'idf' ) . '</a>',
			'valet'           => '<a href="' . esc_url( $valet_url ) . '" aria-label="' . esc_attr__( 'Valet Service', 'idf' ) . '">' . esc_html__( 'Valet Service', 'idf' ) . '</a>',
		);

		return array_merge( $links, $row_meta );
	}

	/**
	 * Register user with Mailchimp
	 *
	 * Registers the user's email with Mailchimp and adds them to the 'Dashboard' tag.
	 *
	 * @param string $email The user's email address.
	 * @return string The response from the Mailchimp API.
	 */
	function register() {
		// Verify the nonce
		check_ajax_referer('idf-activate-plugins-nonce', 'security');

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You don\'t have sufficient permissions to manage options.' );
		}

		$list_id = '500a881df9';
		$api_key = 'd7f27ffef3153597c80be0caf09686c5-us20';
	
		$email = $_POST['email'];
		update_option('idf_registered_email', $email);
	
		$params = array(
			'email_address' => $email,
			'status'        => 'subscribed',
			'tags'          => array('Dashboard'),
		);
	
		$response = wp_remote_post(
			'https://us20.api.mailchimp.com/3.0/lists/' . $list_id . '/members',
			array(
				'method'    => 'POST',
				'body'      => wp_json_encode($params),
				'headers'   => array(
					'Authorization' => 'apikey ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'timeout'   => 10, // Optional: you can specify a timeout in seconds
			)
		);
		
		if (is_wp_error($response)) {
			$error_message = $response->get_error_message();
			echo wp_json_encode(
				array(
					'error'   => true,
					'message' => $error_message,
				)
			);
		} else {
			$response_body = wp_remote_retrieve_body($response);
			// Decode the response body to ensure it's valid JSON
			$decoded_response = json_decode($response_body, true);
	
			if (json_last_error() === JSON_ERROR_NONE) {
				echo wp_json_encode($decoded_response);
			} else {
				// Handle unexpected response format
				echo wp_json_encode(
					array(
						'error'   => true,
						'message' => 'Unexpected response format.',
					)
				);
			}
		}
	
		exit;
	}

	/**
	 * Install or upgrade plugins.
	 *
	 * This function installs or upgrades plugins based on the provided plugin data.
	 *
	 * @param array $plugin An array containing the plugin name, slug, and URL.
	 * @return void
	 */
	function install_plugins() { 		
		// Verify the nonce
    	check_ajax_referer('idf-activate-plugins-nonce', 'security');

		// Check user capabilities.
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_die( 'You don\'t have sufficient permissions to install plugins.' );
		}
		$plugin = array(
			'name' => $_POST['name'],
			'slug' => $_POST['slug'],
			'url'  => $_POST['url'],
		);

		$status = 'Not Installed';
		if ( $this->is_plugin_installed( $plugin['slug'] ) ) {
			$this->upgrade_plugin( $plugin['slug'] );
			$installed = true;
			$status    = 'Updated';
		} else {
			$installed = $this->install_plugin( $plugin['url'] );
			$status    = 'Not Active';
		}

		echo esc_html( $status );
		exit;
	}

	/**
	 * Activate plugins.
	 *
	 * This function activates the specified plugin based on the provided plugin data.
	 *
	 * @param array $plugin An array containing the plugin name, slug, and URL.
	 * @return void
	 */
	function activate_plugins() {

		// Verify nonce.
		if ( ! isset( $_POST['idf_security'] ) || ! wp_verify_nonce( $_POST['idf_security'], 'idf-activate-plugins-nonce' ) ) {
			wp_die( 'Nonce verification failed!' );
		}

		// Check user capabilities.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_die( 'You don\'t have sufficient permissions to access this feature.' );
		}

		$plugin = array(
			'name' => $_POST['name'],
			'slug' => $_POST['slug'],
			'url'  => $_POST['url'],
		);

		$status = 'Not Active';

		$activate = activate_plugin( $plugin['slug'] );
		$status   = 'Installed and Activated';
		echo esc_html( $status );
		exit;
	}

	/**
	 * Install a plugin.
	 *
	 * This function installs a plugin based on the provided plugin ZIP file.
	 *
	 * @param string $plugin_zip The path to the plugin ZIP file.
	 * @return bool Whether the plugin was successfully installed or not.
	 */
	function install_plugin( $plugin_zip ) {
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		wp_cache_flush();
		$upgrader  = new Plugin_Upgrader();
		$installed = $upgrader->install( $plugin_zip );
		return $installed;
	}

	/**
	 * Upgrade a plugin.
	 *
	 * Upgrades the specified plugin by its slug.
	 *
	 * @param string $plugin_slug The slug of the plugin to upgrade.
	 * @return bool Whether the plugin was successfully upgraded or not.
	 */
	function upgrade_plugin( $plugin_slug ) {
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		wp_cache_flush();
		$upgrader = new Plugin_Upgrader();
		$upgraded = $upgrader->upgrade( $plugin_slug );
		return $upgraded;
	}

	/**
	 * Check if a plugin is installed.
	 *
	 * This function checks if a plugin with the specified slug is installed.
	 *
	 * @param string $slug The slug of the plugin to check.
	 * @return bool Whether the plugin is installed or not.
	 */
	function is_plugin_installed( $slug ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();
		if ( ! empty( $all_plugins[ $slug ] ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Verify license.
	 *
	 * Verifies the license key and updates the license.
	 *
	 * @param string $key The license key to verify.
	 * @return void
	 */
	function verify_license() {
		// Verify the nonce
		check_ajax_referer('idf-activate-plugins-nonce', 'security');

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You don\'t have sufficient permissions to manage options.' );
		}

		$key = sanitize_text_field( $_POST['license'] );
		idcf_license_update( $key );
		require_once WP_PLUGIN_DIR . '/ignitiondeck/templates/admin/_idfMenu/upgrade.php';
		require_once WP_PLUGIN_DIR . '/ignitiondeck/templates/admin/_idfMenu/configure.php';
		exit;
	}

	/**
	 * Save payment method.
	 *
	 * Saves the payment method and triggers an action to update the commerce platform.
	 *
	 * @param string $payment The payment method to save.
	 * @return void
	 */
	function save_payment() {
		// Verify the nonce
		check_ajax_referer('idf-activate-plugins-nonce', 'security');

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You don\'t have sufficient permissions to manage options.' );
		}

		$save_payment = sanitize_text_field( $_POST['payment'] );
		update_option( 'idf_commerce_platform', $save_payment );
		do_action( 'idf_update_commerce_platform', $save_payment );
		require_once WP_PLUGIN_DIR . '/ignitiondeck/templates/admin/_idfMenu/upgrade.php';
		require_once WP_PLUGIN_DIR . '/ignitiondeck/templates/admin/_idfMenu/configure.php';
		exit;
	}

	/**
	 * Install themes.
	 *
	 * Installs a theme based on the provided theme URL and slug.
	 *
	 * @param string $url The URL of the theme to install.
	 * @param string $slug The slug of the theme to install.
	 * @return void
	 */
	function install_themes() {
		// Verify the nonce
		check_ajax_referer('idf-activate-plugins-nonce', 'security');

		// Check user capabilities.
		if ( ! current_user_can( 'install_themes' ) ) {
			wp_die( 'You don\'t have sufficient permissions to install themes.' );
		}

		$status = __( 'Installed' );
		$theme  = array(
			'url'  => $_POST['url'],
			'slug' => $_POST['slug'],
		);

		$all_themes = wp_get_themes();
		if ( empty( $all_themes[ $theme['slug'] ] ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			include_once ABSPATH . 'wp-admin/includes/theme.php';

			$skin     = new WP_Ajax_Upgrader_Skin();
			$upgrader = new Theme_Upgrader( $skin );
			$result   = $upgrader->install( $theme['url'] );
			if ( is_wp_error( $result ) ) {
				$status = $result->get_error_message();
			} elseif ( is_wp_error( $skin->result ) ) {
				$status = $skin->result->get_error_message();
			} elseif ( $skin->get_errors()->has_errors() ) {
				$status = $skin->get_error_messages();
			} elseif ( is_null( $result ) ) {
				global $wp_filesystem;

				$status = __( 'Unable to connect to the filesystem. Please confirm your credentials.' );

				// Pass through the error from WP_Filesystem if one was raised.
				if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->has_errors() ) {
					$status = esc_html( $wp_filesystem->errors->get_error_message() );
				}
			}
		}
		// switch_theme($theme['slug'], $theme['slug']);

		echo esc_html( $status );
		exit;
	}

    /**
     * Validate Themes Access
     *
     * Handles the AJAX request to validate theme access by buffering the output
     * of the included themes.php template and returning it as a JSON response.
     *
     * @return void Outputs the JSON response and terminates execution.
     */
    function validate_themes_access() {
        // Start output buffering
        ob_start();

        // Include the required template
        include WP_PLUGIN_DIR . '/ignitiondeck/templates/admin/_idfMenu/themes.php';

        // Get the buffered content
        $response = ob_get_clean();

        // Send response back to the JavaScript
        wp_send_json_success($response);
    }

	/**
	 * Check configuration settings.
	 *
	 * Checks the configuration settings and updates the default values if necessary.
	 *
	 * @return void
	 */
	function check_config() {
		$default = array(
			'dashboard' => false,
			'checkout'  => false,
			'timezone'  => false,
			'permalink' => false,
			'receipt'   => false,
			'payment'   => false,
			'currency'  => false,
			'privacy'   => false,
			'terms'     => false,
			'sample'    => false,
		);
		$config  = empty( get_option( 'wiz-configure' ) ) ? array() : get_option( 'wiz-configure' );
		$return  = array_merge( $default, $config );
		update_option( 'wiz-configure', $return );
		echo wp_json_encode( $return );
		exit;
	}

	/**
	 * Create dashboard page.
	 *
	 * Checks if the dashboard page exists and creates it if not. Updates the
	 * configuration settings accordingly.
	 *
	 * @return void
	 */
	function create_dashboard() {
		$return = array(
			'success' => true,
			'message' => '',
			'result'  => '',
		);
		
		// Define the placeholder value
		$placeholder_value = 'idc_dashboard';
		
		// Set up the WP_Query arguments
		$args = array(
			'post_type' => 'any',
			's'         => $placeholder_value,
			'posts_per_page' => -1,
		);
		
		// Execute the query
		$query = new WP_Query( $args );
		
		if ( $query->have_posts() ) {
			$html = '<p>Dashboard page already exists.</p>';
			while ( $query->have_posts() ) {
				$query->the_post();
				$html .= '<p><b>#' . get_the_ID() . ' ' . get_the_title() . '</b> Click <a href="' . get_permalink() . '" target="_blank">here</a> to view.</p>';
			}
			wp_reset_postdata(); // Reset the global post data
			$return['message']   = 'Dashboard Page already exists.';
			$return['result']    = array(
				'heading' => 'Notice',
				'content' => $html,
			);
			$config              = get_option( 'wiz-configure' );
			$config['dashboard'] = true;
			update_option( 'wiz-configure', $config );
		} else {
			// Check user capabilities.
			if ( ! current_user_can( 'publish_posts' ) ) {
				wp_die( 'You don\'t have sufficient permissions to access this feature.' );
			}
			$my_post = array(
				'post_type'    => 'page',
				'post_title'   => wp_strip_all_tags( 'Dashboard' ),
				'post_content' => '[idc_dashboard]',
				'post_status'  => 'publish',
				'post_author'  => get_current_user_id(),
			);
			// Insert the post into the database
			$dashboard_id = wp_insert_post( $my_post );
			update_post_meta( $dashboard_id, '_wp_page_template', 'page-fullwidth.php' );
			$html                = '<p>Dashboard page created successfully.</p>';
			$html               .= '<p><b>#' . $dashboard_id . ' ' . $my_post['post_title'] . '</b> Click <a href="' . get_permalink( $dashboard_id ) . '" target="_blank">here</a> to view.</p>';
			$return['message']   = 'Dashboard Page created successfully.';
			$return['result']    = array(
				'heading' => 'Notice',
				'content' => $html,
			);
			$config              = get_option( 'wiz-configure' );
			$config['dashboard'] = true;
			update_option( 'wiz-configure', $config );
		}
		echo wp_json_encode( $return );
		exit;
	}

	/**
	 * Create checkout page.
	 *
	 * Checks if the checkout page exists and creates it if not. Updates the wiz-configure
	 * option accordingly.
	 *
	 * @return void
	 */
	function create_checkout() {
		$return = array(
			'success' => true,
			'message' => '',
			'result'  => '',
		);
		
		// Define the placeholder value
		$placeholder_value = 'idc_checkout';
		
		// Set up the WP_Query arguments
		$args = array(
			'post_type' => 'any',
			's'         => $placeholder_value,
			'posts_per_page' => -1,
		);
		
		// Execute the query
		$query = new WP_Query( $args );
		
		if ( $query->have_posts() ) {
			$html = '<p>Checkout page already exists.</p>';
			while ( $query->have_posts() ) {
				$query->the_post();
				$html .= '<p><b>#' . get_the_ID() . ' ' . get_the_title() . '</b> Click <a href="' . get_permalink() . '" target="_blank">here</a> to view.</p>';
			}
			wp_reset_postdata(); // Reset the global post data
			$return['message']  = 'Checkout Page already exists.';
			$return['result']   = array(
				'heading' => 'Notice',
				'content' => $html,
			);
			$config             = get_option( 'wiz-configure' );
			$config['checkout'] = true;
			update_option( 'wiz-configure', $config );
		} else {
			// Check user capabilities.
			if ( ! current_user_can( 'publish_posts' ) ) {
				wp_die( 'You don\'t have sufficient permissions to access this feature.' );
			}
			$my_post = array(
				'post_type'    => 'page',
				'post_title'   => wp_strip_all_tags( 'Checkout' ),
				'post_content' => '[idc_checkout]',
				'post_status'  => 'publish',
				'post_author'  => get_current_user_id(),
			);
			// Insert the post into the database
			$checkout_id = wp_insert_post( $my_post );
			update_post_meta( $checkout_id, '_wp_page_template', 'page-fullwidth.php' );
			$html                       = '<p>Checkout page created successfully.</p>';
			$html                      .= '<p><b>#' . $checkout_id . ' ' . $my_post['post_title'] . '</b> Click <a href="' . get_permalink( $checkout_id ) . '" target="_blank">here</a> to view.</p>';
			$purchase_default           = get_option( 'id_purchase_default' );
			$purchase_default['option'] = 'page_or_post';
			$purchase_default['value']  = $checkout_id;
			update_option( 'id_purchase_default', $purchase_default );
			$return['message']  = 'Checkout Page created successfully.';
			$return['result']   = array(
				'heading' => 'Notice',
				'content' => $html,
			);
			$config             = get_option( 'wiz-configure' );
			$config['checkout'] = true;
			update_option( 'wiz-configure', $config );
		}
		echo wp_json_encode( $return );
		exit;
	}

	/**
	 * Get timezone HTML.
	 *
	 * Generates HTML for selecting and setting the timezone.
	 *
	 * @return void
	 */
	function get_timezone_html() {
		$return = array(
			'success' => true,
			'message' => '',
			'result'  => '',
		);

		$html              = '<table class="form-table" role="presentation"><tbody><tr><th scope="row" style="text-align: right;width: 100px;"><label for="timezone_string">Timezone</label></th><td><select id="timezone_string" name="timezone_string" aria-describedby="timezone-description" style="width: 100%;">';
		$html             .= wp_timezone_choice( wp_timezone_string() );
		$html             .= '</select></td></tr></tbody></table>';
		$html             .= '<p class="submit"><input type="button" class="button button-primary" value="Set Timezone" onclick="wizSaveTimezone(this)"></p>';
		$return['message'] = 'Set Timezone';
		$return['result']  = array(
			'heading' => 'Set Timezone',
			'content' => $html,
		);
		echo wp_json_encode( $return );
		exit;
	}

	/**
	 * Save timezone.
	 *
	 * Updates the timezone setting and configuration options.
	 *
	 * @return void
	 */
	function save_timezone() {
		// Verify the nonce
		check_ajax_referer('idf-activate-plugins-nonce', 'security');

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You don\'t have sufficient permissions to manage options.' );
		}

		$return = array(
			'success' => true,
			'message' => '',
			'result'  => '',
		);
		update_option( 'timezone_string', $_POST['wiz_timezone'] );
		$return['message']  = 'Timezone updated successfully.';
		$return['result']   = array(
			'heading' => 'Notice',
			'content' => 'Timezone updated successfully.',
		);
		$config             = get_option( 'wiz-configure' );
		$config['timezone'] = true;
		update_option( 'wiz-configure', $config );
		echo wp_json_encode( $return );
		exit;
	}

	/**
	 * Set permalink structure.
	 *
	 * Updates the permalink structure and flushes rewrite rules.
	 *
	 * @return void
	 */
	function set_permalink() {
		$return = array(
			'success' => true,
			'message' => '',
			'result'  => '',
		);

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
		update_option( 'rewrite_rules', false );
		$wp_rewrite->flush_rules( true );

		$return['message']   = 'Permalink updated successfully.';
		$return['result']    = array(
			'heading' => 'Notice',
			'content' => 'Permalink updated successfully.',
		);
		$config              = get_option( 'wiz-configure' );
		$config['permalink'] = true;
		update_option( 'wiz-configure', $config );
		echo wp_json_encode( $return );
		exit;
	}

	/**
	 * Get receipt HTML.
	 *
	 * Retrieves the HTML for the receipt settings form if IDC is the commerce platform.
	 *
	 * @return void
	 */
	function get_receipt_html() {
		$idc_checked = ! get_option( 'idf_commerce_platform' ) || get_option( 'idf_commerce_platform' ) === 'idc' ? true : false;
		if ( $idc_checked ) {
			$return = array(
				'success' => true,
				'message' => '',
				'result'  => '',
			);

			$receipts = maybe_unserialize( get_option( 'md_receipt_settings' ) );
			$coname   = isset( $receipts['coname'] ) ? $receipts['coname'] : '';
			$coemail  = isset( $receipts['coemail'] ) ? $receipts['coemail'] : '';

			$html = '<form method="POST" action="" class="receipt-html">
			<div class="form-input">
				<label for="co-name">Company Name : </label>
				<input type="text" name="co-name" id="co-name" value="' . $coname . '" required>
				<div class="ign-receipt-settings-error-message" style="display: none">Company Name is required.</div>
			</div>
			<div class="form-input">
				<label for="co-email">Customer Service Email : </label>
				<input type="text" name="co-email" id="co-email" value="' . $coemail . '" required>
				<div class="ign-receipt-settings-error-message" style="display: none">Customer Service Email is required.</div>
			</div>
			<div class="submit">
				<input type="button" class="button-primary" value="Save" onclick="saveReceiptSettings(this)">
			</div>
		</form>';

			$return['message'] = 'Specify receipt settings html.';
			$return['result']  = array(
				'heading' => 'Receipt Settings',
				'content' => $html,
			);

			echo wp_json_encode( $return );
		}
		exit;
	}

	/**
	 * Save receipt settings.
	 *
	 * Saves the company name and customer service email for receipt settings and updates
	 * the wiz-configure option accordingly.
	 *
	 * @return void
	 */
	function save_receipt_settings() {
		// Verify the nonce
		check_ajax_referer('idf-activate-plugins-nonce', 'security');

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You don\'t have sufficient permissions to manage options.' );
		}

		$return = array(
			'success' => true,
			'message' => '',
			'result'  => '',
		);

		if ( ! empty( $_POST['co_name'] ) && ! empty( $_POST['co_name'] ) ) {
			$coname              = sanitize_text_field( $_POST['co_name'] );
			$coemail             = sanitize_text_field( $_POST['co_email'] );
			$receipts            = maybe_unserialize(get_option( 'md_receipt_settings' ));
			$receipts['coname']  = $coname;
			$receipts['coemail'] = $coemail;
			update_option( 'md_receipt_settings', serialize( $receipts ) );

			$return['message'] = 'Receipt settings updated successfully.';
			$return['result']  = array(
				'heading' => 'Notice',
				'content' => 'Receipt settings updated successfully.',
			);
			$config            = get_option( 'wiz-configure' );
			$config['receipt'] = true;
			update_option( 'wiz-configure', $config );
		} else {
			$return['success'] = false;
			$return['message'] = 'Please fill your receipt settings in order to enable sending the receipt emails';
			$return['result']  = array(
				'heading' => 'Notice',
				'content' => 'Please fill your receipt settings in order to enable sending the receipt emails',
			);
		}
		echo wp_json_encode( $return );
		exit;
	}

	/**
	 * Payment gateway settings.
	 *
	 * Updates the payment gateway settings and configuration options.
	 *
	 * @return void
	 */
	function payment_gateway() {
		$return            = array(
			'success' => true,
			'message' => '',
			'result'  => '',
		);
		$config            = get_option( 'wiz-configure' );
		$config['payment'] = true;
		update_option( 'wiz-configure', $config );
		echo wp_json_encode( $return );
		exit;
	}

	/**
	 * Get currency HTML.
	 *
	 * Retrieves the HTML for the global currency settings form.
	 *
	 * @return void
	 */
	function get_currency_html() {
		$return = array(
			'success' => true,
			'message' => '',
			'result'  => '',
		);

		$global_currency   = get_option( 'idc_global_currency' );
		$receipts          = get_option( 'md_receipt_settings' );
		$coname            = isset( $receipts['coname'] ) ? $receipts['coname'] : '';
		$coemail           = isset( $receipts['coemail'] ) ? $receipts['coemail'] : '';

		$site_url = site_url();
		// Append the correct path relative to the site URL
		$json_url = $site_url . '/wp-content/plugins/idcommerce/inc/currencies_global.json';
		$response = wp_remote_get( $json_url );		
		$currencies_json = wp_remote_retrieve_body( $response );		
		$global_currencies = json_decode( $currencies_json, true );		

		$options = '';
		foreach ( $global_currencies as $gk => $gv ) {
			$selected = $global_currency == $gv['Currency_Code'] ? 'selected="selected"' : '';
			$options .= '<option value="' . $gv['Currency_Code'] . '" data-symbol="' . $gv['Symbol'] . '" ' . $selected . '>' . $gv['Currency_Code'] . '</option>';
		}
		$html = '<form method="POST" action="" class="receipt-html">
			<div class="form-input">
				<label for="global-currency">' . __( 'Global Currency : ', 'memberdeck' ) . '</label>
				<select id="global-currency" name="global_currency" data-selected="' . $global_currency . '">' . $options . '</select>
			</div>
			<div class="submit">
				<input type="button" class="button-primary" value="' . __( 'Update', 'memberdeck' ) . '" onclick="saveGlobalCurrency()" />
			</div>
		</form>';

		$return['message'] = 'Global currency settings html.';
		$return['result']  = array(
			'heading' => 'Notice',
			'content' => $html,
		);

		echo wp_json_encode( $return );
		exit;
	}

	/**
	 * Save global currency.
	 *
	 * Saves the selected global currency and updates the wiz-configure option accordingly.
	 *
	 * @param array $_POST The array containing the global currency data.
	 * @return void
	 */
	function save_global_currency() {
		// Verify the nonce
		check_ajax_referer('idf-activate-plugins-nonce', 'security');
		
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You don\'t have sufficient permissions to manage options.' );
		}
		
		$return = array(
			'success' => true,
			'message' => '',
			'result'  => '',
		);

		$global_currency = sanitize_text_field( $_POST['global_currency'] );
		update_option( 'idc_global_currency', $global_currency );

		$return['message']  = 'Global currency updated successfully.';
		$return['result']   = array(
			'heading' => 'Notice',
			'content' => 'Global currency updated successfully.',
		);
		$config             = get_option( 'wiz-configure' );
		$config['currency'] = true;
		update_option( 'wiz-configure', $config );
		echo wp_json_encode( $return );
		exit;
	}
	/**
	 * Create privacy policy page.
	 *
	 * This function creates a new page for the privacy policy and sets the necessary
	 * content and settings. It also updates the general settings and configuration
	 * options accordingly.
	 *
	 * @return void
	 */
	function create_privacy_policy() {
		$return = array(
			'success' => true,
			'message' => '',
			'result'  => '',
		);

		$new_page                   = array();
		$new_page['post_title']     = 'Privacy Policy';
		$new_page['post_content']   = '<h2>Who we are</h2>
		<p><strong>Suggested text:</strong> Our website address is: ' . home_url() . '.</p>
		<h2>Comments</h2>
		<p><strong>Suggested text:</strong> When visitors leave comments on the site we collect the data shown in the comments form, and also the visitor’s IP address and browser user agent string to help spam detection.</p>
		<p>An anonymized string created from your email address (also called a hash) may be provided to the Gravatar service to see if you are using it. The Gravatar service privacy policy is available here: https://automattic.com/privacy/. After approval of your comment, your profile picture is visible to the public in the context of your comment.</p>
		<h2>Media</h2>
		<p><strong>Suggested text:</strong> If you upload images to the website, you should avoid uploading images with embedded location data (EXIF GPS) included. Visitors to the website can download and extract any location data from images on the website.</p>
		<h2>Cookies</h2>
		<p><strong>Suggested text:</strong> If you leave a comment on our site you may opt-in to saving your name, email address and website in cookies. These are for your convenience so that you do not have to fill in your details again when you leave another comment. These cookies will last for one year.</p>
		<p>If you visit our login page, we will set a temporary cookie to determine if your browser accepts cookies. This cookie contains no personal data and is discarded when you close your browser.</p>
		<p>When you log in, we will also set up several cookies to save your login information and your screen display choices. Login cookies last for two days, and screen options cookies last for a year. If you select "Remember Me", your login will persist for two weeks. If you log out of your account, the login cookies will be removed.</p>
		<p>If you edit or publish an article, an additional cookie will be saved in your browser. This cookie includes no personal data and simply indicates the post ID of the article you just edited. It expires after 1 day.</p>
		<h2>Embedded content from other websites</h2>
		<p><strong>Suggested text:</strong> Articles on this site may include embedded content (e.g. videos, images, articles, etc.). Embedded content from other websites behaves in the exact same way as if the visitor has visited the other website.</p>
		<p>These websites may collect data about you, use cookies, embed additional third-party tracking, and monitor your interaction with that embedded content, including tracking your interaction with the embedded content if you have an account and are logged in to that website.</p>
		<h2>Who we share your data with</h2>
		<p><strong>Suggested text:</strong> If you request a password reset, your IP address will be included in the reset email.</p>
		<h2>How long we retain your data</h2>
		<p><strong>Suggested text:</strong> If you leave a comment, the comment and its metadata are retained indefinitely. This is so we can recognize and approve any follow-up comments automatically instead of holding them in a moderation queue.</p>
		<p>For users that register on our website (if any), we also store the personal information they provide in their user profile. All users can see, edit, or delete their personal information at any time (except they cannot change their username). Website administrators can also see and edit that information.</p>
		<h2>What rights you have over your data</h2>
		<p><strong>Suggested text:</strong> If you have an account on this site, or have left comments, you can request to receive an exported file of the personal data we hold about you, including any data you have provided to us. You can also request that we erase any personal data we hold about you. This does not include any data we are obliged to keep for administrative, legal, or security purposes.</p>
		<h2>Where your data is sent</h2>
		<p><strong>Suggested text:</strong> Visitor comments may be checked through an automated spam detection service.</p>';
		$new_page['post_type']      = 'page';
		$new_page['post_status']    = 'publish';
		$new_page['comment_status'] = 'closed';
		$new_page['ping_status']    = 'closed';

		$posts = get_posts(
			array(
				'post_type'              => 'page',
				'title'                  => $new_page['post_title'],
				'post_status'            => 'all',
				'numberposts'            => 1,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'orderby'                => 'post_date ID',
				'order'                  => 'ASC',
			)
		);

		$general = get_option( 'md_receipt_settings' );
		if ( ! is_array( $general ) ) {
			$general = array();
		}
		if ( ! empty( $posts ) ) {
			$found                   = $posts[0];
			$general['show_terms']   = 1;
			$general['privacy_page'] = $found->ID;
			// wp_publish_post( $found->ID );
			$return['message'] = $new_page['post_title'] . ' page already exist.';
			$html              = $new_page['post_title'] . ' page already exists. Page status is <b>' . ucfirst( $found->post_status ) . '</b>. Click here to <a href="' . admin_url( 'post.php?action=edit&post=' . $found->ID ) . '" target="_blank">Edit<a>.';
			$return['result']  = array(
				'heading' => 'Notice',
				'content' => $html,
			);
		} else {
			// Check user capabilities.
			if ( ! current_user_can( 'publish_posts' ) ) {
				wp_die( 'You don\'t have sufficient permissions to access this feature.' );
			}
			$post_id = wp_insert_post( $new_page );
			update_post_meta( $post_id, '_wp_page_template', 'page-fullwidth.php' );
			if ( ! is_wp_error( $post_id ) ) {
				$general['show_terms']   = 1;
				$general['privacy_page'] = $post_id;
				$return['message']       = $new_page['post_title'] . ' page created and published successfully.';
				$html                    = $new_page['post_title'] . ' page created and published successfully. Click here to <a href="' . admin_url( 'post.php?action=edit&post=' . $post_id ) . '" target="_blank">Edit<a>.';
				$return['result']        = array(
					'heading' => 'Notice',
					'content' => $html,
				);
			} else {
				$return['message'] = '<b>' . $new_page['post_title'] . '</b> Page Error:' . $post_id->get_error_message();
				$html              = '<b>' . $new_page['post_title'] . '</b> Page Error:' . $post_id->get_error_message();
				$return['result']  = array(
					'heading' => 'Error',
					'content' => $html,
				);
			}
		}
		update_option( 'md_receipt_settings', $general );
		$config            = get_option( 'wiz-configure' );
		$config['privacy'] = true;
		update_option( 'wiz-configure', $config );
		echo wp_json_encode( $return );
		exit;
	}

	/**
	 * Create Terms of Use page.
	 *
	 * This function creates a new page for the Terms of Use with the specified content and settings.
	 *
	 * @return void
	 */
	function create_terms_of_use() {
		$return = array(
			'success' => true,
			'message' => '',
			'result'  => '',
		);

		$new_page                   = array();
		$new_page['post_title']     = 'Terms of Use';
		$new_page['post_content']   = '<p>A <strong>Terms and Conditions</strong> agreement is the agreement that includes the terms, the rules and the guidelines of acceptable behavior and other useful sections to which users must agree in order to use your platform.</p>
		<p>Here are a few examples:</p>
		<p><ol>
		<li>An <strong>Intellectual Property disclosure</strong> will inform users that the contents, logo and other visual media you created is your property and is protected by copyright laws.</li>
		<li>A <strong>Termination clause</strong> will inform that users\' accounts on your website or users\' access to your website can be terminated in case of abuses or at your sole discretion.</li>
		<li>A <strong>Governing Law statement</strong> will inform users which laws govern the agreement. This should the country in which your company is headquartered or the country from which you operate your website.</li>
		<li>A <strong>Links To Other Web Sites clause</strong> will inform users that you are not responsible for any third party websites that you link to. This kind of clause will generally inform users that they are responsible for reading and agreeing (or disagreeing) with the Terms and Conditions or Privacy Policies of these third parties.</li>
		<li>As your website allows users to create content and make that content public to other users, a <strong>Content clause</strong> will inform users that they own the rights to the content they have created. The Content clause usually mentions that users must give you (the website owner) a license so that you can share this content on your website/mobile app and to make it available to other users.</li>
		<li>Because the content created by users is public to other users, a <strong>Copyright Infringement section</strong> is helpful to inform users and copyright authors that, if any content is found to be a copyright infringement, you will respond to any DMCA takedown notices received and you will take down the content.</li>
		<li>A <strong>Limit What Users Can Do clause</strong> can inform users that by agreeing to use your service, they\'re also agreeing to not do certain things. This can be part of a very long and thorough list in your Terms and Conditions agreements so as to encompass the most amount of negative uses.</li></ol></p>';
		$new_page['post_type']      = 'page';
		$new_page['post_status']    = 'publish';
		$new_page['comment_status'] = 'closed';
		$new_page['ping_status']    = 'closed';

		$posts   = get_posts(
			array(
				'post_type'              => 'page',
				'title'                  => $new_page['post_title'],
				'post_status'            => 'all',
				'numberposts'            => 1,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'orderby'                => 'post_date ID',
				'order'                  => 'ASC',
			)
		);
		$general = get_option( 'md_receipt_settings' );
		if ( ! is_array( $general ) ) {
			$general = array();
		}
		if ( ! empty( $posts ) ) {
			$found                 = $posts[0];
			$general['show_terms'] = 1;
			$general['terms_page'] = $found->ID;
			$return['message']     = $new_page['post_title'] . ' page already exist.';
			$html                  = $new_page['post_title'] . ' page already exists. Page status is <b>' . ucfirst( $found->post_status ) . '</b>. Click here to <a href="' . admin_url( 'post.php?action=edit&post=' . $found->ID ) . '" target="_blank">Edit<a>.';
			$return['result']      = array(
				'heading' => 'Notice',
				'content' => $html,
			);
		} else {
			// Check user capabilities.
			if ( ! current_user_can( 'publish_posts' ) ) {
				wp_die( 'You don\'t have sufficient permissions to access this feature.' );
			}
			$post_id = wp_insert_post( $new_page );
			update_post_meta( $post_id, '_wp_page_template', 'page-fullwidth.php' );
			if ( ! is_wp_error( $post_id ) ) {
				$general['show_terms'] = 1;
				$general['terms_page'] = $post_id;
				$return['message']     = $new_page['post_title'] . ' page created and published successfully.';
				$html                  = $new_page['post_title'] . ' page created and published successfully. Click here to <a href="' . admin_url( 'post.php?action=edit&post=' . $post_id ) . '" target="_blank">Edit<a>.';
				$return['result']      = array(
					'heading' => 'Notice',
					'content' => $html,
				);
			} else {
				$return['message'] = '<b>' . $new_page['post_title'] . '</b> Page Error:' . $post_id->get_error_message();
				$html              = '<b>' . $new_page['post_title'] . '</b> Page Error:' . $post_id->get_error_message();
				$return['result']  = array(
					'heading' => 'Error',
					'content' => $html,
				);
			}
		}
		update_option( 'md_receipt_settings', $general );
		$config          = get_option( 'wiz-configure' );
		$config['terms'] = true;
		update_option( 'wiz-configure', $config );
		echo wp_json_encode( $return );
		exit;
	}
	/**
	 * Create a sample project.
	 *
	 * This function creates a sample ID project with predefined settings and levels.
	 *
	 * @return void
	 */
	function create_sample_project() {
		// Check user capabilities.
		if ( ! current_user_can( 'publish_posts' ) ) {
			wp_die( 'You don\'t have sufficient permissions to access this feature.' );
		}

		// Create Sample ID Project
		$sample_project['post_title']  = 'Sample Project';
		$sample_project['post_type']   = 'ignition_product';
		$sample_project['post_status'] = 'publish';

		$project_post_id = wp_insert_post( $sample_project );

		$project_post_meta['ign_project_id']               = '';
		$project_post_meta['ign_end_type']                 = 'closed';
		$project_post_meta['ign_fund_goal']                = '1000';
		$project_post_meta['ign_start_date']               = strtotime( 'now' );
		$project_post_meta['ign_fund_end']                 = strtotime( '+30 days' );
		$project_post_meta['ign_project_description']      = 'A short description to promote your project. Keep it short and simple.';
		$project_post_meta['ign_project_long_description'] = 'The details of your project—the who, what, where, when, why, and how—goes here. Extra media such as images, videos, links to other content can also be added.';
		$project_post_meta['ign_product_video']            = '<iframe title="sample-project" src="https://player.vimeo.com/video/93519782?h=c3af1c741c" width="100%" height="360" frameborder="0" allowfullscreen></iframe>';

		// Upload image
		$image_url     = plugins_url( '/ignitiondeck/images/demo-project.png' );
		$attachment_id = $this->wiz_upload_file_by_url( $image_url );
		set_post_thumbnail( $project_post_id, $attachment_id );

		// Project Category
		wp_insert_term( 'Demo', 'category' );
		wp_set_object_terms( $project_post_id, 'demo', 'category', true );

		// Levels
		$project_post_meta['ign_product_title']             = 'Pay What You Want';
		$project_post_meta['ign_product_price']             = 1;
		$project_post_meta['ign_product_short_description'] = 'Example of donation/PWYW level';
		$project_post_meta['ign_product_details']           = 'Long descriptions are used with some themes';

		$project_post_meta['ign_product_level_count'] = 3;

		$project_post_meta['ign_product_level_2_title']      = 'Standard Reward Level';
		$project_post_meta['ign_product_level_2_limit']      = '';
		$project_post_meta['ign_product_level_2_order']      = '';
		$project_post_meta['ign_product_level_2_price']      = 15;
		$project_post_meta['ign_product_level_2_short_desc'] = 'Supporters will receive ebook';
		$project_post_meta['ign_product_level_2_desc']       = 'Long descriptions are used with some themes';

		$project_post_meta['ign_product_level_3_title']      = 'Limited Reward Level';
		$project_post_meta['ign_product_level_3_limit']      = 25;
		$project_post_meta['ign_product_level_3_order']      = '';
		$project_post_meta['ign_product_level_3_price']      = 50;
		$project_post_meta['ign_product_level_3_short_desc'] = '25 supporters will receive signed copy of book';
		$project_post_meta['ign_product_level_3_desc']       = 'Long descriptions are used with some themes';
		// End Levels

		// Project Settings Meta
		$project_post_meta['ign_option_project_url']  = 'current_page';
		$project_post_meta['ign_option_purchase_url'] = 'default';
		$project_post_meta['ign_project_parent']      = 0;
		$project_post_meta['ign_fund_raised']         = 0;
		$project_post_meta['ign_percent_raised']      = 0;

		foreach ( $project_post_meta as $pk => $pv ) {
			update_post_meta( $project_post_id, $pk, $pv );
		}

		// Create Sample IDC Product
		$idc_product['product_name']      = 'Demo';
		$idc_product['ign_product_title'] = $idc_product['product_name'] . ': ' . $project_post_meta['ign_product_title'];
		$idc_product['ign_product_limit'] = '';
		$idc_product['product_details']   = $project_post_meta['ign_product_details'];
		$idc_product['product_price']     = $project_post_meta['ign_product_price'];
		$idc_product['goal']              = $project_post_meta['ign_fund_goal'];
		$project_id                       = ID_Project::insert_project( $idc_product );

		// Link with project
		update_post_meta( $project_post_id, 'ign_project_id', $project_id );

		// Create levels
		$user_id = get_current_user_id();
		// Level 1
		$level_1                  = new ID_Member_Level();
		$args                     = array();
		$args['product_status']   = 'active';
		$args['product_type']     = 'purchase';
		$args['level_name']       = $idc_product['product_name'] . ': ' . $project_post_meta['ign_product_title'];
		$args['level_price']      = $project_post_meta['ign_product_price'];
		$args['credit_value']     = 0;
		$args['txn_type']         = 'capture';
		$args['level_type']       = 'lifetime';
		$args['recurring_type']   = 'none';
		$args['trial_period']     = 0;
		$args['trial_length']     = 0;
		$args['trial_type']       = '';
		$args['limit_term']       = 0;
		$args['term_length']      = 0;
		$args['plan']             = '';
		$args['license_count']    = 0;
		$args['enable_renewals']  = 0;
		$args['renewal_price']    = '';
		$args['enable_multiples'] = 1;
		$args['combined_product'] = 0;
		$args['custom_message']   = 0;
		$new_level                = $level_1->add_level( $args );
		$level_id                 = $new_level['level_id'];
		// Adding level 1 associations
		idc_id_add_level_associations( 0, $level_id, $project_id, $user_id );
		// Level 2
		$level_2             = new ID_Member_Level();
		$args['level_name']  = $idc_product['product_name'] . ': ' . $project_post_meta['ign_product_level_2_title'];
		$args['level_price'] = $project_post_meta['ign_product_level_2_price'];
		$new_level           = $level_2->add_level( $args );
		$level_id            = $new_level['level_id'];
		// Adding level 2 associations
		idc_id_add_level_associations( 1, $level_id, $project_id, $user_id );
		// Level 3
		$level_3             = new ID_Member_Level();
		$args['level_name']  = $idc_product['product_name'] . ': ' . $project_post_meta['ign_product_level_3_title'];
		$args['level_price'] = $project_post_meta['ign_product_level_3_price'];
		$new_level           = $level_3->add_level( $args );
		$level_id            = $new_level['level_id'];
		// Adding level 3 associations
		idc_id_add_level_associations( 2, $level_id, $project_id, $user_id );
		$config           = get_option( 'wiz-configure' );
		$config['sample'] = $project_post_id;
		update_option( 'wiz-configure', $config );
		$project_permalink = get_permalink($project_post_id); // Get the project permalink
		$admin_edit_url = admin_url("post.php?post=$project_post_id&action=edit"); // Get the project admin edit URL
		$html = '<p><b>Sample Project</b> has been created successfully. You may <a href="' . esc_url($admin_edit_url) . '" target="_blank">Edit</a> or <a href="' . esc_url($project_permalink) . '" target="_blank">View</a> it now.</p>';
		$return = array(
			'success' => true,
			'message' => 'Sample Project has been created successfully.',
			'result'  => array(
				'heading' => 'Notice',
				'content' => $html,
			),
		);
		echo wp_json_encode( $return );
		exit;
	}

	/**
	 * Upload a file by URL.
	 *
	 * This function downloads a file from a given URL and adds it to the WordPress media library.
	 *
	 * @param string $image_url The URL of the image to download and upload.
	 * @return int|WP_Error The attachment ID on success, or a WP_Error object on failure.
	 */
	function wiz_upload_file_by_url( $image_url ) {
		add_filter( 'https_ssl_verify', '__return_false' );
		// it allows us to use download_url() and wp_handle_sideload() functions
		require_once ABSPATH . 'wp-admin/includes/file.php';
		// download to temp dir
		$temp_file = download_url( $image_url );
		if ( is_wp_error( $temp_file ) ) {
			return $temp_file->get_error_message() . __LINE__;
		}
		// move the temp file into the uploads directory
		$file     = array(
			'name'     => basename( $image_url ),
			'type'     => mime_content_type( $temp_file ),
			'tmp_name' => $temp_file,
			'size'     => filesize( $temp_file ),
		);
		$sideload = wp_handle_sideload( $file, array( 'test_form' => false ) );
		if ( ! empty( $sideload['error'] ) ) {
			// you may return error message if you want
			return $sideload->get_error_message() . __LINE__;
		}
		// it is time to add our uploaded image into WordPress media library
		$attachment_id = wp_insert_attachment(
			array(
				'guid'           => $sideload['url'],
				'post_mime_type' => $sideload['type'],
				'post_title'     => basename( $sideload['file'] ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$sideload['file']
		);
		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
			return $attachment_id->get_error_message() . __LINE__;
		}
		// update metadata, regenerate image sizes
		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata(
			$attachment_id,
			wp_generate_attachment_metadata( $attachment_id, $sideload['file'] )
		);
		return $attachment_id;
	}

	/**
	 * Delete a sample project and its associated data.
	 *
	 * This function deletes a sample ID project and its associated data, including
	 * project settings, levels, featured image, meta, categories, assignments, orders,
	 * and the associated IGN Product.
	 *
	 * @return void
	 */
	function delete_sample_project() {
		global $wpdb;
		
		// Check user capabilities.
		if ( ! current_user_can( 'delete_posts' ) ) {
			wp_die( 'You don\'t have sufficient permissions to access this feature.' );
		}
		$config          = get_option( 'wiz-configure' );
		$project_post_id = $config['sample'];
		$post            = get_post( $project_post_id );
		// Get Product ID
		$product_id = get_post_meta( $project_post_id, 'ign_project_id', true );
		// Delete featured image
		$attachment_id = get_post_thumbnail_id( $project_post_id );
		wp_delete_attachment( $attachment_id, true );
		delete_post_thumbnail( $project_post_id );
		// Delete its all meta
		$post_metas = get_post_meta( $project_post_id );
		foreach ( $post_metas as $key => $val ) {
			delete_post_meta( $project_post_id, $key );
		}
		// Delete sample project category
		$term    = 'category';
		$value   = 'demo';
		$theterm = get_term_by( 'slug', $value, $term );
		wp_delete_term( $theterm->term_id, $term );
		// Delete Sample Project
		wp_delete_post( $project_post_id, true );

		// Delete all assignments
		$mdid_assignments = get_assignments_by_project( $product_id );
		foreach ( $mdid_assignments as $ma ) {
			$level = new ID_Member_Level( $ma->level_id );
			$level->delete_level();
			$level_obj = new ID_Member_Level();
			$level_obj->delete_level_meta_all( $ma->level_id );
			$level_obj->delete_user_level( $ma->level_id, $post->post_author );

			/* Delete orders */		
			// Prepare and execute the query to select the transaction ID
			$res = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT transaction_id FROM {$wpdb->prefix}memberdeck_orders WHERE level_id = %d AND user_id = %d",
					$ma->level_id,
					$post->post_author
				),
				ARRAY_A
			);
		
			if (!empty($res)) {
				// Prepare and execute the query to delete from ign_pay_info
				$wpdb->query(
					$wpdb->prepare(
						"DELETE FROM {$wpdb->prefix}ign_pay_info WHERE transaction_id = %s",
						$res[0]['transaction_id']
					)
				);
		
				// Prepare and execute the query to delete from memberdeck_orders
				$wpdb->query(
					$wpdb->prepare(
						"DELETE FROM {$wpdb->prefix}memberdeck_orders WHERE level_id = %d AND user_id = %d",
						$ma->level_id,
						$post->post_author
					)
				);
			}
		}
		// Delete IGN Product
		$ign_project     = new ID_Project( $product_id );
		$the_ign_project = $ign_project->the_project();

		// Prepare and execute the query to delete from ign_products
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}ign_products WHERE id = %d",
				$product_id
			)
		);
		$ign_project->clear_project_settings();

		// Update status
		unset( $config['sample'] );
		update_option( 'wiz-configure', $config );

		$html   = '<p><b>Sample Project</b> and all its data has been deleted successfully.</p>';
		$return = array(
			'success' => true,
			'message' => 'Sample Project has been deleted successfully.',
			'result'  => array(
				'heading' => 'Notice',
				'content' => $html,
			),
		);
		echo wp_json_encode( $return );
		exit;
	}
}
new IDF_wizard();
