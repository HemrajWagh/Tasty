<?php
if ( ! function_exists( 'sanitize_flagname' ) ) {
	function sanitize_flagname( $filename ) {

		//$filename = wp_strip_all_tags( $filename );
		//$filename = remove_accents( $filename );
		// Kill octets
		$filename = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '', $filename );
		$filename = preg_replace( '/&.+?;/', '', $filename ); // Kill entities
		$filename = preg_replace( '|[^a-zA-Z0-9 _.\-]|i', '', $filename );
		$filename = preg_replace( '/[\s-]+/', '-', $filename );
		$filename = trim( $filename, '.-_ ' );

		return $filename;
	}
}

function flag_skin_options( $act_skin ) {
	$flag_options = get_option( 'flag_options' );

	if ( isset( $_POST['skin'] ) && ! isset( $_POST['reset_skin_settings'] ) ) {
		check_admin_referer( 'skin_settings' );

		$skin_options     = $_POST['skin'];
		$skin_options_key = "{$act_skin}_options";
		array_walk_recursive( $skin_options, 'esc_attr' );

		$skin_preset = empty( $_POST['flagallery_preset'] ) ? '' : sanitize_text_field( trim( $_POST['flagallery_preset'] ) );
		if ( $skin_preset ) {
			$flag_options[ $skin_options_key ]['presets'][ $skin_preset ] = $skin_options;
		} else {
			$flag_options[ $skin_options_key ] = $skin_options;
		}
		// Save options.
		update_option( 'flag_options', $flag_options );
		flagGallery::show_message( __( 'Update Successfully', 'flash-album-gallery' ) );
	}

	/**
	 * @var $options_tree
	 * @var $default_options
	 */
	include $flag_options['skinsDirABS'] . $act_skin . '/settings.php';
	$gallery_settings = array();
	if ( empty( $skin_preset ) ) {
		$skin_preset = empty( $_GET['preset'] ) ? '' : sanitize_text_field( trim( $_GET['preset'] ) );
	}
	if ( isset( $_POST['reset_skin_settings'] ) ) {
		$gallery_settings = array();
		flagGallery::show_message( __( 'All settings fields reset to default. Click Update button to save default settings.', 'flash-album-gallery' ) );
	} elseif ( isset( $flag_options["{$act_skin}_options"] ) ) {
		$gallery_settings = maybe_unserialize( $flag_options["{$act_skin}_options"] );
		if ( $skin_preset && isset( $gallery_settings['presets'][ $skin_preset ] ) ) {
			$gallery_settings = $gallery_settings['presets'][ $skin_preset ];
		} else {
			unset( $gallery_settings['presets'] );
		}
	}

	require_once ( dirname(__FILE__) . '/get_skin.php');
	$skin_data = get_skin_data( $flag_options['skinsDirABS'] . $act_skin . '/' . $act_skin . '.php' );
	?>
	<div class="panel">
		<h2><?php echo esc_html( $skin_data['Name'] ); echo $skin_preset? ': ' . esc_html( $skin_preset ) : ''; ?></h2>
		<a href="#" id="toggle-postboxes" class="closed"><?php _e('Toggle Panels', 'flash-album-gallery'); ?></a>
	</div>
	<form id="skinOptions" class="wp-core-ui postbox-container" method="post" style="overflow:hidden; max-width: 100%; background-color:#f1f1f1; padding: 10px;">
		<div class="meta-box-sortables">
			<?php
			if ( isset( $options_tree ) ) {
				flag_skin_options_fieldset( $options_tree, $default_options, $gallery_settings );
			}
			wp_nonce_field( 'skin_settings' );
			?>
			<div class="textright flag-options-footer">
				<input type="hidden" name="flagallery_preset" value="<?php echo esc_attr( $skin_preset ); ?>" />
				<button type="submit" class="button button-secondary" name="reset_skin_settings"><?php _e( 'Reset to Default', 'flash-album-gallery' ) ?></button>
				&nbsp;&nbsp;&nbsp;
				<button type="submit" class="button button-primary"><?php _e( 'Update', 'flash-album-gallery' ) ?></button>
			</div>
			<script type="text/javascript">
	          jQuery(document).ready(function() {
	            jQuery('#skinOptions [data-type="color"]').spectrum({
	              showInput: true,
	              showAlpha: false,
	              allowEmpty: false,
	              preferredFormat: 'hex'
	            });
	            jQuery('#skinOptions [data-type="rgba"]').spectrum({
	              showInput: true,
	              showAlpha: true,
	              allowEmpty: false,
	              preferredFormat: 'rgb'
	            });
	          });
			</script>
		</div>
	</form>
	<?php
}

function flag_xml2array( $xmlObject, $out = array() ) {
	foreach ( (array) $xmlObject as $index => $node ) {
		$out[ $index ] = ( is_object( $node ) || is_array( $node ) ) ? flag_xml2array( $node ) : $node;
	}

	return $out;
}

/**
 * @param       $options_tree
 * @param       $default
 * @param array $value
 */
function flag_skin_options_fieldset( $options_tree, $default, $value = array() ) {
	$flag_options = get_option( 'flag_options' );
	$premium = !empty($flag_options['license_key']) && !empty($flag_options['license_name']);
	$i = 0;
	$panes_state = isset($_POST['pane_state'])? (array) $_POST['pane_state'] : [];
	foreach ( $options_tree as $section ) {
		$i ++;
		$pane_class = 'tab-pane postbox';
		$pane_open = !empty($panes_state[$i]);
		if(!$pane_open) {
			$pane_class .= ' closed';
		}
		?>
		<div id="gallery_settings<?php echo $i; ?>" class="<?php echo $pane_class; ?>">
			<button type="button" class="handlediv" aria-expanded="<?php echo $pane_open? 'true' : 'false'?>"><span class="toggle-indicator" aria-hidden="true"></span></button>
			<h2 class="hndle"><span><?php echo $section['label']; ?></span></h2>
			<input type="checkbox" class="postbox_state" name="pane_state[<?php echo $i; ?>]" <?php checked( $pane_open ); ?> />
			<div class="inside">
				<?php
				foreach ( $section['fields'] as $name => $field ) {
					if ( 'textblock' == $field['tag'] ) {
						$args = array(
							'id'    => $name,
							'field' => $field,
						);
					} else {
						if ( isset( $section['key'] ) ) {
							$key = $section['key'];
							if ( ! isset( $default[ $key ][ $name ] ) ) {
								$default[ $key ][ $name ] = false;
							}
							$val  = isset( $value[ $key ][ $name ] ) ? $value[ $key ][ $name ] : $default[ $key ][ $name ];
							$args = array(
								'id'      => strtolower( "{$key}_{$name}" ),
								'name'    => "skin[{$key}][{$name}]",
								'field'   => $field,
								'value'   => $val,
								'default' => $default[ $key ][ $name ],
							);
						} else {
							if ( ! isset( $default[ $name ] ) ) {
								$default[ $name ] = false;
							}
							$val  = isset( $value[ $name ] ) ? $value[ $name ] : $default[ $name ];
							$args = array(
								'id'      => strtolower( $name ),
								'name'    => "skin[{$name}]",
								'field'   => $field,
								'value'   => $val,
								'default' => $default[ $name ],
							);
						}
					}
					flag_skin_options_formgroup( $args, $premium );
				}
				?>
			</div>
		</div>
		<?php
	}
}

/**
 * @param $args
 */
function flag_skin_options_formgroup( $args, $premium = false ) {
	/**
	 * @var $id
	 * @var $name
	 * @var $field
	 * @var $value
	 * @var $default
	 */
	extract( $args );
	$premium_only = !$premium && !empty($field['premium']);
	if ( 'input' == $field['tag'] ) {
		?>
		<div class="form-group" id="div_<?php echo $id; ?>">
			<label><?php echo $field['label']; echo $premium_only? ' <span class="premium-only">*Premium</span>' : ''; ?></label>
			<input <?php echo $field['attr']; ?> id="<?php echo $id; ?>" class="form-control input-sm"
				name="<?php echo $name; ?>" value="<?php echo esc_attr( $value ); ?>"
				data-value="<?php echo $default; ?>"
				<?php echo $premium_only? 'disabled="disabled"' : ''; ?>
				placeholder="<?php echo $default; ?>"/>
			<?php if ( ! empty( $field['text'] ) ) {
				echo "<p class='help-block'>{$field['text']}</p>";
			} ?>
		</div>
	<?php } elseif ( 'checkbox' == $field['tag'] ) { ?>
		<div class="form-group" id="div_<?php echo $id; ?>">
			<div class="checkbox">
				<input type="hidden" name="<?php echo $name; ?>" value="0"/>
				<label><input type="checkbox" <?php echo $field['attr']; ?> id="<?php echo $id; ?>"
						name="<?php echo $name; ?>" value="1"
						data-value="<?php echo $default; ?>"
						<?php echo $premium_only? 'disabled="disabled"' : ''; ?>
						<?php echo checked( $value, '1' ); ?>/> <?php echo $field['label']; echo $premium_only? ' <span class="premium-only">*Premium</span>' : ''; ?>
				</label>
				<?php if ( ! empty( $field['text'] ) ) {
					echo "<p class='help-block'>{$field['text']}</p>";
				} ?>
			</div>
		</div>
	<?php } elseif ( 'select' == $field['tag'] ) { ?>
		<div class="form-group" id="div_<?php echo $id; ?>">
			<label><?php echo $field['label']; echo $premium_only? ' <span class="premium-only">*Premium</span>' : ''; ?></label>
			<select <?php echo $field['attr']; ?> id="<?php echo $id; ?>" class="form-control input-sm"
				name="<?php echo $name; ?>" data-value="<?php echo $default; ?>"
				<?php echo $premium_only? 'disabled="disabled"' : ''; ?>>
				<?php foreach ( $field['choices'] as $choice ) { ?>
					<option value="<?php esc_attr_e( $choice['value'] ); ?>" <?php echo selected( $value, $choice['value'] ); ?>><?php echo $choice['label']; ?></option>
				<?php } ?>
			</select>
			<?php if ( ! empty( $field['text'] ) ) {
				echo "<p class='help-block'>{$field['text']}</p>";
			} ?>
		</div>
	<?php } elseif ( 'textarea' == $field['tag'] ) { ?>
		<div class="form-group" id="div_<?php echo $id; ?>">
			<label><?php echo $field['label']; echo $premium_only? ' <span class="premium-only">*Premium</span>' : ''; ?></label>
			<textarea <?php echo $field['attr']; ?>
				id="<?php echo $id; ?>"
				class="form-control input-sm"
				name="<?php echo $name; ?>"
				<?php echo $premium_only? 'disabled="disabled"' : ''; ?>><?php echo esc_textarea( $value ); ?></textarea>
			<?php if ( ! empty( $field['text'] ) ) {
				echo "<p class='help-block'>{$field['text']}</p>";
			} ?>
		</div>
	<?php } elseif ( 'textblock' == $field['tag'] ) { ?>
		<div class="text-block">
			<?php echo $field['label']; ?>
			<?php echo $field['text']; ?>
		</div>
	<?php } ?>
	<?php
}
