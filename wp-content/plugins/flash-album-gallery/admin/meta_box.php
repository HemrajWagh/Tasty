<?php if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 	die('You are not allowed to call this page directly.'); }

global $flag, $flagdb, $post;
require_once (dirname(__FILE__) . '/get_skin.php');
$i_skins = get_skins();
$flag_custom = get_post_custom($post->ID);
if(isset($flag_custom["mb_items_array"][0])){
	$flag_custom["mb_items_array"][0] = maybe_unserialize($flag_custom["mb_items_array"][0]);
	if(is_string($flag_custom["mb_items_array"][0])) {
		$flag_custom["mb_items_array"][0] = array_map( 'intval', explode( ',', $flag_custom["mb_items_array"][0] ) );
	}
}
$items_array = !empty($flag_custom["mb_items_array"][0])? array_unique( array_filter( $flag_custom["mb_items_array"][0] ) ) : array();
$skinname = isset($flag_custom["mb_skinname"][0])? $flag_custom["mb_skinname"][0] : '';
$skinpreset = isset($flag_custom["mb_skinpreset"][0])? $flag_custom["mb_skinpreset"][0] : '';
$scode = isset($flag_custom["mb_scode"][0])? $flag_custom["mb_scode"][0] : '';
$mb_galorderby = isset($flag_custom["mb_galorderby"][0])? $flag_custom["mb_galorderby"][0] : '';
$mb_galorder = isset($flag_custom["mb_galorder"][0])? $flag_custom["mb_galorder"][0] : '';
$flag_custom["mb_galexclude"][0] = isset($flag_custom["mb_galexclude"][0])? maybe_unserialize($flag_custom["mb_galexclude"][0]) : array();
$mb_galexclude = !empty($flag_custom["mb_galexclude"][0])? array_unique( array_filter( array_map( 'intval', $flag_custom["mb_galexclude"][0] ) ) ) : array();
$home_button_text = isset($flag_custom["mb_button_home"][0])? $flag_custom["mb_button_home"][0] : '';
$button_text = isset($flag_custom["mb_button"][0])? $flag_custom["mb_button"][0] : '';
$button_link = isset($flag_custom["mb_button_link"][0])? $flag_custom["mb_button_link"][0] : '';
$bg_link = isset($flag_custom["mb_bg_link"][0])? $flag_custom["mb_bg_link"][0] : '';
$bg_pos = isset($flag_custom["mb_bg_pos"][0])? $flag_custom["mb_bg_pos"][0] : 'center center';
$bg_repeat = isset($flag_custom["mb_bg_repeat"][0])? $flag_custom["mb_bg_repeat"][0] : 'repeat';
$bg_size = isset($flag_custom["mb_bg_size"][0])? $flag_custom["mb_bg_size"][0] : 'auto';
?>
<link rel="stylesheet" type="text/css" href="<?php echo set_url_scheme( FLAG_URLPATH, 'admin'); ?>admin/js/selectize/selectize.css" />
<script language="javascript" type="text/javascript" src="<?php echo set_url_scheme( FLAG_URLPATH, 'admin'); ?>admin/js/selectize/selectize.min.js"></script>
<script type="text/javascript">/*<![CDATA[*/
jQuery(document).ready(function() {
  var selected_galleries = jQuery('#mb_items_array').val();
  var galleries = 'gid=' + (selected_galleries ? selected_galleries.join(',') : 'all');
  var galorderby = jQuery('#mb_galorderby').val();
  var galorder = jQuery('#mb_galorder').val();
  var galexclude = jQuery('#mb_galexclude').val();
  if(galexclude) {
    galexclude = galexclude.join(',');
  }
  var skin = jQuery('#mb_skinname option:selected').val();
  if(skin) {
  	var skin_preset = skin.split(':');
    skin = ' skin=' + skin_preset[0];
    if(skin_preset[1]) {
    	skin += " preset='" + skin_preset[1] + "'";
    }
  } else {
    skin = '';
  }
  if('gid=all' == galleries) {
    if(galorderby) {
      galorderby = ' orderby=' + galorderby;
    } else {
      galorderby = '';
    }
    if(galorder) {
      galorder = ' order=' + galorder;
    } else {
      galorder = '';
    }
    if(galexclude) {
      galexclude = ' exclude=' + galexclude;
    } else {
      galexclude = '';
    }
  } else {
    galorderby = '';
    galorder = '';
    galexclude = '';
    jQuery('.sort_tab').css('display', 'none');
  }
  short_code(galleries, skin, galorderby, galorder, galexclude);

  jQuery('#mb_items_array').selectize({
    plugins: ['drag_drop', 'remove_button'],
    create: false,
    hideSelected: true,
    onChange: function(value) {
      if(value) {
        jQuery('.sort_tab').css('display', 'none');
      } else {
        jQuery('.sort_tab').css('display', '');
      }
      selected_galleries = jQuery('#mb_items_array').val();
      galleries = 'gid=' + (selected_galleries ? selected_galleries.join(',') : 'all');
      if(selected_galleries) {
        short_code(galleries, skin, '', '', '');
      } else {
        short_code(galleries, skin, galorderby, galorder, galexclude);
      }
    },
  });
  jQuery('#mb_skinname').change(function() {
    skin = jQuery(this).val();
    if(skin) {
	    var skin_preset = skin.split(':');
	    skin = ' skin=' + skin_preset[0];
	    if(skin_preset[1]) {
		    skin += " preset='" + skin_preset[1] + "'";
	    }
    } else {
      skin = '';
    }
    short_code(galleries, skin, galorderby, galorder, galexclude);
  });
  jQuery('#mb_galorderby').change(function() {
    galorderby = jQuery(this).val();
    if(galorderby) {
      galorderby = ' orderby=' + galorderby;
    } else {
      galorderby = '';
    }
    short_code(galleries, skin, galorderby, galorder, galexclude);
  });
  jQuery('#mb_galorder').change(function() {
    galorder = jQuery(this).val();
    if(galorder) {
      galorder = ' order=' + galorder;
    } else {
      galorder = '';
    }
    short_code(galleries, skin, galorderby, galorder, galexclude);
  });
  jQuery('#mb_galexclude').selectize({
    plugins: ['remove_button'],
    create: false,
    hideSelected: true,
    onChange: function(value) {
      var excluded_galleries = jQuery('#mb_galexclude').val();
      if(excluded_galleries) {
        galexclude = ' exclude=' +excluded_galleries.join(',');
      } else {
        galexclude = '';
      }
      short_code(galleries, skin, galorderby, galorder, galexclude);
    },
  });
});

function short_code(galleries, skin, galorderby, galorder, galexclude) {
  jQuery('#mb_scode').val('[flagallery ' + galleries + ' w=100% h=100%' + skin + galorderby + galorder + galexclude + ' fullwindow=true]');
}

var current_image = '';

function send_to_editor(html) {
  var source = html.match(/src=\".*\" alt/);
  source = source[0].replace(/^src=\"/, '').replace(/" alt$/, '');
  jQuery('#mb_bg_link').val(source);
  tb_remove();
}

/*]]>*/</script>
<div class="wrap">
<form id="generator1">
	<table border="0" cellpadding="4" cellspacing="0" style="width: 90%;">
		<tr>
			<td nowrap="nowrap" valign="middle"><label for="mb_items_array"><?php _e("Select galleries", 'flash-album-gallery'); ?>:</label></td>
			<td style="width: 100%;"><select id="mb_items_array" name="mb_items_array[]" size="6" multiple="multiple" placeholder="<?php _e("Leave blank for all galleries", 'flash-album-gallery'); ?>">
					<option value=""><?php _e("Leave blank for all galleries", 'flash-album-gallery'); ?></option>
					<?php
					$gallerylist = $flagdb->find_all_galleries($flag->options['albSort'], $flag->options['albSortDir']);
					if(is_array($gallerylist)) {
						foreach($gallerylist as $gallery) {
							$name = ( empty($gallery->title) ) ? $gallery->name : esc_html(stripslashes($gallery->title));
							if($flag->options['albSort'] == 'gid'){ $name = $gallery->gid.' - '.$name; }
							if($flag->options['albSort'] == 'title'){ $name = $name.' ('.$gallery->gid.')'; }
							$selected = '';
							if(in_array($gallery->gid, $items_array)){
								$selected = ' selected="selected"';
							}
							echo '<option value="' . $gallery->gid . '" ' . $selected . '>' . $name . '</option>' . "\n";
						}
					}
					?>
				</select></td>
		</tr>
		<tr class="sort_tab">
			<td nowrap="nowrap" valign="middle"><label for="mb_galorderby"><?php _e("Order galleries by", 'flash-album-gallery'); ?>:</label></td>
			<td valign="middle"><select id="mb_galorderby" name="mb_galorderby">
					<option value=""><?php _e("Gallery IDs (default)", 'flash-album-gallery'); ?></option>
					<option value="title" <?php selected($mb_galorderby, 'title'); ?>><?php _e("Gallery Title", 'flash-album-gallery'); ?></option>
					<option value="rand" <?php selected($mb_galorderby, 'rand'); ?>><?php _e("Randomly", 'flash-album-gallery'); ?></option>
				</select></td>
		</tr>
		<tr class="sort_tab">
			<td nowrap="nowrap" valign="middle"><label for="mb_galorder"><?php _e("Order", 'flash-album-gallery'); ?>:</label></td>
			<td valign="middle"><select id="mb_galorder" name="mb_galorder">
					<option value="" selected="selected"><?php _e("DESC (default)", 'flash-album-gallery'); ?></option>
					<option value="ASC" <?php selected($mb_galorder, 'ASC'); ?>><?php _e("ASC", 'flash-album-gallery'); ?></option>
				</select></td>
		</tr>
		<tr class="sort_tab">
			<td nowrap="nowrap" valign="middle"><label for="mb_galexclude"><?php _e("Exclude Gallery", 'flash-album-gallery'); ?>:</label></td>
			<td valign="middle"><select id="mb_galexclude" name="mb_galexclude[]" size="6" multiple="multiple">
					<option value=""></option>
					<?php
					if(is_array($gallerylist)) {
						foreach($gallerylist as $gallery) {
							$name = ( empty($gallery->title) ) ? $gallery->name : esc_html(stripslashes($gallery->title));
							if($flag->options['albSort'] == 'gid'){ $name = $gallery->gid.' - '.$name; }
							if($flag->options['albSort'] == 'title'){ $name = $name.' ('.$gallery->gid.')'; }
							$selected = '';
							if(in_array($gallery->gid, $mb_galexclude)){
								$selected = ' selected="selected"';
							}
							echo '<option value="' . $gallery->gid . '" ' . $selected . '>' . $name . '</option>' . "\n";
						}
					}
					?>
				</select></td>
		</tr>
        <tr>
            <td nowrap="nowrap" valign="top"><p style="padding-top:3px;"><label for="mb_skinname"><?php _e("Choose skin", 'flash-album-gallery'); ?>:</label></p></td>
            <td valign="top"><p><select id="mb_skinname" name="mb_skinname">
                    <option value="" <?php selected($skinname,''); ?>><?php _e("skin active by default", 'flash-album-gallery'); ?></option>
					<?php
						foreach ( (array)$i_skins as $skin_file => $skin_data) {
							echo '<option value="'.dirname($skin_file).'" '.selected($skinname,dirname($skin_file),false).'>'.$skin_data['Name'].'</option>'."\n";

							$act_skin         = dirname( $skin_file );
							$skin_options_key = "{$act_skin}_options";
							if ( ! empty( $flag->options[ $skin_options_key ]['presets'] ) ) {
								foreach ( $flag->options[ $skin_options_key ]['presets'] as $preset_name => $preset_settings ) {
									$val = dirname($skin_file).':'.esc_attr( $preset_name );
									echo '<option value="'.$val.'" '.selected($skinname.':'.$skinpreset,$val,false).'>'.$skin_data['Name'].': '.esc_html( $preset_name ).'</option>'."\n";
								}
							}
						}
					?>
            </select></p>
			<input id="mb_scode" name="mb_scode" type="text" style="width: 98%;"  value="<?php echo $scode; ?>" />
			</td>
        </tr>
		<tr>
			<td nowrap="nowrap" valign="top"><div style="padding-top: 3px;"><?php _e("Home Button Text", 'flash-album-gallery'); ?>: &nbsp; </div></td>
            <td valign="top"><input id="mb_button" name="mb_button_home" type="text" style="width: 49%;" placeholder="<?php _e('Home', 'flash-album-gallery'); ?>" value="<?php echo $home_button_text; ?>" /></td>
		</tr>
		<tr>
			<td nowrap="nowrap" valign="top"><div style="padding-top: 3px;"><?php _e("Back Button Text", 'flash-album-gallery'); ?>: &nbsp; </div></td>
            <td valign="top"><input id="mb_button" name="mb_button" type="text" style="width: 49%;" placeholder="<?php _e('Go Back', 'flash-album-gallery'); ?>" value="<?php echo $button_text; ?>" /></td>
		</tr>
		<tr>
			<td nowrap="nowrap" valign="top"><div style="padding-top: 3px;"><?php _e("Back Button Link", 'flash-album-gallery'); ?>: &nbsp; </div></td>
            <td valign="top"><input id="mb_button_link" name="mb_button_link" type="text" style="width: 49%;" placeholder="<?php echo home_url(); ?>" value="<?php echo $button_link; ?>" /><br />
				<small><?php _e("Leave empty to use referer link", 'flash-album-gallery'); ?></small></td>
		</tr>
		<tr>
			<td nowrap="nowrap" valign="top"><div style="padding-top: 3px;"><?php _e("Background Image Link", 'flash-album-gallery'); ?>: &nbsp; </div></td>
            <td valign="top">
                <input id="mb_bg_link" name="mb_bg_link" type="text" style="width: 49%;"  value="<?php echo $bg_link; ?>" />
                <a class="thickbox" href="media-upload.php?type=image&amp;TB_iframe=1&amp;width=640&amp;height=400" title="<?php _e('Add an Image', 'flash-album-gallery'); ?>"><?php _e('assist', 'flash-album-gallery'); ?></a>
            </td>
		</tr>
        <tr>
            <td nowrap="nowrap" valign="top"><div style="padding-top: 3px;"><?php _e("Background Position", 'flash-album-gallery'); ?>:</div></td>
            <td valign="top"><select id="mb_bg_pos" name="mb_bg_pos">
                    <option value="center center" <?php selected($bg_pos,'center center'); ?>>center center</option>
                    <option value="left top" <?php selected($bg_pos,'left top'); ?>>left top</option>
                    <option value="left center" <?php selected($bg_pos,'left center'); ?>>left center</option>
                    <option value="left bottom" <?php selected($bg_pos,'left bottom'); ?>>left bottom</option>
                    <option value="center top" <?php selected($bg_pos,'center top'); ?>>center top</option>
                    <option value="center bottom" <?php selected($bg_pos,'center bottom'); ?>>center bottom</option>
                    <option value="right top" <?php selected($bg_pos,'right top'); ?>>right top</option>
                    <option value="right center" <?php selected($bg_pos,'right center'); ?>>right center</option>
                    <option value="right bottom" <?php selected($bg_pos,'right bottom'); ?>>right bottom</option>
            </select></td>
        </tr>
        <tr>
            <td nowrap="nowrap" valign="top"><div style="padding-top: 3px;"><?php _e("Background Repeat", 'flash-album-gallery'); ?>:</div></td>
            <td valign="top"><select id="mb_bg_repeat" name="mb_bg_repeat">
                    <option value="repeat" <?php selected($bg_repeat,'repeat'); ?>>repeat</option>
                    <option value="repeat-x" <?php selected($bg_repeat,'repeat-x'); ?>>repeat-x</option>
                    <option value="repeat-y" <?php selected($bg_repeat,'repeat-y'); ?>>repeat-y</option>
                    <option value="no-repeat" <?php selected($bg_repeat,'no-repeat'); ?>>no-repeat</option>
            </select></td>
        </tr>
        <tr>
            <td nowrap="nowrap" valign="top"><div style="padding-top: 3px;"><?php _e("Background Size", 'flash-album-gallery'); ?>:</div></td>
            <td valign="top"><select id="mb_bg_size" name="mb_bg_size">
                    <option value="auto" <?php selected($bg_size,'auto'); ?>>auto</option>
                    <option value="contain" <?php selected($bg_size,'contain'); ?>>contain</option>
                    <option value="cover" <?php selected($bg_size,'cover'); ?>>cover</option>
            </select></td>
        </tr>
    </table>
</form>
</div>
