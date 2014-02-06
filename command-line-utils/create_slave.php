<?php
/* ********************************************************************
 * CRM-CTT Interleave
 * Copyright (c) 2001-2011 Hidde Fennema (info@crm-ctt.com)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is the core of Interleave. It is allways needed. It contains only functions.
 *
 * Check http://www.crm-ctt.com/ for more information
 **********************************************************************
 */
function DetermineBasePath() {
	$curpath = str_replace("\\","/",getcwd());
	$dirs = explode(" ", "webdav_fs jp fckeditor js lib images command-line-utils config docs_examples openid2 css");
	foreach ($dirs AS $dir) {
		if (substr($curpath, strlen($curpath) - strlen($dir), strlen($dir)) == $dir) {
			$base_path = "../";
		} else {
			// in right or totally wrong path
		}
	}
	return($base_path);
}

$base_path = DetermineBasePath();

if ($_SERVER['REQUEST_URI']) {
	require_once($base_path . "header.inc.php");
} else {
	require_once($base_path . "config/config.inc.php");
	require_once($base_path . "functions.php");
	require_once($base_path . "config/config-vars.php");
	if (!CommandlineLogin($username,$password,$repository)) {
		print "Exiting...";
		exit;
	}
	print "This module will drop (DELETE!) all content of the slave database (" . $database[$GLOBALS['repository_nr']] . "@" . $slave[$GLOBALS['repository_nr']] ."). After that, it will\ncopy all data and tables from the production server to the slave server.";
	print "\n\n";
	print "Are you sure you want to copy all master tables and data from the master database to the slave database? (type 'yes' to continue)\n";
	print "Interleave > ";
	$response = readln();
	if ($response <> "yes") {
		print "OK! Bye!";
		EndHTML(false);
	}

	$cmdline = true;
}
RepairFailOverdatabase($cmdline);
if ($cmdline) {
	print "\n\nDone. I think.\n";
	EndHTML(false);
} else {
	print "Done.";
	EndHTML();
}
?>