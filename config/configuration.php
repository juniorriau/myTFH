<?php
/* prevent direct access */
if (!defined('__SITE')) exit('No direct calls please...');

/* Database configuration settings */
$settings['db']['engine']     = 'mysql';
$settings['db']['hostname']   = 'localhost';
$settings['db']['username']   = 'myTFHUser';
$settings['db']['password']   = 'p@ssw0rd';
$settings['db']['database']   = 'myTFH';

/* Application specific settings */
$settings['opts']['title']    = 'myTFH';
$settings['opts']['timeout']  = '3600';
$settings['opts']['template'] = 'views/default';
$settings['opts']['caching']  = 'views/cache';
$settings['opts']['flogin']   = '15';

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
$settings['sessions']['db-key']   = 'ce3ec4161d9b448158c9cfbd5a36d9f20dacbbf3fd38420213ef82ed131b8a6346c039f53214bf0981e6e8faf98e3aa7843a7f0594dd7e34ab5a1fa1e62ebb2549a9cdea172691f1d91a65cae1f3519b1066aec746b75ebb31e40147cc8e4b27121160433361d95aff92b82b03d5b846559497e116fc7252bdb49fba47b23b16bdafac127b772c53e121b19d633b64ca1a625e13fe5a4f7046b6683b7b905836d349a8fe4fe26bcad872da79e0c41e2a29c8d42993cb4ae29b604f8bf100348feccd3806d6e89b74eaabd165c3299bb0116d27bd571d892549e653b02232d44d67d8856fe9f75eaf33d7a158893aab2e6649ff4c082cb7df5c7ff5df597907b2';
?>