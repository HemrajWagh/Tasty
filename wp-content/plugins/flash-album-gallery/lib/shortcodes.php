<?php
/**
 * @description Use WordPress Shortcode API for more features
 * @Docs http://codex.wordpress.org/Shortcode_API
 */

if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FlAG_shortcodes {

	// register the new shortcodes
	function __construct() {

		// do_shortcode on the_excerpt could causes several unwanted output. Uncomment it on your own risk
		// add_filter('the_excerpt', array(&$this, 'convert_shortcode'));
		// add_filter('the_excerpt', 'do_shortcode', 11);

		add_shortcode( 'flagallery', array( &$this, 'show_flashalbum' ) );
		add_shortcode( 'grandmp3', array( &$this, 'grandmp3' ) );
		add_shortcode( 'grandmusic', array( &$this, 'grandmusic' ) );
		add_shortcode( 'grandflv', array( &$this, 'grandflv' ) );
		add_shortcode( 'grandvideo', array( &$this, 'grandvideo' ) );
		add_shortcode( 'grandbanner', array( &$this, 'grandbanner' ) );
		add_action( 'flag_footer_scripts', 'wp_footer' );

	}

	function show_flashalbum( $atts ) {
		global $wpdb, $flagdb, $flag;

		extract( shortcode_atts( array(
			'gid' => '',
			'album' => '',
			'name' => '',
			'w' => '',
			'orderby' => '',
			'order' => '',
			'exclude' => '',
			'skin' => '',
			'preset' => '',
			'playlist' => '',
			'fullwindow' => false,
			'align' => '',
		), $atts ) );

		$out = '';
		// make an array out of the ids
		$draft_clause = ( get_option( 'flag_db_version' ) < 2.75 ) ? '' : 'AND status=0';
		if ( $album ) {
			$gallerylist = $flagdb->get_album( $album );
			$ids = explode( ',', $gallerylist );
			$galleryIDs = array();
			foreach ( $ids as $id ) {
				$galleryIDs[] = $wpdb->get_var( $wpdb->prepare( "SELECT gid FROM {$wpdb->flaggallery} WHERE gid = %d $draft_clause", $id ) );
			}
			$galleryIDs = array_filter( $galleryIDs );
			if ( empty( $galleryIDs ) ) {
				return $out = sprintf( __( '[Gallery %s not found]', 'flash-album-gallery' ), $gallerylist );
			}
			if ( ! $flag->options['license_name'] ) {
				$galleryIDs = array_slice( $galleryIDs, 0, 3 );
			}
			$gids = implode( '_', $galleryIDs );
			$out = flagShowFlashAlbum( $gids, $name, $w, $skin, $preset, $preset, false, $fullwindow, $align );

		} elseif ( $gid == "all" ) {
			$flag_options = get_option( 'flag_options' );
			if ( empty( $orderby ) ) {
				$orderby = $flag_options['albSort'];
			}
			if ( empty( $order ) ) {
				$order = $flag_options['albSortDir'];
			}

			if ( ! in_array( $orderby, array( 'title', 'rand' ) ) ) {
				$orderby = 'gid';
			}
			if ( ! $order ) {
				$order = 'DESC';
			}
			$gallerylist = $flagdb->find_all_galleries( $orderby, $order );
			if ( is_array( $gallerylist ) ) {
				$excludelist = explode( ',', $exclude );
				$gids        = [];
				$gids_       = '';
				foreach ( $gallerylist as $gallery ) {
					if ( in_array( $gallery->gid, $excludelist ) ) {
						continue;
					}
					$gids[] = $gallery->gid;
				}

				if ( ! $flag->options['license_name'] ) {
					$gids = array_slice( $gids, 0, 3 );
				}

				$gids_ = implode( '_', $gids );
				$out   = flagShowFlashAlbum( $gids_, $name, $w, $skin, $preset, false, $fullwindow, $align );
			} else {
				$out = __( '[Galleries not found]', 'flash-album-gallery' );
			}
		} else {
			$ids = explode( ',', $gid );

			$galleryIDs = array();
			foreach ( $ids as $id ) {
				$id = intval( $id );
				$galleryIDs[] = $wpdb->get_var( $wpdb->prepare( "SELECT gid FROM {$wpdb->flaggallery} WHERE gid = %d  $draft_clause", $id ) );
			}
			$galleryIDs = array_filter( $galleryIDs );
			if ( empty( $galleryIDs ) ) {
				$out = sprintf( __( '[Galleries %s not found]', 'flash-album-gallery' ), $gid );

				return $out;
			}

			if ( ! $flag->options['license_name'] ) {
				$galleryIDs = array_slice( $galleryIDs, 0, 3 );
			}
			$gids = implode( '_', $galleryIDs );
			$out = flagShowFlashAlbum( $gids, $name, $w, $skin, $preset, false, $fullwindow, $align );
		}

		if ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) {
			do_action( 'flag_footer_scripts' );
		}

		return $out;
	}

	function grandmusic( $atts ) {

		extract( shortcode_atts( array(
			'playlist' => '',
			'w' => ''
		), $atts ) );
		$out = sprintf( __( '[Playlist %s not found]', 'flash-album-gallery' ), $playlist );
		if ( $playlist ) {
			$flag_options = get_option( 'flag_options' );
			if ( ! file_exists( ABSPATH . $flag_options['galleryPath'] . 'playlists/' . $playlist . '.xml' ) ) {
				return $out;
			}
			$out = flagShowMPlayer( $playlist, $w );
		}

		return $out;
	}

	function grandmp3( $atts ) {
		extract( shortcode_atts( array(
			'id' => '',
			'autoplay' => 'false',
		), $atts ) );
		$out = '';
		if ( $id ) {
			$url = wp_get_attachment_url( $id );
			if ( $autoplay && $autoplay !== 'false' ) {
				$autoplay = 'autoplay';
			}
			$out .= '<div id="c-' . $id . '" class="grandmp3"><audio src="' . $url . '" controls ' . $autoplay . ' preload="none" autobuffer="false"></audio></div>';
		}

		return $out;
	}

	function grandvideo( $atts ) {

		extract( shortcode_atts( array(
			'playlist' => '',
			'w' => '',
		), $atts ) );
		$out = sprintf( __( '[Playlist %s not found]', 'flash-album-gallery' ), $playlist );
		if ( $playlist ) {
			$flag_options = get_option( 'flag_options' );
			if ( ! file_exists( ABSPATH . $flag_options['galleryPath'] . 'playlists/video/' . $playlist . '.xml' ) ) {
				return $out;
			}
			$out = flagShowVPlayer( $playlist, $w );

		}

		return $out;
	}

	function grandflv( $atts ) {
		global $wpdb;
		extract( shortcode_atts( array(
			'id' => '',
			'w' => '',
			'h' => '',
			'autoplay' => '',
		), $atts ) );
		$out = '';
		if ( $id ) {
			$out = flagShowVmPlayer( $id, $w, $h, $autoplay );
		}

		return $out;
	}

	function grandbanner( $atts ) {

		extract( shortcode_atts( array(
			'xml' => '',
			'w' => '',
		), $atts ) );
		$out = sprintf( __( '[XML %s not found]', 'flash-album-gallery' ), $xml );
		if ( $xml ) {
			$flag_options = get_option( 'flag_options' );
			if ( ! file_exists( ABSPATH . $flag_options['galleryPath'] . 'playlists/banner/' . $xml . '.xml' ) ) {
				return $out;
			}
			$out = flagShowBanner( $xml, $w );
		}

		return $out;
	}

}

// let's use it
$flagShortcodes = new FlAG_Shortcodes;
