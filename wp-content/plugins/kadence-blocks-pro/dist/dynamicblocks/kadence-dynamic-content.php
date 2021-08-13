<?php
/**
 * Enqueue JS for Custom Icons and build admin for icons.
 *
 * @since   1.4.0
 * @package Kadence Blocks Pro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue JS for Custom Icons and build admin for icons.
 *
 * @category class
 */
class Kadence_Blocks_Pro_Dynamic_Content {
	/**
	 * Instance of this class
	 *
	 * @var null
	 */
	private static $instance = null;

	const POST_GROUP = 'post';

	const AUTHOR_GROUP = 'author';

	const SITE_GROUP = 'site';

	const COMMENTS_GROUP = 'comments';

	const MEDIA_GROUP = 'media';

	const OTHER_GROUP = 'other';

	const TEXT_CATEGORY = 'text';

	const NUMBER_CATEGORY = 'number';

	const IMAGE_CATEGORY = 'image';

	const DATE_CATEGORY = 'date';

	const AUDIO_CATEGORY = 'audio';

	const VIDEO_CATEGORY = 'video';

	const URL_CATEGORY = 'url';

	const HTML_CATEGORY = 'html';

	const EMBED_CATEGORY = 'embed';

	const VALUE_SEPARATOR = '#+*#';

	const CUSTOM_POST_TYPE_REGEXP = '/"(custom_post_type\|[^\|]+\|\d+)"/';

	const SHORTCODE = 'kb-dynamic';

	/**
	 * The post group field options.
	 *
	 * @var array
	 */
	private static $post_group = array(
		'post_title',
		'post_url',
		'post_content',
		'post_excerpt',
		'post_id',
		'post_date',
		'post_date_modified',
		'post_type',
		'post_status',
		'post_custom_field',
		'post_featured_image',
	);

	/**
	 * Instance Control
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Class Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'on_init' ) );

	}
	/**
	 * On init
	 */
	public function on_init() {
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'script_enqueue' ), 30 );
		}

		add_shortcode( self::SHORTCODE, array( $this, 'dynamic_shortcode_render' ) );
	}
	/**
	 * Enqueue Script for Meta options
	 */
	public function script_enqueue() {
		wp_localize_script(
			'kadence-blocks-pro-js',
			'kadenceDynamicParams',
			array(
				'textFields' => $this->get_text_fields(),
			)
		);
	}
	/**
	 * On init
	 */
	public function get_text_fields() {
		$options = array(
			array(
				'label' => __( 'Post', 'kadence-blocks-pro' ),
				'options' => array(
					array(
						'value' => self::POST_GROUP . '|post_title',
						'label' => esc_attr__( 'Post Title', 'kadence-blocks-pro' ),
					),
					array(
						'value' => self::POST_GROUP . '|post_url',
						'label' => esc_attr__( 'Post URL', 'kadence-blocks-pro' ),
					),
					array(
						'value' => self::POST_GROUP . '|post_excerpt',
						'label' => esc_attr__( 'Post Excerpt', 'kadence-blocks-pro' ),
					),
					array(
						'value' => self::POST_GROUP . '|post_id',
						'label' => esc_attr__( 'Post ID', 'kadence-blocks-pro' ),
					),
					array(
						'value' => self::POST_GROUP . '|post_date',
						'label' => esc_attr__( 'Post Date', 'kadence-blocks-pro' ),
					),
					array(
						'value' => self::POST_GROUP . '|post_date_modified',
						'label' => esc_attr__( 'Post Last Modified Date', 'kadence-blocks-pro' ),
					),
					array(
						'value' => self::POST_GROUP . '|post_featured_image',
						'label' => esc_attr__( 'Featured Image URL', 'kadence-blocks-pro' ),
					),
					array(
						'value' => self::POST_GROUP . '|post_type',
						'label' => esc_attr__( 'Post Type', 'kadence-blocks-pro' ),
					),
					array(
						'value' => self::POST_GROUP . '|post_status',
						'label' => esc_attr__( 'Post Status', 'kadence-blocks-pro' ),
					),
					array(
						'value' => self::POST_GROUP . '|post_custom_field',
						'label' => esc_attr__( 'Post Custom Field', 'kadence-blocks-pro' ),
					),
				),
			),
			// self::ARCHIVE_GROUP => array(
			// 	'label' => __( 'Archive', 'kadence-blocks-pro' ),
			// ),
			// self::SITE_GROUP => array(
			// 	'label' => __( 'Site', 'kadence-blocks-pro' ),
			// ),
			// self::MEDIA_GROUP => array(
			// 	'label' => __( 'Media', 'kadence-blocks-pro' ),
			// ),
			// self::AUTHOR_GROUP => array(
			// 	'label' => __( 'Author', 'kadence-blocks-pro' ),
			// ),
			// self::COMMENTS_GROUP => array(
			// 	'label' => __( 'Comments', 'kadence-blocks-pro' ),
			// ),
		);
		return apply_filters( 'kadence_block_pro_dynamic_text_fields_options', $options );
	}
	/**
	 * Render the dynamic shortcode.
	 *
	 * @param array $attributes the shortcode attributes.
	 */
	public function dynamic_shortcode_render( $attributes ) {
		$atts = shortcode_atts(
			array(
				'post'         => 'current',
				'source'       => 'core',
				'group'        => 'post',
				'type'         => 'text',
				'field'        => '',
				'custom'       => '',
				'force-string' => false,
				'before'       => null,
				'after'        => null,
				'fallback'     => null,
			),
			$attributes
		);

		// Sanitize Attributes.
		$post     = sanitize_text_field( $atts['post'] );
		$source   = sanitize_text_field( $atts['source'] );
		$group    = sanitize_text_field( $atts['group'] );
		$custom   = sanitize_text_field( $atts['custom'] );
		$field    = sanitize_text_field( $atts['field'] );
		$before   = sanitize_text_field( $atts['before'] );
		$after    = sanitize_text_field( $atts['after'] );
		$fallback = sanitize_text_field( $atts['fallback'] );

		if ( 'current' === $post ) {
			$post = get_the_ID();
		} else {
			$post = intval( $post );
		}

		$post = apply_filters( 'kadence_dynamic_post', $post, $source, $group, $field, $custom );

		$output = $this->get_field_content( $post, $source, $group, $field, $custom );

		if ( $atts['force-string'] && is_array( $output ) ) {
			if ( 'first' === $atts['force-string'] ) {
				$output = reset( $output );
			}
			if ( is_array( $output ) ) {
				$output = implode( ',', $output );
			}
		}
		if ( ! $output && null !== $fallback ) {
			return $fallback;
		}
		return $output;
	}
	/**
	 * Get the Shortcode output.
	 *
	 * @param object $post the post.
	 * @param string $source the source for the content.
	 * @param string $group the group of the content.
	 * @param string $field the field of the content.
	 */
	public function get_field_content( $post, $source, $group, $field, $custom ) {
		if ( 'core' === $source ) {
			// Render Core.
			if ( 'post' === $group ) {
				switch ( $field ) {
					case 'post_title':
						$output = wp_kses_post( get_the_title( $post ) );
						break;
					case 'post_date':
						$output = get_the_date( '', $post );
						break;
					case 'post_date_modified':
						$output = get_the_modified_date( '', $post );
						break;
					case 'post_type':
						$output = get_post_type( $post );
						break;
					case 'post_status':
						$output = get_post_status( $post );
						break;
					case 'post_id':
						$output = get_the_ID();
						break;
					case 'post_url':
						$output = get_permalink( $post );
						break;
					case 'post_excerpt':
						$output = get_the_excerpt( $post );
						break;
					case 'post_content':
						$output = get_the_content( $post );
						break;
					case 'post_custom_field':
						$output = '';
						if ( isset( $custom ) && ! empty( $custom ) ) {
							$output = get_post_meta( $post->ID, $custom, true );
						}
						break;
					case 'post_featured_image':
						$output = get_the_post_thumbnail_url( $post );
						break;
					default:
						$output = apply_filters( 'kadence_dynamic_content_core_post_{$field}_render', '', $post, $source, $group, $field, $custom  );
						break;
				}
			}
		} else {
			$output = apply_filters( 'kadence_dynamic_content_{$source}_render', $post, $source, $group, $field, $custom );
		}
		return apply_filters( 'kadence_dynamic_content_render', $output, $post, $source, $group, $field, $custom );
	}
	/**
	 * Get the title output.
	 */
	public function get_the_title() {
		if ( is_404() ) {
			?>
			<h1 class="page-title 404-page-title">
				<?php esc_html_e( 'Oops! That page can&rsquo;t be found.', 'kadence' ); ?>
			</h1>
			<?php
		} elseif ( is_home() && ! have_posts() ) {
			?>
			<h1 class="page-title post-home-title archive-title">
				<?php esc_html_e( 'Nothing Found', 'kadence' ); ?>
			</h1>
			<?php
		} elseif ( is_home() && ! is_front_page() ) {
			?>
			<h1 class="page-title post-home-title archive-title">
				<?php single_post_title(); ?>
			</h1>
			<?php
		} elseif ( is_search() ) {
			?>
			<h1 class="page-title search-title">
				<?php
				printf(
					/* translators: %s: search query */
					esc_html__( 'Search Results for: %s', 'kadence' ),
					'<span>' . get_search_query() . '</span>'
				);
				?>
			</h1>
			<?php
		} elseif ( is_archive() || is_home() ) {
			the_archive_title( '<h1 class="page-title archive-title">', '</h1>' );
		}
	}
}
Kadence_Blocks_Pro_Dynamic_Content::get_instance();
