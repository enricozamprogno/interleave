<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This is the system logger plugin for Interleave
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */

//print_r($_SERVER);
$GLOBALS['PATHTOINTERLEAVE'] = str_replace("crmlogger.php", "", $_SERVER['SCRIPT_FILENAME']);

if ($GLOBALS['PATHTOINTERLEAVE'] == "") {
	$GLOBALS['PATHTOINTERLEAVE'] = getcwd();
}

if ($argv[1]) {
	$repository = $argv[1];
}
if ($argv[2]) {
	$username = $argv[2];
}
if ($argv[3]) {
	$password = $argv[3];
}
if ($argv[4]) {
	$entity_nr = $argv[4];
}
if ($argv[5]) {
	$action = $argv[5];
}
if ($argv[6]) {
	$category = $argv[6];
}
if ($repository==0) {
	// make this a string
	$repository = trim(" 0 ");
}
$reposnr_to_connect_to = $repository;

if ($argv[1]=="-help" || $argv[1]=="--help" || $argv[1]=="help" || $argv[1]=="-h" ||  $username=="" || $password=="" || $entity_nr=="" || $action=="") {
	print "\nInterleave Remote entity logger (class wrapper)\n\nUsage:\n\n";
	print "Add a new entity: (all fields are required)\n\n\tphp ./crmlogger.php [reposnr] [user] [pass] [new] [\"customer name\"] [\"category text\"]\n";
	print "\nUpdate an existing entity: (all fields are required)\n\n\tphp ./crmlogger.php [reposnr] [user] [pass] [entity nr] [action=\"arg\"]\n";
	print "\nWhere action is one of: (the quotes MUST be around your arguments as shown!)\n";
	print "\n\taddlog=\"text\"";
	print "\n\taddlogfromfile=\"/path/to/file.log\"";
	print "\n\taddfile=\"/path/to/file.doc\"";
	print "\n\tsetstatus=\"status\"";
	print "\n\tsetpriority=\"priority\"";
	print "\n\tsetowner=\"owner username\"";
	print "\n\tsetassignee=\"assignee username\"";
	print "\n\tsetduedate=\"duedate\" (syntax: DD-MM-YYYY)";
	print "\n\tsetduetime=\"duetime\" (syntax: HHMM e.g. 0930 or 1400 - only whole or half hour (00 or 30))";
	print "\n\tsetdeleted=\"y|n\" (syntax: 'y' for deleted, 'n' for not deleted)";
	print "\n\tsetprivate=\"y|n\" (syntax: 'y' for private, 'n' for not private)";
	print "\n\tsetreadonly=\"y|n\" (syntax: 'y' for readonly, 'n' for not readonly)";
	print "\n\tsetefidXX=\"value\" (update extra field XX to value \"value\")";
	print "\n\nE1: php ./crmlogger.php 0 user user_pwd new \"Cust. X\" \"Cat. Y\" (returns entity number)\n";
	print "E2: php ./crmlogger.php 0 user user_pwd 40 addlog=\"This server is not responding\" (logs text to entity 40)\n";
	print "E3: php ./crmlogger.php 0 user user_pwd 40 addfile=\"/tmp/file.doc\" (attaches file to entity 40)\n\n";
	print "When using this script from a remote location, you need all Interleave files!\n\n";

//	print_r($argv);
	exit;
}
//require_once($config);

$GLOBALS['ORIGINAL_REPOSITORY'] = $reposnr_to_connect_to;

$silent = 1;
$GLOBALS['silent'] = true;
$noneedtobeadmin = 1;
$c_l = "1";

require_once($GLOBALS['PATHTOINTERLEAVE'] . "functions.php");
require($GLOBALS['CONFIGFILE']);
require_once($GLOBALS['PATHTOINTERLEAVE'] . "config/config-vars.php");

SwitchToRepos($reposnr_to_connect_to);
InitUser();

// Check if this is done using the command line (e.g. not the web)
CheckIfShell();

if (!CommandlineLogin($username,$password,$reposnr_to_connect_to)) {
		print "Exiting...";
		exit;
} else {
	//if (GetClearanceLevel($GLOBALS['USERID'])<>"logger") {
	//	print "This is not a logging user account. Fatal, quitting.\n";
	//	exit;
	//}
	
}



// Load all required local settings
//SwitchToRepos($reposnr_to_connect_to);

$lang = do_language();

InitUser();


//print "You are user " . $GLOBALS['USERID'] . " - " . GetUserName($GLOBALS['USERID']);

require($GLOBALS['PATHTOINTERLEAVE'] . "class.interleave.php");


if ($entity_nr=="new") {
	$customer = $action;

	if ($category=="" || $customer=="") {
		print "No category/customer found. Fatal, quitting.\n";
		$fatal_error = true;
	} else {

		$sql = "SELECT id FROM " . $GLOBALS['TBL_PREFIX'] . "customer WHERE custname='" . mres($customer) . "'";
		$result = mcq($sql,$db);
		$row = mysql_fetch_array($result);
		$customer_id = $row[0];
		if ($customer_id == "") {
			print "Customer name could not be resolved. Fatal, quitting.\n";
			$fatal_error = true;
		} else {
			// DEFAULT ENTITY SETTINGS NOT HANDLED BY SCRIPT ARGUMENTS


			$Interleave = new Interleave();

			$Interleave->ConnectToRepository($GLOBALS['PATHTOINTERLEAVE'] ,$reposnr_to_connect_to,$username,$password);

			$Interleave->SetCustomer($customer_id);
			$Interleave->SetReadonly("n");
			$Interleave->SetPrivate("n");
			$Interleave->SetDuedate("");
			$Interleave->SetPriority("0 - Unknown");
			$Interleave->SetStatus("0 - Unknown");
			$Interleave->SetOwner($GLOBALS['USERID']);
			$Interleave->SetAssignee($GLOBALS['USERID']);
			$Interleave->SetContent("Added by logger " . $username . ", " . date('r') . ":\n");
			$Interleave->SetStartdate(date('d-m-Y'));
			$Interleave->SetCategory($category);
			$Interleave->SetFormID("");
			$Interleave->Execute();
//			print $Interleave->Status;
			// Update any existing fail-over databases
			SynchroniseFailOverDatabase();

			// Print resulting Entity ID
			print $Interleave->EntityID;

		} // end if customer name was resolved
	} // end if empty category or customer
} else {

	if (CheckEntityAccess($entity_nr) == "nok" || CheckEntityAccess($entity_nr) == "readonly") {
		print "The log user ('" . GetUserName($GLOBALS['USERID']) . "') has no access to this entity. CheckEntityAccess returned [" . CheckEntityAccess($entity_nr) . "]. Fatal, quitting.\n";
		print $GLOBALS['tracelog'];
		$fatal_error = 1;
	} else {

		qlog(INFO, "Logger: Now updating entity $entity_nr");

		ExpireFormCache($entity_nr);
//		print "1";
		$Interleave = new Interleave();
//		print "2" . $GLOBALS['PATHTOINTERLEAVE'];
		$Interleave->ConnectToRepository($GLOBALS['PATHTOINTERLEAVE'],$reposnr_to_connect_to,$username,$password);
//		print "3";
		$Interleave->LoadEntity($entity_nr);

		// parse actions
		$eid = $entity_nr;
		if (stristr($action,"setefid")) {
			$txt = str_replace("setefid","",$action);
			$txt = explode("=", $txt);
			$Interleave->SetExtraField($txt[0], $txt[1]);
		} elseif (stristr($action,"addlog=")) {

			$txt = str_replace("addlog=","",$action);
			$sql = "SELECT content FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE eid='" . mres($entity_nr) . "'";
			$result = mcq($sql,$db);
			$row = mysql_fetch_array($result);
			$new_content = $row['content'] . "\n" . $txt;

			$Interleave->SetContent($new_content);

			journal($entity_nr,"Contents updated (by logger)");

		} elseif (stristr($action,"addlogfromfile=")) {
			$log_from_file = str_replace("addlogfromfile=","",$action);

			$sql = "SELECT content FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE eid='" . mres($entity_nr) . "'";
			$result = mcq($sql,$db);
			$row = mysql_fetch_array($result);

			$fp = fopen($log_from_file,"r");
			$txt = fread($fp,filesize($log_from_file));
			fclose($fp);
			$new_content = $row['content'] . "\n" . $txt;

			$Interleave->SetContent($new_content);

			journal($entity_nr,"Contents updated (by logger, from file " . $log_from_file . ")");

		} elseif (stristr($action,"addfile=")) {
			$file = str_replace("addfile=","",$action);
			$fp = fopen($file,"r");
			$txt = fread($fp,filesize($file));
			fclose($fp);

			$size = filesize($file);
			if (strstr($file,"/")) {
				$file = split("/",$file);
				$x = sizeof($file)-1;
				$filename = $file[$x];
			} elseif (strstr($file,"\\")) {
				$file = split("\\",$file);
				$x = sizeof($file)-1;
				$filename = $file[$x];
			} else {
				//print "file is: " . $file;
				$filename = $file;
			}

			$Interleave->AttachFile($filename,$txt);

			//journal($entity_nr,"File " . $filename . " added (by logger)");

		} elseif (stristr($action,"setstatus=")) {
			$to_status = str_replace("setstatus=","",$action);
			$Interleave->SetStatus($to_status);
		} elseif (stristr($action,"setpriority=")) {
			$to_priority = str_replace("setpriority=","",$action);
			$Interleave->SetPriority($to_priority);
		} elseif (stristr($action,"setowner=")) {
			$to_owner = str_replace("setowner=","",$action);
			$id = GetUserID($to_owner);
			if (!$id) {
				print "Target user '" . $to_owner . "' could not be resolved. Fatal, quitting.\n";
				$fatal_error = 1;
			} else {
				$Interleave->SetOwner($id);
			}
		} elseif (stristr($action,"setassignee=")) {
			$to_assignee = str_replace("setassignee=","",$action);
			$id = GetUserID($to_assignee);
			if (!$id) {
				print "Target user '" . $to_assignee . "' could not be resolved. Fatal, quitting.\n";
				$fatal_error = 1;
			} else {
				$Interleave->SetAssignee($id);
			}
		} elseif (stristr($action,"setduedate=")) {
			$to_duedate = str_replace("setduedate=","",$action);
			$Interleave->SetDuedate($to_duedate);
			journal($entity_nr,"Duedate set to " . $to_duedate . " (by logger)");

		} elseif (stristr($action,"setduetime=")) {
			$to_duetime = str_replace("setduetime=","",$action);
			$Interleave->SetDuetime($to_duetime);
			journal($entity_nr,"Duetime set to " . $to_duetime . " (by logger)");
		}  elseif (stristr($action,"setdeleted=")) {
			$to_deleted = str_replace("setdeleted=","",$action);
			if ($to_deleted <> "y" && $to_deleted <> "n") {
				print "You passed an invalid value. Fatal, quitting.\n";
				$fatal_error = 1;
			} else {
				$Interleave->SetDeleted($to_deleted);
				journal($entity_nr,"Deleted set to " . $to_deleted . " (by logger)");
			}
		} elseif (stristr($action,"setprivate=")) {
			$setprivate = str_replace("setprivate=","",$action);
			if ($setprivate <> "y" && $setprivate <> "n") {
				print "You passed an invalid value. Fatal, quitting.\n";
				$fatal_error = 1;
			} else {
				$Interleave->SetPrivate($setprivate);
				journal($entity_nr,"Private set to " . $setprivate . " (by logger)");
			}
		} elseif (stristr($action,"setreadonly=")) {
			$setreadonly = str_replace("setreadonly=","",$action);
			if ($setreadonly <> "y" && $setreadonly <> "n") {
				print "You passed an invalid value. Fatal, quitting.\n";
				$fatal_error = 1;
			} else {

				$Interleave->SetReadonly($to_deleted);

				journal($entity_nr,"Readonly set to " . $setreadonly . " (by logger)");
			}
		SynchroniseFailOverDatabase();

		$Interleave->Execute();


		} // end if owner
	}

}
function DetermineBasePath() {
	$curpath = str_replace("\\","/",getcwd());
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


?>