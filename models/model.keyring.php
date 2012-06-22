<?php

/* define the namespace */
//namespace models\keyring;

/* prevent direct access */
if (!defined('__SITE')) exit('No direct calls please...');

/**
 * Manage RSA keys and encryption
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
 *! @class keyring
 *  @abstract Handles OpenSSL RSA keys
 */
class keyring
{

	/**
	 * @var registry object
	 * @abstract Global class handler
	 */
	private $registry;

	/**
	 * @var ssl object
	 * @abstract Handles current users ssl object
	 */
	public $ssl;

	/**
	 *! @var instance object - class singleton object
	 */
	protected static $instance;

	/**
	 *! @var keys array
	 *  @abstract array containing private and public keys per email
	 */
	protected static $keys = array();

	/**
	 *! @var config array - OpenSSL configuration settings
	 */
	private $config = array('config'				=> 'config/openssl.cnf',
							'encrypt_key'			=> true,
							'private_key_type'		=> OPENSSL_KEYTYPE_RSA,
							'digest_algorithm'		=> 'sha512',
							'private_key_bits'		=> 2048,
							'x509_extensions'		=> 'usr_cert',
							'encrypt_key_cipher'	=> OPENSSL_CIPHER_3DES);

	/**
	 *! @var dn array - Contains location specific settings
	 */
	private $dn = array('countryName'             => 'US',
						'stateOrProvinceName'     => 'Utah',
						'localityName'            => 'Salt Lake City',
						'organizationName'        => 'University Of Utah',
						'organizationalUnitName'  => 'Marriott Library',
						'commonName'              => 'localhost:8080',
						'emailAddress'            => 'licensing@utah.edu');

	/**
	 *! @function __construct
	 *  @abstract Initializes singleton for proxyView class
	 *  @param registry array - Global array of class objects
	 */
	public function __construct($registry, $args)
	{
		$this->registry = $registry;
		$email = (!empty($_SESSION[$this->registry->libs->_getRealIPv4()]['email'])) ? $_SESSION[$this->registry->libs->_getRealIPv4()]['email'] : $args['email'];
		$this->__setup($this->registry->val->__do($email));
	}

	/**
	 *! @function instance
	 *  @abstract Creates non-deserializing, non-cloneable instance object
	 *  @param configuration array - server, username, password, database
	 *  @return Singleton - Singleton object
	 */
	public static function instance($config, $args)
	{
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new self($config, $args);
		}
		return self::$instance;
	}

	/**
	 *! @function setup
	 *  @abstract Creates new OpenSSL object based on current user or
	 *            default settings
	 */
	private function __setup($email=false)
	{
		$c = $this->__tsettings($this->__settings());
		$d = $this->__dn();

		$this->config = ($this->__vsettings($c)) ? $c : $this->config;
		$this->dn = ($this->__vdn($d)) ? $d : $this->dn;

		if (class_exists('openssl')){
			$this->ssl = openssl::instance(array('config'=>$this->config, 'dn'=>$this->dn));
		}

		if (is_object($this->ssl)){
			(!empty($args['email'])) ? $this->__keyring($args['email']) : $this->__keyring();
		}
	}

	/*
	 * Verify our $this->config array
	 */
	private function __vsettings($array)
	{
		return ((!empty($array['config']))&&
				(!empty($array['encrypt_key']))&&
				(!empty($array['private_key_type']))&&
				(!empty($array['digest_algorithm']))&&
				(!empty($array['private_key_bits']))&&
				(!empty($array['x509_extensions']))&&
				(!empty($array['encrypt_key_cipher']))) ? true : false;
	}

	/*
	 * Perform typecasting on required elements
	 */
	private function __tsettings($array)
	{
		$array['private_key_type'] = (int)$array['private_key_type'];
		$array['encrypt_key_cipher'] = (int)$array['encrypt_key_cipher'];
		return $array;
	}

	/*
	 * Verify our $this->dn array
	 */
	private function __vdn($array)
	{
		return ((!empty($array['countryName']))&&
				(!empty($array['stateOrProvinceName']))&&
				(!empty($array['localityName']))&&
				(!empty($array['organizationName']))&&
				(!empty($array['organizationalUnitName']))&&
				(!empty($array['commonName']))&&
				(!empty($array['emailAddress']))) ? true : false;
	}

	/**
	 *! @function __private
	 *  @abstract Creates session based on current user which contains necessary
	 *            keys and attributes for future communications
	 */
	private function __keyring($email=false)
	{
		$r = true;
		$email = (!empty($_SESSION[$this->registry->libs->_getRealIPv4()]['email'])) ? $_SESSION[$this->registry->libs->_getRealIPv4()]['email'] : $email;
		try{
			if ($this->__chkKeys($_SESSION[$this->registry->libs->_getRealIPv4()])){
				if (!$email){
					$sql = sprintf('CALL Configuration_def_keys("%s")', $this->registry->db->sanitize(hashes::init($this->registry)->_do($this->registry->opts['dbKey'])));
				} else {
					$sql = sprintf('CALL Configuration_keys_get("%s, %s")', $this->registry->db->sanitize($email), $this->registry->db->sanitize(hashes::init($this->registry)->_do($this->registry->opts['dbKey'])));
				}

				$r = $this->registry->db->query($sql);

				$r = ((!empty($r['publicKey']))&&(!empty($r['emailAddress']))&&(!empty($r['privateKey']))&&(!empty($r['pword']))) ? array('email'=>$r['emailAddress'], 'privateKey'=>$r['privateKey'], 'publicKey'=>$r['publicKey'],'password'=>$r['pword']) : false;

				if ($r){
					$this->__setKeys($r);
					$r=true;
				}
			}
		} catch(Exception $e){
			// error handler
		}
		return $r;
	}

	/**
	 *! @function __public
	 *  @abstract Retrieves current public key and passes to XMLHttpRequest
	 */
	public function __public($a=false)
	{
		return ((!empty($_SESSION[$this->registry->libs->_getRealIPv4()]['email']))&&(!empty($_SESSION[$this->registry->libs->_getRealIPv4()]['publicKey']))) ?
				array('email'=>$_SESSION[$this->registry->libs->_getRealIPv4()]['email'], 'key'=>$_SESSION[$this->registry->libs->_getRealIPv4()]['publicKey']) :
				array('error'=>'No keyring information could be located');
	}

	/**
	 *! @function __setKeys
	 *  @abstract Adds or merges current keys with available keys
	 */
	private function __setKeys($a)
	{
		foreach($a as $key => $value) {
			$_SESSION[$this->registry->libs->_getRealIPv4()][$key] = $value;
		}
	}

	/**
	 *! @function __chkKeys
	 *  @abstract Verifies existing keyring structure
	 */
	private function __chkKeys($a)
	{
		if ((is_array($a))&&(count($a)>=1)&&(!in_array('privateKey', $a))&&(!in_array('publicKey', $a))&&(!in_array('email', $a))&&(!in_array('password', $a))) {
			$x=true;
		}else{
			$x=false;
		}
		return $x;
	}

	/**
	 *! @function __settings
	 *  @abstract Handles the retrieval of current OpenSSL configuration data
	 */
	private function __settings()
	{
		try{
			$sql = sprintf('CALL Configuration_cnf_get()');
			$r = $this->registry->db->query($sql);
		} catch(Exception $e){
			// error handler
		}
		return (!empty($r)) ? $r : false;
	}

	/**
	 *! @function __dn
	 *  @abstract Handles the retrieval of current OpenSSL DN
	 */
	private function __dn()
	{
		try{
			$sql = sprintf('CALL Configuration_get_dn()');
			$r = $this->registry->db->query($sql);
		} catch(Exception $e){
			// error handler
		}
		return (!empty($r)) ? $r : false;
	}

	public function __clone() {
		trigger_error('Cannot clone instance of Singleton pattern ...', E_USER_ERROR);
	}

	public function __wakeup() {
		trigger_error('Cannot deserialize instance of Singleton pattern ...', E_USER_ERROR);
	}

	public function __destruct()
	{
		unset($instance);
		return true;
	}
}
?>
