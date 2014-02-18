<?php
//-----------------------------------------------
// Define root folder and load base
//-----------------------------------------------
if (!defined('MAESTRANO_ROOT')) {
  define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));
}
require MAESTRANO_ROOT . '/app/init/base.php';

//-----------------------------------------------
// Require your app specific files here
//-----------------------------------------------
define('APP_DIR', realpath(MAESTRANO_ROOT . '/../'));
chdir(APP_DIR);
//require MY_APP_DIR . '/include/some_class_file.php';
//require MY_APP_DIR . '/config/some_database_config_file.php';


//-----------------------------------------------
// Interleave init code
//-----------------------------------------------
$GLOBALS['PATHTOINTERLEAVE'] = APP_DIR . '/';

if ($_SERVER['HTTPS'] == "on") {
	$_GLOBALS['SecureCookie'] = 1;
} else {
	$_GLOBALS['SecureCookie'] = 0;
}

if (!$GLOBALS['CONFIGFILE']) {
	$GLOBALS['CONFIGFILE'] = $GLOBALS['PATHTOINTERLEAVE'] . "config/config.inc.php";
}
require_once($GLOBALS['PATHTOINTERLEAVE'] . "functions.php");
require_once($GLOBALS['CONFIGFILE']);
require_once($GLOBALS['PATHTOINTERLEAVE'] . "config/config-vars.php");


// This is done so we can use this var without htme later on. These vars should never contain characters which should be
// encoded so this is only for security (if someone starts messing around)

if (isset($_REQUEST['AjaxHandler']))	$_REQUEST['AjaxHandler'] = htme($_REQUEST['AjaxHandler']);
if (isset($_REQUEST['e']))				$_REQUEST['e']			 = htme($_REQUEST['e']);
if (isset($_REQUEST['eid']))			$_REQUEST['eid']		 = htme($_REQUEST['eid']);
if (isset($_REQUEST['id']))				$_REQUEST['id']			 = htme($_REQUEST['id']);
if (isset($_REQUEST['recordid']))		$_REQUEST['recordid']	 = htme($_REQUEST['recordid']);
if (isset($_REQUEST['templateid']))		$_REQUEST['templateid']	 = htme($_REQUEST['templateid']);
if (isset($_REQUEST['fileid']))			$_REQUEST['fileid']		 = htme($_REQUEST['fileid']);
if (isset($_REQUEST['tid']))			$_REQUEST['tid']		 = htme($_REQUEST['tid']);


// Set interleave repository	
if ($_REQUEST['repository'] == "" && $_REQUEST['repositoryToLoginTo'] == "") {
	$_REQUEST['repositoryToLoginTo'] = "0";
}

// Set a global variable called $db
if (DB_Connect($_REQUEST['repository'], false)) {
	SwitchToRepos($_REQUEST['repository']);
}

// Flag the app as initiated
$GLOBALS['INITIATED'] = true;

//-----------------------------------------------
// Perform your custom preparation code
//-----------------------------------------------
// If you define the $opts variable then it will
// automatically be passed to the MnoSsoUser object
// for construction
// e.g:
$opts = array();
global $db;
$opts['db_connection'] = $db;



