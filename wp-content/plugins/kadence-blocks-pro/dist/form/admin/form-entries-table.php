<?php
/**
 * Entries Schema Class.
 *
 * @package     Kadence Blocks Pro
 */

namespace KBP\Tables;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use KBP\BerlinDB\Table;

/**
 * Entries Schema Class.
 */
final class Entries extends Table {

	/**
	 * @var string Table name
	 */
	protected $name = 'form_entry';

	/**
	 * @var string Database version
	 */
	protected $version = 2019101613;

	// protected $upgrades = array(
	// 	'2019101613' => 2019101613
	// );
	/**
	 * Customers constructor.
	 *
	 * @access public
	 * @since  1.2.8
	 * @return void
	 */
	public function __construct() {

		parent::__construct();

	}

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since  1.2.8
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(255) DEFAULT NULL,
			form_id varchar(55) DEFAULT NULL,
			post_id bigint(20) unsigned NOT NULL DEFAULT '0',
			user_id bigint(20) unsigned NOT NULL DEFAULT '0',
			date_created datetime NOT NULL,
			user_ip int(11) unsigned NOT NULL DEFAULT '0',
			user_device varchar(55) DEFAULT NULL,
			referer varchar(255) DEFAULT NULL,
			status varchar(10) DEFAULT 'publish',
			uuid varchar(100) NOT NULL default '',
			PRIMARY KEY (id),
			KEY post_id (post_id)";
	}
}
