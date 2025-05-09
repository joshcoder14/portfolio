<?php

function ide_create_project_url() {
	$durl   = md_get_durl();
	$prefix = idf_get_querystring_prefix();
	return $durl . $prefix . 'create_project=1';
}

function ide_creator_info( $post_id ) {
	// returns project creator info
	$post             = get_post( $post_id );
	$author           = $post->post_author;
	$user             = get_user_by( 'id', $author );
	$user_meta        = get_user_meta( $author );
	$company_name     = ide_creator_company( $user, $post_id );
	$company_logo     = ide_creator_logo( $author, $post_id );
	$company_location = ide_company_location( $user_meta, $post_id );
	$company_url      = ide_company_url( $user, $post_id );
	$company_fb       = ide_company_fb( $user_meta, $post_id );
	$company_twitter  = ide_company_twitter( $user_meta, $post_id );
	$company_google   = ide_company_google( $user_meta, $post_id );
	$profile          = array(
		'author'   => $author,
		'name'     => $company_name,
		'logo'     => $company_logo,
		'location' => $company_location,
		'url'      => $company_url,
		'facebook' => $company_fb,
		'twitter'  => $company_twitter,
		'google'   => $company_google,
	);
	return $profile;
}

function ide_creator_company( $user, $post_id ) {
	$company_name = get_post_meta( $post_id, 'ign_company_name', true );
	if ( empty( $company_name ) ) {
		$company_name = $user->display_name;
	}
	return $company_name;
}

function ide_creator_logo( $author, $post_id ) {
	$avatar_id = get_user_meta($author, 'idc_avatar', true);
	$company_logo = isset(wp_get_attachment_image_src($avatar_id)[0])?wp_get_attachment_image_src($avatar_id)[0]:false;
	if ( empty( $company_logo ) ) {
		$company_logo = get_avatar_url( $author );
	}
	return $company_logo;
	/*$company_logo = idf_get_object( 'ide_creator_logo-' . $author );
	if ( empty( $company_logo ) ) {
		// cache is empty
		$company_logo = get_post_meta( $post_id, 'ign_company_logo', true );
		if ( empty( $company_logo ) ) {
			$company_logo = get_avatar_url( $author );
		}
		do_action( 'idf_cache_object', 'ide_creator_logo-' . $author, $company_logo );
	}
	return $company_logo;*/
}

function ide_company_location( $user_meta, $post_id ) {
	$company_location = get_post_meta( $post_id, 'ign_company_location', true );
	if ( empty( $company_location ) && ! empty( $user_meta['location'][0] ) ) {
		$company_location = $user_meta['location'][0];
	}
	return $company_location;
}

function ide_company_url( $user, $post_id ) {
	$company_url = get_post_meta( $post_id, 'ign_company_url', true );
	if ( empty( $company_url ) ) {
		$company_url = $user->user_url;
	}
	return $company_url;
}

function ide_company_fb( $user_meta, $post_id ) {
	$company_fb = get_post_meta( $post_id, 'ign_company_fb', true );
	if ( empty( $company_fb ) ) {
		$company_fb = ( ! empty( $user_meta['facebook'] ) ? $user_meta['facebook'][0] : null );
	}
	return $company_fb;
}

function ide_company_twitter( $user_meta, $post_id ) {
	$company_twitter = get_post_meta( $post_id, 'ign_company_twitter', true );
	if ( empty( $company_twitter ) ) {
		$company_twitter = ( ! empty( $user_meta['twitter'] ) ? $user_meta['twitter'][0] : null );
	}
	return $company_twitter;
}

function ide_company_google( $user_meta, $post_id ) {
	$company_google = get_post_meta( $post_id, 'ign_company_google', true );
	if ( empty( $company_google ) ) {
		$company_google = ( ! empty( $user_meta['google'] ) ? $user_meta['google'][0] : null );
	}
	return $company_google;
}

add_shortcode( 'project_submission_form', 'id_submissionForm' );


function id_submissionForm( $post_id = null ) {
	global $wpdb;
	global $permalink_structure;
	if ( is_multisite() ) {
		require ABSPATH . WPINC . '/pluggable.php';
	}
	$current_user = wp_get_current_user();
	if ( empty( $permalink_structure ) ) {
		$prefix = '&';
	} else {
		$prefix = '?';
	}
	$wp_upload_dir = wp_upload_dir();
	if ( ! function_exists( 'wp_handle_upload' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	if ( empty( $post_id ) ) {
		if ( isset( $_GET['edit_project'] ) ) {
			$post_id = $_GET['edit_project'];
			$post    = get_post( $post_id );
			$user_id = $current_user->ID;
			if ( current_user_can( 'create_edit_projects' ) ) {
				if ( $user_id == $post->post_author || apply_filters( 'ide_fes_edit_project_editor', false, $post_id, $user_id ) ) {
					// allows user to post iframe and embed code in long descriptions
					add_filter( 'wp_kses_allowed_html', 'idcf_filter_wp_kses', 11, 2 );
				}
			}
		} else {
			if ( isset( $_GET['create_project'] ) && $_GET['create_project'] ) {
				if ( current_user_can( 'create_edit_projects' ) ) {
					// allows user to post iframe and embed code in long descriptions
					add_filter( 'wp_kses_allowed_html', 'idcf_filter_wp_kses', 11, 2 );
				}
			}
		}
	} else {
		// post_id is coming in arguments, check that user can edit post, and it's his post as well
		$post    = get_post( $post_id );
		$user_id = $current_user->ID;
		if ( current_user_can( 'create_edit_projects' ) ) {
			if ( $user_id == $post->post_author ) {
				// allows user to post iframe and embed code in long descriptions
				add_filter( 'wp_kses_allowed_html', 'idcf_filter_wp_kses', 11, 2 );
			}
		}
	}
	$memberdeck_gateways = get_option( 'memberdeck_gateways' );
	$fund_types          = get_option( 'idc_cf_fund_type' );
	if ( empty( $fund_types ) ) {
		$fund_types = 'capture';
	}
	$tz          = get_option( 'timezone_string', 'UTC' ); // for project start/end
	$date_format = idf_date_format();
	$vars        = array(
		'fund_types'       => $fund_types,
		'project_end_type' => apply_filters( 'ide_project_end_type', 'open' ), // default to open #devnote make this an option
		'project_start'    => apply_filters( 'ide_project_start', date( $date_format ) ), // default to today
		'project_end'      => apply_filters( 'ide_project_end', date( $date_format, strtotime( '+30 days' ) ) ), // default to 30 days #devnote make an option
	);

	if ( ! empty( $post_id ) && $post_id > 0 ) {
		if ( empty( $post ) ) {
			$post = get_post( $post_id );
		}
		$status       = $post->post_status;
		$creator_info = ide_creator_info( $post_id );
		$project_name = get_the_title( $post_id );
		$categories   = wp_get_post_terms( $post_id, 'project_category' );
		if ( ! empty( $categories ) && is_array( $categories ) ) {
			$project_category = $categories[0]->slug;
		} else {
			$project_category = null;
		}
		//converting project start and end date to default string based date format saved in wordpress settings so it works in a same manner as it is used to be before
		$date_format               = get_option( 'date_format' );
		$project_start             = date($date_format, get_post_meta( $post_id, 'ign_start_date', true ));
		$project_end               = date($date_format, get_post_meta( $post_id, 'ign_fund_end', true ));
		$project_goal              = get_post_meta( $post_id, 'ign_fund_goal', true );
		$project_short_description = get_post_meta( $post_id, 'ign_project_description', true );
		$project_long_description  = get_post_meta( $post_id, 'ign_project_long_description', true );
		$project_faq               = get_post_meta( $post_id, 'ign_faqs', true );
		$project_updates           = get_post_meta( $post_id, 'ign_updates', true );
		$project_video             = get_post_meta( $post_id, 'ign_product_video', true );
		$project_hero              = ID_Project::get_project_thumbnail( $post_id );
		$project_id                = get_post_meta( $post_id, 'ign_project_id', true );
		$project_end_type          = get_post_meta( $post_id, 'ign_end_type', true );
		$purchase_form             = get_post_meta( $post_id, 'ign_option_purchase_url', true );
		// levels
		$project_levels = get_post_meta( $post_id, 'ign_product_level_count', true );

		$levels             = array();
		$levels[0]          = array();
		$levels[0]['title'] = get_post_meta( $post_id, 'ign_product_title', true ); /* level 1 */
		$levels[0]['price'] = get_post_meta( $post_id, 'ign_product_price', true ); /* level 1 */
		$levels[0]['short'] = get_post_meta( $post_id, 'ign_product_short_description', true ); /* level 1 */
		$levels[0]['long']  = get_post_meta( $post_id, 'ign_product_details', true ); /* level 1 */
		$levels[0]['limit'] = get_post_meta( $post_id, 'ign_product_limit', true ); /* level 1 */
		// Project fund type for the levels
		$levels_project_fund_type = get_post_meta( $post_id, 'mdid_levels_fund_type', true );
		if ( ! empty( $levels_project_fund_type ) ) {
			$levels[0]['fund_type'] = $levels_project_fund_type[0];
		}
		for ( $i = 1; $i <= $project_levels - 1; $i++ ) {
			$levels[ $i ]          = array();
			$levels[ $i ]['title'] = get_post_meta( $post_id, 'ign_product_level_' . ( $i + 1 ) . '_title', true );
			$levels[ $i ]['price'] = get_post_meta( $post_id, 'ign_product_level_' . ( $i + 1 ) . '_price', true );
			$levels[ $i ]['short'] = get_post_meta( $post_id, 'ign_product_level_' . ( $i + 1 ) . '_short_desc', true );
			$levels[ $i ]['long']  = get_post_meta( $post_id, 'ign_product_level_' . ( $i + 1 ) . '_desc', true );
			$levels[ $i ]['limit'] = get_post_meta( $post_id, 'ign_product_level_' . ( $i + 1 ) . '_limit', true );
			if ( ! empty( $levels_project_fund_type[ $i ] ) ) {
				$levels[ $i ]['fund_type'] = $levels_project_fund_type[ $i ];
			}
		}

		$new_vars = array(
			'post_id'                   => $post_id,
			'project_name'              => $project_name,
			'project_category'          => $project_category,
			'project_start'             => $project_start,
			'project_end'               => $project_end,
			'project_goal'              => $project_goal,
			'project_short_description' => $project_short_description,
			'project_long_description'  => $project_long_description,
			'project_faq'               => $project_faq,
			'project_updates'           => $project_updates,
			'project_video'             => $project_video,
			'project_hero'              => $project_hero,
			'project_id'                => $project_id,
			'project_end_type'          => $project_end_type,
			'fund_types'                => $fund_types,
			'project_levels'            => $project_levels,
			'levels'                    => $levels,
			'status'                    => $status,
		);
		$vars     = wp_parse_args( $new_vars, $vars );
	}
	if ( isset( $_POST['project_fesubmit'] ) || isset( $_POST['project_fesave'] ) ) {
		// Checking nonce field first, before going further
		if ( wp_verify_nonce( sanitize_text_field( $_POST['idcf_fes_wp_nonce'] ), 'idcf_fes_section_nonce' ) ) {
			// Create project variables
			if ( isset( $_POST['project_name'] ) ) {
				$project_name = sanitize_text_field( $_POST['project_name'] );
			}
			if ( isset( $_POST['project_category'] ) ) {
				$project_category = sanitize_text_field( $_POST['project_category'] );
			} elseif ( ! empty( $vars['project_category'] ) ) {
				$project_category = $vars['project_category'];
			} else {
				$project_category = null;
			}
			$project_goal              = ( isset( $_POST['project_goal'] ) ? sanitize_text_field( str_replace( ',', '', $_POST['project_goal'] ) ) : $vars['project_goal'] );
			$project_start             = ( isset( $_POST['project_start'] ) ? sanitize_text_field( $_POST['project_start'] ) : $vars['project_start'] );
			$project_end               = ( isset( $_POST['project_end'] ) ? sanitize_text_field( $_POST['project_end'] ) : $vars['project_end'] );
			$project_short_description = ( isset( $_POST['project_short_description'] ) ? sanitize_text_field( $_POST['project_short_description'] ) : '' );
			$project_long_description  = ( isset( $_POST['project_long_description'] ) ? wpautop( wp_kses_post( balanceTags( $_POST['project_long_description'] ) ) ) : '' );
			$project_faq               = ( isset( $_POST['project_faq'] ) ? wpautop( wp_kses_post( balanceTags( $_POST['project_faq'] ) ) ) : '' );
			$project_updates           = ( isset( $_POST['project_updates'] ) ? wpautop( wp_kses_post( balanceTags( $_POST['project_updates'] ) ) ) : '' );
			$project_video             = ( isset( $_POST['project_video'] ) ? wp_kses_post( $_POST['project_video'] ) : '' );
			if ( isset( $_FILES['project_hero'] ) && $_FILES['project_hero']['size'] > 0 ) {
				//$project_hero = sanitize_text_field($_POST['project_hero']);
				$project_hero  = wp_handle_upload( $_FILES['project_hero'], array( 'test_form' => false ) );
				$hero_filetype = wp_check_filetype( basename( $project_hero['file'] ), null );
				if ( $hero_filetype['ext'] == strtolower( 'png' ) || $hero_filetype['ext'] == strtolower( 'jpg' ) || $hero_filetype['ext'] == strtolower( 'gif' ) || $hero_filetype['ext'] == strtolower( 'jpeg' ) ) {
					$hero_attachment = array(
						'guid'           => $wp_upload_dir['url'] . '/' . basename( $project_hero['file'] ),
						'post_mime_type' => $hero_filetype['type'],
						'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $project_hero['file'] ) ),
						'post_content'   => '',
						'post_status'    => 'inherit',
					);
					$hero_posted     = true;
				} else {
					$hero_posted = false;
				}
			} else {
				$hero_posted = false;
				if ( empty( $vars['project_hero'] ) ) {
					$project_hero = null;
				} else {
					$project_hero = $vars['project_hero'];
				}
				// Check if the already present image is removed
				if ( isset( $_POST['project_hero_removed'] ) && $_POST['project_hero_removed'] == 'yes' ) {
					$project_hero_removed = true;
				}
			}

			$project_end_type = ( isset( $_POST['project_end_type'] ) ? sanitize_text_field( $_POST['project_end_type'] ) : $vars['project_end_type'] );
			$saved_levels     = array();
			// set defaults
			$level_defaults      = array(
				'title' => $project_name,
				'price' => '',
				'short' => $project_short_description,
				'long'  => $project_long_description,
				'limit' => '',
			);
			$saved_funding_types = array();
			$project_levels      = 1;
			if ( isset( $_POST['project_levels'] ) ) {
				// #devnote cleanup
				$project_levels = absint( $_POST['project_levels'] );
				$saved_levels   = array();
				// Check for cloned level #devnote push this into another method or filter
				if ( isset( $_POST['project_fund_type'] ) ) {
					if ( count( $_POST['project_fund_type'] ) > 1 ) {
						// remove cloned level data
						array_pop( $_POST['project_fund_type'] );
					}
				}
				for ( $i = 0, $j = 0; $i <= $project_levels - 1; $i++ ) {
					$saved_levels[ $i ] = ( $i == 0 ? $level_defaults : array() );
					if ( isset( $_POST['project_level_title'][ $i ] ) ) {
						$saved_levels[ $i ]['title'] = $_POST['project_level_title'][ $i ];
					} elseif ( isset( $levels[ $i ]['title'] ) ) {
						// project is live and title cannot be edited
						$saved_levels[ $i ]['title'] = $levels[ $i ]['title'];
					}
					if ( isset( $_POST['project_level_price'][ $i ] ) ) {
						if ( empty( $_POST['project_level_price'][ $i ] ) ) {
							$saved_levels[ $i ]['price'] = sanitize_text_field( $_POST['project_level_price'][ $i ] );
						} else {
							$saved_levels[ $i ]['price'] = sanitize_text_field( $_POST['project_level_price'][ $i ] );
						}
					} elseif ( isset( $levels[ $i ]['price'] ) ) {
						// project is live and price cannot be edited
						$saved_levels[ $i ]['price'] = $levels[ $i ]['price'];
					}
					if ( isset( $_POST['level_description'][ $i ] ) ) {
						$saved_levels[ $i ]['short'] = sanitize_text_field( $_POST['level_description'][ $i ] );
					}
					if ( isset( $_POST['level_long_description'][ $i ] ) ) {
						$saved_levels[ $i ]['long'] = wpautop( wp_kses_post( balanceTags( $_POST['level_long_description'][ $i ] ) ) );
					}
					if ( isset( $_POST['project_level_limit'][ $i ] ) ) {
						$saved_levels[ $i ]['limit'] = absint( $_POST['project_level_limit'][ $i ] );
					} elseif ( isset( $levels[ $i ]['limit'] ) ) {
						// project is live and limit cannot be edited
						$saved_levels[ $i ]['limit'] = $levels[ $i ]['limit'];
					}
					if ( ! isset( $status ) || ( isset( $status ) && $status !== 'publish' ) ) {
						// non-published post
						if ( isset( $_POST['project_fund_type'][ $i ] ) ) {
							$saved_funding_types[ $i ] = sanitize_text_field( $_POST['project_fund_type'][ $i ] );
						} elseif ( isset( $levels_project_fund_type[ $i ] ) ) {
							$saved_funding_types[ $i ] = $levels_project_fund_type[ $i ];
						}
					} else {
						// published
						if ( ! empty( $levels_project_fund_type[ $i ] ) ) {
							$saved_funding_types[ $i ] = $levels_project_fund_type[ $i ];
						} else {
							$saved_funding_types[ $i ] = ( ! empty( $_POST['project_fund_type'][ $j ] ) ? sanitize_text_field( $_POST['project_fund_type'][ $j ] ) : null );
							$j++;
						}
					}
				}
			}
			// Create user variables
			if ( is_user_logged_in() ) {
				$current_user   = wp_get_current_user();
				$user_id        = $current_user->ID;
				$comment_status = get_option( 'default_comment_status' );
				// Create a New Post
				$args = array(
					'post_author'    => $user_id,
					'post_content'   => '',
					'post_title'     => $project_name,
					'post_name'      => str_replace( ' ', '-', $project_name ),
					'post_type'      => 'ignition_product',
					'tax_input'      => array( 'project_category' => $project_category ),
					'comment_status' => $comment_status,
				);
				if ( isset( $_POST['project_post_id'] ) ) {
					// existing post
					$args['ID'] = absint( $_POST['project_post_id'] );
					$post       = get_post( $post_id );
					$status     = $post->post_status;
					if ( strtoupper( $status ) !== 'PUBLISH' ) {
						if ( isset( $_POST['project_fesubmit'] ) ) {
							// only filter unpublished posts
							$status = apply_filters( 'ide_project_fesubmit_status', 'pending' );
						}
					}
					$args['post_status']    = $status;
					$args['tax_input']      = array( 'project_category' => $project_category );
					$args['comment_status'] = $post->comment_status;
				} else {
					// new post
					if ( isset( $_POST['project_fesave'] ) ) {
						$args['post_status'] = apply_filters( 'ide_project_fesave_status', 'draft' );
					} elseif ( isset( $_POST['project_fesubmit'] ) ) {
						$args['post_status'] = apply_filters( 'ide_project_fesubmit_status', 'pending' );
					}
				}
				// update posted date (update cases)
				if ( isset( $post_id ) && isset( $_GET['edit_project'] ) ) {
					$args['post_date'] = $post->post_date;
				}
				$post_id = ig_sanitise_insert_post( $args );
				if ( ! current_user_can( 'manage_categories' ) ) {
					wp_set_object_terms( $post_id, $project_category, 'project_category' );
				}
				if ( isset( $post_id ) ) {
					if ( $hero_posted ) {
						$hero_id = wp_insert_attachment( $hero_attachment, $project_hero['file'], $post_id );
						require_once ABSPATH . 'wp-admin/includes/image.php';
						$hero_data = wp_generate_attachment_metadata( $hero_id, $project_hero['file'] );
						$metadata  = wp_update_attachment_metadata( $hero_id, $hero_data );
					}
					// use project id to determine if we are creating or updating
					$project_id = get_post_meta( $post_id, 'ign_project_id', true );
					// Insert to ign_products
					$proj_args         = array( 'product_name' => $project_name );
					$proj_args['goal'] = $project_goal;
					// there are some cases where we might not have level data when creating new projects
					$saved_levels = apply_filters( 'ide_saved_levels', $saved_levels, $post_id );
					if ( isset( $saved_levels[0] ) || empty( $project_id ) ) {
						$proj_args['ign_product_title'] = apply_filters( 'ide_level_1_title', ( isset( $saved_levels[0]['title'] ) ? $saved_levels[0]['title'] : '' ), $post_id );
						$proj_args['ign_product_limit'] = apply_filters( 'ide_level_1_limit', ( isset( $saved_levels[0]['limit'] ) ? $saved_levels[0]['limit'] : 0 ), $post_id );
						$proj_args['product_details']   = apply_filters( 'ide_level_1_desc', ( isset( $saved_levels[0]['short'] ) ? $saved_levels[0]['short'] : '' ), $post_id );
						$proj_args['product_price']     = apply_filters( 'ide_level_1_price', ( isset( $saved_levels[0]['price'] ) ? $saved_levels[0]['price'] : 0 ), $post_id );
					}
					if ( ! empty( $project_id ) ) {
						$project = new ID_Project( $project_id );
						$project->update_project( $proj_args );
					} else {
						$project_id = ID_Project::insert_project( $proj_args );
					}
					if ( isset( $project_id ) ) {
						// Update postmeta
						//update_post_meta($post_id, 'ign_product_name', $project_name);

						//create date object from wp saved date format
						$dateObj=date_create_from_format(get_option( 'date_format' ) , $project_start);
						//fallback to generic date function if date object fails
						$new_project_startdate = $dateObj ? $dateObj->getTimestamp() : strtotime($project_start);
						update_post_meta( $post_id, 'ign_start_date', $new_project_startdate );

						//create date object from wp saved date format
						$dateObj=date_create_from_format(get_option( 'date_format' ) , $project_end);
						//fallback to generic date function if date object fails
						$new_project_enddate = $dateObj ? $dateObj->getTimestamp() : strtotime($project_end);
						update_post_meta( $post_id, 'ign_fund_end', $new_project_enddate );

						update_post_meta( $post_id, 'ign_fund_goal', $project_goal );
						update_post_meta( $post_id, 'ign_project_description', $project_short_description );
						update_post_meta( $post_id, 'ign_project_long_description', $project_long_description );
						update_post_meta( $post_id, 'ign_faqs', $project_faq );
						update_post_meta( $post_id, 'ign_updates', $project_updates );
						update_post_meta( $post_id, 'ign_product_video', $project_video );
						if ( isset( $project_hero['url'] ) && is_array( $project_hero ) ) {
							$project_hero = sanitize_text_field( $project_hero['url'] );
							//update_post_meta($post_id, 'ign_product_image1', $project_hero);
							$sql = $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->prefix . 'posts WHERE guid = %s', $project_hero );
							$res = $wpdb->get_row( $sql );
							if ( ! empty( $res ) ) {
								$attachment_id = $res->ID;
								set_post_thumbnail( $post_id, $attachment_id );
							}
						} elseif ( ! isset( $project_hero ) ) {
							//delete_post_meta($post_id, 'ign_product_image1');
							delete_post_thumbnail( $post_id );
						} elseif ( isset( $project_hero_removed ) && $project_hero_removed ) {
							delete_post_thumbnail( $post_id );
						}

						update_post_meta( $post_id, 'ign_project_id', $project_id );
						update_post_meta( $post_id, 'ign_end_type', $project_end_type );
						if ( empty( $purchase_form ) ) {
							update_post_meta( $post_id, 'ign_option_purchase_url', 'default' );
						}
						// levels
						update_post_meta( $post_id, 'ign_product_level_count', $project_levels );
						if ( ! empty( $saved_levels ) ) {
							// level 1
							update_post_meta( $post_id, 'ign_product_title', ( isset( $saved_levels[0]['title'] ) ? $saved_levels[0]['title'] : null ) );
							update_post_meta( $post_id, 'ign_product_price', ( isset( $saved_levels[0]['price'] ) ? $saved_levels[0]['price'] : null ) );
							update_post_meta( $post_id, 'ign_product_short_description', ( isset( $saved_levels[0]['short'] ) ? $saved_levels[0]['short'] : null ) );
							update_post_meta( $post_id, 'ign_product_details', ( isset( $saved_levels[0]['long'] ) ? $saved_levels[0]['long'] : null ) );
							update_post_meta( $post_id, 'ign_product_limit', ( isset( $saved_levels[0]['limit'] ) ? $saved_levels[0]['limit'] : null ) );

							for ( $i = 2; $i <= $project_levels; $i++ ) {
								update_post_meta( $post_id, 'ign_product_level_' . ( $i ) . '_title', $saved_levels[ $i - 1 ]['title'] );
								update_post_meta( $post_id, 'ign_product_level_' . ( $i ) . '_price', $saved_levels[ $i - 1 ]['price'] );
								update_post_meta( $post_id, 'ign_product_level_' . ( $i ) . '_short_desc', $saved_levels[ $i - 1 ]['short'] );
								update_post_meta( $post_id, 'ign_product_level_' . ( $i ) . '_desc', $saved_levels[ $i - 1 ]['long'] );
								update_post_meta( $post_id, 'ign_product_level_' . ( $i ) . '_limit', $saved_levels[ $i - 1 ]['limit'] );
							}
						}
						// Saving project fund type for all the levels in postmeta
						$saved_funding_types = apply_filters( 'ide_saved_funding_types', $saved_funding_types, $post_id );
						update_post_meta( $post_id, 'mdid_levels_fund_type', $saved_funding_types );

						// Attach product to user
						set_user_projects( $post_id, $user_id );
						if ( ! isset( $status ) ) {
							do_action( 'ide_fes_create', $user_id, $project_id, $post_id, $proj_args, $saved_levels, $saved_funding_types );
						} else {
							do_action( 'ide_fes_update', $user_id, $project_id, $post_id, $proj_args, $saved_levels, $saved_funding_types );
						}
						$vars = array(
							'post_id'                   => $post_id,
							'project_name'              => $project_name,
							'project_category'          => $project_category,
							'project_start'             => $project_start,
							'project_end'               => $project_end,
							'project_goal'              => $project_goal,
							'project_short_description' => $project_short_description,
							'project_long_description'  => $project_long_description,
							'project_faq'               => $project_faq,
							'project_updates'           => $project_updates,
							'project_video'             => $project_video,
							'project_hero'              => $project_hero,
							'project_id'                => $project_id,
							/*'project_fund_type' => $project_fund_type,*/
							'project_end_type'          => $project_end_type,
							'project_levels'            => $project_levels,
							'levels'                    => $saved_levels,
						);
						do_action( 'ide_fes_submit', $post_id, $project_id, $vars );
						echo '<script>location.href="' . apply_filters( 'ide_fes_submit_redirect', md_get_durl() . $prefix . 'edit_project=' . $post_id ) . '";</script>';
					} else {
						// return some error
					}
				} else {
					// return some error
				}
			}
		}
	}
	$form = new ID_FES( null, ( isset( $vars ) ? $vars : null ) );
	ob_start();
	do_action( 'ide_before_fes_display' );
	$www = ob_get_clean();
	$output  = $www.'<div class="ignitiondeck"><div class="' . apply_filters( 'id_fes_form_wrapper_class', 'id-fes-form-wrapper' ) . '">';
	$output .= '<form name="fes" id="fes" action="" method="POST" enctype="multipart/form-data" data-status="' . ( isset( $status ) ? $status : 'draft' ) . '">';
	$output .= $form->display_form();
	$output .= '</form>';
	$output .= '</div></div>';
	return apply_filters( 'ide_fes_display', $output, $vars );
}

add_action( 'init', 'ide_check_create_project', 2 );

function ide_check_create_project() {
	if ( ! is_user_logged_in() ) {
		return;
	}
	if ( isset( $_GET['create_project'] ) ) {
		add_action( 'wp_enqueue_scripts', 'enqueue_enterprise_js' );
		add_filter( 'the_content', 'ide_create_project' );
		add_filter( 'jetpack_enable_open_graph', '__return_false', 99 );
	} elseif ( isset( $_GET['edit_project'] ) ) {
		$project_id     = absint( $_GET['edit_project'] );
		$current_user   = wp_get_current_user();
		$user_id        = $current_user->ID;
		$project_editor = apply_filters( 'ide_fes_edit_project_editor', false, $project_id );
		$ide_edit_hooks = false;
		// If we are getting project_editor true, then we don't need to check for the project owner
		if ( $project_editor ) {
			$ide_edit_hooks = true;
		} else {
			// Check if current user is project owner
			// $user_projects = get_user_meta($user_id, 'ide_user_projects', true);
			$post = get_post( $project_id );
			if ( ! empty( $post ) ) {
				if ( $user_id == $post->post_author ) {
					$ide_edit_hooks = true;
				}
			}
		}
		// If current user can edit project using FES, the attach edit action functions
		if ( $ide_edit_hooks ) {
			add_filter( 'the_content', 'ide_edit_project' );
			add_action( 'wp_enqueue_scripts', 'enqueue_enterprise_js' );
		}
		add_filter( 'jetpack_enable_open_graph', '__return_false', 99 );
	} elseif ( isset( $_GET['export_project'] ) ) {
		$project_id = get_post_meta( $_GET['export_project'], 'ign_project_id', true );
		if ( $project_id > 0 ) {
			$force_download = ID_Member::export_members( $project_id, true );
		}
	}
}

function ide_create_project( $content ) {
	if ( has_shortcode( $content, 'idc_dashboard' ) || has_shortcode( $content, 'memberdeck_dashboard' ) ) {
		$content = id_submissionForm();
	}
	return $content;
}

function ide_edit_project( $content ) {
	$post_id = absint( $_GET['edit_project'] );
	if ( isset( $post_id ) && $post_id > 0 ) {
		$post_status = get_post_status( $post_id );
		$permalink   = get_permalink( $post_id );
		$status_open = '<div class="ignitiondeck"><p class="notification green">';
		$status      = '';
		$bswp        = '';
		if ( strtoupper( $post_status ) == 'DRAFT' ) {
			$status .= '<strong>' . sprintf( __( 'Your project is currently saved as a draft. You can see a preview %1$s%2$s%3$s here%4$s', 'ignitiondeck' ), '<a title="View Project" href="', $permalink, '&preview=1">', '</a>.' ) . '</strong><br/>';
			$status .= __( 'You can visit this page at any time in order to continue editing your project.', 'ignitiondeck' );
		}
		if ( strtoupper( $post_status ) == 'PENDING' ) {
			$status .= '<strong>' . sprintf( __( 'Your project has been submitted and is awaiting review. You can see a preview %1$s%2$s%3$s here%4$s', 'ignitiondeck' ), '<a title="View Project" href="', $permalink, '&preview=1">', '</a>.' ) . '</strong><br/>';
			$status .= __( 'You can visit this page at any time in order to continue editing your project.', 'ignitiondeck' );
		} elseif ( strtoupper( $post_status ) == 'PUBLISH' ) {
			$status .= '<strong>' . sprintf( __( 'Your project is live. You can view it %1$s%2$s%3$s here%4$s', 'ignitiondeck' ), '<a title="View Project" href="', $permalink, '">', '</a>.' ) . '</strong><br/>';
			$status .= __( 'You may continue to add levels or edit content available to you on this screen.', 'ignitiondeck' );

			ob_start();
			do_action('ignitiondeck_share_creator_project_page', $post_id);
			$bswp = ob_get_clean();
		}
		$status_close = '</p></div>';
				
		$content      = $status_open . apply_filters( 'ide_project_edit_status', $status, $post_id ) . $status_close . $bswp .  id_submissionForm( $post_id );
	}
	return $content;
}

add_action( 'transition_post_status', 'ide_transition_post_status', 10, 3 );

function ide_transition_post_status( $new_status, $previous_status, $post ) {
	if ( empty( $post ) ) {
		// need a post to get ID
		return;
	}

	$post_id = $post->ID;
	if ( $post_id <= 0 ) {
		// we need a post ID to get postmeta
		return;
	}
	if ( $post->post_type == 'ignition_product' ) {
		// project here
		do_action( 'id_transition_project_status', $new_status, $previous_status, $post );
		if ( $new_status !== $previous_status ) {
			do_action( 'id_project_to_' . $new_status, $post );
		}
	}
}

add_action( 'ide_fes_submit', 'ide_fes_clear_caches', 99, 3 );

function ide_fes_clear_caches( $post_id, $project_id, $vars ) {
	do_action( 'save_post_ignition_product', $post_id );
}

function enqueue_enterprise_js() {
	wp_register_script( 'fes', plugins_url( 'js/fes-min.js', __FILE__ ) );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'fes' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_register_style( 'id-datepicker', plugins_url( 'ign_metabox/idcf_jquery_ui-min.css', __FILE__ ) );
	wp_enqueue_style( 'id-datepicker' );
}

function set_user_projects( $post_id, $user_id = null ) {
	$post = get_post( $post_id );
	if ( isset( $post ) ) {
		$post_type = $post->post_type;
		if ( $post_type == 'ignition_product' ) {
			if ( empty( $user_id ) ) {
				$user_id = $post->post_author;
			} else {
				$user_id = 1;
			}
			if ( isset( $user_id ) ) {
				$user_projects = get_user_meta( $user_id, 'ide_user_projects', true );
				if ( ! empty( $user_projects ) ) {
					$user_projects = unserialize( $user_projects );
					if ( is_array( $user_projects ) ) {
						$user_projects[] = $post_id;
						$user_projects   = array_unique( $user_projects );
					} else {
						$user_projects = array( $post_id );
					}
				} else {
					$user_projects = array( $post_id );
				}
				$new_record = serialize( $user_projects );
				update_user_meta( $user_id, 'ide_user_projects', $new_record );
			}
		}
	}
}

add_action( 'save_post', 'set_user_projects', 500 );

add_action( 'wp', 'ide_use_default_project_page' );

function ide_use_default_project_page() {
	$theme = wp_get_theme();
	if ( ! empty( $theme ) ) {
		$author = strtolower( $theme->get( 'Author' ) );
		$name   = strtolower( $theme->get( 'Name' ) );
		$hijack = true;
		if ( $author == 'ignitiondeck' && $name !== 'gig-wp' ) {
			$hijack = false;
		}
		if ( $hijack ) {
			global $post;
			if ( isset( $post ) ) {
				$post_id = $post->ID;
				$content = $post->post_content;
				if ( $post->post_type == 'ignition_product' ) {
					add_filter( 'the_content', 'ide_default_shortcode' );
				}
			}
		}
	}
}

function ide_default_shortcode( $content ) {
	global $post;
	$post_id    = $post->ID;
	$project_id = get_post_meta( $post_id, 'ign_project_id', true );
	$content    = do_shortcode( '[project_page_complete product="' . $project_id . '"]' );
	return $content;
}

add_action( 'wp', 'ide_check_show_preview' );

function ide_check_show_preview() {
	global $post;
	if ( isset( $post ) ) {
		$post_id = $post->ID;
		if ( isset( $post_id ) ) {
			if ( is_user_logged_in() ) {
				$current_user  = wp_get_current_user();
				$user_id       = $current_user->ID;
				$user_projects = get_user_meta( $user_id, 'ide_user_projects', true );
				if ( ! empty( $user_projects ) ) {
					$user_projects = unserialize( $user_projects );
					if ( in_array( $post_id, $user_projects ) ) {
						//add_filter('pre_get_posts', 'ide_show_preview');
					}
				}
			}
		}
	}
}

//add_action('pre_get_posts', 'ide_show_preview');

function ide_show_preview_old( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_singular() ) {
		if ( isset( $_GET['p'] ) ) {
			$post_id = $_GET['p'];
		}
	}
	if ( isset( $post_id ) ) {
		if ( is_user_logged_in() ) {
			$current_user  = wp_get_current_user();
			$user_id       = $current_user->ID;
			$user_projects = get_user_meta( $user_id, 'ide_user_projects', true );
			if ( ! empty( $user_projects ) ) {
				$user_projects = unserialize( $user_projects );
				if ( in_array( $post_id, $user_projects ) ) {
					$query->set( 'post_status', 'publish, draft' );
					add_filter( 'posts_results', 'test_some_stuff' );
				}
			}
		}
	}
	return $query;
}

add_filter( 'posts_results', 'ide_show_preview' );

function ide_show_preview( $posts ) {
	if ( isset( $posts ) ) {
		if ( is_main_query() && ! is_admin() && is_singular() ) {
			if ( ! empty( $posts ) ) {
				$post = $posts[0];
				if ( $post->post_type == 'ignition_product' ) {
					$post_id = $post->ID;
				}
			}
		}
	}
	if ( isset( $post_id ) ) {
		if ( is_user_logged_in() ) {
			$current_user  = wp_get_current_user();
			$user_id       = $current_user->ID;
			$user_projects = get_user_meta( $user_id, 'ide_user_projects', true );
			if ( ! empty( $user_projects ) ) {
				$user_projects = unserialize( $user_projects );
				if ( in_array( $post_id, $user_projects ) ) {
					$posts[0]->post_status = 'publish';
				}
			}
		}
	}
	return $posts;
}

/* Start Tabs */

/* Backer Profile */

add_action( 'md_profile_extratabs', 'ide_backer_profile_tab', 1 );

function ide_backer_profile_tab() {
	global $permalink_structure;
	if ( empty( $permalink_structure ) ) {
		$prefix = '&';
	} else {
		$prefix = '?';
	}
	$current_user = wp_get_current_user();
	$user_id      = $current_user->ID;
	if ( isset( $_GET[ apply_filters( 'idc_backer_profile_slug', 'backer_profile' ) ] ) ) {
		$profile = absint( $_GET[ apply_filters( 'idc_backer_profile_slug', 'backer_profile' ) ] );
	}
	echo '<li class="dashtab backer_profile' . ( isset( $profile ) && $profile == $user_id ? ' active' : '' ) . '"><a href="' . md_get_durl() . $prefix . apply_filters( 'idc_backer_profile_slug', 'backer_profile' ) . '=' . $user_id . '">' . __( 'Backer Profile', 'ignitiondeck' ) . '</a></li>';
}

add_action( 'init', 'ide_backer_profile' );

function ide_backer_profile() {
	if ( isset( $_GET[ apply_filters( 'idc_backer_profile_slug', 'backer_profile' ) ] ) ) {
		$profile = absint( $_GET[ apply_filters( 'idc_backer_profile_slug', 'backer_profile' ) ] );
		if ( isset( $profile ) && $profile > 0 ) {
			add_filter( 'the_content', 'ide_backer_profile_display' );
			add_filter( 'the_title', 'ide_backer_profile_title', 10, 2 );
			add_action( 'wp_head', 'ide_backer_profile_og' );
			add_filter( 'wp_title', 'ide_backer_profile_tab_title', 10, 2 );
		}
	}
}

function ide_backer_profile_display( $content ) {
	// we should really turn this into a template
	$content = '';
	if ( isset( $_GET[ apply_filters( 'idc_backer_profile_slug', 'backer_profile' ) ] ) ) {
		$profile = absint( $_GET[ apply_filters( 'idc_backer_profile_slug', 'backer_profile' ) ] );
	}
	$user = get_user_by( 'id', $profile );
	//$name = $user->user_firstname.' '.$user->user_lastname;
	$name         = apply_filters( 'ide_profile_name', $user->display_name, $user );
	$twitter_link = apply_filters( 'ide_profile_twitter_url', get_user_meta( $profile, 'twitter', true ), $user );
	$fb_link      = apply_filters( 'ide_profile_fb_url', get_user_meta( $profile, 'facebook', true ), $user );
	$google_link  = apply_filters( 'ide_profile_google_url', get_user_meta( $profile, 'google', true ), $user );
	$website_link = apply_filters( 'ide_profile_website_url', $user->user_url, $user );
	ob_start();
	do_action( 'ide_before_backer_profile' );
	$www = ob_get_clean();
	$content .= $www.'<div class="ignitiondeck backer_profile">';
	$content .= '<div class="backer_info">';
	$content .= '<div class="backer_avatar">' . apply_filters( 'ide_profile_avatar', get_avatar( $profile, 70 ) ) . '</div>';
	$content .= '<div class="backer_title"><h3>' . apply_filters( 'ide_backer_name', $name, $user ) . '</h3>';
	$content .= '<p>' . wpautop( apply_filters( 'ide_profile_description', $user->description, $user ) ) . '</p></div></div>';
	// this would be so much more efficient if we attached a project ID to an mdid order or
	// to a pay info id
	if ( class_exists( 'ID_Member_Order' ) ) {
		$misc   = ' WHERE user_id = "' . $profile . '"';
		$misc   = ' WHERE user_id = "' . $profile . '"';
		$orders = ID_Member_Order::get_orders( null, null, $misc );
		if ( ! empty( $orders ) ) {
			$mdid_orders = array();
			foreach ( $orders as $order ) {
				$mdid_order = mdid_by_orderid( $order->id );
				if ( ! empty( $mdid_order ) ) {
					$mdid_orders[] = $mdid_order;
				}
			}
			if ( ! empty( $mdid_orders ) ) {
				$id_orders = array();
				foreach ( $mdid_orders as $payment ) {
					$order     = new ID_Order( $payment->pay_info_id );
					$the_order = $order->get_order();
					if ( ! empty( $the_order ) ) {
						$id_orders[] = $the_order;
					}
				}
				$id_orders = apply_filters( 'ide_backer_profile_projects', $id_orders, $user );
				if ( ! empty( $id_orders ) ) {
					$listed        = array();
					$order_content = '<div class="cf"> </div><ul class="backer_projects">';
					foreach ( $id_orders as $id_order ) {
						$project     = new ID_Project( $id_order->product_id );
						$the_project = $project->the_project();
						if ( ! empty( $the_project ) && ! in_array( $id_order->product_id, $listed ) ) {
							$post_id = $project->get_project_postid();
							$url     = getProjectURLfromType( $id_order->product_id );
							$image   = ID_Project::get_project_thumbnail( $post_id, 'id_profile_image' );
							if ( empty( $image ) ) {
								$image = idcf_project_placeholder_image( 'thumb' );
							}
							$deck       = new Deck( $id_order->product_id );
							$mini_deck  = $deck->mini_deck();
							$closed     = $project->project_closed();
							$successful = $mini_deck->successful;
							ob_start();
							do_action( 'ide_before_backer_item', $id_order, $post_id );
							$ob_contenta = ob_get_contents();
							ob_end_clean();
							$order_content .= $ob_contenta;
							$order_content .= '<li class="backer_project_mini"><div class="backer_wrapper"><div class="inner_wrapper"><a href="' . $url . '">';
							ob_start();
							do_action( 'ide_above_backer_item', $id_order, $post_id );
							$ob_contentb = ob_get_contents();
							ob_end_clean();
							$order_content .= $ob_contentb;
							if ( isset( $image ) ) {
								$order_content .= '<a href="' . $url . '" class="backer_project_image" style="background-image: url(' . $image . ');"></a>';
							}
							if ( $mini_deck->end_type !== 'open' ) {
								$order_content .= '<div class="backers_days_left">' . ( ! $closed ? $mini_deck->days_left . ' ' . __( 'days to go', 'ignitiondeck' ) : ( $successful ? __( 'Successful', 'ignitiondeck' ) : __( 'Ended', 'ignitiondeck' ) ) ) . '</div>';
							}
							$order_content .= '<span class="backer_project_title"><a href="' . $url . '">' . get_the_title( $post_id ) . '</a></span>';
							$order_content .= '<div class="backers_funded">' . $mini_deck->p_current_sale . ' ' . __( 'Raised', 'ignitiondeck' ) . '</div>';
							$order_content .= '<a href="' . $url . '"><div class="backers_hover_content">';
							$order_content .= '<span class="backer_project_text">' . stripslashes( html_entity_decode( $project->short_description() ) ) . '</span></div></a>';
							ob_start();
							do_action( 'ide_below_backer_item', $id_order, $post_id );
							$ob_contentc = ob_get_contents();
							ob_end_clean();
							$order_content .= $ob_contentc;
							$order_content .= '</a></div></div></li>';
							ob_start();
							do_action( 'ide_after_backer_item', $id_order, $post_id );
							$ob_contentd = ob_get_contents();
							ob_end_clean();
							$order_content .= $ob_contentd;
							$listed[]       = $id_order->product_id;
						}
					}
					$order_content .= '</ul>';
					$order_count    = count( $listed );
				}
			}
		}
		$content .= ( isset( $order_count ) && $order_count > 0 ? '<div class="backer_data">' . do_action( 'ide_before_backer_data' ) . '<p class="backer_supported">' . __( 'Backed', 'ignitiondeck' ) . '<span class="order_count">' . $order_count . '</span> ' . __( 'projects', 'ignitiondeck' ) . '</p>' : '<div class="backer_data">' );
		$content .= '<p class="backer_joined">' . __( 'Joined', 'ignitiondeck' ) . ' ' . date( idf_date_format(), strtotime( $user->user_registered ) ) . '</p>
	<div class="id-backer-links">' . ( ! empty( $website_link ) ? '<a href="' . $website_link . '" class="website" title="' . __( 'Website', 'ignitiondeck' ) . '">' . __( 'Website', 'ignitiondeck' ) . '</a>' : '' ) . '' . ( ! empty( $twitter_link ) ? '<a href="' . $twitter_link . '" class="twitter" title="' . __( 'Twitter', 'ignitiondeck' ) . '">' . __( 'Twitter', 'ignitiondeck' ) . '</a>' : '' ) . ( ! empty( $fb_link ) ? '<a href="' . $fb_link . '" class="facebook" title="' . __( 'Facebook', 'ignitiondeck' ) . '">' . __( 'Facebook', 'ignitiondeck' ) . '</a>' : '' ) . ( ! empty( $google_link ) ? '<a href="' . $google_link . '" class="googleplus" title="' . __( 'Google Plus', 'ignitiondeck' ) . '">' . __( 'Google Plus', 'ignitiondeck' ) . '</a>' : '' ) . '</div>' . do_action( 'ide_after_backer_data' ) . '</div>';
		$content .= ( isset( $order_content ) ? $order_content : '' );
		$content .= '</div>';
		do_action( 'ide_after_backer_profile' );
	}
	return $content;
}

function ide_backer_profile_title( $title, $id = null ) {
	if ( ! function_exists( 'md_get_did' ) ) {
		return $title;
	}

	$did = md_get_did();

	if ( empty( $did ) ) {
		return $title;
	}

	if ( $did == $id ) {
		$user_id = absint( $_GET[ apply_filters( 'idc_backer_profile_slug', 'backer_profile' ) ] );
		$user    = get_user_by( 'id', $user_id );
		if ( ! empty( $user ) ) {
			$display = $user->display_name;
			$title   = $display;
		}
	}
	return $title;
}

function ide_backer_profile_og() {
	$user_id = absint( $_GET[ apply_filters( 'idc_backer_profile_slug', 'backer_profile' ) ] );
	$user    = get_user_by( 'id', $user_id );
	$meta    = null;
	if ( ! empty( $user ) ) {
		$display     = $user->display_name;
		$meta       .= '<meta property="og:title" content="' . $display . '&rsquo;s ' . __( 'Backer Profile', 'ignitiondeck' ) . '" />';
		$meta       .= '<meta property="og:url" content="http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '" />';
		$avatar_data = get_avatar_data( $user_id );
		if ( ! empty( $avatar_data['url'] ) ) {
			$meta = '<meta property="og:image" content="' . $avatar_data['url'] . '" />';
		}
		$current_site = get_option( 'blogname' );
		if ( ! empty( $current_site ) ) {
			$meta .= '<meta property="og:site_name" content="' . $current_site . '" />';
		}
	}
	if ( ! empty( $meta ) ) {
		echo $meta;
	}
}

function ide_backer_profile_tab_title( $title, $sep ) {
	$user_id = absint( $_GET[ apply_filters( 'idc_backer_profile_slug', 'backer_profile' ) ] );
	$user    = get_user_by( 'id', $user_id );
	if ( ! empty( $user ) ) {
		$display = $user->display_name;
		$title   = $display;
	}
	return $title . ' ' . $sep . ' ' . get_bloginfo( 'name' );
}

/* Creator Profile */

add_action( 'md_profile_extratabs', 'ide_creator_profile_tab', 1 );

function ide_creator_profile_tab() {
	global $permalink_structure;
	if ( current_user_can( 'create_edit_projects' ) ) {
		if ( empty( $permalink_structure ) ) {
			$prefix = '&';
		} else {
			$prefix = '?';
		}
		$current_user = wp_get_current_user();
		$user_id      = $current_user->ID;
		if ( isset( $_GET[ apply_filters( 'idc_creator_profile_slug', 'creator_profile' ) ] ) ) {
			$profile = absint( $_GET[ apply_filters( 'idc_creator_profile_slug', 'creator_profile' ) ] );
		}
		echo '<li class="dashtab creator_profile' . ( isset( $profile ) && $profile == $user_id ? ' active' : '' ) . '"><a href="' . md_get_durl() . $prefix . apply_filters( 'idc_creator_profile_slug', 'creator_profile' ) . '=' . $user_id . '">' . __( 'Creator Profile', 'ignitiondeck' ) . '</a></li>';
	}
}

add_action( 'init', 'ide_creator_profile_init' );

function ide_creator_profile_init() {
	if ( isset( $_GET[ apply_filters( 'idc_creator_profile_slug', 'creator_profile' ) ] ) ) {
		$profile = absint( $_GET[ apply_filters( 'idc_creator_profile_slug', 'creator_profile' ) ] );
		if ( isset( $profile ) && $profile > 0 ) {
			add_filter( 'the_content', 'ide_creator_profile_display' );
			add_filter( 'the_title', 'ide_creator_profile_title', 10, 2 );
			add_action( 'wp_head', 'ide_creator_profile_og' );
			add_filter( 'wp_title', 'ide_creator_profile_tab_title', 10, 2 );
		}
	}
}

function ide_creator_profile_display( $content ) {
	// we should really turn this into a template
	$content = '';
	if ( isset( $_GET[ apply_filters( 'idc_creator_profile_slug', 'creator_profile' ) ] ) ) {
		$profile = absint( $_GET[ apply_filters( 'idc_creator_profile_slug', 'creator_profile' ) ] );
	}
	$user = get_user_by( 'id', $profile );
	//$name = $user->user_firstname.' '.$user->user_lastname;
	$name         = apply_filters( 'ide_profile_name', $user->display_name, $user );
	$twitter_link = apply_filters( 'ide_profile_twitter_url', get_user_meta( $profile, 'twitter', true ), $user );
	$fb_link      = apply_filters( 'ide_profile_fb_url', get_user_meta( $profile, 'facebook', true ), $user );
	$google_link  = apply_filters( 'ide_profile_google_url', get_user_meta( $profile, 'google', true ), $user );
	$website_link = apply_filters( 'ide_profile_website_url', $user->user_url, $user );
	ob_start();
	do_action( 'ide_before_creator_profile' );
	$ob_before_cp = ob_get_contents();
	ob_end_clean();
	$content .= $ob_before_cp;
	ob_start();
	do_action( 'ide_above_creator_info' );
	$ob_above_ci = ob_get_contents();
	ob_end_clean();
	$content .= $ob_above_ci;
	$content .= '<div class="ignitiondeck backer_profile">';
	$content .= '<div class="backer_info">';
	$content .= '<div class="backer_avatar">' . apply_filters( 'ide_profile_avatar', get_avatar( $profile, 70 ) ) . '</div>';
	$content .= '<div class="backer_title"><h3>' . apply_filters( 'ide_creator_name', $name, $user ) . '</h3>';
	$content .= '<p>' . wpautop( apply_filters( 'ide_profile_description', $user->description, $user ) ) . '</p></div></div>';
	ob_start();
	do_action( 'ide_below_creator_info' );
	$ob_after_ci = ob_get_contents();
	ob_end_clean();
	$content         .= $ob_after_ci;
	$creator_args     = array(
		'post_type'      => 'ignition_product',
		'post_status'    => 'publish',
		'author'         => $profile,
		'posts_per_page' => -1,
	);
	$created_projects = apply_filters( 'id_creator_projects', get_posts( apply_filters( 'id_creator_args', $creator_args ) ) );
	if ( ! empty( $created_projects ) ) {
		$order_content = '<div class="cf"> </div><ul class="backer_projects">';
		foreach ( $created_projects as $created_project ) {
			$project_id  = get_post_meta( $created_project->ID, 'ign_project_id', true );
			$project     = new ID_Project( $project_id );
			$the_project = $project->the_project();
			if ( ! empty( $the_project ) ) {
				$post_id    = $created_project->ID;
				$deck       = new Deck( $project_id );
				$mini_deck  = $deck->mini_deck();
				$closed     = $project->project_closed();
				$successful = $mini_deck->successful;
				$url        = get_permalink( $post_id );
				$image      = ID_Project::get_project_thumbnail( $post_id, 'id_profile_image' );
				if ( empty( $image ) ) {
					$image = idcf_project_placeholder_image( 'thumb' );
				}
				ob_start();
				do_action( 'ide_before_creator_item', $post_id );
				$ob_contenta = ob_get_contents();
				ob_end_clean();
				$order_content .= $ob_contenta;
				$order_content .= '<li class="backer_project_mini"><div class="backer_wrapper"><div class="inner_wrapper">';
				ob_start();
				do_action( 'ide_above_creator_item', $post_id );
				$ob_contentb = ob_get_contents();
				ob_end_clean();
				$order_content .= $ob_contentb;
				if ( isset( $image ) ) {
					$order_content .= '<a href="' . $url . '" class="backer_project_image" style="background-image: url(' . $image . ');"></a>';
				}
				if ( $mini_deck->end_type !== 'open' ) {
					$order_content .= '<div class="backers_days_left">' . ( ! $closed ? $mini_deck->days_left . ' ' . __( 'days to go', 'ignitiondeck' ) : ( $successful ? __( 'Successful', 'ignitiondeck' ) : __( 'Ended', 'ignitiondeck' ) ) ) . '</div>';
				}
						$order_content .= '<span class="backer_project_title"><a href="' . $url . '">' . get_the_title( $post_id ) . '</a></span>';
						$order_content .= '<div class="backers_funded">' . $mini_deck->p_current_sale . ' ' . __( 'Raised', 'ignitiondeck' ) . '</div>';
						$order_content .= '<a href="' . $url . '"><div class="backers_hover_content">';
						$order_content .= '<span class="backer_project_text">' . stripslashes( html_entity_decode( $project->short_description() ) ) . '</span></div></a>';
						ob_start();
						do_action( 'ide_below_creator_item', $post_id );
						$ob_contentc = ob_get_contents();
						ob_end_clean();
						$order_content .= $ob_contentc;
						$order_content .= '</div></div></li>';
						ob_start();
						do_action( 'ide_after_creator_item', $post_id );
						$ob_contentd = ob_get_contents();
						ob_end_clean();
						$order_content .= $ob_contentd;
			}
		}
			$order_content .= '</ul>';
			$order_count    = count( $created_projects );

		if ( isset( $order_count ) && $order_count > 0 ) {
			$content .= '<div class="backer_data">';
			ob_start();
			do_action( 'ide_before_creator_data' );
			$before_creator_data = ob_get_contents();
			ob_end_clean();
			$content .= $before_creator_data;
			$content .= '<p class="backer_supported"><span class="order_count">' . $order_count . '</span> ' . __( 'Projects Created', 'ignitiondeck' ) . '</p>';
		} else {
			$content .= '<div class="backer_data">';
		}
		$content .= '<p class="backer_joined">' . __( 'Joined', 'ignitiondeck' ) . ' ' . date( idf_date_format(), strtotime( $user->user_registered ) ) . '</p>' .
					'<div class="id-backer-links">' .
						( ! empty( $website_link ) ? '<a href="' . $website_link . '" class="website" title="' . __( 'Website', 'ignitiondeck' ) . '">' . __( 'Website', 'ignitiondeck' ) . '</a>' : '' ) . '' .
						( ! empty( $twitter_link ) ? '<a href="' . $twitter_link . '" class="twitter" title="' . __( 'Twitter', 'ignitiondeck' ) . '">' . __( 'Twitter', 'ignitiondeck' ) . '</a>' : '' ) .
						( ! empty( $fb_link ) ? '<a href="' . $fb_link . '" class="facebook" title="' . __( 'Facebook', 'ignitiondeck' ) . '">' . __( 'Facebook', 'ignitiondeck' ) . '</a>' : '' ) .
						( ! empty( $google_link ) ? '<a href="' . $google_link . '" class="googleplus" title="' . __( 'Google Plus', 'ignitiondeck' ) . '">' . __( 'Google Plus', 'ignitiondeck' ) . '</a>' : '' ) .
					'</div>';
		ob_start();
		do_action( 'ide_after_backer_data' );
		$after_backer_data = ob_get_contents();
		ob_end_clean();
		$content .= $after_backer_data;

		$content .= '</div>';

		$content .= ( isset( $order_content ) ? $order_content : '' );
		$content .= '</div>';
	}
	ob_start();
	do_action( 'ide_after_creator_profile' );
	$ob_after_cp = ob_get_contents();
	ob_end_clean();
	$content .= $ob_after_cp;
	return $content;
}

function ide_creator_profile_title( $title, $id = null ) {
	$dash_settings = get_option( 'md_dash_settings' );
	if ( ! empty( $dash_settings ) ) {
		if ( ! is_array( $dash_settings ) ) {
			$dash_settings = unserialize( $dash_settings );
		}
		$durl = $dash_settings['durl'];
		if ( $durl == $id ) {
			$user_id = absint( $_GET[ apply_filters( 'idc_creator_profile_slug', 'creator_profile' ) ] );
			$user    = get_user_by( 'id', $user_id );
			if ( ! empty( $user ) ) {
				$display  = $user->display_name;
				$lastchar = substr( $display, -1 );
				if ( strtolower( $lastchar ) == 's' ) {
					$title = $display . __( "' Projects", 'ignitiondeck' );
				} else {
					$title = $display . __( "'s Projects", 'ignitiondeck' );
				}
			}
		}
	}

	return $title;
}

function ide_creator_profile_og() {
	$user_id = absint( $_GET[ apply_filters( 'idc_creator_profile_slug', 'creator_profile' ) ] );
	$user    = get_user_by( 'id', $user_id );
	$meta    = null;
	if ( ! empty( $user ) ) {
		$display = $user->display_name;
		if ( ! empty( $display ) ) {
			$meta .= '<meta property="og:title" content="' . $display . '&rsquo;s ' . __( 'Creator Profile', 'ignitiondeck' ) . '" />';
		}
		$avatar_data = get_avatar_data( $user_id );
		if ( ! empty( $avatar_data['url'] ) ) {
			$meta = '<meta property="og:image" content="' . $avatar_data['url'] . '" />';
		}
		$meta .= '<meta property="og:url" content="http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '" />';

		$current_site = get_option( 'blogname' );
		if ( ! empty( $current_site ) ) {
			$meta .= '<meta property="og:site_name" content="' . $current_site . '" />';
		}
	}
	if ( ! empty( $meta ) ) {
		echo $meta;
	}
}

function ide_creator_profile_tab_title( $title, $sep ) {
	$user_id = absint( $_GET[ apply_filters( 'idc_creator_profile_slug', 'creator_profile' ) ] );
	$user    = get_user_by( 'id', $user_id );
	if ( ! empty( $user ) ) {
		$display = $user->display_name;
		$title   = $display . __( "'s Projects", 'ignitiondeck' );
	}
	return $title . ' ' . $sep . ' ' . get_bloginfo( 'name' );
}

function ide_creator_profile_projects( $projects, $user ) {
	$args  = array(
		'author'      => $user->ID,
		'post_type'   => 'ignition_product',
		'post_status' => 'publish',
	);
	$posts = get_posts( $args );
	if ( ! empty( $posts ) ) {
		$projects = $posts;
	}
	return $projects;
}

/* End Tabs */

add_filter( 'idc_order_level_title', 'ide_add_project_order_info', 10, 2 );

function ide_add_project_order_info( $title, $last_order ) {
	$order_id   = $last_order->id;
	$mdid_order = mdid_by_orderid( $order_id );
	if ( ! empty( $mdid_order ) ) {
		$pay_id    = $mdid_order->pay_info_id;
		$id_order  = new ID_Order( $pay_id );
		$get_order = $id_order->get_order();
		if ( ! empty( $get_order ) ) {
			$project_id = $get_order->product_id;
			$project    = new ID_Project( $project_id );
			$post_id    = $project->get_project_postid();
			if ( $post_id > 0 && ! is_id_pro() ) {
				$project_title = get_the_title( $post_id );
				$title         = $project_title . ': ' . $title;
			}
		}
	}
	return $title;
}

add_filter( 'idc_order_post', 'ide_add_project_order_post', 10, 2 );

function ide_add_project_order_post( $last_order ) {
	$igPostId = false;
	$order_id   = $last_order->id;
	$mdid_order = mdid_by_orderid( $order_id );
	if ( ! empty( $mdid_order ) ) {
		$pay_id    = $mdid_order->pay_info_id;
		$id_order  = new ID_Order( $pay_id );
		$get_order = $id_order->get_order();
		if ( ! empty( $get_order ) ) {
			$project    = new ID_Project( $get_order->product_id );
			$igPostId 	= $project->get_project_postid();
		}
	}
	return $igPostId;
}

add_filter( 'idc_order_level_url', 'ide_add_project_order_url', 10, 2 );

function ide_add_project_order_url( $url, $last_order ) {
	$order_id   = $last_order->id;
	$mdid_order = mdid_by_orderid( $order_id );
	if ( ! empty( $mdid_order ) ) {
		$pay_id    = $mdid_order->pay_info_id;
		$id_order  = new ID_Order( $pay_id );
		$get_order = $id_order->get_order();
		if ( ! empty( $get_order ) ) {
			$project_id = $get_order->product_id;
			$project    = new ID_Project( $project_id );
			$post_id    = $project->get_project_postid();
			if ( $post_id > 0 ) {
				$url = get_permalink( $post_id );
			}
		}
	}
	return $url;
}

add_filter( 'idc_order_level_thumbnail', 'ide_add_project_order_thumbnail', 10, 2 );

function ide_add_project_order_thumbnail( $thumbnail, $last_order ) {
	$order_id   = $last_order->id;
	$mdid_order = mdid_by_orderid( $order_id );
	if ( ! empty( $mdid_order ) ) {
		$pay_id    = $mdid_order->pay_info_id;
		$id_order  = new ID_Order( $pay_id );
		$get_order = $id_order->get_order();
		if ( ! empty( $get_order ) ) {
			$project_id = $get_order->product_id;
			$project    = new ID_Project( $project_id );
			$post_id    = $project->get_project_postid();
			if ( $post_id > 0 ) {
				$thumbnail = ID_Project::get_project_thumbnail( $post_id );
			}
		}
	}
	return $thumbnail;
}

function idcf_filter_wp_kses( $allowedtags, $context ) {
	$allowedtags['iframe'] = array(
		'src'         => true,
		'width'       => true,
		'height'      => true,
		'frameborder' => true,
		'scrolling'   => true,
	);
	$allowedtags['embed']  = array(
		'src'    => true,
		'width'  => true,
		'height' => true,
		'type'   => true,
	);
	$allowedtags['object'] = array(
		'src'    => true,
		'width'  => true,
		'height' => true,
		'type'   => true,
	);
	return $allowedtags;
}

function ide_auto_approve_status() {
	$enterprise_settings = get_option( 'idc_enterprise_settings' );
	$auto_approve        = ( ! empty( $enterprise_settings['auto_approve'] ) ? absint( $enterprise_settings['auto_approve'] ) : 0 );
	return $auto_approve;
}

add_action( 'idc_gateway_settings_after', 'ide_process_project_authorizations' );

function ide_process_project_authorizations() {
	if ( function_exists( 'is_idc_free' ) && ! is_idc_free() ) {
		include_once 'templates/admin/_projectPreauthSelect.php';
	}
}

add_action( 'ide_after_enterprise_settings', 'ide_project_approval_settings' );

function ide_project_approval_settings() {
	$enterprise_settings = get_option( 'idc_enterprise_settings' );
	include_once 'templates/admin/_projectApprovalSettings.php';
}

add_filter( 'idc_enterprise_settings', 'ide_save_project_approval_settings' );

function ide_save_project_approval_settings( $settings ) {
	if ( isset( $_POST['enterprise_submit'] ) ) {
		$auto_approve             = ( isset( $_POST['auto_approve'] ) ? absint( $_POST['auto_approve'] ) : '' );
		$settings['auto_approve'] = $auto_approve;
	}
	return $settings;
}

add_filter( 'ide_project_fesubmit_status', 'ide_auto_approve_fesubmit' );

function ide_auto_approve_fesubmit( $status ) {
	if ( ide_auto_approve_status() ) {
		return 'publish';
	}
	return $status;
}

add_filter( 'gettext', 'ide_auto_approve_button_text', 10, 3 );

function ide_auto_approve_button_text( $translated_text, $text, $domain ) {
	if ( ide_auto_approve_status() ) {
		$domain_list = array( 'memberdeck', 'idf', 'ignitiondeck', 'fivehundred' );
		if ( in_array( $domain, $domain_list ) ) {
			if ( strpos( $text, 'Submit for Review' ) !== false ) {
				$translated_text = __( 'Publish Project', 'memberdeck' );
			}
		}
	}
	return $translated_text;
}

add_action( 'ide_after_enterprise_settings', 'ide_hide_failed_projects' );

function ide_hide_failed_projects() {
	$enterprise_settings = get_option( 'idc_enterprise_settings' );
	include_once 'templates/admin/_projectDisplaySettings.php';
}

add_filter( 'idc_enterprise_settings', 'ide_save_failed_project_settings' );

function ide_save_failed_project_settings( $settings ) {
	if ( isset( $_POST['enterprise_submit'] ) ) {
		$hide_failed             = ( isset( $_POST['hide_failed'] ) ? absint( $_POST['hide_failed'] ) : '' );
		$settings['hide_failed'] = $hide_failed;
	}
	return $settings;
}

add_filter( 'pre_get_posts', 'ide_filter_project_display' );

function ide_filter_project_display( $query ) {
	# this is a very slow query, so we need to set a flag
	if ( is_admin() ) {
		return;
	}
	# check for instances like dashboard
	$enterprise_settings = get_option( 'idc_enterprise_settings' );
	if ( ! empty( $enterprise_settings['hide_failed'] ) ) {
		if ( $query->is_single() ) {
			return;
		}
		if ( empty( $query->query['post_type'] ) ) {
			return;
		}
		if ( strpos( idf_current_url(), md_get_durl() ) !== false ) {
			return;
		}
		$post_type = $query->query['post_type'];
		if ( $post_type == 'ignition_product' ) {
			$exclude = array();
			$projects = ID_Project::get_all_projects();
			foreach($projects as $project) {
				$new_hdeck = new Deck($project->id);
				$hDeck = $new_hdeck->hDeck();
				if($hDeck->days_left==0 && $hDeck->end_type=='closed') {
					$project = new ID_Project($project->id);
					$post_id    = $project->get_project_postid();
					$successful = get_post_meta( $post_id, 'ign_project_success', true );
					$closed     = get_post_meta( $post_id, 'ign_project_closed', true );
					if ( $closed && empty( $successful ) ) {
						$exclude[] = $post_id;
					}
				}
			}
			$query->set( 'post__not_in', $exclude );
			/*
			$meta_query = array(
				array(
					'key'     => 'ign_project_failed',
					'value'   => true,
					'compare' => '!=',
					'type'    => 'BINARY',
				),
				'relation' => 'OR',
				array(
					'key'     => 'ign_project_failed',
					'compare' => 'NOT EXISTS',
				),
			);
			$query->set( 'meta_query', $meta_query );
			*/
		}
	}
}



function ig_sanitise_insert_post( $postarr, $wp_error = false ) {
    global $wpdb;
 
    $user_id = get_current_user_id();
 
    $defaults = array(
        'post_author'           => $user_id,
        'post_content'          => '',
        'post_content_filtered' => '',
        'post_title'            => '',
        'post_excerpt'          => '',
        'post_status'           => 'draft',
        'post_type'             => 'post',
        'comment_status'        => '',
        'ping_status'           => '',
        'post_password'         => '',
        'to_ping'               => '',
        'pinged'                => '',
        'post_parent'           => 0,
        'menu_order'            => 0,
        'guid'                  => '',
        'import_id'             => 0,
        'context'               => '',
    );
 
    $postarr = wp_parse_args( $postarr, $defaults );
 
    unset( $postarr['filter'] );
 
    $postarr = sanitize_post( $postarr, 'db' );
 
    // Are we updating or creating?
    $post_ID = 0;
    $update  = false;
    $guid    = $postarr['guid'];
 
    if ( ! empty( $postarr['ID'] ) ) {
        $update = true;
 
        // Get the post ID and GUID.
        $post_ID     = $postarr['ID'];
        $post_before = get_post( $post_ID );
        if ( is_null( $post_before ) ) {
            if ( $wp_error ) {
                return new WP_Error( 'invalid_post', __( 'Invalid post ID.' ) );
            }
            return 0;
        }
 
        $guid            = get_post_field( 'guid', $post_ID );
        $previous_status = get_post_field( 'post_status', $post_ID );
    } else {
        $previous_status = 'new';
    }
 
    $post_type = empty( $postarr['post_type'] ) ? 'post' : $postarr['post_type'];
 
    $post_title   = $postarr['post_title'];
    $post_content = $postarr['post_content'];
    $post_excerpt = $postarr['post_excerpt'];
    if ( isset( $postarr['post_name'] ) ) {
        $post_name = $postarr['post_name'];
    } elseif ( $update ) {
        // For an update, don't modify the post_name if it wasn't supplied as an argument.
        $post_name = $post_before->post_name;
    }
 
    $maybe_empty = 'attachment' !== $post_type
        && ! $post_content && ! $post_title && ! $post_excerpt
        && post_type_supports( $post_type, 'editor' )
        && post_type_supports( $post_type, 'title' )
        && post_type_supports( $post_type, 'excerpt' );
 
    /**
     * Filters whether the post should be considered "empty".
     *
     * The post is considered "empty" if both:
     * 1. The post type supports the title, editor, and excerpt fields
     * 2. The title, editor, and excerpt fields are all empty
     *
     * Returning a truthy value to the filter will effectively short-circuit
     * the new post being inserted, returning 0. If $wp_error is true, a WP_Error
     * will be returned instead.
     *
     * @since 3.3.0
     *
     * @param bool  $maybe_empty Whether the post should be considered "empty".
     * @param array $postarr     Array of post data.
     */
    if ( apply_filters( 'wp_insert_post_empty_content', $maybe_empty, $postarr ) ) {
        if ( $wp_error ) {
            return new WP_Error( 'empty_content', __( 'Content, title, and excerpt are empty.' ) );
        } else {
            return 0;
        }
    }
 
    
	$post_status = empty( $postarr['post_status'] ) ? 'draft' : $postarr['post_status'];
    if ( 'attachment' === $post_type && ! in_array( $post_status, array( 'inherit', 'private', 'trash', 'auto-draft' ), true ) ) {
        $post_status = 'inherit';
    }
 
    if ( ! empty( $postarr['post_category'] ) ) {
        // Filter out empty terms.
        $post_category = array_filter( $postarr['post_category'] );
    }
 
    // Make sure we set a valid category.
    if ( empty( $post_category ) || 0 == count( $post_category ) || ! is_array( $post_category ) ) {
        // 'post' requires at least one category.
        if ( 'post' == $post_type && 'auto-draft' != $post_status ) {
            $post_category = array( get_option( 'default_category' ) );
        } else {
            $post_category = array();
        }
    }
 
    /*
     * Don't allow contributors to set the post slug for pending review posts.
     *
     * For new posts check the primitive capability, for updates check the meta capability.
     */
    $post_type_object = get_post_type_object( $post_type );
 
    if ( ! $update && 'pending' === $post_status && ! current_user_can( $post_type_object->cap->publish_posts ) ) {
        $post_name = '';
    } elseif ( $update && 'pending' === $post_status && ! current_user_can( 'publish_post', $post_ID ) ) {
        $post_name = '';
    }
 
    /*
     * Create a valid post name. Drafts and pending posts are allowed to have
     * an empty post name.
     */
    if ( empty( $post_name ) ) {
        if ( ! in_array( $post_status, array( 'draft', 'pending', 'auto-draft' ) ) ) {
            $post_name = sanitize_title( $post_title );
        } else {
            $post_name = '';
        }
    } else {
        // On updates, we need to check to see if it's using the old, fixed sanitization context.
        $check_name = sanitize_title( $post_name, '', 'old-save' );
        if ( $update && strtolower( urlencode( $post_name ) ) == $check_name && get_post_field( 'post_name', $post_ID ) == $check_name ) {
            $post_name = $check_name;
        } else { // new post, or slug has changed.
            $post_name = sanitize_title( $post_name );
        }
    }
 
    /*
     * If the post date is empty (due to having been new or a draft) and status
     * is not 'draft' or 'pending', set date to now.
     */
    if ( empty( $postarr['post_date'] ) || '0000-00-00 00:00:00' == $postarr['post_date'] ) {
        if ( empty( $postarr['post_date_gmt'] ) || '0000-00-00 00:00:00' == $postarr['post_date_gmt'] ) {
            $post_date = current_time( 'mysql' );
        } else {
            $post_date = get_date_from_gmt( $postarr['post_date_gmt'] );
        }
    } else {
        $post_date = $postarr['post_date'];
    }
 
    // Validate the date.
    $mm         = substr( $post_date, 5, 2 );
    $jj         = substr( $post_date, 8, 2 );
    $aa         = substr( $post_date, 0, 4 );
    $valid_date = wp_checkdate( $mm, $jj, $aa, $post_date );
    if ( ! $valid_date ) {
        if ( $wp_error ) {
            return new WP_Error( 'invalid_date', __( 'Invalid date.' ) );
        } else {
            return 0;
        }
    }
 
    if ( empty( $postarr['post_date_gmt'] ) || '0000-00-00 00:00:00' == $postarr['post_date_gmt'] ) {
        if ( ! in_array( $post_status, get_post_stati( array( 'date_floating' => true ) ), true ) ) {
            $post_date_gmt = get_gmt_from_date( $post_date );
        } else {
            $post_date_gmt = '0000-00-00 00:00:00';
        }
    } else {
        $post_date_gmt = $postarr['post_date_gmt'];
    }
 
    if ( $update || '0000-00-00 00:00:00' == $post_date ) {
        $post_modified     = current_time( 'mysql' );
        $post_modified_gmt = current_time( 'mysql', 1 );
    } else {
        $post_modified     = $post_date;
        $post_modified_gmt = $post_date_gmt;
    }
 
    if ( 'attachment' !== $post_type ) {
        $now = gmdate( 'Y-m-d H:i:s' );
 
        if ( 'publish' === $post_status ) {
            if ( strtotime( $post_date_gmt ) - strtotime( $now ) >= MINUTE_IN_SECONDS ) {
                $post_status = 'future';
            }
        } elseif ( 'future' === $post_status ) {
            if ( strtotime( $post_date_gmt ) - strtotime( $now ) < MINUTE_IN_SECONDS ) {
                $post_status = 'publish';
            }
        }
    }
 
    // Comment status.
    if ( empty( $postarr['comment_status'] ) ) {
        if ( $update ) {
            $comment_status = 'closed';
        } else {
            $comment_status = get_default_comment_status( $post_type );
        }
    } else {
        $comment_status = $postarr['comment_status'];
    }
 
    // These variables are needed by compact() later.
    $post_content_filtered = $postarr['post_content_filtered'];
    $post_author           = isset( $postarr['post_author'] ) ? $postarr['post_author'] : $user_id;
    $ping_status           = empty( $postarr['ping_status'] ) ? get_default_comment_status( $post_type, 'pingback' ) : $postarr['ping_status'];
    $to_ping               = isset( $postarr['to_ping'] ) ? sanitize_trackback_urls( $postarr['to_ping'] ) : '';
    $pinged                = isset( $postarr['pinged'] ) ? $postarr['pinged'] : '';
    $import_id             = isset( $postarr['import_id'] ) ? $postarr['import_id'] : 0;
 
    /*
     * The 'wp_insert_post_parent' filter expects all variables to be present.
     * Previously, these variables would have already been extracted
     */
    if ( isset( $postarr['menu_order'] ) ) {
        $menu_order = (int) $postarr['menu_order'];
    } else {
        $menu_order = 0;
    }
 
    $post_password = isset( $postarr['post_password'] ) ? $postarr['post_password'] : '';
    if ( 'private' == $post_status ) {
        $post_password = '';
    }
 
    if ( isset( $postarr['post_parent'] ) ) {
        $post_parent = (int) $postarr['post_parent'];
    } else {
        $post_parent = 0;
    }
 
    $new_postarr = array_merge(
        array(
            'ID' => $post_ID,
        ),
        compact( array_diff( array_keys( $defaults ), array( 'context', 'filter' ) ) )
    );
 
    /**
     * Filters the post parent -- used to check for and prevent hierarchy loops.
     *
     * @since 3.1.0
     *
     * @param int   $post_parent Post parent ID.
     * @param int   $post_ID     Post ID.
     * @param array $new_postarr Array of parsed post data.
     * @param array $postarr     Array of sanitized, but otherwise unmodified post data.
     */
    
	$post_parent = apply_filters( 'wp_insert_post_parent', $post_parent, $post_ID, $new_postarr, $postarr );
 
    /*
     * If the post is being untrashed and it has a desired slug stored in post meta,
     * reassign it.
     */
    if ( 'trash' === $previous_status && 'trash' !== $post_status ) {
        $desired_post_slug = get_post_meta( $post_ID, '_wp_desired_post_slug', true );
        if ( $desired_post_slug ) {
            delete_post_meta( $post_ID, '_wp_desired_post_slug' );
            $post_name = $desired_post_slug;
        }
    }
 
    // If a trashed post has the desired slug, change it and let this post have it.
    if ( 'trash' !== $post_status && $post_name ) {
        /**
         * Filters whether or not to add a `__trashed` suffix to trashed posts that match the name of the updated post.
         *
         * @since 5.4.0
         *
         * @param bool   $add_trashed_suffix Whether to attempt to add the suffix.
         * @param string $post_name          The name of the post being updated.
         * @param int    $post_ID            Post ID.
         */
        $add_trashed_suffix = apply_filters( 'add_trashed_suffix_to_trashed_posts', true, $post_name, $post_ID );
 
        if ( $add_trashed_suffix ) {
            wp_add_trashed_suffix_to_post_name_for_trashed_posts( $post_name, $post_ID );
        }
    }
 
    // When trashing an existing post, change its slug to allow non-trashed posts to use it.
    if ( 'trash' === $post_status && 'trash' !== $previous_status && 'new' !== $previous_status ) {
        $post_name = wp_add_trashed_suffix_to_post_name_for_post( $post_ID );
    }
 
    $post_name = wp_unique_post_slug( $post_name, $post_ID, $post_status, $post_type, $post_parent );
 
    // Don't unslash.
    $post_mime_type = isset( $postarr['post_mime_type'] ) ? $postarr['post_mime_type'] : '';
 
    // Expected_slashed (everything!).
    $data = compact( 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_content_filtered', 'post_title', 'post_excerpt', 'post_status', 'post_type', 'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_parent', 'menu_order', 'post_mime_type', 'guid' );
 
    $emoji_fields = array( 'post_title', 'post_content', 'post_excerpt' );
 
    foreach ( $emoji_fields as $emoji_field ) {
        if ( isset( $data[ $emoji_field ] ) ) {
            $charset = $wpdb->get_col_charset( $wpdb->posts, $emoji_field );
            if ( 'utf8' === $charset ) {
                $data[ $emoji_field ] = wp_encode_emoji( $data[ $emoji_field ] );
            }
        }
    }
 
    if ( 'attachment' === $post_type ) {
        /**
         * Filters attachment post data before it is updated in or added to the database.
         *
         * @since 3.9.0
         *
         * @param array $data    An array of sanitized attachment post data.
         * @param array $postarr An array of unsanitized attachment post data.
         */
        $data = apply_filters( 'wp_insert_attachment_data', $data, $postarr );
    } else {
        /**
         * Filters slashed post data just before it is inserted into the database.
         *
         * @since 2.7.0
         *
         * @param array $data    An array of slashed post data.
         * @param array $postarr An array of sanitized, but otherwise unmodified post data.
         */
        $data = apply_filters( 'wp_insert_post_data', $data, $postarr );
    }
    $data  = wp_unslash( $data );
    $where = array( 'ID' => $post_ID );
 
    if ( $update ) {
        /**
         * Fires immediately before an existing post is updated in the database.
         *
         * @since 2.5.0
         *
         * @param int   $post_ID Post ID.
         * @param array $data    Array of unslashed post data.
         */
        do_action( 'pre_post_update', $post_ID, $data );
        if ( false === $wpdb->update( $wpdb->posts, $data, $where ) ) {
            if ( $wp_error ) {
                return new WP_Error( 'db_update_error', __( 'Could not update post in the database' ), $wpdb->last_error );
            } else {
                return 0;
            }
        }
    } else {
        // If there is a suggested ID, use it if not already present.
        if ( ! empty( $import_id ) ) {
            $import_id = (int) $import_id;
            if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE ID = %d", $import_id ) ) ) {
                $data['ID'] = $import_id;
            }
        }
        if ( false === $wpdb->insert( $wpdb->posts, $data ) ) {
            if ( $wp_error ) {
                return new WP_Error( 'db_insert_error', __( 'Could not insert post into the database' ), $wpdb->last_error );
            } else {
                return 0;
            }
        }
        $post_ID = (int) $wpdb->insert_id;
 
        // Use the newly generated $post_ID.
        $where = array( 'ID' => $post_ID );
    }
 
    if ( empty( $data['post_name'] ) && ! in_array( $data['post_status'], array( 'draft', 'pending', 'auto-draft' ) ) ) {
        $data['post_name'] = wp_unique_post_slug( sanitize_title( $data['post_title'], $post_ID ), $post_ID, $data['post_status'], $post_type, $post_parent );
        $wpdb->update( $wpdb->posts, array( 'post_name' => $data['post_name'] ), $where );
        clean_post_cache( $post_ID );
    }
 
    if ( is_object_in_taxonomy( $post_type, 'category' ) ) {
        wp_set_post_categories( $post_ID, $post_category );
    }
 
    if ( isset( $postarr['tags_input'] ) && is_object_in_taxonomy( $post_type, 'post_tag' ) ) {
        wp_set_post_tags( $post_ID, $postarr['tags_input'] );
    }
 
    // New-style support for all custom taxonomies.
    if ( ! empty( $postarr['tax_input'] ) ) {
        foreach ( $postarr['tax_input'] as $taxonomy => $tags ) {
            $taxonomy_obj = get_taxonomy( $taxonomy );
            if ( ! $taxonomy_obj ) {
                /* translators: %s: Taxonomy name. */
                _doing_it_wrong( __FUNCTION__, sprintf( __( 'Invalid taxonomy: %s.' ), $taxonomy ), '4.4.0' );
                continue;
            }
 
            // array = hierarchical, string = non-hierarchical.
            if ( is_array( $tags ) ) {
                $tags = array_filter( $tags );
            }
            if ( current_user_can( $taxonomy_obj->cap->assign_terms ) ) {
                wp_set_post_terms( $post_ID, $tags, $taxonomy );
            }
        }
    }
 
    if ( ! empty( $postarr['meta_input'] ) ) {
        foreach ( $postarr['meta_input'] as $field => $value ) {
            update_post_meta( $post_ID, $field, $value );
        }
    }
 
    $current_guid = get_post_field( 'guid', $post_ID );
 
    // Set GUID.
    if ( ! $update && '' == $current_guid ) {
        $wpdb->update( $wpdb->posts, array( 'guid' => get_permalink( $post_ID ) ), $where );
    }
 
    if ( 'attachment' === $postarr['post_type'] ) {
        if ( ! empty( $postarr['file'] ) ) {
            update_attached_file( $post_ID, $postarr['file'] );
        }
 
        if ( ! empty( $postarr['context'] ) ) {
            add_post_meta( $post_ID, '_wp_attachment_context', $postarr['context'], true );
        }
    }
 
    // Set or remove featured image.
    if ( isset( $postarr['_thumbnail_id'] ) ) {
        $thumbnail_support = current_theme_supports( 'post-thumbnails', $post_type ) && post_type_supports( $post_type, 'thumbnail' ) || 'revision' === $post_type;
        if ( ! $thumbnail_support && 'attachment' === $post_type && $post_mime_type ) {
            if ( wp_attachment_is( 'audio', $post_ID ) ) {
                $thumbnail_support = post_type_supports( 'attachment:audio', 'thumbnail' ) || current_theme_supports( 'post-thumbnails', 'attachment:audio' );
            } elseif ( wp_attachment_is( 'video', $post_ID ) ) {
                $thumbnail_support = post_type_supports( 'attachment:video', 'thumbnail' ) || current_theme_supports( 'post-thumbnails', 'attachment:video' );
            }
        }
 
        if ( $thumbnail_support ) {
            $thumbnail_id = intval( $postarr['_thumbnail_id'] );
            if ( -1 === $thumbnail_id ) {
                delete_post_thumbnail( $post_ID );
            } else {
                set_post_thumbnail( $post_ID, $thumbnail_id );
            }
        }
    }
 
    clean_post_cache( $post_ID );
 
    $post = get_post( $post_ID );
 
    if ( ! empty( $postarr['page_template'] ) ) {
        $post->page_template = $postarr['page_template'];
        $page_templates      = wp_get_theme()->get_page_templates( $post );
        if ( 'default' != $postarr['page_template'] && ! isset( $page_templates[ $postarr['page_template'] ] ) ) {
            if ( $wp_error ) {
                return new WP_Error( 'invalid_page_template', __( 'Invalid page template.' ) );
            }
            update_post_meta( $post_ID, '_wp_page_template', 'default' );
        } else {
            update_post_meta( $post_ID, '_wp_page_template', $postarr['page_template'] );
        }
    }
 
    if ( 'attachment' !== $postarr['post_type'] ) {
        wp_transition_post_status( $data['post_status'], $previous_status, $post );
    } else {
        if ( $update ) {
            /**
             * Fires once an existing attachment has been updated.
             *
             * @since 2.0.0
             *
             * @param int $post_ID Attachment ID.
             */
            do_action( 'edit_attachment', $post_ID );
            $post_after = get_post( $post_ID );
 
            /**
             * Fires once an existing attachment has been updated.
             *
             * @since 4.4.0
             *
             * @param int     $post_ID      Post ID.
             * @param WP_Post $post_after   Post object following the update.
             * @param WP_Post $post_before  Post object before the update.
             */
            do_action( 'attachment_updated', $post_ID, $post_after, $post_before );
        } else {
 
            /**
             * Fires once an attachment has been added.
             *
             * @since 2.0.0
             *
             * @param int $post_ID Attachment ID.
             */
            do_action( 'add_attachment', $post_ID );
        }
 
        return $post_ID;
    }
 
    if ( $update ) {
        /**
         * Fires once an existing post has been updated.
         *
         * The dynamic portion of the hook name, `$post->post_type`, refers to
         * the post type slug.
         *
         * @since 5.1.0
         *
         * @param int     $post_ID Post ID.
         * @param WP_Post $post    Post object.
         */
        do_action( "edit_post_{$post->post_type}", $post_ID, $post );
 
        /**
         * Fires once an existing post has been updated.
         *
         * @since 1.2.0
         *
         * @param int     $post_ID Post ID.
         * @param WP_Post $post    Post object.
         */
        do_action( 'edit_post', $post_ID, $post );
 
        $post_after = get_post( $post_ID );
 
        /**
         * Fires once an existing post has been updated.
         *
         * @since 3.0.0
         *
         * @param int     $post_ID      Post ID.
         * @param WP_Post $post_after   Post object following the update.
         * @param WP_Post $post_before  Post object before the update.
         */
        do_action( 'post_updated', $post_ID, $post_after, $post_before );
    }
 
    /**
     * Fires once a post has been saved.
     *
     * The dynamic portion of the hook name, `$post->post_type`, refers to
     * the post type slug.
     *
     * @since 3.7.0
     *
     * @param int     $post_ID Post ID.
     * @param WP_Post $post    Post object.
     * @param bool    $update  Whether this is an existing post being updated or not.
     */
    do_action( "save_post_{$post->post_type}", $post_ID, $post, $update );
 
    /**
     * Fires once a post has been saved.
     *
     * @since 1.5.0
     *
     * @param int     $post_ID Post ID.
     * @param WP_Post $post    Post object.
     * @param bool    $update  Whether this is an existing post being updated or not.
     */
    //do_action( 'save_post', $post_ID, $post, $update );
 
    /**
     * Fires once a post has been saved.
     *
     * @since 2.0.0
     *
     * @param int     $post_ID Post ID.
     * @param WP_Post $post    Post object.
     * @param bool    $update  Whether this is an existing post being updated or not.
     */

    do_action( 'id_insert_post', $post_ID, $post, $update );
 
    return $post_ID;
}

