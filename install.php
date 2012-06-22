<?php

/* define application path */
define('__SITE', realpath(dirname(__FILE__)));

/* execute autoload */
if (!file_exists(__SITE.'/models/model.autoloader.php')){
	exit('Error loading autoloader class, unable to proceed. 0x0c2');
}
include __SITE.'/models/model.autoloader.php';

/* load the registry class */
if (!class_exists('autoloader')){
	exit('Error initializing autoloader class, unable to proceed. 0x0c3');
}
new autoloader(__SITE.'/models/');

/* load the registry class */
if (!class_exists('registry')){
	exit('Error initializing registry class, unable to proceed. 0x0c4');
}
$registry = new registry;

/* intialize the libraries */
if (!class_exists('libraries')){
	exit('Error initializing libraries class, unable to proceed. 0x0c5');
}
$registry->libs = libraries::init();

/* initialize the filters */
if (!class_exists('validation')){
	exit('Error initializing validation libraries class, unable to proceed. 0x0c6');
}
$registry->val = validation::init();

/* load the router via the registry */
$registry->router = new router($registry);

/* route requests through controllers */
$registry->router->setPath(__SITE.'/controllers');

/* load the template via the registry */
$registry->template = new template($registry);

/* begin routing requests */
$registry->router->loader();
?>