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
class Kadence_Blocks_Pro_Custom_Icons {
	/**
	 * Instance of this class
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Icon post type query.
	 *
	 * @var $icon_query
	 */
	public static $icon_query = null;

	/**
	 * Icon post type query.
	 *
	 * @var $icon_array
	 */
	public static $icon_array = null;

	/**
	 * Icon post type query.
	 *
	 * @var $icon_names
	 */
	public static $icon_names = null;

	/**
	 * Show only one custom set.
	 *
	 * @var $only_custom
	 */
	public static $only_custom = false;

	/**
	 * Icon file contents.
	 *
	 * @var $icon_contents
	 */
	public static $icon_contents = null;

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
		add_action( 'init', array( $this, 'icon_post_type' ) );
		add_action( 'current_screen', array( $this, 'add_block_icons' ), 10 );

	}
	/**
	 * On init
	 */
	public function on_init() {
		if ( is_admin() ) {
			add_action( 'add_meta_boxes_kb_icon', array( $this, 'add_meta_box' ) );

			add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 20 );

			add_action( 'admin_enqueue_scripts', array( $this, 'metabox_admin_scripts' ), 10, 1 );
			add_action( 'save_post_kb_icon', array( $this, 'save_post_meta' ), 10, 3 );
			// add_action( 'manage_kb_icon_posts_custom_column', array( $this, 'render_columns' ), 10, 2 );
			// add_filter( 'manage_kb_icon_posts_columns', array( $this, 'manage_columns' ), 100 );
		}

		add_filter( 'upload_mimes', array( $this, 'extra_mime_types' ) );

		add_filter( 'wp_check_filetype_and_ext', array( $this, 'files_ext_webp' ), 10, 4 );
	}
	/**
	 * Add Icon manager link to admin menu
	 */
	public function register_admin_menu() {
		$menu_title = _x( 'Custom Icons', 'Kadence Blocks', 'kadence-blocks-pro' );
		add_submenu_page(
			'kadence-blocks',
			$menu_title,
			$menu_title,
			'manage_options',
			'edit.php?post_type=kb_icon'
		);
	}
	/**
	 * Load the media uploader and our meta upload file.
	 */
	public function metabox_admin_scripts( $hook ) {
		global $typenow;
		if ( 'kb_icon' === $typenow ) {
			wp_enqueue_media();
			// Registers and enqueues the required javascript.
			wp_register_script(
				'kb-custom-icons-meta',
				KBP_URL . 'dist/kb-custom-icons-admin.js',
				array( 'jquery' ),
				KBP_VERSION,
				true
			);
			wp_localize_script(
				'kb-custom-icons-meta',
				'kb_meta_file',
				array(
					'title' => __( 'Choose or Upload File', 'kadence-blocks-pro' ),
					'button' => __( 'Use this File', 'kadence-blocks-pro' ),
				)
			);
			wp_enqueue_script( 'kb-custom-icons-meta' );
		}
	}
	/**
	 * Add Meta Box
	 */
	public function add_meta_box() {
		add_meta_box(
			'kadence-custom-icons-metabox',
			__( 'Icon Set', 'kadence-blocks-pro' ),
			array( $this, 'render_metabox' ),
			'kb_icon',
			'normal',
			'default'
		);
	}
	/**
	 * Render Meta Box
	 */
	public function render_metabox() {
		// Variables.
		global $post;
		$selection     = get_post_meta( $post->ID, '_kb_icon_selection', true );
		$show_only     = get_post_meta( $post->ID, '_kb_icon_show_only', true );
		$option_values = array( 'false', 'true' );
		?>

			<fieldset class="kt_meta_box kb_icon_show_only" style="padding: 0 0 10px; border-bottom:1px solid #e9e9e9;">
				<?php
					if ( ! empty( $selection ) ) {
						$icons = $this->build_preview_icon_array( $selection );
						?>
						<div>
							<?php
							echo '<h3 class="kb-file-title"><strong>' . esc_html__( 'Icon Set Name:', 'kadence-blocks-pro' ) . '</strong> ' . esc_html( $this->get_icon_name( $selection ) ) . '</h3>';
							?>
							<input type="hidden" name="_kb_icon_selection" id="_kb_icon_selection" value="<?php echo esc_attr( $selection ); ?>">
							<div class="kb-meta-topbar-options" style="display: flex; justify-content: space-between;">
								<?php
								echo '<p class="kb-meta-desc"><strong>' . esc_html__( 'Icon Set Prefix:', 'kadence-blocks-pro' ) . '</strong> ' . esc_html( $this->get_icon_prefix( $selection ) ) . '</p>';
								?>
								<button type="button" class="button kb-meta-file-upload" id="kb_file_upload_btn" data-file-uploader-target="#_kb_icon_selection"><?php esc_html_e( 'Edit File', 'kadence-blocks-pro' ); ?></button>
							</div>
							<button type="button" class="button kb-meta-file-upload" style="display:none" id="kb_file_edit_btn" data-file-uploader-target="#_kb_icon_selection"><?php esc_html_e( 'Edit File', 'kadence-blocks-pro' ); ?></button>
							<p class="kb-meta-save-notice" style="display:none; background: #FFFAF0;padding: 10px;"><?php echo esc_html__( 'Save/Publish to see uploaded icons.', 'kadence-blocks-pro' ); ?></p>
							<ul class="kb-icon-previews" style="display: grid; grid-template-columns: repeat(auto-fill,minmax(105px,1fr)); grid-gap: 20px; padding: 15px 15px 0; overflow-y: auto;max-height: 575px; list-style: none;">
								<?php
								foreach ( $icons as $key => $item ) {
									echo '<li class="icon-item" style="position: relative;border-radius: 3px;border: 1px solid #eee;overflow: hidden;text-align: center;display: flex;flex-direction: column;padding: 10px 10px 0;align-items: center;color:#333;justify-content: center;">';
									echo '<svg style="display:inline-block;vertical-align:middle" viewBox="' . esc_attr( $item['vB'] ) . '" preserveAspectRatio="xMinYMin meet" height="24" width="24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">';
									foreach ( $item['cD'] as $subkey => $path ) {
										echo '<path d="' . esc_attr( $path['aBs']['d'] ) . '"></path>';
									}
									echo '</svg>';
									echo '<p style="font-size: 10px;font-weight: normal; color:#888;">' . esc_html( $key ) . '</p>';
									echo '</li>';
								}
								?>
							</ul>
						</div>
						<?php
					} else {
						?>
						<div>
							<label for="_kb_icon_selection"><?php esc_html_e( 'Selection.json file', 'kadence-blocks-pro' ); ?></label><br>
							<p class="kb-meta-desc"><?php echo esc_html__( 'Upload .json file from within the IcoMoon .zip file (Make sure each set has a unique prefix)', 'kadence-blocks-pro' ); ?></p>
							<input type="hidden" name="_kb_icon_selection" id="_kb_icon_selection" value="">
							<button type="button" class="button button-primary kb-meta-file-upload" id="kb_file_upload_btn" data-file-uploader-target="#_kb_icon_selection"><?php esc_html_e( 'Upload File', 'kadence-blocks-pro' ); ?></button>
							<button type="button" class="button kb-meta-file-upload" style="display:none" id="kb_file_edit_btn" data-file-uploader-target="#_kb_icon_selection"><?php esc_html_e( 'Edit File', 'kadence-blocks-pro' ); ?></button>
							<p class="kb-meta-save-notice" style="display:none;background: #FFFAF0;padding: 10px;"><?php echo esc_html__( 'Save/Publish to see uploaded icons.', 'kadence-blocks-pro' ); ?></p>
						</div>
						<?php
					}
				?>
			</fieldset>
			<div class="clearfixit" style="padding: 5px 0; clear:both;"></div>
			<fieldset class="kt_meta_box kb_icon_show_only" style="padding: 10px 0 0;">
				<div style="width: 18%; padding: 0 2% 0 0; float: left;">
					<label for="_kb_icon_show_only" style="font-weight: 600;"><?php echo esc_html__( 'Show only these icons', 'kadence-reading-time' ); ?></label>
				</div>
				<div style="width: 80%;float: right;">
					<select name="_kb_icon_show_only">
					<?php
					foreach ( $option_values as $value ) {
						if ( $value == $show_only ) {
							?>
							<option value="<?php echo esc_attr( $value ); ?>" selected><?php echo esc_attr( $value ); ?></option>
							<?php
						} else {
							?>
							<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_attr( $value ); ?></option>
							<?php
						}
					}
					?>
					</select>
				</div>
				<div class="clearfixit" style="padding: 5px 0; clear:both;"></div>
		<?php
		// Security field.
		wp_nonce_field( 'kb_icons_form_metabox_nonce', 'kb_icons_form_metabox_process' );
	}
	/**
	 * Save Meta Box.
	 *
	 * @param number $post_id the post id.
	 * @param object $post the post object.
	 * @param object $update the update object.
	 */
	public function save_post_meta( $post_id, $post, $update ) {
		// If this is an autosave, our form has not been submitted,
		// so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		// Check if user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return $post_id;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return $post_id;
		}

		$nonce_name   = isset( $_POST['kb_icons_form_metabox_process'] ) ? $_POST['kb_icons_form_metabox_process'] : '';
		$nonce_action = 'kb_icons_form_metabox_nonce';

		// Check if nonce is set.
		if ( ! isset( $nonce_name ) ) {
			return $post_id;
		}
		// Check if nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
			return $post_id;
		}
		if ( isset( $_POST['_kb_icon_selection'] ) ) {
			update_post_meta( $post_id, '_kb_icon_selection', sanitize_text_field( wp_unslash( $_POST['_kb_icon_selection'] ) ) );
		}

		if ( isset( $_POST['_kb_icon_show_only'] ) ) {
			update_post_meta( $post_id, '_kb_icon_show_only', sanitize_text_field( wp_unslash( $_POST['_kb_icon_show_only'] ) ) );
		}
	}
	/**
	 * Add icons to blocks.
	 */
	public function add_block_icons() {
		if ( ! is_admin() ) {
			return;
		}
		global $pagenow;
		if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
			$current_screen = get_current_screen();
			if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
				$icons = $this->kadence_icon_query();
				if ( $icons ) {
					if ( is_null( self::$icon_array ) || is_null( self::$icon_names ) ) {
						$this->build_block_icon_array();
					}
					wp_enqueue_script( 'kadence_blocks_custom_icons', KBP_URL . 'dist/kb-custom-icons.js', array( 'wp-blocks', 'wp-i18n', 'wp-element' ), KBP_VERSION, true );
					wp_localize_script(
						'kadence_blocks_custom_icons',
						'kadence_custom_icons',
						array(
							'icons'            => self::$icon_array,
							'icon_names'       => self::$icon_names,
							'only_custom_sets' => apply_filters( 'kadence_blocks_only_custom_icon_sets', false ),
							'only_custom'      => self::$only_custom,
						)
					);
				}
			}
		} else if ( 'nav-menus.php' === $pagenow ) {
			$icons = $this->kadence_icon_query();
				if ( $icons ) {
					if ( is_null( self::$icon_array ) || is_null( self::$icon_names ) ) {
						$this->build_block_icon_array();
					}
					wp_enqueue_script( 'kadence_blocks_custom_icons', KBP_URL . 'dist/kb-custom-icons.js', array( 'wp-blocks', 'wp-i18n', 'wp-element' ), KBP_VERSION, true );
					wp_localize_script(
						'kadence_blocks_custom_icons',
						'kadence_custom_icons',
						array(
							'icons'            => self::$icon_array,
							'icon_names'       => self::$icon_names,
							'only_custom_sets' => apply_filters( 'kadence_blocks_only_custom_icon_sets', false ),
							'only_custom'      => self::$only_custom,
						)
					);
				}
		}
	}
	/**
	 * Build Icon post type
	 */
	public function icon_post_type() {
		$icon_labels = array(
			'name'               => __( 'Custom Icons', 'kadence-blocks-pro' ),
			'singular_name'      => __( 'Custom Icon', 'kadence-blocks-pro' ),
			'add_new'            => __( 'Add New Custom Icon', 'kadence-blocks-pro' ),
			'add_new_item'       => __( 'Add New Custom Icon', 'kadence-blocks-pro' ),
			'edit_item'          => __( 'Edit Custom Icon', 'kadence-blocks-pro' ),
			'new_item'           => __( 'New Custom Icon', 'kadence-blocks-pro' ),
			'all_items'          => __( 'All Custom Icons', 'kadence-blocks-pro' ),
			'view_item'          => __( 'View Custom Icon', 'kadence-blocks-pro' ),
			'search_items'       => __( 'Search Custom Icons', 'kadence-blocks-pro' ),
			'not_found'          => __( 'No Custom Icons found', 'kadence-blocks-pro' ),
			'not_found_in_trash' => __( 'No Custom Icons found in Trash', 'kadence-blocks-pro' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Custom Icons', 'kadence-blocks-pro' ),
		);

		$icon_args = array(
			'labels'              => $icon_labels,
			'public'              => false,
			'publicly_queryable'  => false,
			'map_meta_cap'        => true,
			'show_ui'             => true,
			'exclude_from_search' => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'query_var'           => true,
			'rewrite'             => false,
			'has_archive'         => false,
			'show_in_rest'        => true,
			'hierarchical'        => false,
			'capability_type'     => 'post',
			'menu_icon'           => 'dashicons-editor-textcolor',
			'supports'            => array( 'title' ),
		);

		register_post_type( 'kb_icon', $icon_args );
	}
	/**
	 * Query Custom icons.
	 */
	public static function kadence_icon_query() {
		if ( is_null( self::$icon_query ) ) {
			$icon = new WP_Query(
				array(
					'post_type'      => 'kb_icon',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
				)
			);

			self::$icon_query = $icon->posts;
		}
		return self::$icon_query;
	}
	/**
	 * Get Icon Name.
	 *
	 * @param number $id the file id.
	 */
	public function get_icon_name( $id ) {
		$name = __( 'Custom Icon Set', 'kadence-blocks-pro' );
		if ( is_null( self::$icon_contents ) ) {
			$path = get_attached_file( $id );
			if ( ! empty( $path ) && file_exists( $path ) ) {
				self::$icon_contents = json_decode( file_get_contents( $path ) );
			}
		}
		if ( ! is_null( self::$icon_contents ) ) {
			$import = self::$icon_contents;
			if ( isset( $import->IcoMoonType ) && isset( $import->preferences ) && isset( $import->preferences->imagePref ) && isset( $import->preferences->imagePref->name ) ) {
				$name = $import->preferences->imagePref->name;
			}
		}
		return $name;
	}
	/**
	 * Get Icon prefix.
	 *
	 * @param number $id the file id.
	 */
	public function get_icon_prefix( $id ) {
		$prefix = __( 'Not Found', 'kadence-blocks-pro' );
		if ( is_null( self::$icon_contents ) ) {
			$path = get_attached_file( $id );
			if ( ! empty( $path ) && file_exists( $path ) ) {
				self::$icon_contents = json_decode( file_get_contents( $path ) );
			}
		}
		if ( ! is_null( self::$icon_contents ) ) {
			$import = self::$icon_contents;
			if ( isset( $import->IcoMoonType ) && isset( $import->preferences ) && isset( $import->preferences->imagePref ) && isset( $import->preferences->imagePref->prefix ) ) {
				$prefix = $import->preferences->imagePref->prefix;
			}
		}
		return $prefix;
	}
	/**
	 * Build custom Icons array for preview.
	 *
	 * @param number $id the file id.
	 */
	public function build_preview_icon_array( $id ) {
		$icons = array();
		if ( is_null( self::$icon_contents ) ) {
			$path = get_attached_file( $id );
			if ( ! empty( $path ) && file_exists( $path ) ) {
				self::$icon_contents = json_decode( file_get_contents( $path ) );
			}
		}
		if ( ! is_null( self::$icon_contents ) ) {
			$import = self::$icon_contents;
			if ( isset( $import->IcoMoonType ) && isset( $import->icons ) ) {
				foreach ( $import->icons as $icon_object ) {
					$icons[ $icon_object->properties->name ] = array(
						'name' => $icon_object->properties->name,
					);
					$first  = '0';
					$second = '0';
					if ( isset( $icon_object->icon->width ) ) {
						if ( $icon_object->icon->width > $import->height ) {
							$diff = $icon_object->icon->width - $import->height;
							$second = - floor( $diff / 2 );
						} elseif ( $icon_object->icon->width < $import->height ) {
							$diff = $import->height - $icon_object->icon->width;
							$first = - floor( $diff / 2 );
						}
					}
					$icons[ $icon_object->properties->name ] = array(
						'vB' => $first . ' ' . $second . ' ' . $import->height . ' ' . ( isset( $icon_object->icon->width ) ? $icon_object->icon->width : $import->height ),
						'cD' => array(),
					);
					foreach ( $icon_object->icon->paths as $key => $value ) {
						$icons[ $icon_object->properties->name ]['cD'][] = array(
							'nE'  => 'path',
							'aBs' => array( 'd' => $value ),
						);
					}
				}
			}
		}
		return $icons;
	}

	/**
	 * Build custom Icons array.
	 */
	public function build_block_icon_array() {
		$icon_array      = array( 'icons' => array() );
		$icon_name_array = array( 'icon_cats' => array() );
		$icons           = $this->kadence_icon_query();

		if ( $icons ) {
			foreach ( $icons as $icon ) {
				$title     = $icon->post_title;
				$show_only = get_post_meta( $icon->ID, '_kb_icon_show_only', true );
				$selection = get_post_meta( $icon->ID, '_kb_icon_selection', true );
				if ( ! empty( $title ) && ! empty( $selection ) ) {
					$path = get_attached_file( $selection );
					if ( ! empty( $path ) && file_exists( $path ) ) {
						if ( 'true' === $show_only ) {
							self::$icon_array = null;
							self::$icon_names = null;
							self::$only_custom = true;
						}
						$import = json_decode( file_get_contents( $path ) );
						if ( isset( $import->IcoMoonType ) && isset( $import->preferences ) && isset( $import->preferences->imagePref ) && isset( $import->preferences->imagePref->prefix ) ) {
							$prefix = $import->preferences->imagePref->prefix;
							$icon_name_array['icon_cats'][ $title ] = array();
							foreach ( $import->icons as $icon_object ) {
								$icon_name_array['icon_cats'][ $title ][] = $prefix . $icon_object->properties->name;
								$first  = '0';
								$second = '0';
								if ( isset( $icon_object->icon->width ) ) {
									if ( $icon_object->icon->width > $import->height ) {
										$diff = $icon_object->icon->width - $import->height;
										$second = - floor( $diff / 2 );
									} elseif ( $icon_object->icon->width < $import->height ) {
										$diff = $import->height - $icon_object->icon->width;
										$first = - floor( $diff / 2 );
									}
								}
								$icon_array['icons'][ $prefix . $icon_object->properties->name ] = array(
									'vB' => $first . ' ' . $second . ' ' . $import->height . ' ' . ( isset( $icon_object->icon->width ) ? $icon_object->icon->width : $import->height ),
									'cD' => array(),
								);
								foreach ( $icon_object->icon->paths as $key => $value ) {
									$icon_array['icons'][ $prefix . $icon_object->properties->name ]['cD'][] = array(
										'nE'  => 'path',
										'aBs' => array( 'd' => $value ),
									);
								}
							}
						}
					}
					if ( 'true' === $show_only ) {
						break;
					}
				}
			}
		}
		self::$icon_array = $icon_array;
		self::$icon_names = $icon_name_array;
	}
	/**
	 * Add mime types for json file
	 *
	 * @param array $mimes the upload mimes.
	 * @return array
	 */
	public function extra_mime_types( $mimes ) {
		$mimes['json'] = 'application/json';

		return $mimes;
	}

	/**
	 * Allow upload of json file.
	 *
	 * @param array  $types the upload types.
	 * @param string $file the file.
	 * @param string $filename the file name.
	 * @param array  $mimes the upload mimes.
	 * @return array
	 */
	public function files_ext_webp( $types, $file, $filename, $mimes ) {
		if ( false !== strpos( $filename, '.json' ) ) {
			$types['ext']  = 'json';
			$types['type'] = 'application/json';
		}
		return $types;
	}
}
Kadence_Blocks_Pro_Custom_Icons::get_instance();
