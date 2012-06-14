<?php

/* define the namespace */
//namespace core\utilities\logging;

/* prevent direct access */
if (!defined('__SITE')) exit('No direct calls please...');

/**
 * Handle debug logging
 *
 * LICENSE: This source file is subject to version 3.01 of the GPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/gpl.html.  If you did not receive a copy of
 * the GPL License and are unable to obtain it through the web, please
 *
 * @category   utilities
 * @package    phpMyFramework
 * @author     Original Author <jason.gerfen@gmail.com>
 * @copyright  2010 Jason Gerfen
 * @license    http://www.gnu.org/licenses/gpl.html  GPL License 3
 * @version    0.1
 */
class log
{
	protected static $instance;
	private $handle;
	private function __construct($file, $message)
	{
		$this->main($file, $message);
	}
	public static function instance($file, $message)
	{
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new self($file, $message);
		}
		return self::$instance;
	}
	private function _date()
	{
		return date("Y-m-d H:i:s");
	}
	private function main($file, $message)
	{
		$h = fopen($file, 'a+');
		flock($h, LOCK_EX);
		fwrite($h, '['.$this->_date().'] - '.$message.PHP_EOL);
		flock($h, LOCK_UN);
		fflush($h);
		fclose($h);
	}
}
?>
