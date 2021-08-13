<?php
/*
* Grand Flagallery Widget
*/

/**
 * flagSlideshowWidget - The slideshow widget control for Grand Flagallery ( require WP2.8 or higher)
 *
 * @package Grand Flagallery
 * @access public
 */
class flagSlideshowWidget extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget_grandpages', 'description' => __( 'Show links to GRAND Pages as random images from the galleries', 'flash-album-gallery') );
		parent::__construct('flag-grandpages', __('FLAGallery GRANDPages', 'flash-album-gallery'), $widget_ops);
	}

	function widget( $args, $instance ) {
		global $wpdb, $flagdb;

		extract( $args );

        $title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);

        $pages = array_filter( array_map ( 'intval', explode( ',', $instance['pages'] ) ) );
		$args = array( 'post_type' => 'flagallery', 'post__in' => $pages, 'orderby' => 'post__in' );
		$loop = new WP_Query( $args );
		$imageList = array();
		while ( $loop->have_posts() ) : $loop->the_post();
			$gp_ID = get_the_ID();
			$flag_custom = get_post_custom($gp_ID);
			$gal_array = maybe_unserialize( $flag_custom["mb_items_array"][0] );
			if('all' == $gal_array){
				$gid = 0;
			}else {
				if(is_array( $gal_array )) {
					$gal_array = array_filter( array_map( 'intval', $gal_array ) );
				} else {
					$gal_array = array_filter( array_map( 'intval', explode( ',', $gal_array ) ) );
				}
				if ( empty( $gal_array ) ) {
					continue;
				}
				$gid = $gal_array[0];
			}
			if($gid){
				$galID = (int) $gid;
				$status = $wpdb->get_var("SELECT status FROM $wpdb->flaggallery WHERE gid={$galID}");
				if(intval($status)){
					continue;
				}
				$imageList[$gp_ID] = $wpdb->get_results("SELECT t.*, tt.* FROM $wpdb->flaggallery AS t INNER JOIN $wpdb->flagpictures AS tt ON t.gid = tt.galleryid WHERE tt.exclude != 1 AND t.gid = {$gid} ORDER by rand() LIMIT 1");
			}
			else if ($gid == 0) {
				$imageList[$gp_ID] = $wpdb->get_results("SELECT t.*, tt.* FROM $wpdb->flaggallery AS t INNER JOIN $wpdb->flagpictures AS tt ON t.gid = tt.galleryid WHERE tt.exclude != 1 AND t.status = 0 ORDER by rand() LIMIT 1");
			} else {
				return false;
			}
			$imageList[$gp_ID]['link'] = get_permalink( $gp_ID );
			$imageList[$gp_ID]['title'] = get_the_title( $gp_ID );
		endwhile;
		echo $before_widget;
		if(!empty($title)) {
			echo $before_title . $title . $after_title;
		}
		echo "\n" . '<div class="flag-widget">'. "\n";

		if (!empty($imageList)){
			$wrapper_r = $instance['width']/$instance['height'];
			foreach($imageList as $key => $image) {
				// get the URL constructor
				$image = new flagImage($image[0]);

				// get the effect code
				$thumbcode = 'class="flag_grandpages"';

				// enable i18n support for alttext and description
				$alttext      =  $imageList[$key]['title'];
				$description  =  strip_tags( htmlspecialchars( stripslashes( flagGallery::i18n($image->description) )) );

				$thumburl = $image->thumbURL;
				$thumbinfo = @getimagesize($image->thumbPath);
				if(($thumbinfo[0] - $instance['width']) < -20){
					$thumburl = $image->webimageURL;
					$thumbpath = $image->webimagePath;
					if(!file_exists($image->webimagePath)){
						$thumburl = $image->imageURL;
						$thumbpath = $image->imagePath;
					}
					$thumbinfo = @getimagesize($thumbpath);
				}
				$thumb_r = $thumbinfo[0]/$thumbinfo[1];
				if($wrapper_r < $thumb_r){
					$orientation = 'flag_thumb_landscape';
					$style = 'width:auto;height:100%;margin:0 0 0 -'.floor(($instance['height']*$thumb_r - $instance['width'])/$instance['width']*50).'%;';
				} else{
					$orientation = 'flag_thumb_portrait';
					$style = 'width:100%;height:auto;margin:-'.floor(($instance['width']/$thumb_r - $instance['height'])/$instance['height']*25).'% 0 0 0;';
				}

				$out = '<a href="'.$imageList[$key]['link'].'" title="' . $image->title . '" ' . $thumbcode .' style="overflow:hidden;display:inline-block;text-align:center;width:'.$instance['width'].'px;height:'.$instance['height'].'px;">';
				$out .= '<img src="'.$thumburl.'" style="'.$style.'" class="'.$orientation.'" title="'.$alttext.'" alt="'.$description.'" />';
				echo $out . '</a>'."\n";

			}
		}

		echo '</div>'."\n";
		echo '<style type="text/css">.flag_grandpages { box-sizing:border-box; border: 1px solid #A9A9A9; margin: 0 2px 2px 0; padding: 0; } .flag_grandpages img {max-width:none;max-height:none;}</style>'."\n";
		echo $after_widget;

	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']	= strip_tags($new_instance['title']);
		$instance['width']	= (int) $new_instance['width'];
		$instance['height']	= (int) $new_instance['height'];
		$instance['pages']	= $new_instance['pages'];

		return $instance;
	}

	function form( $instance ) {
		global $wpdb, $flagdb;

		//Defaults
		$instance = wp_parse_args( (array) $instance, array(
            'title' => 'GRAND Galleries',
            'width' => '75',
            'height'=> '65',
            'pages' =>  '') );
		$title  = esc_html( $instance['title'] );
		$width  = esc_attr( $instance['width'] );
        $height = esc_attr( $instance['height'] );
        $pages = esc_attr( $instance['pages'] );

		?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title :','flash-album-gallery'); ?>
			<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title');?>" type="text" class="widefat" value="<?php echo $title; ?>" />
			</label>
		</p>

		<p>
			<?php _e('Width x Height of thumbs:','flash-album-gallery'); ?><br />
			<input style="width: 50px; padding:3px;" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>" /> x
			<input style="width: 50px; padding:3px;" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo $height; ?>" /> (px)
		</p>

		<div>
			<div><?php _e('Select GRAND Pages:','flash-album-gallery'); ?></div>
			<div class="grandGalleries" style="width: 206px; height: auto; max-height: 160px; overflow: auto; margin-bottom: 10px;">
				<?php
					$args = array( 'post_type' => 'flagallery' );
					$loop = new WP_Query( $args );
					while ( $loop->have_posts() ) : $loop->the_post();
						$id = get_the_ID();
						$ch = in_array($id, explode(',',$pages))? ' checked="checked"' : '';
						echo '<div class="row"><input type="checkbox"'.$ch.' value="' . $id . '" /> <span>' . $id . ' - ' . get_the_title() . '</span></div>' . "\n";
					endwhile;
				?>
			</div>
			<div class="grand_items_array"><?php _e('galleries order:','flash-album-gallery'); ?><br /><input readonly="readonly" type="text" id="<?php echo $this->get_field_id('pages'); ?>" name="<?php echo $this->get_field_name('pages'); ?>" value="<?php echo $pages; ?>" style="width: 206px; font-size:10px;" /></div>
		</div>

	<?php

	}

}

// register it
add_action('widgets_init', 'register_flagSlideshowWidget');
function register_flagSlideshowWidget(){
	register_widget("flagSlideshowWidget");
}


class flagBannerWidget extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget_banner', 'description' => __( 'Show a Grand Flagallery Banner', 'flash-album-gallery') );
		parent::__construct('flag-banner', __('FLAGallery Banner', 'flash-album-gallery'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base);

		$out = self::render_slideshow($instance['xml'] , $instance['width']);

		if ( !empty( $out ) ) {
			echo $before_widget;
			if ( !empty($title) ) {
				echo $before_title . $title . $after_title;
			}
		?>
		<div class="flag_banner widget">
			<?php echo $out; ?>
		</div>
		<?php
			echo $after_widget;
		}

	}

	static function render_slideshow($xml, $w = '100%') {
        $out = do_shortcode('[grandbanner xml='.$xml.' w='.$w.']');
		return $out;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags($new_instance['title']);
		$instance['xml'] = $new_instance['xml'];
		$instance['width'] = $new_instance['width'];

		return $instance;
	}

	function form( $instance ) {

		require_once (dirname( dirname(__FILE__) ) . '/admin/banner.functions.php');

		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => 'Banner', 'xml' => '', 'width' => '100%') );
		$title  = esc_html( $instance['title'] );
		$width  = esc_attr( $instance['width'] );
		$all_playlists = get_b_playlists();
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p>
			<label for="<?php echo $this->get_field_id('xml'); ?>"><?php _e('Select playlist:', 'flash-album-gallery'); ?></label>
				<select size="1" name="<?php echo $this->get_field_name('xml'); ?>" id="<?php echo $this->get_field_id('xml'); ?>" class="widefat">
<?php
	foreach((array)$all_playlists as $playlist_file => $playlist_data) {
		$playlist_name = basename($playlist_file, '.xml');
?>
					<option <?php selected($playlist_name , $instance['xml']); ?> value="<?php echo $playlist_name; ?>"><?php echo $playlist_data['title']; ?></option>
<?php
	}
?>
				</select>
		</p>
		<p><label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width:', 'flash-album-gallery'); ?></label> <input id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" style="padding: 3px; width: 45px;" value="<?php echo $width; ?>" /></p>
<?php
	}

}

// register it
add_action('widgets_init', 'register_flagBannerWidget');
function register_flagBannerWidget(){
	register_widget("flagBannerWidget");
}

function flagBannerWidget($xml, $w = '100%') {

	echo flagBannerWidget::render_slideshow($xml, $w);

}



/**
 * flagWidget - The widget control for Grand Flagallery
 *
 * @package Grand Flagallery
 * @access public
 */
class flagWidget extends WP_Widget {

   	function __construct() {
		$widget_ops = array('classname' => 'flag_images', 'description' => __( 'Add recent or random images from the galleries', 'flash-album-gallery') );
	    parent::__construct('flag-images', __('FLAGallery Widget', 'flash-album-gallery'), $widget_ops);
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']	= strip_tags($new_instance['title']);
		$instance['album']	= (int) $new_instance['album'];
		$instance['skin']	= $new_instance['skin'];

		return $instance;
	}

	function form( $instance ) {
		global $flagdb;

		require_once (dirname( dirname(__FILE__) ) . '/admin/get_skin.php');

		$all_skins = get_skins();

		//Defaults
		$instance = wp_parse_args( (array) $instance, array(
            'title' => 'Galleries',
            'album' =>  '',
			'skin'	=> '' ) );
		$title  = esc_html( $instance['title'] );

		?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title :','flash-album-gallery'); ?>
			<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title');?>" type="text" class="widefat" value="<?php echo $title; ?>" />
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('album'); ?>"><?php _e('Select Album:','flash-album-gallery'); ?>
			<select id="<?php echo $this->get_field_id('album'); ?>" name="<?php echo $this->get_field_name('album'); ?>" class="widefat">
			<?php
				$albumlist = $flagdb->find_all_albums();
				if(is_array($albumlist) && !empty($albumlist)) {
					foreach($albumlist as $album) { ?>
						<option <?php selected( $album->id , $instance['album']); ?> value="<?php echo $album->id; ?>"><?php echo esc_html($album->name); ?></option>
					<?php }
				} else{ ?>
                    <option value="" ><?php _e('No Albums Created','flash-album-gallery'); ?></option>
			    <?php } ?>
			</select>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('skin'); ?>"><?php _e('Select Skin:', 'flash-album-gallery'); ?></label>
				<select size="1" name="<?php echo $this->get_field_name('skin'); ?>" id="<?php echo $this->get_field_id('skin'); ?>" class="widefat">
<?php
				if($all_skins) {
					foreach ( (array)$all_skins as $skin_file => $skin_data) {
						echo '<option value="'.dirname($skin_file).'"';
						if (dirname($skin_file) == $instance['skin']) echo ' selected="selected"';
						echo '>'.$skin_data['Name'].'</option>'."\n";
					}
				}
?>
				</select>
		</p>

	<?php

	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);

		$album = $instance['album'];
		$skin = $instance['skin'];

		echo $before_widget;
		if(!empty($title)) {
			echo $before_title . $title . $after_title;
		}
		echo "\n" . '<div class="flag-widget">'. "\n";
        echo do_shortcode('[flagallery album='.$album.' skin='.$skin.']');
		echo '</div>'."\n";
		echo $after_widget;

	}

}// end widget class

// register it
add_action('widgets_init', 'register_flagWidget');
function register_flagWidget(){
	register_widget("flagWidget");
}

/**
 * flagVideoWidget - The widget control for Grand Flagallery
 *
 * @package Grand Flagallery
 * @access public
 */
class flagVideoWidget extends WP_Widget {

   	function __construct() {
		$widget_ops = array('classname' => 'flag_video', 'description' => __( 'Add recent or random video from the galleries', 'flash-album-gallery') );
	    parent::__construct('flag-video', __('FLAGallery Video Widget', 'flash-album-gallery'), $widget_ops);
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']	= strip_tags($new_instance['title']);
		$instance['width']	= (int) $new_instance['width'];
		$instance['height']	= (int) $new_instance['height'];
		$instance['fwidth']	= (int) $new_instance['fwidth'];
		$instance['fheight']	= (int) $new_instance['fheight'];
		$instance['vxml']	= $new_instance['vxml'];

		return $instance;
	}

	function form( $instance ) {
		global $wpdb, $flagdb;

		require_once (dirname( dirname(__FILE__) ) . '/admin/video.functions.php');

		//Defaults
		$instance = wp_parse_args( (array) $instance, array(
            'title' => 'Videos',
            'width' => '75',
            'height'=> '65',
            'fwidth' => '640',
            'fheight'=> '480',
            'vxml' =>  '' ) );
		$title  = esc_html( $instance['title'] );
		$width  = esc_attr( $instance['width'] );
        $height = esc_attr( $instance['height'] );
		$fwidth  = esc_attr( $instance['fwidth'] );
        $fheight = esc_attr( $instance['fheight'] );

		?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title :','flash-album-gallery'); ?>
			<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title');?>" type="text" class="widefat" value="<?php echo $title; ?>" />
			</label>
		</p>

		<p>
			<?php _e('Width x Height of thumbs:','flash-album-gallery'); ?><br />
			<input style="width: 50px; padding:3px;" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>" /> x
			<input style="width: 50px; padding:3px;" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo $height; ?>" /> (px)
		</p>

		<p>
			<?php _e('Width x Height of popup:','flash-album-gallery'); ?><br />
			<input style="width: 50px; padding:3px;" id="<?php echo $this->get_field_id('fwidth'); ?>" name="<?php echo $this->get_field_name('fwidth'); ?>" type="text" value="<?php echo $fwidth; ?>" /> x
			<input style="width: 50px; padding:3px;" id="<?php echo $this->get_field_id('fheight'); ?>" name="<?php echo $this->get_field_name('fheight'); ?>" type="text" value="<?php echo $fheight; ?>" /> (px)
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('vxml'); ?>"><?php _e('Select Playlist:','flash-album-gallery'); ?>
			<select id="<?php echo $this->get_field_id('vxml'); ?>" name="<?php echo $this->get_field_name('vxml'); ?>" class="widefat">
				<option value="" ><?php _e('Choose playlist','flash-album-gallery'); ?></option>
			<?php
				$all_playlists = get_v_playlists();
				if(is_array($all_playlists)) {
					foreach((array)$all_playlists as $playlist_file => $playlist_data) {
						$playlist_name = basename($playlist_file, '.xml');
				?>
					<option<?php if ($playlist_name == $instance['vxml']) echo ' selected="selected"'; ?> value="<?php echo $playlist_name; ?>"><?php echo $playlist_data['title']; ?></option>
				<?php
					}
				}
			?>
			</select>
			</label>
		</p>

	<?php

	}

	function widget( $args, $instance ) {
		extract( $args );

		require_once (dirname( dirname(__FILE__) ) . '/admin/video.functions.php');
		$flag_options = get_option('flag_options');
        $title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);

		echo $before_widget;
		if(!empty($title)) {
			echo $before_title . $title . $after_title;
		}

		$playlistPath = $flag_options['galleryPath'].'playlists/video/'.$instance['vxml'].'.xml';
		if(file_exists($playlistPath)) {
			$playlist = get_v_playlist_data(ABSPATH.$playlistPath);
			$items_a = $playlist['items'];

			echo "\n" . '<div class="flag-widget">'. "\n";

			if (count($items_a)){
				foreach($items_a as $item) {
					$flv = get_post($item);
					if($flv->ID) {
				        $thumb = $flvthumb = get_post_meta($item, 'thumbnail', true);
				        if(empty($thumb)) {
				          $thumb = site_url().'/wp-includes/images/crystal/video.png';
				          $flvthumb = '';
				        }
						$url = wp_get_attachment_url($flv->ID);

						// get the effect code
						$thumbcode = 'class="flag_fancyvid"';

						// enable i18n support for alttext and description
						$alttext      =  strip_tags( htmlspecialchars( stripslashes( $flv->post_title )) );
						$description  =  strip_tags( htmlspecialchars( stripslashes( $flv->post_content )) );

						//TODO:For mixed portrait/landscape it's better to use only the height setting, if widht is 0 or vice versa
						$out = '<a href="'.plugins_url().'/flash-album-gallery/flagframe.php?mv='.$flv->ID.'&amp;w=1&amp;h='.$instance['fheight'].'" style="width:'.$instance['width'].'px;height:'.$instance['height'].'px;background:url(\''.$thumb.'\') no-repeat center center;background-size:cover;" title="' . $alttext . '" ' . $thumbcode .'>';
						$out .= '<img src="'.$thumb.'" width="'.$instance['width'].'" height="'.$instance['height'].'" title="'.$alttext.'" alt="'.$description.'" />';
						echo $out . '</a>'."\n";
					}
				}
			}

		} else {
			echo '<p>'.__('Error! No playlist.','flash-album-gallery').'</p>';
		}

		echo '</div>'."\n";
		echo '<style type="text/css">.flag_fancyvid {overflow:hidden;display:inline-block;border:1px solid #A9A9A9;margin:0 2px 2px 0;} .flag_fancyvid img { width:100%; height:auto; opacity:0; }</style>'."\n";
		echo '<script type="text/javascript" defer="defer">jQuery(function(){ var fvVar = "'.plugins_url('/', dirname(__FILE__)).'"; var fvW = '.$instance['fwidth'].', fvH = '.$instance['fheight'].'; waitJQv(fvVar,fvW,fvH); });</script>'."\n";
		echo $after_widget;
	}

}// end widget class

// register it
add_action('widgets_init', 'register_flagVideoWidget');
function register_flagVideoWidget() {
	register_widget("flagVideoWidget");
}


/**
 * flagMusicWidget - The widget control for Grand Flagallery
 *
 * @package Grand Flagallery
 * @access public
 */
class flagMusicWidget extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget_music', 'description' => __( 'Show a Grand Flagallery Music Player', 'flash-album-gallery') );
		parent::__construct('flag-music', __('FLAGallery Music', 'flash-album-gallery'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base);

		$out = self::render_music($instance['xml'], $instance['width']);

		if ( !empty( $out ) ) {
			echo $before_widget;
			if(!empty($title)) {
				echo $before_title . $title . $after_title;
			}
		?>
		<div class="flag_banner widget">
			<?php echo $out; ?>
		</div>
		<?php
			echo $after_widget;
		}

	}

	static function render_music($xml, $w = '100%') {
        $out = do_shortcode('[grandmusic playlist='.$xml.' w='.$w.']');
		return $out;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags($new_instance['title']);
		$instance['xml'] = $new_instance['xml'];
		$instance['width'] = $new_instance['width'];

		return $instance;
	}

	function form( $instance ) {

		require_once (dirname( dirname(__FILE__) ) . '/admin/playlist.functions.php');

		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => 'Music', 'xml' => '', 'width' => '100%') );
		$title  = esc_html( $instance['title'] );
		$width  = esc_attr( $instance['width'] );
		$all_playlists = get_playlists();
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p>
			<label for="<?php echo $this->get_field_id('xml'); ?>"><?php _e('Select playlist:', 'flash-album-gallery'); ?></label>
				<select size="1" name="<?php echo $this->get_field_name('xml'); ?>" id="<?php echo $this->get_field_id('xml'); ?>" class="widefat">
<?php
	foreach((array)$all_playlists as $playlist_file => $playlist_data) {
		$playlist_name = basename($playlist_file, '.xml');
?>
					<option <?php selected($playlist_name , $instance['xml']); ?> value="<?php echo $playlist_name; ?>"><?php echo $playlist_data['title']; ?></option>
<?php
	}
?>
				</select>
		</p>
		<p><label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width:', 'flash-album-gallery'); ?></label> <input id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" style="padding: 3px; width: 45px;" value="<?php echo $width; ?>" /></p>
<?php
	}

}

// register it
add_action('widgets_init', 'register_flagMusicWidget');
function register_flagMusicWidget() {
	register_widget("flagMusicWidget");
}

function flagMusicWidget($xml, $w = '100%') {

	echo flagMusicWidget::render_music($xml, $w);

}
