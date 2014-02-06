<?php
/* ********************************************************************
 * CRM-CTT Interleave
 * Copyright (c) 2001-2011 Hidde Fennema (info@crm-ctt.com)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file does several things :)
 *
 * Check http://www.crm-ctt.com/ for more information
 **********************************************************************
 */
// Set error reporting level
error_reporting(E_ERROR);
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
require_once($base_path . "config/config.inc.php");
require_once($base_path . "pdf_inc2.php");
require_once($base_path . "functions.php");
// Check if this is done using the command line (e.g. not the web)
CheckIfShell();
print "Interleave PDF Summary (batch)\n\n";
if ($argv[1]=="-help" || $argv[1]=="--help" || $argv[1]=="help" || $argv[1]=="-h") {
	print "\nUsage:\n";
	print "\t[no arguments]\t:Interactive\n";
	print "or:\n";
	print "\t[reposnr] [user] [pass] [images - Y or N] [path (default current dir)\n";
	print "\nExample: php -q generate_total_pdf_summary.php 0 admin admin_pwd Y ./\n\n";
	exit;
}
if ($argv[1]) {
	$repository = $argv[1];
}
if ($repository == "" || !is_numeric($repository)) {
        $repository = "0";
}
if ($argv[2]) {
	$username = $argv[2];
}
if ($argv[3]) {
	$password = $argv[3];
}
if ($argv[4]) {
	$images = $argv[4];
}
if ($argv[5]) {
	$path = $argv[5];
	$auto=1;
}
if (!CommandlineLogin($username,$password,$repository)) {
		print "Exiting...";
		exit;
} else {
	require_once($base_path . "language.php");
}
if (!$path) {
	print "Where do you want the report to be placed? [./]: ";
	$path = readln();
}
if ($path=="") {
	$path = "./";
} else {
	$path .= "/";
}
if (!$argv[1]) {
    $filename = $path . "Interleave-generated-report-" . date('U') . "-repos-" . $GLOBALS['REPOSNR'] . ".pdf";
	} else {
    $filename = $path . "Interleave_generated-report-batch-repos-" . $GLOBALS['REPOSNR'] . ".pdf";
}

$pdfa = array();
$sql = "select eid from " . $GLOBALS['TBL_PREFIX'] . "entity";
$result = mcq($sql,$db);
while ($row = mysql_fetch_array($result)) {
	array_push($pdfa,$row['eid']);
}
if (!$images) {
	print "Do you want activity graphs in your report? [Y|n]: ";
	$images = readln();
}
if (strtoupper($images)=="N") {
	$NoImageInclude=1;
	print "Excluding images\n";
} else {
	print "Including images\n";
}

if (!$auto) {
	print "Ready to create a report of " . sizeof($pdfa) . " entities and save it as $filename.\n\n";
	print "Press enter to continue or CTRL-C to abort: ";
	$dummy = readln();
}
print "\n\n";
$date = date("F j, Y, H:i") . "h";
$st = date('U');
StartPDF();
$shell_status="1";
toc($pdfa);
$filename_to_disk = "CRMTMPIMGFILE";
//$NoImageInclude=1;
$in_line="1";
CreatePDF($pdfa,$filename);
$et = date('U');
print "\n Report created in " . ($et - $st) . " seconds. Saved in $filename. Quitting.\n\n";
?>