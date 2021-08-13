<?php

//define('WP_INSTALLING', true);
require_once( dirname( dirname( __FILE__ ) ) . '/flag-config.php' );
require_once( dirname( dirname( __FILE__ ) ) . '/lib/core.php' );
require_once( dirname( __FILE__ ) . '/skin_functions.php' );

add_filter( 'option_active_plugins', '__return_empty_array' );

if ( ! function_exists( 'wp_get_current_user' ) ) {
	require( ABSPATH . WPINC . '/formatting.php' );
	require( ABSPATH . WPINC . '/capabilities.php' );
	require( ABSPATH . WPINC . '/user.php' );
	require( ABSPATH . WPINC . '/meta.php' );
	require( ABSPATH . WPINC . '/pluggable.php' );
	require( ABSPATH . WPINC . '/post.php' );
	wp_cookie_constants();
}

// check for correct capability
if ( ! is_user_logged_in() ) {
	die( '-1' );
}

// check for correct FlAG capability
if ( ! current_user_can( 'FlAG Change skin' ) ) {
	die( '-1' );
}

$flag_options = get_option( 'flag_options' );
$act_skin     = isset( $_GET['skin'] ) ? $_GET['skin'] : $flag_options['flashSkin'];
$act_skin     = sanitize_flagname( $act_skin );

if ( isset( $_GET['show_options'] ) ) {
	?>
	<!doctype html>
	<html class="js">
	<head>
		<link rel='stylesheet' id='common-css' href='<?php echo get_admin_url( null, '/css/common.min.css' ); ?>' type='text/css'/>
		<link rel='stylesheet' id='dashicons-css' href='<?php echo includes_url( '/css/dashicons.min.css' ); ?>' type='text/css'/>
		<link rel='stylesheet' id='forms-css' href='<?php echo get_admin_url( null, '/css/edit.css' ); ?>' type='text/css'/>
		<link rel='stylesheet' id='forms-css' href='<?php echo get_admin_url( null, '/css/forms.min.css' ); ?>' type='text/css'/>
		<link rel='stylesheet' id='buttons-css' href='<?php echo includes_url( '/css/buttons.css' ); ?>' type='text/css'/>
		<link rel="stylesheet" id="flagadmin-css" href="<?php echo plugins_url( '/flash-album-gallery/admin/css/flagadmin.css' ); ?>" type="text/css"/>
		<link rel='stylesheet' id='wp-color-picker-css' href='<?php echo plugins_url( '/flash-album-gallery/assets/spectrum/spectrum.min.css' ); ?>' type='text/css' media='all'/>
		<script type='text/javascript' src='<?php echo includes_url( '/js/jquery/jquery.js' ); ?>'></script>
		<script type='text/javascript' src='<?php echo includes_url( '/js/jquery/jquery-migrate.js' ); ?>'></script>
		<style>
			html, body {
				height: auto;
			}
			#poststuff {
				padding: 0 0 35px;
				display: flex;
				flex-direction: column;
				overflow-y: scroll;
			}
			#poststuff * {
				box-sizing: border-box;
			}
			#poststuff h2.hndle {
				cursor: pointer;
				color: #fff;
				background-color: lightslategrey;
			}
			#poststuff .handlediv{
				position: absolute;
    		right: 10px;
			}
			body > .wrap {
				margin-right: 0;
			}
			body > .wrap h2 {
				display: none;
			}

			span.toggle-indicator {
				color: #fff;
			}
			.sp-replacer {
				display: block;
				max-width: 174px;
				border-radius: 3px;
			}

			.sp-preview {
				width: calc(100% - 18px);
			}
			.panel {
				position: sticky;
				top: 0;
				width: 100%;
				display: flex;
				z-index: 10;
				background-color: #fff;
				border-bottom: 1px solid #b3b2b3;
				padding: 4px 20px 4px 10px;
				align-items: center;
			}
			.panel h2 {
				flex: 1 1 auto;
			}
			.postbox input.postbox_state {
				visibility: hidden;
				pointer-events: none;
				position: absolute;
				top: 0;
			}
		</style>
	</head>
	<body id="poststuff">
	<?php flag_skin_options( $act_skin ); ?>

	<script type='text/javascript' src='<?php echo get_admin_url( null, '/js/svg-painter.js' ); ?>'></script>
	<script type='text/javascript' src='<?php echo includes_url( '/js/jquery/ui/core.min.js' ); ?>'></script>
	<script type='text/javascript' src='<?php echo includes_url( '/js/jquery/ui/widget.min.js' ); ?>'></script>
	<script type='text/javascript' src='<?php echo includes_url( '/js/jquery/ui/mouse.min.js' ); ?>'></script>
	<script type='text/javascript' src='<?php echo includes_url( '/js/jquery/ui/draggable.min.js' ); ?>'></script>
	<script type='text/javascript' src='<?php echo includes_url( '/js/jquery/ui/droppable.min.js' ); ?>'></script>
	<script type='text/javascript' src='<?php echo includes_url( '/js/jquery/ui/sortable.min.js' ); ?>'></script>
	<script type='text/javascript' src='<?php echo includes_url( '/js/jquery/jquery.ui.touch-punch.js' ); ?>'></script>
	<script type='text/javascript' src='<?php echo includes_url( '/js/jquery/ui/slider.min.js' ); ?>'></script>
	<script type='text/javascript' src='<?php echo get_admin_url( null, '/js/iris.min.js' ); ?>'></script>
	<script type='text/javascript' src='<?php echo plugins_url( '/flash-album-gallery/assets/spectrum/spectrum.min.js' ); ?>'></script>
	<script>
		var $handles = jQuery('.postbox .hndle, .postbox .handlediv');
		$handles.on('click.postboxes', function() {
				var $el = jQuery(this),
					p = $el.parent('.postbox'),
					ariaExpandedValue;

				p.toggleClass('closed');

				ariaExpandedValue = !p.hasClass('closed');

				if ($el.hasClass('handlediv')) {
					// The handle button was clicked.
					$el.attr('aria-expanded', ariaExpandedValue);
				}
				else {
					// The handle heading was clicked.
					$el.closest('.postbox').find('button.handlediv').attr('aria-expanded', ariaExpandedValue);
				}
				jQuery('.postbox_state', p).prop('checked', ariaExpandedValue);
			}
		);

		jQuery('#toggle-postboxes').on('click', function() {
			var ariaExpandedValue,
				p = jQuery('.postbox'),
				$el = jQuery('.handlediv', p);

			ariaExpandedValue = !jQuery(this).hasClass('closed');
			jQuery(this).toggleClass('closed', ariaExpandedValue);
			p.toggleClass('closed', ariaExpandedValue);
			$el.attr('aria-expanded', !ariaExpandedValue);
			jQuery('.postbox_state', p).prop('checked', !ariaExpandedValue);
		});
	</script>
	</body>
	</html>
	<?php
} ?>
