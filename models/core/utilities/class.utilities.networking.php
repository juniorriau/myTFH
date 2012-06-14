<?php

/* define the namespace */
//namespace core\utilities\networking;

/* prevent direct access */
if (!defined('__SITE')) exit('No direct calls please...');

/**
 * Handle networking functions
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

class networking
{
	public function __construct()
	{
		return;
	}

	public static function dnsrecordtype($hostname, $type='A')
	{
		return checkdnsrr($hostname, $type);
	}

	public static function dnsrecord($hostname, $type=DNS_ALL, $opt=NUL, $ext=NULL)
	{
		return dns_get_record($hostname, $type, $opt, $ext);
	}

	public static function ip2hostname($ip)
	{
		return gethostbyaddr($ip);
	}

	public static function hostname2ip($hostname)
	{
		return gethostbyname($hostname);
	}

	public static function hostname2iparray($hostname)
	{
		return gethostbynamel($hostname);
	}

	public function __destruct()
	{
		if (isset($this->handle)) {
			unset($this->handle);
		}
		return;
	}
}

/* stolen from http://www.php.net/manual/en/function.ip2long.php#102898 */
class ipFilter
{
	private static $_IP_TYPE_SINGLE = 'single';
    private static $_IP_TYPE_WILDCARD = 'wildcard';
    private static $_IP_TYPE_MASK = 'mask';
    private static $_IP_TYPE_CIDR = 'CIDR';
    private static $_IP_TYPE_SECTION = 'section';
    private $_allowed_ips = array(); 

	public function __construct($allowed_ips)
	{
		$this->_allowed_ips = $this->_fix($allowed_ips);
	}

	private function _fix($array)
	{
		$x = array();
		if (is_array($array)) {
			foreach($array as $key => $value) {
				$x[$key] = (preg_match('/,/', $value)) ? preg_split('/, /', $value) : $value;
			}
		}
		return ($x>0) ? $x : $array;
	}

	public function check($ip, $allowed_ips = null) {
        $allowed_ips = $allowed_ips ? $allowed_ips : $this->_allowed_ips;

        foreach ($allowed_ips as $allowed_ip) {
            $type = $this->_judge_ip_type($allowed_ip);
            $sub_rst = call_user_func(array($this, '_sub_checker_' . $type), $allowed_ip, $ip);
            if ($sub_rst) {
				return true;
            }
        }

        return false;
    }

    private function _judge_ip_type($ip) {
        if (strpos($ip, '*')) {
            return self :: $_IP_TYPE_WILDCARD;
        }

        if (strpos($ip, '/')) {
            $tmp = explode('/', $ip);
            if (strpos($tmp[1], '.')) {
                return self :: $_IP_TYPE_MASK;
            } else {
                return self :: $_IP_TYPE_CIDR;
            }
        }

        if (strpos($ip, '-')) {
            return self :: $_IP_TYPE_SECTION;
        }

        if (ip2long($ip)) {
            return self :: $_IP_TYPE_SINGLE;
        }

        return false;
    }
	
	private function _sub_checker_single($allowed_ip, $ip)
	{ 
		return (ip2long($allowed_ip) == ip2long($ip));
	}

	private function _sub_checker_wildcard($allowed_ip, $ip)
	{
		$allowed_ip_arr = explode('.', $allowed_ip);
		$ip_arr = explode('.', $ip);
		for($i = 0;$i < count($allowed_ip_arr);$i++){
			if ($allowed_ip_arr[$i] == '*'){
				return true;
			} else {
				if (false == ($allowed_ip_arr[$i] === $ip_arr[$i])){
					return false;
				}
			}
		}
	}

    private function _sub_checker_mask($allowed_ip, $ip)
	{
        list($allowed_ip_ip, $allowed_ip_mask) = explode('/', $allowed_ip);
        $begin = (ip2long($allowed_ip_ip) & ip2long($allowed_ip_mask)) + 1;
        $end = (ip2long($allowed_ip_ip) | (~ ip2long($allowed_ip_mask))) + 1;
        $ip = ip2long($ip);
        return ($ip >= $begin && $ip <= $end);
    } 

    private function _sub_checker_section($allowed_ip, $ip)
	{
        list($begin, $end) = explode('-', $allowed_ip);
        $begin = ip2long($begin);
        $end = ip2long($end);
        $ip = ip2long($ip);
        return ($ip >= $begin && $ip <= $end);
    }

    private function _sub_checker_CIDR($CIDR, $IP)
	{
        list ($net, $mask) = explode('/', $CIDR);
        return ( ip2long($IP) & ~((1 << (32 - $mask)) - 1) ) == ip2long($net);
    }
}

?>
