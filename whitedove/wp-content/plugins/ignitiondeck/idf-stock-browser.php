<?php

/**
 * Fetches stock photos from Unsplash.
 *
 * This function fetches stock photos from Unsplash by scraping the website and extracting
 * the image URLs. It uses file_get_contents to retrieve the HTML content and then parses
 * it using DOMDocument. It then extracts the image URLs and returns them as an object.
 * 
 * @todo Is this still being used anywhere?
 *
 * @param string $content Optional content parameter. Default is an empty string.
 * @return stdClass An object containing the fetched image URLs.
 */
function idf_fetch_stock($content = '') {
    $url = 'https://unsplash.com';

    // Perform the HTTP GET request
    $response = wp_remote_get( $url );

    // Check for errors in the response
    if ( is_wp_error( $response ) ) {
        // Handle the error appropriately
        $error_message = $response->get_error_message();
        // Log the error or notify the user
        return new stdClass(); // Return an empty object or handle as needed
    }

    // Retrieve the body of the response
    $data = wp_remote_retrieve_body( $response );

    // Load the HTML content
    $doc = new DOMDocument();
    @$doc->loadHTML($data);

    // Extract image elements
    $images = $doc->getElementsByTagName('img');
    $photos = new stdClass();
    $i = 0;

    foreach ($images as $image) {
        $class = $image->getAttribute('class');
        if (strpos($class, 'photo__image') !== false) {
            $photo = $image->getAttribute('src');
            $photos->$i = $photo;
        }
        $i++;
    }

    return $photos;
}

//add_action('the_content', 'idf_stock_test');

/**
 * Test function for fetching and displaying stock photos.
 *
 * This function fetches stock photos using idf_fetch_stock() and displays them in a lightbox gallery
 * if any photos are returned. It appends the gallery to the provided content and returns the modified content.
 *
 * @param string $content The content to which the stock photos gallery will be appended.
 * @return string The modified content with the stock photos gallery appended.
 */
function idf_stock_test($content) {
	$photos = idf_fetch_stock();
	if (!empty($photos)) { 
		$content .= '<div class="idc_lightbox idf_stock_gallery" style="display: none;">';
		foreach ($photos as $photo) {
			$content .= '<a class="idf_stock_item_wrapper" href="#"><img class="idf_stock_item" src="'.$photo.'"/></a>';
		}
		$content .= '</div>';
	}
	return $content;
}

add_action('wp_ajax_idf_stock_item_click', 'idf_stock_item_click');

/**
 * Handle the click event for stock items.
 *
 * This function handles the click event for stock items. It retrieves the URL of the clicked
 * item, downloads the image, and inserts it as a WordPress attachment.
 *
 * @return void
 */
function idf_stock_item_click() {
	// Check if the user has the required capability
	if (!current_user_can('read')) {
		wp_send_json_error(__('You do not have sufficient permissions to perform this action.', 'memberdeck'));
		exit;
	}
	if (isset($_GET['wp_id_nonce'])) {
		check_admin_referer('wp_id_nonce', 'wp_id_nonce');
	}
	if (isset($_POST['Url'])) {
		$url = sanitize_text_field($_POST['Url']);
		if (!empty($url)) {
			$type = exif_imagetype($url);
			$extension = image_type_to_extension($type);
			$wp_upload_dir = wp_upload_dir();
			$file = $wp_upload_dir['path'].'/id_stock_'.uniqid().$extension;
			$copy = copy($url, $file);
			$title = preg_replace('/\.[^.]+$/', '', basename($file, $extension));
			$mime = mime_content_type($file);
			if ($copy) {
				$file_info = array(
					'name' => $title.$extension,
					'type' => $mime,
					'tmp_name' => $file,
					'error' => 0,
					'size' => filesize($file),
				);
				if ( ! function_exists( 'wp_handle_sideload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
				$sideload = wp_handle_sideload($file_info, array('test_form' => false));
				$attachment = array(
			    	'guid' => $sideload['url'], 
			    	'post_mime_type' => $mime,
			    	'post_title' => $title,
			    	'post_content' => '',
			    	'post_status' => 'inherit'
			  	);
			  	$insert = wp_insert_attachment($attachment, $sideload['file']);
			  	// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
				require_once (ABSPATH . 'wp-admin/includes/image.php');
				require_once (ABSPATH . 'wp-admin/includes/media.php');
				// Generate the metadata for the attachment, and update the database record.
				$attach_data = wp_generate_attachment_metadata($insert, $sideload['file']);
				wp_update_attachment_metadata($insert, $attach_data);
			}
		}
	}
	exit;
}
?>