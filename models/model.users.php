<?php

/* define the namespace */
//namespace models\users;

/* prevent direct access */
if (!defined('__SITE')) exit('No direct calls please...');

/**
 * Handle authentication
 *
 *
 * LICENSE: This source file is subject to version 3.01 of the GPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/gpl.html.  If you did not receive a copy of
 * the GPL License and are unable to obtain it through the web, please
 *
 * @category   libraries
 * @package    phpMyFramework
 * @author     Original Author <jason.gerfen@gmail.com>
 * @copyright  2010 Jason Gerfen
 * @license    http://www.gnu.org/licenses/gpl.html  GPL License 3
 * @version    0.1
 */

/**
 *! @class users
 *  @abstract Handles user account manangement
 */
class users
{
	/**
	 * @var registry object
	 * @abstract Global class handler
	 */
	private $registry;

	/**
	 *! @var instance object - class singleton object
	 */
	protected static $instance;

	/**
	 *! @function __construct
	 *  @abstract Initializes singleton for proxyView class
	 *  @param registry array - Global array of class objects
	 */
	private function __construct($registry)
	{
		$this->registry = $registry;
		if (!$this->__setup($registry)) {
			exit(array('Error'=>'Necessary keys are missing, cannot continue'));
		}
	}

	public static function instance($registry)
	{
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new self($registry);
		}
		return self::$instance;
	}

	/**
	 *! @function setup
	 *  @abstract Performs initial requirements
	 */
	private function __setup($args)
	{
		if ((!empty($_SESSION[$this->registry->libs->_getRealIPv4()]['email']))&&
			(!empty($_SESSION[$this->registry->libs->_getRealIPv4()]['privateKey']))&&
			(!empty($_SESSION[$this->registry->libs->_getRealIPv4()]['publicKey']))&&
			(!empty($_SESSION[$this->registry->libs->_getRealIPv4()]['password']))) {
			return true;
		}else{
			return false;
		}
	}

	/**
	 *! @function __do
	 *  @abstract Determines action and acts accordingly
	 */
	public function __do($obj)
	{
		$x = false;

		$d = $this->__decrypt($obj);

		$auth = authentication::instance($this->registry);

		$u = $this->__permsUser($auth->__user($_SESSION[$this->registry->libs->_getRealIPv4()]['token']));
		$grp = $auth->__group($_SESSION[$this->registry->libs->_getRealIPv4()]['token']);
		$g = $this->__permsGroup($grp);

		if (!empty($d['do'])) {
			switch($d['do']) {
				case 'add':
					$x = $this->registry->libs->JSONEncode($this->__addUser($d, $g));
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
	 *! @function __addUser
	 *  @abstract Creates new user account and assigns default permissions on
	 *            the newly created account for the currently authenticated
	 *            user based on access level and group. Also initiates the
	 *            users key creation and key ring additions.
	 */
	private function __addUser($details, $grp)
	{
		if ($this->__valEmpty($details)) {
			return array('error'=>'Form data missing');
		}

		if ($this->__valFormat($details)) {
			return array('error'=>'Form data invalid');
		}

		if (!$this->__valPW($details['password'], $details['confirm'])) {
			return array('error'=>'Passwords did not match');
		}

		if (!$this->registry->val->_isComplex($details['password'])) {
			return array('error'=>'Password does not meet complexity requirements');
		}

		/* because we want a strong password per account create blowfish hash & salt it with site wide key */
		//$keys['pwd'] = hashes::init($this->registry)->_do($details['password'], $this->registry->opts['dbKey']);
		$keys['pwd'] = hashes::init($this->registry)->_do($details['password'], hashes::init($this->registry)->_do($this->registry->opts['dbKey']));

		$keys['pri'] = $this->registry->keyring->ssl->genPriv($keys['pwd']);
		$keys['pub'] = $this->registry->keyring->ssl->genPub();
		if ((empty($keys['pri'])) || (empty($keys['pub']))) {
			return array('error'=>'An error occured generating users keyring data');
		}

		$u = $this->__doUser($details, $keys);
		if ($u <= 0) {
			return array('error'=>'An error occured during database transaction to create user account');
		}

		$up = $this->__doPerms($details['email'], 1, 1, 1, 1);
        if ($up <= 0) {
            return array('error'=>'An error occured while creating new permissions on user object');
        }

		$k = $this->__doKeys($details, $keys);
		if ($k <= 0) {
			return array('error'=>'An error occured during database transaction to create new keyring entry');
		}

		$kp = $this->__doPerms($details['email'], 1, 1, 1, 1);
         if ($kp <= 0) {
            return array('error'=>'An error occured while creating new permissions on users keyring entry');
        }

        return array('success'=>'New account created successfully');
	}

    /**
     *! @function __doUser
     *  @abstract Helper for formatting and adding the new user
     */
    private function __doUser($details, $keys)
    {
		try {
			$sql = sprintf('CALL Users_AddUpdate("%s", "%s", "%s", "%s", "%s")',
						   $this->registry->db->sanitize($details['email']),
						   $this->registry->db->sanitize($keys['pwd']),
						   $this->registry->db->sanitize($details['level']),
						   $this->registry->db->sanitize($details['group']),
                           $this->registry->db->sanitize($keys['pwd']));
			$r = $this->registry->db->query($sql);
		} catch(Exception $e) {
			return false;
		}
        return ($r>0) ? true : false;
    }

    /**
     *! @function __doKeys
     *  @abstract Helper for formatting and adding the new keyring entry
     */
    private function __doKeys($details, $keys)
    {
		try {
			$sql = sprintf('CALL Configuration_keys_add("%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s")',
						   $this->registry->db->sanitize($details['countryName']),
						   $this->registry->db->sanitize($details['stateOrProvinceName']),
						   $this->registry->db->sanitize($details['localityName']),
						   $this->registry->db->sanitize($details['organizationalName']),
						   $this->registry->db->sanitize($details['organizationalUnitName']),
						   $this->registry->db->sanitize($this->registry->libs->_getRealIPv4()),
						   $this->registry->db->sanitize($details['email']),
						   $this->registry->db->sanitize($keys['pri']),
						   $this->registry->db->sanitize($keys['pub']),
						   $this->registry->db->sanitize($keys['pwd']));
			$r = $this->registry->db->query($sql);
		} catch(Exception $e) {
			return false;
		}
        return ($r>0) ? true : false;
    }

    /**
     *! @function __doPerms
     *  @abstract Helper for adding new permissions
     */
    private function __doPerms($name, $gw, $gr, $uw, $ur)
    {
        $auth = authentication::instance($this->registry);
        $user['name'] = $auth->__user($_SESSION['token']);
        $user['group'] = $auth->__group($_SESSION['token']);
        unset($auth);

        $user['name'] = (empty($user['name'])) ? 'admin' : $user['name'];
        $user['group'] = (empty($user['group'])) ? 'admin' : $user['group'];

        try {
            $sql = sprintf('CALL Perms_AddUpdate("%s", "%s", "%s", "%d", "%d", "%s", "%d", "%d", "%s")',
                           $this->registry->db->sanitize($name),
                           $this->registry->db->sanitize($user['name']),
                           $this->registry->db->sanitize($user['group']),
                           $this->registry->db->sanitize($gw),
                           $this->registry->db->sanitize($gr),
                           $this->registry->db->sanitize($user['name']),
                           $this->registry->db->sanitize($uw),
                           $this->registry->db->sanitize($ur),
                           $this->registry->db->sanitize(hashes::init($this->registry)->_do($this->registry->opts['dbKey'])));
            $r = $this->registry->db->query($sql);
        } catch(Exception $e) {
            return false;
        }
        return ($r>0) ? true : false;
    }

    /**
     *! @function __valEmpty
     *  @abstract Perform check on empty variables
     */
    private function __valEmpty($details)
    {
		return ((empty($details['email'])) || (empty($details['password'])) ||
                (empty($details['confirm'])) || (empty($details['level'])) ||
                (empty($details['group'])) || (empty($details['organizationalName'])) ||
                (empty($details['organizationalUnitName'])) || (empty($details['localityName'])) ||
                (empty($details['stateOrProvinceName'])) || (empty($details['countryName'])));
    }

    /**
     *! @function __valFormat
     *  @abstract Perform validation on submitted data
     */
    private function __valFormat($details)
    {
		return ((($this->registry->val->type($details['email'], 'alpha')) ||
                 ($this->registry->val->type($details['email'], 'email'))) ||
                //($this->registry->val->type($details['password'], 'special')) ||
                //($this->registry->val->type($details['confirm'], 'special')) ||
                ($this->registry->val->type($details['level'], 'alpha')) ||
                ($this->registry->val->type($details['group'], 'alpha')) ||
                //($this->registry->val->type($details['organizationalName'], 'special')) ||
                //($this->registry->val->type($details['organizationalUnitName'], 'special')) ||
                ($this->registry->val->type($details['localityName'], 'alpha')) ||
                ($this->registry->val->type($details['stateOrProvinceName'], 'string')) ||
                ($this->registry->val->type($details['countryName'], 'string')));
    }

    /**
     *! @function __valPW
     *  @abstract Perform comparison on password
     */
    private function __valPW($pw, $cpw)
    {
        return (strcmp(sha1($pw), sha1($cpw)) === 0) ? true : false;
    }

    /**
     *! @function __permsUser
     *  @abstract Retrieves object permissions by users
     */
    private function __permsUser($u)
    {
		try {
			$sql = sprintf('CALL Perms_SearchUser("%s", "%s")', $this->registry->db->sanitize($u), $this->registry->db->sanitize(hashes::init($this->registry)->_do($this->registry->opts['dbKey'])));
			$r = $this->registry->db->query($sql);
		} catch(Exception $e) {
			// error handler
		}
        return (($r) && (is_array($r))) ? $r : false;
    }

    /**
     *! @function __permsGroup
     *  @abstract Retrieves object permissions by group
     */
    private function __permsGroup($g)
    {
		try {
			$sql = sprintf('CALL Perms_SearchGroup("%s", "%s")', $this->registry->db->sanitize($g), $this->registry->db->sanitize(hashes::init($this->registry)->_do($this->registry->opts['dbKey'])));
			$r = $this->registry->db->query($sql);
		} catch(Exception $e) {
			// error handler
		}
        return (($r) && (is_array($r))) ? $r : false;
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
		return ($this->__dHlpr($obj, $x)) ? $x : array('error'=>'Decryption of submitted form data failed');
	}

	/**
	 *! @function __dHlpr
	 *  @abstract Compares original key/value with decrypted key/value to ensure no missing data
	 */
	private function __dHlpr($orig, $dec)
	{
		$x = true;
		if (is_array($dec)) {
			foreach($dec as $key => $value) {
				if ((array_key_exists($key, $orig))&&(empty($value))) {
					return false;
				}
			}
		}
		return $x;
	}

	public function __clone() {
		trigger_error('Cloning prohibited', E_USER_ERROR);
	}

	public function __wakeup() {
		trigger_error('Deserialization of singleton prohibited ...', E_USER_ERROR);
	}

	public function __destruct()
	{
		unset($this->instance);
		return true;
	}
}
?>
