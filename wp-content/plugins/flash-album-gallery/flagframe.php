<?php if(file_exists(dirname(__FILE__) . '/flag-config.php')){
	require_once( dirname(__FILE__) . '/flag-config.php');
} else if(file_exists(dirname(__FILE__) . '/wp-load.php')){
	require_once( dirname(__FILE__) . '/wp-load.php');
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head profile="https://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<title><?php $title = wp_title('', false, 'left');
    echo strip_tags($title); echo ' - ' . get_bloginfo('name') ?></title>
    <style>
        html, body { height: 100%; margin: 0; padding: 0; }
        body > .flagallery-wrapper {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        body > .flagallery-wrapper > div.flagallery {
            overflow: auto;
            overflow-x: hidden;
        }
        body > .flagallery-wrapper > div {
            flex: 1 1 auto;
        }
    </style>
</head>
<body id="fullwindow" class="flagallery-template">
<div id="page" class="flagallery-wrapper">
<?php
	wp_enqueue_scripts();
	wp_print_scripts(array('jquery'));
?>
<?php $flag_options = get_option('flag_options');
if(isset($_GET['l'])) {
	$linkto = intval($_GET['l']);
} else {
	$posts = get_posts(array("showposts" => 1));
	$linkto = $posts[0]->ID;
}
if(isset($_GET['i'])) {
	$skin = '';
	if(isset($_GET['f']) && false === strpos($_GET['f'], '..') ){
		$skin = sanitize_flagname($_GET['f']);
	}

	$preset = '';
	if(isset($_GET['pr'])){
		$preset = sanitize_text_field($_GET['pr']);
	}

	$gids = $_GET['i'];
	if($gids=='all') {
		/** @var $flagdb flagdb */
		global $flagdb;
		$gids='';
		$orderby=$flag_options['albSort'];
		$order=$flag_options['albSortDir'];
	  $gallerylist = $flagdb->find_all_galleries($orderby, $order);
	  if(is_array($gallerylist)) {
			foreach($gallerylist as $gallery) {
				$gids.='_'.$gallery->gid;
			}
			$gids = ltrim($gids,'_');
		}
	} else {
		$gids = explode('_',$gids);
		$mapping = array_map('intval', $gids);
		$gids = implode('_',$mapping);
	}

	if($gids){

		echo flagShowFlashAlbum($gids, $name='', $width='100%', $skin, $preset, $linkto); ?>

	<?php
		do_action('flag_footer_scripts');
	?>

<?php }
} ?>

<?php
if(isset($_GET['m'])) {
	$file = sanitize_flagname($_GET['m']);
	$playlistpath = $flag_options['galleryPath'].'playlists/'.$file.'.xml';
	if(file_exists($playlistpath))
		echo flagShowMPlayer($file, $width='');
	else
		_e("Can't find playlist");
}
?>
<?php
if(isset($_GET['v'])) {
	$height = isset($_GET['h'])? intval($_GET['h']) : '';
	$width = isset($_GET['w'])? '100%' : '';
	$file = sanitize_flagname($_GET['v']);
	$playlistpath = $flag_options['galleryPath'].'playlists/video/'.$file.'.xml';
	if(file_exists($playlistpath))
		echo flagShowVPlayer($file, $width);
	else
		_e("Can't find playlist");
}
?>
<?php
if(isset($_GET['mv'])) {
	$height = isset($_GET['h'])? intval($_GET['h']) : '';
	$width = '100%';
	$mv = intval($_GET['mv']);
	echo flagShowVmPlayer($mv, $width, $height, $autoplay='true');
}
?>
<?php
if(isset($_GET['b'])) {
	$file = sanitize_flagname($_GET['b']);
	$playlistpath = $flag_options['galleryPath'].'playlists/banner/'.$file.'.xml';
	if(file_exists($playlistpath))
		echo flagShowBanner($file, $width='');
	else
		_e("Can't find playlist");
}
?>
</div>
</body>
</html>