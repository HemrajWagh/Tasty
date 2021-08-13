<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

/**
 * flag_admin_overview()
 *
 * Add the admin overview in wp2.7 style
 * @return mixed content
 */
function flag_admin_overview()  {
	global $flag;
    if ( ! ( is_plugin_active( 'woowgallery/woowgallery.php' ) || ! empty( $flag->options['hide_woow'] ) ) ){
        ?>
        <div class="promote-woowbox" style="padding-top:10px"><a href="https://bit.ly/flag-woowgallery" target="_blank"><img src="<?php echo plugins_url('/flash-album-gallery/admin/images/woowbox-promote.png'); ?>" alt="Try WoowGallery plugin" /></a></div>
        <?php
    }
	echo get_option('flag_plugin_error');
?>
<div class="flag-wrap">
	<h2 class="overview-title"><?php _e('Grand Flagallery Overview', 'flash-album-gallery'); echo ' v'.FLAGVERSION; ?></h2>
	<div id="flag-overview" class="metabox-holder">
		<div id="post-body" class="has-sidebar">
			<div class="has-sidebar-content">
					<?php do_meta_boxes('flag-overview', 'normal', null); ?>
			</div>
		</div>
		<div id="side-info-column" class="inner-sidebar" style="display:block; margin-left: -300px;">
				<?php do_meta_boxes('flag-overview', 'side', null); ?>
		</div>
	</div>
</div>

<?php
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
?>
<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready( function() {
		jQuery('#side-info-column #major-publishing-actions').appendTo('#dashboard_primary');
	});
	//]]>
</script>

<?php
}

/**
 * Show the server settings
 *
 * @return void
 */
function flag_overview_server() {
?>
<div id="dashboard_server_settings" class="dashboard-widget-holder wp_dashboard_empty">
	<div class="flag-dashboard-widget">
	  <?php if (IS_WPMU) {
	  	if (flagGallery::flag_wpmu_enable_function('wpmuQuotaCheck'))
			flag_SpaceManager::details();
		else {
			//TODO:WPMU message in WP2.5 style
			flag_SpaceManager::details();
		}
	  } else { ?>
		<div class="dashboard-widget-content">
     	<ul class="settings">
     		<?php get_serverinfo(); ?>
	  	</ul>
		</div>
	  <?php } ?>
  </div>
</div>
<?php
}

/**
 * Show the GD ibfos
 *
 * @return void
 */
function flag_overview_graphic_lib() {
?>
<div id="dashboard_server_settings" class="dashboard-widget-holder">
	<div class="flag-dashboard-widget">
	  	<div class="dashboard-widget-content">
	  		<ul class="settings">
			<?php flag_GD_info(); ?>
			</ul>
		</div>
    </div>
</div>
<?php
}

/**
 * Show the Setup Box and some info for FlaGallery
 *
 * @return void
 */
function flag_overview_setup(){
	global $flag;

	if (isset($_POST['resetdefault'])) {
		check_admin_referer('flag_uninstall');

		include_once ( dirname (__FILE__). '/flag_install.php');
		include_once( dirname (__FILE__). '/tuning.php');

		flag_default_options();
		flag_tune(true,true);
		$flag->define_constant();
		$flag->load_options();

		flagGallery::show_message(__('Reset all settings to default parameter','flash-album-gallery'));
	}
?>
		<div class="submitbox" id="submitpost">
			<div id="minor-publishing">
				<div id="misc-publishing-actions">
					<div class="misc-pub-section">
						<span id="plugin-home" class="icon">
							<strong><a href="https://mypgc.co/" style="text-decoration: none;"><?php _e('Plugin Home','flash-album-gallery'); ?></a></strong>
						</span>
					</div>
					<div class="misc-pub-section">
						<span id="plugin-comments" class="icon">
							<a href="https://codeasily.com/community/forum/flagallery-wordpress-plugin/" style="text-decoration: none;"><?php _e('Plugin Forum','flash-album-gallery'); ?></a>
						</span>
					</div>
					<div class="misc-pub-section">
						<span id="rate-plugin" class="icon">
							<a href="https://wordpress.org/support/view/plugin-reviews/flash-album-gallery" style="text-decoration: none;"><?php _e('Rate Plugin','flash-album-gallery'); ?></a>
						</span>
					</div>
					<div class="misc-pub-section curtime misc-pub-section-last">
						<span id="contact-me" class="icon">
							<a href="https://mypgc.co/contact/" style="text-decoration: none;"><?php _e('Contact Me','flash-album-gallery'); ?></a>
						</span>
					</div>
				</div>
			</div>
		</div>
	<?php if (!IS_WPMU || flag_wpmu_site_admin() ) : ?>
	<div id="major-publishing-actions">
	<form id="resetsettings" name="resetsettings" method="post">
		<?php wp_nonce_field('flag_uninstall'); ?>
			<div id="save-action" class="alignleft">
				<input class="button" id="save-post" type="submit" name="resetdefault" value="<?php _e('Reset settings', 'flash-album-gallery'); ?>" onclick="javascript:check=confirm('<?php _e('Reset all options to default settings ?\n\nChoose [Cancel] to Stop, [OK] to proceed.\n','flash-album-gallery'); ?>');if(check==false) return false;" />
			</div>
			<div id="preview-action" class="alignright">
				<input type="submit" name="uninstall" class="button delete" value="<?php _e('Uninstall plugin', 'flash-album-gallery'); ?>" onclick="javascript:check=confirm('<?php _e('You are about to Uninstall this plugin from WordPress.\nThis action is not reversible.\n\nChoose [Cancel] to Stop, [OK] to Uninstall.\n','flash-album-gallery'); ?>');if(check==false) return false;" />
			</div>
			<br class="clear" />
	</form>
	</div>
	<?php endif; ?>

<?php
}

/**
 * Show a summary of the used images
 *
 * @return void
 */
function flag_overview_right_now() {
	global $wpdb;
	$images    = intval( $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->flagpictures") );
	$galleries = intval( $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->flaggallery") );
?>

<div class="table table_content">
	<strong><?php _e('At a Glance', 'flash-album-gallery'); ?>:</strong>
	<table>
			<tr class="first">
				<td class="t"><?php echo _n( 'Image', 'Images', $images, 'flash-album-gallery' ); ?></td>
				<td>:</td>
				<td class="b"><a href="admin.php?page=flag-manage-gallery&tabs=1"><?php echo $images; ?></a></td>
			</tr>
			<tr>
				<td class="t"><?php echo _n( 'Gallery', 'Galleries', $galleries, 'flash-album-gallery' ); ?></td>
				<td>:</td>
				<td class="b"><a href="admin.php?page=flag-manage-gallery&tabs=0"><?php echo $galleries; ?></a></td>
			</tr>
	</table>
</div>
<div class="versions">
	<?php if(current_user_can('FlAG Upload images')): ?>
	<p>
		<?php _e('Here you can control your images and galleries', 'flash-album-gallery'); ?>
		&nbsp;
		<a class="button rbutton" href="admin.php?page=flag-manage-gallery&tabs=1"><strong><?php _e('Upload pictures', 'flash-album-gallery'); ?></strong></a>
	</p>
	<?php endif; ?>
	<span><?php
		$userlevel = '<span class="b">' . (current_user_can('manage_options') ? __('Gallery Administrator', 'flash-album-gallery') : __('Gallery Editor', 'flash-album-gallery')) . '</span>';
        printf(__('You currently have %s rights.', 'flash-album-gallery'), $userlevel);
    ?></span>
</div>
    <?php
}

add_meta_box('flag_dashboard_right_now', __('Welcome to FlAGallery !', 'flash-album-gallery'), 'flag_overview_right_now', 'flag-overview', 'normal', 'default');
add_meta_box('flag_server', __('Server Settings', 'flash-album-gallery'), 'flag_overview_server', 'flag-overview', 'normal', 'default');
add_meta_box('flag_gd_lib', __('Graphic Library', 'flash-album-gallery'), 'flag_overview_graphic_lib', 'flag-overview', 'normal', 'default');
add_meta_box('dashboard_primary', __('Setup Box', 'flash-album-gallery'), 'flag_overview_setup', 'flag-overview', 'side', 'core');

/**
 * Show GD Library version information
 *
 * @return void
 */
function flag_GD_info() {

	if(function_exists("gd_info")){
		$info = gd_info();
		$keys = array_keys($info);
		for($i=0; $i<count($keys); $i++) {
			if(is_bool($info[$keys[$i]]))
				echo "<li> " . $keys[$i] ." : <span>" . flag_GD_Support($info[$keys[$i]]) . "</span></li>\n";
			else
				echo "<li> " . $keys[$i] ." : <span>" . $info[$keys[$i]] . "</span></li>\n";
		}
	}
	else {
		echo '<h4>'.__('No GD support', 'flash-album-gallery').'!</h4>';
	}
}

/**
 * Return localized Yes or no
 *
 * @param bool $bool
 * @return string  'Yes' | 'No'
 */
function flag_GD_Support($bool){
	if($bool)
		return __('Yes', 'flash-album-gallery');
	else
		return __('No', 'flash-album-gallery');
}

/**
 * Show up some server infor's
 * @author GamerZ (http://www.lesterchan.net)
 *
 * @return void
 */
function get_serverinfo() {
	global $wpdb;
	// Get MYSQL Version
	$sqlversion = $wpdb->get_var("SELECT VERSION() AS version");
	// GET SQL Mode
	$mysqlinfo = $wpdb->get_results("SHOW VARIABLES LIKE 'sql_mode'");
	if (is_array($mysqlinfo)) $sql_mode = $mysqlinfo[0]->Value;
	if (empty($sql_mode)) $sql_mode = __('Not set', 'flash-album-gallery');
	// Get PHP allow_url_fopen
	if(ini_get('allow_url_fopen')) $allow_url_fopen = __('On', 'flash-album-gallery');
	else $allow_url_fopen = __('Off', 'flash-album-gallery');
	// Get PHP Max Upload Size
	if(ini_get('upload_max_filesize')) $upload_max = ini_get('upload_max_filesize');
	else $upload_max = __('N/A', 'flash-album-gallery');
	// Get PHP Output buffer Size
	if(ini_get('output_buffering')) $output_buffer = ini_get('output_buffering');
	else $output_buffer = __('N/A', 'flash-album-gallery');
	// Get PHP Max Post Size
	if(ini_get('post_max_size')) $post_max = ini_get('post_max_size');
	else $post_max = __('N/A', 'flash-album-gallery');
	// Get PHP Max execution time
	if(ini_get('max_execution_time')) $max_execute = ini_get('max_execution_time');
	else $max_execute = __('N/A', 'flash-album-gallery');
	// Get PHP Memory Limit
	if(ini_get('memory_limit')) $memory_limit = ini_get('memory_limit');
	else $memory_limit = __('N/A', 'flash-album-gallery');
	// Get actual memory_get_usage
	if (function_exists('memory_get_usage')) $memory_usage = round(memory_get_usage() / 1024 / 1024, 2) . __(' MByte', 'flash-album-gallery');
	else $memory_usage = __('N/A', 'flash-album-gallery');
	// required for EXIF read
	if (is_callable('exif_read_data')) $exif = __('Yes', 'flash-album-gallery'). " ( V" . substr(phpversion('exif'),0,4) . ")" ;
	else $exif = __('No', 'flash-album-gallery');
	// required for meta data
	if (is_callable('iptcparse')) $iptc = __('Yes', 'flash-album-gallery');
	else $iptc = __('No', 'flash-album-gallery');
	// required for meta data
	if (is_callable('xml_parser_create')) $xml = __('Yes', 'flash-album-gallery');
	else $xml = __('No', 'flash-album-gallery');
?>
	<li><?php _e('Operating System', 'flash-album-gallery'); ?> : <span><?php echo PHP_OS; ?>&nbsp;(<?php echo (PHP_INT_SIZE * 8); ?>&nbsp;Bit)</span></li>
	<li><?php _e('Server', 'flash-album-gallery'); ?> : <span><?php echo $_SERVER["SERVER_SOFTWARE"]; ?></span></li>
	<li><?php _e('Memory usage', 'flash-album-gallery'); ?> : <span><?php echo $memory_usage; ?></span></li>
	<li><?php _e('MYSQL Version', 'flash-album-gallery'); ?> : <span><?php echo $sqlversion; ?></span></li>
	<li><?php _e('SQL Mode', 'flash-album-gallery'); ?> : <span><?php echo $sql_mode; ?></span></li>
	<li><?php _e('PHP Version', 'flash-album-gallery'); ?> : <span><?php echo PHP_VERSION; ?></span></li>
	<li><?php _e('PHP Allow URL fopen', 'flash-album-gallery'); ?> : <span><?php echo $allow_url_fopen; ?></span></li>
	<li><?php _e('PHP Memory Limit', 'flash-album-gallery'); ?> : <span><?php echo $memory_limit; ?></span></li>
	<li><?php _e('PHP Max Upload Size', 'flash-album-gallery'); ?> : <span><?php echo $upload_max; ?></span></li>
	<li><?php _e('PHP Max Post Size', 'flash-album-gallery'); ?> : <span><?php echo $post_max; ?></span></li>
	<li><?php _e('PHP Output Buffer Size', 'flash-album-gallery'); ?> : <span><?php echo $output_buffer; ?></span></li>
	<li><?php _e('PHP Max Script Execute Time', 'flash-album-gallery'); ?> : <span><?php echo $max_execute; ?>s</span></li>
	<li><?php _e('PHP Exif support', 'flash-album-gallery'); ?> : <span><?php echo $exif; ?></span></li>
	<li><?php _e('PHP IPTC support', 'flash-album-gallery'); ?> : <span><?php echo $iptc; ?></span></li>
	<li><?php _e('PHP XML support', 'flash-album-gallery'); ?> : <span><?php echo $xml; ?></span></li>
<?php
}

/**
 * WPMU feature taken from Z-Space Upload Quotas
 * @author Dylan Reeve
 * @url http://dylan.wibble.net/
 *
 */
class flag_SpaceManager {

 	static function getQuota() {
		if (function_exists('get_space_allowed'))
			$quota = get_space_allowed();
		else
			$quota = get_site_option( "blog_upload_space" );

		return $quota;
	}

	static function details() {

		// take default seetings
		$settings = array(

			'remain'	=> array(
			'color_text'	=> 'white',
			'color_bar'		=> '#0D324F',
			'color_bg'		=> '#a0a0a0',
			'decimals'		=> 2,
			'unit'			=> 'm',
			'display'		=> true,
			'graph'			=> false
			),

			'used'		=> array(
			'color_text'	=> 'white',
			'color_bar'		=> '#0D324F',
			'color_bg'		=> '#a0a0a0',
			'decimals'		=> 2,
			'unit'			=> 'm',
			'display'		=> true,
			'graph'			=> true
			)
		);

		$quota = flag_SpaceManager::getQuota() * 1024 * 1024;
		$used = get_dirsize( constant( 'ABSPATH' ) . constant( 'UPLOADS' ) );
//		$used = get_dirsize( ABSPATH."wp-content/blogs.dir/".$blog_id."/files" );

		if ($used > $quota) $percentused = '100';
		else $percentused = ( $used / $quota ) * 100;

		$remaining = $quota - $used;
		$percentremain = 100 - $percentused;

		$out = '';
		$out .= '<div id="spaceused"> <h3>'.__('Storage Space','flash-album-gallery').'</h3>';

		if ($settings['used']['display']) {
			$out .= __('Upload Space Used:','flash-album-gallery') . "\n";
			$out .= flag_SpaceManager::buildGraph($settings['used'], $used,$quota,$percentused);
			$out .= "<br />";
		}

		if($settings['remain']['display']) {
			$out .= __('Upload Space Remaining:','flash-album-gallery') . "\n";
			$out .= flag_SpaceManager::buildGraph($settings['remain'], $remaining,$quota,$percentremain);

		}

		$out .= "</div>";

		echo $out;
	}

	static function buildGraph($settings, $size, $quota, $percent) {
		$color_bar = $settings['color_bar'];
		$color_bg = $settings['color_bg'];
		$color_text = $settings['color_text'];

		switch ($settings['unit']) {
			case "b":
				$unit = "B";
				break;

			case "k":
				$unit = "KB";
				$size = $size / 1024;
				$quota = $quota / 1024;
				break;

			case "g":   // Gigabytes, really?
				$unit = "GB";
				$size = $size / 1024 / 1024 / 1024;
				$quota = $quota / 1024 / 1024 / 1024;
				break;

			default:
				$unit = "MB";
				$size = $size / 1024 / 1024;
				$quota = $quota / 1024 / 1024;
				break;
		}

		$size = round($size, (int)$settings['decimals']);

		$pct = round(($size / $quota)*100);

		if ($settings['graph']) {
			//TODO:move style to CSS
			$out = '<div style="display: block; margin: 0; padding: 0; height: 15px; border: 1px inset; width: 100%; background-color: '.$color_bg.';">'."\n";
			$out .= '<div style="display: block; height: 15px; border: none; background-color: '.$color_bar.'; width: '.$pct.'%;">'."\n";
			$out .= '<div style="display: inline; position: relative; top: 0; left: 0; font-size: 10px; color: '.$color_text.'; font-weight: bold; padding-bottom: 2px; padding-left: 5px;">'."\n";
			$out .= $size.$unit;
			$out .= "</div>\n</div>\n</div>\n";
		} else {
			$out = "<strong>".$size.$unit." ( ".number_format($percent)."%)"."</strong><br />";
		}

		return $out;
	}

}

/**
 * get_phpinfo() - Extract all of the data from phpinfo into a nested array
 *
 * @author jon@sitewizard.ca
 * @return array
 */
function get_phpinfo() {

	ob_start();
	phpinfo();
	$phpinfo = array('phpinfo' => array());

	if ( preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER) )
	    foreach($matches as $match) {
	        if(strlen($match[1]))
	            $phpinfo[$match[1]] = array();
	        elseif(isset($match[3]))
	            $phpinfo[end(array_keys($phpinfo))][$match[2]] = isset($match[4]) ? array($match[3], $match[4]) : $match[3];
	        else
	            $phpinfo[end(array_keys($phpinfo))][] = $match[2];
	    }

	return $phpinfo;
}
