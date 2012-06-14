<?php
/* prevent direct access */
if (!defined('__SITE')) exit('No direct calls please...');

/**
 * Handle dashboard for user management area
 *
 * LICENSE: This source file is subject to version 3.01 of the GPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/gpl.html.  If you did not receive a copy of
 * the GPL License and are unable to obtain it through the web, please
 *
 * @category   views
 * @discussion Handles the dashboard for user management area
 * @author     jason.gerfen@gmail.com
 * @copyright  2008-2012 Jason Gerfen
 * @license    http://www.gnu.org/licenses/gpl.html  GPL License 3
 * @version    0.3
 */

/**
 *! @class dashboardController
 *  @abstract Handles the dashboard
 */
class dashboardUsersController
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
		$this->registry->auth = authentication::instance($this->registry);
	}

	/**
	 *! @function __auth
	 *  @abstract Handles authentication
	 */
	private function __auth($token)
	{
		$this->registry->keyring = new keyring($this->registry, $this->registry->val->__do($_POST));
		$x = $this->registry->auth->__reauth($token);
		return (array_key_exists('success', $x)) ? true : false;
	}

	/**
	 *! @function __perms
	 *  @abstract Handles permissions examination on resources
	 */
	private function __perms($token)
	{

	}

	/**
	 *! @function __load
	 *  @abstract Handles loading of associated view classes
	 */
	private function __load($file, $class)
	{
		if (file_exists($file)) {
			require $file;
		}
		$class::instance($this->registry);
	}

	/**
	 *! @function index
	 *  @abstract Calls default view
	 */
	public function index()
	{
		if ($this->__auth($_SESSION[$this->registry->libs->_getRealIPv4()]['token'])) {

			$l = $this->registry->auth->__level($_SESSION[$this->registry->libs->_getRealIPv4()]['token']);
			$g = $this->registry->auth->__group($_SESSION[$this->registry->libs->_getRealIPv4()]['token']);

			if (($l) && ($g)) {
				switch($l) {
					/* super user, admin access level and admin group membership */
					case (($l === "admin") && ($g === "admin")):
						$p = 'views/admin/view.dashboardUsers.php';
						$c = 'dashboardUsersView';
						break;
					/* group super user, admin access level and non-admin group membership */
					case (($l === "admin") && ($g !== "admin")):
						$p = 'views/admin/view.dashboardUsers.php';
						$c = 'dashboardUsersView';
						break;
					/* normal user, non-admin access level and non-admin group membership */
					case (($l !== "admin") && ($g !== "admin")):
						$p = 'views/admin/view.dashboardUsers.php';
						$c = 'dashboardUsersView';
						break;
					/* provide disallowed view default */
					default:
						$p = 'views/admin/view.dashboardDisallowed.php';
						$c = 'dashboardDisallowed';
						break;
				}
			} else {
				/* problem with supplied user & group default to deny */
				$p = 'views/admin/view.dashboardDisallowed.php';
				$c = 'dashboardDisallowed';
			}
		} else {
			/* problem with authentication default to deny */
			$p = 'views/admin/view.dashboardDisallowed.php';
			$c = 'dashboardDisallowed';
		}
		$this->__load($p, $c);
	}
}
?>