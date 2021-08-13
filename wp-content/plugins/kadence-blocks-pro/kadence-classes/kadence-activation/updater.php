<?php
/**
 * Class file to check for active license
 *
 * @package Kadence Plugins
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load activation API
$current_theme = wp_get_theme();
$current_theme_name = $current_theme->get( 'Name' );
$current_theme_template = $current_theme->get( 'Template' );
if ( 'Pinnacle Premium' == $current_theme_name || 'pinnacle_premium' == $current_theme_template || 'Ascend - Premium' == $current_theme_name || 'ascend_premium' == $current_theme_template || 'Virtue - Premium' == $current_theme_name || 'virtue_premium' == $current_theme_template ) {
	$kadence_gutenberg_pro_updater = new PluginUpdateChecker_2_0( 'https://kernl.us/api/v1/updates/5bac0345bfd05d0705fbddec/', KBP_PATH . 'kadence-blocks-pro.php', 'kadence-blocks-pro', 1 );
} else {
	require_once KBP_PATH . 'kadence-classes/kadence-activation/class-kadence-plugin-api-manager.php';
	if ( is_multisite() ) {
		$show_local_activation = apply_filters( 'kadence_activation_individual_multisites', false );
		if ( $show_local_activation ) {
			if ( 'Activated' === get_option( 'kadence_gutenberg_pro_activation' ) ) {
				$kadence_gutenberg_pro_updater = new PluginUpdateChecker_2_0( 'https://kernl.us/api/v1/updates/5bac0345bfd05d0705fbddec/', KBP_PATH . 'kadence-blocks-pro.php', 'kadence-blocks-pro', 1 );
			}
		} else {
			if ( 'Activated' === get_site_option( 'kadence_gutenberg_pro_activation' ) ) {
				$kadence_gutenberg_pro_updater = new PluginUpdateChecker_2_0( 'https://kernl.us/api/v1/updates/5bac0345bfd05d0705fbddec/', KBP_PATH . 'kadence-blocks-pro.php', 'kadence-blocks-pro', 1 );
			}
		}
	} elseif ( 'Activated' === get_option( 'kadence_gutenberg_pro_activation' ) ) {
		$kadence_gutenberg_pro_updater = new PluginUpdateChecker_2_0( 'https://kernl.us/api/v1/updates/5bac0345bfd05d0705fbddec/', KBP_PATH . 'kadence-blocks-pro.php', 'kadence-blocks-pro', 1 );
	}
}
