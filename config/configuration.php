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
$settings['sessions']['secure']  = false;
$settings['sessions']['proto']   = false;

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
$settings['sessions']['db-key']   = '79ea5cee4cd3a4f6bb3642cda3d59bcf4ccf18969d2f56ed2d5182b4413e4cb9586555fa06dc30f87f13641d4a7a47ab2d736a35018e2655f1a9e23ac631404840c1369372ab41a9e697cb6e9a9a56787e82370846f84d8b313b0c2dc38d618815f6e31d5ef02ffcc059d366c6124bcdfafc7479479c8bb66720e6b09d0b3ec3078ee4d617a06b0ec498459e692c716b1db218a2218619d411abfcac2bc101369f696e44781119d64648b40b53a98e47c8dcf38bee6b2378e3218b50191edf7d548254ca8167598adbee034ad327cc51c014a4a122e362e1b55b477075127fcf2bf8fcaf8ab01782745bc2c0d474a828cb2f53f0547293996543dedf4d128927';
?>