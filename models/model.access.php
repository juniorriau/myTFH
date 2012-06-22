<?php

/* define the namespace */
//namespace models\access;

/* prevent direct access */
if (!defined('__SITE')) exit('No direct calls please...');

/**
 * Manage access lists (allow/deny)
 *
 *
 * LICENSE: This source file is subject to version 3.01 of the GPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/gpl.html.  If you did not receive a copy of
 * the GPL License and are unable to obtain it through the web, please
 *
 * @category   access
 * @package    phpMyFramework
 * @author     Original Author <jason.gerfen@gmail.com>
 * @copyright  2010-2012 Jason Gerfen
 * @license    http://www.gnu.org/licenses/gpl.html  GPL License 3
 * @version    0.1
 */

/**
 *! @class access
 *  @abstract Handles allow/deny list
 */
class access {

	/**
	 * @var registry object
	 * @abstract Global class handler
	 */
	private $registry;

	/**
	 * @var instance object
	 * @abstract This class handler
	 */
	protected static $instance;

	/**
	 *! @function init
	 *  @abstract Creates singleton for allow/deny class
	 *  @param $args array Array of registry items
	 */
	public static function init($args)
	{
		if (self::$instance == NULL)
			self::$instance = new self($args);
		return self::$instance;
	}

	/**
	 *! @function __construct
	 *  @abstract Class initialization and ip to access/deny processing
	 *  @param $args array Array of registry items
	 */
	public function __construct($registry)
	{
		$this->registry = $registry;
	}

	/**
	 *! @function _do
	 *  @abstract Perform access list to visitor allow/deny functions
	 */
	public function _do()
	{
		return $this->__compare($this->__visitor(), $this->_get());
	}

	/**
	 *! @function _get
	 *  @abstract Retrieves currently configured list of allowed/denied hosts
	 */
	private function _get()
	{
		$list = 0;
		try{
			$list = $this->registry->libs->__flatten($this->registry->db->query($this->__query(), true));
		} catch(PDOException $e){
			// error handling
		}
		return $list;
	}

	/**
	 *! @function __visitor
	 *  @abstract Retrieves and assigns the visiting address
	 */
	private function __visitor()
	{
		return $this->registry->libs->_getRealIPv4();
	}

	/**
	 *! @function __query
	 *  @abstract Generates SQL query to access list of allowed/denied hosts
	 */
	private function __query()
	{
		return sprintf('CALL Configuration_access_get("%s")',
						$this->registry->db->sanitize(hashes::init($this->registry)->_do($this->registry->opts['dbKey'])));
	}

	/**
	 *! @function __compare
	 *  @abstract Performs comparisions on hosts, ips and additional network
	 *            declarations
	 */
	private function __compare($ip, $denyList)
	{
		$filter = (class_exists('ipFilter')) ? new ipFilter($denyList) : false;
		return $filter->check($ip, $denyList);
	}

	/**
	 *! @function _helper
	 *  @abstract Helps convert array of allowed IP's for ipFilter class
	 */
	private function _helper($array)
	{
		
	}

	public function __destruct()
	{
		return;
	}
}

/**
 *! @class access
 *  @abstract Handles management of allow/deny list
 */
class manageAccess
{
	/**
	 * @var registry object
	 * @abstract Global class handler
	 */
	private $registry;

	/**
	 * @var instance object
	 * @abstract This class handler
	 */
	protected static $instance;

	/**
	 *! @function init
	 *  @abstract Creates singleton for allow/deny class
	 *  @param $args array Array of registry items
	 */
	public static function init($args)
	{
		if (self::$instance == NULL)
			self::$instance = new self($args);
		return self::$instance;
	}

	/**
	 *! @function __construct
	 *  @abstract Class initialization and ip to access/deny processing
	 *  @param $args array Array of registry items
	 */
	public function __construct($registry)
	{
		$this->registry = $registry;
	}
	
	/**
	 *! @function __do
	 *  @abstract Determines action and acts accordingly
	 */
	public function __do($obj)
	{
		$x = false;

		$d = $this->__decrypt($obj);
		$a = $d['do'];
		unset($d['do']);

		if (!empty($a)) {
			switch($a) {
				case 'add':
					$x = $this->registry->libs->JSONEncode($this->_add($d));
					break;
				case 'edit':
					break;
				case 'del':
					break;
				default:
                    break;
			}
		}
		return $x;
	}

	/**
	 *! @function _get
	 *  @abstract Retrieves currently configured list of allowed/denied hosts
	 */
	public function _get()
	{
		$list = 0;
		try{
			$sql = sprintf('CALL Configuration_access_get_list("%s")',
							$this->registry->db->sanitize(hashes::init($this->registry)->_do($this->registry->opts['dbKey'])));
			$list = $this->registry->db->query($sql, true);
		} catch(PDOException $e){
			// error handling
		}
		return $list;
	}

	/**
	 *! @function _add
	 *  @abstract Determines allow or deny and adds/updates records
	 */
	private function _add($data)
	{
		$r = 0;
		if (!is_array($data)) {
			return $r;
		}

		try{
			$sql = sprintf('CALL Configuration_access_add("%s", "%s", "%s")',	
							$this->registry->db->sanitize($data['name']),
							$this->registry->db->sanitize($data['filter']),
						    $this->registry->db->sanitize(hashes::init($this->registry)->_do($this->registry->opts['dbKey'])));
			$r = $this->registry->db->query($sql);
		} catch(PDOException $e){
			// error handling
		}

		return ($r > 0) ? array('success'=>'Successfully added new ACL') : array('error'=>'An error occured adding new ACL');
	}

	/**
	 *! @function __decrypt
	 *  @abstract Handle decryption of submitted form data
	 */
	private function __decrypt($obj)
	{
		if (count($obj)>0) {
			$x = array();
			foreach($obj as $key => $value) {
				$x[$key] = $this->registry->keyring->ssl->privDenc($value, $_SESSION[$this->registry->libs->_getRealIPv4()]['privateKey'], $_SESSION[$this->registry->libs->_getRealIPv4()]['password']);
			}
		}
		return $x;
	}

}
?>
