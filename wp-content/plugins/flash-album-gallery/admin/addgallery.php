<?php
if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// sometimes a error feedback is better than a white screen
@ini_set( 'error_reporting', E_ALL ^ E_NOTICE );

function flag_admin_add_gallery() {

	global $wpdb, $flagdb, $flag;

	// same as $_SERVER['REQUEST_URI'], but should work under IIS 6.0
	$filepath = admin_url() . 'admin.php?page=' . urlencode( $_GET['page'] );

	// check for the max image size
	$maxsize = flagGallery::check_memory_limit();

	$defaultpath = $flag->options['galleryPath'];

	if ( $_POST['addgallery'] ) {
		check_admin_referer( 'flag_addgallery' );
		$newgallery = trim( $_POST['galleryname'] );
		if ( ! empty( $newgallery ) ) {
			flagAdmin::create_gallery( $newgallery, $defaultpath );
		}
	}
	if ( $_POST['uploadimage'] ) {
		check_admin_referer( 'flag_upload' );

		$flag->options['thumbWidth']  = intval( $_POST['thumbWidth'] ) ? intval( $_POST['thumbWidth'] ) : 300;
		$flag->options['thumbHeight'] = intval( $_POST['thumbHeight'] ) ? intval( $_POST['thumbHeight'] ) : 300;
		update_option( 'flag_options', $flag->options );

		if ( $_FILES['MF__F_0_0']['error'] == 0 ) {
			flagAdmin::upload_images();
		} else {
			flagGallery::show_error( __( 'Upload failed!', 'flash-album-gallery' ) );
		}
	}
	if ( $_POST['importfolder'] ) {
		check_admin_referer( 'flag_addgallery' );
		$galleryfolder = $_POST['galleryfolder'];
		if ( ( ! empty( $galleryfolder ) ) AND ( $defaultpath != $galleryfolder ) AND false === strpos( $galleryfolder, '..' ) ) {
			flagAdmin::import_gallery( $galleryfolder );
		}
	}


	if ( isset( $_POST['disable_flash'] ) ) {
		check_admin_referer( 'flag_upload' );
		$flag->options['swfUpload'] = false;
		update_option( 'flag_options', $flag->options );
	}

	if ( isset( $_POST['enable_flash'] ) ) {
		check_admin_referer( 'flag_upload' );
		$flag->options['swfUpload'] = true;
		update_option( 'flag_options', $flag->options );
	}

	//get all galleries (after we added new ones)
	$gallerylist = $flagdb->find_all_galleries( $flag->options['albSort'], $flag->options['albSortDir'], false, 0, 0, 0, true );

	?>

	<?php if ( ! IS_WPMU || current_user_can( 'FlAG Import folder' ) ) { ?>
		<link rel="stylesheet" type="text/css" href="<?php echo FLAG_URLPATH; ?>admin/js/jqueryFileTree/jqueryFileTree.css"/>
		<script type="text/javascript" src="<?php echo FLAG_URLPATH; ?>admin/js/jqueryFileTree/jqueryFileTree.js"></script>
		<script type="text/javascript">
			/* <![CDATA[ */
			jQuery(function(){
				jQuery("span.browsefiles").show().click(function(){
					jQuery("#file_browser").fileTree({
						script: "admin-ajax.php?action=flag_file_browser&nonce=<?php echo wp_create_nonce( 'flag-ajax' ) ;?>",
						root: jQuery("#galleryfolder").val()
					}, function(file){
						//var path = file.replace("<?php echo WINABSPATH; ?>", "");
						jQuery("#galleryfolder").val(file);
					});

					jQuery("#file_browser").show("slide");
				});
			});
			/* ]]> */
		</script>
	<?php }
    if ( ! ( is_plugin_active( 'woowgallery/woowgallery.php' ) || ! empty( $flag->options['hide_woow'] ) ) ){
	    ?>
        <div class="promote-woowbox" style="padding-top:10px"><a href="https://bit.ly/flag-woowgallery" target="_blank"><img src="<?php echo plugins_url('/flash-album-gallery/admin/images/woowbox-promote.png') ?>" alt="Try WoowGallery plugin" /></a></div>
        <?php
    }
	?>
	<div id="slider" class="flag-wrap">

		<ul id="tabs" class="tabs">
			<li class="selected"><a href="#" rel="addgallery"><?php _e( 'Add new gallery', 'flash-album-gallery' ); ?></a></li>
			<li><a href="#" rel="uploadimage"><?php _e( 'Upload Images', 'flash-album-gallery' ); ?></a></li>
			<?php if ( ! IS_WPMU || current_user_can( 'FlAG Import folder' ) ) { ?>
				<li><a href="#" rel="importfolder"><?php _e( 'Import image folder', 'flash-album-gallery' ); ?></a></li>
			<?php } ?>
		</ul>

		<!-- create gallery -->
		<div id="addgallery" class="cptab">
			<h2><?php _e( 'Create a new gallery', 'flash-album-gallery' ); ?></h2>

			<form name="addgallery" id="addgallery_form" method="POST" action="<?php echo $filepath; ?>" accept-charset="utf-8">
				<?php wp_nonce_field( 'flag_addgallery' ); ?>
				<table class="form-table" style="width: auto;">
					<tr>
						<th scope="col" colspan="2" style="padding-bottom: 0;">
							<strong><?php _e( 'New Gallery', 'flash-album-gallery' ); ?></strong></th>
					</tr>
					<tr valign="top">
						<td><input type="text" size="65" name="galleryname" value=""/><br/>
							<?php if ( ! IS_WPMU ) { ?>
								<?php _e( 'Create a new , empty gallery below the folder', 'flash-album-gallery' ); ?>
								<strong><?php echo $defaultpath; ?></strong><br/>
							<?php } ?>
							<i>( <?php _e( 'Allowed characters for file and folder names are', 'flash-album-gallery' ); ?>: a-z, A-Z, 0-9, -, _ )</i>
						</td>
						<?php do_action( 'flag_add_new_gallery_form' ); ?>
						<td style="vertical-align:top;">
							<div class="submit" style="margin: 0; padding: 0;">
								<input class="button-primary" type="submit" name="addgallery" value="<?php _e( 'Add gallery', 'flash-album-gallery' ); ?>"/>
							</div>
						</td>
					</tr>
				</table>
				<p>&nbsp;</p>
			</form>
		</div>
		<!-- upload images -->
		<div id="uploadimage" class="cptab">
			<h2><?php _e( 'Upload images', 'flash-album-gallery' ); ?></h2>

			<form name="uploadimage" id="gmUpload" method="POST" enctype="multipart/form-data" action="<?php echo $filepath; ?>" accept-charset="utf-8">
				<?php wp_nonce_field( 'flag_upload' ); ?>
				<table class="flag-form-table">
					<tr valign="top">
						<td style="width: 216px;">
							<label for="galleryselect"><?php _e( 'Upload images in', 'flash-album-gallery' ); ?> *</label>
							<select name="galleryselect" id="galleryselect" style="width: 200px">
								<option value="0"><?php _e( 'Choose gallery', 'flash-album-gallery' ); ?></option>
								<?php $ingallery = isset( $_GET['gid'] ) ? (int) $_GET['gid'] : '';
								foreach ( $gallerylist as $gallery ) {
									if ( ! flagAdmin::can_manage_this_gallery( $gallery->author ) ) {
										continue;
									}
									$name = ( empty( $gallery->title ) ) ? $gallery->name : esc_html( stripslashes( $gallery->title ) );
									if ( $flag->options['albSort'] == 'gid' ) {
										$name = '#' . $gallery->gid . ' - ' . $name;
									}
									if ( $flag->options['albSort'] == 'title' ) {
										$name = $name . ' (#' . $gallery->gid . ')';
									}
									$sel = ( $ingallery == $gallery->gid ) ? 'selected="selected" ' : '';
									echo '<option ' . $sel . 'value="' . $gallery->gid . '" >' . $name . '</option>' . "\n";
								} ?>
							</select>
							<?php echo $maxsize; ?>
							<br/><?php if ( ( IS_WPMU ) && flagGallery::flag_wpmu_enable_function( 'wpmuQuotaCheck' ) ) {
								display_space_usage();
							} ?>
							<br/>

							<p><?php _e( 'Thumbnail WIDTH x HEIGHT (in pixel)', 'flash-album-gallery' ); ?> *
								<br/><input type="number" size="5" maxlength="5" min="300" max="800" name="thumbWidth" id="thumbWidth" value="<?php echo $flag->options['thumbWidth']; ?>"/> x
								<input type="number" size="5" maxlength="5" min="300" max="800" name="thumbHeight" id="thumbHeight" value="<?php echo $flag->options['thumbHeight']; ?>"/>
								<br/>
								<small><?php _e( 'These values are maximum values ', 'flash-album-gallery' ); ?></small>
							</p>

							<div class="submit">
					<span class="useflashupload">
					<?php if ( $flag->options['swfUpload'] ) { ?>
						<input type="submit" class="button-secondary" name="disable_flash" id="disable_flash" title="<?php _e( 'The batch upload via Plupload, disable it if you have problems', 'flash-album-gallery' ); ?>" value="<?php _e( 'Switch to Browser Upload', 'flash-album-gallery' ); ?>"/>
					<?php } else { ?>
						<input type="submit" class="button-secondary" name="enable_flash" id="enable_flash" title="<?php _e( 'Upload multiple files at once by ctrl/shift-selecting in dialog', 'flash-album-gallery' ); ?>" value="<?php _e( 'Switch to Plupload based Upload', 'flash-album-gallery' ); ?>"/>
					<?php } ?>
					</span>

								<div class="clear"></div>
							</div>

						</td>

						<td>
							<div id="pluploadUploader">
								<?php if ( ! $flag->options['swfUpload']) { ?>
								<strong><?php _e( 'Upload image(s):', 'flash-album-gallery' ); ?></strong><br>
								<input type="file" name="imagefiles[]" id="imagefiles" size="35" class="imagefiles"/>
							</div>
						<span id="choosegalfirst">
							<input class="button-primary" type="submit" name="uploadimage" id="uploadimage_btn" value="<?php _e( 'Upload images', 'flash-album-gallery' ); ?>"/>
							<span class="disabledbut" style="display: none;"></span>
						</span>
							<?php } ?>
						</td>
					</tr>
				</table>
				<div id="pl-message"></div>
			</form>
			<?php if ( $flag->options['swfUpload'] ) {
				$nonce = wp_create_nonce( 'flag_upload' );
				?>
				<script type="text/javascript">
					// Convert divs to queue widgets when the DOM is ready
					jQuery(function($){
						var files_remaining = 0;
						$("#pluploadUploader").plupload({
							runtimes: 'html5,flash,html4',
							url: '<?php echo str_replace( '&#038;', '&', wp_nonce_url( admin_url('admin-ajax.php?action=plupload_uploader'), 'flag_upload' ) ); ?>',
							multipart: true,
							multipart_params: {postData: '', pluploadimage: 1},
							max_file_size: '<?php echo (floor( wp_max_upload_size() * 0.99 / 1024 / 1024 ) - 1); ?>Mb',
							unique_names: false,
							rename: true,
							chunk_size: 0,
							max_retries: 2,
							sortable: true,
							dragdrop: true,
							views: {
								list: true,
								thumbs: true,
								active: 'thumbs'
							},
							filters: [{title: "Images", extensions: "jpeg,jpg,gif,png"}],
							flash_swf_url: '<?php echo plugins_url( FLAGFOLDER. '/admin/js/plupload/plupload.flash.swf'); ?>'

						});

						var uploader = $("#pluploadUploader").plupload('getUploader');
						uploader.bind('QueueChanged StateChanged', function(up){
							if(up.state == plupload.QUEUED){
								files_remaining = up.files.length;
							}
							if(up.state == plupload.STARTED){
								up.settings.multipart_params = {
									galleryselect: jQuery('#galleryselect').val(),
									thumbw: jQuery('#thumbWidth').val(),
									thumbh: jQuery('#thumbHeight').val(),
									last: files_remaining,
									action: 'flag_plupload_uploader',
									_wpnonce: '<?php echo $nonce; ?>'
								};
							}
							if($("#galleryselect").val() == 0){
								$("#pluploadUploader_start").addClass('ui-button-disabled ui-state-disabled');
							}
							console.log('[StateChanged]', up.state, up.settings.multipart_params);
						});
						uploader.bind('ChunkUploaded', function(up, file, info){
							console.log('[ChunkUploaded] File:', file, "Info:", info);
							var response = {};
							try {
                                response = jQuery.parseJSON(info.response);
                            } catch(e) {
								response.error = info.response;
                            }
							if(response && response.error){
								up.stop();
								file.status = plupload.FAILED;
	                            jQuery('<div/>').addClass('error').html('<span><u><em>' + file.name + ':</em></u> ' + response.error + '</span>').appendTo('#pl-message');
								console.log(response.error);
								up.trigger('QueueChanged StateChanged');
								up.trigger('UploadProgress', file);
								up.start();
							}
						});
						uploader.bind('FileUploaded', function(up, file, info){
							console.log('[FileUploaded] File:', file, "Info:", info);
							files_remaining--;
							if(info.response){
								file.status = plupload.FAILED;
								jQuery('<div/>').addClass('error').html('<span><u><em>' + file.name + ':</em></u> ' + info.response + '</span>').appendTo('#pl-message');
							}
						});
						uploader.bind('UploadProgress', function(up, file){
							var percent = uploader.total.percent;
							$('#total-progress-info .progress-bar').css('width', percent + "%").attr('aria-valuenow', percent);
						});
						uploader.bind('Error', function(up, args){
							jQuery('<div/>').addClass('error').html('<span><u><em>' + args.file.name + ':</em></u> ' + args.message + ' ' + args.status + '</span>').appendTo('#pl-message');
							console.log('[error] ', args);
						});
						uploader.bind('UploadComplete', function(up, files){
							console.log('[UploadComplete]', files);
							jQuery('<div/>').addClass('success').html('<?php _e('Done!', 'flash-album-gallery'); ?> <a href="<?php echo wp_nonce_url( $flag->manage_page->base_page . "&mode=edit", 'flag_editgallery'); ?>&gid=' + jQuery("#galleryselect").val() + '">Open Gallery</a>').appendTo('#pl-message');
						});

						jQuery("#gmUpload").on('click', '.ui-button-disabled', function(e){
							if(files_remaining){
								alert("Choose gallery, please.")
							}
						});
						jQuery("#galleryselect").change(function(){
							if(jQuery(this).val() == 0){
								jQuery("#pluploadUploader_start").addClass('ui-button-disabled ui-state-disabled');
							} else{
								if(files_remaining){
									jQuery("#pluploadUploader_start").removeClass('ui-button-disabled ui-state-disabled');
								}
							}
						});

					});
				</script>
			<?php } else { ?>
				<!-- MultiFile script -->
				<script type="text/javascript">
					/* <![CDATA[ */
					jQuery(document).ready(function(){
						jQuery('#imagefiles').MultiFile({
							STRING: {
								remove: '<?php _e('remove', 'flash-album-gallery'); ?>'
							}
						});

						if(jQuery("#galleryselect").val() == 0){
							jQuery("#choosegalfirst").animate({opacity: "0.5"}, 600);
							jQuery("#choosegalfirst .disabledbut").show();
						}
						jQuery("#choosegalfirst .disabledbut").click(function(){
							alert("Choose gallery, please.")
						});
						jQuery("#galleryselect").change(function(){
							if(jQuery(this).val() == 0){
								jQuery("#choosegalfirst .disabledbut").show();
								jQuery("#choosegalfirst").animate({opacity: "0.5"}, 600);
							} else{
								jQuery("#choosegalfirst .disabledbut").hide();
								jQuery("#choosegalfirst").animate({opacity: "1"}, 600);
							}
						});
					});
					/* ]]> */
				</script>

			<?php } ?>
		</div>
		<?php if ( ! IS_WPMU || current_user_can( 'FlAG Import folder' ) ) { ?>
			<!-- import folder -->
			<div id="importfolder" class="cptab">
				<h2><?php _e( 'Import image folder', 'flash-album-gallery' ); ?></h2>

				<form name="importfolder" id="importfolder_form" method="POST" action="<?php echo $filepath; ?>" accept-charset="utf-8">
					<?php wp_nonce_field( 'flag_addgallery' ); ?>
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e( 'Import from Server path:', 'flash-album-gallery' ); ?></th>
							<td>
								<input type="text" size="35" id="galleryfolder" name="galleryfolder" value="<?php echo $defaultpath; ?>"/><span class="browsefiles button" style="display:none"><?php _e( 'Browse...', "flash-album-gallery" ); ?></span>

								<div id="file_browser"></div>
								<div><?php echo $maxsize; ?>
									<?php if ( FLAG_SAFE_MODE ) { ?>
										<br/><?php _e( ' Please note : For safe-mode = ON you need to add the subfolder thumbs manually', 'flash-album-gallery' ); ?><?php }; ?>
								</div>
							</td>
						</tr>
					</table>
					<div class="submit">
						<input class="button-primary" type="submit" name="importfolder" value="<?php _e( 'Import folder', 'flash-album-gallery' ); ?>"/>
					</div>
				</form>
			</div>
		<?php } ?>

		<script type="text/javascript">
			var cptabs = new ddtabcontent("tabs");
			cptabs.setpersist(true);
			cptabs.setselectedClassTarget("linkparent");
			cptabs.init();
		</script>
	</div>
<?php
}
