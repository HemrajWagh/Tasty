<?php
require_once( dirname(__FILE__) . '/flag-config.php');
global $post;
$flag_custom = get_post_custom($post->ID);
$scode = $flag_custom["mb_scode"][0];
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<title><?php $title = wp_title('', false, 'left'); echo strip_tags($title); echo ' - ' . get_bloginfo('name') ?></title>
<style type="text/css">
html, body { margin: 0; padding: 0; height:100%; }
<?php if(isset($flag_custom['mb_bg_link'][0]) && !empty($flag_custom['mb_bg_link'][0])) {
    $size = isset($flag_custom['mb_bg_size'][0])? $flag_custom['mb_bg_size'][0] : 'auto';
    ?>
body { background-image: url(<?php echo $flag_custom['mb_bg_link'][0]; ?>); background-position: <?php echo $flag_custom['mb_bg_pos'][0]; ?>; background-repeat: <?php echo $flag_custom['mb_bg_repeat'][0]; ?>; background-size: <?php echo $size; ?>; }
<?php } ?>
body > .flagallery-wrapper { height:100%; display:flex; flex-direction:column; }
body > .flagallery-wrapper > div { flex:1 1 auto; }
body > .flagallery-wrapper > div.flagallery-header { flex:0 0 auto; background-color: #0f0f0f; color: #f1f1f1; padding: 5px 0 5px 30px; font-family: "Arial", "Verdana", serif; }
.flagallery-header-title { display: inline-block; font-size: 16px; vertical-align: bottom; margin-top: 3px; }
.flagallery-menu { float: right; margin: 0 30px 0 0; padding: 0; }
.flagallery-menu .flagallery-menu-items { float: right; }
.flagallery-menu .flagallery-menu-items a,
.flagallery-menu .flagallery-menu-items a:visited { display: inline-block; color: #ffffff; background: #444444; border: none; padding: 2px 7px; min-width: 2.1em; opacity: 0.9; box-shadow: 0 2px 0 0 rgba(0, 0, 0, 0.2); outline: none; text-align: center; box-sizing: border-box; text-decoration: none; }
.flagallery-menu .flagallery-menu-items a i span { font-style: normal; }
.flagallery-menu .flagallery-menu-items a:hover { color: #eeeeee; }
.flagallery-menu .flagallery-menu-items a:active { position: relative; top: 2px; box-shadow: none; color: #e2e2e2; outline: none; }
body > .flagallery-wrapper > div.flagallery { overflow: auto; overflow-x:hidden; }
</style>
	<?php
    wp_enqueue_scripts();
    wp_print_styles('flagallery');

    wp_print_scripts(array('jquery', 'flagscript'));

    ob_start();
    wp_head();
    wp_footer();
    ob_end_clean();

	?>
</head>
<body id="fullwindow" class="flagallery-template">
<div id="page" class="flagallery-wrapper">
    <?php if(!isset($_GET['iframe'])){
        global $gmedia;

        $home_text = isset($flag_custom["mb_button_home"][0]) && !empty($flag_custom["mb_button_home"][0])? $flag_custom["mb_button_home"][0] :  __('Home', 'flash-album-gallery');
        $backlink_text = isset($flag_custom["mb_button"][0]) && !empty($flag_custom["mb_button"][0])? $flag_custom["mb_button"][0] :  __('Go Back', 'flash-album-gallery');
        $backlink = isset($flag_custom["mb_button_link"][0]) && !empty($flag_custom["mb_button_link"][0])? $flag_custom['mb_button_link'][0] : '';
        if(!$backlink || $backlink == 'http://'){
            $backlink = isset($_SERVER["HTTP_REFERER"])? $_SERVER["HTTP_REFERER"] : '';
        }

        ?>
        <div class="flagallery-header">
            <div class="flagallery-menu">
                <?php $home_url = home_url(); ?>
                <div class="flagallery-menu-items">
                    <a href="<?php echo $home_url; ?>" class="btn btn-homepage" title="<?php esc_attr_e(get_bloginfo('name')); ?>"><i class="fa fa-home"><span><?php echo esc_html( $home_text ); ?></span></i></a>
                    <?php if ($backlink && ($home_url != $backlink)) {
                        echo "<a href='{$backlink}' class='btn btn-goback'><i class='fa fa-arrow-left'><span>" . $backlink_text . "</span></i></a>";
                    } ?>
                </div>
            </div>
            <div class="flagallery-header-title"><?php $title = wp_title( '', false);
                echo get_bloginfo('name'); echo strip_tags($title); ?></div>
        </div>
    <?php } ?>
<?php
if ( post_password_required( $post ) ) {
	the_content();
} else {
	echo do_shortcode($scode);
} ?>
</div>
<?php
do_action('flag_footer_scripts');
$flag_options = get_option('flag_options');
if(isset($flag_options['gp_jscode'])){ echo stripslashes($flag_options['gp_jscode']); }
?>

</body>
</html>