<?php
/**
 * @var $sso string SSO server
 */
$sso = 'http://sso.dev/?nxs=proxy/remote';

/**
 * @var $uid string Unique CSRF token
 */
$uid = _uuid();

/**
 * @var $proto string Set the connection protocol
 */
$proto = (!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off'||$_SERVER['SERVER_PORT']==443) ? 'https://' : 'http://';

/**
 * @array $opt Array of header options
 */
$referer = $proto.$_SERVER['HTTP_HOST'];

/**
 * @var $token string Client token (should be located within client cookie)
 */
$token = _token();

/**
 * @array $opt Array of header options
 * @var 0 string Header referer option
 * @var 1 string Header requested-with option
 * @var 2 string Header CSRF token
 * @var 3 string Optional authentication token
 * @var 4 string Header content-md5 checksum option
 */
$opt = array('Origin: '.$referer,
			 'X-Requested-With: XMLHttpRequest',
			 'X-Alt-Referer: '.$uid,
			 (!empty($token)) ? 'X-Token: '.$token : '',
			 'Client-IP: '._getRealIPv4(),
			 'Content-MD5: '.base64_encode(md5($uid)));

/* Perform page generation from SSO service */
_do($sso, $uid, $opt, $referer);

/* Register authentication token if it exists */
_register($token);

/**
 * @function _do
 * @abstract Perform page request
 * @var $sso string SSO server argument
 * @var $uid string UUID for CSRF validation
 * @var $opt array Array of header options for page request
 * @var $referer string Specified referal string
 */
function _do($sso, $uid, $opt, $referer)
{
	$h=curl_init();
	curl_setopt($h, CURLOPT_URL, $sso);
	curl_setopt($h, CURLOPT_HEADER, false);
	curl_setopt($h, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($h, CURLOPT_REFERER, $referer);
	curl_setopt($h, CURLOPT_HTTPHEADER, $opt);
	$r=curl_exec($h);
	curl_close($h);
}

/**
 * @function _register
 * @abstract Register authentication token if present
 */
function _register($token)
{
	session_start();
	if (!empty($token)) {
		$_SESSION['token'] = $token;
	}
	session_regenerate_id(true);
	return;
}

/**
 * @function _uuid
 * @abstract Generates a random GUID
 */
function _uuid()
{
	return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff),
					mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000,
					mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff),
					mt_rand(0, 0xffff), mt_rand(0, 0xffff));
}

/**
 * @function _token
 * @abstract Retrieves client cookie and uses for X-Token header value
 */
function _token()
{
	$x = apache_request_headers();
	$token = preg_split('/token=/', $x['Cookie']);
	return $token[1];
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
}

/**
 * @function _ip
 * @abstract Attempts to determine if IP is non-routeable
 */
function _ip($ip)
{
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
?>
