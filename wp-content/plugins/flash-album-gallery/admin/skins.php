<?php
if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// look up for the path
require_once( dirname( dirname( __FILE__ ) ) . '/flag-config.php' );

// check for correct capability
if ( ! is_user_logged_in() ) {
	die( '-1' );
}

// check for correct FlAG capability
if ( ! current_user_can( 'FlAG Change skin' ) ) {
	die( '-1' );
}

/**
 * @var flagLoad $flag
 */
global $flag;
$flag_options = get_option( 'flag_options' );

require_once( dirname( __FILE__ ) . '/get_skin.php' );

if ( isset( $_POST['installskin'] ) ) {
	check_admin_referer( 'skin-upload' );
	require_once( dirname( __FILE__ ) . '/skin_install.php' );
}
if ( isset( $_POST['skinzipurl'] ) ) {
	check_admin_referer( 'skin_install' );
	$url       = 'https://mypgc.co/skins/' . sanitize_flagname( basename( $_POST['skinzipurl'] ) );
	$skins_dir = $flag_options['skinsDirABS'];
	$mzip      = download_url( $url );
	if ( is_wp_error( $mzip ) ) {
		$userAgent = 'Googlebot/2.1 (https://www.googlebot.com/bot.html)';
		$filename  = basename( $url );
		$mzip      = rtrim( $skins_dir, '/' ) . '/' . $filename;
		$ch        = curl_init();
		$fp        = fopen( "$mzip", "w" );
		curl_setopt( $ch, CURLOPT_USERAGENT, $userAgent );
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_FAILONERROR, true );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		@curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_BINARYTRANSFER, true );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 20 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $ch, CURLOPT_FILE, $fp );
		$data = curl_exec( $ch );
		curl_close( $ch );
		fclose( $fp );
	}

	$mzip = str_replace( "\\", "/", $mzip );

	if ( class_exists( 'ZipArchive' ) ) {
		$zip = new ZipArchive;
		$zip->open( $mzip );
		$zip->extractTo( $skins_dir );
		$zip->close();
	} else {
		require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
		$archive = new PclZip( $mzip );
		$list    = $archive->extract( $skins_dir );
		if ( $list == 0 ) {
			die( "ERROR : '" . $archive->errorInfo( true ) . "'" );
		}

	}
	if ( @unlink( $mzip ) ) {
		flagGallery::show_message( __( 'The skin installed successfully.', 'flash-album-gallery' ) );
	}
}
add_action( 'install_skins_upload', 'upload_skin' );
function upload_skin() {

	echo '<div id="uploadaction">';
	echo '<h3>' . __( 'Install info', 'flash-album-gallery' ) . '</h3>';

	if ( ! ( ( $uploads = wp_upload_dir() ) && false === $uploads['error'] ) ) {
		echo "<p>" . $uploads['error'] . "</p>\n";
	} else {
		$filename = false;
		if ( ! empty( $_FILES ) ) {
			$filename = $_FILES['skinzip']['name'];
		} elseif ( isset( $_GET['package'] ) ) {
			$filename = urlencode( $_GET['package'] );
		}
		if ( ! $filename ) {
			echo "<p>" . __( 'No skin Specified', 'flash-album-gallery' ) . "</p>\n";
		} else {
			check_admin_referer( 'skin-upload' );
			echo '<h4>', sprintf( __( 'Installing Skin from file: %s', 'flash-album-gallery' ), basename( $filename ) ), '</h4>';

			//Handle a newly uploaded file, Else assume it was
			if ( ! empty( $_FILES ) ) {
				$flag_options = get_option( 'flag_options' );
				$skins_dir    = $flag_options['skinsDirABS'];
				if ( ! wp_mkdir_p( $skins_dir ) ) {
					echo "<p>" . sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?', 'flash-album-gallery' ), $skins_dir ) . "</p>\n";
					echo '</div>';

					return;
				}
				if ( ! is_writable( $skins_dir ) ) {
					@chmod( $skins_dir, 0755 );
					if ( ! is_writable( $skins_dir ) ) {
						//@unlink( $_FILES['modulezip']['tmp_name'] );
						echo "<p>" . sprintf( __( 'Directory %s is not writable by the server.', 'flash-album-gallery' ), $skins_dir ) . "</p>\n";
						echo '</div>';

						return;
					}
				}
				$filename   = wp_unique_filename( $uploads['basedir'], $filename );
				$local_file = $uploads['basedir'] . '/' . $filename;

				// Move the file to the uploads dir
				if ( false === @move_uploaded_file( $_FILES['skinzip']['tmp_name'], $local_file ) ) {
					echo "<p>" . sprintf( __( 'The uploaded file could not be moved to %s.', 'flash-album-gallery' ), $uploads['path'] ) . "</p>\n";
					echo '</div>';

					return;
				}
			} else {
				$local_file = $uploads['basedir'] . '/' . $filename;
			}
			$installed_skin = do_skin_install_local_package( $local_file, $filename );
		}
	}
	echo '</div>';
}

if ( ! empty( $_POST['new_preset'] ) ) {
	check_admin_referer( 'new_preset_nonce' );

	$preset_name      = sanitize_text_field( $_POST['new_preset'] );
	$preset_skin      = sanitize_text_field( $_POST['preset_skin'] );
	$skin_options_key = "{$preset_skin}_options";

	$skin_preset = empty( $_POST['flagallery_preset'] ) ? '' : sanitize_text_field( trim( $_POST['flagallery_preset'] ) );
	if ( ! isset( $flag_options[ $skin_options_key ]['presets'][ $preset_name ] ) ) {
		$flag_options[ $skin_options_key ]['presets'][ $preset_name ] = array();

		// Save preset.
		update_option( 'flag_options', $flag_options );
		flagGallery::show_message( __( 'Created new preset', 'flash-album-gallery' ) );
	}
}

if ( isset( $_POST['license_key'] ) ) {
	check_admin_referer( 'skin-api' );
	$license_key                  = esc_sql( $_POST['license_key'] );
	$license_name                 = esc_sql( $_POST['license_name'] );
	$flag_options['license_key']  = trim( $license_key );
	$flag_options['license_name'] = trim( $license_name );
	if ( empty( $_POST['license_key'] ) ) {
		$flag_options['license_name'] = '';
	}
	update_option( 'flag_options', $flag_options );
	flagGallery::show_message( __( 'License Key Updated', 'flash-album-gallery' ) );
}

if ( ! empty( $flag_options['license_key'] ) ) {
	if ( function_exists( 'curl_init' ) ) {
		$ch = curl_init( 'https://mypgc.co/app/account_st.php' );
		curl_setopt( $ch, CURLOPT_REFERER, home_url() );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, array( 'check_status' => $flag_options['license_key'] ) );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 3 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		$status = curl_exec( $ch );
		curl_close( $ch );
		if ( $status === '0' ) {
			$flag_options['license_key'] = '';
			if ( empty( $_POST['license_name'] ) ) {
				$flag_options['license_name'] = '';
			}
			update_option( 'flag_options', $flag_options );
			flagGallery::show_message( __( 'Your license key was deactivated', 'flash-album-gallery' ) );
		} elseif ( $status === '' ) {
			$flag_options['license_key'] = '';
			if ( empty( $_POST['license_name'] ) ) {
				$flag_options['license_name'] = '';
			}
			flagGallery::show_message( __( 'Bad Licence Key', 'flash-album-gallery' ) );
		} elseif ( ! empty( $status ) ) {
			$flag_options['license_name'] = $status;
		}
		update_option( 'flag_options', $flag_options );
	} else {
		flagGallery::show_message( __( 'cURL library is not installed on your server.', 'flash-album-gallery' ) );
	}
}

if ( isset( $_POST['updateoption'] ) ) {
	check_admin_referer( 'flag_settings' );
	// get the hidden option fields, taken from WP core
	$options = false;
	if ( $_POST['page_options'] ) {
		$options = explode( ',', stripslashes( $_POST['page_options'] ) );
	}
	if ( $options ) {
		foreach ( $options as $option ) {
			$option                   = trim( $option );
			$value                    = trim( $_POST[ $option ] );
			$flag->options[ $option ] = $value;
		}
		// the path should always end with a slash
		$flag->options['galleryPath'] = trailingslashit( $flag->options['galleryPath'] );
	}
	// Save options
	update_option( 'flag_options', $flag->options );
	flagGallery::show_message( __( 'Update Successfully', 'flash-album-gallery' ) );
}


if ( isset( $_GET['delete'] ) ) {
	check_admin_referer( 'delete_skin' );
	$delskin = sanitize_flagname( $_GET['delete'] );
	if ( current_user_can( 'FlAG Delete skins' ) && false === strpos( $delskin, '..' ) ) {
		if ( $flag_options['flashSkin'] != $delskin ) {
			$skins_dir = trailingslashit( $flag_options['skinsDirABS'] );
			$skin      = $skins_dir . $delskin . '/';
			if ( basename( $skin ) != 'flagallery-skins' ) {
				if ( is_dir( $skin ) ) {
					if ( flagGallery::flagFolderDelete( $skin ) ) {
						flagGallery::show_message( __( 'Skin', 'flash-album-gallery' ) . ' \'' . $delskin . '\' ' . __( 'deleted successfully', 'flash-album-gallery' ) );
					} else {
						flagGallery::show_message( __( 'Can\'t find skin directory ', 'flash-album-gallery' ) . ' \'' . $delskin . '\' ' . __( '. Try delete it manualy via ftp', 'flash-album-gallery' ) );
					}
				}
			} else {
				flagGallery::show_message( __( 'Can\'t find skin directory ', 'flash-album-gallery' ) . ' \'' . $delskin . '\' ' . __( '. Try delete it manualy via ftp', 'flash-album-gallery' ) );
			}
		} else {
			flagGallery::show_message( __( 'You need activate another skin before delete it', 'flash-album-gallery' ) );
		}
	} else {
		wp_die( __( 'You do not have sufficient permissions to delete skins of Grand Flagallery.' ) );
	}
}

if ( isset( $_GET['delete_preset'] ) ) {
	check_admin_referer( 'delete_preset' );
	$delpreset = $_GET['delete_preset'];
	$skin      = sanitize_flagname( $_GET['preset_skin'] );
	if ( current_user_can( 'FlAG Delete skins' ) && false === strpos( $skin, '..' ) ) {
		if ( isset( $flag_options["{$skin}_options"] ) ) {
			if ( isset( $flag_options["{$skin}_options"]['presets'][ $delpreset ] ) ) {
				unset( $flag_options["{$skin}_options"]['presets'][ $delpreset ] );
				update_option( 'flag_options', $flag_options );
				flagGallery::show_message( __( 'Preset was deleted successfully', 'flash-album-gallery' ) );
			} else {
				flagGallery::show_message( __( 'Can\'t find preset', 'flash-album-gallery' ) );
			}
		}
	} else {
		wp_die( __( 'You do not have sufficient permissions to delete skins of Grand Flagallery.' ) );
	}
}

if ( isset( $_GET['skin'] ) ) {
	check_admin_referer( 'set_default_skin' );
	$set_skin = sanitize_flagname( $_GET['skin'] );
	if ( $flag_options['flashSkin'] != $set_skin ) {
		$aValid = array( '-', '_' );
		if ( ! ctype_alnum( str_replace( $aValid, '', $set_skin ) ) ) {
			die( 'try again' );
		}
		$active_skin = $flag_options['skinsDirABS'] . $set_skin . '/' . $set_skin . '.php';
		if ( ! file_exists( $active_skin ) ) {
			die( 'try again' );
		}
		$flag_options['flashSkin'] = $set_skin;
		include( $active_skin );
		update_option( 'flag_options', $flag_options );
		flagGallery::show_message( __( 'Skin', 'flash-album-gallery' ) . ' \'' . $set_skin . '\' ' . __( 'activated successfully. Optionally it can be overwritten with shortcode parameter.', 'flash-album-gallery' ) );
	}
}
$type = isset( $_GET['type'] ) ? sanitize_key( $_GET['type'] ) : '';
switch ( $type ) {
	case '':
		$stype     = 'gallery';
		$new_skins = __( 'New Photo Skins', 'flash-album-gallery' );
		break;
	case 'm':
		$stype     = 'music';
		$new_skins = __( 'New Music Skins', 'flash-album-gallery' );
		break;
	case 'v':
		$stype     = 'video';
		$new_skins = __( 'New Video Skins', 'flash-album-gallery' );
		break;
	case 'b':
		$stype     = 'banner';
		$new_skins = __( 'New Banner Skins', 'flash-album-gallery' );
		break;
	default:
		$stype     = 'gallery';
		$new_skins = __( 'New Photo Skins', 'flash-album-gallery' );
		break;
}

if ( isset( $_GET['skins_refresh'] ) ) {
	check_admin_referer( 'skins_refresh' );
	// upgrade plugin
	require_once( FLAG_ABSPATH . 'admin/tuning.php' );
	$ok = flag_tune();
	if ( $ok ) {
		flagGallery::show_message( __( 'Skins refreshed successfully', 'flash-album-gallery' ) );
	}
}
if ( ! ( is_plugin_active( 'woowgallery/woowgallery.php' ) || ! empty( $flag->options['hide_woow'] ) ) ) {
	?>
	<div class="promote-woowbox" style="padding-top:10px"><a href="https://bit.ly/flag-woowgallery" target="_blank"><img src="<?php echo plugins_url( '/flash-album-gallery/admin/images/woowbox-promote.png' ) ?>" alt="Try WoowGallery plugin"/></a></div>
	<?php
}

wp_clear_scheduled_hook('flaskins_update');
wp_schedule_event(time(), 'daily', 'flaskins_update');
$flag->skins_update();

?>
<div id="slider" class="flag-wrap">
	<?php if ( current_user_can( 'FlAG Add skins' ) ) { ?>
		<ul id="tabs" class="tabs">
			<li class="selected"><a href="#" rel="addskin"><?php _e( 'Add new skin', 'flash-album-gallery' ); ?></a></li>
		</ul>
	<?php } ?>

	<?php if ( current_user_can( 'FlAG Add skins' ) ) { ?>
		<div id="addskin" class="cptab" style="display:block;">
			<div>&nbsp;</div>
			<h4 style="margin-top:0;"><?php _e( 'Install a skin in .zip format', 'flash-album-gallery' ); ?></h4>
			<p><?php _e( 'If you have a skin in a .zip format, You may install it by uploading it here.', 'flash-album-gallery' ); ?></p>
			<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=flag-skins' ); ?>">
				<?php wp_nonce_field( 'skin-upload' ); ?>
				<p><input type="file" name="skinzip"/>
					<input type="submit" class="button" name="installskin" value="<?php _e( 'Install Now', 'flash-album-gallery' ); ?>"/>
				</p>
			</form>
			<?php if ( isset( $_POST['installskin'] ) ) {
				do_action( 'install_skins_upload' );
			} ?>
		</div>
	<?php } ?>

	<!--<script type="text/javascript">-->
	<!--    /* <![CDATA[ */-->
	<!--    //noinspection JSPotentiallyInvalidConstructorUsage-->
	<!--    var cptabs = new ddtabcontent("tabs");-->
	<!--    cptabs.setpersist(true);-->
	<!--    cptabs.setselectedClassTarget("linkparent");-->
	<!--    cptabs.init();-->
	<!--    /* ]]> */-->
	<!--</script>-->
</div>

<?php if ( current_user_can( 'FlAG Add skins' ) ) { ?>
	<div id="skinapikey">
		<h2><?php _e( 'FlaGallery License Key', 'flash-album-gallery' ); ?></h2>
		<p><?php _e( 'If you have license key then paste it here.', 'flash-album-gallery' ); ?></p>
		<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=flag-skins' ); ?>">
			<?php wp_nonce_field( 'skin-api' ); ?>
			<p>
				<input type="text" name="license_key" value="<?php echo $flag_options['license_key'] ?>" size="55"/>
				<input style="display:none" type="text" name="license_name" value="<?php echo $flag_options['license_name'] ?>"/>
				<input type="submit" class="button" value="<?php _e( 'Save', 'flash-album-gallery' ); ?>"/>
			</p>
			<?php if ( $flag_options['license_name'] ) { ?>
				<p style="font-weight: bold;"><?php printf( __( 'You have <span style="color:red">%s</span> license' ), $flag_options['license_name'] ); ?></p>
			<?php }
			if ( ! $flag_options['license_name'] ) {
				?>
				<p>
					<a href="http://bit.ly/2jPNRB0" class="button button-primary button-red" target="_blank"><?php _e( 'Get Premium', 'flash-album-gallery' ) ?></a>
				</p>
				<?php
			} else {
				if ( 'MINIPack' === $flag_options['license_name'] ) {
					?>
					<p>
						<a href="http://bit.ly/2jPNRB0" class="button button-primary button-red" target="_blank"><?php _e( 'Upgrade License to GRANDPack or GRANDPack+', 'flash-album-gallery' ) ?></a>
					</p>
					<?php
				} elseif ( 'GRANDPack' === $flag_options['license_name'] ) {
					?>
					<p>
						<a href="http://bit.ly/2jPNRB0" class="button button-primary button-red" target="_blank"><?php _e( 'Upgrade License to GRANDPack+', 'flash-album-gallery' ) ?></a>
					</p>
					<?php
				}
			}
			?>
		</form>
	</div>
<?php } ?>

<div class="flag-wrap" style="min-width: 878px;">
	<style>
		#TB_window {
			top: 50% !important;
			margin-top: -45vh !important;
			transform: none !important;
		}
		#TB_window iframe {
			height: 87vh !important;
		}
	</style>
	<h2><?php _e( 'Skins', 'flash-album-gallery' ); ?>:</h2>
	<p style="float: right; display:none;">
		<a class="button" href="<?php echo wp_nonce_url( 'admin.php?page=flag-skins&amp;skins_refresh=1', 'skins_refresh' ); ?>"><?php _e( 'Refresh / Update Skins', 'flash-album-gallery' ); ?></a>
	</p>
	<p><a class="button<?php if ( ! $type ) {
			echo '-primary';
		} ?>" href="<?php echo admin_url( 'admin.php?page=flag-skins' ); ?>"><span style="font-size: 14px;"><?php _e( 'Photo skins', 'flash-album-gallery' ); ?></span></a>&nbsp;&nbsp;&nbsp;
		<a class="button<?php if ( $type == 'm' ) {
			echo '-primary';
		} ?>" href="<?php echo admin_url( 'admin.php?page=flag-skins&amp;type=m' ); ?>"><span style="font-size: 14px;"><?php _e( 'Music skins', 'flash-album-gallery' ); ?></span></a>&nbsp;&nbsp;&nbsp;
		<a class="button<?php if ( $type == 'v' ) {
			echo '-primary';
		} ?>" href="<?php echo admin_url( 'admin.php?page=flag-skins&amp;type=v' ); ?>"><span style="font-size: 14px;"><?php _e( 'Video skins', 'flash-album-gallery' ); ?></span></a>&nbsp;&nbsp;&nbsp;
		<a class="button<?php if ( $type == 'b' ) {
			echo '-primary';
		} ?>" href="<?php echo admin_url( 'admin.php?page=flag-skins&amp;type=b' ); ?>"><span style="font-size: 14px;"><?php _e( 'Banner skins', 'flash-album-gallery' ); ?></span></a>&nbsp;&nbsp;&nbsp;
	</p>

	<?php
	$all_skins       = get_skins( false, $type );
	$total_all_skins = count( $all_skins );

	function flag_curl_exec_follow( $ch, &$maxredirect = null ) {

		// we emulate a browser here since some websites detect
		// us as a bot and don't let us do our job
		$user_agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5)" .
		              " Gecko/20041107 Firefox/1.0";
		curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );

		$mr = $maxredirect === null ? 5 : intval( $maxredirect );

		if ( filter_var( ini_get( 'open_basedir' ), FILTER_VALIDATE_BOOLEAN ) === false && FLAG_SAFE_MODE === false ) {

			@curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, $mr > 0 );
			curl_setopt( $ch, CURLOPT_MAXREDIRS, $mr );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

		} else {

			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, false );

			if ( $mr > 0 ) {
				$original_url = curl_getinfo( $ch, CURLINFO_EFFECTIVE_URL );
				$newurl       = $original_url;

				$rch = curl_copy_handle( $ch );

				curl_setopt( $rch, CURLOPT_HEADER, true );
				curl_setopt( $rch, CURLOPT_NOBODY, true );
				curl_setopt( $rch, CURLOPT_FORBID_REUSE, false );
				do {
					curl_setopt( $rch, CURLOPT_URL, $newurl );
					$header = curl_exec( $rch );
					if ( curl_errno( $rch ) ) {
						$code = 0;
					} else {
						$code = curl_getinfo( $rch, CURLINFO_HTTP_CODE );
						if ( $code == 301 || $code == 302 ) {
							preg_match( '/Location:(.*?)\n/i', $header, $matches );
							$newurl = trim( array_pop( $matches ) );

							// if no scheme is present then the new url is a
							// relative path and thus needs some extra care
							if ( ! preg_match( "/^https?:/i", $newurl ) ) {
								$newurl = $original_url . $newurl;
							}
						} else {
							$code = 0;
						}
					}
				} while ( $code && -- $mr );

				curl_close( $rch );

				if ( ! $mr ) {
					if ( $maxredirect === null ) {
						trigger_error( 'Too many redirects.', E_USER_WARNING );
					} else {
						$maxredirect = 0;
					}

					return false;
				}
				curl_setopt( $ch, CURLOPT_URL, $newurl );
			}
		}

		return curl_exec( $ch );
	}

	$skins_remote_xml = 'http://mypgc.co/depository/skins2.xml';
	// not installed skins
	$skins_xml       = @simplexml_load_file( $skins_remote_xml, 'SimpleXMLElement', LIBXML_NOCDATA );
	$all_skins_arr   = $skins_by_type = $skins_to_update = array();
	$skins_xml_error = false;
	if ( empty( $skins_xml ) && function_exists( 'curl_init' ) ) {
		$ch = curl_init( $skins_remote_xml );
		/*curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		*/
		$skins_xml = @simplexml_load_string( flag_curl_exec_follow( $ch ) );
		curl_close( $ch );
	}

	if ( ! empty( $skins_xml ) ) {
		foreach ( $skins_xml as $skin ) {
			$suid                                = (string) $skin->uid;
			$skintype                            = (string) $skin->type;
			$all_skins_arr[ $suid ]              = get_object_vars( $skin );
			$skins_by_type[ $skintype ][ $suid ] = $all_skins_arr[ $suid ];
		}
	} else {
		//$skins_xml_error = __('URL file-access is disabled in the server configuration.', 'flash-album-gallery');
		$skins_xml_error = __( 'cURL library is not installed on your server.', 'flash-album-gallery' ) . '<br>' . __( 'Download skins from http://mypgc.co/', 'flash-album-gallery' );
	}


	?>

	<div style="width:70%; overflow: hidden; float: left;">
		<table class="widefat flag-table" cellspacing="0" id="skins-table">
			<thead>
			<tr>
				<th scope="col" class="manage-column"><?php _e( 'Skin', 'flash-album-gallery' ); ?></th>
				<th scope="col" class="manage-column"><?php _e( 'Description', 'flash-album-gallery' ); ?></th>
				<th scope="col" class="action-links"><?php _e( 'Action', 'flash-album-gallery' ); ?></th>
			</tr>
			</thead>

			<tfoot>
			<tr>
				<th scope="col" class="manage-column"><?php _e( 'Skin', 'flash-album-gallery' ); ?></th>
				<th scope="col" class="manage-column"><?php _e( 'Description', 'flash-album-gallery' ); ?></th>
				<th scope="col" class="action-links"><?php _e( 'Action', 'flash-album-gallery' ); ?></th>
			</tr>
			</tfoot>

			<tbody class="skins">
			<?php

			if ( empty( $all_skins ) ) {
				echo '<tr>
			<td colspan="3">' . __( 'No skins to show' ) . '</td>
		</tr>';
			}
			foreach ( (array) $all_skins as $skin_file => $skin_data ) {
				$class      = ( dirname( $skin_file ) == $flag_options['flashSkin'] ) ? 'active' : 'inactive';
				$is_premium = isset( $skin_data['Status'] );
				$class      .= $is_premium ? ' skin-status-' . $skin_data['Name'] : '';
				if ( ! empty( $skin_data['uid'] ) ) {
					$suid = (string) $skin_data['uid'];
					if ( isset( $all_skins_arr[ $suid ] ) && (string) $all_skins_arr[ $suid ]['uid'] == $suid ) {
						if ( version_compare( (float) $all_skins_arr[ $suid ]['version'], (float) $skin_data['Version'], '<=' ) ) {
							unset( $skins_by_type[ $stype ][ $suid ] );
						} else {
							$skins_to_update[] = $suid;
						}
					}
				} ?>
				<tr id="<?php echo basename( $skin_file, '.php' ); ?>" class="<?php echo $class; ?> first">
					<td class="skin-title"><strong><?php echo $skin_data['Name']; ?></strong>
						- <?php echo ( ! $skin_data['Status'] || 'free' === $skin_data['Status'] ) ? 'Free' : 'Premium'; ?>
					</td>
					<td class="desc">
						<?php
						$skin_meta = array();
						if ( ! empty( $skin_data['Version'] ) ) {
							$skin_meta[] = sprintf( __( 'Version %s', 'flash-album-gallery' ), $skin_data['Version'] );
						}
						if ( ! empty( $skin_data['Author'] ) ) {
							$author = $skin_data['Author'];
							if ( ! empty( $skin_data['AuthorURI'] ) ) {
								$author = '<a href="' . $skin_data['AuthorURI'] . '" title="' . __( 'Visit author homepage', 'flash-album-gallery' ) . '">' . $skin_data['Author'] . '</a>';
							}
							$skin_meta[] = sprintf( __( 'By %s', 'flash-album-gallery' ), $author );
						}
						if ( ! empty( $skin_data['SkinURI'] ) ) {
							$skin_meta[] = '<a href="' . $skin_data['SkinURI'] . '" title="' . __( 'Visit skin site', 'flash-album-gallery' ) . '">' . __( 'Visit skin site', 'flash-album-gallery' ) . '</a>';
						}
						?>
						<?php echo implode( ' | ', $skin_meta ); ?>
					</td>
					<td class="skin-activate action-links" style="white-space: nowrap;">
						<?php
						if ( isset( $_GET['type'] ) && ! empty( $_GET['type'] ) ) {
						} else {
							if ( dirname( $skin_file ) != $flag_options['flashSkin'] ) { ?>
								<strong><a href="<?php echo wp_nonce_url( 'admin.php?page=flag-skins&skin=' . dirname( $skin_file ), 'set_default_skin' ); ?>" title="<?php _e( 'Activate this skin', 'flash-album-gallery' ); ?>"><?php _e( 'Activate', 'flash-album-gallery' ); ?></a></strong>
							<?php } else { ?>
								<strong><?php _e( 'Activated by default', 'flash-album-gallery' ); ?></strong>
								<?php
							}
						} ?>
					</td>

				</tr>
				<tr class="<?php echo $class; ?> second">
					<td class="skin-title">
						<img src="<?php echo WP_PLUGIN_URL . "/flagallery-skins/" . dirname( $skin_file ); ?>/screenshot.png" alt="<?php echo $skin_data['Name']; ?>" title="<?php echo $skin_data['Name']; ?>"/>
					</td>
					<td class="desc">
						<p><?php echo $skin_data['Description']; ?></p>
						<?php
						if ( ! $flag_options['license_name'] ) {
							if ( $skin_data['Status'] && 'free' !== $skin_data['Status'] ) {
								?>
								<p>
									<a href="http://bit.ly/2jPNRB0" class="button button-primary button-red" target="_blank"><?php _e( 'Get Premium', 'flash-album-gallery' ) ?></a>
								</p>
								<?php
							}
						} else {
							if ( 'GRANDPackPlus' === $skin_data['Status'] && 'GRANDPackPlus' !== $flag_options['license_name'] ) {
								?>
								<p>
									<a href="http://bit.ly/2jPNRB0" class="button button-primary button-red" target="_blank"><?php _e( 'Upgrade License to GRANDPack+', 'flash-album-gallery' ) ?></a>
								</p>
								<?php
							} elseif ( 'GRANDPack' === $skin_data['Status'] && ! in_array( $flag_options['license_name'], array(
										'GRANDPackPlus',
										'GRANDPack',
									)
								) ) {
								?>
								<p>
									<a href="http://bit.ly/2jPNRB0" class="button button-primary button-red" target="_blank"><?php _e( 'Upgrade License to GRANDPack or GRANDPack+', 'flash-album-gallery' ) ?></a>
								</p>
								<?php
							}
						}
						?>
					</td>
					<td class="skin-delete action-links">
						<a class="thickbox" title="<?php esc_attr_e( 'Skin Options', 'flash-album-gallery' ); ?>" href="<?php echo FLAG_URLPATH . 'admin/skin_options.php?show_options=1&amp;skin=' . dirname( $skin_file ) . '&amp;TB_iframe=1&amp;width=753&amp;height=700'; ?>"><?php _e( 'Options - Default Preset', 'flash-album-gallery' ); ?></a>
						<br/><strong><?php _e( 'Presets:', 'flash-album-gallery' ); ?></strong>
						<form method="post" class="addnewpreset" action="<?php echo admin_url( 'admin.php?page=flag-skins' ); ?>">
							<?php wp_nonce_field( 'new_preset_nonce' ); ?>
							<input type="hidden" name="preset_skin" value="<?php echo esc_attr( dirname( $skin_file ) ); ?>"/>
							<input type="text" name="new_preset" value="" placeholder="<?php _e( 'add new preset', 'flash-album-gallery' ); ?>" required/>
							<button type="submit" class="button-primary"><span class="dashicons dashicons-plus"></span></button>
						</form>
						<?php
						$act_skin         = dirname( $skin_file );
						$skin_options_key = "{$act_skin}_options";
						if ( ! empty( $flag_options[ $skin_options_key ]['presets'] ) ) {
							foreach ( $flag_options[ $skin_options_key ]['presets'] as $preset_name => $preset_settings ) {
								?>
								<br><a class="thickbox" title="<?php esc_attr_e( 'Skin Options', 'flash-album-gallery' ); ?>" href="<?php echo esc_url( add_query_arg( array(
									'show_options' => 1,
									'skin'         => $act_skin,
									'preset'       => $preset_name,
									'TB_iframe'    => 1,
									'width'        => 753,
									'height'       => 700,
								), FLAG_URLPATH . 'admin/skin_options.php' ) ); ?>"><?php echo esc_html( $preset_name ); ?></a>
								<a class="delete" onclick="javascript:return flag_delskin('<?php echo esc_html( $preset_name ); ?>');" href="<?php echo wp_nonce_url( 'admin.php?page=flag-skins&preset_skin=' . dirname( $skin_file ) . '&delete_preset=' . $preset_name, 'delete_preset' ); ?>" title="<?php _e( 'Delete this preset', 'flash-album-gallery' ); ?>"><span class="dashicons dashicons-trash"></span></a>
								<?php
							}
						}

						if ( current_user_can( 'FlAG Delete skins' ) ) {
							if ( dirname( $skin_file ) != $flag_options['flashSkin'] ) { ?>
								<br/><br/>
								<a class="delete" onclick="javascript:return flag_delskin('<?php echo $skin_data['Name']; ?>');" href="<?php echo wp_nonce_url( 'admin.php?page=flag-skins&delete=' . dirname( $skin_file ), 'delete_skin' ); ?>" title="<?php _e( 'Delete this skin', 'flash-album-gallery' ); ?>"><?php _e( 'Delete skin', 'flash-album-gallery' ); ?></a>
							<?php }
						} ?>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<script type="text/javascript">
          function flag_delskin(skin_name) {
            return confirm('<?php echo __( 'Delete', 'flash-album-gallery' ); ?> "' + skin_name + '"');
          }
		</script>
	</div>

	<div class="postbox metabox-holder" id="newskins" style="width: 29%; float: right; padding-top: 5px; text-align:center">
		<h3 style="font-size: 16px; line-height: 100%; font-weight: bold; color: #2583AD;"><?php echo $new_skins; ?></h3>
		<div class="inside">
			<?php
			if ( isset( $skins_by_type[ $stype ] ) && ! empty( $skins_by_type[ $stype ] ) ) {
				foreach ( $skins_by_type[ $stype ] as $skin ) { ?>
					<div class="skin <?php echo $skin['type'] . ' ' . $skin['status']; ?>" id="uid-<?php echo $skin['uid']; ?>" style="padding: 10px;">
						<div style="text-align: center;">
							<p style="font-size: 120%;"><strong><?php echo $skin['title']; ?></strong>
								<small class="version"><?php echo 'v' . $skin['version']; ?></small>
								- <?php echo ( ! $skin['status'] || 'free' === $skin['status'] ) ? 'Free' : 'Premium'; ?></p>
							<div class="screenshot">
								<img src="<?php echo $skin['screenshot']; ?>" style="width:80%; height:auto" alt=""/>
							</div>
						</div>
						<div class="content">
							<div class="links" style="text-align: center;">
								<form action="<?php echo admin_url( 'admin.php?page=flag-skins' ) . '&amp;type=' . $type; ?>" method="post">
									<?php wp_nonce_field( 'skin_install' ); ?>
									<input type="hidden" name="skinzipurl" value="<?php echo $skin['download']; ?>"/>
									<p>
										<a class="install button-primary" onclick="jQuery(this).closest('form').submit(); return false" href="<?php echo $skin['download']; ?>">
											<?php
											if ( in_array( $skin['uid'], $skins_to_update ) ) {
												_e( 'Update', 'flash-album-gallery' );
											} else {
												_e( 'Install', 'flash-album-gallery' );
											}
											?>
										</a>
										&nbsp;&nbsp;&nbsp;<a class="button" href="<?php echo $skin['demo']; ?>" target="_blank"><?php _e( 'Preview', 'gmLang' ) ?></a>
										<?php
										if ( ! $flag_options['license_name'] ) {
											if ( $skin['status'] && 'free' !== $skin['status'] ) {
												?>
												&nbsp;&nbsp;&nbsp;<a href="http://bit.ly/2jPNRB0" class="button button-primary button-red" target="_blank"><?php _e( 'Get Premium', 'flash-album-gallery' ) ?></a>
												<?php
											}
										} else {
											if ( 'GRANDPackPlus' === $skin['status'] && 'GRANDPackPlus' !== $flag_options['license_name'] ) {
												?>
												&nbsp;&nbsp;&nbsp;<a href="http://bit.ly/2jPNRB0" class="button button-primary button-red" target="_blank"><?php _e( 'Upgrade License', 'flash-album-gallery' ) ?></a>
												<?php
											} elseif ( 'GRANDPack' === $skin['status'] && ! in_array( $flag_options['license_name'], array(
														'GRANDPackPlus',
														'GRANDPack',
													)
												) ) {
												?>
												&nbsp;&nbsp;&nbsp;<a href="http://bit.ly/2jPNRB0" class="button button-primary button-red" target="_blank"><?php _e( 'Upgrade License', 'flash-album-gallery' ) ?></a>
												<?php
											}
										}

										?>
									</p>
								</form>
							</div>
						</div>
					</div>
					<?php
				}
			} else { ?>
				<div class="skin noskins"><?php if ( ! $skins_xml_error ) {
						echo sprintf( __( 'All available %s skins are already installed...', 'gmLang' ), $stype );
					} else {
						echo $skins_xml_error;
					} ?></div>
			<?php }
			?>
		</div>
	</div>


</div>
