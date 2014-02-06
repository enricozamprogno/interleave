<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This is the duedate notifier
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */

$c_l = "1";

$outp = "";

$GLOBALS['CONFIGFILE'] = $GLOBALS['PATHTOINTERLEAVE'] . "config/config.inc.php";
if (is_array($argv)) {
	foreach ($argv AS $cmdlineargument) {
		if (substr($cmdlineargument,0,4) == "cfg=") {
				$cmdlineargument = str_replace("cfg=", "" , $cmdlineargument);
				if (is_file($cmdlineargument)) {
					$GLOBALS['CONFIGFILE'] = $cmdlineargument;
					//$GLOBALS['PATHTOINTERLEAVE'] = str_replace("config/config.inc.php", "", $cmdlineargument);
					//$GLOBALS['PATHTOINTERLEAVE'] = str_replace('config\config.inc.php', "", $GLOBALS['PATHTOINTERLEAVE']);
					print "Using config file " . $GLOBALS['CONFIGFILE'] . "\n";
					continue;
				} else {
					die("Config file declaration is not correct. Fatal.");
				}
		} 
		if ($cmdlineargument == "--skip-recalc") {
			$skip_recalc = true;
		}
	}
}
include($GLOBALS['CONFIGFILE']);
require_once($GLOBALS['PATHTOINTERLEAVE'] . "functions.php");
require_once($GLOBALS['PATHTOINTERLEAVE'] . "lib/class.phpmailer.php");

$GLOBALS['IN_SYNC_FUNC'] = "1"; // This effectively disables fail-over query sync

if ($_REQUEST['password']) {


} else {
		$cl = true;

		if ($argv[1] && $argv[1] != "-help" && $argv[1] != "--help" && $argv[1] != "help" && $argv[1] != "-h") {
			$_REQUEST['reposnr'] = $argv[1];
		}

		if ($argv[2] && $argv[1] != "-help" && $argv[1] != "--help" && $argv[1] != "help" && $argv[1] != "-h") {
			$_REQUEST['password'] = $argv[2];
		}
		if (!$_REQUEST['password'] || (!is_numeric($_REQUEST['reposnr']) && $_REQUEST['reposnr'] != 0)) {
			print "\nUsage:\n";
			print "\t[reposnr] [cron-password] {cfg=/path/to/config.inc.php}\n";
			print "\nExample 1: (suitable for a basic Interleave installation)\n\n";
			print "\t/usr/bin/php /var/www/interleave/duedate-notify-cron.php 0 cron-password\n";
			print "\nExample 2:\n\n";
			print "\t/usr/bin/php /var/www/interleave/duedate-notify-cron.php 3 cron-password cfg=/opt/interleave_config/config.inc.php\n";
			print "\n";
			print "The cron-password can be set in the application (global system settings), setting CRONPASSWORD.\n\n";
			exit;
		}
}

ob_start("due"); 

if (!is_numeric($_REQUEST['reposnr'])) $_REQUEST['reposnr'] = "0";

SwitchToRepos($_REQUEST['reposnr']);

UpdateSetting("TimestampLastDuedateCron", date('U'));

$GLOBALS['ORIGINAL_REPOSITORY'] = $_REQUEST['reposnr'];

do_language();

// Check if we're ran via web or using the command line
if (!$cl) {
	// This is the web, issue a warning, we don't like this anymore
	log_msg("WARNING:: duedate-notify-cron.php was invoked using a web GET. This is deprecated, use the command line (CLI) instead.");
	print "<h4>WARNING: duedate-notify-cron.php was invoked using a web GET. This is deprecated, use the command line (CLI) instead.</h4>";
	print "<h4><pre>Syntax: /usb/bin/php /path/to/interleave/duedate-notify-cron.php [reposnr] [password] {cfg=/path/to/configfile.to.use}</pre></h4>";
	print "<pre>";
	
} 

print date('r') . ": Starting maintenance for repository " . $GLOBALS['title'] . "\n";



if ($_REQUEST['password']<>$GLOBALS['cronpassword']) {
	$output .= "Incorrect user authentication string for this repository (" . $GLOBALS['title'] . "). Quitting.\n\n";
	log_msg("ERROR: Duedate-notify-cron.php was called with incorrect password. No notifications were submitted.");
	if (!$cl) print date('r') . ": " .  "</pre>";
	exit;
}
if (!$_REQUEST['reposnr']) {
	$output .= "WARNING!\n\nNo repository number submitted (or the value = 0, which doesn't get passed to the webserver). Assuming repos# 0!\n\n";
	$reposnr=0;
}
$webhost = getenv("HOSTNAME");

// Determine user under which to do this (must be an administator)
$tmp = db_GetRow("SELECT id FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE administrator='yes' AND name NOT LIKE 'deleted_user%'");
$GLOBALS['USERID'] = $tmp['id'];
print date('r') . ": Sudo to admin account " . $GLOBALS['USERID'] . " (" . GetUserName($GLOBALS['USERID']) . ")\n";


print date('r') . ": " .  "Creating thumbnails for newly added image files ...\n";
GenerateImageThumbnails("all", false, false);

// This sets all sqldates according to the due-date. This is done because somehow these two don't represent the same value.
print date('r') . ": " .  "Maintenance on duedate vs. sqldate ...\n";
mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET timestamp_last_change=timestamp_last_change,sqldate = CONCAT(SUBSTR(duedate,7,4), '-', SUBSTR(duedate,4,2), '-', SUBSTR(duedate, 1,2)) WHERE sqldate <> CONCAT(SUBSTR(duedate,7,4), '-', SUBSTR(duedate,4,2), '-', SUBSTR(duedate, 1,2))", $db);


$GLOBALS['CRON_RUNNING'] = true;
$GLOBALS['IN_SYNC_FUNC'] = true;

if (!$skip_recalc) {
	print date('r') . ": " .  "Recalculating computed fields... \n";
	CalculateAllComputedExtraFields(false, true);
	mcq("TRUNCATE TABLE " . $GLOBALS['TBL_PREFIX'] . "failoverquerystore", $db);
}
print date('r') . ": Check for database alterations to be performed...\n";
IntermediateDatabaseUpgrade();

print date('r') . ": " .  "Synchronising fail-over database...\n";
SynchroniseAllFailOverDatabases($_REQUEST['reposnr']);
//print date('r') . ": " 

print date('r') . ": " .  "Checking duedates and extra field dates...\n";

// Check for duedates to trigger in the entity table:
// >X - Initialize this variable so the display looks good when 0 triggers fired.
$trgd = 0;
$sqldate = date('Y-m-d');
qlog(INFO, " Due date is . $sqldate");
$sql = "SELECT eid FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE sqldate='" . $sqldate . "'";
$result= mcq($sql,$db);
while ($row = mysql_fetch_array($result)) {
	unset ($GLOBALS['email_send_to']);
	ProcessTriggers("duedate_reached",$row['eid'],"Miscellaneous trigger");
	qlog(INFO, "Enabling due-date trigger for entity " . $row['eid']);
	$trgd++;
}
// PAST / EXPIRED
$sql = "SELECT eid FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE STR_TO_DATE(duedate,'%d-%m-%Y')<'" . date('Y-m-d') . "'";
$result= mcq($sql,$db);
while ($row = mysql_fetch_array($result)) {
	unset ($GLOBALS['email_send_to']);
	ProcessTriggers("duedate_expired",$row['eid'],"Miscellaneous trigger");
	qlog(INFO, "Enabling due-date trigger for entity " . $row['eid']);
	$trgd++;
}
// FUTURE
$sql = "SELECT eid FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE STR_TO_DATE(duedate,'%d-%m-%Y')>'" . date('Y-m-d') . "'";
$result= mcq($sql,$db);
while ($row = mysql_fetch_array($result)) {
	unset ($GLOBALS['email_send_to']);
	ProcessTriggers("duedate_infuture",$row['eid'],"Miscellaneous trigger");
	qlog(INFO, "Enabling due-date trigger for entity " . $row['eid']);
	$trgd++;
}

// STARTDATE
$sql = "SELECT eid FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE sqlstartdate='" . $sqldate . "'";
$result= mcq($sql,$db);
while ($row = mysql_fetch_array($result)) {
	unset ($GLOBALS['email_send_to']);
	ProcessTriggers("startdate_reached",$row['eid'],"Miscellaneous trigger");
	qlog(INFO, "Enabling start-date trigger for entity " . $row['eid']);
	$trgd++;
}

$now = date('U');

if (!is_array($GLOBALS['TriggerDays'])) {
	$GLOBALS['TriggerDays'] = explode(",", $GLOBALS['TriggerDays']);
}
foreach ($GLOBALS['TriggerDays'] AS $dfm) {
	if ($dfm > 0) {
		$now = date('U');
		$then = $now + ($dfm * 86400);
		$sqldate = date('Y-m-d', $then);
		qlog(INFO, " Due date is . $sqldate");
		$sql = "SELECT eid FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE sqldate='" . $sqldate . "'";
		$result= mcq($sql,$db);
		while ($row = mysql_fetch_array($result)) {
			unset ($GLOBALS['email_send_to']);
			ProcessTriggers("DuedateReached_minus_" . $dfm . "_days",$row['eid'],"Miscellaneous trigger");
			qlog(INFO, "Enabling due-date trigger for entity " . $row['eid']);
			$trgd++;
			//print date('r') . ": " . ("Enabling due-date trigger for entity $dfm " . $row['eid'] . "\n");
		}
	}
}
foreach ($GLOBALS['TriggerDays'] AS $dfm) {
	if ($dfm < 0) {
		$dfm = $dfm * -1;
		$now = date('U');
		$then = $now - ($dfm * 86400);
		$sqldate = date('Y-m-d', $then);
		qlog(INFO, " Due date is . $sqldate");
		$sql = "SELECT eid FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE DATE(timestamp_last_change)='" . $sqldate . "'";
		
		$result= mcq($sql,$db);
		while ($row = mysql_fetch_array($result)) {
			unset ($GLOBALS['email_send_to']);
			ProcessTriggers("LastUpdate_" . $dfm . "_days_ago",$row['eid'],"Miscellaneous trigger");
			qlog(INFO, "Enabling due-date trigger for entity " . $row['eid']);
			$trgd++;
			//print date('r') . ": " . ("Enabling due-date trigger for entity $dfm " . $row['eid'] . "\n");
		}
	}
}

$onchange = db_GetFlatArray("SELECT onchange FROM " . $GLOBALS['TBL_PREFIX'] . "triggers");

foreach (db_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE deleted!='y'") AS $field) {

	$add_sql = "";
	if ($field['fieldtype'] == "date"  || $field['fieldtype'] == "date/time" || GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Date") {
		$type = GetExtraFieldTableType($field['id']);
		if ($type == "entity") {
			$table = "entity";
			$id = "eid AS RecordToTrigger";
			$fid = false;
			if ($GLOBALS['ALSO_PROCESS_DELETED'] != "Yes") {
				$add_sql = " AND deleted != 'y'";
			}
		} elseif ($type == "customer") {
			$table = "customer";
			$id = "id AS RecordToTrigger";
			$fid = false;
		} else {
			$table = "flextable" . $type;
			$id = "recordid AS RecordToTrigger";
			$add_sql = " AND deleted != 'y'";
			$fid = $type;
		}

		if (in_array("DATE_EFID" . $field['id'] . "_expired", $onchange)) {
			// PAST / EXPIRED
			
			if ($field['fieldtype'] == "date/time") {
				$select = "EFID" . $field['id'] . "<'" . date('Y-m-d') . "'";
			} else {
				$select = "STR_TO_DATE(EFID" . $field['id'] . ",'%d-%m-%Y')<'" . date('Y-m-d') . "'";
			}

			$sql = "SELECT " . $id . " FROM " . $GLOBALS['TBL_PREFIX'] . $table . " WHERE " . $select . " AND EFID" . $field['id'] . "!='' " . $add_sql;
			$result= mcq($sql,$db);
			while ($row = mysql_fetch_array($result)) {
				unset ($GLOBALS['email_send_to']);
				ProcessTriggers("DATE_EFID" . $field['id'] . "_expired",$row['RecordToTrigger'],"Miscellaneous trigger", false, $fid);
				$trgd++;
				//print date('r') . ": " . ("Enabling date-has-past trigger DATE_EFID" . $field['id'] . "_expired for $type " . $row['RecordToTrigger'] . "\n");
			}
		}
		

		if (in_array("DATE_EFID" . $field['id'] . "_future", $onchange)) {
			// FUTURE
			if ($field['fieldtype'] == "date/time") {
				$select = "EFID" . $field['id'] . ">'" . date('Y-m-d') . "'";
			} else {
				$select = "STR_TO_DATE(EFID" . $field['id'] . ",'%d-%m-%Y')>'" . date('Y-m-d') . "'";
			}
			$sql = "SELECT " . $id . " FROM " . $GLOBALS['TBL_PREFIX'] . $table . " WHERE " . $select . " AND EFID" . $field['id'] . "!='' " . $add_sql;
			$result= mcq($sql,$db);
			while ($row = mysql_fetch_array($result)) {
				unset ($GLOBALS['email_send_to']);
				ProcessTriggers("DATE_EFID" . $field['id'] . "_infuture", $row['RecordToTrigger'],"Miscellaneous trigger", false, $fid);
				//print date('r') . ": " . ("Enabling date-has-not-past trigger for $type " . $row['RecordToTrigger'] . "\n");
				$trgd++;
			}
		}
	}
}

foreach ($GLOBALS['TriggerDays'] AS $dfm) {
	$then = $now + ($dfm * 86400);


	$fields = GetExtraFields(false, false, true, true);
	foreach ($fields AS $field) {
		if ($field['fieldtype'] == "date" || $field['fieldtype'] == "date/time" || GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Date") {
			if ($field['fieldtype'] == "date/time") {
				$efdate = date('Y-m-d', $then);	
				$select = " DATE(EFID" . $field['id'] . ")='" . $efdate . "'";
			} else {
				$efdate = date('d-m-Y', $then);	
				$select = " EFID" . $field['id'] . "='" . $efdate . "'";
			}

			// Example: DATE_EFID204_1_days
			$type = GetExtraFieldTableType($field['id']);
			if ($type == "entity") {
				if ($GLOBALS['ALSO_PROCESS_DELETED'] != "Yes") {
					$add_sql = " AND deleted != 'y'";
				}
				$sql = "SELECT eid FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE " . $select . " " . $add_sql;
			} elseif ($type == "customer") {
				$sql = "SELECT id AS eid FROM " . $GLOBALS['TBL_PREFIX'] . "customer WHERE " . $select;
			} else {
				$sql = "SELECT recordid AS eid FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $type . " WHERE " . $select . " AND deleted='n'";
			}

			$result= mcq($sql,$db);
			while ($row = mysql_fetch_array($result)) {
				unset ($GLOBALS['email_send_to']);
				ProcessTriggers("DATE_EFID" . $field['id'] . "_" . $dfm . "_days",$row['eid'],"Miscellaneous trigger");
				qlog(INFO, "Calling EF date trigger for $type " . $row['eid']);
				//print date('r') . ": " . ("- Calling EF date trigger for entity " . $row['eid'] . " DATE_EFID" . $field['id'] . "_" . $dfm . "_days" . "\n");
				$trgd++;
			}


		}
	}
	$fields = GetExtraCustomerFields();
	foreach ($fields AS $field) {
		if ($field['fieldtype'] == "date" || $field['fieldtype'] == "date/time" || GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Date") {
			if ($field['fieldtype'] == "date/time") {
				$efdate = date('Y-m-d', $then);	
				$select = " DATE(EFID" . $field['id'] . ")='" . $efdate . "'";
			} else {
				$efdate = date('d-m-Y', $then);	
				$select = " EFID" . $field['id'] . "='" . $efdate . "'";
			}

			// Example: DATE_EFID204_1_days
			$type = GetExtraFieldTableType($field['id']);
			if ($type == "entity") {
				if ($GLOBALS['ALSO_PROCESS_DELETED'] != "Yes") {
					$add_sql = " AND deleted != 'y'";
				}
				$sql = "SELECT eid FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE " . $select . " " . $add_sql;
			} elseif ($type == "customer") {
				$sql = "SELECT id AS eid FROM " . $GLOBALS['TBL_PREFIX'] . "customer WHERE " . $select;
			} else {
				$sql = "SELECT recordid AS eid FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $type . " WHERE " . $select . " AND deleted='n'";
			}

			//print date('r') . ": " . $sql . " -- DFM $dfm\n";

			$result= mcq($sql,$db);
			while ($row = mysql_fetch_array($result)) {
				unset ($GLOBALS['email_send_to']);
				ProcessTriggers("DATE_EFID" . $field['id'] . "_" . $dfm . "_days",$row['eid'],"Miscellaneous trigger",false,false,true);
				qlog(INFO, "Calling EF date trigger for $type " . $row['eid']);
				print date('r') . ": " . ("- Calling EF date trigger for customer " . $row['eid'] . " DATE_EFID" . $field['id'] . "_" . $dfm . "_days" . "\n");
				$trgd++;
			}


		}
	}
	$fts = GetFlexTableDefinitions(false, false, false);

	foreach ($fts AS $flextable) {
		$list = GetExtraFlexTableFields($flextable['recordid'], false);
		foreach ($list AS $field) {
			if ($field['fieldtype'] == "date"|| $field['fieldtype'] == "date/time" || GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Date") {

				if ($field['fieldtype'] == "date/time") {
					$efdate = date('Y-m-d', $then);	
					$select = " DATE(EFID" . $field['id'] . ")='" . $efdate . "'";
				} else {
					$efdate = date('d-m-Y', $then);	
					$select = " EFID" . $field['id'] . "='" . $efdate . "'";
				}
				$type = GetExtraFieldTableType($field['id']);
				if ($type == "entity") {
					$sql = "SELECT eid FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE " . $select;
				} elseif ($type == "customer") {
					$sql = "SELECT id AS eid FROM " . $GLOBALS['TBL_PREFIX'] . "customer WHERE " . $select;
				} else {
					$sql = "SELECT recordid AS eid FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $type . " WHERE " . $select . " AND deleted='n'";
				}

				$result= mcq($sql,$db);
				while ($row = mysql_fetch_array($result)) {
					unset ($GLOBALS['email_send_to']);
					ProcessTriggers("DATE_EFID" . $field['id'] . "_" . $dfm . "_days",$row['eid'],"Miscellaneous trigger",false, $flextable['recordid']);
					qlog(INFO, "Calling EF date trigger for flextable " . $flextable['recordid'] . " record " . $row['eid']);
					//print date('r') . ": " . ("- Calling EF date trigger for flextable " . $flextable['recordid'] . " record " . $row['eid'] . "\n");
					$trgd++;
				}
			}
		}
	}

}



unset ($GLOBALS['email_send_to']);

print date('r') . ": " .  $trgd . " triggers kicked (some date reached, could have had no active triggers)\n";

print date('r') . ": " .  SendPersonificatedDailyOverviewMail();
print date('r') . ": " .  "Running compression archiver...\n";
ProcessArchiving();
print date('r') . ": " .  "Synchronizing databases...\n";
SynchroniseFailOverDatabase();

print date('r') . ": " .  "Running day-by-day triggers ... \n";
print date('r') . ": " .  "Daily triggers...\n";
//ProcessTriggers("Every day", "", "Miscellaneous trigger", false, false);
ProcessTriggers("Every day", "", "Miscellaneous trigger", false, false);

$day = date('l'); // Monday .. Sunday

if ($day != "Sunday" && $day != "Saturday") {
	print date('r') . ": " .  "Working day triggers...\n";
	ProcessTriggers("Every working day", "", "Miscellaneous trigger", false, false);
	//ProcessTriggers("Every working day", "Miscellaneous trigger", false, false);
}

print date('r') . ": " .  "Trigger for " . $day . "...\n";
ProcessTriggers("Every " . $day, "", "Miscellaneous trigger", false, false);



if (date('d') == "01") {
	print date('r') . ": " .  "First day of the month, running triggers...\n";
	ProcessTriggers("First day of the month", "", "Miscellaneous trigger", false, false);
	ProcessTriggers("First day of month " . date('n'), "", "Miscellaneous trigger", false, false);

	print date('r') . ": " .  "Creating configuration restore-point...\n";
	CreateConfigurationSnapshot($GLOBALS['ORIGINAL_REPOSITORY'], "Auto-snapshot (1st day of the month)", false, "y");
}
if (date('z') == "0") {
	print date('r') . ": " .  "First day of the year, running triggers...\n";
	ProcessTriggers("First day of the year", "", "Miscellaneous trigger", false, false);	
}


// Extract ascii data from MS Office and OpenDocument files (if they don't have data already)
print date('r') . ": " .  "Updating document metadata index ...\n";
ExtractIndexData(false, true);

// Create indexes for extra fields of certain types which don't have an index yet...
print date('r') . ": " .  "Checking/creating database indices ...\n";
CheckAndCreateDatabaseIndices();

print date('r') . ": " .  "Done.\n";




print $outp;

$subj = "Interleave notifier output";
AddMessage("admin", "0", "" . $subj, $outp);

$GLOBALS['IN_SYNC_FUNC'] = true; // This effectively re-enables fail-over query sync
if ($slave[$_REQUEST['reposnr']]) {
	print date('r') . ": " .  "Synchronising databases... \n";
	SyncDbsIncremental($_REQUEST['reposnr']);
}
$GLOBALS['IN_SYNC_FUNC'] = ""; // This effectively re-enables fail-over query sync


function uselogger($comment,$dummy_extra_not_used){
	global $REMOTE_ADDR, $HTTP_SERVER_VARS, $actuser, $username, $user, $HTTP_USER_AGENT,$name,$logqueries;

		// here comes the mail trigger

	 if (getenv(HTTP_X_FORWARDED_FOR)){
	   $ip=getenv(HTTP_X_FORWARDED_FOR);
	 }
	 else {
	   $ip=getenv(REMOTE_ADDR);
	 }


	if (!$comment) {
		$qs  = getenv("QUERY_STRING");
		$qs .= getenv("HTTP_POST_VARS");
		$qs .= $comment;
	} else {
		$qs = $comment;
	}
	$url = $HTTP_SERVER_VARS["PHP_SELF"];

	$query ="INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "uselog (ip, url, useragent, qs, user) VALUES ('" . mres($ip) . "', '" . mres($url) . "', '" . mres($HTTP_USER_AGENT) . "' , '" . mres($qs) . "','" . mres($name) . "')";
	mcq($query,$db);
	if ($logqueries) {
		qlog(INFO, "'$ip', '$url', '$HTTP_USER_AGENT' , '$qs','$name'");
	}
}
?>