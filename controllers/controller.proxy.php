<?php

/* prevent direct access */
if (!defined('__SITE')) exit('No direct calls please...');

/**
 * Handle XMLHttpRequests
 *
 *
 * LICENSE: This source file is subject to version 3.01 of the GPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/gpl.html.  If you did not receive a copy of
 * the GPL License and are unable to obtain it through the web, please
 *
 * @category   views
 * @discussion Handles XMLHttpRequests
 * @author     jason.gerfen@gmail.com
 * @copyright  2008-2012 Jason Gerfen
 * @license    http://www.gnu.org/licenses/gpl.html  GPL License 3
 * @version    0.3
 */

/**
 *! @class proxyController
 *  @abstract Handles XMLHttpRequest proxy loading
 */
class proxyController
{

	/**
	 * @var registry object
	 * @abstract Global class handler
	 */
	private $registry;

	/**
	 *! @function __construct
	 *  @abstract Class loader
	 *  @param registry array - Global array of class objects
	 */
	public function __construct($registry)
	{
		$this->registry = $registry;

		$this->__vACL();

		/* this should do an additional check against the allowed apps prior to changing the CSRF token */
		$csrf = (preg_match('/'.$_SERVER['SERVER_NAME'].'/', $_SERVER['HTTP_ORIGIN'])) ? $_SESSION[$this->registry->libs->_getRealIPv4()]['csrf'] : getenv('HTTP_X_ALT_REFERER');

		$post = (!empty($_POST)) ? $this->registry->libs->_serialize($_POST) : $csrf;

		if (($this->__vRequest(getenv('HTTP_X_REQUESTED_WITH')))&&
			($this->__vCSRF(getenv('HTTP_X_ALT_REFERER'), $csrf))&&
			($this->__vCheckSum(getenv('HTTP_CONTENT_MD5'), $post))) {
			return;
		} else {
			exit($this->registry->libs->JSONencode(array('Error'=>'Required headers were not valid')));
		}
	}

	/**
	 *! @function __vACL
	 *  @abstract Verify the request was not on denied access list
	 */
	private function __vACL()
	{
		if (!class_exists('access')){
			exit($this->registry->JSONencode(array('Error'=>'Error initializing access class, unable to proceed. 0x0c8')));
		}

		if (access::init($this->registry)->_do()){
			exit($this->registry->libs->JSONencode(array('Error'=>'Error due to access restrictions. 0x0c9')));
		}
	}

	/**
	 *! @function __vRequest
	 *  @abstract Verify the request was valid XMLHttpRequest
	 */
	private function __vRequest($request)
	{
		return (strcmp($request, 'XMLHttpRequest')!==0) ? false : true;
	}

	/**
	 *! @function __vCSRF
	 *  @abstract Verify the CSRF token
	 */
	private function __vCSRF($header, $token)
	{
		return (strcmp($header, $token)===0) ? true : false;
	}

	/**
	 *! @function __vCheckSum
	 *  @abstract Verify the post data contained a valid checksum in the header
	 */
	private function __vCheckSum($header, $array)
	{
		$string = (gettype($array)=='string') ? md5($array) : md5($this->registry->libs->_serialize($array));
		return (strcmp(base64_decode($header), $string)!==0) ? false : true;
	}

	/**
	 *! @function index
	 *  @abstract Calls default view
	 */
	public function index()
	{
		if (file_exists('views/view.proxy.php')){
			require 'views/view.proxy.php';
		}
		new proxyView($this->registry);
	}

}
?>
