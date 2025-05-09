<?php
/**
 * Create Projects post type
 */
add_action( 'init', 'ign_create_post_type' );
function ign_create_post_type() {
	$slug = apply_filters( 'idcf_archive_slug', __( 'projects', 'ignitiondeck' ) );
	$vars = array(
		'labels'              => array(
			'name'               => __( 'Projects', 'ignitiondeck' ),
			'singular_name'      => __( 'Project', 'ignitiondeck' ),
			'add_new'            => __( 'Add New Project', 'ignitiondeck' ),
			'add_new_item'       => __( 'Add New Project', 'ignitiondeck' ),
			'edit'               => __( 'Edit Project', 'ignitiondeck' ),
			'edit_item'          => __( 'Edit Project', 'ignitiondeck' ),
			'new_item'           => __( 'New Project', 'ignitiondeck' ),
			'view'               => __( 'View Project', 'ignitiondeck' ),
			'view_item'          => __( 'View Project', 'ignitiondeck' ),
			'search_items'       => __( 'Search Projects', 'ignitiondeck' ),
			'not_found'          => __( 'No Projects Found', 'ignitiondeck' ),
			'not_found_in_trash' => __( 'No Projects Found in Trash', 'ignitiondeck' ),
		),
		'public'              => true,
		'show_in_nav_menus'   => true,
		'show_ui'             => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
		'hierarchical'        => apply_filters( 'idcf_hierarchical', false ),
		'menu_position'       => 5,
		'capability_type'     => 'post',
		'menu_icon'           => 'dashicons-ignitiondeck',
		'query_var'           => true,
		'show_in_rest'        => true,
		'rewrite'             => apply_filters(
			'id_register_project_post_rewrite',
			array(
				'slug'       => $slug,
				'with_front' => true,
			)
		),
		'has_archive'         => apply_filters( 'id_register_project_post_has_archive', $slug ),
		'supports'            => array( 'title', 'editor', 'comments', 'author', 'thumbnail' ),
		//'taxonomies'          => array( 'category', 'post_tag', 'project_category' ),
	);
	register_post_type( 'ignition_product', apply_filters( 'idcf_ign_product_args', $vars ) );
}

add_action( 'init', 'ign_create_taxonomy' );

function ign_create_taxonomy() {
	$labels = array(
		'name'          => __( 'Project Categories', 'ignitiondeck' ),
		'singular_name' => __( 'Project Category', 'ignitiondeck' ),
	);
	$args   = array(
		'hierarchical'      => false,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'project-category' ),
	);
	$args   = apply_filters( 'project_category_args', $args );
	register_taxonomy( 'project_category', 'ignition_product', $args );

	$pt_labels = array(
		'name'          => __( 'Project Types', 'ignitiondeck' ),
		'singular_name' => __( 'Project Type', 'ignitiondeck' ),
	);
	$pt_args   = array(
		'hierarchical'      => false,
		'labels'            => $pt_labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'project-type' ),
	);
	$pt_args   = apply_filters( 'project_type_args', $pt_args );
	register_taxonomy( 'project_type', 'ignition_product', $pt_args );
}

add_image_size( 'id_project_thumb', 300, 175, true );
add_image_size( 'id_embed_image', 190, 127, true );
add_image_size( 'id_checkout_image', 500, 226, true );
add_image_size( 'id_profile_image', 370, 208, true );
/**
 * Enques Admin and Front End JS/CSS
 */

function enqueue_admin_js() {
	wp_register_script( 'ignitiondeck-admin', plugins_url( '/js/ignitiondeck-admin-min.js', __FILE__ ) );
	wp_enqueue_script( 'jQuery' );
	wp_enqueue_script( 'ignitiondeck-admin' );
	if ( is_multisite() && is_id_network_activated() ) {
		$id_ajaxurl = network_home_url( '/' ) . 'wp-admin/admin-ajax.php';
	} else {
		$id_ajaxurl = site_url( '/' ) . 'wp-admin/admin-ajax.php';
	}
	//wp_localize_script( 'ignitiondeck-admin', 'id_homeurl', home_url() );
	//wp_localize_script( 'ignitiondeck-admin', 'id_ajaxurl', $id_ajaxurl );
	wp_add_inline_script( 'ignitiondeck-admin', 'var id_homeurl = "'. home_url() . '";' );
	wp_add_inline_script( 'ignitiondeck-admin', 'var id_ajaxurl = "'. $id_ajaxurl . '";' );
	global $post;
	if ( isset( $post->post_type ) && $post->post_type == 'ignition_product' ) {
		wp_register_script( 'ignitiondeck', plugins_url( '/js/ignitiondeck-min.js', __FILE__ ) );
		wp_enqueue_script( 'ignitiondeck' );
		//wp_localize_script( 'ignitiondeck', 'id_ajaxurl', $id_ajaxurl );
		wp_add_inline_script( 'ignitiondeck', 'var id_ajaxurl = "'. $id_ajaxurl . '";' );
		wp_enqueue_script( 'idf' );
		wp_dequeue_script( 'autosave' );
	}
}

add_action( 'admin_enqueue_scripts', 'enqueue_admin_js' );

function enqueue_admin_css() {
	wp_register_style( 'admin-css', plugins_url( '/ignitiondeck-admin-min.css', __FILE__ ) );
	wp_enqueue_style( 'admin-css' );
}

add_action( 'admin_enqueue_scripts', 'enqueue_admin_css' );

add_action( 'init', 'enqueue_styles_scripts_for_post_type' );

function enqueue_styles_scripts_for_post_type() {
	global $post;
	if ( isset( $post->post_type ) && $post->post_type == 'ignition_product' ) {
		add_action( 'admin_enqueue_scripts', 'enqueue_admin_css' );
		add_action( 'admin_enqueue_scripts', 'enqueue_admin_js' );
	}
}

// Change the columns for the edit CPT screen
function ign_change_columns( $cols ) {
	$cols = array(
		'cb'       => '<input type="checkbox" />',
		'title'    => __( 'Project', 'ignitiondeck' ),
		'author'   => __( 'Author', 'ignitiondeck' ),
		'goal'     => __( 'Funding Goal', 'ignitiondeck' ),
		'raised'   => __( 'Pledged', 'ignitiondeck' ),
		'enddate'  => __( 'End Date', 'ignitiondeck' ),
		'daysleft' => __( 'Days Remaining', 'ignitiondeck' ),
	);
	return apply_filters( 'id_project_columns', $cols );
}
add_filter( 'manage_ignition_product_posts_columns', 'ign_change_columns' );
add_action( 'manage_posts_custom_column', 'manage_ign_product_columns', 10, 2 );

function manage_ign_product_columns( $column_name, $id ) {
	global $post;
	$post_id    = $post->ID;
	$project_id = get_post_meta( $id, 'ign_project_id', true );
	$project    = new ID_Project( $project_id );
	$cCode      = $project->currency_code();
	switch ( $column_name ) {
		// display goal amount with currency formatting
		case 'author':
			echo ( ! empty( $post->post_author ) ? $post->post_author : __( 'None', 'ignitiondeck' ) );
			break;

		case 'goal':
			$goal = apply_filters( 'id_project_goal', $project->the_goal(), $post_id );
			echo $goal;
			break;

		case 'raised':
			if ( isset( $project_id ) ) {
				$project = new ID_Project( $project_id );
				$post_id = $project->get_project_postid();
				$raised  = $project->get_project_raised();
				echo $raised;
			}
			break;
		// display end date
		case 'enddate':
			echo $project->end_date();
			break;

		// calculate days remaining
		case 'daysleft':
			echo $project->days_left();
			break;

		// return standard post columns
		default:
			break;
	} // end switch
}


// Make these columns sortable
function ign_sortable_columns() {
	$sortable_columns = array(
		'title'    => 'title',
		'author'   => 'author',
		'goal'     => 'goal',
		'raised'   => 'raised',
		'enddate'  => 'enddate',
		'daysleft' => 'daysleft',
	);
	return apply_filters( 'id_sortable_project_columns', $sortable_columns );
}

add_filter( 'manage_edit-ignition_product_sortable_columns', 'ign_sortable_columns' );

// This is the NEW Order Details Menu but appears to be unused

add_filter( 'manage-order_columns', 'order_detail_columns' );

function order_detail_columns( $columns ) {
	$columns = array(
		'name'    => '<th scope="col" id="name" class="manage-column sortable desc"><b>' . __( 'Name', 'ignitiondeck' ) . '</b></th>',
		'project' => '<th scope="col" id="status" class="manage-column sortable desc"><b>' . __( 'Project Name', 'ignitiondeck' ) . '</b></th>',
		'level'   => '<th scope="col" id="action" class="manage-column sortable desc"><b>' . __( 'Level', 'ignitiondeck' ) . '</b></th>',
		'pledged' => '<th scope="col" id="action" class="manage-column sortable desc"><b>' . __( 'Pledged', 'ignitiondeck' ) . '</b></th>',
		'date'    => '<th scope="col" id="action" class="manage-column sortable desc"><b>' . __( 'Date', 'ignitiondeck' ) . '</b></th>',
	);
	return apply_filters( 'id_order_columns', $columns );
}

// This is to make order details menu sortable but seems to be unused

add_filter( 'edit-order_columns', 'order_details_sortable_columns' );

function order_details_sortable_columns() {
	$columns = array(
		'name'    => 'name',
		'project' => 'project',
		'level'   => 'level',
		'pledged' => 'pledged',
		'date'    => 'date',
	);
	return apply_filters( 'id_sortable_order_columns', $columns );
}

// change post title box text
function change_ign_product_title_text( $title ) {
	$screen = get_current_screen();
	if ( 'ignition_product' == $screen->post_type ) {
		$title = __( 'Enter Project Name Here', 'ignitiondeck' );
	}
	return $title;
}
add_filter( 'enter_title_here', 'change_ign_product_title_text' );

//-------------------------Admin Side Add IgnitionDeck STARTS------------------------------

add_action( 'admin_menu', 'id_admin_menus', 12 );
function id_admin_menus() {
	$platform = ( function_exists( 'idf_platform' ) ? idf_platform() : 'legacy' );
	if ( current_user_can( 'manage_options' ) ) {
		//$project_settings = add_menu_page( __('Project Settings', 'ignitiondeck'), __('IDCF', 'ignitiondeck'), 'manage_options', 'ignitiondeck', 'product_settings', 'dashicons-ignitiondeck');
		$project_settings = add_submenu_page( 'idf', __( 'Project Settings', 'ignitiondeck' ), __( 'Project Settings', 'ignitiondeck' ), 'manage_options', 'ignitiondeck', 'product_settings' );
		if ( is_id_licensed() ) {
			$deck_settings = add_submenu_page( 'idf', __( 'Deck Builder', 'ignitiondeck' ), __( 'Deck Builder', 'ignitiondeck' ), 'manage_options', 'deck-builder', 'deck_builder' );
		}
		$order_menu   = add_submenu_page( 'idf', __( 'IDCF Orders', 'ignitiondeck' ), __( 'IDCF Orders', 'ignitiondeck' ), 'manage_options', 'order_details', 'order_details' );
		$edit_order   = add_submenu_page( $order_menu, __( 'Edit Order', 'ignitiondeck' ), '', 'manage_options', 'edit_order', 'edit_order' );
		$view_order   = add_submenu_page( $order_menu, __( 'View order', 'ignitiondeck' ), '', 'manage_options', 'view_order', 'view_order' );
		$delete_order = add_submenu_page( $order_menu, __( 'Delete Order', 'ignitiondeck' ), '', 'manage_options', 'delete_order', 'delete_order' );
		$add_order    = add_submenu_page( $order_menu, __( 'Add Order', 'ignitiondeck' ), '', 'manage_options', 'add_order', 'add_order' );
		do_action( 'id_submenu' );
		if ( function_exists( 'id_font_awesome' ) ) {
			add_action( 'admin_print_styles-' . $project_settings, 'id_font_awesome' );
		} else {
			add_action( 'admin_print_styles-' . $project_settings, 'idf_font_awesome' );
		}
		$menus = array( $project_settings, $order_menu, $edit_order, $view_order, $delete_order, $add_order );
		if ( is_id_licensed() ) {
			$menus[] = $deck_settings;
		}
		$menus = apply_filters( 'id_menu_enqueue', $menus );
		if ( is_array( $menus ) ) {
			foreach ( $menus as $menu ) {
				add_action( 'admin_print_styles-' . $menu, 'enqueue_admin_css' );
				add_action( 'admin_print_styles-' . $menu, 'enqueue_admin_js' );
			}
		}
	}
}

function deck_builder() {
	global $wpdb;
	// deck builder settings
	if ( isset( $_POST['deck_submit'] ) ) {
		$attrs = array();
		foreach ( $_POST as $k => $v ) {
			if ( $k !== 'deck_submit' && $k !== 'deck_select' ) {
				if ( $k == 'deck_title' ) {
					$attrs[ $k ] = esc_attr( $v );
				} else {
					$attrs[ $k ] = absint( $v );
				}
			}
		}
		if ( $_POST['deck_select'] !== 'Create New' ) {
			// update saved deck
			$deck_id = absint( $_POST['deck_select'] );
			Deck::update_deck( $attrs, $deck_id );
		} else {
			// new deck, insert
			$new = Deck::create_deck( $attrs );
		}
	} elseif ( isset( $_POST['deck_delete'] ) ) {
		$deck_id = absint( $_POST['deck_select'] );
		Deck::delete_deck( $deck_id );
	}
	// skin selector
	$skins = $wpdb->get_row( 'SELECT theme_choices FROM ' . $wpdb->prefix . 'ign_settings WHERE id="1"' );
	if ( isset( $skins ) && $skins->theme_choices !== null) {
		$skins = unserialize( $skins->theme_choices );
	} else {
		$skins = array();
	}

	$deleted_skin_list = deleted_skin_list( $skins );

	if ( isset( $_POST['add-skin'] ) ) {
		$skin = str_replace( '.css', '', $_POST['skin-name'] );
		if ( $skin !== '' ) {
			$skins[]           = $skin;
			$deleted_skin_list = deleted_skin_list( $skins );
			$skins             = serialize( $skins );
			$sql               = $wpdb->prepare( 'UPDATE ' . $wpdb->prefix . 'ign_settings SET theme_choices=%s WHERE id="1"', $skins );
			$res               = $wpdb->query( $sql );
		}
		idf_flush_object( 'idcf-getSettings' );
	}

	if ( isset( $_POST['delete-skin'] ) ) {
		$deleted = $_POST['deleted-skin'];
		if (is_array($skins)) {
			foreach ( $skins as $key => $val ) {
				if ( strtolower( str_replace( ' ', '', $val ) ) == strtolower( str_replace( ' ', '', $deleted ) ) ) {
					unset( $skins[ $key ] );
				}
			}
			$deleted_skin_list = deleted_skin_list( $skins );
			$skins             = serialize( $skins );
			$sql               = $wpdb->prepare( 'UPDATE ' . $wpdb->prefix . 'ign_settings SET theme_choices=%s WHERE id="1"', $skins );
			$res               = $wpdb->query( $sql );
			idf_flush_object( 'idcf-getSettings' );
		}
	}

	if (isset( $_POST['btnIgnSettings'] ) && $_POST['btnIgnSettings'] == __( 'Save', 'ignitiondeck' )) {
		// Check if a row exists in the "ign_settings" table
		$existing_row = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "ign_settings WHERE id = 1");

		if ($existing_row) {
			// Row exists, perform an update
			$sql_update = '	UPDATE ' . $wpdb->prefix . "ign_settings SET
                        theme_value = '" . $_POST['theme_value'] . "',
                        id_widget_logo_on = '" . (isset($_POST['id_widget_logo_on']) ? absint($_POST['id_widget_logo_on']) : 0) . "',
                        id_widget_link = '" . $_POST['id_widget_link'] . "'
                        WHERE id = 1";
			$wpdb->query($sql_update);
		} else {
			// No row exists, perform an insert
			$sql_insert = '	INSERT INTO ' . $wpdb->prefix . "ign_settings
                        (
                            theme_value,
                            id_widget_logo_on,
                            id_widget_link
                        ) VALUES (	
                            '" . $_POST['theme_value'] . "',
                            '" . (isset($_POST['id_widget_logo_on']) ? absint($_POST['id_widget_logo_on']) : 0) . "',
                            '" . $_POST['id_widget_link'] . "'
                        )";
			$wpdb->query($sql_insert);
		}
		idf_flush_object( 'idcf-getSettings' );
		echo '<div id="message" class="updated">Settings Saved</div>';
	}

	$data = getSettings();
	if ( isset( $data ) ) {
		if ( $data->id_widget_link == '' ) {
			$affiliate_link = 'http://ignitiondeck.com';
		} else {
			$affiliate_link = $data->id_widget_link;
		}
	} else {
		$affiliate_link = 'http://ignitiondeck.com';
	}

	$sql_products = 'SELECT * FROM ' . $wpdb->prefix . 'ign_products';
	$products     = $wpdb->get_results( $sql_products );

	$site_url = site_url();
	echo '<div class="wrap">';
	echo admin_menu_html();
	include 'templates/admin/_deckBuilder.php';
	echo '</div>';
}

/**
 * Order Details
 * @global object $wpdb
 */
function order_details() {
	global $wpdb;
	//$total_count = mysql_num_rows(mysql_query("SELECT * FROM ".$wpdb->prefix."ign_pay_info")); // number of total rows in the database
	$sql_products = 'SELECT * FROM ' . $wpdb->prefix . 'ign_products';
	$products     = $wpdb->get_results( $sql_products );

	echo '<div class="wrap">
			' . admin_menu_html();

	include_once 'templates/admin/_orderDetails.php';
	echo '</div>';
}

/*
 * Function for editing Order
 */
function edit_order() {
	$orderid = $_GET['orderid'];

	global $wpdb;
	$sql        = 'SELECT * FROM ' . $wpdb->prefix . "ign_pay_info WHERE id = '" . $orderid . "'";
	$order_data = $wpdb->get_row( $sql );

	$sql_prods = 'SELECT * FROM ' . $wpdb->prefix . 'ign_products';
	$products  = $wpdb->get_results( $sql_prods );

	echo '<div class="wrap">
			' . admin_menu_html();
	include_once 'templates/admin/_orderEdit.php';
	echo '</div>';
}

/*
 *  function for updating order on submission of form
 */
function update_order() {
	#devnote use ID_Order->update_order();
	if ( isset( $_POST['btnUpdateOrder'] ) ) {
		global $wpdb;
		$order_id = absint( $_GET['orderid'] );
		if ( isset( $_POST['manual-input'] ) && $_POST['manual-input'] !== '' ) {
			$price = sanitize_text_field( $_POST['manual-input'] );
		} else {
			$price = sanitize_text_field( $_POST['prod_price'] );
		}
		$order     = new ID_Order( $order_id );
		$the_order = $order->get_order();
		if ( empty( $the_order ) ) {
			return;
		}
		$sql = 'UPDATE ' . $wpdb->prefix . "ign_pay_info SET
				first_name = '" . sanitize_text_field( $_POST['first_name'] ) . "',
				last_name = '" . sanitize_text_field( $_POST['last_name'] ) . "',
				email = '" . sanitize_email( $_POST['email'] ) . "',
				address = '" . sanitize_text_field( $_POST['address'] ) . "',
				country = '" . sanitize_text_field( $_POST['country'] ) . "',
				state = '" . sanitize_text_field( $_POST['state'] ) . "',
				city = '" . sanitize_text_field( $_POST['city'] ) . "',
				zip = '" . sanitize_text_field( $_POST['zip'] ) . "',
				status = '" . sanitize_text_field( $_POST['status'] ) . "',
				product_id = '" . absint( $_POST['product_id'] ) . "',
				product_level = '" . sanitize_text_field( $_POST['product_level'] ) . "',
				prod_price = '" . $price . "'
				WHERE id = '" . $order_id . "'
				";
		$wpdb->query( $sql );

		wp_redirect( 'admin.php?page=order_details' );
		do_action( 'id_modify_order', $order_id, 'update', $the_order );
		do_action( 'id_update_order', $order_id, $the_order );
		exit;
	}
}
add_action( 'init', 'update_order' );

/*
 *  function for viewing order
 */
function view_order() {
	$orderid = $_GET['orderid'];

	$order      = new ID_Order( $orderid );
	$order_data = $order->get_order();

	$project      = new ID_Project( $order_data->product_id );
	$product_data = $project->the_project();
	$post_id      = $project->get_project_postid();

	if ( $order_data->product_level == 1 ) {
		$level_price = $product_data->product_price;
		$level_desc  = $product_data->product_details;
	} else {
		$product_level = (int) ( $order_data->product_level );
		$level_price   = get_post_meta( $post_id, $name = 'ign_product_level_' . $product_level . '_price', true );
		$level_desc    = get_post_meta( $post_id, $name = 'ign_product_level_' . $product_level . '_desc', true );
	}
	echo '<div class="wrap">
			' . admin_menu_html();
	include_once 'templates/admin/_orderView.php';
	echo '</div>';
}

/*
 *  Function for deleting Order
 */
function delete_order() {
	if ( ! empty( $_GET['orderid'] ) ) {
		$order_id = absint( $_GET['orderid'] );
		$delete   = ID_Order::delete_order( $order_id );
	}
	echo '<script type="text/javascript">window.location = "admin.php?page=order_details";</script>';
	exit;
}

/*
 *  Manually add order
 */
function add_order() {
	global $wpdb;
	$tz = get_option( 'timezone_string' );
	if ( empty( $tz ) ) {
		$tz = 'UTC';
	}
	date_default_timezone_set( $tz );
	if ( isset( $_POST['btnAddOrder'] ) ) {
		if ( isset( $_POST['manual-input'] ) && $_POST['manual-input'] !== '' ) {
			$price = esc_attr( str_replace( ',', '', $_POST['manual-input'] ) );
		} else {
			$price = esc_attr( $_POST['prod_price'] );
		}

		$sql = 'INSERT INTO ' . $wpdb->prefix . "ign_pay_info
					(first_name,last_name,email,address,country,state,city,zip,product_id,product_level,prod_price,status,created_at)
				VALUES (
					
					'" . esc_attr( $_POST['first_name'] ) . "',
					'" . esc_attr( $_POST['last_name'] ) . "',
					'" . esc_attr( $_POST['email'] ) . "',
					'" . esc_attr( $_POST['address'] ) . "',
					'" . esc_attr( $_POST['country'] ) . "',
					'" . esc_attr( $_POST['state'] ) . "',
					'" . esc_attr( $_POST['city'] ) . "',
					'" . esc_attr( $_POST['zip'] ) . "',
					'" . absint( $_POST['product_id'] ) . "',
					'" . absint( $_POST['product_level'] ) . "',
					'" . esc_attr( $price ) . "',
					'" . esc_attr( $_POST['status'] ) . "',
					'" . date( 'Y-m-d H:i:s' ) . "'
				)";
		$wpdb->query( $sql );
		$pay_info_id = $wpdb->insert_id;
		do_action( 'id_payment_success', $pay_info_id );
		do_action( 'id_modify_order', $pay_info_id, 'insert' );
		do_action( 'id_insert_order', $pay_info_id );

		$product_settings = getProductSettings( $_POST['product_id'] );
		echo '<script type="text/javascript">window.location = "admin.php?page=order_details";</script>'; //wp_redirect( "admin.php?page=order_details" );
		exit;
	}

	$products = ID_Project::get_all_projects();

	//print_r($products);
	echo '<div class="wrap">
			' . admin_menu_html();
	include_once 'templates/admin/_orderAdd.php';
	echo '</div>';
}

/*
 *  Refund Paypal
 */
function refund_order() {
	global $wpdb;

	if ( isset( $_POST['btnRefundSubmit'] ) ) {
		session_start();
		try {

			$currencyCode = $_REQUEST['currencyCode'];
			$payKey       = $_REQUEST['payKey'];
			$email        = $_REQUEST['receiveremail'];
			$amount       = $_REQUEST['amount'];

			/* Make the call to PayPal to get the Pay token
			If the API call succeded, then redirect the buyer to PayPal
			to begin to authorize payment.  If an error occured, show the
			resulting errors
			*/
			$refundRequest                                 = new RefundRequest();
			$refundRequest->currencyCode                   = $currencyCode;
			$refundRequest->payKey                         = $payKey;
			$refundRequest->requestEnvelope                = new RequestEnvelope();
			$refundRequest->requestEnvelope->errorLanguage = 'en_US';

			$refundRequest->receiverList           = new ReceiverList();
			$receiver1                             = new Receiver();
			$receiver1->email                      = $email;
			$receiver1->amount                     = $amount;
			$refundRequest->receiverList->receiver = $receiver1;

			$ap       = new AdaptivePayments();
			$response = $ap->Refund( $refundRequest );

			if ( strtoupper( $ap->isSuccess ) == 'FAILURE' ) {
				$_SESSION['FAULTMSG'] = $ap->getLastError();
				$location             = 'APIError.php';
				header( "Location: $location" );

			}
		} catch ( Exception $ex ) {
			$fault                = new FaultMessage();
			$errorData            = new ErrorData();
			$errorData->errorId   = $ex->getFile();
			$errorData->message   = $ex->getMessage();
			$fault->error         = $errorData;
			$_SESSION['FAULTMSG'] = $fault;
			$location             = 'APIError.php';
			//header("Location: $location");
		}
	}//end if

	$order_pay_info  = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . "ign_pay_info WHERE id = '" . $_GET['orderid'] . "'" );
	$product_data    = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . "ign_products WHERE product_id = '" . $order_pay_info->product_id . "'" );
	$paypal_settings = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . "ign_pay_settings WHERE product_id = '" . $order_pay_info->product_id . "'" );

	echo '<div class="wrap">
			' . admin_menu_html();
	include_once 'templates/admin/_orderRefund.php';
	echo '</div>';
}

function main_settings() {
	global $wpdb;
	if ( $_POST['btnIgnSettings'] == __( 'Add', 'ignitiondeck' ) ) {
		$sql_insert = '	INSERT INTO ' . $wpdb->prefix . "ign_settings
						(
							theme_value,
							prod_page_fb,
							prod_page_twitter,
							prod_page_linkedin,
							prod_page_google,
							prod_page_pinterest,
							id_widget_logo_on,
							id_widget_link,
							ask_a_question,
							ask_email
						) VALUES (	
							'" . $_POST['theme_value'] . "',
							'" . $_POST['prod_page_fb'] . "',
							'" . $_POST['prod_page_twitter'] . "',
							'" . $_POST['prod_page_linkedin'] . "',
							'" . $_POST['prod_page_google'] . "',
							'" . $_POST['prod_page_pinterest'] . "',
							'" . $_POST['id_widget_logo_on'] . "',
							'" . $_POST['id_widget_link'] . "',
							'" . $_POST['ask_a_question'] . "',
							'" . $_POST['ask_email'] . "'
						)";

		$wpdb->query( $sql_insert );
		idf_flush_object( 'idcf-getSettings' );
	} elseif ( $_POST['btnIgnSettings'] == __( 'Update', 'ignitiondeck' ) ) {
		$sql_update = '	UPDATE ' . $wpdb->prefix . "ign_settings SET
						theme_value = '" . $_POST['theme_value'] . "',
						prod_page_fb = '" . $_POST['prod_page_fb'] . "',
						prod_page_twitter = '" . $_POST['prod_page_twitter'] . "',
						prod_page_linkedin = '" . $_POST['prod_page_linkedin'] . "',
						prod_page_google = '" . $_POST['prod_page_google'] . "',
						prod_page_pinterest = '" . $_POST['prod_page_pinterest'] . "',
						id_widget_logo_on = '" . $_POST['id_widget_logo_on'] . "',
						id_widget_link = '" . $_POST['id_widget_link'] . "',
						ask_a_question = '" . $_POST['ask_a_question'] . "',
						ask_email = '" . $_POST['ask_email'] . "'
						WHERE id = '1'";
		$wpdb->query( $sql_update );
		idf_flush_object( 'idcf-getSettings' );
	}
	$data = getSettings();

	include_once 'templates/admin/_settingsIgnDeck.php';
}

function generate_embed_code() {
	global $wpdb;
	$site_url = site_url();

	$sql_products = 'SELECT * FROM ' . $wpdb->prefix . 'ign_products';
	$products     = $wpdb->get_results( $sql_products );

	include_once 'templates/admin/_embedWidget.php';
}

function product_settings() {
	global $wpdb;
	if ( is_id_pro() ) {
		$project_default = get_option( 'id_project_default' );
	}
	if ( function_exists( 'idf_platform' ) ) {
		$platform = idf_platform();
	} else {
		$platform = 'legacy';
	}
	$purchase_default = get_option( 'id_purchase_default' );
	$ty_default       = get_option( 'id_ty_default' );
	$auto_insert      = get_option( 'idcf_auto_insert' );
	//============================================================================================================================================
	//	DEFAULT settings
	//============================================================================================================================================

		$sql_currency     = 'SELECT * FROM ' . $wpdb->prefix . "ign_prod_default_settings WHERE id = '1'";
		$default_currency = $wpdb->get_row( $sql_currency );

	if ( isset( $_POST['btnSubmitDefaultSettings'] ) ) {
		if ( ! empty( $_POST['ignitiondeck_form_default'] ) ) {
			$serializedFormDefault = serialize( $_POST['ignitiondeck_form_default'] );
		} else {
			$serializedFormDefault = serialize( array() );
		}
		if ( $_POST['btnSubmitDefaultSettings'] == __( 'Save Settings', 'ignitiondeck' ) ) {
			$default_currency = ( isset( $_POST['currency_code_default'] ) ? $_POST['currency_code_default'] : 'USD' );
			$sql_insert                      = '	INSERT INTO ' . $wpdb->prefix . "ign_prod_default_settings
								(
									form_settings,
									currency_code
								) values (
									'" . $serializedFormDefault . "',
									'" . $default_currency . "'
								)";
			$res                             = $wpdb->query( $sql_insert );
			// first time we are setting defaults, so we're updating option to avoid future nags
			do_action( 'id_set_product_defaults' );
			update_option( 'id_defaults_notice', 'off' );
			$message = '<div class="updated fade below-h2" id="message" class="updated"><p>' . __( 'Settings Saved', 'ignitiondeck' ) . '</p></div>';
		}
		if ( $_POST['btnSubmitDefaultSettings'] == __( 'Update Settings', 'ignitiondeck' ) ) {
			$default_currency->currency_code = ( isset( $_POST['currency_code_default'] ) ? $_POST['currency_code_default'] : 'USD' );
			$sql_update                      = '	UPDATE ' . $wpdb->prefix . "ign_prod_default_settings SET
								form_settings='" . $serializedFormDefault . "',
								currency_code = '" . $default_currency->currency_code . "'
								WHERE id='1'";
			$res                             = $wpdb->query( $sql_update );
			do_action( 'id_update_product_defaults' );
			update_option( 'id_defaults_notice', 'off' );
			$message = '<div class="updated fade below-h2" id="message" class="updated"><p>' . __( 'Settings Updated', 'ignitiondeck' ) . '</p></div>';
		}
		// purchase url
		$purl_sel = esc_attr( $_POST['ign_option_purchase_url'] );
		if ( $purl_sel == 'page_or_post' ) {
			$purl = absint( $_POST['ign_purchase_post_name'] );
		} else {
			$purl = esc_attr( $_POST['id_purchase_URL'] );
		}
		$purchase_default = array(
			'option' => $purl_sel,
			'value'  => $purl,
		);
		update_option( 'id_purchase_default', $purchase_default );
		if ( isset( $_POST['auto_insert'] ) ) {
			$auto_insert = absint( $_POST['auto_insert'] );
		} else {
			$auto_insert = 0;
		}
		update_option( 'idcf_auto_insert', $auto_insert );
	}

		$sql  = 'SELECT * FROM ' . $wpdb->prefix . "ign_prod_default_settings WHERE id='1'";
		$res1 = $wpdb->query( $sql );
		$rows = $wpdb->get_results( $sql );
		$row  = &$rows[0];
	if ( $row != null ) {
		$submit_default = __( 'Update Settings', 'ignitiondeck' );
		$form_default   = unserialize( $row->form_settings );
	} else {
		$submit_default = __( 'Save Settings', 'ignitiondeck' );
	}

	$products = ID_Project::get_all_projects();

	$args = array(
		'orderby'        => 'title',
		'order'          => 'ASC',
		'post_type'      => array( 'post', 'page' ),
		'posts_per_page' => -1,
	);
	$list = new WP_Query( $args );
	echo '<div class="wrap">
			' . admin_menu_html();
	include_once 'templates/admin/_productSettings.php';
	echo '</div>';
}

function admin_menu_html() {
	$platform = idf_platform();
	 //All the lines, with #GLOBALS['<variable name>']; replace with $<variable name>
	$menu  = '
		<div class="sidebar ignitiondeck">
			<div class="icon32"></div><h2 class="title">' . __( 'IgnitionDeck Crowdfunding', 'ignitiondeck' ) . '</h2>
			<div class="help">
				<a href="mailto:support@ignitionwp.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large">' . __( 'Support', 'ignitiondeck' ) . '</button></a>
				<a href="https://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large">' . __( 'Documentation', 'ignitiondeck' ) . '</button></a>
			</div>
			<br style="clear: both;"/>
			<h3 class="nav-tab-wrapper">';
	$menu .= apply_filters( 'idcf_project_settings_tab', '<a ' . ( ( $_GET['page'] == 'ignitiondeck' ) ? ' class="nav-tab nav-tab-active"' : ' class="nav-tab"' ) . ' href="admin.php?page=ignitiondeck">' . __( 'Project Settings', 'ignitiondeck' ) . '</a>' );
	if ( is_id_licensed() ) {
		$menu .= '<a ' . ( ( $_GET['page'] == 'deck-builder' ) ? ' class="nav-tab nav-tab-active"' : ' class="nav-tab"' ) . 'href="admin.php?page=deck-builder">' . __( 'Deck Builder', 'ignitiondeck' ) . '</a>';
	}
	$menu    .= '<a ' . ( ( $_GET['page'] == 'order_details' ) ? ' class="nav-tab nav-tab-active"' : ' class="nav-tab"' ) . ' href="admin.php?page=order_details">' . __( 'Orders', 'ignitiondeck' ) . '</a>';
	$menu_sub = '</h3></div>';

	return apply_filters( 'id_submenu_tab', $menu ) . $menu_sub;
}

function getProductFromPostID( $postid ) {
	$project_id = get_post_meta( $post_id, 'ign_project_id', true );
	if ( $project_id > 0 ) {
		$project     = new ID_Project( $project_id );
		$the_project = $project->the_project();
	}
	return ( ! empty( $the_project ) ? $the_project : null );
}

/*
 *	Desc: function to save the project URL that is stored in metabox for project url
 */
function save_project_url( $post_id ) {
	// check nonce
	if ( ! isset( $_POST['add_project_url_box_nonce'] ) || ! wp_verify_nonce( $_POST['add_project_url_box_nonce'], 'add_project_url_box' ) ) {
		return $post_id;
	}

	// check capabilities
	if ( 'post' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
	} elseif ( ! current_user_can( 'edit_page', $post_id ) ) {
		return $post_id;
	}

	// exit on autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	if ( isset( $_POST['ign_option_project_url'] ) ) {
		update_post_meta( $post_id, 'ign_option_project_url', esc_attr( $_POST['ign_option_project_url'] ) );
	}

	if ( $_POST['ign_option_project_url'] == 'external_url' ) {       // If the Project URL is selected as external URL, that is the popup box is used to insert link
		if ( isset( $_POST['id_project_URL'] ) ) {
			update_post_meta( $post_id, 'id_project_URL', esc_attr( $_POST['id_project_URL'] ) );
			//update_post_meta($post_id, 'id_project_URL', $_POST['id_project_URL']);
		} else {
			delete_post_meta( $post_id, 'id_project_URL' );
		}
		delete_post_meta( $post_id, 'ign_post_name' );
	} elseif ( $_POST['ign_option_project_url'] == 'page_or_post' ) {        // If project URL is some other Project page or Post page, then save its name
		if ( isset( $_POST['ign_post_name'] ) ) {

			if ( $_POST['ign_post_name'] != '' ) {
				update_post_meta( $post_id, 'ign_post_name', esc_attr( $_POST['ign_post_name'] ) );
			}
		}
		delete_post_meta( $post_id, 'id_project_URL' );
	} elseif ( $_POST['ign_option_project_url'] == 'current_page' ) {        // If it is the current page that is used as Project page, do nothing
		// Do nothing as the project page is the ignition_project page itself

		// Deleting the Meta data for other types of $_POST['ign_option_project_url'] if it was previously stored
		delete_post_meta( $post_id, 'ign_post_name' );
		delete_post_meta( $post_id, 'id_project_URL' );
	}

}
add_action( 'save_post', 'save_project_url', 10, 2 );

function save_purchase_url( $post_id ) {
	// check nonce
	if ( ! isset( $_POST['add_purchase_url_box_nonce'] ) || ! wp_verify_nonce( $_POST['add_purchase_url_box_nonce'], 'add_purchase_url_box' ) ) {
		return $post_id;
	}

	// check capabilities
	if ( 'post' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
	} elseif ( ! current_user_can( 'edit_page', $post_id ) ) {
		return $post_id;
	}

	// exit on autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	if ( isset( $_POST['ign_option_purchase_url'] ) ) {
		update_post_meta( $post_id, 'ign_option_purchase_url', esc_attr( $_POST['ign_option_purchase_url'] ) );
	}

	if ( $_POST['ign_option_purchase_url'] == 'external_url' ) {      // If the Project URL is selected as external URL, that is the popup box is used to insert link
		if ( isset( $_POST['purchase_project_URL'] ) ) {
			update_post_meta( $post_id, 'purchase_project_URL', esc_attr( $_POST['purchase_project_URL'] ) );
			//update_post_meta($post_id, 'id_project_URL', $_POST['id_project_URL']);
		} else {
			delete_post_meta( $post_id, 'purchase_project_URL' );
		}
		delete_post_meta( $post_id, 'ign_purchase_post_name' );
	} elseif ( $_POST['ign_option_purchase_url'] == 'page_or_post' ) {       // If project URL is some other Project page or Post page, then save its name
		if ( isset( $_POST['ign_purchase_post_name'] ) ) {

			if ( $_POST['ign_purchase_post_name'] != '' ) {
				update_post_meta( $post_id, 'ign_purchase_post_name', esc_attr( $_POST['ign_purchase_post_name'] ) );
			}
		}
		delete_post_meta( $post_id, 'purchase_project_URL' );
	} elseif ( $_POST['ign_option_purchase_url'] == 'current_page' ) {       // If it is the current page that is used as Project page, do nothing
		// Do nothing as the project page is the ignition_project page itself

		// Deleting the Meta data for other types of $_POST['ign_option_project_url'] if it was previously stored
		delete_post_meta( $post_id, 'ign_purchase_post_name' );
		delete_post_meta( $post_id, 'purchase_project_URL' );
	}

}
add_action( 'save_post', 'save_purchase_url', 10, 2 );

/**
 * Function to save project parent in post meta using the meta_box
 */
function save_project_parent( $post_id ) {
	// Get it's parent id for using below
	$old_parent = get_post_meta( $post_id, 'ign_project_parent', true );

	if ( isset( $_POST['ign_option_project_parent'] ) ) {
		$new_parent = absint( $_POST['ign_option_project_parent'] );
		update_post_meta( $post_id, 'ign_project_parent', $new_parent );
		if ( ! empty( $new_parent ) ) {
			if ( $old_parent !== $new_parent ) {
				$old_parent_children = get_post_meta( $old_parent, 'ign_project_children', true );
				if ( ! empty( $old_parent_children ) ) {
					// remove current $post_id from $old_parent_children
					$index_post_id = array_search( $post_id, $old_parent_children );
					if ( ! ( $index_post_id === false ) ) {
						unset( $old_parent_children[ $index_post_id ] );
					}
					update_post_meta( $parent_id, 'ign_project_children', $old_parent_children );
				}
			}

			$parent_children = get_post_meta( $new_parent, 'ign_project_children', true );

			if ( empty( $parent_children ) ) {
				$parent_children = array();
			}

			if ( array_search( $post_id, $parent_children ) === false ) {
				array_push( $parent_children, $post_id );
				update_post_meta( $new_parent, 'ign_project_children', $parent_children );
			}
		} else {
			$parent_children = get_post_meta( $old_parent, 'ign_project_children', true );
			if ( ! empty( $parent_children ) ) {
				$index_post_id = array_search( $post_id, $parent_children );
				if ( ! ( $index_post_id === false ) ) {
					unset( $parent_children[ $index_post_id ] );
				}
				update_post_meta( $old_parent, 'ign_project_children', $parent_children );
			}
		}
	}
}
add_action( 'save_post', 'save_project_parent', 10, 3 );

function set_project_meta( $post_id, $post, $update ) {
	// #devnote no need to save all when only updating one project
	if ( ! empty( $post ) && $post->post_status !== 'auto-draft' ) {
		if ( $post->post_type == 'ignition_product' ) {
			$project_id = get_post_meta( $post_id, 'ign_project_id', true );
			if ( ! empty( $project_id ) ) {
				$raised  = ID_Project::set_raised_meta( $project_id );
				$percent = ID_Project::set_percent_meta( $project_id );
				$days    = ID_Project::set_days_meta( $project_id );
				$closed  = ID_Project::set_closed_meta( $project_id );
			}
		}
	}
}

add_action( 'save_post', 'set_project_meta', 10, 3 );

function delete_project( $post_id ) {
	global $wpdb;
	$post = get_post( $post_id );
	if ( $post->post_type == 'ignition_product' ) {
		$product = getProductbyPostID( $post->ID );
		if ( ! empty( $product ) ) {
			$project_id = get_post_meta( $post_id, 'ign_project_id', true );
			do_action( 'idcf_before_delete_project', $post_id, $project_id );
			$remove_query      = $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'ign_products WHERE id = %d', $product->id );
			$remove_res        = $wpdb->query( $remove_query );
			$sql_prod_settings = 'DELETE FROM ' . $wpdb->prefix . "ign_product_settings WHERE product_id = '" . $product->id . "'";
			$wpdb->query( $sql_prod_settings );
			do_action( 'idcf_delete_project', $post_id, $project_id );
		}
	}
}
add_action( 'before_delete_post', 'delete_project' );

function id_setup_nags() {
	$settings = getSettings();
	// Let's check if the notices have been cleared before
	$settings_notice = get_option( 'id_settings_notice' );
	$defaults_notice = get_option( 'id_defaults_notice' );
	$products_notice = get_option( 'id_products_notice' );
	$idf_notice      = get_option( 'id_idf_notice' );

	if ( empty( $settings ) && empty( $settings_notice ) ) {
		// add settings nag
		add_action( 'admin_notices', 'id_settings_notice' );
	}
	$defaults = getProductDefaultSettings();
	if ( ( empty( $defaults ) || ! is_object( $defaults ) ) && empty( $defaults_notice ) ) {
		// add defaults nag
		add_action( 'admin_notices', 'id_defaults_notice' );
	}
	$products = ID_Project::get_all_projects();
	if ( empty( $products ) && empty( $products_notice ) ) {
		// add products nag
		add_action( 'admin_notices', 'id_products_notice' );
	}
	if ( ! idf_exists() && empty( $idf_notice ) ) {
		add_action( 'admin_notices', 'id_idf_notice' );
	}
}

add_action( 'admin_init', 'id_setup_nags', 100 );

function id_settings_notice() {
	echo '<div class="updated">
	       <p>IgnitionDeck Crowdfunding is active. Please <a href="wp-admin/admin.php?page=ignitiondeck">save settings</a> before creating your first project. | <a href="#" id="id_settings_notice" class="hide-notice">Hide Notice</a></p>
	    </div>';
}

function id_defaults_notice() {
	echo '<div class="updated">
	       <p>Please <a href="admin.php?page=ignitiondeck">save default project settings</a> before creating your first project. | <a href="#" id="id_defaults_notice" class="hide-notice">Hide Notice</a></p>
	    </div>';
}

function id_products_notice() {
	echo '<div class="updated">
	       <p>IgnitionDeck Crowdfunding is active. Now it&rsquo;s time to <a href="post-new.php?post_type=ignition_product">create your first project</a>. | <a href="#" id="id_products_notice" class="hide-notice">Hide Notice</a></p>
	    </div>';
}

function id_idf_notice() {
	if ( file_exists( plugin_dir_path( dirname( __FILE__ ) ) . '/ignitiondeck/idf.php' ) ) {
		$url = wp_nonce_url( network_admin_url( 'plugins.php' ) );
	} else {
		$url = wp_nonce_url( network_admin_url( 'plugin-install.php?tab=search&s=ignitiondeck' ) );
	}
	echo '<div class="updated">
	       <p>This plugin requires the <a href="' . $url . '">IgnitionDeck Framework</a> in order to function properly.</p>
	    </div>';
}

// fix project sorting based on numeric values
add_action('pre_get_posts', 'id_project_sort_patch');
function id_project_sort_patch( $query ){
	global $pagenow;
	if( !is_admin() || $pagenow != 'edit.php' || $query->get('post_type') != 'ignition_product' || !isset($_GET['orderby']) )
		return;

	$numeric_sortable_fields = array(
										'goal' => 'ign_fund_goal',
										'raised' => 'ign_fund_raised',
										'daysleft' => 'ign_days_left',
										'enddate' => 'ign_fund_end'
									);
	$date_sortable_fields = array(
									);
	if( isset( $numeric_sortable_fields[$_GET['orderby']] ) ){
		$query->set('meta_key', $numeric_sortable_fields[$_GET['orderby']]);
		$query->set('meta_type', 'NUMERIC');
	    $query->set('orderby', 'meta_value_num');
	}
	elseif( isset( $date_sortable_fields[$_GET['orderby']] ) ){
		$query->set('meta_key', $date_sortable_fields[$_GET['orderby']]);
		$query->set('meta_type', 'DATE');
	    $query->set('orderby', 'meta_value_date');
	}

    unset( $query->query_vars['meta_value'] );
}

// Start Project duplicating functionality
add_filter('post_row_actions','project_duplicate_action_row', 10, 2);
function project_duplicate_action_row($actions, $post){
    if ($post->post_type =="ignition_product" && current_user_can('edit_posts')) {
		$url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'ign_duplicate_post_as_draft',
					'post' => $post->ID,
				),
				'admin.php'
			),
			basename(__FILE__),
			'ign_duplicate_nonce'
		);
        $actions['duplicate-project'] = '<a href="'.$url.'" title="Duplicate this Project" rel="permalink">Duplicate Project</a>';
    }
    return $actions;
}

add_action('admin_action_ign_duplicate_post_as_draft', 'ign_duplicate_post_as_draft');
function ign_duplicate_post_as_draft() {
	// check if post ID has been provided and action
	if ( empty( $_GET[ 'post' ] ) ) {
		wp_die( 'No post to duplicate has been provided!' );
	}

	// Nonce verification
	if ( ! isset( $_GET[ 'ign_duplicate_nonce' ] ) || ! wp_verify_nonce( $_GET[ 'ign_duplicate_nonce' ], basename( __FILE__ ) ) ) {
		return;
	}

	// Get the original post id
	$post_id = absint( $_GET[ 'post' ] );

	// And all the original post data then
	$post = get_post( $post_id );

	/*
	 * if you don't want current user to be the new post author,
	 * then change next couple of lines to this: $user_id = $post->post_author;
	 */
	$user_id = get_current_user_id(); // $post->post_author;

	// if post data exists (I am sure it is, but just in a case), create the post duplicate
	if ( $post ) {
		// new post data array
		$args = array(
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $user_id,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $post->post_name,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'draft',
			'post_title'     => $post->post_title . ' (Duplicate)',
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order
		);
		$new_post_id = wp_insert_post( $args );

		/*
		 * get all current post terms ad set them to the new post draft
		 */
		$taxonomies = get_object_taxonomies( get_post_type( $post ) ); // returns array of taxonomy names for post type, ex array("category", "post_tag");
		if( $taxonomies ) {
			foreach ( $taxonomies as $taxonomy ) {
				$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
				wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
			}
		}

		// duplicate all post meta
		$do_not_copy = array( '_wp_old_slug', 'ign_fund_raised', 'ign_percent_raised', 'ign_days_left', 'ign_end_type', 'ign_project_closed', 'ign_project_failed' );
		$post_meta = get_post_meta( $post_id );
		if( $post_meta ) {
			foreach ( $post_meta as $meta_key => $meta_values ) {
				if( in_array($meta_key, $do_not_copy) ) { // do nothing for these metas
					continue;
				} else {
					foreach ( $meta_values as $meta_value ) {
						add_post_meta( $new_post_id, $meta_key, $meta_value );
					}
				}
			}
		}
		
		// IgnitionDeck Part
		// Create IDC Product
		$project_id = get_post_meta($post_id, 'ign_project_id', true);
		$project = new ID_Project($project_id);
		$the_project = $project->the_project();
		if (empty($the_project)) {
			wp_die(__('Project Not Found', 'ignitiondeck'));
		}
		$the_project->id = null;
		$new_project_id = ID_Project::insert_project((array) $the_project);

		// Link with project
		update_post_meta($new_post_id, 'ign_project_id', $new_project_id);

		// Get total levels
		$level_count = get_post_meta($new_post_id, 'ign_product_level_count', true);
		global $wpdb;
		$mdid_assignments = get_assignments_by_project($project_id);
		foreach($mdid_assignments as $ma) {
			$level = ID_Member_Level::get_level($ma->level_id);
			$level->id = null;
			$level->level_name = $level->level_name . ' (Duplicate)';
			$add_level = new ID_Member_Level();
			$added_level = $add_level->add_level((array) $level);

			$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'mdid_project_levels WHERE id = %d', $ma->assignment_id);
			$res = $wpdb->get_row($sql);
			if (!empty($res)) {
				$data = unserialize($res->levels);
				if (is_array($data)) {
					$sql = 'INSERT INTO '.$wpdb->prefix.'mdid_project_levels (levels) VALUES ("'.esc_sql(serialize($data)).'")';
					$res = $wpdb->query($sql);
					$assignment_id = $wpdb->insert_id;
					$sql = 'INSERT INTO '.$wpdb->prefix.'mdid_assignments (level_id, project_id, assignment_id) VALUES ("'.$added_level['level_id'].'", "'.$new_project_id.'", "'.$assignment_id.'")';
					$res = $wpdb->query($sql);
					//echo '<pre>'; print_r($data); exit;
				}
			}
		}

		// Redirect to all posts with a message
		wp_safe_redirect(
			add_query_arg(
				array(
					'post_type' => ( 'post' !== get_post_type( $post ) ? get_post_type( $post ) : false ),
					'saved' => 'ign_post_duplicated'
				),
				admin_url( 'edit.php' )
			)
		);
		exit;

	} else {
		wp_die( 'Project creation failed, could not find original project.' );
	}
}

add_action( 'admin_notices', 'ign_duplication_admin_notice' );
function ign_duplication_admin_notice() {
	// Get the current screen
	$screen = get_current_screen();
	if ( 'edit' !== $screen->base ) {
		return;
	}
    //Checks if settings updated
    if ( isset( $_GET[ 'saved' ] ) && 'ign_post_duplicated' == $_GET[ 'saved' ] ) {
		 echo '<div class="notice notice-success is-dismissible"><p>Project duplicated successfully.</p></div>';
    }
}
// End Project duplicating functionality