<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {	die('You are not allowed to call this page directly.');}

function flag_picturelist() {
// *** show picture list
	global $wpdb, $flagdb, $user_ID, $flag;

	// Look if its a search result
	$is_search = isset ($_GET['s']) ? true : false;

	if ($is_search) {

		// fetch the imagelist
		$picturelist = $flag->manage_page->search_result;

		// we didn't set a gallery or a pagination
		$act_gid     = 0;
		$_GET['paged'] = 1;
		$page_links = false;

	} else {

		// GET variables
		$act_gid    = $flag->manage_page->gid;

		// Load the gallery metadata
		$gallery = $flagdb->find_gallery($act_gid);

		if (!$gallery) {
			flagGallery::show_error(__('Gallery not found.', 'flash-album-gallery'));
			return;
		}

		// Check if you have the correct capability
		if (!flagAdmin::can_manage_this_gallery($gallery->author)) {
			flagGallery::show_error(__('Sorry, you have no access here', 'flash-album-gallery'));
			return;
		}

		// look for pagination
		if ( ! isset( $_GET['paged'] ) || intval($_GET['paged']) < 1 )
			$_GET['paged'] = 1;

		$_GET['paged'] = intval($_GET['paged']);
		$start = ( $_GET['paged'] - 1 ) * 50;

		// get picture values
		$picturelist = $flagdb->get_gallery($act_gid, $flag->options['galSort'], $flag->options['galSortDir'], false, 50, $start );

		// build pagination
		$page_links = paginate_links( array(
			'base' => add_query_arg( 'paged', '%#%' ),
			'format' => '',
			'prev_text' => __('&laquo;'),
			'next_text' => __('&raquo;'),
			'total' => $flagdb->paged['max_objects_per_page'],
			'current' => $_GET['paged']
		));

		// get the current author
		$act_author_user    = get_userdata( (int) $gallery->author );

	}

	// list all galleries
	$gallerylist = $flagdb->find_all_galleries();

	//get the columns
	$gallery_columns = flag_manage_gallery_columns();
	$hidden_columns  = get_hidden_columns('flag-manage-images');
	$hidden_columns = array_filter($hidden_columns);
	if($picturelist){
		$a_hits = array();
		foreach($picturelist as $p){
			$a_hits[] = $p->hitcounter;
		}
		if(!array_sum($a_hits)){
			$hidden_columns[] = 'views_likes';
			$hidden_columns[] = 'rating';
		}
	} else {
		$hidden_columns[] = 'views_likes';
		$hidden_columns[] = 'rating';
	}
	$num_columns     = count($gallery_columns) - count($hidden_columns);
?>
<!--[if lt IE 8]>
	<style type="text/css">
		.custom_thumb {
			display : none;
		}
	</style>
<![endif]-->

<script type="text/javascript">
//<![CDATA[
function showDialog( windowId, height ) {
	var form = document.getElementById('updategallery');
	var elementlist = "";
	for (i = 0, n = form.elements.length; i < n; i++) {
		if(form.elements[i].type == "checkbox") {
			if(form.elements[i].name == "doaction[]")
				if(form.elements[i].checked == true)
					if (elementlist == "")
						elementlist = form.elements[i].value;
					else
						elementlist += "," + form.elements[i].value ;
		}
	}
	jQuery("#" + windowId + "_bulkaction").val(jQuery("#bulkaction").val());
	jQuery("#" + windowId + "_imagelist").val(elementlist);
	// console.log (jQuery("#TB_imagelist").val());
	tb_show("", "#TB_inline?width=640&height=" + height + "&inlineId=" + windowId + "&modal=true", false);
}

function checkAll(form)
{
	for (i = 0, n = form.elements.length; i < n; i++) {
		if(form.elements[i].type == "checkbox") {
			if(form.elements[i].name == "doaction[]") {
				if(form.elements[i].checked == true)
					form.elements[i].checked = false;
				else
					form.elements[i].checked = true;
			}
		}
	}
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

	var numchecked = getNumChecked(document.getElementById('updategallery'));

	if(numchecked < 1) {
		alert('<?php echo esc_js(__("No images selected", "flash-album-gallery")); ?>');
		return false;
	}

	actionId = jQuery('#bulkaction').val();

	switch (actionId) {
		case "copy_to":
		case "move_to":
			showDialog('selectgallery', 120);
			return false;
			break;
		case "resize_images":
			showDialog('resize_images', 120);
			return false;
			break;
		case "new_thumbnail":
			showDialog('new_thumbnail', 160);
			return false;
			break;
	}

	return confirm('<?php echo sprintf(esc_js(__("You are about to start the bulk edit for %s images \n \n 'Cancel' to stop, 'OK' to proceed.",'flash-album-gallery')), "' + numchecked + '") ; ?>');
}

jQuery(document).ready( function() {
	// close postboxes that should be closed
	jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');
	postboxes.add_postbox_toggles('flag-manage-gallery');

});
//]]>
</script>

<div class="flag-wrap">

<?php if ($is_search) :?>
<h2><?php printf( __('Search results for &#8220;%s&#8221;', 'flash-album-gallery'), esc_html( stripslashes(get_search_query()) ) ); ?></h2>
<form class="search-form" action="" method="get">
<p class="search-box">
	<label class="hidden" for="media-search-input"><?php _e( 'Search Images', 'flash-album-gallery' ); ?>:</label>
	<input type="hidden" id="page-name" name="page" value="flag-manage-gallery" />
	<input type="text" id="media-search-input" name="s" value="<?php the_search_query(); ?>" />
	<input type="submit" value="<?php _e( 'Search Images', 'flash-album-gallery' ); ?>" class="button" />
</p>
</form>

<br style="clear: both;" />

<form id="updategallery" class="flagform" method="POST" action="<?php echo esc_url($flag->manage_page->base_page . '&mode=edit&s=' . urlencode(get_search_query())); ?>" accept-charset="utf-8">
<?php wp_nonce_field('flag_updategallery'); ?>
<input type="hidden" name="flag_page" value="manage-images" />

<?php else :?>

<h2><?php echo _n( 'Gallery', 'Galleries', 1, 'flash-album-gallery' ); ?> : <?php echo esc_html($gallery->title); ?></h2>
<select name="select_gid" style="width:180px; float: right; margin: -20px 3px 0 0;" onchange="window.location.href=this.options[this.selectedIndex].value">
	<option selected="selected"><?php _e('Choose another gallery', 'flash-album-gallery'); ?></option>
<?php
	foreach ($gallerylist as $gal) {
		if ($gal->gid != $act_gid) {
?>
	<option value="<?php echo wp_nonce_url( $flag->manage_page->base_page . "&amp;mode=edit&amp;gid=" . $gal->gid, 'flag_editgallery')?>" ><?php if($flag->options['albSort'] == 'gid'){ echo $gal->gid.' - '; } echo esc_html(stripslashes($gal->title)); if($flag->options['albSort'] == 'title'){ echo ' ('.$gal->gid.')'; } ?></option>
<?php
		}
	}
?>
</select>

<form id="updategallery" class="flagform" method="POST" action="<?php echo $flag->manage_page->base_page . '&amp;mode=edit&amp;gid=' . $act_gid . '&amp;paged=' . intval($_GET['paged']); ?>" accept-charset="utf-8">
<?php wp_nonce_field('flag_updategallery'); ?>
<input type="hidden" name="flag_page" value="manage-images" />

<div id="poststuff" class="metabox-holder">
	<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
<div id="post-body"><div id="post-body-content"><div id="normal-sortables" class="meta-box-sortables ui-sortable" style="position: relative;">
	<div id="flagalleryset" class="postbox <?php echo postbox_classes('flagalleryset', 'flag-manage-gallery'); ?>" >
		<div class="handlediv" title="Click to toggle"><br/></div>
		<h3 class="hndle"><span><?php _e('Gallery settings', 'flash-album-gallery'); ?></span></h3>
		<div class="inside">
			<table class="flag-form-table" >
				<tr>
					<th align="right" scope="row"><?php _e('Title', 'flash-album-gallery'); ?>:</th>
					<td> </td>
					<td align="left"><input type="text" size="50" name="title" value="<?php echo esc_html($gallery->title); ?>"  /></td>
				</tr>
				<tr>
					<th align="right" valign="top" scope="row"><?php _e('Description', 'flash-album-gallery'); ?>:</th>
					<td> </td>
					<td align="left"><textarea name="gallerydesc" cols="30" rows="3" style="width: 95%" ><?php echo esc_html($gallery->galdesc); ?></textarea></td>
				</tr>
				<tr>
					<th align="right" scope="row"><?php _e('Path', 'flash-album-gallery'); ?>:</th>
					<td> </td>
					<td align="left"><input <?php if (IS_WPMU) echo 'readonly = "readonly"'; ?> type="text" size="50" name="path" value="<?php echo esc_attr($gallery->path); ?>"  /></td>
				</tr>
				<tr>
					<th align="right" scope="row"><?php _e('Author', 'flash-album-gallery'); ?>:</th>
					<td> </td>
					<td align="left">
					<?php
						$editable_ids = $flag->manage_page->get_editable_user_ids( $user_ID );
						if ( $editable_ids && count( $editable_ids ) > 1 )
							wp_dropdown_users( array('include' => $editable_ids, 'name' => 'author', 'selected' => empty( $gallery->author ) ? 0 : $gallery->author ) );
						else
							echo $act_author_user->display_name;
					?>
						<input type="hidden" name="previewpic" value="<?php if(is_array($picturelist) && count($picturelist)) { $pic = reset($picturelist); echo $pic->pid; }; ?>" />
					</td>
				</tr>
			</table>

			<div class="submit">
				<input type="submit" class="button-secondary" name="scanfolder" value="<?php _e("Scan Folder for new images",'flash-album-gallery')?> " />
				<input type="submit" class="button-primary action" name="updatepictures" value="<?php _e("Save Changes",'flash-album-gallery')?>" />
			</div>
			<div class="clear"></div>
		</div>
	</div>
</div></div></div>
</div> <!-- poststuff -->
<?php endif; ?>

<?php
global $flag;
if ( ! $flag->options['license_name'] ) {
	?>
	<div style="overflow:hidden; margin: 40px 0; padding: 10px; background-color: #fff; border: 1px solid red; border-radius: 5px; font-size: 14px; font-weight: bold;"><?php _e( 'Note: Free version allows you to show maximum 60 images per gallery on the frontend. Purchase lifetime license <a href="https://mypgc.co/membership/" target="_blank">here</a>. It\'s a one time payment.', 'flash-album-gallery' ); ?></div>
	<?php
}
?>
<div class="tablenav flag-tablenav">
	<?php if ( $page_links ) : ?>
	<div class="tablenav-pages"><?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
		number_format_i18n( ( $_GET['paged'] - 1 ) * $flagdb->paged['objects_per_page'] + 1 ),
		number_format_i18n( min( $_GET['paged'] * $flagdb->paged['objects_per_page'], $flagdb->paged['total_objects'] ) ),
		number_format_i18n( $flagdb->paged['total_objects'] ),
		$page_links
	); echo $page_links_text; ?></div>
	<?php endif; ?>
	<div class="alignleft actions">
	<select id="bulkaction" name="bulkaction" class="alignleft">
		<option value="no_action" ><?php _e("No action",'flash-album-gallery')?></option>
		<option value="webview_images" ><?php _e("Create images optimized for web",'flash-album-gallery'); ?></option>
		<option value="new_thumbnail" ><?php _e("Create new thumbnails",'flash-album-gallery')?></option>
		<option value="resize_images" ><?php _e("Resize images",'flash-album-gallery')?></option>
		<option value="delete_images" ><?php _e("Delete images",'flash-album-gallery')?></option>
		<option value="import_meta" ><?php _e("Import metadata",'flash-album-gallery')?></option>
		<option value="copy_meta" ><?php _e("Metadata to description",'flash-album-gallery')?></option>
		<option value="copy_to" ><?php _e("Copy to...",'flash-album-gallery')?></option>
		<option value="move_to"><?php _e("Move to...",'flash-album-gallery')?></option>
		<option value="reset_counters"><?php _e("Reset Views & Likes counters", 'flash-album-gallery') ?></option>
		<?php do_action('flag_manage_images_bulkaction'); ?>
	</select>
	<input class="button-secondary alignleft" style="margin-right:10px;" type="submit" name="showThickbox" value="<?php _e("OK",'flash-album-gallery')?>" onclick="if ( !checkSelected() ) return false;" />

<?php if (($flag->options['galSort'] == "sortorder") && (!$is_search) ) { ?>
	<a href="<?php echo wp_nonce_url( $flag->manage_page->base_page . "&amp;mode=sort&amp;gid=" . $act_gid, 'flag_sortgallery')?>" class="button-secondary alignleft" style="margin:1px 10px 0 0;"><?php _e("Sort gallery",'flash-album-gallery')?></a>
<?php }
	 if(current_user_can('FlAG Upload images') && (!$is_search)){ ?>
	<a href="<?php echo wp_nonce_url( $flag->manage_page->base_page . "&amp;gid=" . $act_gid . "&amp;tabs=1", 'flag_addimages')?>" class="button-secondary alignleft" style="margin:1px 10px 0 0;"><?php _e("Add Images",'flash-album-gallery')?></a>
<?php } ?>
	<input type="submit" name="updatepictures" class="button-primary action alignleft"  value="<?php _e("Save Changes",'flash-album-gallery')?>" />
	</div>
</div>

<table id="flag-listimages" class="widefat fixed flag-table" cellspacing="0" >

	<thead>
	<tr>
<?php foreach($gallery_columns as $key=>$value){
		if ( in_array($key, $hidden_columns) )
			continue;
		echo $cols = '<th class="manage-column column-'.$key.'">'.$value.'</td>';
	}
?>
	</tr>
	</thead>
	<tfoot>
	<tr>
<?php foreach($gallery_columns as $key=>$value){
		if ( in_array($key, $hidden_columns) )
			continue;
		if($key == 'cb') { $value = ''; }
		echo $cols = '<th class="manage-column column-'.$key.'">'.$value.'</td>';
	}
?>
	</tr>
	</tfoot>
	<tbody>
<?php
$counter	= 0;
if($picturelist) {

	$rt=array(24.5, 45.7, 54.8, 59.3, 64.7, 68.9, 71.5, 73.7, 75.9, 77.1);

	foreach($picturelist as $picture) {

		//for search result we need to check the capatibiliy
		if ( !flagAdmin::can_manage_this_gallery($picture->author) && $is_search )
			continue;

		$hits = intval($picture->hitcounter);
		$votes = intval($picture->total_votes);

		$counter++;
		$pid       = (int) $picture->pid;
		$alternate = ( !isset($alternate) || $alternate == 'alternate' ) ? '' : 'alternate';
		$exclude   = ( $picture->exclude ) ? 'checked="checked"' : '';
		$date = mysql2date(get_option('date_format'), $picture->imagedate);
		$time = mysql2date(get_option('time_format'), $picture->imagedate);

		?>
		<tr id="picture-<?php echo $pid; ?>" class="<?php echo $alternate; ?> iedit"  valign="top">
			<?php
			foreach($gallery_columns as $gallery_column_key => $column_display_name) {
				$class = "class=\"$gallery_column_key column-$gallery_column_key\"";

				$style = '';
				if ( in_array($gallery_column_key, $hidden_columns) )
					continue;

				$attributes = "$class$style";

				switch ($gallery_column_key) {
					case 'cb' :
						?>
						<th <?php echo $attributes; ?> scope="row"><input name="doaction[]" type="checkbox" value="<?php echo $pid; ?>" /></th>
						<?php
					break;
					case 'id' :
						?>
						<td <?php echo $attributes; ?>><?php echo $pid; ?>
							<input type="hidden" name="pid[]" value="<?php echo $pid; ?>" />
						</td>
						<?php
					break;
					case 'thumbnail' :
						?>
						<td <?php echo $attributes; ?>><a href="<?php echo $picture->imageURL; ?>" class="thickbox" title="<?php echo $picture->filename; ?>">
								<img class="thumb" src="<?php echo $picture->thumbURL; ?>" id="thumb-<?php echo $pid; ?>" />
							</a>
						</td>
						<?php
					break;
					case 'filename' :
						?>
						<td <?php echo $attributes; ?>>
							<strong><a href="<?php echo $picture->imageURL; ?>" class="thickbox" title="<?php echo $picture->filename; ?>">
								<?php echo $picture->filename; ?>
							</a></strong>
							<br /><?php echo $date; ?>
							<?php if ( !empty($picture->meta_data['width']) ) {
								echo '<br />'.__('Image size: ', 'flash-album-gallery').$picture->meta_data['width'].'x'.$picture->meta_data['height'];
							} else {
								$imgpath = WINABSPATH.$picture->path."/".$picture->filename;
								$img = @getimagesize($imgpath);
								if($img) echo '<br />'.__('Image size: ', 'flash-album-gallery').$img[0].'x'.$img[1];
							} ?>
							<?php if ( !empty($picture->meta_data['thumbnail']) ) {
								echo '<br />'.__('Thumbnail size: ', 'flash-album-gallery').$picture->meta_data['thumbnail']['width'].'x'.$picture->meta_data['thumbnail']['height'];
							} ?>
							<?php if ( !empty($picture->meta_data['webview']) ) {
								echo '<br />'.__('Optimized size: ', 'flash-album-gallery').$picture->meta_data['webview'][0].'x'.$picture->meta_data['webview'][1];
							} else {
								echo '<br />'.__('Optimized size: ', 'flash-album-gallery').__('not optimized ', 'flash-album-gallery');
							} ?>
							<p>
							<?php
							$actions = array();
							$actions['view']   = '<a class="thickbox" href="' . $picture->imageURL . '" title="' . esc_attr(sprintf(__('View "%s"'), $picture->filename)) . '">' . __('View', 'flash-album-gallery') . '</a>';
							$actions['meta']   = '<a class="thickbox" href="' . FLAG_URLPATH . 'admin/showmeta.php?id=' . $pid . '" title="' . __('Show Meta data','flash-album-gallery') . '">' . __('Meta', 'flash-album-gallery') . '</a>';
							$actions['custom_thumb']   = '<a class="thickbox" href="' . FLAG_URLPATH . 'admin/manage_thumbnail.php?id=' . $pid . '&amp;width=800" title="' . __('Customize thumbnail','flash-album-gallery') . '">' . __('Edit thumb', 'flash-album-gallery') . '</a>';
							$actions['delete'] = '<a class="submitdelete" href="' . wp_nonce_url("admin.php?page=flag-manage-gallery&amp;mode=delpic&amp;gid=".$act_gid."&amp;pid=".$pid, 'flag_delpicture'). '" class="delete column-delete" onclick="javascript:check=confirm( \'' . esc_attr(sprintf(__('Delete "%s"' , 'flash-album-gallery'), $picture->filename)). '\');if(check==false) return false;">' . __('Delete','flash-album-gallery') . '</a>';
							$action_count = count($actions);
							$i = 0;
							echo '<div class="row-actions">';
							foreach ( $actions as $action => $link ) {
								++$i;
								( $i == $action_count ) ? $sep = '' : $sep = ' | ';
								echo "<span class='$action'>$link$sep</span>";
							}
							echo '</div>';
							?></p>
						</td>
						<?php
					break;
					case 'views_likes' :
						?>
						<td <?php echo $attributes; ?>>
							<input name="hitcounter[<?php echo $pid; ?>]" type="text" value="<?php echo stripslashes($picture->hitcounter); ?>" /> /
							<input name="total_votes[<?php echo $pid; ?>]" type="text" value="<?php echo stripslashes($picture->total_votes); ?>" />
						</td>
						<?php
					break;
					case 'rating' :
						?>
						<td <?php echo $attributes; ?>>
							<?php
								if($votes==0){
									$like = '0.0';
								}else if($votes<11){
									$like = $rt[$votes-1];
								}else{
									$like = round( ((100-$rt[count($rt)-1])/($hits>0?$hits:1))*($votes<=$hits?$votes:$hits), 1 ) + $rt[count($rt)-1];
								}
								echo $like.'%';
							?>
						</td>
						<?php
					break;
					case 'alt_title_desc' :
						?>
						<td <?php echo $attributes; ?>>
							<input name="alttext[<?php echo $pid; ?>]" type="text" style="width:95%; margin-bottom: 2px;" value="<?php echo esc_html(stripslashes($picture->alttext)); ?>" placeholder="<?php _e('Alt & Title', 'flash-album-gallery'); ?>" /><br/>
							<textarea name="description[<?php echo $pid; ?>]" style="width:95%; margin-top: 2px;" rows="2" placeholder="<?php _e('Description', 'flash-album-gallery'); ?>"><?php echo esc_html(stripslashes($picture->description)); ?></textarea>
							<input name="link[<?php echo $pid; ?>]" type="text" style="width:95%; margin-bottom: 2px;" value="<?php echo esc_attr(stripslashes($picture->link)); ?>" placeholder="<?php _e('Link To (optional)', 'flash-album-gallery'); ?>" /><br/>
						</td>
						<?php
					break;
					case 'exclude' :
						?>
						<td <?php echo $attributes; ?>><input name="exclude[<?php echo $pid; ?>]" type="checkbox" value="1" <?php echo $exclude; ?> /></td>
						<?php
					break;
					/*
                    case 'views' :
						?>
						<td <?php echo $attributes; ?>><?php echo $picture->hitcounter; ?></td>
						<?php
					break;
                    */
					default :
						?>
						<td <?php echo $attributes; ?>><?php do_action('flag_manage_gallery_custom_column', $gallery_column_key, $pid); ?></td>
						<?php
					break;
				}
			?>
			<?php } ?>
		</tr>
		<?php
	}
}

// In the case you have no capaptibility to see the search result
if ( $counter==0 )
	echo '<tr><td colspan="' . $num_columns . '" align="center"><strong>'.__('No entries found','flash-album-gallery').'</strong></td></tr>';

?>

		</tbody>
	</table>
	<p class="submit"><input type="submit" class="button-primary action" name="updatepictures" value="<?php _e("Save Changes",'flash-album-gallery')?>" /></p>
	</form>
	<br class="clear"/>
	</div><!-- /#wrap -->

	<!-- #selectgallery -->
	<div id="selectgallery" style="display: none;" >
		<form id="form-select-gallery" method="POST" action="<?php echo admin_url('admin.php?page=flag-manage-gallery&mode=edit&gid='.$act_gid.'&paged=1'); ?>" accept-charset="utf-8">
		<?php wp_nonce_field('flag_thickbox_form'); ?>
		<input type="hidden" id="selectgallery_imagelist" name="TB_imagelist" value="" />
		<input type="hidden" id="selectgallery_bulkaction" name="TB_bulkaction" value="" />
		<input type="hidden" name="flag_page" value="manage-images" />
		<table width="100%" border="0" cellspacing="3" cellpadding="3" >
		  	<tr>
		    	<th>
		    		<?php _e('Select the destination gallery:', 'flash-album-gallery'); ?>&nbsp;
		    		<select name="dest_gid" style="width:90%" >
		    			<?php
		    				foreach ($gallerylist as $gallery) {
		    					if ($gallery->gid != $act_gid) {
		    			?>
						<option value="<?php echo $gallery->gid; ?>" ><?php echo $gallery->gid; ?> - <?php echo esc_html($gallery->title); ?></option>
						<?php
		    					}
		    				}
		    			?>
		    		</select>
		    	</th>
		  	</tr>
		  	<tr align="right">
		    	<td class="submit">
		    		<input type="submit" class="button-primary" name="TB_SelectGallery" value="<?php _e("OK",'flash-album-gallery')?>" />
		    		&nbsp;
		    		<input class="button-secondary" type="reset" value="<?php _e("Cancel",'flash-album-gallery')?>" onclick="tb_remove()"/>
		    	</td>
			</tr>
		</table>
		</form>
	</div>
	<!-- /#selectgallery -->

	<!-- #resize_images -->
	<div id="resize_images" style="display: none;" >
		<form id="form-resize-images" method="POST" action="<?php echo admin_url('admin.php?page=flag-manage-gallery&mode=edit&gid='.$act_gid.'&paged=1'); ?>" accept-charset="utf-8">
		<?php wp_nonce_field('flag_thickbox_form'); ?>
		<input type="hidden" id="resize_images_imagelist" name="TB_imagelist" value="" />
		<input type="hidden" id="resize_images_bulkaction" name="TB_bulkaction" value="" />
		<input type="hidden" name="flag_page" value="manage-images" />
		<table width="100%" border="0" cellspacing="3" cellpadding="3" >
			<tr valign="top">
				<td>
					<strong><?php _e('Resize Images to', 'flash-album-gallery'); ?>:</strong>
				</td>
				<td>
					<input type="text" size="5" name="imgWidth" value="<?php echo $flag->options['imgWidth']; ?>" /> x <input type="text" size="5" name="imgHeight" value="<?php echo $flag->options['imgHeight']; ?>" />
					<br /><small><?php _e('Width x height (in pixel). Grand Flagallery will keep ratio size','flash-album-gallery'); ?></small>
				</td>
			</tr>
		  	<tr align="right">
		    	<td colspan="2" class="submit">
		    		<input class="button-primary" type="submit" name="TB_ResizeImages" value="<?php _e('OK', 'flash-album-gallery'); ?>" />
		    		&nbsp;
		    		<input class="button-secondary" type="reset" value="&nbsp;<?php _e('Cancel', 'flash-album-gallery'); ?>&nbsp;" onclick="tb_remove()"/>
		    	</td>
			</tr>
		</table>
		</form>
	</div>
	<!-- /#resize_images -->

	<!-- #new_thumbnail -->
	<div id="new_thumbnail" style="display: none;" >
		<form id="form-new-thumbnail" method="POST" action="<?php echo admin_url('admin.php?page=flag-manage-gallery&mode=edit&gid='.$act_gid.'&paged=1'); ?>" accept-charset="utf-8">
		<?php wp_nonce_field('flag_thickbox_form'); ?>
		<input type="hidden" id="new_thumbnail_imagelist" name="TB_imagelist" value="" />
		<input type="hidden" id="new_thumbnail_bulkaction" name="TB_bulkaction" value="" />
		<input type="hidden" name="flag_page" value="manage-images" />
		<table width="100%" border="0" cellspacing="3" cellpadding="3" >
			<tr valign="top">
				<th align="left"><?php _e('Width x height (in pixel)','flash-album-gallery'); ?></th>
				<td><input type="number" size="5" maxlength="5" min="300" max="800" name="thumbWidth" value="<?php echo $flag->options['thumbWidth']; ?>" /> x <input type="number" size="5" maxlength="5" min="300" max="800" name="thumbHeight" value="<?php echo $flag->options['thumbHeight']; ?>" />
				<br /><small><?php _e('These values are maximum values ','flash-album-gallery'); ?></small></td>
			</tr>
		  	<tr align="right">
		    	<td colspan="2" class="submit">
		    		<input class="button-primary" type="submit" name="TB_NewThumbnail" value="<?php _e('OK', 'flash-album-gallery'); ?>" />
		    		&nbsp;
		    		<input class="button-secondary" type="reset" value="&nbsp;<?php _e('Cancel', 'flash-album-gallery'); ?>&nbsp;" onclick="tb_remove()"/>
		    	</td>
			</tr>
		</table>
		</form>
	</div>
	<!-- /#new_thumbnail -->

	<script type="text/javascript">
	/* <![CDATA[ */
	jQuery(document).ready(function(){columns.init('flag-manage-images');});
	/* ]]> */
	</script>
	<?php
}

// define the columns to display, the syntax is 'internal name' => 'display name'
function flag_manage_gallery_columns() {
	global $flag;

	$gallery_columns = array();

	$gallery_columns['cb'] = '<input name="checkall" type="checkbox" onclick="checkAll(document.getElementById(\'updategallery\'));" />';
	$gallery_columns['id'] = __('ID');
	$gallery_columns['thumbnail'] = __('Thumbnail', 'flash-album-gallery');
	$gallery_columns['filename'] = __('Filename', 'flash-album-gallery');
	$gallery_columns['views_likes'] = __('Views / Likes', 'flash-album-gallery');
	$gallery_columns['rating'] = __('Rating', 'flash-album-gallery');
	$gallery_columns['alt_title_desc'] = __('Alt &amp; Title Text', 'flash-album-gallery') . ' / ' . __('Description', 'flash-album-gallery');// . ' / ' . __('Link', 'flash-album-gallery');
	$gallery_columns['exclude'] = '<span title="'.__('Only for logged in users', 'flash-album-gallery').'"><img src="'.FLAG_URLPATH.'admin/images/lock.png" alt="member view" /> Private</span>';
	$gallery_columns = apply_filters('flag_manage_images_columns', $gallery_columns);

	return $gallery_columns;
}
