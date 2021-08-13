<?php
/**
 * Return a script for the flash slideshow. Can be used in any tmeplate with <?php echo flagShowFlashAlbum($galleryID, $name, $width, $skin) ? >
 *
 * @access public
 * @param integer $galleryID ID of the gallery
 * @param string $name
 * @param string $width
 * @param string $skin
 * @param string $preset
 * @param string $linkto
 * @param bool $fullwindow
 * @param string $align
 * @return string the content
 */
function flagShowFlashAlbum($galleryID, $name='', $width='', $skin='', $preset='', $linkto='', $fullwindow=false, $align='') {
	global $post, $wpdb, $flag;

	$flag->shortcode++;

	if($linkto) {
		$post = get_post($linkto);
	}
	$flag_options = get_option('flag_options');
	if($skin == '') $skin = $flag_options['flashSkin'];
	$skin = sanitize_flagname($skin);
	$preset = sanitize_text_field($preset);
	$skinpath = str_replace("\\","/", WP_PLUGIN_DIR ).'/flagallery-skins/'.$skin;
    if(!is_dir($skinpath)) {
        $skinpath = str_replace("\\","/", WP_PLUGIN_DIR ).'/flash-album-gallery/skins/'.$skin;
        if(!is_dir($skinpath)) {
            $skin = 'phantom';
            $skinpath = str_replace("\\","/", WP_PLUGIN_DIR ).'/flagallery-skins/'.$skin;
            if(!is_dir($skinpath)) {
                $skinpath = str_replace("\\","/", WP_PLUGIN_DIR ).'/flash-album-gallery/skins/'.$skin;
            }
        }
    }
	if (empty($name) ) $name  = 'Gallery';
    $out = '';

    require_once(dirname(dirname(__FILE__)) . '/admin/functions.php');

	/**
	 * @var $default_options
	 */
	include $skinpath . '/settings.php';
	$settings = $default_options;
	if(isset($flag_options["{$skin}_options"])){
		$db_skin_options = maybe_unserialize( $flag_options["{$skin}_options"] );
		if(isset($db_skin_options['presets'])){
			if(isset($db_skin_options['presets'][$preset])){
				$db_skin_options = $db_skin_options['presets'][$preset];
			}
			unset($db_skin_options['presets']);
		}
		$settings = array_replace_recursive( $settings, $db_skin_options );
	}
	$settings['key'] = $flag_options['license_key'];
	$settings['name'] = $flag_options['license_name'];

    $gID = explode( '_', $galleryID ); // get the gallery id
    $width = $width? (strpos($width, '%')? $width : $width.'px') : '';
    $galleryID = 'sc' . $flag->shortcode . '_' . $galleryID;

    if ( is_user_logged_in() ) $exclude_clause = '';
    else $exclude_clause = ' AND exclude<>1 ';

	if ( ! $flag->options['license_name'] ) {
		$limit = ' LIMIT 60';
	} else {
		$limit = '';
	}

	$data = array();
    foreach ( $gID as $galID ) {
        $galID = (int) $galID;
        $status = $wpdb->get_var("SELECT status FROM $wpdb->flaggallery WHERE gid={$galID}");
        if(intval($status)){
            continue;
        }

        if ( $galID == 0) {
            $pictures = $wpdb->get_results("SELECT pid, galleryid, filename, description, alttext, link, imagedate, sortorder, hitcounter, total_value, total_votes, meta_data FROM $wpdb->flagpictures WHERE 1=1 {$exclude_clause} ORDER BY {$flag_options['galSort']} {$flag_options['galSortDir']} {$limit}", ARRAY_A);
	        $gallery = array(
            	'gid' => $galID,
            	'name' => sanitize_file_name( $name ),
	            'path' => '',
	            'title' => $name,
	            'galdesc' => '',
            );
        } else {
	        $gallery = $wpdb->get_row("SELECT gid, name, path, title, galdesc FROM $wpdb->flaggallery WHERE gid={$galID}", ARRAY_A);
	        $pictures = $wpdb->get_results("SELECT pid, filename, description, alttext, link, imagedate, hitcounter, total_value, total_votes, meta_data FROM $wpdb->flagpictures WHERE galleryid = '{$galID}' {$exclude_clause} ORDER BY {$flag_options['galSort']} {$flag_options['galSortDir']} {$limit}", ARRAY_A);
        }

        if (is_array($pictures) && count($pictures)){
	        $gallery = array_map('stripslashes', $gallery);
	        $gallery['galdesc'] = htmlspecialchars_decode($gallery['galdesc']);
	        $gallery['path'] = site_url($gallery['path']);

	        $broken_images = false;
	        foreach($pictures as $i => $pic){
	            $pictures[$i]['alttext'] = stripslashes($pic['alttext']);
	            $pictures[$i]['description'] = stripslashes($pic['description']);
	            $pictures[$i]['meta_data'] = (array) maybe_unserialize($pic['meta_data']);
	            unset($pictures[$i]['meta_data']['0']);
                if(empty($pictures[$i]['meta_data']['webview'])){
                    //$pictures[$i]['meta_data']['webview'] = flagAdmin::webview_image($pic['pid'], true);
	                unset($pictures[$i]);
	                $broken_images = true;
                }
            }

            if($broken_images){
            	$pictures = array_values($pictures);
            }

            $data[] = array(
            	'gallery' => $gallery,
            	'data' => $pictures
            );
        }
    }
    $is_bot = isset($_SERVER['HTTP_USER_AGENT'])? flagGetUserNow($_SERVER['HTTP_USER_AGENT']) : false;
    $customCSS = isset($settings['customCSS'])? $settings['customCSS'] : '';

    ob_start();
    // create the output
    include $skinpath . '/init.php';
    $out = ob_get_contents();
    ob_end_clean();

	$out = flagallery_img_nolazy( $out );

	if(!empty($customCSS)){
        $customCSS = '<style type="text/css" class="flagallery_skin_style_import">' . $customCSS . '</style>';
	}
	$noscript = '';
    if(!empty($out)){

	    $noscript = '<noscript id="noscript_FlaGallery_' . $galleryID . '">';
	    foreach ( $data as $gallery_data ) {
	        foreach ( $gallery_data['data'] as $img_data ) {
	        	$imgsrc = trailingslashit( $gallery_data['gallery']['path'] ) . $img_data['filename'];
			    if ( ! empty( $img_data['link'] ) ) {
					$noscript .= '<a href="' . esc_url( $img_data['link'] ) . '">';
			    }

			    $noscript .= '<img class="skip-lazy no-lazyload noLazy" data-lazy-src="" src ="' . esc_url( $imgsrc ) . '" alt="' . trim( esc_attr( $img_data['alttext'] ) ) . '" />';

		        if ( ! empty( $img_data['link'] ) ) {
			        $noscript .= '</a>';
		        }
			}
	    }
	    $noscript .= '</noscript>';

	    if($width != '100%' && in_array($align, array('left', 'center', 'right'))){
            $margin = '';
            switch($align){
                case 'left':
                    $margin = 'margin-right: auto;';
                    break;
                case 'center':
                    $margin = 'margin:0 auto;';
                    break;
                case 'right':
                    $margin = 'margin-left: auto;';
                    break;
            }
            $width = $width? "width:{$width};" : '';
            $out = '<div class="flagallerywraper" style="text-align:'.$align.';"><div class="flagallery ' . $skin . '_skin" id="FlaGallery_' . $galleryID . '" style="'.$width . $margin.'">' . $customCSS . $out . '</div></div>';
        } else {
            $width = $width? "style='width:{$width};'" : '';
            $out = '<div class="flagallery ' . $skin . '_skin" id="FlaGallery_' . $galleryID . '" '.$width.'>' . $customCSS . $out . '</div>';
        }
    }

	$out = apply_filters('flag_show_skin_content', $out);

	// Replace doubled spaces with single ones (ignored in HTML any way)
	// Remove single and multiline comments, tabs and newline chars
	$out = preg_replace('@(\s){2,}@', '\1', $out);
	$out = preg_replace(
		'@(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|((?<!:)//.*)|[\t\r\n]@i',
		'',
		$out
	);

	return $out . $noscript;
}

function flagShowMPlayer($playlist, $width, $align = '') {

	require_once ( dirname(dirname(__FILE__)) . '/admin/playlist.functions.php');

	$flag_options = get_option('flag_options');
	$galleryPath = trim($flag_options['galleryPath'],'/');
	$playlist = sanitize_flagname($playlist);
	$playlistPath = $galleryPath.'/playlists/'.$playlist.'.xml';
	$playlist_data = get_playlist_data(ABSPATH.$playlistPath);
    $items = $playlist_data['items'];
    $skin = sanitize_flagname($playlist_data['skin']);
    $skinpath = str_replace("\\","/", WP_PLUGIN_DIR ).'/flagallery-skins/'.$skin;
    if(!is_dir($skinpath)) {
        $skinpath = str_replace("\\","/", WP_PLUGIN_DIR ).'/flash-album-gallery/skins/'.$skin;
        if(!is_dir($skinpath)) {
            $skin = 'jq-mplayer';
            $skinpath = str_replace("\\","/", WP_PLUGIN_DIR ).'/flagallery-skins/'.$skin;
            if(!is_dir($skinpath)) {
                $skinpath = str_replace("\\","/", WP_PLUGIN_DIR ).'/flash-album-gallery/skins/'.$skin;
            }
        }
    }

    if(empty($items)){
        return '';
    }

    $music = get_posts(array('post__in' => $items, 'post_type' => 'attachment', 'orderby' => 'post__in', 'posts_per_page' => -1) );
    if(empty($music)){
        return '';
    }

    $out = '';

    /**
     * @var $default_options
     */
    include $skinpath . '/settings.php';
    $settings = $default_options;
    if(isset($flag_options["{$skin}_options"])){
        $db_skin_options = maybe_unserialize( $flag_options["{$skin}_options"] );
        $settings = array_replace_recursive( $settings, $db_skin_options );
    }
    if(!empty($playlist_data['settings'])){
        $xml_settings = json_decode($playlist_data['settings']);
        if(!empty($xml_settings)){
            $settings = array_replace_recursive($settings, (array)$xml_settings);
        }
    }
    $settings['key'] = $flag_options['license_key'];
    $settings['name'] = $flag_options['license_name'];

    $is_bot = isset($_SERVER['HTTP_USER_AGENT'])? flagGetUserNow($_SERVER['HTTP_USER_AGENT']) : false;
    $customCSS = isset($settings['customCSS'])? $settings['customCSS'] : '';

    ob_start();
    // create the output
    include $skinpath . '/init.php';
    $out = ob_get_contents();
    ob_end_clean();

	$out = flagallery_img_nolazy( $out );

	if(!empty($customCSS)){
        $customCSS = '<style type="text/css" class="flagallery_skin_style_import">' . $customCSS . '</style>';
    }
    if(!empty($out)){
        if($width != '100%' && in_array($align, array('left', 'center', 'right'))){
            $margin = '';
            switch($align){
                case 'left':
                    $margin = 'margin-right: auto;';
                    break;
                case 'center':
                    $margin = 'margin:0 auto;';
                    break;
                case 'right':
                    $margin = 'margin-left: auto;';
                    break;
            }
            $width = $width? "width:{$width};" : '';
            $out = '<div class="flagallerywraper" style="text-align:'.$align.';"><div class="flagallery ' . $skin . '_skin" style="'.$width . $margin.'">' . $customCSS . $out . '</div></div>';
        } else {
            $width = $width? "style='width:{$width};'" : '';
            $out = '<div class="flagallery ' . $skin . '_skin" '.$width.'>' . $customCSS . $out . '</div>';
        }
    }

    $out = apply_filters('flag_show_skin_content', $out);

	// Replace doubled spaces with single ones (ignored in HTML any way)
	// Remove single and multiline comments, tabs and newline chars
	$out = preg_replace('@(\s){2,}@', '\1', $out);
	$out = preg_replace(
		'@(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|((?<!:)//.*)|[\t\r\n]@i',
		'',
		$out
	);

	return $out;
}

function flagShowVPlayer($playlist, $width, $align = '') {

	require_once ( dirname(dirname(__FILE__)) . '/admin/video.functions.php');

	$flag_options = get_option('flag_options');
	$galleryPath = trim($flag_options['galleryPath'],'/');
	$playlist = sanitize_flagname($playlist);
	$playlistPath = $galleryPath.'/playlists/video/'.$playlist.'.xml';
	$playlist_data = get_v_playlist_data(ABSPATH.$playlistPath);
    $items = $playlist_data['items'];
	$skin = sanitize_flagname($playlist_data['skin']);
    $skinpath = str_replace("\\","/", WP_PLUGIN_DIR ).'/flagallery-skins/'.$skin;
    if(!is_dir($skinpath)) {
        $skinpath = str_replace("\\","/", WP_PLUGIN_DIR ).'/flash-album-gallery/skins/'.$skin;
        if(!is_dir($skinpath)) {
            $skin = 'wp-videoplayer';
            $skinpath = str_replace("\\","/", WP_PLUGIN_DIR ).'/flagallery-skins/'.$skin;
            if(!is_dir($skinpath)) {
                $skinpath = str_replace("\\","/", WP_PLUGIN_DIR ).'/flash-album-gallery/skins/'.$skin;
            }
        }
    }

    if(empty($items)){
        return '';
    }

    $videos = get_posts(array('post__in' => $items, 'post_type' => 'attachment', 'orderby' => 'post__in', 'posts_per_page' => -1) );
    if(empty($videos)){
        return '';
    }

    $out = '';

    /**
     * @var $default_options
     */
    include $skinpath . '/settings.php';
    $settings = $default_options;
    if(isset($flag_options["{$skin}_options"])){
        $db_skin_options = maybe_unserialize( $flag_options["{$skin}_options"] );
        $settings = array_replace_recursive( $settings, $db_skin_options );
    }
    if(!empty($playlist_data['settings'])){
        $xml_settings = json_decode($playlist_data['settings']);
        if(!empty($xml_settings)){
            $settings = array_replace_recursive($settings, (array)$xml_settings);
        }
    }
    $settings['key'] = $flag_options['license_key'];
    $settings['name'] = $flag_options['license_name'];

    $is_bot = isset($_SERVER['HTTP_USER_AGENT'])? flagGetUserNow($_SERVER['HTTP_USER_AGENT']) : false;
    $customCSS = isset($settings['customCSS'])? $settings['customCSS'] : '';

    ob_start();
    // create the output
    include $skinpath . '/init.php';
    $out = ob_get_contents();
    ob_end_clean();

	$out = flagallery_img_nolazy( $out );

	if(!empty($customCSS)){
        $customCSS = '<style type="text/css" class="flagallery_skin_style_import">' . $customCSS . '</style>';
    }
    if(!empty($out)){
        if($width != '100%' && in_array($align, array('left', 'center', 'right'))){
            $margin = '';
            switch($align){
                case 'left':
                    $margin = 'margin-right: auto;';
                    break;
                case 'center':
                    $margin = 'margin:0 auto;';
                    break;
                case 'right':
                    $margin = 'margin-left: auto;';
                    break;
            }
            $width = $width? "width:{$width};" : '';
            $out = '<div class="flagallerywraper" style="text-align:'.$align.';"><div class="flagallery ' . $skin . '_skin" style="'.$width . $margin.'">' . $customCSS . $out . '</div></div>';
        } else {
            $width = $width? "style='width:{$width};'" : '';
            $out = '<div class="flagallery ' . $skin . '_skin" '.$width.'>' . $customCSS . $out . '</div>';
        }
    }

    $out = apply_filters('flag_show_skin_content', $out);

	// Replace doubled spaces with single ones (ignored in HTML any way)
	// Remove single and multiline comments, tabs and newline chars
	$out = preg_replace('@(\s){2,}@', '\1', $out);
	$out = preg_replace(
		'@(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|((?<!:)//.*)|[\t\r\n]@i',
		'',
		$out
	);

	return $out;
}

function flagShowVmPlayer($id, $w, $h, $autoplay) {

	$flag_options = get_option('flag_options');

	$videoObject = get_post($id);
	$url = wp_get_attachment_url($videoObject->ID);
	$thumb = get_post_meta($videoObject->ID, 'thumbnail', true);
	$aimg = $thumb? '<img src="'.$thumb.'" style="float:left;margin-right:10px;width:150px;height:auto;" alt="" />' : '';
	$atitle = $videoObject->post_title? '<strong>'.$videoObject->post_title.'</strong>' : '';
	$acontent = $videoObject->post_content? '<div style="padding:4px 0;">'.$videoObject->post_content.'</div>' : '';
	$autoplay = $autoplay? 'autoplay' : '';
	$video = '<div id="video_'.$videoObject->ID.'" style="overflow:hidden;"><video src="' . $url . '" '.$autoplay.' controls poster="' . $thumb . '" width="'.$w.'" height="'.$h.'">'.$aimg.$atitle.$acontent.'<p>Sorry, your browser doesn\'t support this type of video, but don\'t worry, you can <a href="'.$url.'" download="'.basename($url).'">download it</a> and watch it with your favorite video player!</p></video></div>';

	// create the output
	$out = '<div class="grandflv">' . $video . '</div>';
	$out = apply_filters('flag_video_single', $out);

	// Replace doubled spaces with single ones (ignored in HTML any way)
	// Remove single and multiline comments, tabs and newline chars
	$out = preg_replace('@(\s){2,}@', '\1', $out);
	$out = preg_replace(
		'@(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|((?<!:)//.*)|[\t\r\n]@i',
		'',
		$out
	);

	return $out;
}

function flagShowBanner($xml, $width, $align = '') {

	require_once ( dirname(dirname(__FILE__)) . '/admin/banner.functions.php');

	$flag_options = get_option('flag_options');
	$galleryPath = trim($flag_options['galleryPath'],'/');
	$xml = sanitize_flagname($xml);
	$playlistPath = $galleryPath.'/playlists/banner/'.$xml.'.xml';
	$playlist_data = get_b_playlist_data(ABSPATH.$playlistPath);
	$skin = sanitize_flagname($playlist_data['skin']);
	$items = $playlist_data['items'];
    $skinpath = str_replace("\\","/", WP_PLUGIN_DIR ).'/flagallery-skins/'.$skin;
    if(!is_dir($skinpath)) {
        $skinpath = str_replace("\\","/", WP_PLUGIN_DIR ).'/flash-album-gallery/skins/'.$skin;
        if(!is_dir($skinpath)) {
            $skin = 'nivoslider';
            $skinpath = str_replace("\\","/", WP_PLUGIN_DIR ).'/flagallery-skins/'.$skin;
            if(!is_dir($skinpath)) {
                $skinpath = str_replace("\\","/", WP_PLUGIN_DIR ).'/flash-album-gallery/skins/'.$skin;
            }
        }
    }

	if(empty($items)){
	    return '';
    }

	$pictures = get_posts(array('post__in' => $items, 'post_type' => 'attachment', 'orderby' => 'post__in', 'posts_per_page' => -1) );
	if(empty($pictures)){
	    return '';
    }

    $sizes = get_intermediate_image_sizes();
    $sizes[] = 'full';

    foreach($pictures as &$picture) {
        $picture->link = get_post_meta($picture->ID, 'link', true);
        $picture->url = wp_get_attachment_url($picture->ID);
        $picture->sizes = array();
        /* Loop through each of the image sizes. */
        foreach ( $sizes as $size ) {

            /* Get the image source, width, height, and whether it's intermediate. */
            $image = wp_get_attachment_image_src( $picture->ID, $size );
            /* Add the link to the array if there's an image and if $is_intermediate (4th array value) is true or full size. */
            if ( !empty( $image ) || 'full' == $size ){
                $picture->sizes[ $size ] = $image;
            }
        }
    }

    $out = '';

    /**
     * @var $default_options
     */
    include $skinpath . '/settings.php';
    $settings = $default_options;
    if(isset($flag_options["{$skin}_options"])){
        $db_skin_options = maybe_unserialize( $flag_options["{$skin}_options"] );
        $settings = array_replace_recursive( $settings, $db_skin_options );
    }
    if(!empty($playlist_data['settings'])){
        $xml_settings = json_decode($playlist_data['settings']);
        if(!empty($xml_settings)){
            $settings = array_replace_recursive($settings, (array)$xml_settings);
        }
    }
    $settings['key'] = $flag_options['license_key'];
    $settings['name'] = $flag_options['license_name'];

	$is_bot = isset($_SERVER['HTTP_USER_AGENT'])? flagGetUserNow($_SERVER['HTTP_USER_AGENT']) : false;
    $customCSS = isset($settings['customCSS'])? $settings['customCSS'] : '';

    ob_start();
    // create the output
    include $skinpath . '/init.php';
    $out = ob_get_contents();
    ob_end_clean();

    $out = flagallery_img_nolazy( $out );

    if(!empty($customCSS)){
        $customCSS = '<style type="text/css" class="flagallery_skin_style_import">' . $customCSS . '</style>';
    }
    if(!empty($out)){
        if($width != '100%' && in_array($align, array('left', 'center', 'right'))){
            $margin = '';
            switch($align){
                case 'left':
                    $margin = 'margin-right: auto;';
                    break;
                case 'center':
                    $margin = 'margin:0 auto;';
                    break;
                case 'right':
                    $margin = 'margin-left: auto;';
                    break;
            }
            $width = $width? "width:{$width};" : '';
            $out = '<div class="flagallerywraper" style="text-align:'.$align.';"><div class="flagallery ' . $skin . '_skin" style="'.$width . $margin.'">' . $customCSS . $out . '</div></div>';
        } else {
            $width = $width? "style='width:{$width};'" : '';
            $out = '<div class="flagallery ' . $skin . '_skin" '.$width.'>' . $customCSS . $out . '</div>';
        }
    }

    $out = apply_filters('flag_show_skin_content', $out);

	// Replace doubled spaces with single ones (ignored in HTML any way)
	// Remove single and multiline comments, tabs and newline chars
	$out = preg_replace('@(\s){2,}@', '\1', $out);
	$out = preg_replace(
		'@(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|((?<!:)//.*)|[\t\r\n]@i',
		'',
		$out
	);

	return $out;
}

function flagGetUserNow($userAgent) {
	$crawlers = 'Google|msnbot|Rambler|Yahoo|AbachoBOT|accoona|FeedBurner|' .
			'AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|' .
			'GeonaBot|Gigabot|Lycos|MSRBOT|Scooter|AltaVista|IDBot|eStyle|Scrubby|yandex|facebook';
	$isCrawler = (preg_match("/$crawlers/i", $userAgent) > 0);
	return $isCrawler;
}

function flagallery_img_nolazy( $content ) {
	$matches = array();
	preg_match_all( '/<img[\s\r\n]+.*?>/is', $content, $matches );
	$search  = array();
	$replace = array();
	foreach ( $matches[0] as $img_html ) {
		// add the noLazy class to the img element.
		if ( preg_match( '/class=["\']/i', $img_html ) ) {
			$replace_html = preg_replace( '/class=(["\'])(.*?)["\']/is', 'class=$1noLazy $2$1', $img_html );
		} else {
			$replace_html = preg_replace( '/<img/is', '<img class="noLazy"', $img_html );
		}
		array_push( $search, $img_html );
		array_push( $replace, $replace_html );
	}
	$search  = array_unique( $search );
	$replace = array_unique( $replace );

	return str_replace( $search, $replace, $content );
}
