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
$settings['sessions']['db-key']   = 'c69a7dbfe0af6549fe3504169386a4686a16289f7abf66e604139adbdc6d29190e3538dffce8daaa52735fa5ae5099d412b439092eb7b6055296d93cbf2a1bb5c78927ec18300ca84d1ca0185fc14b421854c0dac78bb6a3734a1d950b97d1690f8283d0350d942438771c3b5194f814581cd8784b3b91990e982e52ab35f737d2c40026e616ec60ecbde523fb329700c1d8cf6d447f48317f8ea919de33ca7f4397f93bf7879f2e508998225b81562bcf7e9a68be69c43a3bb5874c5a0c7645615657adc03be388d1808599ab371a01f178d729e135ba1545fb38c832e7f15b0e6a20d40ec54345ae291f150eb90b265e48c0584aff906a0d15e83c557977f9';
?>