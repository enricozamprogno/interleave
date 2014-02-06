<?php
/* ********************************************************************
 * CRM-CTT Interleave 2008
 * Copyright (c) 2001-2011 info@crm-ctt.com
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This script checks the repository currently logged onto for errors and
 * inconsistencies, and optimizes all tables.
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
			$GLOBALS['PATH_TO_BASE'] = "../";
		} else {
			// in right or totally wrong path
		}
	}

	return($base_path);
}

$base_path = DetermineBasePath();

$fp = fopen($base_path . "qlist.txt","r");

$cache = 0;
$pages = 0;
$users = array();
$titles = array();
$queries = array();
$functions = array();

print " Please wait ... \n\n";
while (!feof($fp)) {
	$lnum++;
	if ($lnum == ($lastnum+100)) {
		print "\015 " . $lnum . "/" . $num;
		$lastnum = $lnum;
	}
	$line = fgets($fp,1024);
	$linarr = explode("::", $line);
	unset($functionname);
	unset($done);
	$leuk = ereg_replace("\t"," ", trim($linarr[0]));
	
	for ($x=strlen($leuk);$x!=0;$x--) {
		
		if (substr($leuk, $x, 1) == " " && !$done) {
			//print "BINGO\n";
			$functionname = substr($leuk, $x+1,strlen($leuk)-$x);
			$done = true;
		} 
			//print substr($leuk, $x, 1) . "\n";
		
	}

	$functions[$functionname]++;

	if (strstr($line,"CACHE")) {
		$cache++;
	}
	if (strstr($line,"RSS")) {
		$RSS++;
	}

	unset($lastwasnewline);
	if (strstr($line,"=============================================================================")) {
		$pages++;
		$lastwasnewline = true;
	}
	$lastline = $line;
	unset($linarr);
}
print "\n Pages      : " . number_format($pages) . "\n";
print " Cache hits : " . number_format($cache) . "\n";
print " RSS hits   : " . number_format($RSS) . "\n\n";

//sort($users);

foreach($users AS $username => $usertimes) {
	if ($usertimes>40) {
		print " " . fillout($username,30) . " - " . number_format($usertimes) . " hits\n";
	}
}
print "\n";
foreach($functions AS $functionname => $functiontimes) {
	print " " . fillout($functionname,30) . " - " . number_format($functiontimes) . " hits\n";
}
print "\n";
foreach($titles AS $title => $titletimes) {
	if ($titletimes > 4) {
		print " " . fillout($title,30) . " - " . number_format($titletimes) . " pages\n";
	}
}
print "\n";
foreach($queries AS $query => $querytimes) {
		if ($querytimes > $maxq) {
			$maxq = $querytimes;
		}
}
print "Query most tagged as slow:";
foreach($queries AS $query => $querytimes) {
	if ($querytimes > ($maxq-10)) {
		print " " . fillout($query,30) . " - " . number_format($querytimes) . " queries\n";
	}
}
print "\n Last hit   : " . $last . "\n";
print " Total lines parsed: " . $lnum . "\n";

//=============================================================================
//index.php 19-11-2005 17:05:29s (Demo (with WebDAV))
//1125003223 NOUSER :  GetClearanceLevel:: No profile override...
//1125003223 NOUSER :  GetClearanceLevel:: GetClearanceLevel 1 administrator administrator
//1125003223 hidde :  GetClearanceLevel:: hit 8 CACHE GetClearanceLevel 1  administrator
//1125003223 hidde :   hit 9 CACHE This entity is NOT locked

function fillout($var,$len) {
		while (strlen($var)<$len) {
				$var = $var . " ";
		}
		if ($var=="____0") {
			$var="_____";
		}
	return $var;
}
