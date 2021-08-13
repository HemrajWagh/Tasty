<?php

/**
 * Main PHP class for the WordPress plugin FlaGallery
 *
 */
class flagGallery {

	/**
	 * Show a system messages
	 */
	static function show_message( $message ) {
		echo '<div class="wrap"><h2></h2><div class="updated fade" id="message"><p>' . $message . '</p></div></div>' . "\n";
	}

	/**
	 * flagGallery::create_webview_folder()
	 *
	 * @param mixed $gallerypath
	 * @param bool  $include_Abspath
	 *
	 * @return string $foldername
	 */
	static function create_webview_folder( $gallerypath, $include_Abspath = true ) {
		if ( ! $include_Abspath ) {
			$gallerypath = WINABSPATH . $gallerypath;
		}

		if ( ! file_exists( $gallerypath ) ) {
			return false;
		}

		if ( is_dir( $gallerypath . '/webview/' ) ) {
			return '/webview/';
		}

		if ( is_admin() ) {
			if ( ! is_dir( $gallerypath . '/webview/' ) ) {
				if ( ! wp_mkdir_p( $gallerypath . '/webview/' ) ) {
					if ( FLAG_SAFE_MODE ) {
						flagAdmin::check_safemode( $gallerypath . '/webview/' );
					} else {
						flagGallery::show_error( __( 'Unable to create directory ', 'flash-album-gallery' ) . $gallerypath . '/webview !' );
					}

					return false;
				}

				return '/webview/';
			}
		}

		return false;

	}

	/**
	 * Show a error messages
	 */
	static function show_error( $message ) {
		echo '<div class="wrap"><h2></h2><div class="error" id="error"><p>' . $message . '</p></div></div>' . "\n";
	}

	/**
	 * flagGallery::get_thumbnail_folder()
	 *
	 * @param mixed $gallerypath
	 * @param bool  $include_Abspath
	 *
	 * @return string $foldername
	 * @deprecated use create_thumbnail_folder() if needed;
	 */
	static function get_thumbnail_folder( $gallerypath, $include_Abspath = true ) {
		return flagGallery::create_thumbnail_folder( $gallerypath, $include_Abspath );
	}

	/**
	 * flagGallery::get_thumbnail_folder()
	 *
	 * @param mixed $gallerypath
	 * @param bool  $include_Abspath
	 *
	 * @return string $foldername
	 */
	static function create_thumbnail_folder( $gallerypath, $include_Abspath = true ) {
		if ( ! $include_Abspath ) {
			$gallerypath = WINABSPATH . $gallerypath;
		}

		if ( ! file_exists( $gallerypath ) ) {
			return false;
		}

		if ( is_dir( $gallerypath . '/thumbs/' ) ) {
			return '/thumbs/';
		}

		if ( is_admin() ) {
			if ( ! is_dir( $gallerypath . '/thumbs/' ) ) {
				if ( ! wp_mkdir_p( $gallerypath . '/thumbs/' ) ) {
					if ( FLAG_SAFE_MODE ) {
						flagAdmin::check_safemode( $gallerypath . '/thumbs/' );
					} else {
						flagGallery::show_error( __( 'Unable to create directory ', 'flash-album-gallery' ) . $gallerypath . '/thumbs !' );
					}

					return false;
				}

				return '/thumbs/';
			}
		}

		return false;

	}

	/**
	 * flagGallery::graphic_library() - switch between GD and ImageMagick
	 *
	 * @return string path to the selected library
	 */
	static function graphic_library() {

		return FLAG_ABSPATH . '/lib/gd.thumbnail.inc.php';

	}

	/**
	 * Support for i18n with polyglot or qtrans
	 *
	 * @param string $in
	 *
	 * @return string $in localized
	 */
	static function i18n( $in ) {

		if ( function_exists( 'langswitch_filter_langs_with_message' ) ) {
			$in = langswitch_filter_langs_with_message( $in );
		}

		if ( function_exists( 'polyglot_filter' ) ) {
			$in = polyglot_filter( $in );
		}

		if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			$in = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $in );
		}

		$in = apply_filters( 'localization', $in );

		return $in;
	}

	/**
	 * Check the memory_limit and calculate a recommended memory size
	 *
	 * @return string message about recommended image size
	 */
	static function check_memory_limit() {

		if ( ( function_exists( 'memory_get_usage' ) ) && ( ini_get( 'memory_limit' ) ) ) {

			// get memory limit
			$memory_limit = ini_get( 'memory_limit' );
			if ( $memory_limit === '' ) {
				return false;
			}
			if ( preg_match( '/^(\d+)(.)$/', $memory_limit, $matches ) ) {
				if ( 'M' === strtoupper( $matches[2] ) ) {
					$memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB.
				} elseif ( 'G' === strtoupper( $matches[2] ) ) {
					$memory_limit = $matches[1] * 1024 * 1024 * 1024; // nnnG -> nnn GB.
				} elseif ( 'K' === strtoupper( $matches[2] ) ) {
					$memory_limit = $matches[1] * 1024; // nnnK -> nnn KB.
				}
			}

			// calculate the free memory
			$freeMemory = $memory_limit - memory_get_usage();

			// build the test sizes
			$sizes   = [];
			$sizes[] = [ 'width' => 800, 'height' => 600 ];
			$sizes[] = [ 'width' => 1024, 'height' => 768 ];
			$sizes[] = [ 'width' => 1280, 'height' => 960 ];  // 1MP
			$sizes[] = [ 'width' => 1600, 'height' => 1200 ]; // 2MP
			$sizes[] = [ 'width' => 2016, 'height' => 1512 ]; // 3MP
			$sizes[] = [ 'width' => 2272, 'height' => 1704 ]; // 4MP
			$sizes[] = [ 'width' => 2560, 'height' => 1920 ]; // 5MP

			// test the classic sizes
			foreach ( $sizes as $size ) {
				// very, very rough estimation
				if ( $freeMemory < round( $size['width'] * $size['height'] * 5.09 ) ) {
					$result = sprintf( __( 'Note : Based on your server memory limit you should not upload larger images then <strong>%d x %d</strong> pixel', 'flash-album-gallery' ), $size['width'], $size['height'] );

					return $result;
				}
			}
		}

		return false;
	}

	/**
	 * Slightly modfifed version of pathinfo(), clean up filename & rename jpeg to jpg
	 *
	 * @param string $name The name being checked.
	 *
	 * @return array containing information about file
	 */
	static function fileinfo( $name ) {

		//Sanitizes a filename replacing whitespace with dashes
		$name = sanitize_flagname( $name );

		//get the parts of the name
		$filepart = pathinfo( $name );

		if ( empty( $filepart ) ) {
			return false;
		}

		// required until PHP 5.2.0
		if ( empty( $filepart['filename'] ) ) {
			$filepart['filename'] = substr( $filepart['basename'], 0, strlen( $filepart['basename'] ) - ( strlen( $filepart['extension'] ) + 1 ) );
		}

		$filepart['filename'] = sanitize_title_with_dashes( $filepart['filename'] );

		if ( empty( $filepart['filename'] ) ) {
			$filepart['filename'] = str_replace( [ ' ', ':' ], [ '_', '' ], current_time( 'mysql' ) );
		}

		//extension jpeg will not be recognized by the slideshow, so we rename it
		$filepart['extension'] = ( empty( $filepart['extension'] ) || $filepart['extension'] == 'jpeg' ) ? 'jpg' : $filepart['extension'];

		//combine the new file name
		$filepart['basename'] = $filepart['filename'] . '.' . $filepart['extension'];

		return $filepart;
	}

	/**
	 * Function used to delete a folder.
	 *
	 * @param string $path full-path to folder
	 *
	 * @return bool result of deletion
	 */
	static function flagFolderDelete( $path ) {
		if ( is_dir( $path ) ) {
			if ( version_compare( PHP_VERSION, '5.0.0' ) < 0 ) {
				$entries = [];
				if ( $handle = opendir( $path ) ) {
					while ( false !== ( $file = readdir( $handle ) ) ) {
						$entries[] = $file;
					}
					closedir( $handle );
				}
			} else {
				$entries = scandir( $path );
				if ( $entries === false ) {
					$entries = [];
				}
			}
			foreach ( $entries as $entry ) {
				if ( $entry != '.' && $entry != '..' ) {
					flagGallery::flagFolderDelete( $path . '/' . $entry );
				}
			}

			return @rmdir( $path );
		} elseif ( file_exists( $path ) ) {
			return @unlink( $path );
		} else {
			return false;
		}
	}

	static function saveFile( $sName, $sContent, $mode = 'w+' ) {
		if ( ! $dFile = fopen( $sName, $mode ) ) {
			flagGallery::show_error( __( "Can't create/open file '", "flash-album-gallery" ) . $sName . "'." );
			exit;
		}
		flock( $dFile, LOCK_EX );
		ftruncate( $dFile, 0 );
		if ( fwrite( $dFile, $sContent ) === false ) {
			flagGallery::show_error( __( "Can't write data to file '", "flash-album-gallery" ) . $sName . "'." );
			exit;
		}
		fflush( $dFile );
		flock( $dFile, LOCK_UN );
		fclose( $dFile );

		return true;
	}

	static function flag_wpmu_enable_function( $value ) {
		if ( IS_WPMU ) {
			$flag_options = get_site_option( 'flag_options' );

			return $flag_options[ $value ];
		}

		// if this is not WPMU, enable it !
		return true;
	}

	static function flagGetBetween( $content, $start, $end ) {
		$r = explode( $start, $content );
		if ( isset( $r[1] ) ) {
			$r = explode( $end, $r[1] );

			return $r[0];
		}

		return '';
	}

	/*
	 * Save file
	 * @param $sName    - file name
	 * @param $sContent - file content
	 * @param $mode     - open file mode
	 * @return the number of bytes written, or FALSE on error.
	 */

	static function flagSaveWpMedia() {
		global $wpdb;
		if ( ! empty( $_POST['item_a'] ) ) {
			foreach ( $_POST['item_a'] as $item_id => $item ) {
				$post        = $_post = get_post( $item_id, ARRAY_A );
				$postmeta    = get_post_meta( $item_id, 'thumbnail', true );
				$postlink    = get_post_meta( $item_id, 'link', true );
				$postpreview = get_post_meta( $item_id, 'preview', true );
				if ( isset( $item['post_content'] ) ) {
					$post['post_content'] = esc_sql( $item['post_content'] );
				}
				if ( isset( $item['post_title'] ) ) {
					$post['post_title'] = esc_sql( $item['post_title'] );
				}

				$post = apply_filters( 'attachment_fields_to_save', $post, $item );

				if ( isset( $item['post_thumb'] ) && $item['post_thumb'] != $postmeta ) {
					/*$thumb = image_resize( $item['post_thumb'], $max_w=200, $max_h=200, $crop = true, $suffix = null, $dest_path = null, $jpeg_quality = 90 );
					if(is_string($thumb))
						update_post_meta($item_id, 'thumbnail', $thumb);
					else*/
					update_post_meta( $item_id, 'thumbnail', $item['post_thumb'] );
				}
				if ( isset( $item['link'] ) && $item['link'] != $postlink ) {
					update_post_meta( $item_id, 'link', $item['link'] );
				}
				if ( isset( $item['preview'] ) && $item['preview'] != $postpreview ) {
					update_post_meta( $item_id, 'preview', $item['preview'] );
				}
				if ( isset( $post['errors'] ) ) {
					$errors[ $item_id ] = $post['errors'];
					unset( $post['errors'] );
				}
				if ( $post != $_post ) {
					wp_update_post( $post );
				}
			}
		}
	}

	/**
	 * Convert a hex color to rgb
	 *
	 * @param $hex
	 *
	 * @return array [r ,g, b]
	 */
	static function hex2rgb( $hex ) {
		require_once( dirname( __FILE__ ) . '/color.php' );

		$color = new flagColor( $hex );
		$rgb   = $color->getRgb();

		return [ $rgb['R'], $rgb['G'], $rgb['B'] ];
	}

	/**
	 * Return gmColor class
	 *
	 * @param $hex
	 *
	 * @return object class
	 */
	static function color( $hex = null ) {
		require_once( dirname( __FILE__ ) . '/color.php' );

		return new flagColor( $hex );
	}

	/**
	 * get the thumbnail url to the image
	 */
	function get_thumbnail_url( $imageID, $picturepath = '', $fileName = '' ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		// safety first
		$imageID = (int) $imageID;

		// get gallery values
		if ( empty( $fileName ) ) {
			list( $fileName, $picturepath ) = $wpdb->get_row( "SELECT p.filename, g.path FROM $wpdb->flagpictures AS p INNER JOIN $wpdb->flaggallery AS g ON (p.galleryid = g.gid) WHERE p.pid = '$imageID' ", ARRAY_N );
		}

		if ( empty( $picturepath ) ) {
			$picturepath = $wpdb->get_var( "SELECT g.path FROM $wpdb->flagpictures AS p INNER JOIN $wpdb->flaggallery AS g ON (p.galleryid = g.gid) WHERE p.pid = '$imageID' " );
		}

		// set gallery url
		$folder_url   = get_option( 'siteurl' ) . '/' . $picturepath . flagGallery::create_thumbnail_folder( $picturepath, false );
		$thumbnailURL = $folder_url . 'thumbs_' . $fileName;

		return $thumbnailURL;
	}

	/**
	 * get the complete url to the image
	 */
	function get_image_url( $imageID, $picturepath = '', $fileName = '' ) {
		/** @var $wpdb wpdb */
		global $wpdb;

		// safety first
		$imageID = (int) $imageID;

		// get gallery values
		if ( empty( $fileName ) ) {
			list( $fileName, $picturepath ) = $wpdb->get_row( "SELECT p.filename, g.path FROM $wpdb->flagpictures AS p INNER JOIN $wpdb->flaggallery AS g ON (p.galleryid = g.gid) WHERE p.pid = '$imageID' ", ARRAY_N );
		}

		if ( empty( $picturepath ) ) {
			$picturepath = $wpdb->get_var( "SELECT g.path FROM $wpdb->flagpictures AS p INNER JOIN $wpdb->flaggallery AS g ON (p.galleryid = g.gid) WHERE p.pid = '$imageID' " );
		}

		// set gallery url
		$imageURL = get_option( 'siteurl' ) . '/' . $picturepath . '/' . $fileName;

		return $imageURL;
	}

	/**
	 * flagGallery::get_thumbnail_prefix() - obsolete
	 *
	 * @param string $gallerypath
	 * @param bool   $include_Abspath
	 *
	 * @return string  "thumbs_";
	 * @deprecated prefix is now fixed to "thumbs_";
	 */
	function get_thumbnail_prefix( $gallerypath, $include_Abspath = true ) {
		return 'thumbs_';
	}

	function getUserNow( $userAgent ) {
		$crawlers  = 'Google|msnbot|Rambler|Yahoo|AbachoBOT|accoona|FeedBurner|' .
		             'AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|' .
		             'GeonaBot|Gigabot|Lycos|MSRBOT|Scooter|AltaVista|IDBot|eStyle|Scrubby|yandex';
		$isCrawler = ( preg_match( "/$crawlers/i", $userAgent ) > 0 );

		return $isCrawler;
	}

	/** Get skins update info
	 *
	 * @return array
	 */
	static function get_skins_update_info() {

		require_once ( dirname( dirname(__FILE__) ) . '/admin/get_skin.php');

		$all_gallery_skins_by_file = get_skins( false, '' );
		$all_gallery_skins         = [];

		foreach ( (array) $all_gallery_skins_by_file as $skin_file => $skin_data ) {
			if ( ! empty( $skin_data['uid'] ) ) {
				$suid                       = (string) $skin_data['uid'];
				$all_gallery_skins[ $suid ] = $skin_data;
			}
		}


		$all_skins_arr = [];
		$skins_by_type = [ 'gallery' => [] ];

		$update_skins = [];
		$new_skins    = [];

		$skins_remote_xml = 'http://mypgc.co/depository/skins2.xml';
		$get_xml          = wp_remote_get( $skins_remote_xml, [ 'sslverify' => true ] );
		if ( ! is_wp_error( $get_xml ) && ( 200 == $get_xml['response']['code'] ) ) {
			$xml = @simplexml_load_string( $get_xml['body'] );
			if ( ! empty( $xml ) ) {
				foreach ( $xml as $skin ) {
					$suid                                = (string) $skin->uid;
					$skintype                            = (string) $skin->type;
					$all_skins_arr[ $suid ]              = get_object_vars( $skin );
					$skins_by_type[ $skintype ][ $suid ] = $all_skins_arr[ $suid ];
				}
			}
		}

		foreach ( (array) $skins_by_type['gallery'] as $suid => $skin_data ) {
			if ( isset( $all_gallery_skins[ $suid ] ) ) {
				if ( version_compare( (float) $skin_data['version'], (float) $all_gallery_skins[ $suid ]['Version'], '>' ) ) {
					$update_skins[] = $suid;
				}
			} else {
				$new_skins[] = $suid;
			}
		}

		return compact( 'update_skins', 'new_skins' );
	}

}
