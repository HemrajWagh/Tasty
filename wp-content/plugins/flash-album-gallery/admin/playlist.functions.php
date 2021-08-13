<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])){
    die('You are not allowed to call this page directly.');
}

function get_playlist_data($playlist_file){

    $playlist_content = file_get_contents($playlist_file);

    $playlist_data['settings']    = flagGallery::flagGetBetween($playlist_content, '<settings><![CDATA[', ']]></settings>');
    $playlist_data['title']       = flagGallery::flagGetBetween($playlist_content, '<title><![CDATA[', ']]></title>');
    $playlist_data['skin']        = flagGallery::flagGetBetween($playlist_content, '<skin><![CDATA[', ']]></skin>');
    $playlist_data['description'] = flagGallery::flagGetBetween($playlist_content, '<description><![CDATA[', ']]></description>');
    preg_match_all('|<item id="(.*)">|', $playlist_content, $items);
    $playlist_data['items'] = $items[1];

    return $playlist_data;
}

/**
 * Check the playlists directory and retrieve all playlist files with playlist data.
 *
 */
function get_playlists($playlist_folder = ''){

    $flag_options   = get_option('flag_options');
    $flag_playlists = array();
    $playlist_root  = ABSPATH . $flag_options['galleryPath'] . 'playlists';
    if( !empty($playlist_folder)){
        $playlist_root = $playlist_folder;
    }

    // Files in flagallery/playlists directory
    $playlists_dir  = @ opendir($playlist_root);
    $playlist_files = array();
    if($playlists_dir){
        while(($file = readdir($playlists_dir)) !== false){
            if(substr($file, 0, 1) == '.'){
                continue;
            }
            if(substr($file, - 4) == '.xml'){
                $playlist_files[] = $file;
            }
        }
    }
    @closedir($playlists_dir);

    if( !$playlists_dir || empty($playlist_files)){
        return $flag_playlists;
    }

    foreach($playlist_files as $playlist_file){
        if( !is_readable("$playlist_root/$playlist_file")){
            continue;
        }

        $playlist_data = get_playlist_data("$playlist_root/$playlist_file");

        if(empty ($playlist_data['title'])){
            continue;
        }

        $flag_playlists[ basename($playlist_file, ".xml") ] = $playlist_data;
    }
    uasort($flag_playlists, function($a, $b) { return strnatcasecmp( $a["title"], $b["title"] ); } );

    return $flag_playlists;
}

function flagSavePlaylist($title, $descr, $data, $file = '', $skinaction = ''){

    if( !trim($title)){
        $title = 'default';
    }
    $title = htmlspecialchars_decode(stripslashes($title), ENT_QUOTES);
    $descr = htmlspecialchars_decode(stripslashes($descr), ENT_QUOTES);
    if( !$file){
        $file = sanitize_flagname($title);
    }
    if( !is_array($data)){
        $data = explode(',', $data);
    }

    $flag_options = get_option('flag_options');
    $skin         = isset($_POST['skinname'])? sanitize_flagname($_POST['skinname']) : 'music_default';
    if( !$skinaction){
        $skinaction = isset($_POST['skinaction'])? sanitize_key($_POST['skinaction']) : 'update';
    }
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
    $playlistPath = ABSPATH . $flag_options['galleryPath'] . 'playlists/' . $file . '.xml';

    if(file_exists($skinpath . "/settings.php")){
        /**
         * @var $default_options
         */
        include($skinpath . "/settings.php");
    } else{
        flagGallery::show_message(__("Can't find skin settings", 'flash-album-gallery'));

        return;
    }

    $arr_xml_settings = array();
    $settings = array();
    if(file_exists($playlistPath)){
        $playlist     = file_get_contents($playlistPath);
        $xml_settings = flagGallery::flagGetBetween($playlist, '<settings><![CDATA[', ']]></settings>');
        if($xml_settings){
            $arr_xml_settings = json_decode($xml_settings);
            if(!empty($arr_xml_settings)){
                $settings = array_replace_recursive((array)$arr_xml_settings, $settings);
            }
        }
    }
    if(empty($settings) || ($skin !== $skinaction)){
        $settings = $default_options;
        if(isset($flag_options["{$skin}_options"])){
            $db_skin_options = maybe_unserialize( $flag_options["{$skin}_options"] );
            $settings = array_replace_recursive( $settings, $db_skin_options );
        }
    }
    $properties = json_encode($settings);

    $content = '<gallery>
<settings><![CDATA[' . $properties . ']]></settings>
<category id="' . $file . '">
	<properties>
		<title><![CDATA[' . $title . ']]></title>
		<description><![CDATA[' . $descr . ']]></description>
		<skin><![CDATA[' . $skin . ']]></skin>
	</properties>
	<items>';

	if(count($data)){
        foreach((array) $data as $id){
            $mp3 = get_post($id);
            if( $mp3->ID){
                $thumb   = get_post_meta($id, 'thumbnail', true);
                $content .= '
		<item id="' . $mp3->ID . '">
          <track>' . wp_get_attachment_url($mp3->ID) . '</track>
          <title><![CDATA[' . $mp3->post_title . ']]></title>
          <description><![CDATA[' . $mp3->post_content . ']]></description>
          <thumbnail>' . $thumb . '</thumbnail>
        </item>';
            }
        }
	}

    $content .= '
	</items>
</category>
</gallery>';
    // Save options
    $flag_options = get_option('flag_options');
    if(wp_mkdir_p(ABSPATH . $flag_options['galleryPath'] . 'playlists/')){
        if(flagGallery::saveFile($playlistPath, $content, 'w')){
            flagGallery::show_message(__('Playlist Saved Successfully', 'flash-album-gallery'));
        }
    } else{
        flagGallery::show_message(__('Create directory please:', 'flash-album-gallery') . '"/' . $flag_options['galleryPath'] . 'playlists/"');
    }
}

function flagSavePlaylistSkin($file){

    $file         = sanitize_flagname($file);
    $flag_options = get_option('flag_options');
    $playlistPath = ABSPATH . $flag_options['galleryPath'] . 'playlists/' . $file . '.xml';
    // Save options
    $title = esc_html($_POST['playlist_title']);
    $descr = esc_html($_POST['playlist_descr']);
    $items = get_playlist_data($playlistPath);
    $data  = $items['items'];
    flagSavePlaylist($title, $descr, $data, $file, $skinaction = 'update');
}

function flag_playlist_delete($playlist){
    $playlist     = sanitize_flagname($playlist);
    $flag_options = get_option('flag_options');
    $playlistXML  = ABSPATH . $flag_options['galleryPath'] . 'playlists/' . $playlist . '.xml';
    if(file_exists($playlistXML)){
        if(unlink($playlistXML)){
            flagGallery::show_message("'" . $playlist . ".xml' " . __('deleted', 'flash-album-gallery'));
        }
    }
}
