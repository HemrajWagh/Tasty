<?php

// look up for the path
require_once( dirname( dirname( dirname( __FILE__ ) ) ) . '/flag-config.php' );
require_once( dirname( dirname( __FILE__ ) ) . '/get_skin.php' );

// check for rights
if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
	wp_die( __( "You are not allowed to be here" ) );
}

global $flag, $flagdb, $wp_query;

$all_skins = get_skins();
//$riched = isset($_REQUEST['riched']);
if ( empty( $riched ) ) {
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php _e( "Insert FlaGallery Album with one or more galleries", 'flash-album-gallery' ); ?></title>
	<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php echo get_option( 'blog_charset' ); ?>"/>
	<script language="javascript" type="text/javascript" src="<?php echo set_url_scheme( get_option( 'siteurl' ), 'admin' ); ?>/wp-includes/js/jquery/jquery.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo set_url_scheme( FLAG_URLPATH, 'admin' ); ?>admin/js/tabs.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo set_url_scheme( FLAG_URLPATH, 'admin' ); ?>admin/tinymce/popup.css"/>
	<link rel="stylesheet" type="text/css" href="<?php echo set_url_scheme( FLAG_URLPATH, 'admin' ); ?>admin/js/selectize/selectize.css"/>
	<script language="javascript" type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo set_url_scheme( FLAG_URLPATH, 'admin' ); ?>admin/js/selectize/selectize.min.js"></script>
	<base target="_self"/>
</head>
<body id="link">
<?php } else { ?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php _e( "Insert FlaGallery Album with one or more galleries", 'flash-album-gallery' ); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</title>
	<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php echo get_option( 'blog_charset' ); ?>"/>
	<script language="javascript" type="text/javascript" src="<?php echo set_url_scheme( get_option( 'siteurl' ), 'admin' ); ?>/wp-includes/js/jquery/jquery.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo set_url_scheme( get_option( 'siteurl' ), 'admin' ); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo set_url_scheme( FLAG_URLPATH, 'admin' ); ?>admin/js/tabs.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo set_url_scheme( FLAG_URLPATH, 'admin' ); ?>admin/tinymce/popup.css"/>
	<link rel="stylesheet" type="text/css" href="<?php echo set_url_scheme( FLAG_URLPATH, 'admin' ); ?>admin/js/selectize/selectize.css"/>
	<script language="javascript" type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo set_url_scheme( FLAG_URLPATH, 'admin' ); ?>admin/js/selectize/selectize.min.js"></script>
	<base target="_self"/>
</head>
<body id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';document.getElementById('galleries').focus();" style="display: none; font-size: 13px;">
<?php } ?>
<form name="FlAG" action="#">
	<div class="cptabs_wrapper">
		<ul id="tabs" class="tabs">
			<li class="selected"><a href="#" rel="gallery_panel"><span><?php _e( 'Galleries', 'flash-album-gallery' ); ?></span></a></li>
			<li><a href="#" rel="album_panel"><span><?php _e( 'Albums', 'flash-album-gallery' ); ?></span></a></li>
			<li id="sort_tab"><a href="#" rel="sort_panel"><span><?php _e( 'Sort', 'flash-album-gallery' ); ?></span></a></li>
			<li><a href="#" rel="custom_panel"><span><?php _e( 'Skin', 'flash-album-gallery' ); ?></span></a></li>
		</ul>

		<!-- gallery panel -->
		<div id="gallery_panel" class="panel cptab current">
			<table border="0" cellpadding="4" cellspacing="0">
				<tr>
					<td nowrap="nowrap" valign="middle" width="35%"><label for="galleryname"><?php _e( "Album Name", 'flash-album-gallery' ); ?>:<span style="color:red;"> *</span></label></td>
					<td valign="middle"><input id="galleryname" name="galleryname" value="Gallery" type="text"/></td>
				</tr>
				<tr>
					<td nowrap="nowrap" valign="middle"><label for="galleries"><?php _e( "Select galleries", 'flash-album-gallery' ); ?>:</label><br/>
						<small><?php _e( "(album categories)", 'flash-album-gallery' ); ?></small>
					</td>
					<td><select id="galleries" name="galleries" size="6" multiple="multiple" placeholder="<?php _e( "Leave blank for all galleries", 'flash-album-gallery' ); ?>">
						<option value=""><?php _e( "Leave blank for all galleries", 'flash-album-gallery' ); ?></option>
						<?php
						$gallerylist = $flagdb->find_all_galleries( $flag->options['albSort'], $flag->options['albSortDir'] );
						if ( is_array( $gallerylist ) ) {
							foreach ( $gallerylist as $gallery ) {
								$name = ( empty( $gallery->title ) ) ? $gallery->name : esc_html( stripslashes( $gallery->title ) );
								if ( $flag->options['albSort'] == 'gid' ) {
									$name = $gallery->gid . ' - ' . $name;
								}
								if ( $flag->options['albSort'] == 'title' ) {
									$name = $name . ' (' . $gallery->gid . ')';
								}
								echo '<option value="' . $gallery->gid . '" >' . $name . '</option>' . "\n";
							}
						}
						?>
					</select></td>
				</tr>
			</table>
		</div>
		<!-- /gallery panel -->
		<!-- album panel -->
		<div id="album_panel" class="panel cptab">
			<table border="0" cellpadding="4" cellspacing="0">
				<tr>
					<td nowrap="nowrap" valign="middle" width="35%"><label for="album"><?php _e( "Select album", 'flash-album-gallery' ); ?>:</label></td>
					<td><select id="album" name="album">
						<option value="galleries"><?php _e( "Choose Album or skip to use Galleries", 'flash-album-gallery' ); ?></option>
						<?php
						$albumlist = $flagdb->find_all_albums( 'id', 'ASC' );
						if ( is_array( $albumlist ) ) {
							foreach ( $albumlist as $album ) {
								$name = $album->name;
								echo '<option value="' . $album->id . '" >' . esc_html( $name ) . '</option>' . "\n";
							}
						}
						?>
					</select></td>
				</tr>
			</table>
		</div>
		<!-- /album panel -->
		<!-- skin panel -->
		<div id="custom_panel" class="panel cptab">
			<table border="0" cellpadding="4" cellspacing="0">
				<tr>
					<td nowrap="nowrap" valign="middle" width="35%"><label for="skinname"><?php _e( "Choose skin", 'flash-album-gallery' ); ?>:</label></td>
					<td valign="middle"><select id="skinname" name="skinname">
						<option value="" selected="selected"><?php _e( "choose custom skin", 'flash-album-gallery' ); ?></option>
						<?php
						foreach ( (array) $all_skins as $skin_file => $skin_data ) {
							echo '<option value="' . dirname( $skin_file ) . '">' . $skin_data['Name'] . '</option>' . "\n";
						}
						?>
					</select></td>
				</tr>
				<tr>
					<td nowrap="nowrap" valign="middle" width="35%"><label for="skinpreset"><?php _e( 'Choose preset', 'flash-album-gallery' ); ?>:</label></td>
					<td valign="middle"><select id="skinpreset" name="skinpreset">
						<option class="default-preset" value="" selected="selected"><?php _e( 'default', 'flash-album-gallery' ); ?></option>
						<?php
						foreach ( (array) $all_skins as $skin_file => $skin_data ) {
							$skin_slug = dirname( $skin_file );
							if ( empty( $flag->options["{$skin_slug}_options"]['presets'] ) ) {
								continue;
							}
							foreach ( $flag->options["{$skin_slug}_options"]['presets'] as $preset_name => $settings ) {
								echo '<option class="' . esc_attr( $skin_slug ) . '" value="' . esc_attr( $preset_name ) . '">' . esc_html( $preset_name ) . '</option>' . "\n";
							}
						}
						?>
					</select></td>
				</tr>
				<tr>
					<td valign="top"><label><?php _e( "Skin size", 'flash-album-gallery' ); ?>:</label><br/><span style="font-size:9px">(<?php _e( "blank for default", 'flash-album-gallery' ); ?>)</span></td>
					<td valign="top"><?php _e( "width", 'flash-album-gallery' ); ?>: <input id="gallerywidth" type="text" name="gallerywidth" style="width: 50px"/></td>
				</tr>
				<tr>
					<td valign="top"><label><?php _e( "Skin align", 'flash-album-gallery' ); ?>:</label></td>
					<td valign="top"><select id="skinalign" name="skinalign">
						<option value="" selected="selected"><?php _e( "default", 'flash-album-gallery' ); ?></option>
						<option value="left"><?php _e( "align left", 'flash-album-gallery' ); ?></option>
						<option value="center"><?php _e( "align center", 'flash-album-gallery' ); ?></option>
						<option value="right"><?php _e( "align right", 'flash-album-gallery' ); ?></option>
					</select></td>
				</tr>
			</table>
		</div>
		<!-- /custom panel -->
		<!-- sort panel -->
		<div id="sort_panel" class="panel cptab">
			<table border="0" cellpadding="4" cellspacing="0">
				<tr>
					<td nowrap="nowrap" valign="middle" width="35%"><label for="galorderby"><?php _e( "Order galleries by", 'flash-album-gallery' ); ?>:</label></td>
					<td valign="middle"><select id="galorderby" name="galorderby">
						<option value="" selected="selected"><?php _e( "Gallery IDs (default)", 'flash-album-gallery' ); ?></option>
						<option value="title"><?php _e( "Gallery Title", 'flash-album-gallery' ); ?></option>
						<!-- <option value="sortorder"><?php _e( "User Defined", 'flash-album-gallery' ); ?></option> -->
						<option value="rand"><?php _e( "Randomly", 'flash-album-gallery' ); ?></option>
					</select></td>
				</tr>
				<tr>
					<td nowrap="nowrap" valign="middle"><label for="galorder"><?php _e( "Order", 'flash-album-gallery' ); ?>:</label></td>
					<td valign="middle"><select id="galorder" name="galorder">
						<option value="" selected="selected"><?php _e( "DESC (default)", 'flash-album-gallery' ); ?></option>
						<option value="ASC"><?php _e( "ASC", 'flash-album-gallery' ); ?></option>
					</select></td>
				</tr>
				<tr>
					<td nowrap="nowrap" valign="middle"><label for="galexclude"><?php _e( "Exclude Gallery", 'flash-album-gallery' ); ?>:</label></td>
					<td valign="middle"><input id="galexclude" name="galexclude" type="text"/></td>
				</tr>
			</table>
		</div>
		<!-- /sort panel -->

	</div>
	<div class="mceActionPanel">
		<div style="float: right">
			<input type="button" id="insert" name="insert" value="<?php _e( "Insert", 'flash-album-gallery' ); ?>"/>
		</div>
	</div>
	<script type="text/javascript">
      /* <![CDATA[ */
      var cptabs = new ddtabcontent('tabs');
      cptabs.setpersist(false);
      cptabs.setselectedClassTarget('linkparent');
      cptabs.init();
      /* ]]> */
	</script>
	<script type="text/javascript">
      /* <![CDATA[ */

      jQuery('#galleries').selectize({
        plugins: ['drag_drop', 'remove_button'],
        create: false,
        hideSelected: true,
        onChange: function(value) {
          if(value) {
            jQuery('#sort_tab').css('display', 'none');
          }
          else {
            jQuery('#sort_tab').css('display', 'block');
          }
        }
      });
      jQuery('#album').selectize({
        create: false,
        hideSelected: false,
        onChange: function(value) {
          if(value && (value != 'galleries')) {
            jQuery('#sort_tab').css('display', 'none');
          }
          else {
            if(!jQuery('#galleries').val()) {
              jQuery('#sort_tab').css('display', 'block');
            }
          }
        }
      });

      jQuery('#skinname').on('change', function(){
        var skin = jQuery(this).val();
        jQuery('#skinpreset').val('').find('option').removeAttr('style').filter('.'+ skin).show();
      });

      var win = window.dialogArguments || opener || parent || top;
      jQuery('#insert').click(function() {
        var tagtext;
        var galleryname = document.getElementById('galleryname').value;
        var gallerywidth = document.getElementById('gallerywidth').value;
        var galorderby = document.getElementById('galorderby').value;
        var galorder = document.getElementById('galorder').value;
        var galexclude = document.getElementById('galexclude').value;
        var skinname = document.getElementById('skinname').value;
        var skinpreset = document.getElementById('skinpreset').value;
        var skinalign = document.getElementById('skinalign').value;
        var gallery = document.getElementById('galleries');
        var album = jQuery('#album').val();
        var len = gallery.length;
        var galleryid = '';
        var gallerysize = '';
        if(!album || 'galleries' == album) {
          album = '';
          galleryid = ' gid=';
          if(len) {
            for(var i = 0; i < len; i++) {
              if(gallery.options[i].selected) {
                if(galleryid === ' gid=') {
                  galleryid = galleryid + gallery.options[i].value;
                }
                else {
                  galleryid = galleryid + ',' + gallery.options[i].value;
                }
              }
            }
          }
          else {
            galleryid = galleryid + 'all';
          }
        }
        else {
          galleryname = jQuery('#album option:selected').text();
          album = ' album=' + album;
        }
        if(galleryname == 'Gallery') {
          galleryname = '';
        }
        if(galleryname.indexOf(' ') >= 0) {
          galleryname = '\'' + galleryname + '\'';
        }
        if(galleryname) {
          galleryname = ' name=' + galleryname;
        }
        if(gallerywidth) {
          gallerysize = ' w=' + gallerywidth;
        }
        else {
          gallerysize = '';
        }

        if(galleryid == ' gid=all') {
          if(galorderby) {
            galorderby = ' orderby=' + galorderby;
          }
          if(galorder) {
            galorder = ' order=' + galorder;
          }
          if(galexclude) {
            galexclude = ' exclude=' + galexclude;
          }
        }
        else {
          galorderby = '';
          galorder = '';
          galexclude = '';
        }
        if(skinname) {
          skinname = ' skin=' + skinname;
        }
        else {
          skinname = '';
        }
        if(skinpreset) {
          if(skinpreset.indexOf(' ') >= 0) {
            skinpreset = ' preset=\'' + skinpreset + '\'';
          } else {
            skinpreset = ' preset=' + skinpreset;
          }
        }
        else {
          skinpreset = '';
        }
        if(skinalign) {
          skinalign = ' align=' + skinalign;
        }
        else {
          skinalign = '';
        }

        if(galleryid || album) {
          tagtext = '[flagallery' + galleryid + album + galleryname + gallerysize + galorderby + galorder + galexclude + skinname + skinpreset + skinalign + ']';
          win.send_to_editor(tagtext);
          win.bind_resize();
			<?php if(! empty( $riched )) { ?>
          tinyMCEPopup.close();
			<?php } ?>
        }
        else {
          alert('Choose at least one gallery!');
        }
      });
      jQuery(window).unload(function() {
        jQuery(win).unbind('resize');
      });
      /* ]]> */
	</script>
</form>
</body>
</html>
