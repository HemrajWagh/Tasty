<?php if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// check for correct capability
if ( ! is_user_logged_in() ) {
	die( '-1' );
}

global $flagdb;

$sort_gall_by  = 'title';
$sort_gall_dir = 'ASC';

$gallerylist = $flagdb->find_all_galleries( $sort_gall_by, $sort_gall_dir );

require_once( dirname( __FILE__ ) . '/get_skin.php' );
$i_skins = get_skins();
?>
<link rel="stylesheet" type="text/css" href="<?php echo set_url_scheme( FLAG_URLPATH, 'admin' ); ?>admin/js/selectize/selectize.css"/>
<script language="javascript" type="text/javascript" src="<?php echo set_url_scheme( FLAG_URLPATH, 'admin' ); ?>admin/js/selectize/selectize.min.js"></script>
<script type="text/javascript">/*<![CDATA[*/
	jQuery(document).ready(function() {
		var selected_galleries = jQuery('#mb_items_array').val();
		var galleries = 'gid=' + (selected_galleries ? selected_galleries.join(',') : 'all');
		var galorderby = jQuery('#mb_galorderby').val();
		var galorder = jQuery('#mb_galorder').val();
		var galexclude = jQuery('#mb_galexclude').val();
		if (galexclude) {
			galexclude = galexclude.join(',');
		}
		var skin = jQuery('#mb_skinname option:selected').val();
		if (skin) {
			var skin_preset = skin.split(':');
			skin = ' skin=' + skin_preset[0];
			if (skin_preset[1]) {
				skin += ' preset=\'' + skin_preset[1] + '\'';
			}
		}
		else {
			skin = '';
		}
		if ('gid=all' == galleries) {
			if (galorderby) {
				galorderby = ' orderby=' + galorderby;
			}
			else {
				galorderby = '';
			}
			if (galorder) {
				galorder = ' order=' + galorder;
			}
			else {
				galorder = '';
			}
			if (galexclude) {
				galexclude = ' exclude=' + galexclude;
			}
			else {
				galexclude = '';
			}
		}
		else {
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
				if (value) {
					jQuery('.sort_tab').css('display', 'none');
				}
				else {
					jQuery('.sort_tab').css('display', '');
				}
				selected_galleries = jQuery('#mb_items_array').val();
				galleries = 'gid=' + (selected_galleries ? selected_galleries.join(',') : 'all');
				if (selected_galleries) {
					short_code(galleries, skin, '', '', '');
				}
				else {
					short_code(galleries, skin, galorderby, galorder, galexclude);
				}
			}
		});
		jQuery('#mb_skinname').change(function() {
			skin = jQuery(this).val();
			if (skin) {
				var skin_preset = skin.split(':');
				skin = ' skin=' + skin_preset[0];
				if (skin_preset[1]) {
					skin += ' preset=\'' + skin_preset[1] + '\'';
				}
			}
			else {
				skin = '';
			}
			short_code(galleries, skin, galorderby, galorder, galexclude);
		});
		jQuery('#mb_galorderby').change(function() {
			galorderby = jQuery(this).val();
			if (galorderby) {
				galorderby = ' orderby=' + galorderby;
			}
			else {
				galorderby = '';
			}
			short_code(galleries, skin, galorderby, galorder, galexclude);
		});
		jQuery('#mb_galorder').change(function() {
			galorder = jQuery(this).val();
			if (galorder) {
				galorder = ' order=' + galorder;
			}
			else {
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
				if (excluded_galleries) {
					galexclude = ' exclude=' + excluded_galleries.join(',');
				}
				else {
					galexclude = '';
				}
				short_code(galleries, skin, galorderby, galorder, galexclude);
			}
		});
	});

	function short_code(galleries, skin, galorderby, galorder, galexclude) {
		jQuery('#mb_scode').val('[flagallery ' + galleries + ' w=100%' + skin + galorderby + galorder + galexclude + ']');
	}

	/*]]>*/</script>
<div class="flag-wrap" style="margin-top:40px;" id="generator1">
	<h2><?php _e( 'Shortcode Generator', 'flash-album-gallery' ); ?></h2>
	<table border="0" cellpadding="4" cellspacing="0" style="width: 90%;">
		<tr>
			<td nowrap="nowrap" valign="middle"><label for="mb_items_array"><?php _e( "Select galleries", 'flash-album-gallery' ); ?>:</label></td>
			<td style="width: 100%;">
				<div style="max-width: 600px; width: 98%;"><select id="mb_items_array" size="6" multiple="multiple" placeholder="<?php _e( "Leave blank for all galleries", 'flash-album-gallery' ); ?>">
						<option value=""><?php _e( "Leave blank for all galleries", 'flash-album-gallery' ); ?></option>
						<?php
						if ( is_array( $gallerylist ) ) {
							foreach ( $gallerylist as $gallery ) {
								$name = ( empty( $gallery->title ) ) ? $gallery->name : esc_html( stripslashes( $gallery->title ) );
								$name = $name . ' (#' . $gallery->gid . ')';
								echo '<option value="' . $gallery->gid . '">' . $name . '</option>' . "\n";
							}
						}
						?>
					</select></div>
			</td>
		</tr>
		<tr class="sort_tab">
			<td></td>
			<td>
				<div style="margin: -5px 0 7px"><i><?php _e( 'Drag and drop selected galleries in the field above to sort order them.', 'flash-album-gallery' ); ?></i></div>
			</td>
		</tr>
		<tr class="sort_tab">
			<td nowrap="nowrap" valign="middle"><label for="mb_galorderby"><?php _e( "Order galleries by", 'flash-album-gallery' ); ?>:</label></td>
			<td valign="middle"><select style="max-width: 600px; width: 98%;" id="mb_galorderby">
					<option value=""><?php _e( "Gallery IDs (default)", 'flash-album-gallery' ); ?></option>
					<option value="title"><?php _e( "Gallery Title", 'flash-album-gallery' ); ?></option>
					<option value="rand"><?php _e( "Randomly", 'flash-album-gallery' ); ?></option>
				</select></td>
		</tr>
		<tr class="sort_tab">
			<td nowrap="nowrap" valign="middle"><label for="mb_galorder"><?php _e( "Order", 'flash-album-gallery' ); ?>:</label></td>
			<td valign="middle"><select style="max-width: 600px; width: 98%;" id="mb_galorder">
					<option value="" selected="selected"><?php _e( "DESC (default)", 'flash-album-gallery' ); ?></option>
					<option value="ASC"><?php _e( "ASC", 'flash-album-gallery' ); ?></option>
				</select></td>
		</tr>
		<tr class="sort_tab">
			<td nowrap="nowrap" valign="middle"><label for="mb_galexclude"><?php _e( "Exclude galleries", 'flash-album-gallery' ); ?>:</label></td>
			<td valign="middle">
				<div style="max-width: 600px; width: 98%;"><select id="mb_galexclude" size="6" multiple="multiple">
						<option value=""></option>
						<?php
						if ( is_array( $gallerylist ) ) {
							foreach ( $gallerylist as $gallery ) {
								$name = ( empty( $gallery->title ) ) ? $gallery->name : esc_html( stripslashes( $gallery->title ) );
								$name = $name . ' (#' . $gallery->gid . ')';
								echo '<option value="' . $gallery->gid . '">' . $name . '</option>' . "\n";
							}
						}
						?>
					</select></div>
			</td>
		</tr>
		<tr>
			<td nowrap="nowrap" valign="middle"><label for="mb_skinname"><?php _e( "Choose skin", 'flash-album-gallery' ); ?>:</label></td>
			<td valign="top">
				<div><select style="max-width: 600px; width: 98%;" id="mb_skinname">
						<option value=""><?php _e( "skin active by default", 'flash-album-gallery' ); ?></option>
						<?php
						foreach ( (array) $i_skins as $skin_file => $skin_data ) {
							echo '<option value="' . dirname( $skin_file ) . '">' . $skin_data['Name'] . '</option>' . "\n";

							$act_skin         = dirname( $skin_file );
							$skin_options_key = "{$act_skin}_options";
							if ( ! empty( $flag->options[ $skin_options_key ]['presets'] ) ) {
								foreach ( $flag->options[ $skin_options_key ]['presets'] as $preset_name => $preset_settings ) {
									$val = dirname( $skin_file ) . ':' . esc_attr( $preset_name );
									echo '<option value="' . $val . '">' . $skin_data['Name'] . ': ' . esc_html( $preset_name ) . '</option>' . "\n";
								}
							}
						}
						?>
					</select></div>
			</td>
		</tr>
		<tr>
			<td nowrap="nowrap" valign="middle"><label for="mb_skinname"><?php _e( "SHORTCODE", 'flash-album-gallery' ); ?>:</label></td>
			<td valign="top"><input id="mb_scode" type="text" style="max-width: 600px; width: 98%;" value="" readonly/></td>
		</tr>
	</table>
</div>

