<?php
/* ********************************************************************
 * CRM-CTT Interleave
 * Copyright (c) 2001-2011 Hidde Fennema (info@crm-ctt.com)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file can be run from both the command line and via web
 * It indexes all entities and saves the result in the searchindex table.
 *
 * Argument: -t  :: Truncate searchindex table before re-indexing
 *
 * When run on the commandline the index will ALWAYS be created, when
 * run through web it will only work when ENABLEINDEXEDSEARCHING has 'Yes'
 * as value.
 *
 * Check http://www.crm-ctt.com/ for more information
 **********************************************************************
 */
// For safety
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
unset($res);
if ($argv[1] == "-t") {
		$GLOBALS['truncate'] = true;
		print "\n * Table truncating enabled * \n\n";
	}
if ($_REQUEST['web']) {
	require_once($base_path . "header.inc.php");
	$res = true;
	SafeModeInterruptCheck();
} else {
	require_once($base_path . "config/config.inc.php");
    require_once($base_path . "config/config-vars.php");
	require_once($base_path . "functions.php");
	$res = CommandLineLogin("", "", "");
	$GLOBALS['ENABLEINDEXEDSEARCHING'] = "Yes";
}
if (!$res) {
    print "Bye bye ...\n\n";
} else {
	UpdateSearchIndexTable();
}
if ($_REQUEST['web']) {
	EndHTML();
}
?>