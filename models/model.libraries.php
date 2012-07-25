<?php

/* define the namespace */
//namespace models\libraries;

/* prevent direct access */
if (!defined('__SITE')) exit('No direct calls please...');

/**
 * Libraries
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
 * @copyright  2010-2011 Jason Gerfen
 * @license    http://www.gnu.org/licenses/gpl.html  GPL License 3
 * @version    0.1
 */

class libraries {
	protected static $instance;
	private function __construct()
	{
		return;
	}

	public static function init()
	{
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @function _dbEngine
	 * @abstract Determine database engine and class
	 * @param $opt Option passed for database class to load
	 */
	function _dbEngine($opt)
	{
		switch($opt){
			case 'mssql':
				$eng = 'mssqlDBconn';
				break;
			case 'pgsql':
				$eng = 'pgSQLDBconn';
				break;
			case 'mysql':
				$eng = 'mysqlDBconn';
				break;
			default:
				$eng = 'mysqlDBconn';
				break;
		}
		return $eng;
	}

	/**
	 * @function _16
	 * @abstract Creates substring of argument
	 * @param $string string String to return sub-string of
	 */
	function _16($string)
	{
		return substr($string, round(strlen($string)/3, 0, PHP_ROUND_HALF_UP), 16);
	}

	/**
	 * @function _uuid
	 * @abstract Generates a random GUID
	 */
	function uuid()
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff),
						mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000,
						mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff),
						mt_rand(0, 0xffff), mt_rand(0, 0xffff));
	}

	/**
	 * @function _getRealIPv4
	 * @abstract Try all methods of obtaining 'real' IP address
	 */
	function _getRealIPv4()
	{
		if ((getenv('HTTP_CLIENT_IP')) && ($this->_ip(getenv('HTTP_CLIENT_IP')))) return getenv('HTTP_CLIENT_IP');
		if ((getenv('HTTP_X_FORWARDED_FOR')) && ($this->_forwarded(getenv('HTTP_X_FORWARDED_FOR')))) return $this->_forwarded(getenv('HTTP_X_FORWARDED_FOR'));
		if ((getenv('HTTP_X_FORWARDED_HOST')) && ($this->_ip(getenv('HTTP_X_FORWARDED_HOST')))) return getenv('HTTP_X_FORWARDED_HOST');
		if ((getenv('HTTP_X_FORWARDED_SERVER')) && ($this->_ip(getenv('HTTP_X_FORWARDED_SERVER')))) return getenv('HTTP_X_FORWARDED_SERVER');
		if ((getenv('HTTP_X_CLUSTER_CLIENT_IP')) && ($this->_ip(getenv('HTTP_X_CLUSTER_CLIENT_IP')))) return getenv('HTTP_X_CLUSTER_CLIENT_IP');
		return getenv('REMOTE_ADDR');
/*
 		return (getenv('HTTP_CLIENT_IP') && $this->_ip(getenv('HTTP_CLIENT_IP'))) ?
					getenv('HTTP_CLIENT_IP') :
					(getenv('HTTP_X_FORWARDED_FOR')	 && $this->_forwarded(getenv('HTTP_X_FORWARDED_FOR'))) ?
						$this->_forwarded(getenv('HTTP_X_FORWARDED_FOR')) :
						(getenv('HTTP_X_FORWARDED') && $this->_ip(getenv('HTTP_X_FORWARDED'))) ?
							getenv('HTTP_X_FORWARDED') :
							(getenv('HTTP_X_FORWARDED_HOST') && $this->_ip(getenv('HTTP_FORWARDED_HOST'))) ?
								getenv('HTTP_X_FORWARDED_HOST') :
								(getenv('HTTP_X_FORWARDED_SERVER') && $this->_ip(getenv('HTTP_X_FORWARDED_SERVER'))) ?
									getenv('HTTP_X_FORWARDED_SERVER') :
									(getenv('HTTP_X_CLUSTER_CLIENT_IP') && $this->_ip(getenv('HTTP_X_CLIUSTER_CLIENT_IP'))) ?
										getenv('HTTP_X_CLUSTER_CLIENT_IP') :
										getenv('REMOTE_ADDR');
*/
	}

	/**
	 * @function _ip
	 * @abstract Attempts to determine if IP is non-routeable
	 */
	function _ip($ip, $allow=true)
	{
		if ($allow) return true;
		if (!empty($ip) && ip2long($ip)!=-1 && ip2long($ip)!=false){
			$nr = array(array('0.0.0.0','2.255.255.255'),
						array('10.0.0.0','10.255.255.255'),
						array('127.0.0.0','127.255.255.255'),
						array('169.254.0.0','169.254.255.255'),
						array('172.16.0.0','172.31.255.255'),
						array('192.0.2.0','192.0.2.255'),
						array('192.168.0.0','192.168.255.255'),
						array('255.255.255.0','255.255.255.255'));
			foreach($nr as $r){
				$min = ip2long($r[0]);
				$max = ip2long($r[1]);
				if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @function _forwarded
	 * @abstract A helper for HTTP_X_FORWARDED_FOR, loops over comma
	 *           separated list of proxies associated with request
	 */
	function _forwarded($l)
	{
		if (!empty($l)){
			foreach (explode(',', $l) as $i){
				if (_ip(trim($i))) {
					return (!_ip(trim($i))) ? false : $i;
				}
			}
		} else {
			return false;
		}
	}

	/**
	 *! @function __array2string
	 *  @abstract Creates comma separated list from array values
	 */
	public function __array2string($a)
	{
		$x = '';
		if ((count($a)>0)&&(is_array($a))){
			foreach($a as $k => $v){
				if (is_array($v)){
					$x .= $this->__array2string($v).',';
				} else {
					$x .= $v.',';
				}
			}
		} else {
			$x = $a;
		}
		return (!is_array($x)) ? substr($x, 0, -1) : false;
	}

	/**
	 *! @function __string2array
	 *  @abstract Creates array from comma separated string
	 */
	public function __string2array($a)
	{
		$x = array();
		if ((!empty($a))&&(preg_match('/[\w+]\,\s?', $a))) {
			$x = preg_split('/\s/', preg_split('/,/', $a));
		}
		return (count($x) >0 ) ? $x : false;
	}

	/**
	 *! @function __flatten
	 *  @abstract Flattens a multi-dimensional array into one array
	 */
	public function __flatten($a)
	{
		$x = array();
		foreach(new RecursiveIteratorIterator(new RecursiveArrayIterator($a)) as $value){
			$x[] = $value;
		}
		return $x;
	}

	/*!
	 * @function response
	 * @abstract Handle older versions of PHP that do not have json_encode, json_decode
	 * @param $array Array Nested array of configuration options
	 * @return object A JSON object
	 */
	public function JSONencode($array)
	{
		if (!function_exists('json_encode')) {
			return self::arr2json($array);
		} else {
			return json_encode($array);
		}
	}

	/*!
	 * @function arr2json
	 * @abstract Private function to create a JSON object
	 * @param $array Array Associative array
	 * @return object The resulting JSON object
	 */
	private function arr2json($array)
	{
		if (is_array($array)) {
			foreach($array as $key => $value) $json[] = $key . ':' . self::php2js($value);
			if(count($json)>0) return '{'.implode(',',$json).'}';
			else return '';
		}
	}

	/*!
	 * @function php2js
	 * @abstract Private function using to determine array value type
	 * @param $value String|INT|BOOL|NULL|ARRAY Mixed
	 * @return STRING|INT|BOOL|NULL|ARRAY The typecasted variable
	 */
	public function php2js($value)
	{
		if(is_array($value)) return self::arr2json($val);
		if(is_string($value)) return '"'.$value.'"';
		if(is_bool($value)) return 'Boolean('.(int) $value.')';
		if(is_null($value)) return '""';
		return $value;
	}

	/*!
	 * @function geolocation
	 * @abstract Public function to retrieve GEO location data
	 * @param $ip String IPv4 string
	 * @return object The results of the GEO object
	 */
	public function geolocation($ip)
	{
		$opts = array('http'=>array('method'=>'GET','header'=>'Accept-language: en\r\nConnection: close\r\n'));
		$context = stream_context_create($opts);
		$ex = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$ip, false, $context));
		if (empty($ex)) {
			$h=curl_init();
			curl_setopt($h, CURLOPT_URL, 'http://www.geoplugin.net/php.gp?ip='.$ip);
			curl_setopt($h, CURLOPT_HEADER, false);
			curl_setopt($h, CURLOPT_RETURNTRANSFER, 1);
			$ex=unserialize(curl_exec($h));
			curl_close($h);
		}
		return $ex;
	}

	/*!
	 * @function parsegeo
	 * @abstract Public function to parse GEO data
	 * @param $data Object Parses and returns the GEO data as an array
	 * @return Array The CN data returned from the GEO lookup
	 */
	public function parsegeo($data)
	{
		$settings['latitude'] = (!empty($data['geoplugin_latitude'])) ? $data['geoplugin_latitude'] : '46.0730555556';
		$settings['longitude'] = (!empty($data['geoplugin_longitude'])) ? $data['geoplugin_longitude'] : '-100.546666667';
		$settings['localityName'] = (!empty($data['geoplugin_city'])) ? $data['geoplugin_city'] : false;
		$settings['stateOrProvinceName'] = (!empty($data['geoplugin_region'])) ? $data['geoplugin_region'] : false;
		$settings['countryName'] = (!empty($data['geoplugin_countryCode'])) ? $data['geoplugin_countryCode'] : false;
		return $settings;
	}

	/**
	 * @function _serialize
	 * @abstract Perform serialization of sent POST data. This is required for the
	 *           jQuery.AJAX plug-in checksum verification as the current PHP
	 *           serialize() function will not create an accurate hash
	 */
	function _serialize($array)
	{
		$x = '';
		if ((is_array($array)) && (count($array) > 0)) {
			foreach($array as $key => $value) {
				$x .= $key.'='.$value.'&';
			}
			$x = substr($x, 0, -1);
		}
		return (strlen($x) > 0) ? $x : false;
	}

	/**
	 * @function _genOptionsList
	 * @abstract Generic method of creating an select/option list from array
	 */
	public function _genOptionsList($array, $i)
	{
		$l = false;	$x=0;
		if ((is_array($array)) && (count($array) > 0)) {
			$l = '<option id="" value="">Make a selection...</option>';
			foreach($array as $key => $value) {
				if (!empty($value[$i])){
					$l .= (!empty($i)) ? '<option id="" value="'.$value[$i].'">'.$value[$i].'</option>' : '<option id="" value="'.$value[$key].'">'.$value[$key].'</option>';
				} else {
					$l .= '<option id="" value="'.$value[$x].'">'.$value[$x].'</option>';
				}
				$x++;
			}
		}
		return $l;
	}

	/**
	 * @function __sql2Array
	 * @abstract Generates sql from args and returns results as associative array
	 */
	public function __sql2Array($sql, $conn)
	{
		try {
			$r = $conn->query($sql, true);
		} catch(Exception $e){
			// error handler
		}
        return (($r) && (is_array($r))) ? $r : false;
	}

	/**
	 * @function _templates
	 * @abstract Generates select list of available templates
	 */
	public function _templates($folder)
	{
		if (is_dir($folder)) {
			$x=0;
			if (($handle = opendir($folder))!==false) {
				while (($dir = readdir($handle))!==false) {
					if (($dir!=='.')&&($dir!=='..')&&($dir!=='admin')&&($dir!=='cache')) {
						if (is_dir($folder.'/'.$dir)){
							$dirs[$x][] = preg_replace('/..\//', '', $folder).'/'.$dir;
						}
					}
					$x++;
				}
			} else {
				return false;
			}
		}
		return (count($dirs)!==0) ? $dirs : false;
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
