<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])){
    die('You are not allowed to call this page directly.');
}

function flag_admin_options(){

    global $flag;

    // same as $_SERVER['REQUEST_URI'], but should work under IIS 6.0
    $filepath = admin_url() . 'admin.php?page=' . urlencode($_GET['page']);

    if(isset($_POST['updateoption'])){
        check_admin_referer('flag_settings');
        // get the hidden option fields, taken from WP core
        $options = array();
        if($_POST['page_options']){
            $options = explode(',', stripslashes($_POST['page_options']));
        }
        if( !empty($options)){
            foreach($options as $option){
                $option = trim($option);
                if(isset($_POST[ $option ])){
                    $value = trim($_POST[ $option ]);
                } else{
                    $value = false;
                }
                $flag->options[ $option ] = $value;
            }
            if(isset($_POST['galleryPath'])){
                // the path should always end with a slash
                $flag->options['galleryPath'] = trailingslashit($flag->options['galleryPath']);
            }
            // the custom sortorder must be ascending
            //$flag->options['galSortDir'] = ($flag->options['galSort'] == 'sortorder') ? 'ASC' : $flag->options['galSortDir'];
        }
        // Save options
        update_option('flag_options', $flag->options);

        flagGallery::show_message(__('Update Successfully', 'flash-album-gallery'));
    }

    if(isset($_POST['membership'])){

	    $options = explode(',', stripslashes($_POST['page_options']));
	    foreach($options as $option){
		    $option                   = trim($option);
		    $value                    = trim($_POST[ $option ]);
		    $flag->options[ $option ] = $value;
	    }

        if( !empty($_POST['license_key']) && function_exists('curl_init')){
            $ch = curl_init('https://mypgc.co/app/account_st.php');
            curl_setopt($ch, CURLOPT_REFERER, home_url());
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array('check_status' => $_POST['license_key']));
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $status = curl_exec($ch);
            curl_close($ch);
	        if ( $status === '0' ) {
		        $flag->options['license_key'] = '';
		        if ( empty( $_POST['license_name'] ) ) {
			        $flag->options['license_name'] = '';
		        }
		        flagGallery::show_message( __( 'Your license key was deactivated', 'flash-album-gallery' ) );
	        } elseif ( $status === '' ) {
		        $flag->options['license_key'] = '';
		        if ( empty( $_POST['license_name'] ) ) {
			        $flag->options['license_name'] = '';
		        }
		        flagGallery::show_message( __( 'Bad Licence Key', 'flash-album-gallery' ) );
	        } elseif ( ! empty( $status ) ) {
		        $flag->options['license_name'] = $status;
	        }
        }

        if( empty($_POST['license_key']) ) {
        	$flag->options['license_key'] = '';
        	$flag->options['license_name'] = '';
        }

        // Save options
        update_option('flag_options', $flag->options);
    }

    if(isset($_POST['update_cap'])){

        check_admin_referer('flag_addroles');

        // now set or remove the capability
        flag_set_capability($_POST['general'], "FlAG overview");
        flag_set_capability($_POST['tinymce'], "FlAG Use TinyMCE");
        flag_set_capability($_POST['add_gallery'], "FlAG Upload images");
        flag_set_capability($_POST['import_gallery'], "FlAG Import folder");
        flag_set_capability($_POST['manage_gallery'], "FlAG Manage gallery");
        flag_set_capability($_POST['manage_others'], "FlAG Manage others gallery");
        flag_set_capability($_POST['change_skin'], "FlAG Change skin");
        flag_set_capability($_POST['add_skins'], "FlAG Add skins");
        flag_set_capability($_POST['delete_skins'], "FlAG Delete skins");
        flag_set_capability($_POST['change_options'], "FlAG Change options");
        flag_set_capability($_POST['manage_music'], "FlAG Manage music");
        flag_set_capability($_POST['manage_video'], "FlAG Manage video");
        flag_set_capability($_POST['manage_banners'], "FlAG Manage banners");
        flag_set_capability($_POST['flagframe_page'], "FlAG iFrame page");

        flagGallery::show_message(__('Updated capabilities', "flash-album-gallery"));
    }

    // message windows
    if( !empty($messagetext)){
        echo '<!-- Last Action --><div id="message" class="updated fade"><p>' . $messagetext . '</p></div>';
    }

    $flag_options = get_option('flag_options');
    ?>

    <div id="slider" class="flag-wrap">

        <ul id="tabs" class="tabs">
            <li class="selected">
                <a href="#" rel="imageoptions"><?php _e('Gallery Options', 'flash-album-gallery'); ?></a></li>
            <li><a href="#" rel="grandpages"><?php _e('GRAND Pages', 'flash-album-gallery'); ?></a></li>
            <?php if(current_user_can('administrator')){ ?>
                <li><a href="#" rel="rControl"><?php _e('License Key & Plugin Options', 'flash-album-gallery'); ?></a>
                </li>
                <?php if(flagGallery::flag_wpmu_enable_function('wpmuRoles')) : ?>
                    <li><a href="#" rel="roles"><?php _e('Roles', 'flash-album-gallery'); ?></a></li>
                <?php endif; ?>
            <?php } ?>
        </ul>
        <!-- Image Gallery Options -->
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery('.flag_colors .colorPick').each(function(){
                    var inpID = jQuery(this).attr('name');
                    jQuery('#cp_' + inpID).farbtastic('#' + inpID);
                    jQuery('#' + inpID).focus(function(){
                        jQuery('#cp_' + inpID).show();
                    });
                    jQuery('#' + inpID).blur(function(){
                        jQuery('#cp_' + inpID).hide();
                    });
                });
            });
        </script>
        <div id="imageoptions" class="cptab">
            <form name="generaloptions" method="post">
                <?php wp_nonce_field('flag_settings'); ?>
                <input type="hidden" name="page_options" value="galleryPath,flashWidth,flashHeight,deleteImg,deepLinks,useMediaRSS,imgQuality,albSort,albSortDir,albPerPage,galSort,galSortDir,disableViews"/>
                <h2><?php _e('Image Gallery Options', 'flash-album-gallery'); ?></h2>
                <h3><?php _e('General Options', 'flash-album-gallery'); ?></h3>
                <table class="form-table flag-options" style="width: auto; white-space: nowrap;">
                    <tr valign="top">
                        <th align="left" width="200"><?php _e('Gallery path', 'flash-album-gallery'); ?></th>
                        <td>
                            <input readonly="readonly" type="text" size="35" name="galleryPath" value="<?php echo $flag_options['galleryPath']; ?>"/>
                            <span class="setting-description"><?php _e('This is the default path for all galleries', 'flash-album-gallery'); ?></span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th align="left"><?php _e('Delete image files', 'flash-album-gallery'); ?></th>
                        <td><input <?php if(IS_WPMU){
                                echo 'readonly = "readonly"';
                            } ?> type="checkbox" name="deleteImg" value="1" <?php checked('1', $flag_options['deleteImg']); ?> />
                            <?php _e('Delete files, when removing a gallery in the database', 'flash-album-gallery'); ?>
                        </td>
                    </tr>
                    <tr>
                        <th align="left"><?php _e('Activate Media RSS feed', 'flash-album-gallery'); ?></th>
                        <td>
                            <input type="checkbox" name="useMediaRSS" value="1" <?php checked('1', $flag_options['useMediaRSS']); ?> />
                            <span class="setting-description"><?php _e('A RSS feed will be added to you blog header.', 'flash-album-gallery'); ?></span>
                        </td>
                    </tr>
                </table>

                <h3><?php _e('Image settings', 'flash-album-gallery'); ?></h3>
                <table class="form-table flag-options" style="width: auto; white-space: nowrap;">
                    <tr valign="top">
                        <th align="left"><?php _e('Image quality', 'flash-album-gallery'); ?></th>
                        <td>
                            <input type="text" size="3" maxlength="3" name="imgQuality" value="<?php echo $flag_options['imgQuality']; ?>"/>
                            %
                            &nbsp;&nbsp;<span class="setting-description"><?php _e('Default: 85%', 'flash-album-gallery'); ?></span>
                        </td>
                    </tr>
                </table>

                <h3><?php _e('Sort options', 'flash-album-gallery'); ?></h3>
                <table class="form-table flag-options" style="width: auto; white-space: nowrap;">
                    <tr>
                        <th valign="top" width="200"><?php _e('Sort galleries', 'flash-album-gallery'); ?>:</th>
                        <td valign="top">
                            <label><input name="albSort" type="radio" value="gid" <?php checked('gid', $flag_options['albSort']); ?> /> <?php _e('Gallery ID', 'flash-album-gallery'); ?>
                            </label><br/>
                            <label><input name="albSort" type="radio" value="title" <?php checked('title', $flag_options['albSort']); ?> /> <?php _e('Title', 'flash-album-gallery'); ?>
                            </label><br/>
                        </td>
                        <td valign="top">
                            <label><input name="albSortDir" type="radio" value="ASC" <?php checked('ASC', $flag_options['albSortDir']); ?> /> <?php _e('Ascending', 'flash-album-gallery'); ?>
                            </label><br/>
                            <label><input name="albSortDir" type="radio" value="DESC" <?php checked('DESC', $flag_options['albSortDir']); ?> /> <?php _e('Descending', 'flash-album-gallery'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #000000;">
                        <th valign="top" width="200"><?php _e('Galleries per page: <br><small>on Manage Galleries page</small>', 'flash-album-gallery'); ?></th>
                        <td valign="top">
                            <input name="albPerPage" type="text" value="<?php echo $flag_options['albPerPage']; ?>"/>
                        </td>
                        <td valign="top"></td>
                    </tr>
                    <tr>
                        <th valign="top" width="200"><?php _e('Sort images', 'flash-album-gallery'); ?>:</th>
                        <td valign="top">
                            <label><input name="galSort" type="radio" value="sortorder" <?php checked('sortorder', $flag_options['galSort']); ?> /> <?php _e('Custom order', 'flash-album-gallery'); ?>
                            </label><br/>
                            <label><input name="galSort" type="radio" value="pid" <?php checked('pid', $flag_options['galSort']); ?> /> <?php _e('Image ID', 'flash-album-gallery'); ?>
                            </label><br/>
                            <label><input name="galSort" type="radio" value="filename" <?php checked('filename', $flag_options['galSort']); ?> /> <?php _e('File name', 'flash-album-gallery'); ?>
                            </label><br/>
                            <label><input name="galSort" type="radio" value="alttext" <?php checked('alttext', $flag_options['galSort']); ?> /> <?php _e('Alt / Title text', 'flash-album-gallery'); ?>
                            </label><br/>
                            <label><input name="galSort" type="radio" value="imagedate" <?php checked('imagedate', $flag_options['galSort']); ?> /> <?php _e('Date / Time', 'flash-album-gallery'); ?>
                            </label><br/>
                            <label><input name="galSort" type="radio" value="hitcounter" <?php checked('hitcounter', $flag_options['galSort']); ?> /> <?php _e('Image views', 'flash-album-gallery'); ?>
                            </label><br/>
                            <label><input name="galSort" type="radio" value="total_votes" <?php checked('total_votes', $flag_options['galSort']); ?> /> <?php _e('Image likes', 'flash-album-gallery'); ?>
                            </label><br/>
                            <label><input name="galSort" type="radio" value="rand()" <?php checked('rand()', $flag_options['galSort']); ?> /> <?php _e('Randomly', 'flash-album-gallery'); ?>
                            </label>
                        </td>
                        <td valign="top">
                            <label><input name="galSortDir" type="radio" value="ASC" <?php checked('ASC', $flag_options['galSortDir']); ?> /> <?php _e('Ascending', 'flash-album-gallery'); ?>
                            </label><br/>
                            <label><input name="galSortDir" type="radio" value="DESC" <?php checked('DESC', $flag_options['galSortDir']); ?> /> <?php _e('Descending', 'flash-album-gallery'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                <div class="submit">
                    <input class="button-primary" type="submit" name="updateoption" value="<?php _e('Save Changes', 'flash-album-gallery'); ?>"/>
                </div>
            </form>
        </div>

        <div id="grandpages" class="cptab">
            <form name="grandpages" method="post">
                <?php wp_nonce_field('flag_settings'); ?>
                <input type="hidden" name="page_options" value="gp_jscode"/>
                <h2><?php _e('GRAND Pages settings', 'flash-album-gallery'); ?></h2>
                <h3><?php _e('Google Analytics Tracking Code', 'flash-album-gallery'); ?></h3>
                <textarea name="gp_jscode" rows="5" cols="50"><?php if(isset($flag_options['gp_jscode'])){
                        echo stripslashes($flag_options['gp_jscode']);
                    } ?></textarea>
                <p><?php _e('Enter your Google analytics tracking Code here. It will automatically be added to GRAND Pages so google can track your visitors behavior.', 'flash-album-gallery'); ?></p>
                <div class="submit">
                    <input class="button-primary" type="submit" name="updateoption" value="<?php _e('Save Changes', 'flash-album-gallery'); ?>"/>
                </div>
            </form>
        </div>

        <?php if(current_user_can('administrator')){ ?>
            <div id="rControl" class="cptab">
                <form name="rControl" method="post" style="float: left;width: 50%;">
                    <?php wp_nonce_field('flag_settings'); ?>
                    <input type="hidden" name="page_options" value="license_key, hide_woow, show_music_box, show_video_box, show_banner_box"/>
                    <h2><?php _e('License Key & Plugin Options', 'flash-album-gallery'); ?></h2>
                    <input type="hidden" name="access_url" value="<?php echo plugins_url() . '/' . FLAGFOLDER . '/lib/app.php'; ?>"/>
                    <table class="form-table flag-options" style="">
                        <tr>
                            <th valign="top" width="200">
                                <a href="http://mypgc.co/membership/" target="_blank"><?php _e('License Key', 'flash-album-gallery') ?></a>:
                            </th>
                            <td valign="top">
                                <input type="text" size="40" id="license_key" name="license_key" value="<?php echo $flag_options['license_key'] ?>"/>
	                            <?php if ( $flag_options['license_name'] ) { ?>
	                            <p style="font-weight: bold;"><?php printf( __( 'You have <span style="color:red">%s</span> license' ), $flag_options['license_name'] ); ?></p>
	                            <?php } ?>
                                <?php
                                if( !$flag_options['license_name']){
                                    ?>
                                    <p>
                                        <a href="http://bit.ly/2jPNRB0" class="button button-primary button-red" target="_blank"><?php _e('Get Premium', 'flash-album-gallery') ?></a>
                                    </p>
                                    <?php
                                } else{
                                    if('MINIPack' === $flag_options['license_name']){
                                        ?>
                                        <p>
                                            <a href="http://bit.ly/2jPNRB0" class="button button-primary button-red" target="_blank"><?php _e('Upgrade License to GRANDPack or GRANDPack+', 'flash-album-gallery') ?></a>
                                        </p>
                                        <?php
                                    } elseif('GRANDPack' === $flag_options['license_name']){
                                        ?>
                                        <p>
                                            <a href="http://bit.ly/2jPNRB0" class="button button-primary button-red" target="_blank"><?php _e('Upgrade License to GRANDPack+', 'flash-album-gallery') ?></a>
                                        </p>
                                        <?php
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th valign="top" width="200"><?php _e('Disable ads', 'flash-album-gallery'); ?>:</th>
                            <td valign="top">
	                            <input type="hidden" name="hide_woow" value="0"/>
                                <input type="checkbox" name="hide_woow" value="1" <?php checked( (int) $flag_options['hide_woow'], 1); ?> />
                            </td>
                        </tr>
                        <tr>
	                        <th valign="top" colspan="2"><h3><?php _e('Legacy settings', 'flash-album-gallery'); ?>:</h3></th>
                        </tr>
	                    <tr>
		                    <th valign="top" width="200"><?php _e('Show Music Box', 'flash-album-gallery'); ?>:</th>
		                    <td valign="top">
			                    <input type="hidden" name="show_music_box" value="0"/>
			                    <input type="checkbox" name="show_music_box" value="1" <?php checked( (int) $flag_options['show_music_box'], 1); ?> />
		                    </td>
	                    </tr>
	                    <tr>
		                    <th valign="top" width="200"><?php _e('Show Video Box', 'flash-album-gallery'); ?>:</th>
		                    <td valign="top">
			                    <input type="hidden" name="show_video_box" value="0"/>
			                    <input type="checkbox" name="show_video_box" value="1" <?php checked( (int) $flag_options['show_video_box'], 1); ?> />
		                    </td>
	                    </tr>
	                    <tr>
		                    <th valign="top" width="200"><?php _e('Show Banner Box', 'flash-album-gallery'); ?>:</th>
		                    <td valign="top">
			                    <input type="hidden" name="show_banner_box" value="0"/>
			                    <input type="checkbox" name="show_banner_box" value="1" <?php checked( (int) $flag_options['show_banner_box'], 1); ?> />
		                    </td>
	                    </tr>
                    </table>
                    <div class="submit">
                        <input class="button-primary" type="submit" name="membership" value="<?php _e('Update Settings', 'flash-album-gallery'); ?>"/>
                    </div>
                </form>

                <div style="clear: both;"></div>
            </div>

            <?php if(flagGallery::flag_wpmu_enable_function('wpmuRoles')) : ?>
                <div id="roles" class="cptab">
                    <form method="POST" name="addroles" id="addroles" accept-charset="utf-8">
                        <?php wp_nonce_field('flag_addroles'); ?>
                        <h2><?php _e('Roles / capabilities', 'flash-album-gallery'); ?></h2>
                        <div>&nbsp;</div>
                        <p><?php _e('Select the lowest role which should be able to access the follow capabilities. FlaGallery supports the standard roles from WordPress.', 'flash-album-gallery'); ?></p>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row" style="white-space: nowrap"><?php _e('Main FlaGallery overview', 'flash-album-gallery'); ?>
                                    :
                                </th>
                                <td>
                                    <label for="general"><select style="width: 150px;" name="general" id="general"><?php wp_dropdown_roles(flag_get_role('FlAG overview')); ?></select></label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" style="white-space: nowrap"><?php _e('Add GRAND Pages / View Flagallery Button on Edit Post', 'flash-album-gallery'); ?>
                                    :
                                </th>
                                <td>
                                    <label for="tinymce"><select style="width: 150px;" name="tinymce" id="tinymce"><?php wp_dropdown_roles(flag_get_role('FlAG Use TinyMCE')); ?></select></label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" style="white-space: nowrap"><?php _e('Add gallery / Upload images', 'flash-album-gallery'); ?>
                                    :
                                </th>
                                <td>
                                    <label for="add_gallery"><select style="width: 150px;" name="add_gallery" id="add_gallery"><?php wp_dropdown_roles(flag_get_role('FlAG Upload images')); ?></select></label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" style="white-space: nowrap"><?php _e('Import images folder', 'flash-album-gallery'); ?>
                                    :
                                </th>
                                <td>
                                    <label for="add_gallery"><select style="width: 150px;" name="import_gallery" id="import_gallery"><?php wp_dropdown_roles(flag_get_role('FlAG Import folder')); ?></select></label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" style="white-space: nowrap"><?php _e('Manage gallery', 'flash-album-gallery'); ?>
                                    :
                                </th>
                                <td>
                                    <label for="manage_gallery"><select style="width: 150px;" name="manage_gallery" id="manage_gallery"><?php wp_dropdown_roles(flag_get_role('FlAG Manage gallery')); ?></select></label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" style="white-space: nowrap"><?php _e('Manage others galleries and Albums', 'flash-album-gallery'); ?>
                                    :
                                </th>
                                <td>
                                    <label for="manage_others"><select style="width: 150px;" name="manage_others" id="manage_others"><?php wp_dropdown_roles(flag_get_role('FlAG Manage others gallery')); ?></select></label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" style="white-space: nowrap"><?php _e('Manage music', 'flash-album-gallery'); ?>
                                    :
                                </th>
                                <td>
                                    <label for="manage_music"><select style="width: 150px;" name="manage_music" id="manage_music"><?php wp_dropdown_roles(flag_get_role('FlAG Manage music')); ?></select></label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" style="white-space: nowrap"><?php _e('Manage video', 'flash-album-gallery'); ?>
                                    :
                                </th>
                                <td>
                                    <label for="manage_video"><select style="width: 150px;" name="manage_video" id="manage_video"><?php wp_dropdown_roles(flag_get_role('FlAG Manage video')); ?></select></label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" style="white-space: nowrap"><?php _e('Manage banners', 'flash-album-gallery'); ?>
                                    :
                                </th>
                                <td>
                                    <label for="manage_banners"><select style="width: 150px;" name="manage_banners" id="manage_banners"><?php wp_dropdown_roles(flag_get_role('FlAG Manage banners')); ?></select></label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" style="white-space: nowrap"><?php _e('Change skin', 'flash-album-gallery'); ?>
                                    :
                                </th>
                                <td>
                                    <label for="change_skin"><select style="width: 150px;" name="change_skin" id="change_skin"><?php wp_dropdown_roles(flag_get_role('FlAG Change skin')); ?></select></label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" style="white-space: nowrap"><?php _e('Add skins', 'flash-album-gallery'); ?>
                                    :
                                </th>
                                <td>
                                    <label for="add_skins"><select style="width: 150px;" name="add_skins" id="add_skins"><?php wp_dropdown_roles(flag_get_role('FlAG Add skins')); ?></select></label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" style="white-space: nowrap"><?php _e('Delete skins', 'flash-album-gallery'); ?>
                                    :
                                </th>
                                <td>
                                    <label for="delete_skins"><select style="width: 150px;" name="delete_skins" id="delete_skins"><?php wp_dropdown_roles(flag_get_role('FlAG Delete skins')); ?></select></label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" style="white-space: nowrap"><?php _e('Change options', 'flash-album-gallery'); ?>
                                    :
                                </th>
                                <td>
                                    <label for="change_options"><select style="width: 150px;" name="change_options" id="change_options"><?php wp_dropdown_roles(flag_get_role('FlAG Change options')); ?></select></label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" style="white-space: nowrap"><?php _e('iFrame page', 'flash-album-gallery'); ?>
                                    :
                                </th>
                                <td>
                                    <label for="flagframe_page"><select style="width: 150px;" name="flagframe_page" id="flagframe_page"><?php wp_dropdown_roles(flag_get_role('FlAG iFrame page')); ?></select></label>
                                </td>
                            </tr>
                        </table>
                        <div class="submit">
                            <input type="submit" class="button-primary" name="update_cap" value="<?php _e('Update capabilities', 'flash-album-gallery'); ?>"/>
                        </div>
                    </form>
                </div>
            <?php endif;
        } ?>
    </div>
    <script type="text/javascript">
        var cptabs = new ddtabcontent("tabs");
        cptabs.setpersist(true);
        cptabs.setselectedClassTarget("linkparent");
        cptabs.init();
    </script>

    <?php
}

function flag_get_sorted_roles(){
    // This function returns all roles, sorted by user level (lowest to highest)
    global $wp_roles;
    $roles  = $wp_roles->role_objects;
    $sorted = array();

    if(class_exists('RoleManager')){
        foreach($roles as $role_key => $role_name){
            $role = get_role($role_key);
            if(empty($role)){
                continue;
            }
            $role_user_level            = array_reduce(array_keys($role->capabilities), array(
                'WP_User',
                'level_reduction',
            ), 0
            );
            $sorted[ $role_user_level ] = $role;
        }
        $sorted = array_values($sorted);
    } else{
        $role_order = array("subscriber", "contributor", "author", "editor", "administrator");
        foreach($role_order as $role_key){
            $sorted[ $role_key ] = get_role($role_key);
        }
    }

    return $sorted;
}

function flag_get_role($capability){
    // This function return the lowest roles which has the capabilities
    $check_order = flag_get_sorted_roles();

    $args = array_slice(func_get_args(), 1);
    $args = array_merge(array($capability), $args);

    foreach($check_order as $check_role){
        if(empty($check_role)){
            return false;
        }

        if(call_user_func_array(array(&$check_role, 'has_cap'), $args)){
            return $check_role->name;
        }
    }

    return false;
}

function flag_set_capability($lowest_role, $capability){
    // This function set or remove the $capability
    $check_order = flag_get_sorted_roles();

    $add_capability = false;

    foreach($check_order as $the_role){
        $role = $the_role->name;

        if($lowest_role == $role){
            $add_capability = true;
        }

        // If you rename the roles, the please use the role manager plugin

        if(empty($the_role)){
            continue;
        }

        $add_capability? $the_role->add_cap($capability) : $the_role->remove_cap($capability);
    }

}
