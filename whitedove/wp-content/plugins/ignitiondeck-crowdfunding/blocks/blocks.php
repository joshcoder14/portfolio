<?php
/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/writing-your-first-block-type/
 */
defined( 'ABSPATH' ) || exit;

/**
 * Add custom "Ignition Deck" block category
 *
 * @link https://wordpress.org/gutenberg/handbook/designers-developers/developers/filters/block-filters/#managing-block-categories
 */

function idcf_add_new_block_category( $categories, $block_editor_context ) {
	// Check the context of this filter, return default if not in the post/page block editor.
	// Alternatively, use this check to add custom categories to only the customizer or widget screens.
	if ( ! ( $block_editor_context instanceof WP_Block_Editor_Context ) ) {
		return $categories;
	}

	return array_merge(
		$categories,
		array(
			array(
				'slug'  => 'ignitiondeck',
				'title' => __( 'IgnitionDeck', 'idcf_blocks'),
			),
		)
	);
}

if ( version_compare( $GLOBALS['wp_version'], '5.8-alpha-1', '<' ) ) {
	add_filter( 'block_categories', 'idcf_add_new_block_category', 10, 2 );
} else {
	add_filter( 'block_categories_all', 'idcf_add_new_block_category', 10, 2 );
}

function idcf_blocks_create_init() {
	// Only load if Gutenberg is available.
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	$dir = __DIR__;

	$script_asset_path = "$dir/build/index.asset.php";
	if ( ! file_exists( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "create-block/ds-refresh-blocks" block first.'
		);
	}

	$index_js     = 'build/index.js';
	$script_asset = require $script_asset_path;
	wp_register_script(
		'ignitiondeck-blocks-editor-js',
		plugins_url( $index_js, __FILE__ ),
		$script_asset['dependencies'],
		$script_asset['version'],
		true
	);

	$editor_css = 'build/index.css';
	wp_register_style(
		'ignitiondeck-blocks-editor-style',
		plugins_url( $editor_css, __FILE__ ),
		array(),
		filemtime( "$dir/$editor_css" )
	);

	$style_css = 'build/style-index.css';
	wp_register_style(
		'ignitiondeck-blocks-style',
		plugins_url( $style_css, __FILE__ ),
		array(),
		filemtime( "$dir/$style_css" )
	);

	register_block_type(
		'idcf-blocks/featured-project',
		array(
			'editor_script'   => 'ignitiondeck-blocks-editor-js',
			'editor_style'    => 'ignitiondeck-blocks-style',
			'style'           => 'ignitiondeck-blocks-style',
			'render_callback' => 'idcf_blocks_render_featured_project',
		)
	);

	register_block_type(
		'idcf-blocks/grid-project',
		array(
			'editor_script'   => 'ignitiondeck-blocks-editor-js',
			'editor_style'    => 'ignitiondeck-blocks-style',
			'style'           => 'ignitiondeck-blocks-style',
			'render_callback' => 'idcf_blocks_render_grid_project',
		)
	);
}
add_action( 'init', 'idcf_blocks_create_init' );

/**
 * Expose custom fields to the 'Featured Project' block
 */
function idcf_blocks_add_custom_field() {
	$project_meta_fields = array( 'ign_end_type', 'ign_project_description', 'ign_end_type' );
	foreach ( $project_meta_fields as $field ) {
		register_rest_field(
			'ignition_product',
			$field,
			array(
				'get_callback'    => 'idcf_blocks_rest_get_project_fields',
				'update_callback' => null,
				'schema'          => null,
			)
		);
	}

	register_rest_field(
		'ignition_product',
		'total',
		array(
			'get_callback'    => 'idcf_blocks_rest_get_product_raised',
			'update_callback' => null,
			'schema'          => null,
		)
	);

	register_rest_field(
		'ignition_product',
		'goal',
		array(
			'get_callback'    => 'idcf_blocks_rest_get_product_goal',
			'update_callback' => null,
			'schema'          => null,
		)
	);

	register_rest_field(
		'ignition_product',
		'pledges',
		array(
			'get_callback'    => 'idcf_blocks_rest_get_product_pĺedges',
			'update_callback' => null,
			'schema'          => null,
		)
	);

	register_rest_field(
		'ignition_product',
		'days_left',
		array(
			'get_callback'    => 'idcf_blocks_rest_get_days_left',
			'update_callback' => null,
			'schema'          => null,
		)
	);

	register_rest_field(
		'ignition_product',
		'thumbnail',
		array(
			'get_callback'    => 'idcf_blocks_rest_get_project_thumbnail',
			'update_callback' => null,
			'schema'          => null,
		)
	);

	register_rest_field(
		'ignition_product',
		'percentage',
		array(
			'get_callback'    => 'idcf_blocks_rest_get_percentage',
			'update_callback' => null,
			'schema'          => null,
		)
	);
}

function idcf_blocks_rest_get_project_fields( $post, $field_name, $request ) {
	return get_post_meta( $post['id'], $field_name, true );
}

function idcf_blocks_rest_get_project_thumbnail( $post, $field_name, $request ) {
	$thumbnail = ID_Project::get_project_thumbnail( $post['id'] );
	return esc_url( $thumbnail );
}

function idcf_blocks_rest_get_product_raised( $post, $field_name, $request ) {
	$project = idcf_blocks_get_project( $post['id'] );
	$total   = $project->get_project_raised();
	return $total;
}

function idcf_blocks_rest_get_product_goal( $post, $field_name, $request ) {
	$project = idcf_blocks_get_project( $post['id'] );
	$goal    = apply_filters( 'id_project_goal', $project->the_goal(), $post['id'] );
	return $goal;
}

function idcf_blocks_rest_get_product_pĺedges( $post, $field_name, $request ) {
	$project = idcf_blocks_get_project( $post['id'] );
	$pledges = apply_filters( 'id_number_pledges', $project->get_project_orders(), $post['id'] );
	return $pledges;
}

function idcf_blocks_rest_get_days_left( $post, $field_name, $request ) {
	$project   = idcf_blocks_get_project( $post['id'] );
	$days_left = $project->days_left();
	return $days_left;
}

function idcf_blocks_rest_get_percentage( $post, $field_name, $request ) {
	$project    = idcf_blocks_get_project( $post['id'] );
	$percentage = $project->percent();
	return $percentage;
}

function idcf_blocks_get_project( $id = -1 ) {
	$project_id = get_post_meta( $id, 'ign_project_id', true );
	$project    = new ID_Project( $project_id );
	return $project;
}

add_action( 'rest_api_init', 'idcf_blocks_add_custom_field' );


function idcf_blocks_render_featured_project( $block_attributes, $content, $block ) {
	$id = isset( $block_attributes['projectsSelected'] ) ? $block_attributes['projectsSelected'] : -1;

	if ( -1 === $id ) {
		return;
	}

	$project_id = get_post_meta( $id, 'ign_project_id', true );
	$project    = new ID_Project( $project_id );
	$deck       = new Deck( $project_id );
	$hdeck      = $deck->hDeck();
	$the_deck   = $deck->the_deck();

	$project_title       = get_the_title( $id );
	$project_description = $project->short_description();
	$thumbnail           = ID_Project::get_project_thumbnail( $id );
	$project_total       = $hdeck->total;
	$project_goal        = $hdeck->goal;
	$project_pledges     = $hdeck->pledges;
	$project_days_left   = $the_deck->days_left;
	$project_end_type    = $hdeck->end_type;
	$support_url         = get_permalink( $id );

	$title_color       = isset( $block_attributes['titleColor'] ) ? $block_attributes['titleColor'] : '#000';
	$description_color = isset( $block_attributes['descriptionColor'] ) ? $block_attributes['descriptionColor'] : '#000';
	$button_color      = isset( $block_attributes['buttonColor'] ) ? $block_attributes['buttonColor'] : '#3182CE';
	$button_text_color = isset( $block_attributes['buttonText'] ) ? $block_attributes['buttonText'] : '#FFF';
	$meta_color        = isset( $block_attributes['metaColor'] ) ? $block_attributes['metaColor'] : '#000';

	$image_alignment = isset( $block_attributes['imageAlignment'] ) ? $block_attributes['imageAlignment'] : '0';

	$post_id      = get_the_ID();
	$author_id    = get_post_field( 'post_author', $post_id );

	// SVG icons from https://www.svgrepo.com/
	$share_twitter  = "https://www.twitter.com/intent/tweet?url=$support_url&text=";
	$share_facebook = "https://www.facebook.com/sharer/sharer.php?&u=$support_url";
	$share_linkedin = "https://www.linkedin.com/sharing/share-offsite/?url=$support_url";
	$share_pinterest = "http://pinterest.com/pin/create/link/?url=$support_url&description=$project_title&media=$thumbnail";

	$end_type_text = '';
	if ( 'closed' === $project_end_type && 1 === $project_days_left ) {
		$end_type_text = sprintf(
			__( 'This project ends in %s day', 'ignitiondeck' ),
			$project_days_left
		);
	} elseif ( $project_days_left > 1 ) {
		$end_type_text = sprintf(
			__( 'This project ends in %s days', 'ignitiondeck' ),
			$project_days_left
		);
	} else {
		$end_type_text = __( 'This project has ended', 'ignitiondeck' );
	}

	$project_render_html = '<div class="idcf-featured-project-block">
								<div class="idcf-featured-project-block-first-row">
									<h2 style="color: %9$s">%1$s</h2>
									<p style="color: %10$s">%2$s</p>
								</div>

								<div class="idcf-featured-project-block-second-row">
									<div class="idcf-featured-project-block-single-image" style="order: %14$s">
										<img src="%3$s" />
									</div>

									<div class="idcf-featured-project-meta">
										<div class="idcf-featured-project-block-single-total">
											<h3 style="color: %13$s">%4$s</h3>
											<p style="color: %13$s">' . __( 'Pledged of', 'ignitiondeck' ) . ' %5$s</p>
										</div>
										<div class="idcf-featured-project-block-single-pledges">
											<h3 style="color: %13$s">%6$s</h3>
											<p style="color: %13$s">' . __( 'backers', 'ignitiondeck' ) . '</p>
										</div>
										<div class="idcf-featured-project-block-single-days">
											<h3 style="color: %13$s">%7$s</h3>
											<p style="color: %13$s">' . __( 'days to go', 'ignitiondeck' ) . '</p>
										</div>
										<div class="idcf-ign-supportnow idcf-featured-project-block-single-support">
											<a class="idcf-action-button" style="background: %11$s;color: %12$s" href="%8$s">' . __( 'Back this project', 'ignitiondeck' ) . '</a>
										</div>
										<div class="idcf-featured-project-share">
											<p>' . __( 'Share on Social Media', 'ignitiondeck' ) . '</p>
											<div>
												<div class="idcf-featured-project-block-single-twitter twitter-share-button">
													<a href="%15$s">
														<svg viewBox="0 0 512 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M459.37 151.716c.325 4.548.325 9.097.325 13.645 0 138.72-105.583 298.558-298.558 298.558-59.452 0-114.68-17.219-161.137-47.106 8.447.974 16.568 1.299 25.34 1.299 49.055 0 94.213-16.568 130.274-44.832-46.132-.975-84.792-31.188-98.112-72.772 6.498.974 12.995 1.624 19.818 1.624 9.421 0 18.843-1.3 27.614-3.573-48.081-9.747-84.143-51.98-84.143-102.985v-1.299c13.969 7.797 30.214 12.67 47.431 13.319-28.264-18.843-46.781-51.005-46.781-87.391 0-19.492 5.197-37.36 14.294-52.954 51.655 63.675 129.3 105.258 216.365 109.807-1.624-7.797-2.599-15.918-2.599-24.04 0-57.828 46.782-104.934 104.934-104.934 30.213 0 57.502 12.67 76.67 33.137 23.715-4.548 46.456-13.32 66.599-25.34-7.798 24.366-24.366 44.833-46.132 57.827 21.117-2.273 41.584-8.122 60.426-16.243-14.292 20.791-32.161 39.308-52.628 54.253z"></path>
														</svg>
													</a>
												</div>

												<div class="idcf-featured-project-block-single-facebook">
													<a href="%16$s">
													<svg fill="currentColor" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 60.734 60.733" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M57.378,0.001H3.352C1.502,0.001,0,1.5,0,3.353v54.026c0,1.853,1.502,3.354,3.352,3.354h29.086V37.214h-7.914v-9.167h7.914 v-6.76c0-7.843,4.789-12.116,11.787-12.116c3.355,0,6.232,0.251,7.071,0.36v8.198l-4.854,0.002c-3.805,0-4.539,1.809-4.539,4.462 v5.851h9.078l-1.187,9.166h-7.892v23.52h15.475c1.852,0,3.355-1.503,3.355-3.351V3.351C60.731,1.5,59.23,0.001,57.378,0.001z"></path> </g> </g></svg>
													</a>
												</div>

												<div class="idcf-featured-project-block-single-linkedin">
													<a href="%17$s">
													<svg fill="currentColor" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 32 32" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M17.303,14.365c0.012-0.015,0.023-0.031,0.031-0.048v0.048H17.303z M32,0v32H0V0H32L32,0z M9.925,12.285H5.153v14.354 h4.772V12.285z M10.237,7.847c-0.03-1.41-1.035-2.482-2.668-2.482c-1.631,0-2.698,1.072-2.698,2.482 c0,1.375,1.035,2.479,2.636,2.479h0.031C9.202,10.326,10.237,9.222,10.237,7.847z M27.129,18.408c0-4.408-2.355-6.459-5.494-6.459 c-2.531,0-3.664,1.391-4.301,2.368v-2.032h-4.77c0.061,1.346,0,14.354,0,14.354h4.77v-8.016c0-0.434,0.031-0.855,0.157-1.164 c0.346-0.854,1.132-1.746,2.448-1.746c1.729,0,2.418,1.314,2.418,3.246v7.68h4.771L27.129,18.408L27.129,18.408z"></path> </g> </g></svg>
													</a>
												</div>

												<div class="idcf-featured-project-block-single-pinterest">
												<a href="%18$s" target="_blank">
												<svg fill="currentColor" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 455.731 455.731" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M0,0v455.731h455.731V0H0z M384.795,288.954c-35.709,91.112-131.442,123.348-203.22,100.617 c5.366-13.253,11.473-26.33,15.945-39.943c4.492-13.672,7.356-27.878,10.725-41.037c2.9,2.44,5.814,5.027,8.866,7.439 c15.861,12.535,33.805,13.752,52.605,9.232c19.977-4.803,35.764-16.13,47.455-32.78c19.773-28.16,26.751-60.019,21.972-93.546 c-4.942-34.668-25.469-59.756-57.65-72.389c-48.487-19.034-94.453-12.626-134.269,22.259 c-30.622,26.83-40.916,72.314-26.187,107.724c5.105,12.274,13.173,21.907,25.38,27.695c6.186,2.933,8.812,1.737,10.602-4.724 c0.133-0.481,0.295-0.955,0.471-1.422c3.428-9.04,2.628-16.472-3.472-25.199c-11.118-15.906-9.135-34.319-3.771-51.961 c10.172-33.455,40.062-55.777,75.116-56.101c9.39-0.087,19.056,0.718,28.15,2.937c27.049,6.599,44.514,27.518,46.264,55.253 c1.404,22.242-2.072,43.849-11.742,64.159c-4.788,10.055-11.107,18.996-20.512,25.325c-8.835,5.945-18.496,8.341-28.979,5.602 c-14.443-3.774-22.642-16.95-18.989-31.407c3.786-14.985,8.685-29.69,12.399-44.69c1.57-6.344,2.395-13.234,1.75-19.696 c-1.757-17.601-18.387-25.809-33.933-17.216c-10.889,6.019-16.132,16.079-18.564,27.719c-2.505,11.992-1.292,23.811,2.61,35.439 c0.784,2.337,0.9,5.224,0.347,7.634c-7.063,30.799-14.617,61.49-21.306,92.369c-1.952,9.011-1.589,18.527-2.239,27.815 c-0.124,1.78-0.018,3.576-0.018,5.941C86.223,350.919,37.807,262.343,68.598,172.382C99.057,83.391,197.589,36.788,286.309,69.734 C375.281,102.774,419.287,200.947,384.795,288.954z"></path> </g></svg>
												</a>
												</div>
											</div>
										</div>
										<p>
											%19$s
										</p>
									</div>
								</div>
							</div>';

	return sprintf( $project_render_html, esc_attr( $project_title ), esc_attr( $project_description ), esc_url( $thumbnail ), esc_attr( $project_total ), esc_attr( $project_goal ), esc_attr( $project_pledges ), esc_attr( $project_days_left ), esc_url( $support_url ), esc_attr( $title_color ), esc_attr( $description_color ), esc_attr( $button_color ), esc_attr( $button_text_color ), esc_attr( $meta_color ), esc_attr( $image_alignment ), esc_attr( $share_twitter ), esc_attr( $share_facebook ), esc_attr( $share_linkedin ), esc_attr( $share_pinterest ), esc_attr( $end_type_text ) );
}

function idcf_blocks_render_grid_project( $block_attributes, $content, $block ) {
	//Load front end assets only when this block is visible
	$columns_in_grid = isset( $block_attributes['columnsInGrid'] ) ? $block_attributes['columnsInGrid'] : '3';

	$block_js_attributes = array(
		'columnsInGrid' => $columns_in_grid,
	);
	wp_enqueue_script( 'grid-block-utils', plugins_url( '/src/grid-project/utils.js', __FILE__ ), array(), '1.0.0', 'true' );
	wp_add_inline_script( 'grid-block-utils', 'var gridBlockAttributes = ' . wp_json_encode( $block_js_attributes ), 'before' );

	//Get block attributes to pull the projects from the DB
	$projects_type_selected = isset( $block_attributes['projectsType'] ) ? $block_attributes['projectsType'] : 'all';
	if ( isset( $block_attributes['projectsCategory'] ) ) {
		$projects_category       = $block_attributes['projectsCategory'];
		$projects_category_array = array(
			array(
				'taxonomy' => 'project_category',
				'field'    => 'term_id',
				'terms'    => $projects_category,
			),
		);
	} else {
		$projects_category_array = array();
	}
	$projects_category = isset( $block_attributes['projectsCategory'] ) ? $block_attributes['projectsCategory'] : '';

	if ( isset( $block_attributes['allProjects'] ) && false === $block_attributes['allProjects'] ) {
		$projects_in_grid = isset( $block_attributes['projectsInGrid'] ) ? $block_attributes['projectsInGrid'] : '6';
	} else {
		$projects_in_grid = -1;
	}

	$show_excerpt       = isset( $block_attributes['showExcerpt'] ) ? $block_attributes['showExcerpt'] : true;
	$show_image         = isset( $block_attributes['showImage'] ) ? $block_attributes['showImage'] : true;
	$show_badge         = isset( $block_attributes['showBadge'] ) ? $block_attributes['showBadge'] : false;
	$title_tag          = isset( $block_attributes['projectsTitleSize'] ) ? $block_attributes['projectsTitleSize'] : 'h2';
	$title_color        = isset( $block_attributes['titleColor'] ) ? $block_attributes['titleColor'] : '#000';
	$description_color  = isset( $block_attributes['descriptionColor'] ) ? $block_attributes['descriptionColor'] : '#000';
	$bar_chart_color    = isset( $block_attributes['barChartColor'] ) ? $block_attributes['barChartColor'] : '#3182CE';
	$category_tag_color = isset( $block_attributes['categoryTagColor'] ) ? $block_attributes['categoryTagColor'] : '#3182CE';
	$badge_color        = isset( $block_attributes['badgeColor'] ) ? $block_attributes['badgeColor'] : '#3182CE';
	$meta_color         = isset( $block_attributes['metaColor'] ) ? $block_attributes['metaColor'] : '#000';

	//Get the projects from the DB
	$args = array(
		'post_type'      => 'ignition_product',
		'tax_query'      => $projects_category_array,
		'posts_per_page' => $projects_in_grid,
	);

	$projects_query = new WP_Query( $args );

	$grid_html = '';
	if ( $projects_query->have_posts() ) {
		//Create html of the project grid
		$grid_html .= '<div class="idcf-grid-projects-block" style="grid-template-columns: repeat(' . $columns_in_grid . ', minmax(0, 1fr));">';
		$index      = 1;
		while ( $projects_query->have_posts() ) {
			$projects_query->the_post();
			$id         = get_the_ID();
			$project_id = get_post_meta( $id, 'ign_project_id', true );
			$project    = new ID_Project( $project_id );
			$deck       = new Deck( $project_id );
			$hdeck      = $deck->hDeck();
			$the_deck   = $deck->the_deck();

			$project_title        = get_the_title( $id );
			$project_description  = $show_excerpt ? html_entity_decode( $project->short_description() ) : '';
			$thumbnail            = ID_Project::get_project_thumbnail( $id );
			$project_image_html   = $show_image ? '<div ="idcf-grid-projects-block-single-img"><img src="' . $thumbnail . '" /></div>' : '';
			$project_total        = $hdeck->total;
			$project_goal         = $hdeck->goal;
			$project_pledges      = $hdeck->pledges;
			$project_days_left    = $the_deck->days_left;
			$project_end_type     = $hdeck->end_type;
			$project_percentage   = $hdeck->percentage;
			$support_url          = get_permalink( $id );
			$single_project_types = check_project_types( $project_percentage, $project_days_left, $project_end_type );
			$project_badge_html   = '';
			$project_badge_text   = '';

			if ( $show_badge && $show_image && in_array( 'successful', $single_project_types, true ) ) {
				$project_badge_text = 'Successful';
			}
			if ( $show_badge && $show_image && in_array( 'failed', $single_project_types, true ) ) {
				$project_badge_text = 'Failed';
			}

			if ( $show_badge && $show_image && '' !== $project_badge_text ) {
				$project_badge_html = '<p class="idcf-grid-projects-block-single-badge" style="background-color: ' . $badge_color . '">' . $project_badge_text . '</p>';
			}

			if ( in_array( $projects_type_selected, $single_project_types, true ) ) {
				$project_categories      = get_the_terms( $id, 'project_category' );
				$project_categories_html = '';
				if ( is_array( $project_categories ) ) {
					foreach ( $project_categories as $project_category ) {
						$project_categories_html .= '<li style="background-color:' . $category_tag_color . ';">' . $project_category->name . '</li>';
					}
				}

				$grid_html .= '<div class="idcf-grid-projects-block-single idcf-grid-projects-block-single-' . $index . '">';

				$grid_html .= '<div class="idcf-grid-projects-block-single-first">
								' . $project_image_html . '
								' . $project_badge_html . '
								<ul class="idcf-grid-projects-block-single-tags">' . $project_categories_html . '</ul>
							</div>
							<' . $title_tag . ' class="idcf-grid-projects-block-single-title"><a href="' . $support_url . '" style="color:' . $title_color . ';">' . $project_title . '</a></' . $title_tag . '>
							<p class="idcf-grid-projects-block-single-author" style="color:' . $meta_color . ';">' . get_the_author_meta( 'display_name' ) . '</p>
							<p class="idcf-grid-projects-block-single-description" style="color:' . $description_color . ';">' . $project_description . '</p>
							<div class="idcf-grid-projects-block-single-progress-bar">
									<div style="width:' . $project_percentage . '%; background-color:' . $bar_chart_color . ';"></div>
							</div>
							<div class="idcf-grid-projects-block-single-info" style="color:' . $meta_color . '">
									<div class="idcf-grid-projects-block-single-info-percentage">
										<span>' . $project_percentage . '%</span>
										<span>Funded</span>
									</div>
									<div class="idcf-grid-projects-block-single-info-total">
										<span>' . $project_total . '</span>
										<span>Raised</span>
									</div>
									<div class="idcf-grid-projects-block-single-info-days">
										<span>' . $project_days_left . '</span>
										<span>Days Left</span>
									</div>
							</div>';

				$grid_html .= '</div>';
			}
			$index++;
		}
		$grid_html .= '</div>';
	}
	return $grid_html;
}

function check_project_types( $project_percentage, $project_days_left, $project_end_type ) {
	$project_percentage = intval( $project_percentage );
	$project_days_left  = intval( $project_days_left );
	$project_types      = array( 'all' );

	if ( $project_days_left > 0 || 'open' === $project_end_type ) {
		array_push( $project_types, 'active' );
	}

	if ( $project_percentage >= 100 ) {
		array_push( $project_types, 'successful' );
	}

	if ( $project_percentage < 100 && 0 === $project_days_left && 'closed' === $project_end_type ) {
		array_push( $project_types, 'failed' );
	}

	return $project_types;
}
