<?php

/* define the namespace */
//namespace models\authentication;

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
 *! @class authentication
 *  @abstract Performs primary and additional authentication mechanisms
 */
class authentication
{
	/**
	 * @var registry object
	 * @abstract Global class handler
	 */
	private $registry;

	/**
	 * @var users hash
	 * @abstract Place holder for the users hash. Because of the segragation
	 *           of user account encryption vs. application specific encryption
	 *           keys this needs to be available for token generation purposes
	 */
	private $pass;

	/**
	 * @var users salt
	 * @abstract Place holder for unique salt
	 */
	private $salt;

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
		if (!$this->__setup($registry)){
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
			(!empty($_SESSION[$this->registry->libs->_getRealIPv4()]['password']))){
			return true;
		} else {
			return false;
		}
	}

	/**
	 *! @function __do
	 *  @abstract Performs initial authentication
	 *            1. Checks for existing session token & re-authenticates if present
	 *            2. Decrypts submitted authentication credentials & exits if it fails
	 *            3. Performs authentication on decrypted form submission
	 *            4. Generates new sesssion token upon successful authentication
	 *            5. Associates signature digest with user account to help prevent
	 *               token manipulation
	 *            6. Generates response consisting of success message, hash of session
	 *               token (to be used for cross domain & SSO purposes)
	 */
	public function __do($creds)
	{
		if (!empty($_SESSION[$this->registry->libs->_getRealIPv4()]['token'])){
			$x = $this->__reauth($_SESSION[$this->registry->libs->_getRealIPv4()]['token']);
		} else {
			$obj = $this->__decrypt($creds);
			if (array_key_exists('error', $obj)) {
				$this->__nuke();
				return $obj;
			}

			$x = $this->__auth($obj);

			if (is_array($x)){
				return $x;
			} else {

				if ($x) {
					$k = $this->__reinit($this->__reset($obj['email']), $obj['email']);
					if (is_array($k)) {
						$keyring = array('email'=>$this->registry->val->__do($obj['email'], 'email'), 'key'=>$k['publicKey']);
					}
				}

				$token = $this->__genToken($obj);
				if (!$token) {
					$this->__nuke();
					return array('error'=>'Authenticated token generation failed, cannot continue');
				}

				$obj['signature'] = $this->registry->keyring->ssl->sign($_SESSION[$this->registry->libs->_getRealIPv4()]['token'], $_SESSION[$this->registry->libs->_getRealIPv4()]['privateKey'], $_SESSION[$this->registry->libs->_getRealIPv4()]['password']);
				$x = $this->__register($obj);

				if ($x){
					$proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
					$url = (!empty($_SERVER['HTTP_ORIGIN'])) ? $_SERVER['HTTP_REFERER'] : $proto.$_SERVER['HTTP_HOST'].'/';
				}

				$x = (($x)&&(is_array($keyring))) ? array('success'=>'User was successfully authenticated', 'token'=>sha1($_SESSION[$this->registry->libs->_getRealIPv4()]['token']), 'keyring'=>$keyring, 'url'=>$url) : array('error'=>'An error occured when associating token with user');
			}
		}
		return $x;
	}

	/**
	 *! @function __reset
	 *  @abstract Looks up recently authenticated users keyring data
	 */
	private function __reset($email)
	{
		try {
			$sql = sprintf('CALL Configuration_keys_get("%s", "%s")', $email, $this->pass);
			$r = $this->registry->db->query($sql);
			return (is_array($r)) ? $r : false;
		} catch(Exception $e) {
			// error handling
		}
	}

	/**
	 *! @function __reinit
	 *  @abstract Destroys current SSL registry object and re-initializes it
	 *            based on the currently authenticated users private key to
	 *            further segregate user authentication from one another
	 */
	private function __reinit($obj, $email)
	{
		if (is_array($obj)) {
			unset($this->registry->keyring->ssl);
			$this->registry->keyring->ssl = openssl::instance($obj);

		    $_SESSION[$this->registry->libs->_getRealIPv4()]['email'] = $email;
			$_SESSION[$this->registry->libs->_getRealIPv4()]['privateKey'] = $obj['privateKey'];
			$_SESSION[$this->registry->libs->_getRealIPv4()]['publicKey'] = $obj['publicKey'];
			$_SESSION[$this->registry->libs->_getRealIPv4()]['password'] = $this->pass;

			return $obj;
		} else {
			return false;
		}
	}

	/**
	 *! @function __auth
	 *  @abstract Returns booleans on database lookup of decrypted credentials
	 */
	private function __auth($creds)
	{
		if (is_array($creds)){
			if ((!empty($creds['email']))&&(!empty($creds['password']))) {

				/* prepare the password supplied */
				$this->pass = hashes::init($this->registry)->_do($creds['password'], hashes::init($this->registry)->_do($this->registry->opts['dbKey']));
				$this->salt = $this->registry->libs->_16($this->pass);

				/* check to see if this user has a token first */

				try{
					$sql = sprintf('CALL Auth_CheckUser("%s", "%s", "%s")', $this->registry->db->sanitize($creds['email']), $this->registry->db->sanitize($this->pass), $this->registry->db->sanitize($this->pass));
					$r = $this->registry->db->query($sql);
					if ($r['x']<=0){
						$x = false;
					}else{
						$x = true;
					}
				} catch(Exception $e){
					// error handling
				}
			}else{
				$x = false;
			}
		}else{
			$x = false;
		}

		if (!$x) {
			if ($this->__countLogins($_SESSION[$this->registry->libs->_getRealIPv4()]['count'], $this->registry->opts['flogin'])) {
				$this->__addBlock($this->registry->libs->_getRealIPv4());
			} else {
				$_SESSION[$this->registry->libs->_getRealIPv4()]['count']++;
			}

			$this->__nuke();
		}

		return (!$x) ? array('error'=>'User authentication failed') : $x;
	}

	/**
	 *! @function __reauth
	 *  @abstract Decodes token and re-authenticates user
	 */
	public function __reauth($token, $hash=false)
	{
		$this->pass = $_SESSION[$this->registry->libs->_getRealIPv4()]['password'];
		$this->salt = $this->registry->libs->_16($this->pass);

		if (empty($token)){
			$this->__nuke();
			return array('error'=>'No authentication token exists for this client');
		}

		if ((!empty($token))&&(!empty($hash))) {
			if (strcmp($hash, sha1($token))!==0) {
				$this->__nuke();
				return array('error'=>'Authentication provided incorrect, destroying token');
			}
		}

		$a = $this->__decode($token);

		if (!$this->__hijack($a)){
			$this->__nuke();
			return array('error'=>'Session hijack attempt detected, destroying token');
		}

		if ($this->__timeout($a[6], $this->registry->opts['timeout'])){
			//$this->__nuke(true);
			return array('error'=>'The authenticated session has timed out, please re-authenticate');
		}

		$s = $this->__getSignature($a[0]);

		if (empty($s['signature'])){
			$this->__nuke();
			return array('error'=>'Could not obtain signature associated with authentication, destroying token');
		}

		if (!$this->__checkSignature($_SESSION[$this->registry->libs->_getRealIPv4()]['token'], $s['signature'])){
			$this->__nuke();
			return array('error'=>'Cryptographic verification of authentication token signature failed, destroying token');
		}

		$token = $this->__regenToken($a);
		$a['signature'] = $this->registry->keyring->ssl->sign($_SESSION[$this->registry->libs->_getRealIPv4()]['token'], $_SESSION[$this->registry->libs->_getRealIPv4()]['privateKey'], $this->pass);
		$a['email'] = $this->registry->keyring->ssl->aesDenc($a[0], $this->pass, $this->salt);
		$x = $this->__register($a);

		if (!$x) {
			$this->__nuke();
		}

		if ($x){
			$proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
			$url = (!empty($_SERVER['HTTP_ORIGIN'])) ? $_SERVER['HTTP_REFERER'] : $proto.$_SERVER['HTTP_HOST'].'/';
		}

		return ($x) ? array('success'=>'Re-authentication succeeded', 'token'=>$token, 'url'=>$url) : array('error'=>'Re-authenticaiton failed');
	}

	/**
	 *! @function __decrypt
	 *  @abstract Handle decryption of submitted form data
	 */
	private function __decrypt($obj)
	{
		if (count($obj)>0){
			$x = array(); $obj = $this->__strip($obj);
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

	/**
	 *! @function __strip
	 *  @abstract Strip out XMLHttpRequest specifics as they will cause decryption to fail
	 */
	private function __strip($data)
	{
		$x = false;
		if (is_array($data)) {
			foreach($data as $key => $value) {
				if ((strcmp($key, '_')!=0)&&(strcmp($key, 'callback')!=0)&&(strcmp($key, 'nxs')!=0)) {
					$x[$key] = $value;
				}
			}
		}
		return $x;
	}

	/**
	 *! @function __decode
	 *  @abstract Handle decoding of authentication token
	 */
	private function __decode($token)
	{
		return (!empty($token)) ? preg_split('/:/', $token) : false;
	}

	/**
	 *! @function __hijack
	 *  @abstract Performs anti-session hijacking validations
	 */
	private function __hijack($a)
	{
		if (is_array($a)){

			$t = filter_var(urlencode($this->registry->keyring->ssl->aesDenc($a[5], $this->pass, $this->salt)), FILTER_VALIDATE_REGEXP, array('options'=> array('regexp'=>'/^'.urlencode(getenv('HTTP_REFERER')).'/Di')));

			$x = ((strcmp($this->registry->keyring->ssl->aesDenc($a[3], $this->pass, $this->salt), sha1($this->registry->libs->_getRealIPv4()))==0)&&
				  (strcmp($this->registry->keyring->ssl->aesDenc($a[4], $this->pass, $this->salt), sha1(getenv('HTTP_USER_AGENT')))==0)&&
				  (strcmp($t, urlencode(getenv('HTTP_REFERER'))==0)));
		} else {
			$x = false;
		}
		return $x;
	}

	/**
	 *! @function __genToken
	 *  @abstract Creates unique token based on visiting machine and
	 *            authenticated user information
	 */
	private function __genToken($obj)
	{
		if (count($obj)>=2){

			if (($a = $this->__getLevelGroup($obj['email']))===false){
				return false;
			}

			$token = sprintf("%s:%s:%s:%s:%s:%s:%d",
							$this->registry->keyring->ssl->aesEnc($this->registry->val->__do($obj['email'], 'email'), $this->pass, $this->salt),
							$this->registry->keyring->ssl->aesEnc($this->registry->val->__do($a['level'], 'string'), $this->pass, $this->salt),
							$this->registry->keyring->ssl->aesEnc($this->registry->val->__do($a['grp'], 'string'), $this->pass, $this->salt),
							$this->registry->keyring->ssl->aesEnc($this->registry->val->__do(sha1($this->registry->libs->_getRealIPv4()), 'string'), $this->pass, $this->salt),
							$this->registry->keyring->ssl->aesEnc($this->registry->val->__do(sha1(getenv('HTTP_USER_AGENT')), 'string'), $this->pass, $this->salt),
							$this->registry->keyring->ssl->aesEnc($this->registry->val->__do(getenv('HTTP_REFERER'), 'string'), $this->pass, $this->salt),
							time());

			$_SESSION[$this->registry->libs->_getRealIPv4()]['token'] = $token;

			return sha1($token);
		}
	}

	/**
	 *! @function __regenToken
	 *  @abstract Regenerates unique token based on visiting machine and
	 *            authenticated user information
	 */
	private function __regenToken($obj)
	{
		$token = sprintf("%s:%s:%s:%s:%s:%s:%d",
						$obj[0], $obj[1], $obj[2], $obj[3], $obj[4], $obj[5], time());
		$_SESSION[$this->registry->libs->_getRealIPv4()]['token'] = $token;

		return sha1($token);
	}

	/**
	 *! @function __getLevelGroup
	 *  @abstract Retrieves access leval and group membership for authenticated
	 *            user account
	 */
	private function __getLevelGroup($email)
	{
		try{
			$sql = sprintf('CALL Auth_GetLevelGroup("%s", "%s")', $this->registry->val->__do($email, 'email'), $this->registry->db->sanitize($this->pass));
			$r = $this->registry->db->query($sql);
		} catch(Exception $e){
			// error handler
		}
		return ($r) ? $r : false;
	}

	/**
	 *! @function __register
	 *  @abstract Registers current authentication token within users table
	 */
	private function __register($obj)
	{
		try {
			$sql = sprintf('CALL Users_AddUpdateToken("%s", "%s", "%s")', $this->registry->db->sanitize($obj['email']), $this->registry->db->sanitize($obj['signature']), $this->registry->db->sanitize($this->pass));
			$r = $this->registry->db->query($sql);
		} catch(Exception $e) {
			// error handling
		}
		return ($r) ? true : false;
	}

	/**
	 *! @function __getSignature
	 *  @abstract Retrieve currently authenticated users signature associated with token
	 */
	private function __getSignature($email)
	{
		$r = false;
		if (!empty($email)) {
			try {
				$sql = sprintf('CALL Users_GetToken("%s", "%s")', $this->registry->db->sanitize($this->registry->keyring->ssl->aesDenc($email, $this->pass, $this->salt)), $this->pass);
				$r = $this->registry->db->query($sql);
			} catch(Exception $e) {
				// error handler
			}
		}
		return ($r) ? $r : false;
	}

	/**
	 *! @function __checkSignature
	 *  @abstract Compares signature associated with authentication token
	 */
	private function __checkSignature($token, $signature)
	{
		if ((empty($token))||(empty($signature))) {
			return false;
		}

		if ($this->registry->keyring->ssl->verify($token, $signature, $_SESSION[$this->registry->libs->_getRealIPv4()]['publicKey'])) {
			return true;
		}

		return false;
	}

	/**
	 *! @function __user
	 *  @abstract Decodes authentication token and returns authenticated username
	 */
	public function __user($token)
	{
		if ($l = $this->__decode($token) !== false) {
			return $this->registry->keyring->ssl->aesDenc($l[0], $this->pass, $this->salt);
		}
		return false;
	}

	/**
	 *! @function __level
	 *  @abstract Decodes authentication token and returns access level
	 */
	public function __level($token)
	{
		if (($l = $this->__decode($token)) !== false) {
			return $this->registry->keyring->ssl->aesDenc($l[1], $this->pass, $this->salt);
		}
		return false;
	}

	/**
	 *! @function __group
	 *  @abstract Decodes authentication token and returns group membership
	 */
	public function __group($token)
	{
		if (($l = $this->__decode($token)) !== false) {
			return $this->registry->keyring->ssl->aesDenc($l[2], $this->pass, $this->salt);
		}
		return false;
	}

	/**
	 *! @function __timeout
	 *  @abstract Returns boolean of current time vs. allowed time
	 */
	private function __timeout($a, $v)
	{
		return ($a < (time() - $v));
	}

	/**
	 *! @function __countLogins
	 *  @abstract Performs calculation of current count of logins
	 */
	private function __countLogins($current=0, $allowed)
	{
		return ($current > $allowed) ? true : false;
	}

	/**
	 *! @function _addBlock
	 *  @function Creates new entry on block list ACL
	 */
	private function __addBlock($ip)
	{
		try{
			$sql = sprintf('CALL Configuration_access_add("%s", "%s", "%s")',
							$this->registry->db->sanitize($ip),
							$this->registry->db->sanitize($ip),
						    $this->registry->db->sanitize(hashes::init($this->registry)->_do($this->registry->opts['dbKey'])));
			$r = $this->registry->db->query($sql);
		} catch(PDOException $e){
			// error handling
		}
		return ($r>0) ? true : false;
	}

	/**
	 *! @function __nuke
	 *  @abstract Kills authentication token, removes digital signature of
	 *            authenticated user & destroys user specific authentication data
	 */
	private function __nuke($x=false)
	{
		$c = $_SESSION[$this->registry->libs->_getRealIPv4()]['count'];
		$x = $this->__decode($_SESSION[$this->registry->libs->_getRealIPv4()]['token']);

		unset($_SESSION[$this->registry->libs->_getRealIPv4()]);

		if ($x){
			if ($this->__countLogins($c, $this->registry->opts['flogin'])) {
				$this->__addBlock($this->registry->libs->_getRealIPv4());
			} else {
				$c++;
			}
		}

		$_SESSION[$this->registry->libs->_getRealIPv4()]['count'] = $c;

		$x['signature'] = '';

		$this->__register($x);
		return;
	}
}
?>
