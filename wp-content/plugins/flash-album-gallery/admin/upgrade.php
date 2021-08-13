<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

/**
 * flag_upgrade() - update routine for older version
 * 
 * @return mixed Success message
 */
function flag_upgrade() {
	
	global $wpdb, $user_ID;

	// get the current user ID
    wp_get_current_user();

	// Be sure that the tables exist
	if($wpdb->get_var("show tables like '$wpdb->flagpictures'") == $wpdb->prefix . 'flag_pictures') {

		$wpdb->show_errors();

		$installed_ver = get_option( "flag_db_version" );
		
		// v0.31 -> v0.32
		if (version_compare($installed_ver, '0.32', '<')) {
			// add description and previewpic for the ablum itself
			flag_add_sql_column( $wpdb->flagpictures, 'copyright', "TEXT;");
			flag_add_sql_column( $wpdb->flagpictures, 'credit', "TEXT;");
			flag_add_sql_column( $wpdb->flagpictures, 'country', "TINYTEXT;");
			flag_add_sql_column( $wpdb->flagpictures, 'state', "TINYTEXT;");
			flag_add_sql_column( $wpdb->flagpictures, 'city', "TINYTEXT;");
			flag_add_sql_column( $wpdb->flagpictures, 'location', "TEXT;");
			flag_add_sql_column( $wpdb->flagpictures, 'used_ips', "LONGTEXT;");
			flag_add_sql_column( $wpdb->flagpictures, 'total_votes', "INT(11) UNSIGNED DEFAULT '0';");
			flag_add_sql_column( $wpdb->flagpictures, 'total_value', "INT(11) UNSIGNED DEFAULT '0';");
			flag_add_sql_column( $wpdb->flagpictures, 'hitcounter', "INT(11) UNSIGNED DEFAULT '0';");
			flag_add_sql_column( $wpdb->flagpictures, 'commentson', "INT(1) UNSIGNED NOT NULL DEFAULT '1';");
			flag_add_sql_column( $wpdb->flagpictures, 'exclude', "TINYINT NULL DEFAULT '0';");
		
			$flag_options = get_option('flag_options');	
			$flag_options['skinsDirABS'] = FLAG_ABSPATH . 'skins/'; 
			$flag_options['skinsDirURL'] = FLAG_URLPATH . 'skins/'; 
			update_option('flag_options', $flag_options);
		}		
		// v0.32 -> v0.40
		if (version_compare($installed_ver, '0.40', '<')) {
			flag_add_sql_column( $wpdb->flagpictures, 'meta_data', "LONGTEXT AFTER used_ips;");
		}		

		// On some reason the import / date sometimes failed, due to the memory limit
		if (version_compare($installed_ver, '0.32', '<')) {
			echo __('Import date and time information...', 'flash-album-gallery');
			flag_import_date_time();
			echo __('finished', 'flash-album-gallery') . "<br />\n";
		}		

		// v2.56 -> v2.70
		if (version_compare($installed_ver, '2.70', '<')) {
			flag_add_sql_column( $wpdb->flagpictures, 'link', "TEXT NULL AFTER alttext;");
		}

		// v2.72 -> v2.75
		if (version_compare($installed_ver, '2.75', '<')) {
			flag_add_sql_column( $wpdb->flagpictures, 'modified', "TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP AFTER imagedate;");
			flag_add_sql_column( $wpdb->flaggallery, 'status', "TINYINT NULL DEFAULT '0' AFTER author;");
		}

		if (version_compare($installed_ver, '5.0', '<')) {
            $flag_options = get_option('flag_options');
            $flag_options['license_name'] = '';
            $flag_options['flashSkin'] = 'amron';
            $flag_options['imgWidth']   = 2200;
            $flag_options['imgHeight']  = 2200;
            $flag_options['imgQuality'] = 87;
            $flag_options['thumbWidth'] = 400;
            $flag_options['thumbHeight'] = 400;
            $flag_options['thumbQuality'] = 100;

            if(!empty($flag_options['license_key']) && function_exists('curl_init')){
                $ch = curl_init('https://mypgc.co/app/account_st.php');
                curl_setopt($ch, CURLOPT_REFERER, home_url());
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, array('check_status' => $flag_options['license_key']));
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $status = curl_exec($ch);
                curl_close($ch);
                if($status === '0' || $status === ''){
                    $flag_options['license_key']  = '';
                    $flag_options['license_name'] = '';
                } else{
                    $flag_options['license_name'] = $status;
                }
            }

            update_option('flag_options', $flag_options);
		}

		// update now the database
		update_option( "flag_db_version", FLAG_DBVERSION );
		$wpdb->hide_errors();

	}
	return false;
}


/**
 * flag_import_date_time() - Read the timestamp from exif and insert it into the database
 * 
 * @return void
 */
function flag_import_date_time() {
	global $wpdb;
	
	$imagelist = $wpdb->get_results("SELECT t.*, tt.* FROM $wpdb->flaggallery AS t INNER JOIN $wpdb->flagpictures AS tt ON t.gid = tt.galleryid ORDER BY tt.pid ASC");
	if ( is_array($imagelist) ) {
		foreach ($imagelist as $image) {
			$picture = new flagImage($image);
			$meta = new flagMeta($picture->imagePath, true);
			$date = $meta->get_date_time();
			$wpdb->query($wpdb->prepare("UPDATE $wpdb->flagpictures SET imagedate = '%s' WHERE pid = '%d'", $date, $picture->pid));
		}		
	}	
}

/**
 * Adding a new column if needed
 * Example : flag_add_sql_column( $wpdb->flagpictures, 'imagedate', "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER alttext");
 * 
 * @param string $table_name Database table name.
 * @param string $column_name Database column name to create.
 * @param string $create_ddl SQL statement to create column
 * @return bool True, when done with execution.
 */
function flag_add_sql_column($table_name, $column_name, $create_ddl) {
	global $wpdb;
	
	foreach ($wpdb->get_col("SHOW COLUMNS FROM $table_name") as $column ) {
		if ($column == $column_name)
			return true;
	}
	
	//didn't find it try to create it.
	$wpdb->query("ALTER TABLE $table_name ADD COLUMN $column_name " . $create_ddl);
	
	// we cannot directly tell that whether this succeeded!
	foreach ($wpdb->get_col("SHOW COLUMNS FROM $table_name") as $column ) {
		if ($column == $column_name)
			return true;
	}
	
	echo("Could not add column $column_name in table $table_name<br />\n");
	return false;
}

