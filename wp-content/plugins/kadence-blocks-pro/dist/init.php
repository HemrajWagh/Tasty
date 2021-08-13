<?php
/**
 * Enqueue admin CSS/JS and edit width functions
 *
 * @since   1.0.0
 * @package Kadence Blocks Pro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Get all the registered image sizes along with their dimensions
 *
 * @global array $_wp_additional_image_sizes
 *
 * @link http://core.trac.wordpress.org/ticket/18947 Reference ticket
 *
 * @return array $image_sizes The image sizes
 */
function kadence_blocks_pro_get_all_image_sizes() {
    global $_wp_additional_image_sizes;

    $default_image_sizes = get_intermediate_image_sizes();

    foreach ( $default_image_sizes as $size ) {
        $image_sizes[ $size ][ 'width' ] = intval( get_option( "{$size}_size_w" ) );
        $image_sizes[ $size ][ 'height' ] = intval( get_option( "{$size}_size_h" ) );
        $image_sizes[ $size ][ 'crop' ] = get_option( "{$size}_crop" ) ? get_option( "{$size}_crop" ) : false;
    }

    if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) ) {
        $image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes );
    }

    return $image_sizes;
}

/**
 * Get all the registered image sizes along with their dimensions
 *
 * @global array $_wp_additional_image_sizes
 *
 * @link http://core.trac.wordpress.org/ticket/18947 Reference ticket
 *
 * @return array $image_sizes The image sizes
 */
function kadence_blocks_pro_get_all_image_sizes_array() {
	$image_sizes = kadence_blocks_pro_get_all_image_sizes();
	$image_sizes_array = array();
	foreach ( $image_sizes as $size_key => $size_item ) {
		$image_sizes_array[] = array(
			'value' => $size_key,
			'label' => $size_key . ' (' . $size_item['width'] . 'x' . $size_item['height'] . ')',
		);
	}

    return $image_sizes_array;
}
/**
 * Enqueue Gutenberg block assets for backend editor.
 *
 * `wp-blocks`: includes block type registration and related functions.
 * `wp-element`: includes the WordPress Element abstraction for describing the structure of your blocks.
 * `wp-i18n`: To internationalize the block's text.
 *
 * @since 1.0.0
 */
function kadence_blocks_pro_editor_assets() {
	// Scripts.
	wp_register_script( 'kadence-blocks-pro-js', KBP_URL . 'dist/blocks.build.js', array( 'wp-api-fetch', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-api', 'wp-edit-post', 'wp-dom-ready', 'kadence-blocks-js' ), KBP_VERSION, true );
	wp_localize_script(
		'kadence-blocks-pro-js',
		'ktGbToolsData',
		array(
			'restBase' => esc_url_raw( get_rest_url() ),
			'postSelectEndpoint' => '/rbmh/v1/post-select',
			'postTypes' => kadence_blocks_pro_get_post_types(),
			'taxonomies' => kadence_blocks_pro_get_taxonomies(),
			'isKadenceT'  => class_exists( 'Kadence\Theme' ),
			'wcIsActive' => class_exists( 'Woocommerce' ),
			'imageSizes' => kadence_blocks_pro_get_all_image_sizes_array(),
			'wcProductCarouselPlaceholder' => ( function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src() : '' ),
		)
	);

	// Styles.
	wp_register_style( 'kadence-blocks-pro-editor-css', KBP_URL . 'dist/blocks.editor.build.css', array( 'wp-edit-blocks' ), KBP_VERSION );

	if ( function_exists( 'wp_set_script_translations' ) ) {
		wp_set_script_translations( 'kadence-blocks-pro-js', 'kadence-blocks-pro', KBP_PATH . 'languages' );
	}
}
add_action( 'admin_init', 'kadence_blocks_pro_editor_assets', 30 );

/**
 * Enqueue Gutenberg block assets for backend editor.
 */
function kadence_blocks_pro_early_editor_assets() {
	if ( ! is_admin() ) {
		return;
	}
	global $pagenow;
	if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
		$current_screen = get_current_screen();
		if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
			wp_enqueue_script( 'kadence-blocks-pro-early-filters-js', KBP_URL . 'dist/blocks.early-filters.js', array( 'wp-blocks', 'wp-i18n', 'wp-element' ), KBP_VERSION, true );
		}
	}
}
add_action( 'current_screen', 'kadence_blocks_pro_early_editor_assets' );

add_action( 'rest_api_init', 'kadence_blocks_pro_register_api_endpoints' );
/**
 * Setup the post select API endpoint.
 *
 * @return void
 */
function kadence_blocks_pro_register_api_endpoints() {
	$controller = new Kadence_Blocks_Post_Select_Controller;
	$controller->register_routes();
	$mailchimp_controller = new Kadence_MailChimp_REST_Controller;
	$mailchimp_controller->register_routes();
	$sendinblue_controller = new Kadence_SendInBlue_REST_Controller;
	$sendinblue_controller->register_routes();
}
/**
 * Setup the post type options for post blocks.
 *
 * @return array
 */
function kadence_blocks_pro_get_post_types() {
	$args = array(
		'public'       => true,
		'show_in_rest' => true,
	);
	$post_types = get_post_types( $args, 'objects' );
	$output = array();
	foreach ( $post_types as $post_type ) {
		// if ( 'product' == $post_type->name || 'attachment' == $post_type->name ) {
		// 	continue;
		// }
		if ( 'attachment' == $post_type->name ) {
			continue;
		}
		$output[] = array(
			'value' => $post_type->name,
			'label' => $post_type->label,
		);
	}
	return apply_filters( 'kadence_blocks_post_types', $output );
}
/**
 * Setup the post type taxonomies for post blocks.
 *
 * @return array
 */
function kadence_blocks_pro_get_taxonomies() {
	$post_types = kadence_blocks_pro_get_post_types();
	$output = array();
	foreach ( $post_types as $key => $post_type ) {
		$taxonomies = get_object_taxonomies( $post_type['value'], 'objects' );
		$taxs = array();
		foreach ( $taxonomies as $term_slug => $term ) {
			if ( ! $term->public || ! $term->show_ui ) {
				continue;
			}
			$taxs[ $term_slug ] = $term;
			$terms = get_terms( $term_slug );
			$term_items = array();
			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term_key => $term_item ) {
					$term_items[] = array(
						'value' => $term_item->term_id,
						'label' => $term_item->name,
					);
				}
				$output[ $post_type['value'] ]['terms'][ $term_slug ] = $term_items;
			}
		}
		$output[ $post_type['value'] ]['taxonomy'] = $taxs;
	}
	return apply_filters( 'kadence_blocks_taxonomies', $output );
}
/**
 * Get other templates (e.g. product attributes) passing attributes and including the file.
 *
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 */
function kadence_blocks_pro_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	$cache_key = sanitize_key( implode( '-', array( 'template', $template_name, $template_path, $default_path, KBP_VERSION ) ) );
	$template  = (string) wp_cache_get( $cache_key, 'kadence-blocks' );

	if ( ! $template ) {
		$template = kadence_blocks_pro_locate_template( $template_name, $template_path, $default_path );
		wp_cache_set( $cache_key, $template, 'kadence-blocks' );
	}

	// Allow 3rd party plugin filter template file from their plugin.
	$filter_template = apply_filters( 'kadence_blocks_get_template', $template, $template_name, $args, $template_path, $default_path );

	if ( $filter_template !== $template ) {
		if ( ! file_exists( $filter_template ) ) {
			return;
		}
		$template = $filter_template;
	}

	$action_args = array(
		'template_name' => $template_name,
		'template_path' => $template_path,
		'located'       => $template,
		'args'          => $args,
	);

	if ( ! empty( $args ) && is_array( $args ) ) {
		if ( isset( $args['action_args'] ) ) {
			unset( $args['action_args'] );
		}
		extract( $args ); // @codingStandardsIgnoreLine
	}

	do_action( 'kadence_blocks_before_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args'] );

	include $action_args['located'];

	do_action( 'kadence_blocks_before_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args'] );
}
/**
 * Like kadence_blocks_pro_get_template, but returns the HTML instead of outputting.
 *
 * @see kadence_blocks_pro_get_template
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 *
 * @return string
 */
function kadence_blocks_pro_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	ob_start();
	kadence_blocks_pro_get_template( $template_name, $args, $template_path, $default_path );
	return ob_get_clean();
}
/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 * yourtheme/$template_path/$template_name
 * yourtheme/$template_name
 * $default_path/$template_name
 *
 * @param string $template_name Template name.
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 * @return string
 */
function kadence_blocks_pro_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = apply_filters( 'kadence_blocks_template_path', 'kadenceblocks/' );
	}

	if ( ! $default_path ) {
		$default_path = KBP_PATH . 'dist/templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);

	// Get default template/.
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'kadence_blocks_locate_template', $template, $template_name, $template_path );
}
