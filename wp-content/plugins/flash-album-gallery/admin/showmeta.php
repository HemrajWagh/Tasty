<?php

// look up for the path
require_once( dirname( dirname(__FILE__) ) . '/flag-config.php');

require_once(FLAG_ABSPATH . '/lib/meta.php');
require_once(FLAG_ABSPATH . '/lib/image.php');

if ( !is_user_logged_in() )
	die(__('Cheatin&#8217; uh?'));
	
if ( !current_user_can('FlAG Manage gallery') ) 
	die(__('Cheatin&#8217; uh?'));

global $wpdb;

$id = (int) $_GET['id'];
// let's get the meta data'
$meta = new flagMeta($id);
$dbdata = $meta->get_saved_meta();
$exifdata = $meta->get_EXIF();
$iptcdata = $meta->get_IPTC();
$xmpdata = $meta->get_XMP();

?>
	<!-- META DATA -->
	<fieldset class="options flag">
	<h3><?php _e('Meta Data','flash-album-gallery'); ?></h3>
	<?php if ($dbdata) { ?>
		<table id="the-list-x" width="100%" cellspacing="3" cellpadding="3">
			<thead>
				<tr>
					<th scope="col"><?php _e('Tag','flash-album-gallery'); ?></th>
					<th scope="col"><?php _e('Value','flash-album-gallery'); ?></th>
				</tr>
			</thead>
	<?php $class = '';
			foreach ($dbdata as $key => $value){
				if ( is_array($value) ) continue;
				$class = ( $class == 'class="alternate"' ) ? '' : 'class="alternate"';
				echo '<tr '.$class.'>	
						<td style="width:230px">'.$meta->i8n_name($key).'</td>
						<td>'.$value.'</td>
					</tr>';
			}
	?>
		</table>
	<?php  } else echo "<strong>" . __('No meta data saved','flash-album-gallery') . "</strong>"; ?>
	</fieldset>
	
	<!-- EXIF DATA -->
	<?php if ($exifdata) { ?>
	<fieldset class="options flag">
	<h3><?php _e('EXIF Data','flash-album-gallery'); ?></h3>
	<?php if ($exifdata) { ?>
		<table id="the-list-x" width="100%" cellspacing="3" cellpadding="3">
			<thead>
				<tr>
					<th scope="col"><?php _e('Tag','flash-album-gallery'); ?></th>
					<th scope="col"><?php _e('Value','flash-album-gallery'); ?></th>
				</tr>
			</thead>
	<?php 
			foreach ($exifdata as $key => $value){
				$class = ( $class == 'class="alternate"' ) ? '' : 'class="alternate"';
				echo '<tr '.$class.'>	
						<td style="width:230px">'.$meta->i8n_name($key).'</td>
						<td>'.$value.'</td>
					</tr>';
			}
	?>
		</table>
	<?php  } else echo "<strong>". __('No exif data','flash-album-gallery'). "</strong>"; ?>
	</fieldset>
	<?php  } ?>
	
	<!-- IPTC DATA -->
	<?php if ($iptcdata) { ?>
	<fieldset class="options flag">
	<h3><?php _e('IPTC Data','flash-album-gallery'); ?></h3>
		<table id="the-list-x" width="100%" cellspacing="3" cellpadding="3">
			<thead>
				<tr>
					<th scope="col"><?php _e('Tag','flash-album-gallery'); ?></th>
					<th scope="col"><?php _e('Value','flash-album-gallery'); ?></th>
				</tr>
			</thead>
	<?php 
			foreach ($iptcdata as $key => $value){
				$class = ( $class == 'class="alternate"' ) ? '' : 'class="alternate"';
				echo '<tr '.$class.'>	
						<td style="width:230px">'.$meta->i8n_name($key).'</td>
						<td>'.$value.'</td>
					</tr>';
			}
	?>
		</table>
	</fieldset>
	<?php  } ?>

	<!-- XMP DATA -->
	<?php if ($xmpdata) { ?>
	<fieldset class="options flag">
	<h3><?php _e('XMP Data','flash-album-gallery'); ?></h3>
		<table id="the-list-x" width="100%" cellspacing="3" cellpadding="3">
			<thead>
				<tr>
					<th scope="col"><?php _e('Tag','flash-album-gallery'); ?></th>
					<th scope="col"><?php _e('Value','flash-album-gallery'); ?></th>
				</tr>
			</thead>
	<?php 
			foreach ($xmpdata as $key => $value){
				$class = ( $class == 'class="alternate"' ) ? '' : 'class="alternate"';
				echo '<tr '.$class.'>	
						<td style="width:230px">'.$meta->i8n_name($key).'</td>
						<td>'.$value.'</td>
					</tr>';
			}
	?>
		</table>
	</fieldset>
	<?php  }