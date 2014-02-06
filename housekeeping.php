<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 info@interleave.nl
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * THIS FILE MUST BE CALLED EVERY MINUTE!
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
//$GLOBALS['logtext'] = true;
$c_l = "1";

$GLOBALS['CONFIGFILE'] = $GLOBALS['PATHTOINTERLEAVE'] . "config/config.inc.php";
if (!is_array($argv)) $argv = array();
foreach ($argv AS $cmdlineargument) {
	if (substr($cmdlineargument,0,4) == "cfg=") {
			$cmdlineargument = str_replace("cfg=", "" , $cmdlineargument);
			if (is_file($cmdlineargument)) {
				$GLOBALS['CONFIGFILE'] = $cmdlineargument;
				print date('r') . ": Using config file " . $GLOBALS['CONFIGFILE'] . "\n";
				continue;
			} else {
				die("Config file declaration is not correct. Fatal.");
			}
	} 
}


include($GLOBALS['CONFIGFILE']);

require_once($GLOBALS['PATHTOINTERLEAVE'] . "config/config-vars.php");
require_once($GLOBALS['PATHTOINTERLEAVE'] . "functions.php");
require_once($GLOBALS['PATHTOINTERLEAVE'] . "lib/class.phpmailer.php");


include($GLOBALS['CONFIGFILE']);

//$tmp = $GLOBALS['logtext'];
$GLOBALS['logtext'] = false;

$GLOBALS['CRON_RUNNING'] = true;

if ($_REQUEST['password']) {


} else {
		$cl = true;

		if ($argv[1]=="-help" || $argv[1]=="--help" || $argv[1]=="help" || $argv[1]=="-h") {
			print "\nUsage:\n";
			print "\t[reposnr] [cronpassword]\n";
			print "\nExample: php housekeeping.php 1 cron_password cfg=/path/to/config.inc.php\n\n";
			exit;
		}	
		if ($argv[1]) {
			$_REQUEST['reposnr'] = $argv[1];

		}

		if ($argv[2]) {
			$_REQUEST['password'] = $argv[2];
		} else {
			print date('r') . ": \nUsage:\n";
			print date('r') . ": \t[reposnr] [cronpassword]\n";
			print date('r') . ": \nExample: php housekeeping.php 1 cron_password\n\n";
			exit;
		}
}

if ($_REQUEST['reposnr'] == "" || !is_numeric($_REQUEST['reposnr'])) {
	$_REQUEST['reposnr'] = "0";
}

$working_repository = $_REQUEST['reposnr'];

SwitchToRepos($_REQUEST['reposnr']);



if ($_REQUEST['password']<>$GLOBALS['cronpassword']) {
	$output .= "Incorrect user authentication string for this repository (" . $GLOBALS['title'] . "). Quitting.\n\n";
	log_msg("ERROR: Housekeeping was called with incorrect password. No notifications were submitted.");
	print("ERROR: Housekeeping was called with incorrect password. No notifications were submitted.");
	if (!$cl) print date('r') . ": </pre>";
	exit;
}
if (!$_REQUEST['reposnr']) {
	$output .= "WARNING!\n\nNo repository number submitted (or the value = 0, which doesn't get passed to the webserver). Assuming repos# 0!\n\n";
	$reposnr=0;
}


print date('r') . ": Working on " . $GLOBALS['title'] . " - " . $GLOBALS['CRM_VERSION'] . "\n";

// Switch off any synchronization for now
$GLOBALS['USE_FAILOVER'] = "No";

//$GLOBALS['logtext'] = $tmp;
qlog(INFO, "Disabling QLOG - Housekeeping running for " . $GLOBALS['title']);
$GLOBALS['logtext'] = false;


do_language();

print date('r') . ": Housekeeping running ... \n";

//$syncstat = GetSetting("SYNC_DISABLED_UNTIL");


// Delete values with empty arrays from attributes
mcq("DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "attributes WHERE value='a:0:{}'", $db);

// Some temporary files don't get processed and they stay behind, there's nothing to do about that.
print date('r') . ": Deleting expired temporary files...\n";
DeleteExpiredTempFiles();

print date('r') . ": Deleting expired sessions...\n";
DeleteExpiredSessions();

print date('r') . ": Deleting expired entity locks ...\n";
RemoveExpiredLocks();

print date('r') . ": Deleting expired HTML cache ...\n";
DeleteExpiredTemplateCache();

print date('r') . ": Deleting expired access cache ...\n";
DeleteExpiredAccessCache();


if (GetSetting("UseMailQueue") == "Yes") {
	print date('r') . ": Processing mail queue...\n";
	ProcessMailQueue();
}

// Switch on synchronization (if appliccable)
$GLOBALS['USE_FAILOVER'] = "Yes";

print date('r') . ": Running minute-triggers....\n";
ProcessTriggers("Every minute");

print date('r') . ": Processing to-do list ...\n";
ProcessOldTodos();

print date('r') . ": Starting trigger escalation works for repository " . $GLOBALS['title'] . "\n";


$triggers = db_GetArray("SELECT DISTINCT(onchange) FROM " . $GLOBALS['TBL_PREFIX'] . "triggers WHERE enabled='yes' AND SUBSTR(onchange,1,9)='EntityAge' ORDER BY processorder");

$eid_processed = array();

$t = 0;



foreach ($triggers AS $trigger) {
	print date('r') . ": Evaluating " . $trigger['onchange'] . "\n";
	$sec = str_replace("EntityAge", "", $trigger['onchange']);
	$now = date('U');
	if ($GLOBALS['ALSO_PROCESS_DELETED'] == "Yes") {
			$ent = db_GetFlatArray("SELECT DISTINCT(eid) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE (openepoch+" . $sec . ")<" . $now . "");
	} else {
    	    $ent = db_GetFlatArray("SELECT DISTINCT(eid) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE (openepoch+" . $sec . ")<" . $now . " AND deleted<>'y'");
	}

	ob_flush();

	foreach ($ent AS $eid) {
		if (!in_array($eid . "_" . $trigger['tid'], $eid_processed)) {
			ProcessTriggers($trigger['onchange'], $eid, "Miscellaneous trigger", false, false);
			$t++;
			array_push($eid_processed, $eid . "_" . $trigger['onchange']);
		}
	}

		
}

print date('r') . ": $t triggers\n";



print date('r') . ": Sync db's...\n";
UpdateSetting("SYNC_DISABLED_UNTIL", "");
UpdateSetting("TimestampLastHousekeeping", date('U'));
SyncDBs($_REQUEST['reposnr']);
//SyncDbsIncremental($working_repository, false, false, true);
print date('r') . ": Done\n";


?>