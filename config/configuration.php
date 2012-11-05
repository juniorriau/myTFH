<?php
/* prevent direct access */
if (!defined('__SITE')) exit('No direct calls please...');

/* Database configuration settings */
$settings['db']['engine']     = 'mysql';
$settings['db']['hostname']   = 'localhost';
$settings['db']['username']   = 'myTFHAdmin';
$settings['db']['password']   = 'p@ssw0rd';
$settings['db']['database']   = 'myTFH';

/* Application specific settings */
$settings['opts']['title']    = 'myTFH';
$settings['opts']['timeout']  = '3600';
$settings['opts']['template'] = 'views/default';
$settings['opts']['caching']  = 'views/cache';
$settings['opts']['flogin']   = '40';

/* Session specific settings */
$settings['sessions']['timeout'] = $settings['opts']['timeout'];
$settings['sessions']['title']   = sha1($settings['opts']['title']);
$settings['sessions']['cache']   = true;
$settings['sessions']['cookie']  = true;
$settings['sessions']['secure']  = true;
$settings['sessions']['proto']   = true;

/*
 * Site wide random salt (WARNING)
 * This random value gets generated upon initial framework
 * installation. Various portions of the encryption
 * features rely on this key. If you feel this key has been
 * compromised you must use the install/repair.php utility
 * which will first decrypt each database table then generate
 * a new random site key and re-encrypt and store the
 * database contents. If you change this manually you will
 * loose everything in the database. You have been warned.
 */
$settings['sessions']['db-key']   = '4b8568b15336f64a71edaf9be264ac81d832ba40915a18aa9e7ef5283d601f18f52e9575995797b7c64c4228adb7bd7becb54cfaadb77faeab6cd110d3164eda5067530405fd5fffe302c847f4892bdb1463f55f0fe34a569f14cc6f9074a8ba59e65870f1da218eebd005e82709bf991e5b6cbccfbfb1d9e889969ab0b04a991aa58444d1c7a3156623724ff77278e18f313c026a62f66c4fabe2e83757404d1ec6a3ef66e6a8cbee1a2b8b6117719b865bc4da3087e0aba907b57e61addbcc2f84a4a1e2ec784f1f0a857b7823124fdec83c43c6616593b8eb9c5eb4c745bbf799c1731967b4c44cb01bba4db0b710a9acbc706cb6cba3576e0c619d3a4ade';
?>