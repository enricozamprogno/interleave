<?php

if (!$GLOBALS['CONFIGFILE']) {
	$GLOBALS['CONFIGFILE'] = $GLOBALS['PATHTOINTERLEAVE'] . "config/config.inc.php";
}

function DetermineBasePath() {
	$curpath = str_replace("\\","/",$_SERVER['PWD']);
	$dirs = explode(" ", "webdav_fs jp fckeditor js lib images command-line-utils config docs_examples openid2 css");
	foreach ($dirs AS $dir) {
		if (substr($curpath, strlen($curpath) - strlen($dir), strlen($dir)) == $dir) {
			$GLOBALS['PATHTOINTERLEAVE'] = "../";
		} else {
			// in right or totally wrong path
		}
	}
	return($GLOBALS['PATHTOINTERLEAVE']);
}

if (!$GLOBALS['PATHTOINTERLEAVE']) {
	$GLOBALS['PATHTOINTERLEAVE'] = DetermineBasePath();
}

$GLOBALS['CONFIGFILE'] = $GLOBALS['PATHTOINTERLEAVE'] . "config/config.inc.php";
foreach ($argv AS $cmdlineargument) {
	if (substr($cmdlineargument,0,4) == "cfg=") {
			$cmdlineargument = str_replace("cfg=", "" , $cmdlineargument);
			if (is_file($cmdlineargument)) {
				$GLOBALS['CONFIGFILE'] = $cmdlineargument;
				print "Using config file " . $GLOBALS['CONFIGFILE'] . "\n";
				continue;
			} else {
				die("Config file declaration is not correct. Fatal.");
			}
	} 
}

include($GLOBALS['CONFIGFILE']);


require_once($GLOBALS['PATHTOINTERLEAVE'] . "lib/class.phpmailer.php");
require_once($GLOBALS['PATHTOINTERLEAVE'] . "functions.php");

print "\nInterleave File import function\n\n";

if (CommandlineLogin("","","")) {

	print "OK, so you want to bulk upload files into CRM...\n";
	print "To which entity number do you want to attach the files?\n";
	print " EID > ";
	$eid = readln();

	$eid = $eid * 1;

	$sql = "SELECT category FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE eid='" . mres($eid) . "' AND deleted<>'y'";
	$result = mcq($sql,$db);
	$row = mysql_fetch_array($result);
	if ($row[category]) {
		print "Adding files to \"" . $row[category] . "\"\n";
	} else {
		print "This entity number is not valid (non-existed, empty category, or deleted)\nBye!\n";
		exit;
	}


	print "Enter the directory in which the files are located:\n";
	print " DIR > ";
	$dir = readln();

	if (substr($dir,(strlen($dir)-1),1) <> "/") {
		$dir .= "/";
	}

	$files = array();
	$names = array();

	if ($handle = @opendir($dir)) {
	   while (false !== ($file = readdir($handle))) {
		   if ($file != "." && $file != "..") {
				array_push($files, $dir . "/" . $file);
				array_push($names, $file);
		   }
	   }
	   closedir($handle);
	} else {
		print "Fatal - directory not found!\n";
		exit;
	}

	print "\n";

	print "Type 'yes' if you really want to upload " . sizeof($files) . " files:\n";
	print " CONFIRM > ";
	$confirm = readln();
	if ($confirm<>"yes") {
		print "Ok, bye!\n";
		exit;
	}

	for ($x=0;$x<sizeof($files);$x++) {
			$ft = TryToFigureOutFileType($files[$x]);
			$fp=fopen($files[$x] ,"rb");
			$fs = filesize($files[$x]);
			$filecontent=fread($fp,$fs);
			fclose($fp);
			$filecontenttomail = $filecontent;
			$filenametomail = $names[$x];
			$attachment = AttachFile($eid,$names[$x],$filecontent,"entity",$ft);
			print "Processing: $x $files[$x] type: $ft\n";
			if ($ft == "image/jpeg" || $ft == "image/gif") {
				GenerateImageThumbnails("",true,$attachment);
			}
			$t++;
	}

	print "$t files attached to EID $eid\n";

	print "\nBye!\n\n";
} else {
	// access denied
	print "\nExiting...\n\n";
}



?>
