<?php
/**
 * Post Block Render
 *
 * @since   1.0.5
 * @package Kadence Blocks Pro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Rest API Routes for Woocommerce.
 */
function kadence_wc_register_rest_routes() {
	require_once KBP_PATH . 'dist/dynamicblocks/product-carousel-products-rest-api.php';
	require_once KBP_PATH . 'dist/dynamicblocks/product-carousel-categories-rest-api.php';
	if ( class_exists( 'Kadence_REST_Blocks_Product_Categories_Controller' ) ) {
		$controller = new Kadence_REST_Blocks_Product_Categories_Controller();
		$controller->register_routes();
	}
	if ( class_exists( 'KT_REST_Blocks_Products_Controller' ) ) {
		$controller = new KT_REST_Blocks_Products_Controller();
		$controller->register_routes();
	}
}
add_action( 'rest_api_init', 'kadence_wc_register_rest_routes', 10 );

/**
 * Register the dynamic block.
 *
 * @since 1.0.5
 *
 * @return void
 */
function kadence_blocks_pro_product_carousel_block() {

	// Only load if Gutenberg is available.
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}
	// Hook server side rendering into render callback
	register_block_type( 'kadence/productcarousel', array(
		'attributes' => array(
			'queryType' => array(
				'type' => 'string',
				'default' => 'query',
			),
			'postIds' => array(
				'type' => 'array',
				'default' => array(),
				'items'   => array(
					'type' => 'integer',
				),
			),
			'order' => array(
				'type' => 'string',
				'default' => 'desc',
			),
			'orderBy'  => array(
				'type' => 'string',
				'default' => 'menu_order',
			),
			'categories' => array(
				'type' => 'array',
				'default' => array(),
				'items'   => array(
					'type' => 'object',
				),
			),
			'catOperator' => array(
				'type' => 'string',
				'default' => 'any',
			),
			'uniqueID' => array(
				'type' => 'string',
			),
			'postsToShow' => array(
				'type' => 'number',
				'default' => 6,
			),
			'postColumns'=> array(
				'type' => 'array',
				'default' => array( 3, 3, 3, 2, 2, 1 ),
				'items'   => array(
					'type' => 'integer',
				),
			),
			'columnControl' => array(
				'type' => 'string',
				'default' => 'linked',
			),
			'columnGap' => array(
				'type' => 'number',
				'default' => 30,
			),
			// Layout
			'align' => array(
				'type' => 'string',
				'default' => 'none',
			),
			'autoPlay' => array(
				'type' => 'boolean',
				'default' => true,
			),
			'autoSpeed' => array(
				'type' => 'number',
				'default' => 7000,
			),
			'transSpeed' => array(
				'type' => 'number',
				'default' => 400,
			),
			'slidesScroll' => array(
				'type' => 'string',
				'default' => '1',
			),
			'arrowStyle' => array(
				'type' => 'string',
				'default' => 'whiteondark',
			),
			'dotStyle' => array(
				'type' => 'string',
				'default' => 'dark',
			),
		),
		'render_callback' => 'kadence_blocks_pro_render_product_carousel_block',
	) );

}
add_action( 'init', 'kadence_blocks_pro_product_carousel_block' );

function kadence_blocks_pro_product_carousel_remove_wrap( $content ) {
	$content = apply_filters( 'kadence_blocks_carousel_woocommerce_product_loop_start', '<ul class="products columns-' . esc_attr( wc_get_loop_prop( 'columns' ) ) . '">' );
	return $content;
}
function kadence_blocks_pro_product_carousel_remove_end_wrap( $content ) {
	$content = '</ul>';
	return $content;
}
/**
 * Server rendering for Product Carousel Block
 */
function kadence_blocks_pro_render_product_carousel_block( $attributes ) {
	if ( ! wp_style_is( 'kadence-blocks-product-carousel', 'enqueued' ) ) {
		wp_enqueue_style( 'kadence-blocks-product-carousel' );
	}
	$css = '';
	if ( isset( $attributes['uniqueID'] ) ) {
		$unique_id = $attributes['uniqueID'];
		$style_id = 'kt-blocks' . esc_attr( $unique_id );
		if ( ! wp_style_is( $style_id, 'enqueued' ) ) {
			$css .= kt_blocks_pro_product_carousel_css( $attributes, $unique_id );
			if ( ! empty( $css ) ) {
				$css = '<style id="' . $style_id . '">' . $css . '</style>';
			}
		}
	}
	wp_enqueue_style( 'kadence-blocks-pro-slick' );
	wp_enqueue_script( 'kadence-blocks-pro-slick-init' );
	add_filter( 'woocommerce_product_loop_start', 'kadence_blocks_pro_product_carousel_remove_wrap', 99 );
	add_filter( 'woocommerce_product_loop_end', 'kadence_blocks_pro_product_carousel_remove_end_wrap', 99 );
	ob_start();
	echo '<div class="kt-blocks-product-carousel-block products align' . ( isset( $attributes['blockAlignment'] ) ? $attributes['blockAlignment'] : 'none' ) . ' kt-blocks-carousel kt-product-carousel-loop kt-blocks-carousel' . ( isset( $attributes['uniqueID'] ) ? $attributes['uniqueID'] : 'block-id' ) . '">';
		kadence_blocks_pro_render_product_carousel_query( $attributes );
	echo '</div>';

	$output = ob_get_contents();
	ob_end_clean();
	remove_filter( 'woocommerce_product_loop_start', 'kadence_blocks_pro_product_carousel_remove_wrap', 99 );
	remove_filter( 'woocommerce_product_loop_end', 'kadence_blocks_pro_product_carousel_remove_end_wrap', 99 );
	return $css . $output;
}

/**
 * Server rendering for Post Block Inner Loop
 */
function kadence_blocks_pro_render_product_carousel_query( $attributes ) {
	$carouselclasses = ' kt-carousel-arrowstyle-' . ( isset( $attributes['arrowStyle'] ) ? esc_attr( $attributes['arrowStyle'] ) : 'whiteondark' ) . ' kt-carousel-dotstyle-' . ( isset( $attributes['dotStyle'] ) ? esc_attr( $attributes['dotStyle'] ) : 'dark' );
	$slider_data = ' data-slider-anim-speed="' . ( isset( $attributes['transSpeed'] ) ? esc_attr( $attributes['transSpeed'] ) : '400' ) . '" data-slider-scroll="' . ( isset( $attributes['slidesScroll'] ) ? esc_attr( $attributes['slidesScroll'] ) : '1' ) . '" data-slider-dots="' . ( isset( $attributes['dotStyle'] ) && 'none' === $attributes['dotStyle'] ? 'false' : 'true' ) . '" data-slider-arrows="' . ( isset( $attributes['arrowStyle'] ) && 'none' === $attributes['arrowStyle'] ? 'false' : 'true' ) . '" data-slider-hover-pause="false" data-slider-auto="' . ( isset( $attributes['autoPlay'] ) ? esc_attr( $attributes['autoPlay'] ) : 'true' ) . '" data-slider-speed="' . ( isset( $attributes['autoSpeed'] ) ? esc_attr( $attributes['autoSpeed'] ) : '7000' ) . '"';
	$columns = ( isset( $attributes['postColumns'] ) && is_array( $attributes['postColumns'] ) && 6 === count( $attributes['postColumns'] ) ? $attributes['postColumns'] : array( 2, 2, 2, 2, 1, 1 ) );
	echo '<div class="kt-product-carousel-wrap' . esc_attr( $carouselclasses ) . '" data-columns-xxl="' . esc_attr( $columns[0] ) . '" data-columns-xl="' . esc_attr( $columns[1] ) . '" data-columns-md="' . esc_attr( $columns[2] ) . '" data-columns-sm="' . esc_attr( $columns[3] ) . '" data-columns-xs="' . esc_attr( $columns[4] ) . '" data-columns-ss="' . esc_attr( $columns[5] ) . '"' . wp_kses_post( $slider_data ) . '>';
	$atts = array(
		'class'   => 'kadence-blocks-product-carousel-init',
		'columns' => $columns[2],
		'limit'   => ( isset( $attributes['postsToShow'] ) && !empty( $attributes['postsToShow'] ) ? $attributes['postsToShow'] : 6 ),
		'orderby' => ( isset( $attributes['orderBy'] ) && ! empty( $attributes['orderBy'] ) ? $attributes['orderBy'] : 'title' ),
		'order'   => ( isset( $attributes['order'] ) && ! empty( $attributes['order'] ) ? $attributes['order'] : 'ASC' ),
	);
	$type = 'products';
	if ( isset( $attributes['queryType'] ) && 'individual' === $attributes['queryType'] ) {
		$ids = array();
		if ( is_array( $attributes['postIds'] ) ) {
			foreach ( $attributes['postIds'] as $key => $value ) {
				$ids[] = $value;
			}
		}
		$atts['ids'] = implode( ',', $ids );
		$atts['limit'] = -1;
	} else if ( isset( $attributes['queryType'] ) && 'on_sale' === $attributes['queryType'] ) {
		$type = 'sale_products';
	} else if ( isset( $attributes['queryType'] ) && 'best_selling' === $attributes['queryType'] ) {
		$type = 'best_selling_products';
	} else if ( isset( $attributes['queryType'] ) && 'top_rated' === $attributes['queryType'] ) {
		$type            = 'top_rated_products';
		$atts['orderby'] = 'title';
		$atts['order']   = 'ASC';
	} else {
		if ( isset( $attributes['categories'] ) && ! empty( $attributes['categories'] ) && is_array( $attributes['categories'] ) ) {
			$categories = array();
			foreach ( $attributes['categories'] as $key => $value ) {
				$categories[] = $value['value'];
			}
			$atts['category'] = implode( ',', $categories );
		}
		if ( isset( $attributes['tags'] ) && ! empty( $attributes['tags'] ) && is_array( $attributes['tags'] ) ) {
			$tags = array();
			foreach ( $attributes['tags'] as $key => $value ) {
				$tags[] = $value['value'];
			}
			$atts['tag'] = implode( ',', $tags );
		}
	}
	if ( class_exists( 'WC_Shortcode_Products' ) ) {
		$shortcode = new WC_Shortcode_Products( $atts, $type );

		echo $shortcode->get_content();
	} else {
		echo '<p>' . __( 'WooCommerce Missing', 'kadence-blocks-pro' ) . '</p>';
	}
	echo '</div>';
}
/**
 * Builds CSS for Product Carousel block.
 *
 * @param array  $attr the blocks attr.
 * @param string $unique_id the blocks attr ID.
 */
function kt_blocks_pro_product_carousel_css( $attr, $unique_id ) {
	$css = '';
	// Columns.
	if ( isset( $attr['columnGap'] ) ) {
		$css .= '.kt-blocks-carousel' . $unique_id . ' .products li.product.slick-slide {';
			$css .= 'padding:0px;';
			$css .= 'margin-right:' . $attr['columnGap'] / 2 . 'px;';
			$css .= 'margin-left:' . $attr['columnGap'] / 2 . 'px;';
		$css .= '}';
		$css .= '.kt-blocks-carousel' . $unique_id . ' .kt-product-carousel-wrap {';
			$css .= 'margin-left:-' . $attr['columnGap'] / 2 . 'px;';
			$css .= 'margin-right:-' . $attr['columnGap'] / 2 . 'px;';
		$css .= '}';
		$css .= '.kt-blocks-carousel' . $unique_id . ' .kt-product-carousel-wrap .slick-prev {';
			$css .= 'left:' . $attr['columnGap'] / 2 . 'px;';
		$css .= '}';
		$css .= '.kt-blocks-carousel' . $unique_id . ' .kt-product-carousel-wrap .slick-next {';
			$css .= 'right:' . $attr['columnGap'] / 2 . 'px;';
		$css .= '}';
	}

	return $css;
}
