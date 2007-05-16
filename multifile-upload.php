<?php
/*
Plugin Name: Multifile Upload
Plugin URI: http://wordpress.ex-libris.jp
Description: Replacement of WP's built-in uploading feature. This arrows multiple file uploading and image resize.
Author: Yoshi
Version: 1.0
Author URI: http://wordpress.ex-libris.jp
*/


function multifile_upload_upload_action() { 
	
	global $multifile_upload_thumbnail_sizes;
	$multifile_upload_thumbnail_sizes = array();
	global $action;
	
	if ($action == 'upload') :

		if (!is_array($_FILES['image']['error'])) return;

		wp_reset_vars(array('image_size_orig','image_size_thumb'));
		global $image_size_orig, $image_size_thumb;
		
		global $from_tab, $post_id, $style;
		if ( !$from_tab )
			$from_tab = 'upload';

		check_admin_referer( 'inlineuploading' );

		global $post_id, $post_title, $post_content;

		if ( !current_user_can( 'upload_files' ) )
			wp_die( __('You are not allowed to upload files.')
				. " <a href='" . get_option('siteurl') . "/wp-admin/upload.php?style=$style&amp;tab=browse-all&amp;post_id=$post_id'>"
				. __('Browse Files') . '</a>'
			);

		$overrides = array('action'=>'upload');

		$errors = array();
		$successed = array();
		
		foreach ($_FILES['image']['error'] as $key=>$value) {
		
			if ($value == 4) {
				$errors[] = "File $key(" . wp_specialchars($_FILES['image']['name'][$key]) . "): " . __( "No file was uploaded." );
				continue;
			}
			
			$the_file = array(
							'name' => $_FILES['image']['name'][$key],
							'type' => $_FILES['image']['type'][$key],
							'tmp_name' => $_FILES['image']['tmp_name'][$key],
							'error' => $_FILES['image']['error'][$key],
							'size' => $_FILES['image']['size'][$key]
						);

			$file = wp_handle_upload($the_file, $overrides);

			if ( isset($file['error']) ) {
				$errors[] = "File $key: " . $file['error'];
				continue;
			}

			$url = $file['url'];
			$type = $file['type'];
			$file = $file['file'];
			$filename = basename($file);

			// Construct the attachment array
			$attachment = array(
				'post_title' => $post_title[$key] ? $post_title[$key] : $filename,
				'post_content' => $post_content[$key],
				'post_type' => 'attachment',
				'post_parent' => $post_id,
				'post_mime_type' => $type,
				'guid' => $url
			);

			if ( preg_match('!^image/!', $type) ) {
				if (isset($image_size_orig[$key]) && is_numeric($image_size_orig[$key])) {
					$filename_tmp = preg_replace( '!(\.[^.]+)?$!', __( '___MULTIFILE_UP_TMP___' ).'$1', $filename, 1 );
					$file_tmp = str_replace($filename, $filename_tmp, $file);
					rename($file, $file_tmp);
					$file = wp_create_thumbnail( $file_tmp, $image_size_orig[$key] );
					unlink ($file_tmp);
				}

				if (isset($image_size_thumb[$key]) && is_numeric($image_size_thumb[$key])) {
					$multifile_upload_thumbnail_sizes[$filename] = ceil($image_size_thumb[$key]);
				}
			}
			
			// Save the data
			$id = wp_insert_attachment($attachment, $file, $post_id);
		
			wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
		
			$successed[] = $filename;
		}
		
		if (empty($successed)) {
			wp_die( implode('<br />', $errors ). "<br /><a href='" . get_option('siteurl')
			. "/wp-admin/upload.php?style=$style&amp;tab=$from_tab&amp;post_id=$post_id'>" . __('Back to Image Uploading') . '</a>'
			);
		}
		
		
		if (count($successed) > 1) {$id = '';}
		wp_redirect( get_option('siteurl') . "/wp-admin/upload.php?style=$style&tab=browse&action=view&ID=$id&post_id=$post_id");
		die;
		
	endif;
} 


function multifile_upload_admin_header() {
	wp_enqueue_script( 'prototype', '/wp-includes/js/prototype.js', false, '1.5.0');
	wp_enqueue_script( 'multifile_upload', '/wp-content/plugins/multifile-upload/multifile_upload.js', false, false);
	print '<style type="text/css"><!-- form#upload-file tr.resize input{width:5em;margin-right:1em;} --> </style>';
}

function multifile_upload_thumbnail_max_side_length($default = 128, $attachment_id, $file) {
	global $multifile_upload_thumbnail_sizes;
	$filename = basename($file);
	return (isset($multifile_upload_thumbnail_sizes[$filename])) ? $multifile_upload_thumbnail_sizes[$filename] : $default;
}

function multifile_upload_thumbnail_filename ($filename) {
	return str_replace('___MULTIFILE_UP_TMP___', '', $filename );
}

if ($_SERVER['PHP_SELF'] == '/wp-admin/upload.php' && $_GET['tab']=='upload'){
	add_action('admin_print_scripts', 'multifile_upload_admin_header');
	add_filter('wp_thumbnail_max_side_length', 'multifile_upload_thumbnail_max_side_length', 10, 3);
	add_filter('thumbnail_filename', 'multifile_upload_thumbnail_filename');
}
add_action( 'upload_files_upload', 'multifile_upload_upload_action', 9);

?>