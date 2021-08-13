<?php
/**
 * Admin Form Entries page
 *
 * @package Kadence Blocks Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Admin Form Entries list
 */
class KB_Form_Admin_Entries {

	/**
	 * Instance Control
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Entry WP_List_Table object
	 *
	 * @var object
	 */
	public $entries_table_list;
	/**
	 * KB_Form_Admin_Entries constructor.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	/**
	 * KB_Form_Admin_Entries constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'add_menu' ), 20 );
		}

	}
	/**
	 * Add option page menu
	 */
	public function add_menu() {
		$page = add_submenu_page( 'kadence-blocks',  __( 'Kadence Blocks - Form Entries', 'kadence-blocks-pro' ), __( 'Form Entries', 'kadence-blocks-pro' ), 'edit_pages', 'kadence-blocks-entries', array( $this, 'page_output' ) );
		//$page = add_options_page( __( 'Kadence Blocks - Form Entries', 'kadence-blocks-pro' ), __( 'Kadence Form Entries', 'kadence-blocks-pro' ), 'edit_pages', 'kadence-blocks-entries', array( $this, 'page_output' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'scripts' ) );
		add_action( 'load-' . $page, array( $this, 'entries_page_init' ) );
	}
	/**
	 * Loads entries into memory.
	 */
	public function entries_page_init() {
		if ( ! isset( $_GET['view-entry'] ) ) {
			$this->entries_table_list = new KB_Form_Admin_Entries_Table_List();

			// Add screen option.
			add_screen_option(
				'per_page',
				array(
					'default' => 20,
					'option'  => 'kb_entries_per_page',
				)
			);
		}
	}
	/**
	 * Page output.
	 */
	public function page_output() {
		?>
		<div class="kt_plugin_welcome_title_head">
			<div class="kt_plugin_welcome_head_container">
				<a href="https://www.kadenceblocks.com/" class="kt_plugin_welcome_logo_area">
					<div class="kt_plugin_welcome_logo">
						<img src="<?php echo KBP_URL . 'dist/settings/img/kadence-logo.png'; ?>">
					</div>
					<div class="kt_plugin_welcome_logo_title">
						<h1>
							<?php echo esc_html__( 'Kadence Blocks', 'kadence-blocks-pro' ); ?>
						</h1>
						<h4>
							<?php echo esc_html__( 'Page Builder Toolkit', 'kadence-blocks-pro' ); ?>
						</h4>
					</div>
				</a>
			</div>
		</div>
		<?php
		if ( isset( $_GET['view-entry'] ) ) {
			$form_id  = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
			$entry_id = isset( $_GET['view-entry'] ) ? absint( $_GET['view-entry'] ) : 0;
			$entries = new KBP\Queries\Entry();
			$entry = $entries->get_item( $entry_id );
			$this->single_view_output( $entry );
		} else {
			$this->table_list_output();
		}
	}
	/**
	 * Table list output.
	 */
	public function single_view_output( $entry ) {
		?>
		<div id="kb-form-entries-single" class="kb-form-entries wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Form Entry', 'kadence-blocks-pro' ); ?></h1>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=kadence-blocks-entries' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Back to All Entries', 'kadence-blocks-pro' ); ?></a>
			<hr class="wp-header-end">

			<?php settings_errors(); ?>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<div class="kadence-blocks-single-entry-view">
						<div id="kadence-blocks-entry-fields" class="postbox">
							<h2 class="hndle">
								<span><?php printf( __( '%1$s : Entry #%2$s', 'kadence-blocks-pro' ), esc_html( $entry->get_name() ), absint( $entry->get_id() ) ); ?></span>
							</h2>
							<table class="kb-form-entries-single-table wp-list-table widefat fixed striped posts">
								<tbody>
								<?php
									$meta = get_metadata( 'kbp_form_entry', $entry->get_id() );
									if ( $meta ) {
										if ( is_array( $meta ) ) {
											foreach ( $meta as $meta_key => $meta_value ) {
												if ( 'kb_field_' === substr( $meta_key, 0, 9 ) ) {
													$field = maybe_unserialize( $meta[ $meta_key ][ 0 ] );
													$value = ( is_array( $field['value'] ) ? implode( ', ', $field['value'] ) : $field['value'] );
													echo '<tr class="kadence-forms-entry-field"><th>';
													echo '<strong>' . $field['label'] . ':</strong> ' . ( ! empty( $value ) ? $value : __( 'No Data', 'kadence-blocks-pro' ) );
													echo '</th></tr>';
												}
											}
										} else {
											echo '<tr class="kadence-forms-entry-field"><th>';
											echo '<strong>' . __( 'No Data', 'kadence-blocks-pro' ) . '</strong>';
											echo '</th></tr>';
										}
									} else {
										echo '<tr class="kadence-forms-entry-field"><th>';
										echo '<strong>' . __( 'No Data', 'kadence-blocks-pro' ) . '</strong>';
										echo '</th></tr>';
									}
								?>
								</tbody>
							</table>
						</div>
						<div id="postbox-container-1" class="postbox-container kadence-blocks-entry-details">
							<div id="kadence-blocks-entry-details" class="postbox">
									<h2 class="hndle">
										<span><?php esc_html_e( 'Entry Details', 'kadence-blocks-pro' ); ?></span>
									</h2>
									<div class="inside">
										<div class="kadence-blocks-entry-details-meta">
											<p class="kadence-blocks-entry-id">
												<span class="dashicons dashicons-admin-network"></span>
												<?php esc_html_e( 'Entry ID:', 'kadence-blocks-pro' ); ?>
												<strong><?php echo absint( $entry->get_id() ); ?></strong>
											</p>
											<p class="kadence-blocks-entry-form-id" data-form-id="<?php echo esc_attr( $entry->get_form_id() ); ?>">
												<span class="dashicons dashicons-id"></span>
												<?php esc_html_e( 'Form ID:', 'kadence-blocks-pro' ); ?>
												<strong><?php echo esc_html( $entry->get_form_id() ); ?></strong>
											</p>
											<p class="kadence-blocks-entry-form-name" data-form-id="<?php echo esc_attr( $entry->get_form_id() ); ?>">
												<span class="dashicons dashicons-nametag"></span>
												<?php esc_html_e( 'Form Name:', 'kadence-blocks-pro' ); ?>
												<strong><?php echo esc_html( $entry->get_name() ); ?></strong>
											</p>
											<p class="kadence-blocks-entry-date">
												<span class="dashicons dashicons-calendar"></span>
												<?php esc_html_e( 'Submitted:', 'kadence-blocks-pro' ); ?>
												<strong><?php echo $entry->get_date_created(); ?></strong>
											</p>

											<?php if ( ! empty( $entry->get_user_id() ) && 0 !== $entry->get_user_id() ) : ?>
												<p class="kadence-blocks-entry-user">
													<span class="dashicons dashicons-admin-users"></span>
													<?php
													esc_html_e( 'User:', 'kadence-blocks-pro' );
													$user      = get_userdata( $entry->get_user_id() );
													$user_name = esc_html( ! empty( $user->display_name ) ? $user->display_name : $user->user_login );
													$user_url  = esc_url(
														add_query_arg(
															array(
																'user_id' => absint( $user->ID ),
															),
															admin_url( 'user-edit.php' )
														)
													);
													?>
													<strong><a href="<?php echo $user_url; ?>"><?php echo $user_name; ?></a></strong>
												</p>
											<?php endif; ?>
											<?php if ( ! empty( $entry->get_referer() ) ) : ?>
												<p class="kadence-blocks-entry-referer">
													<span class="dashicons dashicons-text-page"></span>
													<?php esc_html_e( 'Page:', 'kadence-blocks-pro' ); ?>
													<strong><?php echo '<a href="' . esc_url( $entry->get_referer() ) . '"> ' . esc_html( $entry->get_referer() ) . '</a>'; ?></strong>
												</p>
											<?php endif; ?>
											<?php if ( ! empty( $entry->get_user_ip() ) ) : ?>
												<p class="kadence-blocks-entry-ip">
													<span class="dashicons dashicons-location"></span>
													<?php esc_html_e( 'User IP:', 'kadence-blocks-pro' ); ?>
													<strong><?php echo esc_html( $entry->get_user_ip() ); ?></strong>
												</p>
											<?php endif; ?>
											<?php if ( ! empty( $entry->get_user_device() ) ) : ?>
												<p class="kadence-blocks-entry-device">
													<span class="dashicons dashicons-laptop"></span>
													<?php esc_html_e( 'User Device:', 'kadence-blocks-pro' ); ?>
													<strong><?php echo esc_html( $entry->get_user_device() ); ?></strong>
												</p>
											<?php endif; ?>
										</div>

										<div id="major-publishing-actions">
											<div id="delete-action">
												<?php
												echo sprintf(
													'<a class="button button-large button-secondary" href="%s"><span class="dashicons dashicons-trash"></span>%s</a>',
													esc_url(
														wp_nonce_url(
															add_query_arg(
																array(
																	'action' => 'trash',
																	'entry'  => $entry->get_id(),
																),
																admin_url( 'admin.php?page=kadence-blocks-entries' )
															),
															'kb_form_delete_entry'
														)
													),
													__( 'Move to trash', 'kadence-blocks-pro' )
												);
												?>
											</div>
											<div class="clear"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="clear"></div>
					</div>
					<div class="clear"></div>
				</div>
			</div>
		</div>
		<?php
	}
	/**
	 * Table list output.
	 */
	public function table_list_output() {
		$this->entries_table_list->prepare_items();
		$this->entries_table_list->process_bulk_action();
		?>
		<div id="kb-form-entries-list" class="kb-form-entries wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Form Entries', 'kadence-blocks-pro' ); ?></h1>
			<hr class="wp-header-end">

			<?php settings_errors(); ?>
			<div id="poststuff">
				<form id="kb-form-entries-form" method="post">
					<?php
						$this->entries_table_list->views();
						//$this->entries_table_list->extra_tablenav( 'top' );
						$this->entries_table_list->search_box( __( 'Search entries', 'kadence-blocks-pro' ), 'kbp_form_entry' );
						$this->entries_table_list->display();
					?>
				</form>
				<br class="clear">
			</div>
		</div>
		<?php
	}
	/**
	 * Loads admin style sheets and scripts
	 */
	public function scripts() {
		wp_enqueue_style( 'kadence-blocks-admin-css', KBP_URL . '/dist/form/admin/entries-style.css', array(), KBP_VERSION, 'all' );
		// wp_enqueue_script( 'kadence-blocks-form-admin-js', KBP_URL . '/dist/settings/scripts.js', array( 'jquery', 'jquery-ui-dialog', 'wp-color-picker' ), KBP_VERSION, true );
		// wp_localize_script(
		// 	'kadence-blocks-form-admin-js',
		// 	'kb_form_params',
		// 	array(
		// 		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		// 		'wpnonce' => wp_create_nonce( 'kb_forms' ),
		// 	)
		// );
	}
}
KB_Form_Admin_Entries::get_instance();
