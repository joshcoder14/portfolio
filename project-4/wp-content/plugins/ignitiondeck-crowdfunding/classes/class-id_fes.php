<?php
/*
Things we will need
user info (before or after reg?)
*/

/*
Meta Keys:

FAQ
Update
T&C

*/
class ID_FES {

	var $form;
	var $vars;

	function __construct( $form = null, $vars = null ) {
		global $global_currency;
		if ( empty( $form ) ) {
			$misc_0 = 'step="0.25" min="0"';
			$misc_1 = 'step="0.25" min="1.00"';
			if ($global_currency == 'BTC' || $global_currency == 'credits') {
				$misc_0 = 'step="0.00000001" min="0"';
				$misc_1 = 'step="0.00000001" min="0.00000001"';
			}
			$this->form   = apply_filters( 'id_fes_form_init', $form, $vars );
			$this->form[] = array(
				'before' => '<div class="id_fes_errors upper">',
				'after'  => '</div>',
			);
			$this->form[] = self::id_fes_project_name_field( $vars );
			if ( empty( $vars['status'] ) || strtoupper( $vars['status'] ) !== 'PUBLISH' ) {
				// draft or pending review
				$this->form[] = self::id_fes_goal_field( $vars );
				$this->form[] = self::id_fes_cat_form( $vars );
				$this->form[] = self::id_fes_start_date_field( $vars );
				$this->form[] = self::id_fes_end_date_field( $vars );
				$this->form[] = array(
					'before' => '</div>',
				);
				$this->form[] = array(
					'before' => '<div class="fes_section project_end_type"><div class="form-row half"><h3>' . apply_filters( 'fes_campaign_end_options_label', __( 'Campaign End Options', 'ignitiondeck' ) ) . '</h3>',
				);
				$this->form[] = apply_filters( 'fes_project_end_type_before', $vars );
				$this->form[] = self::id_fes_open_option_field( $vars );
				$this->form[] = self::id_fes_close_option_field( $vars );
				$this->form[] = apply_filters( 'fes_project_end_type_after', $vars );
				$this->form[] = array(
					'before' => '</div>',
				);
			}
			$this->form[] = array(
				'before' => '</div>',
			);
			$this->form[] = self::id_fes_short_description_field( $vars );
			$this->form[] = self::id_fes_video_field( $vars );
			$this->form[] = self::id_fes_project_long_description_field( $vars );
			$this->form[] = self::id_fes_faq_field( $vars );
			if ( isset( $vars['status'] ) && strtoupper( $vars['status'] ) == 'PUBLISH' ) {
				// live post
				$this->form[] = array(
					'before' => '<div class="fes_section project_updates">',
				);
				$this->form[] = apply_filters( 'fes_updates_before', $vars );
				$this->form[] = apply_filters(
					'fes_updates_form',
					array(
						'label'  => __( 'Project Updates', 'ignitiondeck' ),
						'value'  => ( isset( $vars['project_updates'] ) ? $vars['project_updates'] : '' ),
						'name'   => 'project_updates',
						'id'     => 'project_updates',
						'type'   => 'wpeditor',
						'wclass' => 'form-row wpeditor',
					)
				);
				$this->form[] = apply_filters( 'fes_updates_after', $vars );
				$this->form[] = array(
					'after' => '</div>',
				);
			}
			$this->form[] = self::id_fes_featured_image_field( $vars );
			$this->form[] = self::id_fes_featured_image_check_field( $vars );
			$this->form[] = array(
				'before' => '<div class="fes_section project_levels">',
			);
			$this->form[] = array(
				'before' => '<h3>' . apply_filters( 'fes_project_reward_levels_label', __( 'Project Reward Levels', 'ignitiondeck' ) ) . '</h3>',
				'label'  => __( 'Number of Levels', 'ignitiondeck' ),
				'value'  => ( isset( $vars['project_levels'] ) ? $vars['project_levels'] : '1' ),
				'name'   => 'project_levels',
				'id'     => 'project_levels',
				'type'   => 'number',
				'wclass' => 'form-row half',
				'class'  => 'required',
				'misc'   => 'max="99"' . ( isset( $vars['project_levels'] ) && isset( $vars['status'] ) && strtoupper( $vars['status'] ) == 'PUBLISH' ? 'min="' . $vars['project_levels'] . '"' : 'min="1"' ),
			);
			if ( empty( $vars['project_levels'] ) || $vars['project_levels'] == 1 ) {
				if ( ! isset( $vars['status'] ) || strtoupper( $vars['status'] ) !== 'PUBLISH' ) {
					$this->form[] = array(
						'before' => '<div class="form-level">',
						'label'  => __( 'Level Title', 'ignitiondeck' ),
						'value'  => ( isset( $vars['levels'][0]['title'] ) ? $vars['levels'][0]['title'] : '' ),
						'name'   => 'project_level_title[]',
						'id'     => 'project_level_1_title',
						'type'   => 'text',
						'wclass' => 'form-row',
						'class'  => 'required',
					);
					$this->form[] = array(
						'label'  => __( 'Level Price', 'ignitiondeck' ),
						'value'  => ( isset( $vars['levels'][0]['price'] ) ? $vars['levels'][0]['price'] : '' ),
						'name'   => 'project_level_price[]',
						'id'     => 'project_level_1_price',
						'type'   => 'number',
						'wclass' => 'form-row half left',
						'class'  => '',
						'misc'   => $misc_0,
					);
					$this->form[] = array(
						'label'  => __( 'Level Limit', 'ignitiondeck' ),
						'value'  => ( isset( $vars['levels'][0]['limit'] ) ? $vars['levels'][0]['limit'] : null ),
						'name'   => 'project_level_limit[]',
						'id'     => 'project_level_1_limit',
						'type'   => 'number',
						'wclass' => 'form-row half',
					);
					$this->form[] = self::id_fes_fund_types_single( $vars );
				} else {
					$this->form[]     = array(
						'before' => '<div class="form-level">',
						'label'  => __( 'Level Title', 'ignitiondeck' ),
						'value'  => ( isset( $vars['levels'][0]['title'] ) ? $vars['levels'][0]['title'] : '' ),
						'name'   => 'project_level_title[]',
						'id'     => 'project_level_1_title',
						'type'   => 'hidden',
						'wclass' => 'form-row',
						'class'  => 'required',
					);
						$this->form[] = array(
							'label'  => __( 'Level Price', 'ignitiondeck' ),
							'value'  => ( isset( $vars['levels'][0]['price'] ) ? $vars['levels'][0]['price'] : '' ),
							'name'   => 'project_level_price[]',
							'id'     => 'project_level_1_price',
							'type'   => 'hidden',
							'wclass' => 'form-row half left',
							'class'  => '',
						);
						$this->form[] = array(
							'label'  => __( 'Level Limit', 'ignitiondeck' ),
							'value'  => ( isset( $vars['levels'][0]['limit'] ) ? $vars['levels'][0]['limit'] : null ),
							'name'   => 'project_level_limit[]',
							'id'     => 'project_level_1_limit',
							'type'   => 'hidden',
							'wclass' => 'form-row half',
						);
				}

				$this->form[] = array(
					'label'  => __( 'Level Description', 'ignitiondeck' ),
					'value'  => ( isset( $vars['levels'][0]['short'] ) ? $vars['levels'][0]['short'] : '' ),
					'name'   => 'level_description[]',
					'id'     => 'project_level_1_description',
					'type'   => 'text',
					'wclass' => 'form-row',
				);
				$this->form[] = array(
					'label'  => __( 'Level Long Description', 'ignitiondeck' ),
					'value'  => ( isset( $vars['levels'][0]['long'] ) ? $vars['levels'][0]['long'] : '' ),
					'name'   => 'level_long_description[]',
					'id'     => 'project_level_1_long_description',
					'type'   => 'wpeditor',
					'wclass' => 'form-row wpeditor',
					'after'  => '</div>',
				);
			} elseif ( isset( $vars['project_levels'] ) && $vars['project_levels'] > 1 ) {
				for ( $i = 0; $i <= $vars['project_levels'] - 1; $i++ ) {
					if ( ! isset( $vars['status'] ) || strtoupper( $vars['status'] ) !== 'PUBLISH' ) {
						$this->form[] = array(
							'before' => '<div class="form-level">',
							'label'  => __( 'Level Title', 'ignitiondeck' ),
							'value'  => ( isset( $vars['levels'][ $i ]['title'] ) ? $vars['levels'][ $i ]['title'] : '' ),
							'name'   => 'project_level_title[]',
							'id'     => 'project_level_' . ( $i + 1 ) . '_title',
							'type'   => 'text',
							'wclass' => 'form-row',
							'class'  => 'required',
						);
						$this->form[] = array(
							'label'  => __( 'Level Price', 'ignitiondeck' ),
							'value'  => ( isset( $vars['levels'][ $i ]['price'] ) ? $vars['levels'][ $i ]['price'] : '' ),
							'name'   => 'project_level_price[]',
							'id'     => 'project_level_' . ( $i + 1 ) . '_price',
							'type'   => 'number',
							'wclass' => 'form-row half left',
							'class'  => 'required',
							'misc'   => $misc_0,
						);
						$this->form[] = array(
							'label'  => __( 'Level Limit', 'ignitiondeck' ),
							'value'  => ( isset( $vars['levels'][ $i ]['limit'] ) ? $vars['levels'][ $i ]['limit'] : null ),
							'name'   => 'project_level_limit[]',
							'id'     => 'project_level_' . ( $i + 1 ) . '_limit',
							'type'   => 'number',
							'wclass' => 'form-row half',
						);
						// Project fund type for multi level project, separate for each level
						if ( isset( $vars['fund_types'] ) ) {
							$this->form[] = array(
								'label'   => __( 'Level Fund Type', 'ignitiondeck' ),
								'name'    => 'project_fund_type[]',
								'id'      => 'level_project_fund_type_' . ( $i + 1 ),
								'type'    => 'select',
								'wclass'  => 'form-row pretty_dropdown',
								'value'   => ( isset( $vars['levels'][ $i ]['fund_type'] ) ? $vars['levels'][ $i ]['fund_type'] : 'capture' ),
								'options' => apply_filters(
									'ide_fund_options',
									array(
										array(
											'value' => 'capture',
											'title' => __( 'Immediately Deliver Funds', 'ignitiondeck' ),
											'misc'  => ( $vars['fund_types'] == 'preauth' ? 'disabled="disabled"' : '' ),
										),
										array(
											'value' => 'preauth',
											'title' => '100% Threshold',
											'misc'  => ( $vars['fund_types'] == 'capture' || $vars['fund_types'] == 'c_sub' ? 'disabled="disabled"' : '' ),
										),
									)
								),
							);
						}
					} else {
						$this->form[] = array(
							'before' => '<div class="form-level">',
							'label'  => __( 'Level Title', 'ignitiondeck' ),
							'value'  => ( isset( $vars['levels'][ $i ]['title'] ) ? $vars['levels'][ $i ]['title'] : '' ),
							'name'   => 'project_level_title[]',
							'id'     => 'project_level_' . ( $i + 1 ) . '_title',
							'type'   => 'hidden',
							'wclass' => 'form-row',
							'class'  => 'required',
						);
						$this->form[] = array(
							'label'  => __( 'Level Price', 'ignitiondeck' ),
							'value'  => ( isset( $vars['levels'][ $i ]['price'] ) ? $vars['levels'][ $i ]['price'] : '' ),
							'name'   => 'project_level_price[]',
							'id'     => 'project_level_' . ( $i + 1 ) . '_price',
							'type'   => 'hidden',
							'wclass' => 'form-row half left',
							'class'  => '',
						);
						$this->form[] = array(
							'label'  => __( 'Level Limit', 'ignitiondeck' ),
							'value'  => ( isset( $vars['levels'][ $i ]['limit'] ) ? $vars['levels'][ $i ]['limit'] : '' ),
							'name'   => 'project_level_limit[]',
							'id'     => 'project_level_' . ( $i + 1 ) . '_limit',
							'type'   => 'hidden',
							'wclass' => 'form-row half',
						);
					}
					$this->form[] = array(
						'label'  => __( 'Level Description', 'ignitiondeck' ),
						'value'  => ( isset( $vars['levels'][ $i ]['short'] ) ? $vars['levels'][ $i ]['short'] : '' ),
						'name'   => 'level_description[]',
						'id'     => 'project_level_' . ( $i + 1 ) . '_description',
						'type'   => 'text',
						'wclass' => 'form-row',
					);
					$this->form[] = array(
						'label'  => __( 'Level Long Description', 'ignitiondeck' ),
						'value'  => ( isset( $vars['levels'][ $i ]['long'] ) ? $vars['levels'][ $i ]['long'] : '' ),
						'name'   => 'level_long_description[]',
						'id'     => 'project_level_' . ( $i + 1 ) . '_long_description',
						'type'   => 'wpeditor',
						'wclass' => 'form-row wpeditor',
						'after'  => '</div>',
					);
				}
			}
				$this->form[] = array(
					'before' => '<div class="form-level-clone">',
					'label'  => __( 'Level Title', 'ignitiondeck' ),
					'name'   => 'project_level_title[]',
					'id'     => 'project_level_1_title',
					'type'   => 'text',
					'wclass' => 'form-row',
					'misc'   => 'disabled="disabled"',
				);
				$this->form[] = array(
					'label'  => __( 'Level Price', 'ignitiondeck' ),
					'name'   => 'project_level_price[]',
					'id'     => 'project_level_1_price',
					'type'   => 'number',
					'wclass' => 'form-row half left',
					'misc'   => 'disabled="disabled" '.$misc_0,
				);
				$this->form[] = array(
					'label'  => __( 'Level Limit', 'ignitiondeck' ),
					'name'   => 'project_level_limit[]',
					'id'     => 'project_level_1_limit',
					'type'   => 'number',
					'wclass' => 'form-row half',
					'misc'   => 'disabled="disabled"',
				);
				// Project fund type if is set
				if ( isset( $vars['fund_types'] ) ) {
					$fund_type_args = array(
						'label'  => __( 'Level Fund Type', 'ignitiondeck' ),
						'name'   => 'project_fund_type[]',
						'id'     => 'level_project_fund_type_1',
						'type'   => 'select',
						'wclass' => 'form-row pretty_dropdown',
						// 'value' => (isset($vars['levels'][0]['fund_type']) ? $vars['levels'][0]['fund_type'] : '')
					);
					$fund_type_args['options'] = array();
					// Pushing both the options, removing on checks then
					$option = array(
						'value' => 'capture',
						'title' => __( 'Immediately Deliver Funds', 'ignitiondeck' ),
					);
					array_push( $fund_type_args['options'], $option );
					$option = array(
						'value' => 'preauth',
						'title' => '100% Threshold',
					);
					array_push( $fund_type_args['options'], $option );

					if ( $vars['fund_types'] == 'capture' || $vars['fund_types'] == 'c_sub' ) {
						// Remove 'preauth' (100% Threshold) option
						for ( $i = 0; $i < count( $fund_type_args['options'] ); $i++ ) {
							if ( $fund_type_args['options'][ $i ]['value'] == 'preauth' ) {
								$removal_index = $i;
							}
						}
						if ( isset( $removal_index ) ) {
							unset( $fund_type_args['options'][ $removal_index ] );
							unset( $removal_index );
						}
					}
					if ( $vars['fund_types'] == 'preauth' ) {
						// Remove the 'capture' (Immediately Deliver Funds) option
						for ( $i = 0; $i < count( $fund_type_args['options'] ); $i++ ) {
							if ( $fund_type_args['options'][ $i ]['value'] == 'capture' ) {
								$removal_index = $i;
							}
						}
						if ( isset( $removal_index ) ) {
							unset( $fund_type_args['options'][ $removal_index ] );
							unset( $removal_index );
						}
					}
					$fund_type_args['options'] = apply_filters( 'ide_fund_options', $fund_type_args['options'] );
					$this->form[]              = $fund_type_args;
				}
				$this->form[]  = array(
					'label'  => __( 'Level Description', 'ignitiondeck' ),
					'name'   => 'level_description[]',
					'id'     => 'project_level_1_description',
					'type'   => 'text',
					'wclass' => 'form-row',
				);
				$this->form[]  = array(
					'label'  => __( 'Level Long Description', 'ignitiondeck' ),
					'name'   => 'level_long_description[]',
					'id'     => 'project_level_1_long_description',
					'type'   => 'wpeditor',
					'wclass' => 'form-row wpeditor',
					'after'  => '</div>',
				);
				$this->form[]  = array(
					'after' => '</div><div class="border-bottom"></div>',
				);
				$this->form[]  = array(
					'before' => '<div class="id_fes_errors lower">',
					'after'  => '</div>',
				);
				$submit_button = array(
					'value'  => ( isset( $vars['status'] ) && strtoupper( $vars['status'] ) == 'PUBLISH' ? __( 'Update', 'ignitiondeck' ) : __( 'Update Submission', 'ignitiondeck' ) ),
					'name'   => 'project_fesubmit',
					'type'   => 'submit',
					'class'  => 'project_fesubmit',
					'wclass' => 'form-row',
				);
				if ( empty( $vars['status'] ) || strtoupper( $vars['status'] ) == 'DRAFT' ) {
					$this->form[]            = array(
						'value'  => ( empty( $vars['status'] ) ? __( 'Save Draft', 'ignitiondeck' ) : __( 'Update Draft', 'ignitiondeck' ) ),
						'name'   => 'project_fesave',
						'class'  => 'project_fesave',
						'type'   => 'submit',
						'wclass' => 'form-row half left',
					);
					$submit_button['value']  = __( 'Submit for Review', 'ignitiondeck' );
					$submit_button['wclass'] = 'form-row half';
				}
				$this->form[] = $submit_button;
				if ( isset( $vars['post_id'] ) && $vars['post_id'] > 0 ) {
					$this->form[] = array(
						'value' => $vars['post_id'],
						'name'  => 'project_post_id',
						'type'  => 'hidden',
					);
				}
				$this->form = apply_filters( 'id_fes_form', $this->form, $vars );
		} else {
			$this->form = apply_filters( 'id_fes_form', $form, $vars );
		}
		$this->vars = apply_filters( 'id_fes_vars', $vars );
	}

	function display_form() {
		$id_form = new ID_Form( $this->form );
		$output  = $id_form->build_form( $this->vars );
		return $output;
	}

	public static function id_fes_fund_types_single( $vars ) {
		// Project fund type for single level project
		if ( isset( $vars['fund_types'] ) ) {
			$fund_type_args            = array(
				'label'  => __( 'Level Fund Type', 'ignitiondeck' ),
				'name'   => 'project_fund_type[]',
				'id'     => 'level_project_fund_type_1',
				'type'   => 'select',
				'wclass' => 'form-row pretty_dropdown',
				'value'  => ( isset( $vars['levels'][0]['fund_type'] ) ? $vars['levels'][0]['fund_type'] : '' ),
			);
			$fund_type_args['options'] = array();
			// Pushing both the options, removing on checks then
			$option = array(
				'value' => 'capture',
				'title' => __( 'Immediately Deliver Funds', 'ignitiondeck' ),
			);
			array_push( $fund_type_args['options'], $option );
			$option = array(
				'value' => 'preauth',
				'title' => '100% Threshold',
			);
			array_push( $fund_type_args['options'], $option );

			if ( $vars['fund_types'] == 'capture' || $vars['fund_types'] == 'c_sub' ) {
				// Remove 'preauth' (100% Threshold) option
				for ( $i = 0; $i < count( $fund_type_args['options'] ); $i++ ) {
					if ( $fund_type_args['options'][ $i ]['value'] == 'preauth' ) {
						$removal_index = $i;
					}
				}
				if ( isset( $removal_index ) ) {
					unset( $fund_type_args['options'][ $removal_index ] );
					unset( $removal_index );
				}
			}
			if ( $vars['fund_types'] == 'preauth' ) {
				// Remove the 'capture' (Immediately Deliver Funds) option
				for ( $i = 0; $i < count( $fund_type_args['options'] ); $i++ ) {
					if ( $fund_type_args['options'][ $i ]['value'] == 'capture' ) {
						$removal_index = $i;
					}
				}
				if ( isset( $removal_index ) ) {
					unset( $fund_type_args['options'][ $removal_index ] );
					unset( $removal_index );
				}
			}
			$fund_type_args['options'] = apply_filters( 'ide_fund_options', $fund_type_args['options'] );
		}
		return ( isset( $fund_type_args ) ? $fund_type_args : null );
	}

	public static function id_fes_project_name_field( $vars ) {
		$field = array(
			'before' => '<div class="fes_section project_name"><h3>' . apply_filters( 'fes_project_information_label', __( 'Project Information', 'ignitiondeck' ) ) . '</h3>',
			'label'  => __( 'Project Title', 'ignitiondeck' ),
			'value'  => ( isset( $vars['project_name'] ) ? $vars['project_name'] : '' ),
			'name'   => 'project_name',
			'id'     => 'project_name',
			'type'   => 'text',
			'class'  => 'required',
			'wclass' => 'form-row',
		);
		return $field;
	}

	public static function id_fes_goal_field( $vars ) {
		global $global_currency;
		$misc_1 = 'step="0.25" min="1.00"';
		if ($global_currency == 'BTC' || $global_currency == 'credits') {
			$misc_1 = 'step="0.00000001" min="0.00000001"';
		}
		$field = array(
			'label'  => __( 'Goal Amount', 'ignitiondeck' ),
			'value'  => ( isset( $vars['project_goal'] ) ? $vars['project_goal'] : '1.00' ),
			'name'   => 'project_goal',
			'id'     => 'project_goal',
			'type'   => 'number',
			'class'  => 'required',
			'wclass' => 'form-row onethird',
			'misc'   => $misc_1,
		);
		return $field;
	}

	public static function id_fes_cat_form( $vars ) {
		$args       = array(
			'type'       => 'ignition_product',
			'taxonomy'   => 'project_category',
			'hide_empty' => false,
		);
		$categories = get_categories( $args );
		if ( empty( $categories ) ) {
			return null;
		}
		$cat_form    = array(
			'label'  => __( 'Project Category', 'ignitiondeck' ),
			'value'  => ( isset( $vars['project_category'] ) ? $vars['project_category'] : '' ),
			'name'   => 'project_category',
			'id'     => 'project_category',
			'type'   => 'select',
			'wclass' => 'form-row pretty_dropdown',
		);
		$cat_options = array();
		foreach ( $categories as $category ) {
			$cat_options[] = array(
				'value' => $category->slug,
				'title' => $category->name,
			);
			//$cat_form['misc'] = (isset($vars['project_category']) && $vars['project_category'] == $category->slug ? 'selected="selected"' : null);
		}
		$cat_form['options'] = $cat_options;
		return $cat_form;
	}

	public static function id_fes_close_option_field( $vars ) {
		$close = array(
			'label'  => __( 'Expires on End Date', 'ignitiondeck' ),
			'name'   => 'project_end_type',
			'id'     => 'closed',
			'type'   => 'radio',
			'value'  => 'closed',
			'wclass' => 'half radio',
			'misc'   => ( ( isset( $vars['project_end_type'] ) && $vars['project_end_type'] == 'closed' ) || ! isset( $vars['project_end_type'] ) ? 'checked="checked"' : '' ),
		);
		return $close;
	}
	public static function id_fes_open_option_field( $vars ) {
		$open = array(
			'label'  => __( 'Never Expires', 'ignitiondeck' ),
			'name'   => 'project_end_type',
			'id'     => 'open',
			'type'   => 'radio',
			'value'  => 'open',
			'wclass' => 'half radio',
			'misc'   => ( isset( $vars['project_end_type'] ) && $vars['project_end_type'] == 'open' ? 'checked="checked"' : '' ),
		);
		return $open;
	}

	public static function id_fes_start_date_field( $vars ) {
		$date_format = idf_date_format();
		$field       = array(
			'label'  => __( 'Start Date', 'ignitiondeck' ),
			'value'  => ( isset( $vars['project_start'] ) ? $vars['project_start'] : date( $date_format, time() ) ),
			'name'   => 'project_start',
			'id'     => 'project_start',
			'type'   => 'text',
			'class'  => 'required date',
			'wclass' => 'form-row half left',
		);
		return $field;
	}

	public static function id_fes_end_date_field( $vars ) {
		$date_format = idf_date_format();
		$field       = array(
			'label'  => __( 'End Date', 'ignitiondeck' ),
			'value'  => ( isset( $vars['project_end'] ) ? $vars['project_end'] : date( $date_format, strtotime( '+30 days' ) ) ),
			'name'   => 'project_end',
			'id'     => 'project_end',
			'type'   => 'text',
			'class'  => 'required date',
			'wclass' => 'form-row half',
		);
		return $field;
	}

	public static function id_fes_short_description_field( $vars ) {
		$field = array(
			'before' => '<div class="fes_section project_short_description"><h3>' . apply_filters( 'fes_project_details_label', __( 'Project Details', 'ignitiondeck' ) ) . '</h3>',
			'label'  => __( 'Project Short Description', 'ignitiondeck' ),
			'value'  => ( isset( $vars['project_short_description'] ) ? $vars['project_short_description'] : '' ),
			'name'   => 'project_short_description',
			'id'     => 'project_short_description',
			'type'   => 'text',
			'class'  => 'required',
			'wclass' => 'form-row',
		);
		return $field;
	}

	public static function id_fes_video_field( $vars ) {
		$field = array(
			'label'  => __( 'Project Video', 'ignitiondeck' ),
			'value'  => ( isset( $vars['project_video'] ) ? $vars['project_video'] : '' ),
			'name'   => 'project_video',
			'id'     => 'project_video',
			'type'   => 'textarea',
			'wclass' => 'form-row',
		);
		return $field;
	}

	public static function id_fes_project_long_description_field( $vars ) {
		$field = array(
			'label'  => __( 'Project Long Description', 'ignitiondeck' ),
			'value'  => ( isset( $vars['project_long_description'] ) ? $vars['project_long_description'] : '' ),
			'name'   => 'project_long_description',
			'id'     => 'project_long_description',
			'type'   => 'wpeditor',
			'wclass' => 'form-row wpeditor',
			'class'  => 'required',
		);
		return $field;
	}

	public static function id_fes_faq_field( $vars ) {
		$field = array(
			'label'  => __( 'Project FAQ', 'ignitiondeck' ),
			'value'  => ( isset( $vars['project_faq'] ) ? $vars['project_faq'] : '' ),
			'name'   => 'project_faq',
			'id'     => 'project_faq',
			'type'   => 'wpeditor',
			'after'  => '</div>',
			'wclass' => 'form-row wpeditor',
		);
		return $field;
	}

	public static function id_fes_featured_image_field( $vars ) {
		$field = array(
			'before' => '<div class="fes_section project_hero"><h3>' . apply_filters( 'fes_project_images_label', __( 'Project Images', 'ignitiondeck' ) ) . '</h3>',
			'label'  => __( 'Featured Image', 'ignitiondeck' ),
			'value'  => ( isset( $vars['project_hero'] ) ? $vars['project_hero'] : '' ),
			'misc'   => ( isset( $vars['project_hero'] ) ? 'data-url="' . $vars['project_hero'] . '" accept="image/*"' : 'accept="image/*"' ),
			'name'   => 'project_hero',
			'id'     => 'project_hero',
			'type'   => 'file',
			'after'  => '</div>',
			'wclass' => 'form-row half left',
		);
		return $field;
	}

	public static function id_fes_featured_image_check_field( $vars ) {
		$field = array(
			'value'  => 'no',
			'name'   => 'project_hero_removed',
			'id'     => 'project_hero_removed',
			'type'   => 'hidden',
			'wclass' => 'hide',
		);
		return $field;
	}
}

