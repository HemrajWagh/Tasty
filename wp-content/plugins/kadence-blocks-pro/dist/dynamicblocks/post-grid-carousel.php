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
 * Add `rand` as an option for orderby param in REST API.
 * Hook to `rest_{$this->post_type}_collection_params` filter.
 *
 * @param array $query_params Accepted parameters.
 * @return array
 */
function kadence_blocks_rand_orderby_rest_post_collection_params( $query_params ) {
	$query_params['orderby']['enum'][] = 'rand';
	$query_params['orderby']['enum'][] = 'menu_order';
	return $query_params;
}
add_filter( 'rest_post_collection_params', 'kadence_blocks_rand_orderby_rest_post_collection_params' );


/**
 * Register the dynamic block.
 *
 * @since 1.0.5
 *
 * @return void
 */
function kadence_blocks_pro_post_block() {

	// Only load if Gutenberg is available.
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}
	// Hook server side rendering into render callback.
	register_block_type(
		'kadence/postgrid',
		array(
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
					'default' => 'date',
				),
				'categories' => array(
					'type' => 'array',
					'default' => array(),
					'items'   => array(
						'type' => 'object',
					),
				),
				'tags' => array(
					'type' => 'array',
					'default' => array(),
					'items'   => array(
						'type' => 'object',
					),
				),
				'postTax'=> array(
					'type' => 'boolean',
					'default' => false,
				),
				'uniqueID' => array(
					'type' => 'string',
				),
				'postsToShow' => array(
					'type' => 'number',
					'default' => 6,
				),
				'pagination'=> array(
					'type' => 'boolean',
					'default' => false,
				),
				// Layout
				'blockAlignment' => array(
					'type' => 'string',
					'default' => 'none',
				),
				'layout' => array(
					'type' => 'string',
				),
				'postColumns'=> array(
					'type' => 'array',
					'default' => array( 2, 2, 2, 2, 1, 1 ),
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
				'rowGap' => array(
					'type' => 'number',
					'default' => 30,
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
				// Container.
				'backgroundColor' => array(
					'type' => 'string',
				),
				'containerPadding'=> array(
					'type' => 'array',
					'default' => array( 0, 0, 0, 0 ),
					'items'   => array(
						'type' => 'integer',
					),
				),
				'borderColor' => array(
					'type' => 'string',
				),
				'borderOpacity' => array(
					'type' => 'number',
					'default' => 1,
				),
				'borderWidth'=> array(
					'type' => 'array',
					'default' => array( 0, 0, 0, 0 ),
					'items'   => array(
						'type' => 'integer',
					),
				),
				'borderRadius'=> array(
					'type' => 'array',
					'default' => array( 0, 0, 0, 0 ),
					'items'   => array(
						'type' => 'integer',
					),
				),
				// Image.
				'displayImage' => array(
					'type' => 'boolean',
					'default' => true,
				),
				'alignImage' => array(
					'type' => 'string',
					'default' => 'top',
				),
				'sideImageWidth' => array(
					'type' => 'number',
					'default' => 30,
				),
				'sideImageMoveAboveMobile' => array(
					'type' => 'boolean',
					'default' => true,
				),
				'imageRatio'=> array(
					'type' => 'string',
					'default' => '75',
				),
				'imagePadding'=> array(
					'type' => 'array',
					'default' => array( 0, 0, 0, 0 ),
					'items'   => array(
						'type' => 'integer',
					),
				),
				// Header.
				'headerBG'=> array(
					'type' => 'string',
				),
				'headerBGOpacity'=> array(
					'type' => 'number',
					'default' => 1,
				),
				'headerPadding'=> array(
					'type' => 'array',
					'default' => array( 0, 0, 0, 0 ),
					'items'   => array(
						'type' => 'integer',
					),
				),
				'headerMargin'=> array(
					'type' => 'array',
					'default' => array( 0, 0, 0, 0 ),
					'items'   => array(
						'type' => 'integer',
					),
				),
				// Above Title.
				'displayAboveCategories' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'aboveDividerSymbol' => array(
					'type' => 'string',
					'default' => 'line',
				),
				'aboveColor'=> array(
					'type' => 'string',
				),
				'aboveLinkColor' => array(
					'type' => 'string',
				),
				'aboveLinkHoverColor' => array(
					'type' => 'string',
				),
				'aboveFont'=> array(
					'type' => 'array',
					'default' => array(
						array(
							'size' => array( '', '', '' ),
							'sizeType' => 'px',
							'lineHeight' => array( '', '', '' ),
							'lineType' => 'px',
							'letterSpacing' => '',
							'textTransform' => '',
							'family' => '',
							'google' => '',
							'style' => '',
							'weight' => '',
							'variant' => '',
							'subset' => '',
							'loadGoogle' => true,
						)
					),
					'items'   => array(
						'type' => 'object',
					),
				),
				// Title.
				'displayTitle' => array(
					'type' => 'boolean',
					'default' => true,
				),
				'titleColor'=> array(
					'type' => 'string',
				),
				'titleFont'=> array(
					'type' => 'array',
					'default' => array(
						array(
							'level' => 2,
							'size' => array( '', '', '' ),
							'sizeType' => 'px',
							'lineHeight' => array( '', '', '' ),
							'lineType' => 'px',
							'letterSpacing' => '',
							'textTransform' => '',
							'family' => '',
							'google' => '',
							'style' => '',
							'weight' => '',
							'variant' => '',
							'subset' => '',
							'loadGoogle' => true,
						)
					),
					'items'   => array(
						'type' => 'object',
					),
				),
				'titlePadding'=> array(
					'type' => 'array',
					'default' => array( 5, 0, 10, 0 ),
					'items'   => array(
						'type' => 'integer',
					),
				),
				'titleMargin'=> array(
					'type' => 'array',
					'default' => array( 0, 0, 0, 0 ),
					'items'   => array(
						'type' => 'integer',
					),
				),
				// Meta.
				'displayDate' => array(
					'type' => 'boolean',
					'default' => true,
				),
				'datePreText' => array(
					'type'    => 'string',
					'default' => __( 'Posted on', 'kadence-blocks-pro' ),
				),
				'displayModifiedDate' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'modifiedDatePreText' => array(
					'type'    => 'string',
					'default' => __( 'Updated on', 'kadence-blocks-pro' ),
				),
				'displayAuthor' => array(
					'type' => 'boolean',
					'default' => true,
				),
				'authorPreText' => array(
					'type'    => 'string',
					'default' => __( 'By', 'kadence-blocks-pro' ),
				),
				'displayCategory' => array(
					'type' => 'boolean',
					'default' => true,
				),
				'categoryPreText' => array(
					'type'    => 'string',
					'default' => __( 'Posted in', 'kadence-blocks-pro' ),
				),
				'displayComment' => array(
					'type' => 'boolean',
					'default' => true,
				),
				'metaColor' => array(
					'type' => 'string',
				),
				'metaLinkColor' => array(
					'type' => 'string',
				),
				'metaLinkHoverColor' => array(
					'type' => 'string',
				),
				'metaFont'=> array(
					'type' => 'array',
					'default' => array(
						array(
							'size' => array( '', '', '' ),
							'sizeType' => 'px',
							'lineHeight' => array( '', '', '' ),
							'lineType' => 'px',
							'letterSpacing' => '',
							'textTransform' => '',
							'family' => '',
							'google' => '',
							'style' => '',
							'weight' => '',
							'variant' => '',
							'subset' => '',
							'loadGoogle' => true,
						)
					),
					'items'   => array(
						'type' => 'object',
					),
				),
				'metaDividerSymbol' => array(
					'type' => 'string',
					'default' => '',
				),
				// Body.
				'bodyBG'=> array(
					'type' => 'string',
				),
				'bodyBGOpacity'=> array(
					'type' => 'number',
					'default' => 1,
				),
				'bodyPadding'=> array(
					'type' => 'array',
					'default' => array( 0, 0, 0, 0 ),
					'items'   => array(
						'type' => 'integer',
					),
				),
				'bodyMargin'=> array(
					'type' => 'array',
					'default' => array( 0, 0, 0, 0 ),
					'items'   => array(
						'type' => 'integer',
					),
				),
				// Excerpt.
				'displayExcerpt' => array(
					'type' => 'boolean',
					'default' => true,
				),
				'excerptColor' => array(
					'type' => 'string',
				),
				'excerptCustomLength' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'excerptLength'=> array(
					'type' => 'number',
					'default' => 40,
				),
				'excerptFont'=> array(
					'type' => 'array',
					'default' => array(
						array(
							'size' => array( '', '', '' ),
							'sizeType' => 'px',
							'lineHeight' => array( '', '', '' ),
							'lineType' => 'px',
							'letterSpacing' => '',
							'family' => '',
							'google' => '',
							'style' => '',
							'weight' => '',
							'variant' => '',
							'subset' => '',
							'loadGoogle' => true,
						)
					),
					'items'   => array(
						'type' => 'object',
					),
				),
				// Footer.
				'footerBG'=> array(
					'type' => 'string',
				),
				'footerBGOpacity'=> array(
					'type' => 'number',
					'default' => 1,
				),
				'footerPadding'=> array(
					'type' => 'array',
					'default' => array( 0, 0, 0, 0 ),
					'items'   => array(
						'type' => 'integer',
					),
				),
				'footerMargin'=> array(
					'type' => 'array',
					'default' => array( 0, 0, 0, 0 ),
					'items'   => array(
						'type' => 'integer',
					),
				),
				'footerBorderColor' => array(
					'type' => 'string',
				),
				'footerBorderOpacity' => array(
					'type' => 'number',
					'default' => 1,
				),
				'footerBorderWidth'=> array(
					'type' => 'array',
					'default' => array( 0, 0, 0, 0 ),
					'items'   => array(
						'type' => 'integer',
					),
				),
				// Footer inner
				'footerDisplayDate' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'footerDisplayCategories' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'footerDisplayTags' => array(
					'type' => 'boolean',
					'default' => true,
				),
				'footerDisplayAuthor' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'footerDisplayComment' => array(
					'type' => 'boolean',
					'default' => false,
				),
				// Footer Font
				'footerColor' => array(
					'type' => 'string',
				),
				'footerLinkColor' => array(
					'type' => 'string',
				),
				'footerLinkHoverColor' => array(
					'type' => 'string',
				),
				'footerFont'=> array(
					'type' => 'array',
					'default' => array(
						array(
							'size' => array( '', '', '' ),
							'sizeType' => 'px',
							'lineHeight' => array( '', '', '' ),
							'lineType' => 'px',
							'letterSpacing' => '',
							'textTransform' => '',
							'family' => '',
							'google' => '',
							'style' => '',
							'weight' => '',
							'variant' => '',
							'subset' => '',
							'loadGoogle' => true,
						)
					),
					'items'   => array(
						'type' => 'object',
					),
				),
				// Read More
				'displayReadMore' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'readMoreText'  => array(
					'type'    => 'string',
					'default' => __( 'Read More', 'kadence-blocks-pro' ),
				),
				'readMoreBorder'=> array(
					'type' => 'number',
					'default' => 0,
				),
				'readMoreBorderRadius'=> array(
					'type' => 'number',
					'default' => 0,
				),
				'readMoreColor' => array(
					'type' => 'string',
					'default' => '#ffffff',
				),
				'readMoreHoverColor' => array(
					'type' => 'string',
					'default' => '#ffffff',
				),
				'readMoreBorderColor' => array(
					'type' => 'string',
					'default' => '#444444',
				),
				'readMoreHoverBorderColor' => array(
					'type' => 'string',
					'default' => '#555555',
				),
				'readMoreBackground' => array(
					'type' => 'string',
					'default' => '#444444',
				),
				'readMoreHoverBackground' => array(
					'type' => 'string',
					'default' => '#555555',
				),
				'readMoreFont'=> array(
					'type' => 'array',
					'default' => array(
						array(
							'size' => array( '', '', '' ),
							'sizeType' => 'px',
							'lineHeight' => array( '', '', '' ),
							'lineType' => 'px',
							'letterSpacing' => '',
							'family' => '',
							'google' => '',
							'style' => '',
							'weight' => '',
							'variant' => '',
							'subset' => '',
							'loadGoogle' => true,
						)
					),
					'items'   => array(
						'type' => 'object',
					),
				),
				'readMorePadding'=> array(
					'type' => 'array',
					'default' => array( 4, 8, 4, 8 ),
					'items'   => array(
						'type' => 'integer',
					),
				),
				'readMoreMargin'=> array(
					'type' => 'array',
					'default' => array( 0, 0, 0, 0 ),
					'items'   => array(
						'type' => 'integer',
					),
				),
				'postType' => array(
					'type' => 'string',
					'default' => 'post',
				),
				'taxType' => array(
					'type' => 'string',
					'default' => '',
				),
				'textAlign' => array(
					'type' => 'string',
					'default' => '',
				),
				'offsetQuery' => array(
					'type' => 'number',
					'default' => 0,
				),
				'postTax' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'excludeTax' => array(
					'type' => 'string',
					'default' => 'include',
				),
				'showUnique' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'displayShadow' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'shadow'=> array(
					'type' => 'array',
					'default' => array(
						array(
							'color' => '#000000',
							'opacity' => 0.2,
							'spread' => 0,
							'blur' => 14,
							'hOffset' => 0,
							'vOffset' => 0,
							'inset' => false,
						)
					),
					'items'   => array(
						'type' => 'object',
					),
				),
				'footerAlignBottom' => array(
					'type' => 'boolean',
					'default' => false,
				),
				// Filter.
				'displayFilter' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'filterAllText' => array(
					'type' => 'string',
				),
				'filterTaxType' => array(
					'type' => 'string',
				),
				'filterTaxSelect' => array(
					'type' => 'array',
					'default' => '',
					'items'   => array(
						'type' => 'object',
					),
				),
				'filterBackground'=> array(
					'type' => 'string',
				),
				'filterBackgroundOpacity'=> array(
					'type' => 'number',
				),
				'filterHoverBackground'=> array(
					'type' => 'string',
				),
				'filterHoverBackgroundOpacity'=> array(
					'type' => 'number',
				),
				'filterActiveBackground'=> array(
					'type' => 'string',
				),
				'filterActiveBackgroundOpacity'=> array(
					'type' => 'number',
				),
				'filterBorder'=> array(
					'type' => 'string',
				),
				'filterBorderOpacity'=> array(
					'type' => 'number',
				),
				'filterHoverBorder'=> array(
					'type' => 'string',
				),
				'filterHoverBorderOpacity'=> array(
					'type' => 'number',
				),
				'filterActiveBorder'=> array(
					'type' => 'string',
				),
				'filterActiveBorderOpacity'=> array(
					'type' => 'number',
				),
				'filterColor' => array(
					'type' => 'string',
				),
				'filterHoverColor' => array(
					'type' => 'string',
				),
				'filterActiveColor' => array(
					'type' => 'string',
				),
				'filterBorderRadius'=> array(
					'type' => 'number',
				),
				'filterBorderWidth'=> array(
					'type' => 'array',
					'default' => array( 0, 0, 2, 0 ),
					'items'   => array(
						'type' => 'integer',
					),
				),
				'filterPadding' => array(
					'type' => 'array',
					'default' => array( 5, 8, 5, 8 ),
					'items'   => array(
						'type' => 'integer',
					),
				),
				'filterMargin'=> array(
					'type' => 'array',
					'default' => array( 0, 10, 0, 0 ),
					'items'   => array(
						'type' => 'integer',
					),
				),
				'filterFont'=> array(
					'type' => 'array',
					'default' => array(
						array(
							'size' => array( '', '', '' ),
							'sizeType' => 'px',
							'lineHeight' => array( '', '', '' ),
							'lineType' => 'px',
							'letterSpacing' => '',
							'textTransform' => '',
							'family' => '',
							'google' => '',
							'style' => '',
							'weight' => '',
							'variant' => '',
							'subset' => '',
							'loadGoogle' => true,
						)
					),
					'items'   => array(
						'type' => 'object',
					),
				),
				'filterAlign' => array(
					'type' => 'string',
					'default' => '',
				),
				'allowSticky' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'imageFileSize'=> array(
					'type' => 'string',
					'default' => 'large',
				),
				'openNewTab' => array(
					'type' => 'boolean',
					'default' => false,
				),
			),
			'render_callback' => 'kadence_blocks_pro_render_post_block',
		)
	);

}
add_action( 'init', 'kadence_blocks_pro_post_block' );

/**
 * Create API fields for additional info
 */
function kadence_blocks_pro_register_rest_fields() {
	// Add featured image source
	$post_types = kadence_blocks_pro_get_post_types();
	foreach ( $post_types as $key => $post_type ) {
		register_rest_field(
			$post_type['value'],
			'featured_image_src_large',
			array(
				'get_callback'    => 'kadence_blocks_pro_get_large_image_src',
				'update_callback' => null,
				'schema'          => null,
			)
		);
		// Add author info
		register_rest_field(
			$post_type['value'],
			'author_info',
			array(
				'get_callback'    => 'kadence_blocks_pro_get_author_info',
				'update_callback' => null,
				'schema'          => null,
			)
		);
		// Add comment info.
		register_rest_field(
			$post_type['value'],
			'comment_info',
			array(
				'get_callback'    => 'kadence_blocks_pro_get_comment_info',
				'update_callback' => null,
				'schema'          => null,
			)
		);
	}
	// Add category info
	register_rest_field(
		'post',
		'category_info',
		array(
			'get_callback'    => 'kadence_blocks_pro_get_category_info',
			'update_callback' => null,
			'schema'          => null,
		)
	);
	// Add tag info
	register_rest_field(
		'post',
		'tag_info',
		array(
			'get_callback'    => 'kadence_blocks_pro_get_tag_info',
			'update_callback' => null,
			'schema'          => null,
		)
	);
}
add_action( 'rest_api_init', 'kadence_blocks_pro_register_rest_fields' );

/**
 * Get category info for the rest field
 *
 * @param object $object Post Object.
 * @param string $field_name Field name.
 * @param object $request Request Object.
 */
function kadence_blocks_pro_get_category_info( $object, $field_name, $request ) {
	$category_array = get_the_category( $object['id'] );
	return $category_array;
}

/**
 * Get tag info for the rest field
 *
 * @param object $object Post Object.
 * @param string $field_name Field name.
 * @param object $request Request Object.
 */
function kadence_blocks_pro_get_tag_info( $object, $field_name, $request ) {
	$tag_array = get_the_tags( $object['id'] );
	return $tag_array;
}

/**
 * Get author info for the rest field
 *
 * @param object $object Post Object.
 * @param string $field_name Field name.
 * @param object $request Request Object.
 */
function kadence_blocks_pro_get_comment_info( $object, $field_name, $request ) {
	// Get the comments count.
	$comments_count = wp_count_comments( $object['id'] );
	return $comments_count->total_comments;
}

/**
 * Get author info for the rest field
 *
 * @param object $object Post Object.
 * @param string $field_name Field name.
 * @param object $request Request Object.
 */
function kadence_blocks_pro_get_author_info( $object, $field_name, $request ) {
	$author_data = array();
	if ( post_type_supports( $object['type'], 'author' ) ) {
		// Get the author name
		$author_data['display_name'] = get_the_author_meta( 'display_name', $object['author'] );

		// Get the author link
		$author_data['author_link'] = get_author_posts_url( $object['author'] );
	}

	// Return the author data
	return $author_data;
}
/**
 * Get image info for the rest field
 *
 * @param object $object Post Object.
 * @param string $field_name Field name.
 * @param object $request Request Object
 */
function kadence_blocks_pro_get_large_image_src( $object, $field_name, $request ) {
	$feat_img_array = array();
	if ( post_type_supports( $object['type'], 'thumbnail' ) ) {
		$feat_img_array = wp_get_attachment_image_src(
			$object['featured_media'],
			'large',
			false
		);
	}
	return $feat_img_array;
}
/**
 * Server rendering for post Block Inner Loop
 *
 * @param array $attributes the block attributes.
 */
function kadence_blocks_pro_render_post_block_filter( $attributes ) {
	if ( isset( $attributes['filterTaxType'] ) && ! empty( $attributes['filterTaxType'] ) ) {
		echo '<div class="kb-post-filter-container">';
		if ( isset( $attributes['filterTaxSelect'] ) && is_array( $attributes['filterTaxSelect'] ) && 1 <= count( $attributes['filterTaxSelect'] ) ) {
			echo '<button class="kb-filter-item is-active" data-filter="*">';
				echo ( isset( $attributes['filterAllText'] ) && ! empty( $attributes['filterAllText'] ) ? esc_html( $attributes['filterAllText'] ) : __( 'All', 'kadence-blocks-pro' ) );
			echo '</button>';
			foreach ( $attributes['filterTaxSelect'] as $value ) {
				$term = get_term( $value['value'], $attributes['filterTaxType'] );
				echo '<button class="kb-filter-item" data-filter=".kb-filter-' . esc_attr( $term->slug ) . '">';
				echo esc_html( $term->name );
				echo '</button>';
			}
		} else {
			$terms = get_terms( $attributes['filterTaxType'] );
			if ( ! empty( $terms ) ) {
				echo '<button class="kb-filter-item is-active" data-filter="*">';
					echo ( isset( $attributes['filterAllText'] ) && ! empty( $attributes['filterAllText'] ) ? esc_html( $attributes['filterAllText'] ) : __( 'All', 'kadence-blocks-pro' ) );
				echo '</button>';
				foreach ( $terms as $term_key => $term_item ) {
					echo '<button class="kb-filter-item" data-filter=".kb-filter-' . esc_attr( $term_item->slug ) . '">';
					echo esc_html( $term_item->name );
					echo '</button>';
				}
			}
		}
		echo '</div>';
	}
}
/**
 * Server rendering for Post Block
 */
function kadence_blocks_pro_render_post_block( $attributes ) {
	if ( ! wp_style_is( 'kadence-blocks-post-grid', 'enqueued' ) ) {
		wp_enqueue_style( 'kadence-blocks-post-grid' );
	}
	$css = '';
	if ( isset( $attributes['uniqueID'] ) ) {
		$style_id = 'kt-blocks' . esc_attr( $attributes['uniqueID'] );
		if ( ! wp_style_is( $style_id, 'enqueued' ) ) {
			$css .= '<style id="' . esc_attr( $style_id ) . '">';
			$unique_id = $attributes['uniqueID'];
			$css .= kt_blocks_pro_post_grid_css( $attributes, $unique_id );
			$css .= '</style>';
		}
	}
	if ( ( isset( $attributes['layout'] ) && 'masonry' === $attributes['layout'] || isset( $attributes['layout'] ) && 'grid' === $attributes['layout'] || ! isset( $attributes['layout'] ) ) && isset( $attributes['displayFilter'] ) && true === $attributes['displayFilter'] ) {
		wp_enqueue_script( 'kadence-blocks-pro-iso-post-init' );
	} elseif ( isset( $attributes['layout'] ) && 'masonry' === $attributes['layout'] ) {
		wp_enqueue_script( 'kadence-blocks-pro-masonry-init' );
	} elseif ( isset( $attributes['layout'] ) && 'carousel' === $attributes['layout'] ) {
		wp_enqueue_style( 'kadence-blocks-pro-slick' );
		wp_enqueue_script( 'kadence-blocks-pro-slick-init' );
	}
	ob_start();
	if ( isset( $attributes['layout'] ) && 'carousel' === $attributes['layout'] ) {
		$carouselclasses = ' kt-blocks-carousel';
	} else {
		$carouselclasses = '';
	}
	if ( empty( $carouselclasses ) && isset( $attributes['displayFilter'] ) && true === $attributes['displayFilter'] && ( ! isset( $attributes['pagination'] ) || isset( $attributes['pagination'] ) && false === $attributes['pagination'] ) ) {
		$filter_class = 'kb-filter-enabled';
	} else {
		$filter_class = '';
	}
	echo '<div class="wp-block-kadence-postgrid kt-blocks-post-loop-block align' . ( isset( $attributes['blockAlignment'] ) ? $attributes['blockAlignment'] : 'none' ) . ' kt-post-loop' . ( isset( $attributes['uniqueID'] ) ? $attributes['uniqueID'] : 'block-id' ) . ' kt-post-grid-layout-'. ( isset( $attributes['layout'] ) ? esc_attr( $attributes['layout'] ) : 'grid' ) . esc_attr( $carouselclasses ) . ' ' . esc_attr( $filter_class ) . ( isset( $attributes['className'] ) && ! empty( $attributes['className'] ) ? ' ' . esc_attr( $attributes['className'] ) : '' ) . '">';
	if ( empty( $carouselclasses ) && isset( $attributes['displayFilter'] ) && true === $attributes['displayFilter'] && ( ! isset( $attributes['pagination'] ) || isset( $attributes['pagination'] ) && false === $attributes['pagination'] ) ) {
		kadence_blocks_pro_render_post_block_filter( $attributes );
	}
		kadence_blocks_pro_render_post_block_query( $attributes );
	echo '</div>';

	$output = ob_get_contents();
	ob_end_clean();
	return $css.$output;
}

/**
 * Server rendering for Post Block Inner Loop
 */
function kadence_blocks_pro_render_post_block_query( $attributes ) {
	global $kadence_blocks_posts_not_in;
	if ( isset( $attributes['layout'] ) && 'carousel' === $attributes['layout'] ) {
		$carouselclasses = ' kt-carousel-arrowstyle-' . ( isset( $attributes['arrowStyle'] ) ? esc_attr( $attributes['arrowStyle'] ) : 'whiteondark' ) . ' kt-carousel-dotstyle-' . ( isset( $attributes['dotStyle'] ) ? esc_attr( $attributes['dotStyle'] ) : 'dark' );
		$slider_data = ' data-slider-anim-speed="' . ( isset( $attributes['transSpeed'] ) ? esc_attr( $attributes['transSpeed'] ) : '400' ) . '" data-slider-scroll="' . ( isset( $attributes['slidesScroll'] ) ? esc_attr( $attributes['slidesScroll'] ) : '1' ) . '" data-slider-dots="' . ( isset( $attributes['dotStyle'] ) && 'none' === $attributes['dotStyle'] ? 'false' : 'true' ) . '" data-slider-arrows="' . ( isset( $attributes['arrowStyle'] ) && 'none' === $attributes['arrowStyle'] ? 'false' : 'true' ) . '" data-slider-hover-pause="false" data-slider-auto="' . ( isset( $attributes['autoPlay'] ) ? esc_attr( $attributes['autoPlay'] ) : 'true' ) . '" data-slider-speed="' . ( isset( $attributes['autoSpeed'] ) ? esc_attr( $attributes['autoSpeed'] ) : '7000' ) . '"';
	} else {
		$carouselclasses = '';
		$slider_data = '';
	}
	$columns = ( isset( $attributes['postColumns'] ) && is_array( $attributes['postColumns'] ) && 6 === count( $attributes['postColumns'] ) ? $attributes['postColumns'] : array( 2, 2, 2, 2, 1, 1 ) );
	$post_type = ( isset( $attributes['postType'] ) && ! empty( $attributes['postType'] ) ? $attributes['postType'] : 'post' );
	echo '<div class="kt-post-grid-wrap kt-post-grid-layout-'. ( isset( $attributes['layout'] ) ? esc_attr( $attributes['layout'] ) : 'grid' ) . '-wrap' . esc_attr( $carouselclasses ) . '" data-columns-xxl="' . esc_attr( $columns[0] ) . '" data-columns-xl="' . esc_attr( $columns[1] ) . '" data-columns-md="' . esc_attr( $columns[2] ) . '" data-columns-sm="' . esc_attr( $columns[3] ) . '" data-columns-xs="' . esc_attr( $columns[4] ) . '" data-columns-ss="' . esc_attr( $columns[5] ) . '"' . wp_kses_post( $slider_data ) . '>';
	if ( isset( $attributes['queryType'] ) && 'individual' === $attributes['queryType'] ) {
		$args = array(
			'post_type' => $post_type,
			'orderby' => 'post__in',
			'posts_per_page' => -1,
			'post__in'  => ( isset( $attributes['postIds'] ) && ! empty( $attributes['postIds'] ) ? $attributes['postIds'] : 0 ),
			'ignore_sticky_posts' => 1,
		);
	} else {
		$args = array(
			'post_type'           => $post_type,
			'posts_per_page'      => ( isset( $attributes['postsToShow'] ) && ! empty( $attributes['postsToShow'] ) ? $attributes['postsToShow'] : 6 ),
			'post_status'         => 'publish',
			'order'               => ( isset( $attributes['order'] ) && ! empty( $attributes['order'] ) ? $attributes['order'] : 'desc' ),
			'orderby'             => ( isset( $attributes['orderBy'] ) && ! empty( $attributes['orderBy'] ) ? $attributes['orderBy'] : 'date' ),
			'ignore_sticky_posts' => ( isset( $attributes['allowSticky'] ) && $attributes['allowSticky'] ? 0 : 1 ),
			'post__not_in'        => ( isset( $kadence_blocks_posts_not_in ) && is_array( $kadence_blocks_posts_not_in ) ? $kadence_blocks_posts_not_in : array() ),
		);
		if ( isset( $attributes['offsetQuery'] ) && ! empty( $attributes['offsetQuery'] ) ) {
			$args['offset'] = $attributes['offsetQuery'];
		}
		if ( isset( $attributes['categories'] ) && ! empty( $attributes['categories'] ) && is_array( $attributes['categories'] ) ) {
			$categories = array();
			$i = 1;
			foreach ( $attributes['categories'] as $key => $value ) {
				$categories[] = $value['value'];
			}
		} else {
			$categories = array();
		}
		if ( 'post' !== $post_type || ( isset( $attributes['postTax'] ) && true === $attributes['postTax'] ) ) {
			if ( isset( $attributes['taxType'] ) && ! empty( $attributes['taxType'] ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => ( isset( $attributes['taxType'] ) ) ? $attributes['taxType'] : 'category',
					'field'    => 'id',
					'terms'    => $categories,
					'operator' => ( isset( $attributes['excludeTax'] ) && 'exclude' === $attributes['excludeTax'] ? 'NOT IN' : 'IN' ),
				);
			}
		} else {
			if ( isset( $attributes['tags'] ) && ! empty( $attributes['tags'] ) && is_array( $attributes['tags'] ) ) {
				$tags = array();
				$i = 1;
				foreach ( $attributes['tags'] as $key => $value ) {
					$tags[] = $value['value'];
				}
			} else {
				$tags = array();
			}
			if ( isset( $attributes['excludeTax'] ) && 'exclude' === $attributes['excludeTax'] ) {
				$args['category__not_in'] = $categories;
				$args['tag__not_in'] = $tags;
			} else {
				$args['category__in'] = $categories;
				$args['tag__in'] = $tags;
			}
		}
		if ( isset( $attributes['layout'] ) && 'carousel' !== $attributes['layout'] && ( ( isset( $attributes['offsetQuery'] ) && 1 > $attributes['offsetQuery'] ) || ! isset( $attributes['offsetQuery'] ) ) && isset( $attributes['pagination'] ) && true === $attributes['pagination'] ) {
			if ( get_query_var( 'paged' ) ) {
				$args['paged'] = get_query_var( 'paged' );
			} else if ( get_query_var( 'page' ) ) {
				$args['paged'] = get_query_var( 'page' );
			} else {
				$args['paged'] = 1;
			}
		}
	}
	$loop = new WP_Query( $args );
	if ( isset( $attributes['layout'] ) && 'carousel' !== $attributes['layout'] && ( ( isset( $attributes['offsetQuery'] ) && 1 > $attributes['offsetQuery'] ) || ! isset( $attributes['offsetQuery'] ) ) && isset( $attributes['pagination'] ) && true === $attributes['pagination'] ) {
		global $wp_query;
		$wp_query = $loop;
	}
	if ( ! isset( $kadence_blocks_posts_not_in ) || ! is_array( $kadence_blocks_posts_not_in ) ) {
		$kadence_blocks_posts_not_in = array();
	}
	if ( $loop->have_posts() ) {
		while ( $loop->have_posts() ) {
			$loop->the_post();
			if ( isset( $attributes['showUnique'] ) && true === $attributes['showUnique'] ) {
				$kadence_blocks_posts_not_in[] = get_the_ID();
			}
			if ( isset( $attributes['layout'] ) && 'masonry' === $attributes['layout'] ) {
				$tax_filter_classes = '';
				if ( isset( $attributes['filterTaxType'] ) && ! empty( $attributes['filterTaxType'] ) ) {
					global $post;
					$terms = get_the_terms( $post->ID, $attributes['filterTaxType'] );
					if ( $terms && ! is_wp_error( $terms ) ) {
						foreach( $terms as $term ) {
							$tax_filter_classes .= ' kb-filter-' . $term->slug;
						}
					}
				}
				echo '<div class="kt-post-masonry-item' . esc_attr( $tax_filter_classes ) . '">';
			} else if ( isset( $attributes['layout'] ) && 'grid' === $attributes['layout'] && isset( $attributes['displayFilter'] ) && true === $attributes['displayFilter'] && ( ! isset( $attributes['pagination'] ) || isset( $attributes['pagination'] ) && false === $attributes['pagination'] ) ) {
				$tax_filter_classes = '';
				if ( isset( $attributes['filterTaxType'] ) && ! empty( $attributes['filterTaxType'] ) ) {
					global $post;
					$terms = get_the_terms( $post->ID, $attributes['filterTaxType'] );
					if ( $terms && ! is_wp_error( $terms ) ) {
						foreach( $terms as $term ) {
							$tax_filter_classes .= ' kb-filter-' . $term->slug;
						}
					}
				}
				echo '<div class="kt-post-masonry-item' . esc_attr( $tax_filter_classes ) . '">';
			} else if ( isset( $attributes['layout'] ) && ( 'carousel' === $attributes['layout'] || 'fluidcarousel' === $attributes['layout'] ) ) {
				echo '<div class="kt-post-slider-item">';
			}
				kadence_blocks_pro_render_post_block_loop( $attributes );
			if ( isset( $attributes['layout'] ) && 'grid' !== $attributes['layout'] ) {
				echo '</div>';
			}
			if ( isset( $attributes['layout'] ) && 'grid' === $attributes['layout'] && isset( $attributes['displayFilter'] ) && true === $attributes['displayFilter'] && ( ! isset( $attributes['pagination'] ) || isset( $attributes['pagination'] ) && false === $attributes['pagination'] ) ) {
				echo '</div>';
			}
		}
	} else {
		/**
		 * @hooked kt_blocks_pro_get_no_posts - 10
		 */
		do_action( 'kadence_blocks_post_no_posts', $attributes );
	}
	echo '</div>';
	wp_reset_postdata();
	if ( isset( $attributes['layout'] ) && 'carousel' !== $attributes['layout'] && ( ( isset( $attributes['offsetQuery'] ) && 1 > $attributes['offsetQuery'] ) || ! isset( $attributes['offsetQuery'] ) ) && isset( $attributes['pagination'] ) && true === $attributes['pagination'] ) {
		if ( $loop->max_num_pages > 1 ) {
			kadence_blocks_pro_pagination();
		}
		wp_reset_query();
	}
}
/**
 * Server rendering for Post Block Inner Loop
 *
 * @param array $attributes the block attritbutes.
 */
function kadence_blocks_pro_render_post_block_loop( $attributes ) {
	$image_align        = ( isset( $attributes['alignImage'] ) && isset( $attributes['displayImage'] ) && true === $attributes['displayImage'] && has_post_thumbnail() ? $attributes['alignImage'] : 'none' );
	$footer_bottom_align = ( isset( $attributes['layout'] ) && 'masonry' !== $attributes['layout'] && isset( $attributes['footerAlignBottom'] ) && true === $attributes['footerAlignBottom'] ? ' kb-post-footer-bottom-align' : '' );
	$image_mobile_align  = ( isset( $attributes['sideImageMoveAboveMobile'] ) && true === $attributes['sideImageMoveAboveMobile'] ? 'kt-feat-image-mobile-align-top' : 'kt-feat-image-mobile-align-side' );
	echo '<article class="kt-blocks-post-grid-item">';
		echo '<div class="kt-blocks-post-grid-item-inner-wrap kt-feat-image-align-' . esc_attr( $image_align ) . ' ' . esc_attr( $image_mobile_align ) . esc_attr( $footer_bottom_align ) . '">';
			/**
			 * Kadence Blocks Post Loop Start
			 *
			 * @hooked kt_blocks_pro_get_post_image - 20
			 */
			do_action( 'kadence_blocks_post_loop_start', $attributes );
			echo '<div class="kt-blocks-post-grid-item-inner">';
				echo '<header>';
				/**
				 * @hooked kt_blocks_pro_get_above_categories - 10
				 * @hooked kt_blocks_pro_get_post_title - 20
				 * * @hooked kt_blocks_pro_get_meta_area - 30
				 */
				do_action( 'kadence_blocks_post_loop_header', $attributes );
				echo '</header>';
				echo '<div class="entry-content">';
					/**
					 * @hooked kt_blocks_pro_get_post_excerpt - 20
					 * @hooked kt_blocks_pro_get_post_read_more - 30
					 */
					do_action( 'kadence_blocks_post_loop_content', $attributes );
				echo '</div>';
				echo '<footer class="kt-blocks-post-footer">';
					echo '<div class="kt-blocks-post-footer-left">';
						/**
						 * @hooked kt_blocks_pro_get_post_footer_date - 10
						 * @hooked kt_blocks_pro_get_post_footer_categories - 15
						 * @hooked kt_blocks_pro_get_post_footer_tags - 20
						 */
						do_action( 'kadence_blocks_post_loop_footer_start', $attributes );
					echo '</div>';
					echo '<div class="kt-blocks-post-footer-right">';
						/**
						 * @hooked kt_blocks_pro_get_post_footer_author - 10
						 * @hooked kt_blocks_pro_get_post_footer_comments - 15
						 */
						do_action( 'kadence_blocks_post_loop_footer_end', $attributes );
					echo '</div>';
				echo '</footer>';
			echo '</div>';
		echo '</div>';
	do_action( 'kadence_blocks_post_loop_end' );
	echo '</article>';
}

function kadence_blocks_pro_pagination() {
	$args = array();
	$args['mid_size'] = 3;
	$args['end_size'] = 1;
	$args['prev_text'] = '<svg style="display:inline-block;vertical-align:middle" class="kt-blocks-pagination-left-svg" viewBox="0 0 320 512" height="14" width="8" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M34.52 239.03L228.87 44.69c9.37-9.37 24.57-9.37 33.94 0l22.67 22.67c9.36 9.36 9.37 24.52.04 33.9L131.49 256l154.02 154.75c9.34 9.38 9.32 24.54-.04 33.9l-22.67 22.67c-9.37 9.37-24.57 9.37-33.94 0L34.52 272.97c-9.37-9.37-9.37-24.57 0-33.94z"></path></svg>';
	$args['next_text'] = '<svg style="display:inline-block;vertical-align:middle" class="kt-blocks-pagination-right-svg" viewBox="0 0 320 512" height="14" width="8" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"></path></svg>';

	echo '<div class="kt-blocks-page-nav">';
		the_posts_pagination( $args );
	echo '</div>';
}
function kt_blocks_pro_hex2rgba( $color, $opacity = null ) {
	if ( strpos( $color, 'palette' ) === 0 ) {
		$color = 'var(--global-' . $color . ')';
	} else if ( isset( $opacity ) && is_numeric( $opacity ) ) {
		$color = kadence_blocks_pro_hex2rgba( $color, $opacity );
	}
	return $color;
}
function kadence_blocks_pro_hex2rgba( $hex, $alpha ) {
	if ( empty( $hex ) ) {
		return '';
	}
	$hex = str_replace( '#', '', $hex );

	if ( strlen( $hex ) == 3 ) {
		$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
		$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
		$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
	} else {
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );
	}
	$rgba = 'rgba(' . $r . ', ' . $g . ', ' . $b . ', ' . $alpha . ')';
	return $rgba;
}
/**
 * Builds CSS for Post Grid block.
 *
 * @param array  $attr the blocks attr.
 * @param string $unique_id the blocks attr ID.
 */
function kt_blocks_pro_post_grid_css( $attr, $unique_id ) {
	$css = '';
	// Image.
	if ( isset( $attr['imagePadding'] ) ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kadence-post-image {';
		if ( isset( $attr['imagePadding'] ) && is_array( $attr['imagePadding'] ) ) {
			$css .= 'padding:' . $attr['imagePadding'][0] . 'px ' . $attr['imagePadding'][1] . 'px ' . $attr['imagePadding'][2] . 'px ' . $attr['imagePadding'][3] . 'px;';
		}
		$css .= '}';
	}
	if ( isset( $attr['sideImageWidth'] ) && isset( $attr['alignImage'] ) && 'left' === $attr['alignImage'] ) {
		if ( isset( $attr['displayImage'] ) && false === $attr['displayImage'] ) {
			// Don't want to add this for no image.
		} else {
			$css .= '.kt-post-loop' . $unique_id . ' .kt-feat-image-align-left {';
			$css .= 'grid-template-columns:' . $attr['sideImageWidth'] . '% auto;';
			$css .= '}';
		}
	}
	// Columns.
	if ( isset( $attr['columnGap'] ) && isset( $attr['layout'] ) && 'carousel' === $attr['layout'] ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kt-post-slider-item {';
			$css .= 'padding:0 ' . $attr['columnGap'] / 2 . 'px;';
		$css .= '}';
		$css .= '.kt-post-loop' . $unique_id . ' .kt-post-grid-layout-carousel-wrap {';
			$css .= 'margin-left:-' . $attr['columnGap'] / 2 . 'px;';
			$css .= 'margin-right:-' . $attr['columnGap'] / 2 . 'px;';
		$css .= '}';
		$css .= '.kt-post-loop' . $unique_id . ' .kt-post-grid-layout-carousel-wrap .slick-prev {';
			$css .= 'left:' . $attr['columnGap'] / 2 . 'px;';
		$css .= '}';
		$css .= '.kt-post-loop' . $unique_id . ' .kt-post-grid-layout-carousel-wrap .slick-next {';
			$css .= 'right:' . $attr['columnGap'] / 2 . 'px;';
		$css .= '}';
	}
	if ( isset( $attr['columnGap'] ) && isset( $attr['layout'] ) && 'masonry' === $attr['layout'] ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kt-post-grid-layout-masonry-wrap .kt-post-masonry-item {';
			$css .= 'padding-left:' . $attr['columnGap'] / 2 . 'px;';
			$css .= 'padding-right:' . $attr['columnGap'] / 2 . 'px;';
		$css .= '}';
		$css .= '.kt-post-loop' . $unique_id . ' .kt-post-grid-layout-masonry-wrap {';
			$css .= 'margin-left:-' . $attr['columnGap'] / 2 . 'px;';
			$css .= 'margin-right:-' . $attr['columnGap'] / 2 . 'px;';
		$css .= '}';
	}
	if ( isset( $attr['rowGap'] ) && isset( $attr['layout'] ) && 'masonry' === $attr['layout'] ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kt-post-grid-layout-masonry-wrap .kt-post-masonry-item {';
			$css .= 'padding-bottom:' . $attr['rowGap'] . 'px;';
		$css .= '}';
	}
	if ( isset( $attr['columnGap'] ) && isset( $attr['layout'] ) && 'grid' === $attr['layout'] ) {
		$rowgap = ( isset( $attr['rowGap'] ) ? $attr['rowGap'] : '30' );
		$css .= '.kt-post-loop' . $unique_id . ' .kt-post-grid-wrap {';
			$css .= 'grid-gap:' . $rowgap . 'px ' . $attr['columnGap'] . 'px;';
		$css .= '}';
	}
	// Container.
	if ( isset( $attr['backgroundColor'] ) || isset( $attr['borderColor'] ) || isset( $attr['borderWidth'] ) || isset( $attr['borderRadius'] ) || ( isset( $attr['displayShadow'] ) && true == $attr['displayShadow'] ) ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-grid-item {';
		if ( isset( $attr['backgroundColor'] ) ) {
			$css .= 'background-color:' . kbp_post_grid_color_output( $attr['backgroundColor'] ) . ';';
		}
		if ( isset( $attr['borderColor'] ) && ! empty( $attr['borderColor'] ) ) {
			$bcoloralpha = ( isset( $attr['borderOpacity'] ) ? $attr['borderOpacity'] : 1 );
			$bcolor = kbp_post_grid_color_output( $attr['borderColor'], $bcoloralpha );
			$css .= 'border-color:' . $bcolor . ';';
		}
		if ( isset( $attr['borderWidth'] ) && is_array( $attr['borderWidth'] ) ) {
			$css .= 'border-width:' . $attr['borderWidth'][0] . 'px ' . $attr['borderWidth'][1] . 'px ' . $attr['borderWidth'][2] . 'px ' . $attr['borderWidth'][3] . 'px;';
		}
		if ( isset( $attr['borderRadius'] ) && is_array( $attr['borderRadius'] ) ) {
			if ( ! empty( $attr['borderRadius'][0] ) || ! empty( $attr['borderRadius'][0] ) || ! empty( $attr['borderRadius'][0] ) || ! empty( $attr['borderRadius'][0] ) ) {
				$css .= 'overflow:hidden;';
			}
			$css .= 'border-radius:' . $attr['borderRadius'][0] . 'px ' . $attr['borderRadius'][1] . 'px ' . $attr['borderRadius'][2] . 'px ' . $attr['borderRadius'][3] . 'px;';
		}
		if ( isset( $attr['displayShadow'] ) && true == $attr['displayShadow'] && isset( $attr['shadow'] ) && is_array( $attr['shadow'] ) && isset( $attr['shadow'][0] ) && is_array( $attr['shadow'][0] ) ) {
			$css  .= 'box-shadow:' . ( isset( $attr['shadow'][0]['inset'] ) && true === $attr['shadow'][0]['inset'] ? 'inset ' : '' ) . ( isset( $attr['shadow'][0]['hOffset'] ) && is_numeric( $attr['shadow'][0]['hOffset'] ) ? $attr['shadow'][0]['hOffset'] : '0' ) . 'px ' . ( isset( $attr['shadow'][0]['vOffset'] ) && is_numeric( $attr['shadow'][0]['vOffset'] ) ? $attr['shadow'][0]['vOffset'] : '0' ) . 'px ' . ( isset( $attr['shadow'][0]['blur'] ) && is_numeric( $attr['shadow'][0]['blur'] ) ? $attr['shadow'][0]['blur'] : '14' ) . 'px ' . ( isset( $attr['shadow'][0]['spread'] ) && is_numeric( $attr['shadow'][0]['spread'] ) ? $attr['shadow'][0]['spread'] : '0' ) . 'px ' . kbp_post_grid_color_output( ( isset( $attr['shadow'][0]['color'] ) && ! empty( $attr['shadow'][0]['color'] ) ? $attr['shadow'][0]['color'] : '#000000' ), ( isset( $attr['shadow'][0]['opacity'] ) && is_numeric( $attr['shadow'][0]['opacity'] ) ? $attr['shadow'][0]['opacity'] : 0.2 ) ) . ';';
		} else if ( isset( $attr['displayShadow'] ) && true == $attr['displayShadow'] && ! isset( $attr['shadow'] ) ) {
			$css  .= 'box-shadow:0px 0px 14px 0px rgba(0, 0, 0, 0.2);';
		}
		$css .= '}';
	}
	if ( isset( $attr['containerPadding'] ) ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-grid-item .kt-blocks-post-grid-item-inner {';
		if ( isset( $attr['containerPadding'] ) && is_array( $attr['containerPadding'] ) ) {
			$css .= 'padding:' . $attr['containerPadding'][0] . 'px ' . $attr['containerPadding'][1] . 'px ' . $attr['containerPadding'][2] . 'px ' . $attr['containerPadding'][3] . 'px;';
		}
		$css .= '}';
	}
	if ( isset( $attr['textAlign'] ) && ! empty( $attr['textAlign'] ) ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-grid-item .kt-blocks-post-grid-item-inner {';
		$css .= 'text-align:' . $attr['textAlign'] . ';';
		$css .= '}';
		if ( 'center' === $attr['textAlign'] ) {
			$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-grid-item .kt-blocks-post-top-meta {';
			$css .= 'justify-content:' . $attr['textAlign'] . ';';
			$css .= '}';
		}
		if ( 'right' === $attr['textAlign'] ) {
			$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-grid-item .kt-blocks-post-top-meta {';
			$css .= 'justify-content: flex-end;';
			$css .= '}';
		}
		if ( 'justify' === $attr['textAlign'] ) {
			$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-grid-item .kt-blocks-post-top-meta {';
			$css .= 'justify-content: space-between;';
			$css .= '}';
		}
	}
	// Header
	if ( isset( $attr['headerBG'] ) || isset( $attr['headerPadding'] ) || isset( $attr['headerMargin'] ) ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-grid-item header {';
		if ( isset( $attr['headerBG'] ) ) {
			$headerbgcoloralpha = ( isset( $attr['headerBGOpacity'] ) ? $attr['headerBGOpacity'] : 1 );
			$headerbgcolor = kbp_post_grid_color_output( $attr['headerBG'], $headerbgcoloralpha );
			$css .= 'background-color:' . $headerbgcolor . ';';
		}
		if ( isset( $attr['headerPadding'] ) && is_array( $attr['headerPadding'] ) ) {
			$css .= 'padding:' . $attr['headerPadding'][0] . 'px ' . $attr['headerPadding'][1] . 'px ' . $attr['headerPadding'][2] . 'px ' . $attr['headerPadding'][3] . 'px;';
		}
		if ( isset( $attr['headerMargin'] ) && is_array( $attr['headerMargin'] ) ) {
			$css .= 'margin:' . $attr['headerMargin'][0] . 'px ' . $attr['headerMargin'][1] . 'px ' . $attr['headerMargin'][2] . 'px ' . $attr['headerMargin'][3] . 'px;';
		}
		$css .= '}';
	}
	// Above Title.
	if ( isset( $attr['aboveColor'] ) || isset( $attr['aboveFont'] ) ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-grid-item .kt-blocks-above-categories {';
		if ( isset( $attr['aboveColor'] ) ) {
			$css .= 'color:' . kbp_post_grid_color_output( $attr['aboveColor'] ) . ';';
		}
		if ( isset( $attr['aboveFont'] ) && is_array( $attr['aboveFont'] ) && isset( $attr['aboveFont'][0] ) && is_array( $attr['aboveFont'][0] ) ) {
			$above_font = $attr['aboveFont'][0];
			if ( isset( $above_font['size'] ) && is_array( $above_font['size'] ) && ! empty(  $above_font['size'][0] ) ) {
				$css .= 'font-size:' . $above_font['size'][0] . ( ! isset( $above_font['sizeType'] ) ? 'px' : $above_font['sizeType'] ) . ';';
			}
			if ( isset( $above_font['lineHeight'] ) && is_array( $above_font['lineHeight'] ) && ! empty( $above_font['lineHeight'][0] ) ) {
				$css .= 'line-height:' . $above_font['lineHeight'][0] . ( ! isset( $above_font['lineType'] ) ? 'px' : $above_font['lineType'] ) . ';';
			}
			if ( isset( $above_font['letterSpacing'] ) && ! empty( $above_font['letterSpacing'] ) ) {
				$css .= 'letter-spacing:' . $above_font['letterSpacing'] .  'px;';
			}
			if ( isset( $above_font['textTransform'] ) && ! empty( $above_font['textTransform'] ) ) {
				$css .= 'text-transform:' . $above_font['textTransform'] .  ';';
			}
			if ( isset( $above_font['family'] ) && ! empty( $above_font['family'] ) ) {
				$css .= 'font-family:' . $above_font['family'] .  ';';
			}
			if ( isset( $above_font['style'] ) && ! empty( $above_font['style'] ) ) {
				$css .= 'font-style:' . $above_font['style'] .  ';';
			}
			if ( isset( $above_font['weight'] ) && ! empty( $above_font['weight'] ) ) {
				$css .= 'font-weight:' . $above_font['weight'] .  ';';
			}
		}
		$css .= '}';
		if ( isset( $attr['aboveFont'] ) && is_array( $attr['aboveFont'] ) && isset( $attr['aboveFont'][0] ) && is_array( $attr['aboveFont'][0] ) && ( ( isset( $attr['aboveFont'][0]['size'] ) && is_array( $attr
		['aboveFont'][0]['size'] ) && isset( $attr['aboveFont'][0]['size'][1] ) && ! empty( $attr['aboveFont'][0]['size'][1] ) ) || ( isset( $attr['aboveFont'][0]['lineHeight'] ) && is_array( $attr
		['aboveFont'][0]['lineHeight'] ) && isset( $attr['aboveFont'][0]['lineHeight'][1] ) && ! empty( $attr['aboveFont'][0]['lineHeight'][1] ) ) ) ) {
			$css .= '@media (min-width: 767px) and (max-width: 1024px) {';
			$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-grid-item .kt-blocks-above-categories {';
				if ( isset( $attr['aboveFont'][0]['size'][1] ) && ! empty( $attr['aboveFont'][0]['size'][1] ) ) {
					$css .= 'font-size:' . $attr['aboveFont'][0]['size'][1] . ( ! isset( $attr['aboveFont'][0]['sizeType'] ) ? 'px' : $attr['aboveFont'][0]['sizeType'] ) . ';';
				}
				if ( isset( $attr['aboveFont'][0]['lineHeight'][1] ) && ! empty( $attr['aboveFont'][0]['lineHeight'][1] ) ) {
					$css .= 'line-height:' . $attr['aboveFont'][0]['lineHeight'][1] . ( ! isset( $attr['aboveFont'][0]['lineType'] ) ? 'px' : $attr['aboveFont'][0]['lineType'] ) . ';';
				}
			$css .= '}';
			$css .= '}';
		}
		if ( isset( $attr['aboveFont'] ) && is_array( $attr['aboveFont'] ) && isset( $attr['aboveFont'][0] ) && is_array( $attr['aboveFont'][0] ) && ( ( isset( $attr['aboveFont'][0]['size'] ) && is_array( $attr
		['aboveFont'][0]['size'] ) && isset( $attr['aboveFont'][0]['size'][2] ) && ! empty( $attr['aboveFont'][0]['size'][2] ) ) || ( isset( $attr['aboveFont'][0]['lineHeight'] ) && is_array( $attr
		['aboveFont'][0]['lineHeight'] ) && isset( $attr['aboveFont'][0]['lineHeight'][2] ) && ! empty( $attr['aboveFont'][0]['lineHeight'][2] ) ) ) ) {
			$css .= '@media (max-width: 767px) {';
			$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-grid-item .kt-blocks-above-categories {';
			if ( isset( $attr['aboveFont'][0]['size'][2] ) && ! empty( $attr['aboveFont'][0]['size'][2] ) ) {
				$css .= 'font-size:' . $attr['aboveFont'][0]['size'][2] . ( ! isset( $attr['aboveFont'][0]['sizeType'] ) ? 'px' : $attr['aboveFont'][0]['sizeType'] ) . ';';
			}
			if ( isset( $attr['aboveFont'][0]['lineHeight'][2] ) && ! empty( $attr['aboveFont'][0]['lineHeight'][2] ) ) {
				$css .= 'line-height:' . $attr['aboveFont'][0]['lineHeight'][2] . ( ! isset( $attr['aboveFont'][0]['lineType'] ) ? 'px' : $attr['aboveFont'][0]['lineType'] ) . ';';
			}
			$css .= '}';
			$css .= '}';
		}
	}
	if ( isset( $attr['aboveLinkColor'] ) ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-grid-item .kt-blocks-above-categories a {';
			$css .= 'color:' . kbp_post_grid_color_output( $attr['aboveLinkColor'] ) . ';';
		$css .= '}';
	}
	if ( isset( $attr['aboveLinkHoverColor'] ) ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-grid-item .kt-blocks-above-categories a:hover {';
			$css .= 'color:' . kbp_post_grid_color_output( $attr['aboveLinkHoverColor'] ) . ';';
		$css .= '}';
	}
	// Title
	if ( isset( $attr['titleColor'] ) || isset( $attr['titleFont'] ) ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-grid-item .entry-title {';
		if ( isset( $attr['titleColor'] ) && ! empty( $attr['titleColor'] ) ) {
			$css .= 'color:' . kbp_post_grid_color_output( $attr['titleColor'] ) . ';';
		}
		if ( isset( $attr['titlePadding'] ) && is_array( $attr['titlePadding'] ) ) {
			$css .= 'padding:' . $attr['titlePadding'][0] . 'px ' . $attr['titlePadding'][1] . 'px ' . $attr['titlePadding'][2] . 'px ' . $attr['titlePadding'][3] . 'px;';
		} else if ( ! isset( $attr['titlePadding'] ) ) {
			$css .= 'padding:5px 0px 10px 0px;';
		}
		if ( isset( $attr['titleMargin'] ) && is_array( $attr['titleMargin'] ) ) {
			$css .= 'margin:' . $attr['titleMargin'][0] . 'px ' . $attr['titleMargin'][1] . 'px ' . $attr['titleMargin'][2] . 'px ' . $attr['titleMargin'][3] . 'px;';
		} else if ( ! isset( $attr['titleMargin'] ) ) {
			$css .= 'margin:0px 0px 0px 0px;';
		}
		if ( isset( $attr['titleFont'] ) && is_array( $attr['titleFont'] ) && isset( $attr['titleFont'][0] ) && is_array( $attr['titleFont'][0] ) ) {
			$title_font = $attr['titleFont'][0];
			if ( isset( $title_font['size'] ) && is_array( $title_font['size'] ) && ! empty( $title_font['size'][0] ) ) {
				$css .= 'font-size:' . $title_font['size'][0] . ( ! isset( $title_font['sizeType'] ) ? 'px' : $title_font['sizeType'] ) . ';';
			}
			if ( isset( $title_font['lineHeight'] ) && is_array( $title_font['lineHeight'] ) && ! empty( $title_font['lineHeight'][0] ) ) {
				$css .= 'line-height:' . $title_font['lineHeight'][0] . ( ! isset( $title_font['lineType'] ) ? 'px' : $title_font['lineType'] ) . ';';
			}
			if ( isset( $title_font['letterSpacing'] ) && ! empty( $title_font['letterSpacing'] ) ) {
				$css .= 'letter-spacing:' . $title_font['letterSpacing'] .  'px;';
			}
			if ( isset( $title_font['textTransform'] ) && ! empty( $title_font['textTransform'] ) ) {
				$css .= 'text-transform:' . $title_font['textTransform'] .  ';';
			}
			if ( isset( $title_font['family'] ) && ! empty( $title_font['family'] ) ) {
				$css .= 'font-family:' . $title_font['family'] .  ';';
			}
			if ( isset( $title_font['style'] ) && ! empty( $title_font['style'] ) ) {
				$css .= 'font-style:' . $title_font['style'] .  ';';
			}
			if ( isset( $title_font['weight'] ) && ! empty( $title_font['weight'] ) ) {
				$css .= 'font-weight:' . $title_font['weight'] .  ';';
			}
		}
		$css .= '}';
		if ( isset( $attr['titleFont'] ) && is_array( $attr['titleFont'] ) && isset( $attr['titleFont'][0] ) && is_array( $attr['titleFont'][0] ) && ( ( isset( $attr['titleFont'][0]['size'] ) && is_array( $attr
		['titleFont'][0]['size'] ) && isset( $attr['titleFont'][0]['size'][1] ) && ! empty( $attr['titleFont'][0]['size'][1] ) ) || ( isset( $attr['titleFont'][0]['lineHeight'] ) && is_array( $attr
		['titleFont'][0]['lineHeight'] ) && isset( $attr['titleFont'][0]['lineHeight'][1] ) && ! empty( $attr['titleFont'][0]['lineHeight'][1] ) ) ) ) {
			$css .= '@media (min-width: 767px) and (max-width: 1024px) {';
			$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-grid-item .entry-title {';
			if ( isset( $attr['titleFont'][0]['size'][1] ) && ! empty( $attr['titleFont'][0]['size'][1] ) ) {
				$css .= 'font-size:' . $attr['titleFont'][0]['size'][1] . ( ! isset( $attr['titleFont'][0]['sizeType'] ) ? 'px' : $attr['titleFont'][0]['sizeType'] ) . ';';
			}
			if ( isset( $attr['titleFont'][0]['lineHeight'][1] ) && ! empty( $attr['titleFont'][0]['lineHeight'][1] ) ) {
				$css .= 'line-height:' . $attr['titleFont'][0]['lineHeight'][1] . ( ! isset( $attr['titleFont'][0]['lineType'] ) ? 'px' : $attr['titleFont'][0]['lineType'] ) . ';';
			}
			$css .= '}';
			$css .= '}';
		}
		if ( isset( $attr['titleFont'] ) && is_array( $attr['titleFont'] ) && isset( $attr['titleFont'][0] ) && is_array( $attr['titleFont'][0] ) && ( ( isset( $attr['titleFont'][0]['size'] ) && is_array( $attr
		['titleFont'][0]['size'] ) && isset( $attr['titleFont'][0]['size'][2] ) && ! empty( $attr['titleFont'][0]['size'][2] ) ) || ( isset( $attr['titleFont'][0]['lineHeight'] ) && is_array( $attr
		['titleFont'][0]['lineHeight'] ) && isset( $attr['titleFont'][0]['lineHeight'][2] ) && ! empty( $attr['titleFont'][0]['lineHeight'][2] ) ) ) ) {
			$css .= '@media (max-width: 767px) {';
			$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-grid-item .entry-title {';
				if ( isset( $attr['titleFont'][0]['size'][2] ) && ! empty( $attr['titleFont'][0]['size'][2] ) ) {
					$css .= 'font-size:' . $attr['titleFont'][0]['size'][2] . ( ! isset( $attr['titleFont'][0]['sizeType'] ) ? 'px' : $attr['titleFont'][0]['sizeType'] ) . ';';
				}
				if ( isset( $attr['titleFont'][0]['lineHeight'][2] ) && ! empty( $attr['titleFont'][0]['lineHeight'][2] ) ) {
					$css .= 'line-height:' . $attr['titleFont'][0]['lineHeight'][2] . ( ! isset( $attr['titleFont'][0]['lineType'] ) ? 'px' : $attr['titleFont'][0]['lineType'] ) . ';';
				}
			$css .= '}';
			$css .= '}';
		}
	}
	// Meta
	if ( isset( $attr['metaColor'] ) || isset( $attr['metaFont'] ) ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-grid-item .kt-blocks-post-top-meta {';
		if ( isset( $attr['metaColor'] ) && ! empty( $attr['metaColor'] ) ) {
			$css .= 'color:' . kbp_post_grid_color_output( $attr['metaColor'] ) . ';';
		}
		if ( isset( $attr['metaFont'] ) && is_array( $attr['metaFont'] ) && isset( $attr['metaFont'][0] ) && is_array( $attr['metaFont'][0] ) ) {
			$title_font = $attr['metaFont'][0];
			if ( isset( $title_font['size'] ) && is_array( $title_font['size'] ) && ! empty( $title_font['size'][0] ) ) {
				$css .= 'font-size:' . $title_font['size'][0] . ( ! isset( $title_font['sizeType'] ) ? 'px' : $title_font['sizeType'] ) . ';';
			}
			if ( isset( $title_font['lineHeight'] ) && is_array( $title_font['lineHeight'] ) && ! empty( $title_font['lineHeight'][0] ) ) {
				$css .= 'line-height:' . $title_font['lineHeight'][0] . ( ! isset( $title_font['lineType'] ) ? 'px' : $title_font['lineType'] ) . ';';
			}
			if ( isset( $title_font['letterSpacing'] ) && ! empty( $title_font['letterSpacing'] ) ) {
				$css .= 'letter-spacing:' . $title_font['letterSpacing'] .  'px;';
			}
			if ( isset( $title_font['textTransform'] ) && ! empty( $title_font['textTransform'] ) ) {
				$css .= 'text-transform:' . $title_font['textTransform'] .  ';';
			}
			if ( isset( $title_font['family'] ) && ! empty( $title_font['family'] ) ) {
				$css .= 'font-family:' . $title_font['family'] .  ';';
			}
			if ( isset( $title_font['style'] ) && ! empty( $title_font['style'] ) ) {
				$css .= 'font-style:' . $title_font['style'] .  ';';
			}
			if ( isset( $title_font['weight'] ) && ! empty( $title_font['weight'] ) ) {
				$css .= 'font-weight:' . $title_font['weight'] .  ';';
			}
		}
		$css .= '}';
		if ( isset( $attr['metaFont'] ) && is_array( $attr['metaFont'] ) && isset( $attr['metaFont'][0] ) && is_array( $attr['metaFont'][0] ) && ( ( isset( $attr['metaFont'][0]['size'] ) && is_array( $attr
		['metaFont'][0]['size'] ) && isset( $attr['metaFont'][0]['size'][1] ) && ! empty( $attr['metaFont'][0]['size'][1] ) ) || ( isset( $attr['metaFont'][0]['lineHeight'] ) && is_array( $attr
		['metaFont'][0]['lineHeight'] ) && isset( $attr['metaFont'][0]['lineHeight'][1] ) && ! empty( $attr['metaFont'][0]['lineHeight'][1] ) ) ) ) {
			$css .= '@media (min-width: 767px) and (max-width: 1024px) {';
			$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-grid-item .kt-blocks-post-top-meta {';
			if ( isset( $attr['metaFont'][0]['size'][1] ) && ! empty( $attr['metaFont'][0]['size'][1] ) ) {
				$css .= 'font-size:' . $attr['metaFont'][0]['size'][1] . ( ! isset( $attr['metaFont'][0]['sizeType'] ) ? 'px' : $attr['metaFont'][0]['sizeType'] ) . ';';
			}
			if ( isset( $attr['metaFont'][0]['lineHeight'][1] ) && ! empty( $attr['metaFont'][0]['lineHeight'][1] ) ) {
				$css .= 'line-height:' . $attr['metaFont'][0]['lineHeight'][1] . ( ! isset( $attr['metaFont'][0]['lineType'] ) ? 'px' : $attr['metaFont'][0]['lineType'] ) . ';';
			}
			$css .= '}';
			$css .= '}';
		}
		if ( isset( $attr['metaFont'] ) && is_array( $attr['metaFont'] ) && isset( $attr['metaFont'][0] ) && is_array( $attr['metaFont'][0] ) && ( ( isset( $attr['metaFont'][0]['size'] ) && is_array( $attr
		['metaFont'][0]['size'] ) && isset( $attr['metaFont'][0]['size'][2] ) && ! empty( $attr['metaFont'][0]['size'][2] ) ) || ( isset( $attr['metaFont'][0]['lineHeight'] ) && is_array( $attr
		['metaFont'][0]['lineHeight'] ) && isset( $attr['metaFont'][0]['lineHeight'][2] ) && ! empty( $attr['metaFont'][0]['lineHeight'][2] ) ) ) ) {
			$css .= '@media (max-width: 767px) {';
			$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-grid-item .kt-blocks-post-top-meta {';
				if ( isset( $attr['metaFont'][0]['size'][2] ) && ! empty( $attr['metaFont'][0]['size'][2] ) ) {
					$css .= 'font-size:' . $attr['metaFont'][0]['size'][2] . ( ! isset( $attr['metaFont'][0]['sizeType'] ) ? 'px' : $attr['metaFont'][0]['sizeType'] ) . ';';
				}
				if ( isset( $attr['metaFont'][0]['lineHeight'][2] ) && ! empty( $attr['metaFont'][0]['lineHeight'][2] ) ) {
					$css .= 'line-height:' . $attr['metaFont'][0]['lineHeight'][2] . ( ! isset( $attr['metaFont'][0]['lineType'] ) ? 'px' : $attr['metaFont'][0]['lineType'] ) . ';';
				}
			$css .= '}';
			$css .= '}';
		}
	}
	if ( isset( $attr['metaLinkColor'] ) && ! empty( $attr['metaLinkColor'] ) ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-grid-item .kt-blocks-post-top-meta a {';
			$css .= 'color:' . kbp_post_grid_color_output( $attr['metaLinkColor'] ) . ';';
		$css .= '}';
	}
	if ( isset( $attr['metaLinkHoverColor'] ) && ! empty( $attr['metaLinkHoverColor'] ) ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-grid-item .kt-blocks-post-top-meta a:hover {';
			$css .= 'color:' . kbp_post_grid_color_output( $attr['metaLinkHoverColor'] ) . ';';
		$css .= '}';
	}
	// Body
	if ( isset( $attr['bodyBG'] ) || isset( $attr['bodyPadding'] ) || isset( $attr['bodyMargin'] ) || isset( $attr['excerptFont'] ) || isset( $attr['excerptColor'] ) ) {
		$css .= '.kt-post-loop' . $unique_id . ' .entry-content {';
		if ( isset( $attr['bodyBG'] ) ) {
			$bodybgcoloralpha = ( isset( $attr['bodyBGOpacity'] ) ? $attr['bodyBGOpacity'] : 1 );
			$bodybgcolor = kbp_post_grid_color_output( $attr['bodyBG'], $bodybgcoloralpha );
			$css .= 'background-color:' . $bodybgcolor . ';';
		}
		if ( isset( $attr['excerptColor'] ) && ! empty( $attr['excerptColor'] ) ) {
			$css .= 'color:' . kbp_post_grid_color_output( $attr['excerptColor'] ) . ';';
		}
		if ( isset( $attr['bodyPadding'] ) && is_array( $attr['bodyPadding'] ) ) {
			$css .= 'padding:' . $attr['bodyPadding'][0] . 'px ' . $attr['bodyPadding'][1] . 'px ' . $attr['bodyPadding'][2] . 'px ' . $attr['bodyPadding'][3] . 'px;';
		}
		if ( isset( $attr['bodyMargin'] ) && is_array( $attr['bodyMargin'] ) ) {
			$css .= 'margin:' . $attr['bodyMargin'][0] . 'px ' . $attr['bodyMargin'][1] . 'px ' . $attr['bodyMargin'][2] . 'px ' . $attr['bodyMargin'][3] . 'px;';
		}
		if ( isset( $attr['excerptFont'] ) && is_array( $attr['excerptFont'] ) && isset( $attr['excerptFont'][0] ) && is_array( $attr['excerptFont'][0] ) ) {
			$excerpt_font = $attr['excerptFont'][0];
			if ( isset( $excerpt_font['size'] ) && is_array( $excerpt_font['size'] ) && ! empty( $excerpt_font['size'][0] ) ) {
				$css .= 'font-size:' . $excerpt_font['size'][0] . ( ! isset( $excerpt_font['sizeType'] ) ? 'px' : $excerpt_font['sizeType'] ) . ';';
			}
			if ( isset( $excerpt_font['lineHeight'] ) && is_array( $excerpt_font['lineHeight'] ) && ! empty( $excerpt_font['lineHeight'][0] ) ) {
				$css .= 'line-height:' . $excerpt_font['lineHeight'][0] . ( ! isset( $excerpt_font['lineType'] ) ? 'px' : $excerpt_font['lineType'] ) . ';';
			}
			if ( isset( $excerpt_font['letterSpacing'] ) && ! empty( $excerpt_font['letterSpacing'] ) ) {
				$css .= 'letter-spacing:' . $excerpt_font['letterSpacing'] .  'px;';
			}
			if ( isset( $excerpt_font['family'] ) && ! empty( $excerpt_font['family'] ) ) {
				$css .= 'font-family:' . $excerpt_font['family'] .  ';';
			}
			if ( isset( $excerpt_font['style'] ) && ! empty( $excerpt_font['style'] ) ) {
				$css .= 'font-style:' . $excerpt_font['style'] .  ';';
			}
			if ( isset( $excerpt_font['weight'] ) && ! empty( $excerpt_font['weight'] ) ) {
				$css .= 'font-weight:' . $excerpt_font['weight'] .  ';';
			}
		}
		$css .= '}';
		if ( isset( $attr['excerptFont'] ) && is_array( $attr['excerptFont'] ) && isset( $attr['excerptFont'][0] ) && is_array( $attr['excerptFont'][0] ) && ( ( isset( $attr['excerptFont'][0]['size'] ) && is_array( $attr
		['excerptFont'][0]['size'] ) && isset( $attr['excerptFont'][0]['size'][1] ) && ! empty( $attr['excerptFont'][0]['size'][1] ) ) || ( isset( $attr['excerptFont'][0]['lineHeight'] ) && is_array( $attr
		['excerptFont'][0]['lineHeight'] ) && isset( $attr['excerptFont'][0]['lineHeight'][1] ) && ! empty( $attr['excerptFont'][0]['lineHeight'][1] ) ) ) ) {
			$css .= '@media (min-width: 767px) and (max-width: 1024px) {';
			$css .= '.kt-post-loop' . $unique_id . ' .entry-content {';
			if ( isset( $attr['excerptFont'][0]['size'][1] ) && ! empty( $attr['excerptFont'][0]['size'][1] ) ) {
				$css .= 'font-size:' . $attr['excerptFont'][0]['size'][1] . ( ! isset( $attr['excerptFont'][0]['sizeType'] ) ? 'px' : $attr['excerptFont'][0]['sizeType'] ) . ';';
			}
			if ( isset( $attr['excerptFont'][0]['lineHeight'][1] ) && ! empty( $attr['excerptFont'][0]['lineHeight'][1] ) ) {
				$css .= 'line-height:' . $attr['excerptFont'][0]['lineHeight'][1] . ( ! isset( $attr['excerptFont'][0]['lineType'] ) ? 'px' : $attr['excerptFont'][0]['lineType'] ) . ';';
			}
			$css .= '}';
			$css .= '}';
		}
		if ( isset( $attr['excerptFont'] ) && is_array( $attr['excerptFont'] ) && isset( $attr['excerptFont'][0] ) && is_array( $attr['excerptFont'][0] ) && ( ( isset( $attr['excerptFont'][0]['size'] ) && is_array( $attr
		['excerptFont'][0]['size'] ) && isset( $attr['excerptFont'][0]['size'][2] ) && ! empty( $attr['excerptFont'][0]['size'][2] ) ) || ( isset( $attr['excerptFont'][0]['lineHeight'] ) && is_array( $attr
		['excerptFont'][0]['lineHeight'] ) && isset( $attr['excerptFont'][0]['lineHeight'][2] ) && ! empty( $attr['excerptFont'][0]['lineHeight'][2] ) ) ) ) {
			$css .= '@media (max-width: 767px) {';
			$css .= '.kt-post-loop' . $unique_id . ' .entry-content {';
				if ( isset( $attr['excerptFont'][0]['size'][2] ) && ! empty( $attr['excerptFont'][0]['size'][2] ) ) {
					$css .= 'font-size:' . $attr['excerptFont'][0]['size'][2] . ( ! isset( $attr['excerptFont'][0]['sizeType'] ) ? 'px' : $attr['excerptFont'][0]['sizeType'] ) . ';';
				}
				if ( isset( $attr['excerptFont'][0]['lineHeight'][2] ) && ! empty( $attr['excerptFont'][0]['lineHeight'][2] ) ) {
					$css .= 'line-height:' . $attr['excerptFont'][0]['lineHeight'][2] . ( ! isset( $attr['excerptFont'][0]['lineType'] ) ? 'px' : $attr['excerptFont'][0]['lineType'] ) . ';';
				}
			$css .= '}';
			$css .= '}';
		}
	}
	// Footer.
	if ( isset( $attr['footerBG'] ) || isset( $attr['footerPadding'] ) || isset( $attr['footerMargin'] ) || isset( $attr['footerBorderColor'] ) || isset( $attr['footerBorderWidth'] ) || isset( $attr['footerColor'] ) || isset( $attr['footerFont'] ) ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-footer {';
		if ( isset( $attr['footerBG'] ) && ! empty( $attr['footerBG'] ) ) {
			$footerbgcoloralpha = ( isset( $attr['footerBGOpacity'] ) ? $attr['footerBGOpacity'] : 1 );
			$footerbgcolor = kbp_post_grid_color_output( $attr['footerBG'], $footerbgcoloralpha );
			$css .= 'background-color:' . $footerbgcolor . ';';
		}
		if ( isset( $attr['footerBorderColor'] ) && ! empty( $attr['footerBorderColor'] ) ) {
			$footerbcoloralpha = ( isset( $attr['footerBorderOpacity'] ) ? $attr['footerBorderOpacity'] : 1 );
			$footerbcolor = kbp_post_grid_color_output( $attr['footerBorderColor'], $footerbcoloralpha );
			$css .= 'border-color:' . $footerbcolor . ';';
		}
		if ( isset( $attr['footerColor'] ) && ! empty( $attr['footerColor'] ) ) {
			$css .= 'color:' . kbp_post_grid_color_output( $attr['footerColor'] ) . ';';
		}
		if ( isset( $attr['footerBorderWidth'] ) && is_array( $attr['footerBorderWidth'] ) ) {
			$css .= 'border-width:' . $attr['footerBorderWidth'][0] . 'px ' . $attr['footerBorderWidth'][1] . 'px ' . $attr['footerBorderWidth'][2] . 'px ' . $attr['footerBorderWidth'][3] . 'px;';
		}
		if ( isset( $attr['footerPadding'] ) && is_array( $attr['footerPadding'] ) ) {
			$css .= 'padding:' . $attr['footerPadding'][0] . 'px ' . $attr['footerPadding'][1] . 'px ' . $attr['footerPadding'][2] . 'px ' . $attr['footerPadding'][3] . 'px;';
		}
		if ( isset( $attr['footerMargin'] ) && is_array( $attr['footerMargin'] ) ) {
			$css .= 'margin:' . $attr['footerMargin'][0] . 'px ' . $attr['footerMargin'][1] . 'px ' . $attr['footerMargin'][2] . 'px ' . $attr['footerMargin'][3] . 'px;';
		}
		if ( isset( $attr['footerFont'] ) && is_array( $attr['footerFont'] ) && isset( $attr['footerFont'][0] ) && is_array( $attr['footerFont'][0] ) ) {
			$footer_font = $attr['footerFont'][0];
			if ( isset( $footer_font['size'] ) && is_array( $footer_font['size'] ) && ! empty( $footer_font['size'][0] ) ) {
				$css .= 'font-size:' . $footer_font['size'][0] . ( ! isset( $footer_font['sizeType'] ) ? 'px' : $footer_font['sizeType'] ) . ';';
			}
			if ( isset( $footer_font['lineHeight'] ) && is_array( $footer_font['lineHeight'] ) && ! empty( $footer_font['lineHeight'][0] ) ) {
				$css .= 'line-height:' . $footer_font['lineHeight'][0] . ( ! isset( $footer_font['lineType'] ) ? 'px' : $footer_font['lineType'] ) . ';';
			}
			if ( isset( $footer_font['letterSpacing'] ) && ! empty( $footer_font['letterSpacing'] ) ) {
				$css .= 'letter-spacing:' . $footer_font['letterSpacing'] .  'px;';
			}
			if ( isset( $footer_font['textTransform'] ) && ! empty( $footer_font['textTransform'] ) ) {
				$css .= 'text-transform:' . $footer_font['textTransform'] .  ';';
			}
			if ( isset( $footer_font['family'] ) && ! empty( $footer_font['family'] ) ) {
				$css .= 'font-family:' . $footer_font['family'] .  ';';
			}
			if ( isset( $footer_font['style'] ) && ! empty( $footer_font['style'] ) ) {
				$css .= 'font-style:' . $footer_font['style'] .  ';';
			}
			if ( isset( $footer_font['weight'] ) && ! empty( $footer_font['weight'] ) ) {
				$css .= 'font-weight:' . $footer_font['weight'] .  ';';
			}
		}
		$css .= '}';
		if ( isset( $attr['footerAlignBottom'] ) && true === isset( $attr['footerAlignBottom'] ) && isset( $attr['footerMargin'] ) && is_array( $attr['footerMargin'] ) ) {
			$css .= '.kt-post-loop' . $unique_id . ' .entry-content:after {';
			$css .= 'height:' . $attr['footerMargin'][0] . 'px;';
			$css .= '}';
		}
		if ( isset( $attr['footerFont'] ) && is_array( $attr['footerFont'] ) && isset( $attr['footerFont'][0] ) && is_array( $attr['footerFont'][0] ) && ( ( isset( $attr['footerFont'][0]['size'] ) && is_array( $attr
		['footerFont'][0]['size'] ) && isset( $attr['footerFont'][0]['size'][1] ) && ! empty( $attr['footerFont'][0]['size'][1] ) ) || ( isset( $attr['footerFont'][0]['lineHeight'] ) && is_array( $attr
		['footerFont'][0]['lineHeight'] ) && isset( $attr['footerFont'][0]['lineHeight'][1] ) && ! empty( $attr['footerFont'][0]['lineHeight'][1] ) ) ) ) {
			$css .= '@media (min-width: 767px) and (max-width: 1024px) {';
			$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-footer {';
			if ( isset( $attr['footerFont'][0]['size'][1] ) && ! empty( $attr['footerFont'][0]['size'][1] ) ) {
				$css .= 'font-size:' . $attr['footerFont'][0]['size'][1] . ( ! isset( $attr['footerFont'][0]['sizeType'] ) ? 'px' : $attr['footerFont'][0]['sizeType'] ) . ';';
			}
			if ( isset( $attr['footerFont'][0]['lineHeight'][1] ) && ! empty( $attr['footerFont'][0]['lineHeight'][1] ) ) {
				$css .= 'line-height:' . $attr['footerFont'][0]['lineHeight'][1] . ( ! isset( $attr['footerFont'][0]['lineType'] ) ? 'px' : $attr['footerFont'][0]['lineType'] ) . ';';
			}
			$css .= '}';
			$css .= '}';
		}
		if ( isset( $attr['footerFont'] ) && is_array( $attr['footerFont'] ) && isset( $attr['footerFont'][0] ) && is_array( $attr['footerFont'][0] ) && ( ( isset( $attr['footerFont'][0]['size'] ) && is_array( $attr
		['footerFont'][0]['size'] ) && isset( $attr['footerFont'][0]['size'][2] ) && ! empty( $attr['footerFont'][0]['size'][2] ) ) || ( isset( $attr['footerFont'][0]['lineHeight'] ) && is_array( $attr
		['footerFont'][0]['lineHeight'] ) && isset( $attr['footerFont'][0]['lineHeight'][2] ) && ! empty( $attr['footerFont'][0]['lineHeight'][2] ) ) ) ) {
			$css .= '@media (max-width: 767px) {';
			$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-footer {';
				if ( isset( $attr['footerFont'][0]['size'][2] ) && ! empty( $attr['footerFont'][0]['size'][2] ) ) {
					$css .= 'font-size:' . $attr['footerFont'][0]['size'][2] . ( ! isset( $attr['footerFont'][0]['sizeType'] ) ? 'px' : $attr['footerFont'][0]['sizeType'] ) . ';';
				}
				if ( isset( $attr['footerFont'][0]['lineHeight'][2] ) && ! empty( $attr['footerFont'][0]['lineHeight'][2] ) ) {
					$css .= 'line-height:' . $attr['footerFont'][0]['lineHeight'][2] . ( ! isset( $attr['footerFont'][0]['lineType'] ) ? 'px' : $attr['footerFont'][0]['lineType'] ) . ';';
				}
			$css .= '}';
			$css .= '}';
		}
	}
	if ( isset( $attr['footerLinkColor'] ) && ! empty( $attr['footerLinkColor'] ) ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-footer a {';
			$css .= 'color:' . kbp_post_grid_color_output( $attr['footerLinkColor'] ) . ';';
		$css .= '}';
	}
	if ( isset( $attr['footerLinkHoverColor'] ) && ! empty( $attr['footerLinkHoverColor'] ) ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kt-blocks-post-footer a:hover {';
			$css .= 'color:' . kbp_post_grid_color_output( $attr['footerLinkHoverColor'] ) . ';';
		$css .= '}';
	}
	// Read More.
	if ( isset( $attr['displayReadMore'] ) && true == $attr['displayReadMore'] ) {
		$css .= '.kt-post-loop' . $unique_id . ' .entry-content .kt-blocks-post-readmore {';
		if ( isset( $attr['readMoreBackground'] ) && ! empty( $attr['readMoreBackground'] ) ) {
			$css .= 'background-color:' . kbp_post_grid_color_output( $attr['readMoreBackground'] ) . ';';
		} else if ( ! isset( $attr['readMoreBackground'] ) ) {
			$css .= 'background-color:#444444;';
		}
		if ( isset( $attr['readMoreBorderColor'] ) && ! empty( $attr['readMoreBorderColor'] ) ) {
			$css .= 'border-color:' . kbp_post_grid_color_output( $attr['readMoreBorderColor'] ) . ';';
		} else if ( ! isset( $attr['readMoreBorderColor'] ) ) {
			$css .= 'border-color:#444444;';
		}
		if ( isset( $attr['readMoreColor'] ) && ! empty( $attr['readMoreColor'] ) ) {
			$css .= 'color:' . kbp_post_grid_color_output( $attr['readMoreColor'] ) . ';';
		} else if ( ! isset( $attr['readMoreColor'] ) ) {
			$css .= 'color:#ffffff;';
		}
		if ( isset( $attr['readMoreBorder'] ) && ! empty( $attr['readMoreBorder'] ) ) {
			$css .= 'border-width:' . $attr['readMoreBorder'] . 'px;';
		}
		if ( isset( $attr['readMoreBorderRadius'] ) && ! empty( $attr['readMoreBorderRadius'] ) ) {
			$css .= 'border-radius:' . $attr['readMoreBorderRadius'] . 'px;';
		}
		if ( isset( $attr['readMorePadding'] ) && is_array( $attr['readMorePadding'] ) ) {
			$css .= 'padding:' . $attr['readMorePadding'][0] . 'px ' . $attr['readMorePadding'][1] . 'px ' . $attr['readMorePadding'][2] . 'px ' . $attr['readMorePadding'][3] . 'px;';
		} else if ( ! isset( $attr['readMorePadding'] ) ) {
			$css .= 'padding:4px 8px 4px 8px;';
		}
		if ( isset( $attr['readMoreMargin'] ) && is_array( $attr['readMoreMargin'] ) ) {
			$css .= 'margin:' . $attr['readMoreMargin'][0] . 'px ' . $attr['readMoreMargin'][1] . 'px ' . $attr['readMoreMargin'][2] . 'px ' . $attr['readMoreMargin'][3] . 'px;';
		}
		if ( isset( $attr['readMoreFont'] ) && is_array( $attr['readMoreFont'] ) && isset( $attr['readMoreFont'][0] ) && is_array( $attr['readMoreFont'][0] ) ) {
			$readmore_font = $attr['readMoreFont'][0];
			if ( isset( $readmore_font['size'] ) && is_array( $readmore_font['size'] ) && ! empty( $readmore_font['size'][0] ) ) {
				$css .= 'font-size:' . $readmore_font['size'][0] . ( ! isset( $readmore_font['sizeType'] ) ? 'px' : $readmore_font['sizeType'] ) . ';';
			}
			if ( isset( $readmore_font['lineHeight'] ) && is_array( $readmore_font['lineHeight'] ) && ! empty( $readmore_font['lineHeight'][0] ) ) {
				$css .= 'line-height:' . $readmore_font['lineHeight'][0] . ( ! isset( $readmore_font['lineType'] ) ? 'px' : $readmore_font['lineType'] ) . ';';
			}
			if ( isset( $readmore_font['letterSpacing'] ) && ! empty( $readmore_font['letterSpacing'] ) ) {
				$css .= 'letter-spacing:' . $readmore_font['letterSpacing'] .  'px;';
			}
			if ( isset( $readmore_font['family'] ) && ! empty( $readmore_font['family'] ) ) {
				$css .= 'font-family:' . $readmore_font['family'] .  ';';
			}
			if ( isset( $readmore_font['style'] ) && ! empty( $readmore_font['style'] ) ) {
				$css .= 'font-style:' . $readmore_font['style'] .  ';';
			}
			if ( isset( $readmore_font['weight'] ) && ! empty( $readmore_font['weight'] ) ) {
				$css .= 'font-weight:' . $readmore_font['weight'] .  ';';
			}
		}
		$css .= '}';
		if ( isset( $attr['readMoreFont'] ) && is_array( $attr['readMoreFont'] ) && isset( $attr['readMoreFont'][0] ) && is_array( $attr['readMoreFont'][0] ) && ( ( isset( $attr['readMoreFont'][0]['size'] ) && is_array( $attr
		['readMoreFont'][0]['size'] ) && isset( $attr['readMoreFont'][0]['size'][1] ) && ! empty( $attr['readMoreFont'][0]['size'][1] ) ) || ( isset( $attr['readMoreFont'][0]['lineHeight'] ) && is_array( $attr
		['readMoreFont'][0]['lineHeight'] ) && isset( $attr['readMoreFont'][0]['lineHeight'][1] ) && ! empty( $attr['readMoreFont'][0]['lineHeight'][1] ) ) ) ) {
			$css .= '@media (min-width: 767px) and (max-width: 1024px) {';
			$css .= '.kt-post-loop' . $unique_id . ' .entry-content .kt-blocks-post-readmore {';
			if ( isset( $attr['readMoreFont'][0]['size'][1] ) && ! empty( $attr['readMoreFont'][0]['size'][1] ) ) {
				$css .= 'font-size:' . $attr['readMoreFont'][0]['size'][1] . ( ! isset( $attr['readMoreFont'][0]['sizeType'] ) ? 'px' : $attr['readMoreFont'][0]['sizeType'] ) . ';';
			}
			if ( isset( $attr['readMoreFont'][0]['lineHeight'][1] ) && ! empty( $attr['readMoreFont'][0]['lineHeight'][1] ) ) {
				$css .= 'line-height:' . $attr['readMoreFont'][0]['lineHeight'][1] . ( ! isset( $attr['readMoreFont'][0]['lineType'] ) ? 'px' : $attr['readMoreFont'][0]['lineType'] ) . ';';
			}
			$css .= '}';
			$css .= '}';
		}
		if ( isset( $attr['readMoreFont'] ) && is_array( $attr['readMoreFont'] ) && isset( $attr['readMoreFont'][0] ) && is_array( $attr['readMoreFont'][0] ) && ( ( isset( $attr['readMoreFont'][0]['size'] ) && is_array( $attr
		['readMoreFont'][0]['size'] ) && isset( $attr['readMoreFont'][0]['size'][2] ) && ! empty( $attr['readMoreFont'][0]['size'][2] ) ) || ( isset( $attr['readMoreFont'][0]['lineHeight'] ) && is_array( $attr
		['readMoreFont'][0]['lineHeight'] ) && isset( $attr['readMoreFont'][0]['lineHeight'][2] ) && ! empty( $attr['readMoreFont'][0]['lineHeight'][2] ) ) ) ) {
			$css .= '@media (max-width: 767px) {';
			$css .= '.kt-post-loop' . $unique_id . ' .entry-content .kt-blocks-post-readmore {';
				if ( isset( $attr['readMoreFont'][0]['size'][2] ) && ! empty( $attr['readMoreFont'][0]['size'][2] ) ) {
					$css .= 'font-size:' . $attr['readMoreFont'][0]['size'][2] . ( ! isset( $attr['readMoreFont'][0]['sizeType'] ) ? 'px' : $attr['readMoreFont'][0]['sizeType'] ) . ';';
				}
				if ( isset( $attr['readMoreFont'][0]['lineHeight'][2] ) && ! empty( $attr['readMoreFont'][0]['lineHeight'][2] ) ) {
					$css .= 'line-height:' . $attr['readMoreFont'][0]['lineHeight'][2] . ( ! isset( $attr['readMoreFont'][0]['lineType'] ) ? 'px' : $attr['readMoreFont'][0]['lineType'] ) . ';';
				}
			$css .= '}';
			$css .= '}';
		}
	}
	if ( isset( $attr['displayReadMore'] ) && true == $attr['displayReadMore'] ) {
		$css .= '.kt-post-loop' . $unique_id . ' .entry-content .kt-blocks-post-readmore:hover {';
		if ( isset( $attr['readMoreHoverColor'] ) && ! empty( $attr['readMoreHoverColor'] ) ) {
			$css .= 'color:' . kbp_post_grid_color_output( $attr['readMoreHoverColor'] ) . ';';
		} else if ( ! isset( $attr['readMoreHoverColor'] ) ) {
			$css .= 'color:#ffffff;';
		}
		if ( isset( $attr['readMoreHoverBorderColor'] ) && ! empty( $attr['readMoreHoverBorderColor'] ) ) {
			$css .= 'border-color:' . kbp_post_grid_color_output( $attr['readMoreHoverBorderColor'] ) . ';';
		} else if ( ! isset( $attr['readMoreHoverBorderColor'] ) ) {
			$css .= 'border-color:#555555;';
		}
		if ( isset( $attr['readMoreHoverBackground'] ) && ! empty( $attr['readMoreHoverBackground'] ) ) {
			$css .= 'background-color:' . kbp_post_grid_color_output( $attr['readMoreHoverBackground'] ) . ';';
		} else if ( ! isset( $attr['readMoreHoverBackground'] ) ) {
			$css .= 'background-color:#555555;';
		}
		$css .= '}';
	}
	// Filter.
	if ( isset( $attr['displayFilter'] ) && true === $attr['displayFilter'] && isset( $attr['filterAlign'] ) && ! empty( $attr['filterAlign'] ) ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kb-post-filter-container {';
		$css .= 'text-align:' . $attr['filterAlign'] . ';';
		$css .= '}';
		if ( 'right' === $attr['filterAlign'] ) {
			$css .= '.kt-post-loop' . $unique_id . ' .kb-post-filter-container {';
			$css .= 'justify-content: flex-end;';
			$css .= '}';
		}
		if ( 'left' === $attr['filterAlign'] ) {
			$css .= '.kt-post-loop' . $unique_id . ' .kb-post-filter-container {';
			$css .= 'justify-content: flex-start;';
			$css .= '}';
		}
	}
	// Filter Font.
	if ( isset( $attr['filterColor'] ) || isset( $attr['filterBorderRadius'] ) || isset( $attr['filterFont'] ) || isset( $attr['filterBorder'] ) || isset( $attr['filterBackground'] ) || isset( $attr['filterBorderWidth'] ) || isset( $attr['filterPadding'] ) || isset( $attr['filterMargin'] )  ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kb-filter-item {';
		if ( isset( $attr['filterColor'] ) && ! empty( $attr['filterColor'] ) ) {
			$css .= 'color:' . kbp_post_grid_color_output( $attr['filterColor'] ) . ';';
		}
		if ( isset( $attr['filterBorderRadius'] ) && is_numeric( $attr['filterBorderRadius'] ) ) {
			$css .= 'border-radius:' . $attr['filterBorderRadius'] . 'px;';
		}
		if ( isset( $attr['filterBackground'] ) && ! empty( $attr['filterBackground'] ) ) {
			$bcoloralpha = ( isset( $attr['filterBackgroundOpacity'] ) ? $attr['filterBackgroundOpacity'] : 1 );
			$bcolorhex = ( isset( $attr['filterBackground'] ) ? $attr['filterBackground'] : '#ffffff' );
			$bcolor = kbp_post_grid_color_output( $bcolorhex, $bcoloralpha );
			$css .= 'background:' . $bcolor . ';';
		}
		if ( isset( $attr['filterBorder'] ) && ! empty( $attr['filterBorder'] ) ) {
			$bcoloralpha = ( isset( $attr['filterBorderOpacity'] ) ? $attr['filterBorderOpacity'] : 1 );
			$bcolorhex = ( isset( $attr['filterBorder'] ) ? $attr['filterBorder'] : '#ffffff' );
			$bcolor = kbp_post_grid_color_output( $bcolorhex, $bcoloralpha );
			$css .= 'border-color:' . $bcolor . ';';
		}
		if ( isset( $attr['filterBorderWidth'] ) && is_array( $attr['filterBorderWidth'] ) && isset( $attr['filterBorderWidth'][0] ) && is_numeric( $attr['filterBorderWidth'][0] ) ) {
			$css .= 'border-width:' . $attr['filterBorderWidth'][0] . 'px ' . $attr['filterBorderWidth'][1] . 'px ' . $attr['filterBorderWidth'][2] . 'px ' . $attr['filterBorderWidth'][3] . 'px;';
		}
		if ( isset( $attr['filterPadding'] ) && is_array( $attr['filterPadding'] ) ) {
			$css .= 'padding:' . $attr['filterPadding'][0] . 'px ' . $attr['filterPadding'][1] . 'px ' . $attr['filterPadding'][2] . 'px ' . $attr['filterPadding'][3] . 'px;';
		}
		if ( isset( $attr['filterMargin'] ) && is_array( $attr['filterMargin'] ) ) {
			$css .= 'margin:' . $attr['filterMargin'][0] . 'px ' . $attr['filterMargin'][1] . 'px ' . $attr['filterMargin'][2] . 'px ' . $attr['filterMargin'][3] . 'px;';
		}
		if ( isset( $attr['filterFont'] ) && is_array( $attr['filterFont'] ) && isset( $attr['filterFont'][0] ) && is_array( $attr['filterFont'][0] ) ) {
			$filter_font = $attr['filterFont'][0];
			if ( isset( $filter_font['size'] ) && is_array( $filter_font['size'] ) && ! empty( $filter_font['size'][0] ) ) {
				$css .= 'font-size:' . $filter_font['size'][0] . ( ! isset( $filter_font['sizeType'] ) ? 'px' : $filter_font['sizeType'] ) . ';';
			}
			if ( isset( $filter_font['lineHeight'] ) && is_array( $filter_font['lineHeight'] ) && ! empty( $filter_font['lineHeight'][0] ) ) {
				$css .= 'line-height:' . $filter_font['lineHeight'][0] . ( ! isset( $filter_font['lineType'] ) ? 'px' : $filter_font['lineType'] ) . ';';
			}
			if ( isset( $filter_font['letterSpacing'] ) && ! empty( $filter_font['letterSpacing'] ) ) {
				$css .= 'letter-spacing:' . $filter_font['letterSpacing'] .  'px;';
			}
			if ( isset( $filter_font['textTransform'] ) && ! empty( $filter_font['textTransform'] ) ) {
				$css .= 'text-transform:' . $filter_font['textTransform'] . ';';
			}
			if ( isset( $filter_font['family'] ) && ! empty( $filter_font['family'] ) ) {
				$css .= 'font-family:' . $filter_font['family'] . ';';
			}
			if ( isset( $filter_font['style'] ) && ! empty( $filter_font['style'] ) ) {
				$css .= 'font-style:' . $filter_font['style'] . ';';
			}
			if ( isset( $filter_font['weight'] ) && ! empty( $filter_font['weight'] ) ) {
				$css .= 'font-weight:' . $filter_font['weight'] . ';';
			}
		}
		$css .= '}';
		if ( isset( $attr['filterFont'] ) && is_array( $attr['filterFont'] ) && isset( $attr['filterFont'][0] ) && is_array( $attr['filterFont'][0] ) && ( ( isset( $attr['filterFont'][0]['size'] ) && is_array( $attr
		['filterFont'][0]['size'] ) && isset( $attr['filterFont'][0]['size'][1] ) && ! empty( $attr['filterFont'][0]['size'][1] ) ) || ( isset( $attr['filterFont'][0]['lineHeight'] ) && is_array( $attr
		['filterFont'][0]['lineHeight'] ) && isset( $attr['filterFont'][0]['lineHeight'][1] ) && ! empty( $attr['filterFont'][0]['lineHeight'][1] ) ) ) ) {
			$css .= '@media (min-width: 767px) and (max-width: 1024px) {';
			$css .= '.kt-post-loop' . $unique_id . ' .kb-filter-item {';
			if ( isset( $attr['filterFont'][0]['size'][1] ) && ! empty( $attr['filterFont'][0]['size'][1] ) ) {
				$css .= 'font-size:' . $attr['filterFont'][0]['size'][1] . ( ! isset( $attr['filterFont'][0]['sizeType'] ) ? 'px' : $attr['filterFont'][0]['sizeType'] ) . ';';
			}
			if ( isset( $attr['filterFont'][0]['lineHeight'][1] ) && ! empty( $attr['filterFont'][0]['lineHeight'][1] ) ) {
				$css .= 'line-height:' . $attr['filterFont'][0]['lineHeight'][1] . ( ! isset( $attr['filterFont'][0]['lineType'] ) ? 'px' : $attr['filterFont'][0]['lineType'] ) . ';';
			}
			$css .= '}';
			$css .= '}';
		}
		if ( isset( $attr['filterFont'] ) && is_array( $attr['filterFont'] ) && isset( $attr['filterFont'][0] ) && is_array( $attr['filterFont'][0] ) && ( ( isset( $attr['filterFont'][0]['size'] ) && is_array( $attr
		['filterFont'][0]['size'] ) && isset( $attr['filterFont'][0]['size'][2] ) && ! empty( $attr['filterFont'][0]['size'][2] ) ) || ( isset( $attr['filterFont'][0]['lineHeight'] ) && is_array( $attr
		['filterFont'][0]['lineHeight'] ) && isset( $attr['filterFont'][0]['lineHeight'][2] ) && ! empty( $attr['filterFont'][0]['lineHeight'][2] ) ) ) ) {
			$css .= '@media (max-width: 767px) {';
			$css .= '.kt-post-loop' . $unique_id . ' .kb-filter-item {';
				if ( isset( $attr['filterFont'][0]['size'][2] ) && ! empty( $attr['filterFont'][0]['size'][2] ) ) {
					$css .= 'font-size:' . $attr['filterFont'][0]['size'][2] . ( ! isset( $attr['filterFont'][0]['sizeType'] ) ? 'px' : $attr['filterFont'][0]['sizeType'] ) . ';';
				}
				if ( isset( $attr['filterFont'][0]['lineHeight'][2] ) && ! empty( $attr['filterFont'][0]['lineHeight'][2] ) ) {
					$css .= 'line-height:' . $attr['filterFont'][0]['lineHeight'][2] . ( ! isset( $attr['filterFont'][0]['lineType'] ) ? 'px' : $attr['filterFont'][0]['lineType'] ) . ';';
				}
			$css .= '}';
			$css .= '}';
		}
	}
	if ( isset( $attr['filterHoverColor'] ) || isset( $attr['filterHoverBorder'] ) || isset( $attr['filterHoverBackground'] ) ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kb-filter-item:hover, .kt-post-loop' . $unique_id . ' .kb-filter-item:focus {';
		if ( isset( $attr['filterHoverColor'] ) && ! empty( $attr['filterHoverColor'] ) ) {
			$css .= 'color:' . kbp_post_grid_color_output( $attr['filterHoverColor'] ) . ';';
		}
		if ( isset( $attr['filterHoverBackground'] ) && ! empty( $attr['filterHoverBackground'] ) ) {
			$bcoloralpha = ( isset( $attr['filterHoverBackgroundOpacity'] ) ? $attr['filterHoverBackgroundOpacity'] : 1 );
			$bcolorhex = ( isset( $attr['filterHoverBackground'] ) ? $attr['filterHoverBackground'] : '#ffffff' );
			$bcolor = kbp_post_grid_color_output( $bcolorhex, $bcoloralpha );
			$css .= 'background:' . $bcolor . ';';
		}
		if ( isset( $attr['filterHoverBorder'] ) && ! empty( $attr['filterHoverBorder'] ) ) {
			$bcoloralpha = ( isset( $attr['filterHoverBorderOpacity'] ) ? $attr['filterHoverBorderOpacity'] : 1 );
			$bcolorhex = ( isset( $attr['filterHoverBorder'] ) ? $attr['filterHoverBorder'] : '#ffffff' );
			$bcolor = kbp_post_grid_color_output( $bcolorhex, $bcoloralpha );
			$css .= 'border-color:' . $bcolor . ';';
		}
		$css .= '}';
	}
	if ( isset( $attr['filterActiveColor'] ) || isset( $attr['filterActiveBorder'] ) || isset( $attr['filterActiveBackground'] ) ) {
		$css .= '.kt-post-loop' . $unique_id . ' .kb-filter-item.is-active {';
		if ( isset( $attr['filterActiveColor'] ) && ! empty( $attr['filterActiveColor'] ) ) {
			$css .= 'color:' . kbp_post_grid_color_output( $attr['filterActiveColor'] ) . ';';
		}
		if ( isset( $attr['filterActiveBackground'] ) && ! empty( $attr['filterActiveBackground'] ) ) {
			$bcoloralpha = ( isset( $attr['filterActiveBackgroundOpacity'] ) ? $attr['filterActiveBackgroundOpacity'] : 1 );
			$bcolorhex = ( isset( $attr['filterActiveBackground'] ) ? $attr['filterActiveBackground'] : '#ffffff' );
			$bcolor = kbp_post_grid_color_output( $bcolorhex, $bcoloralpha );
			$css .= 'background:' . $bcolor . ';';
		}
		if ( isset( $attr['filterActiveBorder'] ) && ! empty( $attr['filterActiveBorder'] ) ) {
			$bcoloralpha = ( isset( $attr['filterActiveBorderOpacity'] ) ? $attr['filterActiveBorderOpacity'] : 1 );
			$bcolorhex = ( isset( $attr['filterActiveBorder'] ) ? $attr['filterActiveBorder'] : '#ffffff' );
			$bcolor = kbp_post_grid_color_output( $bcolorhex, $bcoloralpha );
			$css .= 'border-color:' . $bcolor . ';';
		}
		$css .= '}';
	}

	return $css;
}
/**
 * Get Post Loop Image
 *
 * @param array $attributes Block Attributes.
 */
function kt_blocks_pro_get_post_image( $attributes ) {
	global $post;
	if ( isset( $attributes['displayImage'] ) && true === $attributes['displayImage'] && has_post_thumbnail() ) {
		$image_ratio = ( isset( $attributes['imageRatio'] ) ?  $attributes['imageRatio'] : '75' );
		$image_size = ( isset( $attributes['imageFileSize'] ) && ! empty( $attributes['imageFileSize'] ) ? $attributes['imageFileSize'] : 'large' );
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->id ), $image_size );
		echo '<div class="kadence-post-image">';
		echo '<div class="kadence-post-image-intrisic kt-image-ratio-'. esc_attr( str_replace( '.', '-', $image_ratio ) ) .'" style="padding-bottom:' . ( 'nocrop' === $image_ratio ? ( ( $image[2] / $image[1] ) * 100 ) . '%' : $image_ratio . '%' ) . '">';
		echo '<div class="kadence-post-image-inner-intrisic">';
			echo '<a href="' . esc_url( get_permalink() ) . '"' . ( isset( $attributes['openNewTab'] ) && true == $attributes['openNewTab'] ? ' target="_blank"' : '' ) . '>';
			the_post_thumbnail( $image_size );
			echo '</a>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
	}
}
add_action( 'kadence_blocks_post_loop_start', 'kt_blocks_pro_get_post_image', 20 );

/**
 * Get Post Loop Above Categories
 *
 * @param array $attributes Block Attributes.
 */
function kt_blocks_pro_get_above_categories( $attributes ) {
	if ( isset( $attributes['displayAboveCategories'] ) && true === $attributes['displayAboveCategories'] && 'post' === get_post_type() ) {
		$sep_name = ( isset( $attributes['aboveDividerSymbol'] ) ? $attributes['aboveDividerSymbol'] : 'line' );
		if ( 'dash' === $sep_name ) {
			$sep = '&#8208;';
		} else if ( 'line' === $sep_name ) {
			$sep = '&#124;';
		} else if ( 'dot' === $sep_name ) {
			$sep = '&#183;';
		} else if ( 'bullet' === $sep_name ) {
			$sep = '&#8226;';
		} else if ( 'tilde' === $sep_name ) {
			$sep = '&#126;';
		} else {
			$sep = '';
		}
		echo '<div class="kt-blocks-above-categories">';
		the_category( ' ' . $sep . ' ' );
		echo '</div>';
	}
}
add_action( 'kadence_blocks_post_loop_header', 'kt_blocks_pro_get_above_categories', 10 );

/**
 * Get Post Loop Title
 *
 * @param array $attributes Block Attributes.
 */
function kt_blocks_pro_get_post_title( $attributes ) {
	if ( isset( $attributes['displayTitle'] ) && true === $attributes['displayTitle'] ) {
		echo ( isset( $attributes['titleFont'] ) && isset( $attributes['titleFont'][0] ) && isset( $attributes['titleFont'][0]['level'] ) && ! empty( $attributes['titleFont'][0]['level'] ) ? '<h' . esc_attr( $attributes['titleFont'][0]['level'] ) . ' class="entry-title">' : '<h2 class="entry-title">' );
		echo '<a href="' . esc_url( get_permalink() ) . '"' . ( isset( $attributes['openNewTab'] ) && true == $attributes['openNewTab'] ? ' target="_blank"' : '' ) . '>';
			the_title();
		echo '</a>';
		echo ( isset( $attributes['titleFont'] ) && isset( $attributes['titleFont'][0] ) && isset( $attributes['titleFont'][0]['level'] ) && ! empty( $attributes['titleFont'][0]['level'] ) ? '</h' . esc_attr( $attributes['titleFont'][0]['level'] ) . '>' : '</h2>' );
	}
}
add_action( 'kadence_blocks_post_loop_header', 'kt_blocks_pro_get_post_title', 20 );

/**
 * Get Post Loop Header Meta Area
 *
 * @param array $attributes Block Attributes.
 */
function kt_blocks_pro_get_meta_area( $attributes ) {
	echo '<div class="kt-blocks-post-top-meta">';
	/**
	 * @hooked kt_blocks_pro_get_meta_date - 10
	 * @hooked kt_blocks_pro_get_meta_modified_date - 10
	 * @hooked kt_blocks_pro_get_meta_author - 15
	 * @hooked kt_blocks_pro_get_meta_category - 20
	 * @hooked kt_blocks_pro_get_meta_comments - 25
	 */
	do_action( 'kadence_blocks_post_loop_header_meta', $attributes );
	echo '</div>';
}
add_action( 'kadence_blocks_post_loop_header', 'kt_blocks_pro_get_meta_area', 30 );

/**
 * Get Post Loop Header Meta Date
 *
 * @param array $attributes Block Attributes.
 */
function kt_blocks_pro_get_meta_date( $attributes ) {
	if ( isset( $attributes['displayDate'] ) && true === $attributes['displayDate'] ) {
		echo '<div class="kt-blocks-date">';
		if ( isset( $attributes['datePreText'] ) && ! empty( $attributes['datePreText'] ) ) {
			echo '<span class="kt-blocks-date-pretext">';
			echo esc_html( $attributes['datePreText'] );
			echo ' </span>';
		}
		echo '<time dateTime="' . esc_attr( get_the_date( get_option( 'date_format' ) ) ) . '" class="kt-blocks-post-date">';
		echo get_the_date( get_option( 'date_format' ) );
		echo '</time>';
		echo '</div>';
		if ( ( isset( $attributes['displayModifiedDate'] ) && true === $attributes['displayModifiedDate'] ) || ( isset( $attributes['displayAuthor'] ) && true === $attributes['displayAuthor'] ) || ( isset( $attributes['displayCategory'] ) && true === $attributes['displayCategory'] ) || ( isset( $attributes['displayComment'] ) && true === $attributes['displayComment'] ) ) {
			$sep_name = ( isset( $attributes['metaDividerSymbol'] ) ? $attributes['metaDividerSymbol'] : '' );
			$sep_di_class = 'kt-blocks-meta-has-divider';
			if ( 'dash' === $sep_name ) {
				$sep = '&#8208;';
			} else if ( 'line' === $sep_name ) {
				$sep = '&#124;';
			} else if ( 'dot' === $sep_name ) {
				$sep = '&#183;';
			} else if ( 'bullet' === $sep_name ) {
				$sep = '&#8226;';
			} else if ( 'tilde' === $sep_name ) {
				$sep = '&#126;';
			} else {
				$sep = '';
				$sep_di_class = 'kt-blocks-meta-no-divider';
			}
			echo '<div class="kt-blocks-meta-divider ' . esc_attr( $sep_di_class ) . '">';
			echo $sep;
			echo '</div>';
		}
	}
}
add_action( 'kadence_blocks_post_loop_header_meta', 'kt_blocks_pro_get_meta_date', 10 );

/**
 * Get Post Loop Header Meta Modified Date
 *
 * @param array $attributes Block Attributes.
 */
function kt_blocks_pro_get_meta_modified_date( $attributes ) {
	if ( isset( $attributes['displayModifiedDate'] ) && true === $attributes['displayModifiedDate'] ) {
		echo '<div class="kt-blocks-date-updated">';
		if ( isset( $attributes['modifiedDatePreText'] ) && ! empty( $attributes['modifiedDatePreText'] ) ) {
			echo '<span class="kt-blocks-updated-date-pretext">';
			echo esc_html( $attributes['modifiedDatePreText'] );
			echo ' </span>';
		}
		echo '<time dateTime="' . esc_attr( get_the_modified_date( get_option( 'date_format' ) ) ) . '" class="kt-blocks-post-date">';
		echo get_the_modified_date( get_option( 'date_format' ) );
		echo '</time>';
		echo '</div>';
		if ( ( isset( $attributes['displayAuthor'] ) && true === $attributes['displayAuthor'] ) || ( isset( $attributes['displayCategory'] ) && true === $attributes['displayCategory'] ) || ( isset( $attributes['displayComment'] ) && true === $attributes['displayComment'] ) ) {
			$sep_name = ( isset( $attributes['metaDividerSymbol'] ) ? $attributes['metaDividerSymbol'] : '' );
			$sep_di_class = 'kt-blocks-meta-has-divider';
			if ( 'dash' === $sep_name ) {
				$sep = '&#8208;';
			} else if ( 'line' === $sep_name ) {
				$sep = '&#124;';
			} else if ( 'dot' === $sep_name ) {
				$sep = '&#183;';
			} else if ( 'bullet' === $sep_name ) {
				$sep = '&#8226;';
			} else if ( 'tilde' === $sep_name ) {
				$sep = '&#126;';
			} else {
				$sep = '';
				$sep_di_class = 'kt-blocks-meta-no-divider';
			}
			echo '<div class="kt-blocks-meta-divider ' . esc_attr( $sep_di_class ) . '">';
			echo $sep;
			echo '</div>';
		}
	}
}
add_action( 'kadence_blocks_post_loop_header_meta', 'kt_blocks_pro_get_meta_modified_date', 12 );

/**
 * Get Post Loop Header Meta Author
 *
 * @param array $attributes Block Attributes.
 */
function kt_blocks_pro_get_meta_author( $attributes ) {
	if ( isset( $attributes['displayAuthor'] ) && true === $attributes['displayAuthor'] ) {
		echo '<div class="kt-blocks-post-author">';
		if ( isset( $attributes['authorPreText'] ) && ! empty( $attributes['authorPreText'] ) ) {
			echo '<span class="kt-blocks-author-pretext">';
			echo esc_html( $attributes['authorPreText'] );
			echo ' </span>';
		}
		echo '<a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '" class="kt-blocks-post-author-link fn">';
		echo get_the_author();
		echo '</a>';
		echo '</div>';
		if ( ( isset( $attributes['displayCategory'] ) && true === $attributes['displayCategory'] ) || ( isset( $attributes['displayComment'] ) && true === $attributes['displayComment'] ) ) {
			$sep_name = ( isset( $attributes['metaDividerSymbol'] ) ? $attributes['metaDividerSymbol'] : '' );
			$sep_di_class = 'kt-blocks-meta-has-divider';
			if ( 'dash' === $sep_name ) {
				$sep = '&#8208;';
			} else if ( 'line' === $sep_name ) {
				$sep = '&#124;';
			} else if ( 'dot' === $sep_name ) {
				$sep = '&#183;';
			} else if ( 'bullet' === $sep_name ) {
				$sep = '&#8226;';
			} else if ( 'tilde' === $sep_name ) {
				$sep = '&#126;';
			} else {
				$sep = '';
				$sep_di_class = 'kt-blocks-meta-no-divider';
			}
			echo '<div class="kt-blocks-meta-divider ' . esc_attr( $sep_di_class ) . '">';
			echo $sep;
			echo '</div>';
		}
	}
}
add_action( 'kadence_blocks_post_loop_header_meta', 'kt_blocks_pro_get_meta_author', 15 );

/**
 * Get Post Loop Header Meta Category
 *
 * @param array $attributes Block Attributes.
 */
function kt_blocks_pro_get_meta_category( $attributes ) {
	if ( isset( $attributes['displayCategory'] ) && true === $attributes['displayCategory'] && 'post' === get_post_type() && has_category() ) {
		echo '<div class="kt-blocks-post-author">';
		if ( isset( $attributes['categoryPreText'] ) && ! empty( $attributes['categoryPreText'] ) ) {
			echo '<span class="kt-blocks-category-pretext">';
			echo esc_html( $attributes['categoryPreText'] );
			echo ' </span>';
		}
		$cats = get_taxonomy( 'category' );
		echo '<span class="kt-blocks-categories">';
		the_category( ', ' );
		echo '</span>';
		echo '</div>';
		if ( ( isset( $attributes['displayComment'] ) && true === $attributes['displayComment'] ) ) {
			$sep_name = ( isset( $attributes['metaDividerSymbol'] ) ? $attributes['metaDividerSymbol'] : '' );
			$sep_di_class = 'kt-blocks-meta-has-divider';
			if ( 'dash' === $sep_name ) {
				$sep = '&#8208;';
			} else if ( 'line' === $sep_name ) {
				$sep = '&#124;';
			} else if ( 'dot' === $sep_name ) {
				$sep = '&#183;';
			} else if ( 'bullet' === $sep_name ) {
				$sep = '&#8226;';
			} else if ( 'tilde' === $sep_name ) {
				$sep = '&#126;';
			} else {
				$sep = '';
				$sep_di_class = 'kt-blocks-meta-no-divider';
			}
			echo '<div class="kt-blocks-meta-divider ' . esc_attr( $sep_di_class ) . '">';
			echo $sep;
			echo '</div>';
		}
	}
}
add_action( 'kadence_blocks_post_loop_header_meta', 'kt_blocks_pro_get_meta_category', 20 );

/**
 * Get Post Loop Header Meta Category
 *
 * @param array $attributes Block Attributes.
 */
function kt_blocks_pro_get_meta_comment( $attributes ) {
	if ( isset( $attributes['displayComment'] ) && true === $attributes['displayComment'] ) {
		echo '<div class="kt-blocks-post-comments">';
		echo '<a class="kt-blocks-post-comments-link" href="' . esc_url( get_permalink() ) . '#comments">';
		if ( '1' === get_comments_number() ) {
			echo get_comments_number() . ' ' .  __( 'Comment', 'kadence-blocks-pro' );
		} else {
			echo get_comments_number() . ' ' . __( 'Comments', 'kadence-blocks-pro' );
		}
		echo '</a>';
		echo '</div>';
	}
}
add_action( 'kadence_blocks_post_loop_header_meta', 'kt_blocks_pro_get_meta_comment', 25 );

/**
 * Callback for the excerpt_length filter used by
 * the Latest Posts block at render time.
 *
 * @return int Returns the global $kadence_blocks_post_grid_get_excerpt_length variable
 *             to allow the excerpt_length filter respect the Latest Block setting.
 */
function kadence_blocks_post_get_excerpt_length() {
	global $kadence_blocks_post_grid_get_excerpt_length;
	return $kadence_blocks_post_grid_get_excerpt_length;
}
/**
 * Get Post Loop Excerpt
 *
 * @param array $attributes Block Attributes.
 */
function kt_blocks_pro_get_post_excerpt( $attributes ) {
	global $kadence_blocks_post_grid_get_excerpt_length;
	if ( isset( $attributes['displayExcerpt'] ) && true === $attributes['displayExcerpt'] ) {
		if ( isset( $attributes['excerptCustomLength'] ) && true === $attributes['excerptCustomLength'] ) {
			$kadence_blocks_post_grid_get_excerpt_length = $attributes['excerptLength'];
			add_filter( 'excerpt_length', 'kadence_blocks_post_get_excerpt_length', 20 );
			echo get_the_excerpt();
			remove_filter( 'excerpt_length', 'kadence_blocks_post_get_excerpt_length', 20 );
		} else {
			echo get_the_excerpt();
		}
	}
}
add_action( 'kadence_blocks_post_loop_content', 'kt_blocks_pro_get_post_excerpt', 20 );
/**
 * Get Post Loop Read More
 *
 * @param array $attributes Block Attributes.
 */
function kt_blocks_pro_get_post_read_more( $attributes ) {
	if ( isset( $attributes['displayReadMore'] ) && true === $attributes['displayReadMore'] && isset( $attributes['readMoreText'] ) && ! empty( $attributes['readMoreText'] ) ) {
		echo '<div class="kt-blocks-post-readmore-wrap">';
		echo '<a class="kt-blocks-post-readmore" href="' . esc_url( get_permalink() ) . '"' . ( isset( $attributes['openNewTab'] ) && true == $attributes['openNewTab'] ? ' target="_blank"' : '' ) . '>';
			echo esc_html( $attributes['readMoreText'] );
		echo '</a>';
		echo '</div>';
	}
}
add_action( 'kadence_blocks_post_loop_content', 'kt_blocks_pro_get_post_read_more', 30 );

/**
 * Get Post Loop Footer Date
 *
 * @param array $attributes Block Attributes.
 */
function kt_blocks_pro_get_post_footer_date( $attributes ) {
	if ( isset( $attributes['footerDisplayDate'] ) && true === $attributes['footerDisplayDate'] ) {
		echo '<div class="kt-blocks-date kt-blocks-post-footer-section">';
		echo '<time dateTime="' . esc_attr( get_the_date( get_option( 'date_format' ) ) ) . '" class="kt-blocks-post-date">';
		echo get_the_date( get_option( 'date_format' ) );
		echo '</time>';
		echo '</div>';
	}
}
add_action( 'kadence_blocks_post_loop_footer_start', 'kt_blocks_pro_get_post_footer_date', 10 );

/**
 * Get Post Loop Footer Categories
 *
 * @param array $attributes Block Attributes.
 */
function kt_blocks_pro_get_post_footer_categories( $attributes ) {
	if ( isset( $attributes['footerDisplayCategories'] ) && true === $attributes['footerDisplayCategories'] && has_category() ) {
		$cats = get_taxonomy( 'category' );
		echo '<div class="kt-blocks-categories kt-blocks-post-footer-section">';
		echo '<span class="kt-blocks-tags-icon"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" class="kt-blocks-cat-svg" fill="currentColor" width="32" height="32" viewBox="0 0 32 32"><title>'. esc_html( $cats->label ) . '</title>
		<path d="M0 10h32l-2 20h-28l-2-20zM29 6l1 2h-28l2-4h11l1 2h13z"></path></svg></span>';
		the_category( ', ' );
		echo '</div>';
	}
}
add_action( 'kadence_blocks_post_loop_footer_start', 'kt_blocks_pro_get_post_footer_categories', 15 );
/**
 * Get Post Loop Footer Tags
 *
 * @param array $attributes Block Attributes.
 */
function kt_blocks_pro_get_post_footer_tags( $attributes ) {
	if ( isset( $attributes['footerDisplayTags'] ) && true === $attributes['footerDisplayTags'] && has_tag() ) {
		$tags = get_taxonomy( 'post_tag' );
		echo '<div class="kt-blocks-tags kt-blocks-post-footer-section">';
		echo '<span class="kt-blocks-tags-icon"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" class="kt-blocks-tag-svg" width="36" height="32" fill="currentColor" viewBox="0 0 36 32"><title>'. esc_html( $tags->label ) . '</title><path d="M34.939 19.939l-8.879-8.879c-0.583-0.583-1.736-1.061-2.561-1.061h-18c-0.825 0-1.5 0.675-1.5 1.5v19c0 0.825 0.675 1.5 1.5 1.5h18c0.825 0 1.977-0.477 2.561-1.061l8.879-8.879c0.583-0.583 0.583-1.538-0-2.121zM25 24c-1.657 0-3-1.343-3-3s1.343-3 3-3 3 1.343 3 3-1.343 3-3 3z"></path><path d="M2 8h21l-0.939-0.939c-0.583-0.583-1.736-1.061-2.561-1.061h-18c-0.825 0-1.5 0.675-1.5 1.5v19c0 0.825 0.675 1.5 1.5 1.5h0.5v-20z"></path></svg></span>';
		the_tags( '', ', ' );
		echo '</div>';
	}
}
add_action( 'kadence_blocks_post_loop_footer_start', 'kt_blocks_pro_get_post_footer_tags', 20 );

/**
 * Get Post Loop Footer Author
 *
 * @param array $attributes Block Attributes.
 */
function kt_blocks_pro_get_post_footer_author( $attributes ) {
	if ( isset( $attributes['footerDisplayAuthor'] ) && true === $attributes['footerDisplayAuthor'] ) {
		echo '<div class="kt-blocks-author kt-blocks-post-footer-section">';
		echo '<span class="kt-blocks-post-author-inner kt-blocks-css-tool-top" aria-label="' . esc_attr( get_the_author() ) . '">';
		echo '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" class="kt-blocks-user-svg" fill="currentColor" viewBox="0 0 32 32"><title>' . esc_attr( get_the_author() ) . '</title><path d="M18 22.082v-1.649c2.203-1.241 4-4.337 4-7.432 0-4.971 0-9-6-9s-6 4.029-6 9c0 3.096 1.797 6.191 4 7.432v1.649c-6.784 0.555-12 3.888-12 7.918h28c0-4.030-5.216-7.364-12-7.918z"></path></svg>';
		echo '</span>';
		echo '</div>';
	}
}
add_action( 'kadence_blocks_post_loop_footer_end', 'kt_blocks_pro_get_post_footer_author', 10 );

/**
 * Get Post Loop Footer Comments
 *
 * @param array $attributes Block Attributes.
 */
function kt_blocks_pro_get_post_footer_comments( $attributes ) {
	if ( isset( $attributes['footerDisplayComment'] ) && true === $attributes['footerDisplayComment'] && '0' !== get_comments_number() ) {
		echo '<div class="kt-blocks-post-comments kt-blocks-post-footer-section">';
		echo '<a class="kt-blocks-post-comments-link" href="' . esc_url( get_permalink() ) . '#comments">';
		echo '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" class="kt-blocks-comments-svg" width="36" height="32" fill="currentColor" viewBox="0 0 36 32"><title>' . esc_attr( __( 'Comment Count', 'kadence-blocks-pro' ) ) . '</title><path d="M15 4c-1.583 0-3.112 0.248-4.543 0.738-1.341 0.459-2.535 1.107-3.547 1.926-1.876 1.518-2.91 3.463-2.91 5.474 0 1.125 0.315 2.217 0.935 3.247 0.646 1.073 1.622 2.056 2.821 2.842 0.951 0.624 1.592 1.623 1.761 2.748 0.028 0.187 0.051 0.375 0.068 0.564 0.085-0.079 0.169-0.16 0.254-0.244 0.754-0.751 1.771-1.166 2.823-1.166 0.167 0 0.335 0.011 0.503 0.032 0.605 0.077 1.223 0.116 1.836 0.116 1.583 0 3.112-0.248 4.543-0.738 1.341-0.459 2.535-1.107 3.547-1.926 1.876-1.518 2.91-3.463 2.91-5.474s-1.033-3.956-2.91-5.474c-1.012-0.819-2.206-1.467-3.547-1.926-1.431-0.49-2.96-0.738-4.543-0.738zM15 0v0c8.284 0 15 5.435 15 12.139s-6.716 12.139-15 12.139c-0.796 0-1.576-0.051-2.339-0.147-3.222 3.209-6.943 3.785-10.661 3.869v-0.785c2.008-0.98 3.625-2.765 3.625-4.804 0-0.285-0.022-0.564-0.063-0.837-3.392-2.225-5.562-5.625-5.562-9.434 0-6.704 6.716-12.139 15-12.139zM31.125 27.209c0 1.748 1.135 3.278 2.875 4.118v0.673c-3.223-0.072-6.181-0.566-8.973-3.316-0.661 0.083-1.337 0.126-2.027 0.126-2.983 0-5.732-0.805-7.925-2.157 4.521-0.016 8.789-1.464 12.026-4.084 1.631-1.32 2.919-2.87 3.825-4.605 0.961-1.84 1.449-3.799 1.449-5.825 0-0.326-0.014-0.651-0.039-0.974 2.268 1.873 3.664 4.426 3.664 7.24 0 3.265-1.88 6.179-4.82 8.086-0.036 0.234-0.055 0.474-0.055 0.718z"></path></svg>';
		echo get_comments_number();
		echo '</a>';
		echo '</div>';
	}
}
add_action( 'kadence_blocks_post_loop_footer_end', 'kt_blocks_pro_get_post_footer_comments', 15 );

/**
 * Get no Posts text.
 *
 * @param array $attributes Block Attributes.
 */
function kt_blocks_pro_get_no_posts( $attributes ) {
	echo '<p>' . esc_html__( 'No posts', 'kadence-blocks-pro' ) . '</p>';
}

add_action( 'kadence_blocks_post_no_posts', 'kt_blocks_pro_get_no_posts', 15 );
/**
 * Get Color Output
 *
 * @param string $color the color string
 * @param string $opacity the alpha level
 */
function kbp_post_grid_color_output( $color, $opacity = null ) {
	if ( strpos( $color, 'palette' ) === 0 ) {
		$color = 'var(--global-' . $color . ')';
	} else if ( isset( $opacity ) && is_numeric( $opacity ) ) {
		$color = kadence_blocks_pro_hex2rgba( $color, $opacity );
	}
	return $color;
}