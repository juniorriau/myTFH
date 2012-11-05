<?php

/* define the namespace */
//namespace core\sessions\database;

/* prevent direct access */
if (!defined('__SITE')) exit('No direct calls please...');

/**
 * Handle database session functionality
 *
 *
 * LICENSE: This source file is subject to version 3.01 of the GPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/gpl.html.  If you did not receive a copy of
 * the GPL License and are unable to obtain it through the web, please
 *
 * @category   sessions
 * @package    phpMyFramework
 * @author     Original Author <jason.gerfen@gmail.com>
 * @copyright  2010 Jason Gerfen
 * @license    http://www.gnu.org/licenses/gpl.html  GPL License 3
 * @version    0.1
 */

/**
 *! @class dbSession
 *  @abstract Creates abstract session storage using defined database engine
 */
class dbSession
{

	/**
	 * @var instance object
	 * @abstract This class handler
	 */
	protected static $instance;

	/**
	 * @var database object
	 * @abstract This database handler
	 */
	private $dbconn;

	/**
	 * @var registry object
	 * @abstract This registry handler
	 */
	private $registry;

	/**
	 * @var dbKey string
	 * @abstract Provides transparent AES encryption/decryption of session data
	 */
	public static $dbKey;

	/**
	 *! @function __construct
	 *  @abstract Class initialization. Creates database connection,
	 *            sets global objects and defines default handler for
	 *            session data
	 *  @param $configuration array Array of database sepecific options
	 *  @param $opts array Array of session specific options
	 */
	public function __construct($configuration, $opts)
	{
		if ((class_exists('dbConn'))||(is_object($opts))) {
			$this->dbKey = $configuration['db-key'];
			if (is_object($opts->db)) $this->dbconn = $opts->db;
			$this->options($configuration);
			$this->registry = $opts;
			session_set_save_handler(
					array(&$this, 'open'),
					array(&$this, 'close'),
					array(&$this, 'read'),
					array(&$this, 'write'),
					array(&$this, 'destroy'),
					array(&$this, 'gc'));
			register_shutdown_function('session_write_close');
			if (!isset($_SESSION)) session_start();
		} else {
			exit('Database class handler is missing.');
			unset($instance);
			exit;
		}
	}

	/**
	 *! @function instance
	 *  @abstract Creates singleton object for database session handling
	 *  @param $configuration array Array of database sepecific options
	 *  @param $opts array Array of session specific options
	 */
	public static function instance($configuration, $opts)
	{
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new self($configuration, $opts);
		}
		return self::$instance;
	}

	/**
	 *! @function options
	 *  @abstract Sets default options upon class initialization
	 *  @param $configuration array Array of database sepecific options
	 */
	private function options($configuration)
	{
		ini_set('session.gc_maxlifetime', $configuration['timeout']);
		ini_set('session.name', $configuration['title']);
		ini_set('cache_limiter', $configuration['cache']);
		ini_set('cache_expire', $configuration['timeout']);
		ini_set('use_cookies', $configuration['cookie']);
		if ($configuration['cookie']){
			session_set_cookie_params($configuration['timeout'], $this->_path(), $this->_domain(), $configuration['secure'], $configuration['proto']);
		}
	}

	/**
	 *! @function open
	 *  @abstract Creates database connection handler as global object within class scope
	 *  @param $configuration array Array of database sepecific options
	 */
	private function open($configuration)
	{
		if ((!isset($this->dbconn))||(!is_object($this->dbconn))) {
			$this->dbconn = dbConn::instance($configuration);
			return (is_object($this->dbconn)) ? true : false;
		}
	}

	/**
	 *! @function read
	 *  @abstract Returns current session data
	 *  @param $id strung Current session id
	 */
	public function read($id)
	{
		if (isset($id)){
			try{
				$sql = $query = sprintf('CALL Session_Search("%s", "%s")', $this->dbconn->sanitize($id), $this->dbconn->sanitize($this->dbKey));
				$result = $this->dbconn->query($sql);
			} catch(Exception $e){
				// error handling
			}
			return (count($result)>0) ? $this->sanitizeout($result['session_data']) : '';
		}
		return '';
	}

	/**
	 *! @function write
	 *  @abstract Performs new session data saving
	 *  @param $id string Session ID
	 *  @param $data string Session data
	 */
	public function write($id, $data)
	{
		if ((isset($id))&&(isset($data))){

			$e = $this->read($id);
			if ((count($e)>0) && (!empty($e['session_data']))) {
				$data = $this->sanitizeout($e['session_data']).$data;
			}
			$x = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : $this->_path();

			try{
				$sql = sprintf('CALL Session_Add("%s", "%s", "%d", "%s", "%s", "%s", "%s")',
								$this->dbconn->sanitize($id), $this->sanitizein($data),
								$this->dbconn->sanitize((int)time()),
								$this->dbconn->sanitize(sha1($_SERVER['HTTP_USER_AGENT'])),
								$this->dbconn->sanitize(sha1($this->registry->libs->_getRealIPv4())),
								$this->dbconn->sanitize($x),
								$this->dbconn->sanitize($this->dbKey));
				$r = $this->dbconn->query($sql);
			} catch(Exception $e){
				// error handling
			}
			return ((is_resource($r))&&($this->dbconn->affected($r)>0)) ? true : false;
		}
		return false;
	}

	/**
	 *! @function close
	 *  @abstract Closes the session managment
	 */
	public function close()
	{
		return true;
	}

	/**
	 *! @function destroy
	 *  @abstract Destroys all session data matching session id
	 *  @param $id string Session id
	 */
	private function destroy($id)
	{
		if (isset($id)){
			try{
				$sql = sprintf('CALL Session_destroy("%s")', $this->dbconn->sanitize($id));
				$r = $this->dbconn->query($sql);
			} catch(Exception $e){
				// error handling
			}
			return ((is_resource($r))&&($this->dbconn->affected($this->dbconn)>0)) ? true : false;
		}
		return false;
	}

	/**
	 *! @function sanitizein
	 *  @abstract Assists in serilization and cleaning of session data
	 *  @param $string string The session data to be cleaned
	 */
	private function sanitizein($string)
	{
		if (version_compare(PHP_VERSION, '5.2.11')>=0) {
			return $this->dbconn->sanitize(addslashes(serialize($string)));
		} else {
			return $this->dbconn->sanitize($string);
		}
	}

	/**
	 *! @function sanitizeout
	 *  @abstract Assists in de-serilization and cleaning of session data
	 *  @param $string string The session data to be cleaned
	 */
	private function sanitizeout($string)
	{
		if (version_compare(PHP_VERSION, '5.2.11')>=0) {
			return stripslashes(stripslashes(unserialize($string)));
		} else {
			return stripslashes($string);
		}
	}

	/**
	 *! @function regen
	 *  @abstract Regenerates session id to protect against session hijacking
	 *  @param $flag boolean Used to determine action
	 */
	public function regen($flag=false)
	{
		if ($flag!==false){
			$_SESSION[$this->registry->libs->_getRealIPv4()]['id'] = session_id();
			session_regenerate_id($flag);
			$this->id = session_id();
			try{
				$sql = sprintf('UPDATE `sessions` SET `session_id` = "%s" WHERE `session_id` = "%s"', $this->id, $_SESSION[$this->registry->libs->_getRealIPv4()]['id']);
				$r = $this->dbconn->query($sql);
			} catch(Exception $e) {
				return $e;
			}
			$this->destroy($_SESSION[$this->registry->libs->_getRealIPv4()]['id']);
		}
		return;
	}

	/**
	 *! @function register
	 *  @abstract Registers new key/value to session data
	 *  @param $name string The session key
	 *  @param $value string The session data
	 */
	public function register($name, $value)
	{
		return ((isset($name))&&(isset($value))) ? $_SESSION[$name] : false;
	}

	/**
	 *! @function _path
	 *  @abstract Used to generate the current users FQDN
	 */
	private function _path()
	{
		return '/';//return ($_SERVER['HTTP_HOST'] == $_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] : $_SERVER['HTTP_REFERER'];
	}

	/**
	 *! @function domain
	 *  @abstract Returns the current FQDN of installed application
	 */
	private function _domain()
	{
		return 'sso.scl.utah.edu';//return ($_SERVER['HTTP_HOST'] == $_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_HOST'] : $_SERVER['HTTP_REFERER'];
	}

	/**
	 *! @function gc
	 *  @abstract Performs timeout cleanup on all database key/values
	 *  @param $timeout int The current UNIX timestamp
	 */
	private function gc($timeout)
	{
		if (isset($timeout)) {
			$sql = sprintf('CALL Session_Timeout("%d")', time() - $timeout);
			echo $sql;
			$r = $this->dbconn->query($sql);
			return ((is_resource($r)) && ($this->dbconn->affected() > 0)) ? true : false;
		}
		return false;
	}
}
?>
