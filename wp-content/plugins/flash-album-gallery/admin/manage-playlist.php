<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {	die('You are not allowed to call this page directly.');}

function flag_playlist_edit() {
	global $wpdb;
	$filepath = admin_url() . 'admin.php?page=' . urlencode($_GET['page']);
	$all_playlists = get_playlists();
	$flag_options = get_option('flag_options');
	$playlistPath = $flag_options['galleryPath'].'playlists/'.sanitize_flagname($_GET['playlist']).'.xml';
	$playlist = get_playlist_data(ABSPATH.$playlistPath);
	$items_a = $playlist['items'];
	$items = implode(',',$playlist['items']);
?>
<script type="text/javascript">
//<![CDATA[
function checkAll(form)
{
	jQuery(form).find(':checkbox').each(function(){this.checked = !this.checked});
	return false;
}

function getNumChecked(form)
{
	var num = 0;
	for (i = 0, n = form.elements.length; i < n; i++) {
		if(form.elements[i].type == "checkbox") {
			if(form.elements[i].name == "doaction[]")
				if(form.elements[i].checked == true)
					num++;
		}
	}
	return num;
}

// this function check for a the number of selected images, sumbmit false when no one selected
function checkSelected() {

	var numchecked = getNumChecked(document.getElementById('updatePlaylist'));

	if(numchecked < 1) {
		alert('<?php echo esc_js(__("No items selected", "flash-album-gallery")); ?>');
		return false;
	}

	actionId = jQuery('#bulkaction').val();

	switch (actionId) {
		case "delete_items":
			return confirm('<?php echo sprintf(esc_js(__("You are about to delete %s item(s) \n \n 'Cancel' to stop, 'OK' to proceed.",'flash-album-gallery')), "' + numchecked + '") ; ?>');
			break;
	}

	return confirm('<?php echo sprintf(esc_js(__("You are about to start the bulk edit for %s item(s) \n \n 'Cancel' to stop, 'OK' to proceed.",'flash-album-gallery')), "' + numchecked + '") ; ?>');
}

function showDialog( windowId, height ) {
	var form = document.getElementById('updatePlaylist');
	var elementlist = "";
	for (i = 0, n = form.elements.length; i < n; i++) {
		if(form.elements[i].type == "checkbox") {
			if(form.elements[i].name == "doaction[]")
				if(form.elements[i].checked == true)
					if (elementlist == "")
						elementlist = form.elements[i].value;
					else
						elementlist += "," + form.elements[i].value;
		}
	}
	jQuery("#" + windowId + "_bulkaction").val(jQuery("#bulkaction").val());
	jQuery("#" + windowId + "_playlist").val(elementlist);
	tb_show("", "#TB_inline?width=640&height=" + height + "&inlineId=" + windowId + "&modal=true", false);
}
var current_image = '';
function send_to_editor(html) {
	var source = html.match(/src=\".*\" alt/);
	source = source[0].replace(/^src=\"/, "").replace(/" alt$/, "");
	jQuery('#mp3thumb-'+actInp).attr('value', source);
	jQuery('#thumb-'+actInp).attr('src', source);
	tb_remove();
}
jQuery(document).ready(function(){
  jQuery('.del_thumb').click(function(){
    var id = jQuery(this).attr('data-id');
	jQuery('#mp3thumb-'+id).attr('value', '');
	jQuery('#thumb-'+id).attr('src', '<?php echo site_url()."/wp-includes/images/crystal/audio.png"; ?>');
    return false;
  });
  jQuery('#skinname').change(function(){
  	var skin = jQuery(this).val();
	jQuery('#skinOptions').attr("href","<?php echo FLAG_URLPATH; ?>admin/skin_options.php?show_options=1&amp;skin="+skin+"&amp;TB_iframe=1&amp;width=600&amp;height=560");
  });
});
//]]>
</script>

<div class="flag-wrap">
<h2><?php _e( 'Playlist', 'flash-album-gallery' ); ?>: <?php echo esc_html($playlist['title']); ?></h2>
<div style="float: right; margin: -20px 3px 0 0;">
<span><a href="<?php echo $filepath; ?>"><?php _e('Back to Music Box', 'flash-album-gallery'); ?></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
<select name="select_playlist" onchange="window.location.href=this.options[this.selectedIndex].value">
	<option selected="selected"><?php _e('Choose another playlist', 'flash-album-gallery'); ?></option>
<?php
	foreach((array)$all_playlists as $playlist_file => $playlist_data) {
		$playlist_name = basename($playlist_file, '.xml');
		if ($playlist_file == sanitize_flagname($_GET['playlist'])) continue;
?>
	<option value="<?php echo esc_url($filepath."&playlist=".$playlist_name."&mode=edit"); ?>"><?php echo esc_html($playlist_data['title']); ?></option>
<?php
	}
?>
</select>
</div>
<form id="updatePlaylist" class="flagform" method="POST" action="<?php echo esc_url($filepath."&playlist=".sanitize_flagname($_GET['playlist'])."&mode=edit"); ?>" accept-charset="utf-8">
<?php wp_nonce_field('flag_update'); ?>
<input type="hidden" name="flag_page" value="manage-playlist" />

<div id="poststuff" class="metabox-holder">
<div id="post-body"><div id="post-body-content"><div id="normal-sortables" style="position: relative;">
	<div id="flagalleryset" class="postbox" >
		<h3 class="hndle"><span><?php _e('Playlist settings', 'flash-album-gallery'); ?></span></h3>
		<div class="inside">
			<table cellspacing="8" cellpadding="0" border="0">
				<tr>
					<th align="left" valign="middle" scope="row"><?php _e('Shortcode', 'flash-album-gallery'); ?>:</th>
					<td align="left" valign="middle"><input type="text" readonly="readonly" size="50" onfocus="this.select()" value="[grandmusic playlist=<?php echo sanitize_flagname($_GET['playlist']); ?>]" /></td>
					<td rowspan="3" align="left" valign="top"><div><strong style="display: inline-block; width: 100px;"><?php _e("Playlist Skin", 'flash-album-gallery'); ?>:</strong>
						<input id="skinaction" type="hidden" name="skinaction" value="<?php echo sanitize_flagname($playlist['skin']); ?>" />
                        <select id="skinname" name="skinname" style="width: 200px; height: 24px; font-size: 11px;">
                          <?php require_once (dirname(__FILE__) . '/get_skin.php');
                            $all_skins = get_skins($skin_folder='', $type='m');
                            $current_skin_title = __('No Skin', 'flash-album-gallery');
                            if(count($all_skins)) {
                            	foreach ( (array)$all_skins as $skin_file => $skin_data) {
                                    $cur = '';
                                    if($playlist['skin'] == dirname($skin_file)){
                                        $cur = ' selected="selected"';
                                        $current_skin_title = $skin_data['Name'];
                                    }
                            		echo '<option'.$cur.' value="'.dirname($skin_file).'">'.$skin_data['Name'].'</option>'."\n";
                            	}
                            } else {
                                echo '<option value="music_default">'.__("No Skins", "flash-album-gallery").'</option>';
                            }
                          ?>
                        </select>&nbsp;&nbsp;<a id="skinOptions" class="thickbox" title="<?php echo esc_attr($current_skin_title); ?>" href="<?php echo FLAG_URLPATH.'admin/skin_options.php?show_options=1&amp;skin='.sanitize_flagname($playlist['skin']).'&amp;TB_iframe=1&amp;width=600&amp;height=560'; ?>"><?php _e('Change Skin Options', 'flash-album-gallery' ); ?></a>
                    </div>
					<p style="margin:10px 0 0 100px;"><input type="submit" id="updatePlaylistSkin" name="updatePlaylistSkin" class="button-primary action"  value="<?php _e('Update skin options for this playlist', 'flash-album-gallery'); ?>" /></p>
					</td>
				</tr>
				<tr>
					<th align="left" valign="middle" scope="row"><?php _e('Title', 'flash-album-gallery'); ?>:</th>
					<td align="left" valign="middle"><input type="text" size="50" name="playlist_title" value="<?php echo esc_html($playlist['title']); ?>" /></td>
				</tr>
				<tr>
					<th align="left" valign="top" scope="row"><?php _e('Description', 'flash-album-gallery'); ?>:</th>
					<td align="left" valign="top"><textarea name="playlist_descr" cols="60" rows="2" style="width: 95%" ><?php echo esc_html($playlist['description']); ?></textarea></td>
				</tr>
				<!--<tr>
					<th align="left" valign="top" scope="row"><?php _e('Path', 'flash-album-gallery'); ?>:</th>
					<td align="left" colspan="2" valign="top"><?php echo $playlistPath; ?></td>
				</tr>-->
			</table>
			<div class="clear"></div>
		</div>
	</div>
</div></div></div>
</div> <!-- poststuff -->
<div class="tablenav flag-tablenav">
	<select id="bulkaction" name="bulkaction" class="alignleft">
		<option value="no_action" ><?php _e("No action",'flash-album-gallery')?></option>
		<option value="delete_items" ><?php _e("Delete items",'flash-album-gallery')?></option>
	</select>
	<input class="button-secondary alignleft" style="margin-right:10px;" type="submit" name="updatePlaylist" value="<?php _e("OK",'flash-album-gallery')?>" onclick="if ( !checkSelected() ) return false;" />
	<a href="<?php echo wp_nonce_url($filepath."&playlist=".sanitize_flagname($_GET['playlist'])."&mode=sort", 'flag_sort'); ?>" class="button-secondary alignleft" style="margin:1px 10px 0 0;"><?php _e("Sort Playlist",'flash-album-gallery')?></a>
	<a href="#" onClick="jQuery('#form_listitems').submit();return false;" class="button-secondary alignleft" style="margin:1px 10px 0 0;"><?php _e("Add/Remove Items from Playlist",'flash-album-gallery')?></a>
	<input type="submit" name="updatePlaylist" class="button-primary action alignright"  value="<?php _e("Update Playlist",'flash-album-gallery')?>" />
</div>

<table id="flag-listmusic" class="widefat fixed flag-table" cellspacing="0" >

	<thead>
	<tr>
		<th class="cb" width="54" scope="col"><a href="#" onclick="checkAll(document.getElementById('updatePlaylist'));return false;"><?php _e('Check', 'flash-album-gallery'); ?></a></th>
		<th class="id" width="134" scope="col"><div><?php _e('ID', 'flash-album-gallery'); ?></div></th>
		<th class="size" width="75" scope="col"><div><?php _e('Size', 'flash-album-gallery'); ?></div></th>
		<th class="thumb" width="110" scope="col"><div><?php _e('Thumbnail', 'flash-album-gallery'); ?></div></th>
		<th class="title_filename" scope="col"><div><?php _e('Filename / Title', 'flash-album-gallery'); ?></div></th>
		<th class="description" scope="col"><div><?php _e('Description', 'flash-album-gallery'); ?></div></th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<th class="cb" scope="col"><a href="#" onclick="checkAll(document.getElementById('updatePlaylist'));return false;"><?php _e('Check', 'flash-album-gallery'); ?></a></th>
		<th class="id" scope="col"><?php _e('ID', 'flash-album-gallery'); ?></th>
		<th class="size" scope="col"><?php _e('Size', 'flash-album-gallery'); ?></th>
		<th class="thumb" scope="col"><?php _e('Thumbnail', 'flash-album-gallery'); ?></th>
		<th class="title_filename" scope="col"><?php _e('Filename / Title', 'flash-album-gallery'); ?></th>
		<th class="description" scope="col"><?php _e('Description', 'flash-album-gallery'); ?></th>
	</tr>
	</tfoot>
	<tbody>
<?php
$counter = 0;
if(count($items_a)) {
    $alt = ' class="alternate"';
	$flag_options = get_option('flag_options');
	$uploads = wp_upload_dir();
	foreach($items_a as $item) {
		$mp3 = get_post($item);
        $thumb = $mp3thumb = get_post_meta($item, 'thumbnail', true);
        if(empty($thumb)) {
          $thumb = site_url().'/wp-includes/images/crystal/audio.png';
          $mp3thumb = '';
        }
		$alt = ( empty($alt) ) ? ' class="alternate"' : '';
		$alt2 = ( empty($alt) ) ? '' : ' alternate';
		$url = wp_get_attachment_url($mp3->ID);
		$counter++;
?>
		<tr id="mp3-<?php echo $mp3->ID; ?>"<?php echo $alt; ?> valign="top">
			<th class="cb" scope="row" height="24" style="padding-bottom: 0; border-bottom: none;"><input name="doaction[]" type="checkbox" value="<?php echo $mp3->ID; ?>" /></th>
			<td class="id" style="padding-bottom: 0; border-bottom: none;"><p style="margin-bottom: 3px; white-space: nowrap;">ID: <?php echo $mp3->ID; ?></p></td>
			<td class="size" style="padding-bottom: 0; border-bottom: none;"><?php
				$path = $uploads['basedir'].str_replace($uploads['baseurl'],'',$url);
				$size = filesize($path);
				echo round($size/1024/1024,2).' Mb';
			?></td>
			<td class="thumb" rowspan="2">
				<div style="width: 100px; height: 100px;"><img id="thumb-<?php echo $mp3->ID; ?>" src="<?php echo esc_url($thumb); ?>" style="height:auto; width:auto; max-height:100px; max-width:100px;" alt="" /></div>
			</td>
			<td class="title_filename" rowspan="2">
				<strong><a href="<?php echo $url; ?>"><?php echo basename($url); ?></a></strong><br />
				<textarea title="Title" name="item_a[<?php echo $mp3->ID; ?>][post_title]" cols="20" rows="1" style="width:95%; height: 25px; overflow:hidden;"><?php echo esc_html(stripslashes($mp3->post_title)); ?></textarea><br />
				<p><?php _e('Thumb URL:', 'flash-album-gallery'); ?> <input id="mp3thumb-<?php echo $mp3->ID; ?>" name="item_a[<?php echo $mp3->ID; ?>][post_thumb]" type="text" value="<?php echo esc_url($mp3thumb); ?>" /> <a class="thickbox" onclick="actInp=<?php echo $mp3->ID; ?>" href="media-upload.php?type=image&amp;TB_iframe=1&amp;width=640&amp;height=400" title="<?php _e('Add an Image','flash-album-gallery'); ?>"><?php _e('assist', 'flash-album-gallery'); ?></a></p>
			</td>
			<td class="description" rowspan="2">
				<textarea name="item_a[<?php echo $mp3->ID; ?>][post_content]" style="width:95%; height: 96px; margin-top: 2px; font-size:12px; line-height:115%;" rows="1" ><?php echo esc_html(stripslashes($mp3->post_content)); ?></textarea>
			</td>
		</tr>
        <tr class="mp3-<?php echo $mp3->ID.$alt2; ?>">
            <td valign="top" class="player" colspan="3"><audio src="<?php echo esc_url($url); ?>" controls preload="none" autobuffer="false"></audio></td>
        </tr>
		<?php
	}
}

// In the case you have no capaptibility to see the search result
if ( $counter==0 )
	echo '<tr><td colspan="5" align="center"><strong>'.__('No entries found','flash-album-gallery').'</strong></td></tr>';

?>

		</tbody>
	</table>
	<p class="submit" style="text-align: right;"><input type="submit" class="button-primary action" name="updatePlaylist" value="<?php _e("Update Playlist",'flash-album-gallery')?>" /></p>
	</form>
	<form id="form_listitems" name="form_listitems" method="POST" action="<?php echo esc_url($filepath."&playlist=".sanitize_flagname($_GET['playlist'])."&mode=add"); ?>">
		<?php wp_nonce_field('flag_add'); ?>
		<input type="hidden" name="items" value="<?php echo $items; ?>" />
	</form>
	<br class="clear"/>
	</div><!-- /#wrap -->
	<?php
}
