<?php

/*

Plugin Name:My Coffee
Plugin URI:https://
Description:this is my first plugin
Author:Hemraj
Author URI:
Version:1.0.0

*/
if(!defined("ABSPATH"))
	exit;
if(!defined("MY_COFFEE_PLUGIN_DIR_PATH"))
	define("MY_COFFEE_PLUGIN_DIR_PATH",plugin_dir_path(__FILE__));
if(!defined("MY_COFFEE_PLUGIN_URL"))
	define("MY_COFFEE_PLUGIN_URL",plugins_url()."/my-coffee");

// echo MY_COFFEE_PLUGIN_DIR_PATH;
// echo "<br/>";
// echo MY_COFFEE_PLUGIN_URL;
// die;

function my_coffee_include_assets(){

	wp_enqueue_style("bootstrap",MY_COFFEE_PLUGIN_URL."/assets/css/bootstrap.min.css"," ");
	wp_enqueue_style("datatable",MY_COFFEE_PLUGIN_URL."/assets/css/jquery.dataTables.min.css","");
	wp_enqueue_style("notifybar",MY_COFFEE_PLUGIN_URL."/assets/css/jquery.notifyBar.css"," ");
	wp_enqueue_style("style",MY_COFFEE_PLUGIN_URL."/assets/css/style.css"," ");

	// wp_enqueue_script('jquery');	
	wp_enqueue_script('jquery');
	wp_enqueue_script('bootstrap.bundle.min.js',MY_COFFEE_PLUGIN_URL.'/assets/js/bootstrap.bundle.min.js','',true);
	wp_enqueue_script('jquery.dataTables.min.js',MY_COFFEE_PLUGIN_URL.'/assets/js/jquery.dataTables.min.js','',true);
	wp_enqueue_script('jquery.notiyBar.js',MY_COFFEE_PLUGIN_URL.'/assets/js/jquery.notifyBar.js','',true);
	wp_enqueue_script('jquerG.validate.min.js',MY_COFFEE_PLUGIN_URL.'/assets/js/jquery.validate.min.js','',true);
	wp_enqueue_script('script.js',MY_COFFEE_PLUGIN_URL.'/assets/js/script.js','',true);
	wp_localize_script("script.js","mycoffeeajaxurl",admin_url("admin-ajax.php"));

}
add_action("init","my_coffee_include_assets");

?> 

