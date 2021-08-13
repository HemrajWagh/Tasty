<?php

add_action( 'wp_ajax_flag_ajax_operation', 'flag_ajax_operation' );

function flag_ajax_operation() {
	global $wpdb;
	// if nonce is not correct it returns -1
	check_ajax_referer( "flag-ajax" );
	// check for correct capability
	if ( ! is_user_logged_in() ) {
		die( '-1' );
	}
	// check for correct FlAG capability
	if ( ! current_user_can( 'FlAG Upload images' ) || ! current_user_can( 'FlAG Manage gallery' ) ) {
		die( '-1' );
	}
	// include the flag function
	include_once( dirname( __FILE__ ) . '/functions.php' );
	// Get the image id
	if ( isset( $_POST['image'] ) ) {
		$id = (int) $_POST['image'];
		// let's get the image data
		$picture = flagdb::find_image( $id );
		// what do you want to do ?
		switch ( $_POST['operation'] ) {
			case 'create_thumbnail' :
				$result = flagAdmin::create_thumbnail( $picture );
				break;
			case 'resize_image' :
				$result = flagAdmin::resize_image( $picture );
				break;
			case 'webview_image' :
				$result = flagAdmin::webview_image( $picture );
				break;
			case 'import_metadata' :
				$result = flagAdmin::import_MetaData( $id );
				break;
			case 'copy_metadata' :
				$result = flagAdmin::copy_MetaData( $id );
				break;
			case 'get_image_ids' :
				$result = flagAdmin::get_image_ids( $id );
				break;
			default :
				do_action( 'flag_ajax_' . sanitize_key( $_POST['operation'] ) );
				die( '-1' );
				break;
		}
		// A success should return a '1'
		die ( $result );
	}
	// The script should never stop here
	die( '0' );
}

add_action( 'wp_ajax_flagCreateNewThumb', 'flagCreateNewThumb' );

function flagCreateNewThumb() {

	global $wpdb;

	// check for correct capability
	if ( ! is_user_logged_in() ) {
		die( '-1' );
	}
	// check for correct FlAG capability
	if ( ! current_user_can( 'FlAG Manage gallery' ) ) {
		die( '-1' );
	}

	require_once( dirname( dirname( __FILE__ ) ) . '/flag-config.php' );
	include_once( flagGallery::graphic_library() );

	$flag_options = get_option( 'flag_options' );

	$id      = (int) $_POST['id'];
	$picture = flagdb::find_image( $id );

	$x = round( $_POST['x'] * $_POST['rr'], 0 );
	$y = round( $_POST['y'] * $_POST['rr'], 0 );
	$w = round( $_POST['w'] * $_POST['rr'], 0 );
	$h = round( $_POST['h'] * $_POST['rr'], 0 );

	$thumb = new flag_Thumbnail( $picture->imagePath, true );

	$thumb->crop( $x, $y, $w, $h );

	$thumb->resize( $flag_options['thumbWidth'], $flag_options['thumbHeight'] );

	if ( $thumb->save( $picture->thumbPath, 100 ) ) {
		//read the new sizes
		$new_size       = @getimagesize( $picture->thumbPath );
		$size['width']  = $new_size[0];
		$size['height'] = $new_size[1];

		// add them to the database
		flagdb::update_image_meta( $picture->pid, [ 'thumbnail' => $size ] );

		echo "OK";
	} else {
		header( 'HTTP/1.1 500 Internal Server Error' );
		echo "KO";
	}

	exit();

}

add_action( 'wp_ajax_flag_save_album', 'flag_save_album' );

function flag_save_album() {

	global $wpdb;

	// check for correct capability
	if ( ! is_user_logged_in() ) {
		die( '-1' );
	}
	// check for correct FlAG capability
	if ( ! current_user_can( 'FlAG Manage others gallery' ) ) {
		die( '-1' );
	}

	if ( isset( $_POST['form'] ) ) {
		parse_str( $_POST['form'], $output );
	}
	$result     = false;
	$album_id   = isset( $output['album_id'] ) ? intval( $output['album_id'] ) : 0;
	$album_name = wp_check_invalid_utf8( wp_strip_all_tags( $output['album_name'] ) );
	$g          = isset( $output['g'] ) ? (array) $output['g'] : [];
	if ( $album_name && $album_id ) {
		if ( count( $g ) ) {
			$galstring = implode( ',', $g );
		} else {
			$galstring = '';
		}
		$result = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->flagalbum} SET name = %s, categories = %s WHERE id = %s", $album_name, $galstring, $album_id ) );
	}

	if ( $result ) {
		_e( 'Success', 'flash-album-gallery' );
	}

	exit();

}

add_action( 'wp_ajax_flag_delete_album', 'flag_delete_album' );

function flag_delete_album() {

	global $wpdb;

	// check for correct capability
	if ( ! is_user_logged_in() ) {
		die( '-1' );
	}
	// check for correct FlAG capability
	if ( ! current_user_can( 'FlAG Manage gallery' ) ) {
		die( '-1' );
	}

	$result = false;
	if ( isset( $_POST['post'] ) ) {
		$result = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->flagalbum} WHERE id = %d", $_POST['post'] ) );
	}

	if ( $result ) {
		_e( 'Success', 'flash-album-gallery' );
	}

	exit();

}

add_action( 'wp_ajax_flag_banner_crunch', 'flag_banner_crunch' );

function flag_banner_crunch() {

	// check for correct capability
	if ( ! is_user_logged_in() ) {
		die( '-1' );
	}
	// check for correct FlAG capability
	if ( ! current_user_can( 'FlAG Manage gallery' ) ) {
		die( '-1' );
	}

	if ( isset( $_POST['path'] ) ) {
		include_once( dirname( __FILE__ ) . '/functions.php' );
		$id   = flagAdmin::handle_import_file( $_POST['path'] );
		$file = basename( $_POST['path'] );
		if ( is_wp_error( $id ) ) {
			echo '<p class="error">' . sprintf( __( '<em>%s</em> was <strong>not</strong> imported due to an error: %s', 'flash-album-gallery' ), $file, $id->get_error_message() ) . '</p>';
		} else {
			echo '<p class="success">' . sprintf( __( '<em>%s</em> has been added to Media library', 'flash-album-gallery' ), $file ) . '</p>';
		}
	}

	exit();
}

add_action( 'wp_ajax_flag_file_browser', 'flag_ajax_file_browser' );

/**
 * jQuery File Tree PHP Connector
 *
 * @author  Cory S.N. LaViska - A Beautiful Site (http://abeautifulsite.net/)
 * @version 1.0.1
 *
 * @return string folder content
 */
function flag_ajax_file_browser() {

	// check for correct NextGEN capability
	if ( ! current_user_can( 'FlAG Import folder' ) ) {
		die( 'No access' );
	}

	if ( ! defined( 'ABSPATH' ) ) {
		die( 'No access' );
	}

	// if nonce is not correct it returns -1
	check_ajax_referer( 'flag-ajax', 'nonce' );

	//PHP4 compat script
	if ( ! function_exists( 'scandir' ) ) {
		function scandir( $dir, $listDirectories = false, $skipDots = true ) {
			$dirArray = [];
			if ( $handle = opendir( $dir ) ) {
				while ( false !== ( $file = readdir( $handle ) ) ) {
					if ( ( $file != '.' && $file != '..' ) || $skipDots == true ) {
						if ( $listDirectories == false ) {
							if ( is_dir( $file ) ) {
								continue;
							}
						}
						array_push( $dirArray, basename( $file ) );
					}
				}
				closedir( $handle );
			}

			return $dirArray;
		}
	}

	// start from the default path
	$root = trailingslashit( WINABSPATH );
	// get the current directory
	$dir = trailingslashit( urldecode( $_POST['dir'] ) );

	if ( file_exists( $root . $dir ) && false === strpos( $dir, '..' ) ) {
		$files = scandir( $root . $dir );
		natcasesort( $files );

		// The 2 counts for . and ..
		if ( count( $files ) > 2 ) {
			echo "<ul class=\"jqueryDirTree\" style=\"display: none;\">";

			// return only directories
			foreach ( $files as $file ) {

				//reserved name for the thumnbnails, don't use it as folder name
				if ( $file == 'thumbs' ) {
					continue;
				}

				if ( file_exists( $root . $dir . $file ) && $file != '.' && $file != '..' && is_dir( $root . $dir . $file ) ) {
					echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . esc_html( $dir . $file ) . "/\">" . esc_html( $file ) . "</a></li>";
				}
			}

			echo "</ul>";
		}
	}

	die();
}

add_action( 'wp_ajax_flag_plupload_uploader', 'flag_ajax_plupload_uploader' );
function flag_ajax_plupload_uploader() {
	global $flag;

	//check for correct capability
	if ( ! is_user_logged_in() ) {
		die( 'Login failure. -1' );
	}
	//check for correct capability
	if ( ! current_user_can( 'FlAG Upload images' ) ) {
		die( 'You do not have permission to upload files. -2' );
	}
	//check for correct nonce
	check_ajax_referer( 'flag_upload' );

	include_once( FLAG_ABSPATH . 'admin/functions.php' );
	// get the gallery
	$galleryID = (int) $_POST['galleryselect'];

	echo flagAdmin::swfupload_image( $galleryID );
	die();
}

add_action( 'wp_ajax_flagallery_shortcode_html', 'flagallery_ajax_shortcode_html' );
function flagallery_ajax_shortcode_html() {

	//check for correct capability.
	if ( ! is_user_logged_in() ) {
		die( 'Login failure. -1' );
	}

	check_ajax_referer( 'FlaGallery' );

	if ( isset( $_POST['shortcode'] ) ) {
		global $flag;
		require_once (dirname (dirname (__FILE__) ) . '/lib/skinobject.php');
		require_once (dirname (dirname (__FILE__) ) . '/lib/shortcodes.php');

		// Compatibilbity.
		add_filter( 'jetpack_lazy_images_skip_image_with_attributes', array( $flag, 'jetpack_no_lazy_src' ), 10, 2 );
		add_filter( 'jetpack_lazy_images_blacklisted_classes', array( $flag, 'jetpack_no_lazy_classes' ), 10 );
		add_filter( 'a3_lazy_load_skip_images_classes', array( $flag, 'a3_no_lazy' ), 10 );

		$shortcode = sanitize_text_field( $_POST['shortcode'] );
		echo do_shortcode( $shortcode );

		print_late_styles();
	}
	die();
}

add_action( 'wp_ajax_flag_shortcode_helper', 'flag_ajax_shortcode_helper' );
function flag_ajax_shortcode_helper() {
	//check for correct capability
	if ( ! is_user_logged_in() ) {
		die( 'Login failure. -1' );
	}
	//check for correct capability
	if ( ! current_user_can( 'FlAG Use TinyMCE' ) ) {
		die( 'You do not have permission to upload files. -2' );
	}

	$media_button = isset( $_GET['media_button'] ) && $_GET['media_button'] == 'true' ? true : false;
	$riched       = isset( $_GET['riched'] ) && $_GET['riched'] == 'true' ? true : false;

	include_once( FLAG_ABSPATH . 'admin/tinymce/window.php' );

	die();
}

add_action( 'wp_ajax_flagallery_skin_interaction', 'flagallery_skin_interaction' );
add_action( 'wp_ajax_nopriv_flagallery_skin_interaction', 'flagallery_skin_interaction' );
add_action( 'wp_ajax_flagallery_module_interaction', 'flagallery_module_interaction' );
add_action( 'wp_ajax_nopriv_flagallery_module_interaction', 'flagallery_module_interaction' );
function flagallery_module_interaction() {
	global $wpdb;

	if ( empty( $_SERVER['HTTP_REFERER'] ) ) {
		header( $_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request' );
		die();
	}

	$ref = $_SERVER['HTTP_REFERER'];
	//$uip = str_replace('.', '', $_SERVER['REMOTE_ADDR'])
	if ( ( false === strpos( $ref, get_home_url() ) ) && ( false === strpos( $ref, get_site_url() ) ) ) {
		header( $_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request' );
		die();
	}
	if ( ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) || ! isset( $_SERVER['HTTP_HOST'] ) || ! strpos( get_home_url(), $_SERVER['HTTP_HOST'] ) ) {
		header( $_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request' );
		die();
	}

	$upd = [ 'pid' => false, 'vote' => false, 'hit' => false, 'reset' => false ];

	if ( ( $pid = intval( $_POST['hit'] ) ) ) {
		$upd['pid']  = $pid;
		$upd['hit']  = isset( $_POST['hit'] );
		$upd['vote'] = isset( $_POST['vote'] );

		flag_update_counter( $upd );

		$result = $wpdb->get_results( "SELECT hitcounter, total_votes FROM $wpdb->flagpictures WHERE `pid` = $pid" );

		$meta['views'] = intval( $result[0]->hitcounter );
		$meta['likes'] = intval( $result[0]->total_votes );
		$meta          = array_map( 'intval', $meta );

		header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
		echo json_encode( $meta );
		die();
	}

	die();
}

/**
 * Update image hitcounter in the database
 *
 * @param $upd
 */
function flag_update_counter( $upd ) {
	global $wpdb;

	if ( $pid = abs( intval( $upd['pid'] ) ) ) {
		if ( $upd['reset'] == false ) {
			if ( $upd['hit'] ) {
				$wpdb->query( "UPDATE $wpdb->flagpictures SET `hitcounter` = `hitcounter`+1 WHERE pid = $pid" );
			}
			if ( $upd['vote'] ) {
				$wpdb->query( "UPDATE $wpdb->flagpictures SET `total_votes` = IF(hitcounter > total_votes, total_votes+1, hitcounter) WHERE pid = $pid" );
			}
		} else {
			if ( $upd['hit'] ) {
				$hit = abs( intval( $upd['hit'] ) );
				$wpdb->query( "UPDATE $wpdb->flagpictures SET `hitcounter` = $hit WHERE pid = $pid" );
			}
			if ( $upd['vote'] == 1 ) {
				$vote = abs( intval( $upd['vote'] ) );
				$wpdb->query( "UPDATE $wpdb->flagpictures SET `total_votes` = IF(hitcounter > $vote, $vote, hitcounter) WHERE pid = $pid" );
			}
		}
	}

}

add_action( 'wp_ajax_flagallery_update_counters', 'flagallery_update_counters' );
add_action( 'wp_ajax_nopriv_flagallery_update_counters', 'flagallery_update_counters' );
function flagallery_update_counters() {
	global $wpdb;

	if ( empty( $_SERVER['HTTP_REFERER'] ) ) {
		header( $_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request' );
		die();
	}

	$ref = $_SERVER['HTTP_REFERER'];
	//$uip = str_replace('.', '', $_SERVER['REMOTE_ADDR'])
	if ( ( false === strpos( $ref, get_home_url() ) ) && ( false === strpos( $ref, get_site_url() ) ) ) {
		header( $_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request' );
		die();
	}
	if ( ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) || ! isset( $_SERVER['HTTP_HOST'] ) || ! strpos( get_home_url(), $_SERVER['HTTP_HOST'] ) ) {
		header( $_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request' );
		die();
	}

	$json = (array) json_decode( stripslashes( $_POST['json'] ) );
	//$upd = array('pid'=>false,'vote'=>false,'hit'=>false,'reset'=>false);
	$upd_array = [];
	if ( is_array( $json ) ) {
		if ( isset( $json['views'] ) && is_array( $json['views'] ) ) {
			foreach ( $json['views'] as $id ) {
				if ( (int) $id ) {
					$upd_array[ $id ] = [ 'pid' => $id, 'hit' => 1, 'vote' => false, 'reset' => false ];
				}
			}
		}
		if ( isset( $json['likes'] ) && is_array( $json['likes'] ) ) {
			foreach ( $json['likes'] as $id ) {
				if ( isset( $upd_array[ $id ] ) ) {
					$upd_array[ $id ]['vote'] = 1;
				}
			}
		}
	}

	$meta = [];
	foreach ( $upd_array as $upd ) {
		$pid = (int) $upd['pid'];
		flag_update_counter( $upd );
		$result = $wpdb->get_results( "SELECT hitcounter, total_votes FROM $wpdb->flagpictures WHERE `pid` = $pid" );
		if ( $result ) {
			$meta[ $pid ] = [
				'views' => intval( $result[0]->hitcounter ),
				'likes' => intval( $result[0]->total_votes ),
			];
		}
	}

	header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
	echo json_encode( $meta );
	die();
}
