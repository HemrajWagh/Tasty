<?php
/**
 * Customer Query Class.
 *
 * @package     RCP
 * @subpackage  Database\Queries
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

namespace KBP\Queries;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use KBP\BerlinDB\Query;

/**
 * Class used for querying customers.
 *
 * @since 3.0
 *
 * @see   \RCP\Database\Queries\Customer::__construct() for accepted arguments.
 */
class Entry extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since  3.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'form_entry';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since  3.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'kbform';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since  3.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = '\\KBP\\Schemas\\Entries';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since  3.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'entry';

	/**
	 * Plural version for a group of items.
	 *
	 * @since  3.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'entries';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since  3.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = '\\KBP_Entry';

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since  3.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'entries';

	/**
	 * Sets up the entry query, based on the query vars passed.
	 *
	 * @param string|array $query Optional. Array or query string of entry query parameters. Default empty.
	 */
	public function __construct( $query = array() ) {
		parent::__construct( $query );
	}

}
