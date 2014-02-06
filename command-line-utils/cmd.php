<?php
/* ********************************************************************
 * CRM-CTT Interleave
 * Copyright (c) 2001-2011 Hidde Fennema (info@crm-ctt.com)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * Interleave Maintenance command-line client
 *
 * Check http://www.crm-ctt.com/ for more information
 **********************************************************************
 */
if (!$GLOBALS['CONFIGFILE']) {
	$GLOBALS['CONFIGFILE'] = $GLOBALS['PATHTOINTERLEAVE'] . "config/config.inc.php";
}

$GLOBALS['REPOSLIMIT'] = "none";

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



$GLOBALS['CL'] = "Yes";
$GLOBALS['ONLY_LOCAL'] = true;
require_once($GLOBALS['PATHTOINTERLEAVE'] . "config/config-vars.php");
$GLOBALS['ONLY_LOCAL'] = true;
// Set error reporting level
error_reporting(E_ERROR);
print "\nInterleave command line client. Please log in.\n\n";

//$GLOBALS['NO_INSERTS_TO_LOG'] = true;

$repository = 0;

$GLOBALS['CMDLINE'] = true;

//$GLOBALS['logtext'] = false;
$GLOBALS['ShowTraceLink'] == false;
$_COOKIE['trace_on_screen'] == "n";
$GLOBALS['UC']['PERSONALTRACE'] == "Off";

if (CommandlineLogin($auto_login_cmd_user,$auto_login_cmd_pass,$repository)) {

		
		if ($GLOBALS['VERSION'] <> $GLOBALS['DBVERSION'] && (!stristr($_SERVER['PHP_SELF'],"upgrade.php"))) {

			print "The database version (" . $GLOBALS['DBVERSION'] . ") is incompatible with the software version (" . $GLOBALS['VERSION'] . ").\n";
			print "Since this is the administrative interface, you can still continue. Be careful!\n";
		} 

		print "Core version     : " . $GLOBALS['VERSION'] . "\n";
		print "Database version : " . $GLOBALS['DBVERSION'] . "\n";
	


		for ($t=0;$t<64;$t++) {
			ClearAllRunningCache();
			if (db_Connect($t)) {
				if (!$ditisdeeerste) {
					$ditisdeeerste = $t;
				}
				DeleteExpiredSessions();
				mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "loginusers SET TRACE='Off'", $db);
			}
		}

		SafeModeInterruptCheck();
		print "\n";
		ProcessCommand("use " . $ditisdeeerste);

		if ($argv[1] && !strstr($argv[1],"cmd.php") &&  substr($argv[1],0,4) != "cfg=") {
			unset($argv[0]);
			foreach ($argv AS $cmd) {
			 $cmdt .= $cmd . " ";
			
			}
			ProcessCommand(trim($cmdt));
		} elseif ($commando && !strstr($commando,"cmd.php")) {
			$commando = explode(";", $commando);
			foreach ($commando AS $cmd) {
				ProcessCommand($cmd);
		 	}

		} else {
		
			while ($command <> "exit" && $command <> "\q") {
				unset($GLOBALS['RUNNING_ON_SLAVE']);
				unset($GLOBALS['RUNNING_ON_MASTER']);
				unset($GLOBALS['FO_DB_IS_DOWN']);
				unset($GLOBALS['FORCE_SLAVE_USAGE']);

				$command = Menu();
				if ($command == "\\") {
					ProcessCommand($last_command);
				} else {
					$last_command = $command;
					ProcessCommand($command);
				}
			}
		}

//EndHTML(false);
}




function ProcessCommand($command) {
	global $table_prefix, $short, $web, $host, $auto, $database;
	if (substr($command, 0, 3) == "sh " || substr($command, 0, 2) == "x ") {
		$command = "@@@@@" . $command;
		$command = ereg_replace("@@@@@sh ", "show ", $command);
		$command = ereg_replace("@@@@@x ", "exec ", $command);
	}

	if ($command == "show tables") $command = "tables";

	if (substr($command, 0, 2) == "e ") $command = str_replace("e ", "show entity ", $command);
	if (substr($command, 0, 2) == "c ") $command = str_replace("c ", "show customer ", $command);
	if (substr($command, 0, 2) == "f ") $command = str_replace("f ", "show ftrecord ", $command);

	if (substr($command, 0, 3) == "ft ") $command = str_replace("ft ", "ftinfo ", $command);
	if (substr($command, 0, 3) == "ef ") $command = str_replace("ef ", "efinfo ", $command);

	if (substr($command, 0, 5) == "time ") {
		$command = ereg_replace("time ", "", $command);
		print "\nStart time : " . date('U') . " which is " . date('c') . "\n";
		$start = date('U');
		ProcessCommand($command);
		print "End time   : " . date('U') . " which is " . date('c') . "\n";
		print "Duration   : " . (date('U') - $start) . " seconds.\n";
		print "Done       : " . $GLOBALS['TIMES_DB_CONNECTS'] . " database connections were set up.\n";
		unset($GLOBALS['TIMES_DB_CONNECTS']);
		return(true);
	}

	if ($command == "exec maintenance") {
		ProcessCommand("exec db repair");
		ProcessCommand("exec db optimize");
		ProcessCommand("exec drop cache");
		ProcessCommand("exec db check auto");
	} elseif ($command == "bla") {
		SyncDbsIncremental(1);
		} elseif ($command == "exec fix") {
		ProcessCommand("exec drop cache");
		ProcessCommand("exec db index");
	} elseif ($command == "show all") {
		print "\nRepositories:\n";
		ProcessCommand("show repos");
		print "\nCurrent sessions:\n";
		ProcessCommand("show users");
		print "\nRepository activity:\n";
		ProcessCommand("show act");
		print "\nRepository statistics:\n";
		ProcessCommand("show stat");
		print "\nSynchronisation status:\n";
		ProcessCommand("show sync");
		print "\nSynchronisation errors:\n";
		ProcessCommand("show sync errors");
	} elseif ($command == "exec db index") {
		for ($t=0;$t<64;$t++) {
			if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				print "Indexing " . $GLOBALS['title'] . "...\n";
				ExtractIndexData(false, true);
				print "Done!\n\n";
			}
		}
		unset($GLOBALS['FORCE_SLAVE_USAGE']);
		unset($GLOBALS['RUNNING_ON_SLAVE']);
		unset($GLOBALS['FO_DB_IS_DOWN']);
	} elseif ($command == "exec db index all") {
		for ($t=0;$t<64;$t++) {
			if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				print "Indexing " . $GLOBALS['title'] . "...\n";
				ExtractIndexData(false, false);
				print "Done!\n\n";
			}
		}
		unset($GLOBALS['FORCE_SLAVE_USAGE']);
		unset($GLOBALS['RUNNING_ON_SLAVE']);
		unset($GLOBALS['FO_DB_IS_DOWN']);
	} elseif (substr($command, 0, 4) == "use ") {
		db_Connect(str_replace("use ", "", $command));
		$GLOBALS['REPOSINUSE'] = str_replace("use ", "", $command);
		print "OK: " . $GLOBALS['title'] . "\n";
	} elseif (substr($command, 0, 6) == "limit ") {
		$limit = str_replace("limit ", "", $command);
		if ($limit == "off") {
			print "OK: " . $GLOBALS['title'] . "\n";
			print "OK: Limit deleted\n";
			$GLOBALS['REPOSLIMIT'] = "none";
		} else {
			
			db_Connect($limit);
			$GLOBALS['REPOSLIMIT'] = $limit;
			print "OK: " . $GLOBALS['title'] . "\n";
			print "OK: Only this repository will exist for this session\n";
		}
	} elseif ($command == "tables") {
		$tmp = GetFlextableDefinitions();
		print "Tables in " . $GLOBALS['title'] . "\n";

		print " ------------------------------------------------------------------------------------------\n";
		print " | Num | Name                           | Orientation          | Refers to    | Records   |\n";
		print " ------------------------------------------------------------------------------------------\n";
	
		foreach ($tmp AS $row) {
			$cnt = db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $row['recordid']);
			print " | " . fillout($row['recordid'], 3) . " | " . fillout($row['tablename'],30) . " | " . fillout($row['orientation'],20) . " | " . fillout($row['refers_to'],12) . " | " . fillout($cnt,10) . "|\n";
		}
		print " ------------------------------------------------------------------------------------------\n";
	} elseif ($command == "fields entity") {
		$tmp = GetFlexTableNames(str_replace("fields ", "", $command));
		print "Extra fields of table " . str_replace("fields ", "", $command) . ": " . $tmp[0] . "\n";

		print " -------------------------------------------------------------------------------------\n";
		print " | Type                         | Field                                              |\n";
		print " -------------------------------------------------------------------------------------\n";
		$tmp = db_GetArray("SELECT fieldtype, CONCAT(id, ': ', name) FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE tabletype='entity'");
		
		foreach ($tmp AS $row) {
			print " | " . fillout($row[0], 20) . "\t\t| " . fillout($row[1],50) . " |\n";
		}
		print " -------------------------------------------------------------------------------------\n";
	} elseif ($command == "fields customer") {
		$tmp = GetFlexTableNames(str_replace("fields ", "", $command));
		print "Extra fields of table " . str_replace("fields ", "", $command) . ": " . $tmp[0] . "\n";

		print " -------------------------------------------------------------------------------------\n";
		print " | Type                         | Field                                              |\n";
		print " -------------------------------------------------------------------------------------\n";
		$tmp = db_GetArray("SELECT fieldtype, CONCAT(id, ': ', name) FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE tabletype='customer'");
		
		foreach ($tmp AS $row) {
			print " | " . fillout($row[0], 20) . "\t\t| " . fillout($row[1],50) . " |\n";
		}
		print " -------------------------------------------------------------------------------------\n";

	} elseif (substr($command, 0, 7) == "fields ") {
		$tmp = GetFlexTableNames(str_replace("fields ", "", $command));
		print "Extra fields of table " . str_replace("fields ", "", $command) . ": " . $tmp[0] . "\n";

		print " -------------------------------------------------------------------------------------\n";
		print " | Type                         | Field                                              |\n";
		print " -------------------------------------------------------------------------------------\n";
		$tmp = db_GetArray("SELECT fieldtype, CONCAT(id, ': ', name) FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE tabletype='" . str_replace("fields ", "", $command) . "'");
		
		foreach ($tmp AS $row) {
			print " | " . fillout($row[0], 20) . "\t\t| " . fillout($row[1],50) . " |\n";
		}
		print " -------------------------------------------------------------------------------------\n";


	} elseif (substr($command, 0, 7) == "efinfo ") {
		
		$ef = str_replace("efinfo ", "", $command);
		$table = GetExtraFieldTableType($ef);
		switch ($table) {
			case "entity":
				$tmp = GetExtraFields($ef, true);
			break;
			case "customer":
				$tmp = GetExtraCustomerFields($ef);
			break;
			default: 
				$tmp = GetExtraFlexTableFields($table, $ef);
				$tmp2 = GetFlexTableNames($table);
				$table = $table . " \"" . $tmp2[0] . "\"";
			break;

		}
		print "Extra field " . str_replace("efinfo ", "", $command) . ": " . GetExtraFieldName($ef) . " belonging to table " . $table . "\n";
		print " -------------------------------------------------------------------------------------\n";
		print " | Field                        | Value                                              |\n";
		print " -------------------------------------------------------------------------------------\n";
		
		
		foreach ($tmp[0] AS $key => $val) {
			if (!is_numeric($key)) {
				print " | " . fillout($key, 20) . "\t\t| " . fillout($val,50) . " |\n";
			}
		}
		print " -------------------------------------------------------------------------------------\n";
	} elseif (substr($command, 0, 7) == "ftinfo ") {
		$tmp = GetFlexTableNames(str_replace("ftinfo ", "", $command));
		print "Flextable " . str_replace("ftinfo ", "", $command) . ": " . $tmp[0] . "\n";
		print " -------------------------------------------------------------------------------------\n";
		print " | Field                        | Value                                              |\n";
		print " -------------------------------------------------------------------------------------\n";
		$tmp = GetFlextableDefinitions(str_replace("ftinfo ", "", $command), false, true);

		foreach ($tmp[0] AS $key => $val) {
			if (!is_numeric($key)) {
				print " | " . fillout($key, 20) . "\t\t| " . fillout($val,50) . " |\n";
			}
		}
		print " -------------------------------------------------------------------------------------\n";

	} elseif (substr($command, 0, 13) == "show journal ") {
		$e = str_replace("show journal ", "", $command);
		$tmp  = db_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "journal WHERE eid='" . mres($e) . "'");

		print " ---------------------------------------------------------------------------------------------------------\n";
		print " | User                                         | Message                                                |\n";
		print " ---------------------------------------------------------------------------------------------------------\n";
		foreach ($tmp AS $value) {
			if (!is_numeric($key)) {
				print " | " . fillout(GetUserName($value['user']), 44) . " | " . fillout($value['message'],54) . " |\n";
			}
		}
		print " ---------------------------------------------------------------------------------------------------------\n";

	} elseif (substr($command, 0, 12) == "show entity ") {
		$e = str_replace("show entity ", "", $command);
		$tmp  = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE eid='" . mres($e) . "'");

		print " ---------------------------------------------------------------------------------------------------------\n";
		print " | Field                                        | Value                                                  |\n";
		print " ---------------------------------------------------------------------------------------------------------\n";
		foreach ($tmp AS $key => $value) {
			if (!is_numeric($key)) {
				if (substr($key, 0, 4) == "EFID") {
					$key = fillout(str_replace("EFID", "", $key),3) . " ". GetExtraFieldName(str_replace("EFID", "", $key));
				}
				print " | " . fillout($key, 44) . " | " . fillout($value,54) . " |\n";
			}
		}
		print " ---------------------------------------------------------------------------------------------------------\n";
	} elseif (substr($command, 0, 14) == "show customer ") {
		$e = str_replace("show customer ", "", $command);
		$tmp  = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "customer WHERE id='" . mres($e) . "'");

		print " ---------------------------------------------------------------------------------------------------------\n";
		print " | Field                                        | Value                                                  |\n";
		print " ---------------------------------------------------------------------------------------------------------\n";
		foreach ($tmp AS $key => $value) {
			if (!is_numeric($key)) {
				if (substr($key, 0, 4) == "EFID") {
					$key = fillout(str_replace("EFID", "", $key),3) . " ". GetExtraFieldName(str_replace("EFID", "", $key));
				}
				print " | " . fillout($key, 44) . " | " . fillout($value,54) . " |\n";
			}
		}
		print " ---------------------------------------------------------------------------------------------------------\n";
	} elseif (substr($command, 0, 14) == "show ftrecord ") {
		$e = explode(",", str_replace("show ftrecord ", "", $command));
		$table = $e[0];
		$record = $e[1];
		$tmp  = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . mres($table) . " WHERE recordid='" . mres($record) . "'");

		print " ---------------------------------------------------------------------------------------------------------\n";
		print " | Field                                        | Value                                                  |\n";
		print " ---------------------------------------------------------------------------------------------------------\n";
		foreach ($tmp AS $key => $value) {
			if (!is_numeric($key)) {
				if (substr($key, 0, 4) == "EFID") {
					$key = fillout(str_replace("EFID", "", $key),3) . " ". GetExtraFieldName(str_replace("EFID", "", $key));
				}
				print " | " . fillout($key, 44) . " | " . fillout($value,54) . " |\n";
			}
		}
		print " ---------------------------------------------------------------------------------------------------------\n";

	} elseif (substr($command, 0, 15) == "show attr user ") {
		$user = str_replace("show attr user ", "", $command);

		print "Show attributes of user " . $user . ": " . GetUserName($user) . ", " . GetUserEmail($user) . "\n";
		print " ---------------------------------------------------------------------------------------------------------\n";
		print " | Field                                        | Value                                                  |\n";
		print " ---------------------------------------------------------------------------------------------------------\n";
		$tmp = db_GetArray("SELECT attribute, value FROM " . $GLOBALS['TBL_PREFIX'] . "attributes WHERE entity='" . mres($user) . "' and identifier='user'");
		foreach ($tmp AS $row) {
			print " | " . fillout($row[0], 44) . " | " . fillout($row[1],54) . " |\n";
		}
		print " ---------------------------------------------------------------------------------------------------------\n";
	} elseif ($command == "exec mailq") {
		for ($t=0;$t<64;$t++) {
			if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				if (GetSetting("USEMAILQUEUE") == "Yes") {
					print "Processing mail queue of repository " . $GLOBALS['title'] . "...\n";
					ProcessMailQueue();
				}
			}
		}
	} elseif ($command == "exec drop mailq") {
		for ($t=0;$t<64;$t++) {
			if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				if (GetSetting("USEMAILQUEUE") == "Yes") {
					mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "mailqueue SET status = 'error' WHERE status='unsent'", $db);
				}
			}
		}

	} elseif ($command == "show mailq") {
		for ($t=0;$t<64;$t++) {
			if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				if (GetSetting("USEMAILQUEUE") == "Yes") {
					print str_repeat("-", 104) . "\n";
					print " Mail queue of repository " . fillout($GLOBALS['title'], 77) . "|\n";
					print str_repeat("-", 104) . "\n";
					print " " . fillout("id", 5) . " | ";
					print fillout("User", 15) . " | ";
					print fillout("From", 20) . " | ";
					print fillout("To", 20) . " | ";
					print fillout("Timestamp added", 20) . " | ";
					print fillout("Status", 6) . " | ";
					print "\n";
					print str_repeat("-", 104) . "\n";
					$tmp = db_GetArray("SELECT queueid,user,`from`,`to`,date_queued,status FROM " . $GLOBALS['TBL_PREFIX'] . "mailqueue WHERE status = 'unsent'");
					$queuecounter = 0;
					foreach ($tmp AS $u) {
						print " " . fillout($u['queueid'], 5) . " | ";
						print fillout(GetUserName($u['user']), 15) . " | ";
						print fillout($u['from'], 20) . " | ";
						print fillout($u['to'], 20) . " | ";
						print fillout($u['date_queued'], 20) . " | ";
						print fillout($u['status'], 6) . " | ";
						$queuecounter++;
						print "\n";
					}
					if ($queuecounter == 0) {
						print fillout(" Queue is empty", 103) . "|\n";
					} else {
						print str_repeat("-", 104) . "\n";
						print fillout(" " . $queuecounter . " mails in queue", 103) . "|\n";
					}
					print str_repeat("-", 104) . "\n";
					print "\n";
				}
			}
		}




	} elseif ($command == "exec db compress") {
		for ($t=0;$t<64;$t++) {
			if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				print "Compressing " . $GLOBALS['title'] . "...\n";
				ProcessArchiving();
//				print "Done!\n\n";
			}
		}
		unset($GLOBALS['FORCE_SLAVE_USAGE']);
		unset($GLOBALS['RUNNING_ON_SLAVE']);
		unset($GLOBALS['FO_DB_IS_DOWN']);
	} elseif ($command == "exec index cleanup") {
		for ($t=0;$t<64;$t++) {
			if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				print "Index cleanup for repository " . $GLOBALS['title'] . "...\n";
				ParseCleanupExtractedAscii();
			}
		}
		unset($GLOBALS['FORCE_SLAVE_USAGE']);
		unset($GLOBALS['RUNNING_ON_SLAVE']);
		unset($GLOBALS['FO_DB_IS_DOWN']);
	} elseif ($command == "exec db uncompress") {
		for ($t=0;$t<64;$t++) {
			if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				print "Uncompressing " . $GLOBALS['title'] . "...\n";
				UnCompressAllFiles();
				print "Done!\n\n";
			}
		}
		unset($GLOBALS['FORCE_SLAVE_USAGE']);
		unset($GLOBALS['RUNNING_ON_SLAVE']);
		unset($GLOBALS['FO_DB_IS_DOWN']);
	}  elseif ($command == "exec db copyEAV") {
		for ($t=0;$t<64;$t++) {
			if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				print "Copying EAV model to tables for repository " . $GLOBALS['title'] . "...\n";
				CopyEAVModel();
				print "Done!\n\n";
			}
		}
		unset($GLOBALS['FORCE_SLAVE_USAGE']);
		unset($GLOBALS['RUNNING_ON_SLAVE']);
		unset($GLOBALS['FO_DB_IS_DOWN']);

	} elseif ($command == "exec sync release") {
		for ($t=0;$t<64;$t++) {
			if (DB_Connect($t, false)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				print fillout($GLOBALS['title'], 40) . "Sync released (master)\n";
				mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value='' WHERE setting='SYNC_DISABLED_UNTIL'", $db);
			}
			if (DB_Connect($t, true)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				print fillout($GLOBALS['title'], 40) . "Sync released (slave)\n";
				mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value='' WHERE setting='SYNC_DISABLED_UNTIL'", $db);
			}
		}
		unset($GLOBALS['FORCE_SLAVE_USAGE']);
		unset($GLOBALS['RUNNING_ON_SLAVE']);
		unset($GLOBALS['FO_DB_IS_DOWN']);
	} elseif ($command == "exec sync unlock") {
		for ($t=0;$t<64;$t++) {
			if (DB_Connect($t, false)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				print fillout($GLOBALS['title'], 40) . "Sync unlocked (master)\n";
				mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "failoverquerystore SET lockhash=''", $db);
			}
			if (DB_Connect($t, true)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				print fillout($GLOBALS['title'], 40) . "Sync unlocked (slave)\n";
				mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "failoverquerystore SET lockhash=''", $db);
			}
		}
		unset($GLOBALS['FORCE_SLAVE_USAGE']);
		unset($GLOBALS['RUNNING_ON_SLAVE']);
		unset($GLOBALS['FO_DB_IS_DOWN']);
	} elseif ($command == "exec sync incfix" || $command == "exec sync incfix full" || $command == "exec sync incfix full full") {
		$full = true;
		
		if ($command == "exec sync incfix full") $full = false;
		if ($command == "exec sync incfix full full") $GLOBALS['fullFull'] = true;
		ProcessCommand("exec db sync");

		include($GLOBALS['CONFIGFILE']);
		for ($t=0;$t<64;$t++) {
			if (DB_Connect($t, false) && $slave[$t] && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				print "\n\nWorking on repository " . $t . ", " . $GLOBALS['title'] . "\n";
				SyncDbsIncremental($t, false, false, $full);
			}
		}

	} elseif ($command == "exec db convert_utf8") {
		include($GLOBALS['CONFIGFILE']);
		for ($t=0;$t<64;$t++) {
			if (DB_Connect($t, false) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				print "\n\nWorking on repository " . $t . ", " . $GLOBALS['title'] . "\n";
				ConvertDatabaseToUTF8();
			}
		}
	} elseif ($command == "exec queryset") {
		$q = array();
 

		include($GLOBALS['CONFIGFILE']);
		for ($t=0;$t<64;$t++) {
			if (DB_Connect($t, false) && !$slave[$t]) {
					print "\n\nWorking on repository " . $t . ", " . $GLOBALS['title'] . "\n";
					foreach ($q AS $qu) {
						$qu = str_replace("PRFX@@@@@@@", $GLOBALS['TBL_PREFIX'], $qu);
						print $qu . "\n";
						mcq_upg($qu, $db);

					}
				}
		}
	} elseif ($command == "exec sync repair all") {
		print "\nWarning: this function will cause heavy load on your database (slave and master) server!\n";
		print "\nThis function will re-copy all tables from the ALL master databases to the slave database. This will\n";
		print "fix databases which show up as COMP(romised) in the \"show sync\" command output.\n\n";
		print "Running this can take quite some time depending on the size of your databases. The database will be\n";
		print "put into maintenance mode while this command is running. Current sessions will therefore be lost.\n\n";
		print "Are you sure? (y/n)\n";
		print "Interleave > ";
		$answer = readln();
	//	while ($result <> "i" && $result<>"f") {
	//		print "Repair incrementally or full? (i/f):\n";
	//		print "Interleave > ";
	//		$result = readln();
	//	}
	//	if ($result == "f") {
	//		$OnlyDamagedTables = false;
	//	} else {
	//		$OnlyDamagedTables = true;
	//	}
		if ($answer == "y") {
			
			for ($t=0;$t<64;$t++) {
				if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
					//RepairFailOverdatabase($t,true,$OnlyDamagedTables);
					SyncDbsIncremental($t);
				}
			}
			
		}	
	} elseif (substr($command, 0, 16) == "exec sync repair" && ($command <> "exec sync repair all")) {
		$db_to_process = trim(str_replace("exec sync repair", "", $command));
		if (is_numeric($db_to_process)) {
			// all ok
		} else {
			print "\nWarning: this function will cause heavy load on your database (slave and master) server!\n";
			print "\nThis function will re-copy all tables from the master database to the slave database. This will\n";
			print "fix databases which show up as COMP(romised) in the \"show sync\" command output.\n\n";
			print "Running this can take quite some time depending on the size of your database. The database will be\n";
			print "put into maintenance mode while this command is running. Current sessions will therefore be lost.\n";
			ProcessCommand("show repos");
			print "Enter number of database to copy to fail-over database:\n";
			print "Interleave > ";
			$db_to_process = readln();
			
			/*while ($result <> "i" && $result<>"f") {
				print "Repair incrementally or full? (i/f):\n";
				print "Interleave > ";
				$result = readln();
			}
			if ($result == "f") {
				$OnlyDamagedTables = false;
			} else {
				$OnlyDamagedTables = true;
			}
			*/
			
		}
		db_Connect($db_to_process);
		SyncDbsIncremental($db_to_process);
		//RepairFailOverdatabase($db_to_process,true,$OnlyDamagedTables);
		db_Connect($db_to_process);
		mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value='No' WHERE setting='MAINTENANCE_MODE'", $db);
		ProcessCommand("set mm off");
		db_Connect($db_to_process);
	}  elseif ($command == "exec snapshot create") {

		ProcessCommand("show repos");
		print "Enter number of database create system restore point for:\n";
		print "Interleave > ";
		$db_to_process = readln();
		
		while ($result <> "1" && $result<>"2" && $result<>"3") {
			print "1. Configuration snapshot without users and profiles\n";
			print "2. Configuration snapshot with users and profiles\n";
			print "3. Complete database snapshot (all tables)\n";
			print "Interleave > ";
			$result = readln();
		}
		if ($result == "1") {
			$typeofsnapshot = "nousers";
		} elseif ($result == "2") {
			$typeofsnapshot = "withusers";
		} elseif ($result == "3") {
			$typeofsnapshot = "wholedb";
		}
		print "Description :\n";
		print "Interleave > ";
		$desc = readln();

		db_Connect($db_to_process);
		
		CreateConfigurationSnapshot($db_to_process, $desc, true, $typeofsnapshot);
		
		db_Connect($db_to_process);


	}  elseif ($command == "exec snapshot restore") {

		ProcessCommand("show repos");
		print "Enter number of database restore snapshot for:\n";
		print "Interleave > ";
		$db_to_process = readln();

		db_Connect($db_to_process);
		print "\n-----------------------------------------------------------------------------------------------------------\n";
		print " " . fillout("Num",3) . " | Creation date/time  | " . fillout("Description",30) . " | " . fillout("Type", 30) . " | " . fillout("Size", 10) . " |\n";
		print "-----------------------------------------------------------------------------------------------------------\n";
		$tmp = db_GetArray("SELECT id, datetime, comment, snapshottype, LENGTH(config) AS bytes FROM " .  $GLOBALS['TBL_PREFIX'] . "configsnapshots ORDER BY datetime DESC");
		foreach ($tmp AS $row) {
			if ($row['snapshottype'] == "withusers") {
				$row['snapshottype'] = "Configuration snapshot with users & profiles"; 
			} elseif ($row['snapshottype'] == "nousers") {
				$row['snapshottype'] = "Configuration snapshot without users & profiles";
			} elseif ($row['snapshottype'] == "wholedb") {
				$row['snapshottype'] = "Complete database snapshot";
			}
			print " " . fillout($row['id'],3) . " | " . $row['datetime'] . " | " . fillout($row['comment'],30) . " | " . fillout($row['snapshottype'], 30) . " | " . fillout(FormatNumber($row['bytes'] / 1024, 0) . "K", 10) . " |\n";
		}
		print "-----------------------------------------------------------------------------------------------------------\n";
		print "Enter number of snapshot to restore:\n";
		print "Interleave > ";
		$restorepoint = readln();

		print "Are your sure? (y|n) :\n";
		print "Interleave > ";
		$confirm = readln();

		if ($confirm == "y") {
			SafeModeInterruptCheck();
			RestoreConfigurationSnaphot($db_to_process, $restorepoint, true);
		} else {
			print "OK Quitting.\n";
		}
		
		

		
		db_Connect($db_to_process);


	} elseif ($command == "exec cache test") {
		print "\nWarning: this test may cause heavy load on your database server!\n";
		ProcessCommand("show repos");
		print "Enter number of database to test:\n";
		print "Interleave > ";
		$db_to_process = readln();
		CheckIfCacheIsUsefull($database[$db_to_process]);
		mcq("TRUNCATE TABLE " . $GLOBALS['TBL_PREFIX'] . "accesscache", $db);

	} elseif ($command == "show author") {
		sh_auth();
	} elseif ($command == "exec config fix") {
		for ($t=0;$t<64;$t++) {
			if (DB_Connect($t, false)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				check_config($table_prefix[$t]);
			}
		}
	} elseif ($command == "set mm on") {
		for ($t=0;$t<64;$t++) {
			if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value='Yes' WHERE setting='MAINTENANCE_MODE'", $db);
			}
		}
		print "Maintenance mode enabled on all repositories.\n";
	} elseif ($command == "set pcon on") {
		print "\nUsing persistent connection to connect to database.\n";
		unset($GLOBALS['CMD_CONN_OVRW']);
	} elseif ($command == "set pcon off") {
		$GLOBALS['CMD_CONN_OVRW'] = true;
		print "\nUsing regular connection to connect to database.\n";
	} elseif ($command == "exec db repair") {
		print "\nRunning REPAIR TABLE on all tables on all repositories.\n";

		for ($t=0;$t<64;$t++) {
			if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				SetTIU("");
				$x = 1;
				foreach ($GLOBALS['TABLES_IN_USE'] AS $table) {
					print "\015" . fillout($GLOBALS['title'],40) . ": " . $x . "/" . sizeof($GLOBALS['TABLES_IN_USE']);
					$x++;
					$res = mcq("REPAIR TABLE " . $table, $db);

				}
				print " done.\n";
			}
		}
	} elseif ($command == "exec db optimize") {
		print "\nRunning OPTIMIZE TABLE on all tables on all repositories.\n";
		for ($t=0;$t<64;$t++) {
			if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				SetTIU("");
				$x = 1;
				foreach ($GLOBALS['TABLES_IN_USE'] AS $table) {
					print "\015" . fillout($GLOBALS['title'], 40) . ": " . $x . "/" . sizeof($GLOBALS['TABLES_IN_USE']);
					$x++;
					$res = mcq("OPTIMIZE TABLE " . $table, $db);
				}
				print " done.\n";
			}
		}
	} elseif (substr($command,0, 15) == "exec recalc all") {
		$res = str_replace("exec recalc all", "", $command);
		if ($res != "") {
			if ($res == "entity" || $res == "e" || $res == "customer" || $res == "c" || IsValidFlexTable($res)) {
				if ($res == "e") $res = "entity";
				if ($res == "c") $res = "customer";
			} else {
				$res = false;
			}
		}
		for ($t=0;$t<64;$t++) {
			ClearAllRunningCache();
			if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				$GLOBALS['CRON_RUNNING'] = true;
				$GLOBALS['IN_SYNC_FUNC'] = true;
				SetTIU("");
				print "Recalculating ALL computed extra fields (" . $GLOBALS['title'] . ") ... \n";
				CalculateAllComputedExtraFields(true, true, $res);
				print " done.\n";
			}
		}
		print "It's nescessary to run 'exec drop cache' now to make sure all values on cached forms are correct.\n";
	} elseif (substr($command,0,11) == "exec recalc") {
		$res = str_replace("exec recalc", "", $command);
		if ($res != "") {
			if ($res == "entity" || $res == "e" || $res == "customer" || $res == "c" || IsValidFlexTable($res)) {
				if ($res == "e") $res = "entity";
				if ($res == "c") $res = "customer";
			} else {
				$res = false;
			}
		}
		for ($t=0;$t<64;$t++) {
			ClearAllRunningCache();
			if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				$GLOBALS['CRON_RUNNING'] = true;
				$GLOBALS['IN_SYNC_FUNC'] = true;
				SetTIU("");
				print "Recalculating computed extra fields for open entities only (add 'all' to command to recalculate all entities).\n";
				print "All customer and flextable computed fields will also be recalculated.\n";
				print "Working on " . $GLOBALS['title'] . "... ";
				CalculateAllComputedExtraFields(false, true, $res);
				print " done.\n";
			}
		}
		print "It's nescessary to run 'exec drop cache' now to make sure all values on cached forms are correct.\n";


	} elseif ($command == "exec db tn") {
		for ($t=0;$t<64;$t++) {
			ClearAllRunningCache();
			if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				print "Start: " . $GLOBALS['title'] . "\n";
				GenerateImageThumbnails("all", true);
			}
		}
	} elseif ($command == "set mm off") {

		for ($t=0;$t<64;$t++) {
			if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value='No' WHERE setting='MAINTENANCE_MODE'", $db);
			}
		}
		print "Maintenance mode disabled on all repositories.\n";

	} elseif ($command == "exec db sync") {
			include($GLOBALS['CONFIGFILE']);
			print  "-------------------------------------------------------\n";
			print  " Repository                              | Sync       |\n";
			print  "-------------------------------------------------------\n";

			for ($t=0;$t<64;$t++) {
				ClearAllRunningCache();
				if ($slave[$t]) {
					unset($GLOBALS['FO_DB_IS_DOWN']);
					$GLOBALS['CRON_RUNNING'] = true;
					$GLOBALS['NO_TEXT'] = true;

					if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
						SetTIU("");
		
						print " " . fillout($GLOBALS['title'],39) . " |";
						SynchroniseFailOverDatabase();
						print " OK         |\n";
					}

				}
			}
			print  "-------------------------------------------------------\n";

	} elseif ($command == "exec drop cache") {
			print "\nDeleting all cache on all repositories.\n";
			for ($t=0;$t<64;$t++) {
				ClearAllRunningCache();
				if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
					print "Delete cache on " . fillout($GLOBALS['title'], 40) . " : ";
					DropAllCache();
					print "done.\n";
				}
			}
	} elseif ($command == "exec create cache") {
			print "\nCreating access cache for all users for all tables on all repositories.\n";
			for ($t=0;$t<64;$t++) {
				ClearAllRunningCache();
				if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
					SetAllAccessCache();
				}
			}

	} elseif ($command == "exec drop lqueue") {
			for ($t=0;$t<64;$t++) {
				ClearAllRunningCache();
				if (DB_Connect($t, false)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
					if (!$GLOBALS['RUNNING_ON_SLAVE'] && !$GLOBALS['FO_DB_IS_DOWN']) {
						$GLOBALS['FORCE_SLAVE_USAGE'] = true;
						if (DB_Connect($t, true)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
							// OK Slave is reachable
						}
						unset($GLOBALS['FORCE_SLAVE_USAGE']);
					}
					DB_connect($t, false); // back to normal db.
					if ($GLOBALS['RUNNING_ON_SLAVE']) {
						print fillout($GLOBALS['title'], 40) . "ERROR : Master database is down.\n";
					} elseif ($GLOBALS['RUNNING_ON_MASTER'] && $GLOBALS['FO_DB_IS_DOWN']) {
						print fillout($GLOBALS['title'], 40) . "Local queue dropped (slave is down)\n";
						mcq("TRUNCATE TABLE " . $GLOBALS['TBL_PREFIX'] . "failoverquerystore", $db);
					} else {
						print fillout($GLOBALS['title'], 40) . "Local queue dropped\n";
						mcq("TRUNCATE TABLE " . $GLOBALS['TBL_PREFIX'] . "failoverquerystore", $db);
					}
				}
			unset($GLOBALS['FORCE_SLAVE_USAGE']);
			unset($GLOBALS['RUNNING_ON_SLAVE']);
			unset($GLOBALS['FO_DB_IS_DOWN']);
			}
	} elseif ($command == "exec drop rqueue") {
			include($GLOBALS['CONFIGFILE']);
			for ($t=0;$t<64;$t++) {
				ClearAllRunningCache();
				if (DB_Connect($t, false)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
					if (!$GLOBALS['RUNNING_ON_SLAVE'] && !$GLOBALS['FO_DB_IS_DOWN']) {
						$GLOBALS['FORCE_SLAVE_USAGE'] = true;
						if (DB_Connect($t, true)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
							// OK Slave is reachable
						}
					}

					if ($GLOBALS['RUNNING_ON_SLAVE']) {
						print fillout($GLOBALS['title'], 40) . "Remote queue dropped (master is down)\n";
						mcq("TRUNCATE TABLE " . $table_prefix[$t] . "failoverquerystore", $db);
					} elseif ($GLOBALS['RUNNING_ON_MASTER'] && $GLOBALS['FO_DB_IS_DOWN']) {
						print fillout($GLOBALS['title'], 40) . "ERROR : Slave database is down.\n";
					} else {
						print fillout($GLOBALS['title'], 40) . "Remote queue dropped\n";
						mcq("TRUNCATE TABLE " . $table_prefix[$t] . "failoverquerystore", $db);
					}
				}
			unset($GLOBALS['FORCE_SLAVE_USAGE']);
			unset($GLOBALS['RUNNING_ON_SLAVE']);
			unset($GLOBALS['FO_DB_IS_DOWN']);

			}
	} elseif ($command == "exec eval") {

		print "Which command to evaluate? (warning: eval will be run only once!) (" . $GLOBALS['title'] . ")\n";
		print "PHP > ";
		$cmd = readln();
		eval($cmd);
		/*
		for ($t=0;$t<64;$t++) {
			if (DB_Connect($t, false)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				eval($cmd);
				print fillout("Repository " . $t, 40) . "\nCommand evaluated (master)\n";

			}
			if (DB_Connect($t, true)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				eval($cmd);
				print fillout("Repository " . $t, 40) . "\nCommand evaluated (slave)\n";

			}

		}
		*/
	} elseif ($command == "exec sql") {

		foreach ($table_prefix AS $pr) {
				if (!$t) {
					$t = $pr;
				}
				if ($t <> $pr) {
					$strange = true;
				}
				$t = $pr;
			}
		if ($strange) {
			print "You NEED to use PRFX@@@@@@@ as table prefix. The routine will replace that with the appropriate table prefix.\n";
		}
		print "Which query to execute?\n";
		print "SQL > ";
		$sql = readln();
		for ($t=0;$t<64;$t++) {
			if (DB_Connect($t, false)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				print fillout($GLOBALS['title'], 40) . "Query executed (master)\n";
				mcq(str_replace("PRFX@@@@@@@", $GLOBALS['TBL_PREFIX'], $sql), $db);
			}
			//if (DB_Connect($t, true)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
			//	print fillout($GLOBALS['title'], 40) . "Query executed (slave)\n";
			//	mcq(str_replace("PRFX@@@@@@@", $GLOBALS['TBL_PREFIX'], $sql), $db);
			//}

		}

	} elseif ($command == "exec db check" || $command == "exec db check auto") {

		//$short = true;
		$web = false;
		for ($t=0;$t<64;$t++) {
			if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				if ($command == "exec db check auto") {
					$auto = true;
				}
				CheckDB();
			} else {
				//print "Failed to connect to repository " . $t . "\n";
			}
		}
	} elseif ($command == "exec trace") {
		ProcessCommand("show users");
		print "Enter the number of the user you want to trace > ";
		$usernum = readln();
		$repos = $GLOBALS['utt'][$usernum][0];
		$user = $GLOBALS['utt'][$usernum][1];
		db_Connect($repos);
		mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "loginusers SET TRACE='On' WHERE id='" . mres($user) . "'", $db);
		print "Trace flag set .... Due to PHP limitations, you can only stop tracing by killing this script\n";
		print "with CTRL-C. Please log on to cmd.php after that again; the trace will automatically be disabled.\n";
		print "If you don't do that, the trace will be enabled until the users logs out, generating lots of data.\n";
		$key = "TRACE" . $repos . $user;
		$codes = array();
		while (1==1) {
			sleep(1);
			if ($last_epoch) {
				$t = DB_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "cache WHERE LEFT(value," . strlen($key) . ")='" . mres($key) . "' AND epoch>=" . $last_epoch);
			} else {
				$t = DB_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "cache WHERE LEFT(value," . strlen($key) . ")='" . mres($key) . "'");
			}
			foreach ($t AS $row) {
				$code = md5($row['value']);
				if (!in_array($code, $codes)) {
					print str_replace($key . ": ", "", $row['value']) . "";
				}
				array_push($codes, $code);
				$last_epoch = $row['epoch'];
			}
		}

	} elseif ($command == "show users") {
		$users = 0;
		$GLOBALS['utt'] = array();
		print "Num " . fillout("User",20) . " " . fillout("Last activity",20) . " " . fillout("Session expire",20) . "\n";
		for ($t=0;$t<64;$t++) {
			if (DB_Connect($t, false)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				DeleteExpiredSessions();
				$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "sessions ORDER BY exptime DESC";
				$result = mcq($sql,$db);
				print "\n ---- " . $GLOBALS['title'] . ":\n\n";
				while ($row = mysql_fetch_array($result)) {
					$users++;
					$timeout_sec =  $row['exptime'] + (GetSetting("timeout") * 60);
					print fillout($users,3) . " " . fillout(GetUserNameByName($row['name']),20) . " " . fillout(date('Y-m-d H:i:s',$row['exptime']),20) . " " . fillout(date('Y-m-d H:i:s',$timeout_sec),20) . "\n";

					$one = true;
					$GLOBALS['utt'][$users] = array($t,GetUserID($row['name']));

				}
				if (!$one) {
//					print "No sessions.\n";
				} else {
					unset($one);
				}
			} else {
			}
		}
	//	print "=========================================================================\n";
		print "\nTotal active sessions: " . $users . "\n";
	//	print "=========================================================================\n";
	} elseif ($command == "exec drop users") {
		print "Are you sure? (y/n)\n";
		print "Interleave > ";
		$answer = readln();
		if ($answer == "y") {
			for ($t=0;$t<64;$t++) {
				if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
					DeleteExpiredSessions();
					mcq("DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "sessions", $db);
				}
			}
			print "All user sessions ended on all repositories.\n\n";
		}
	} elseif ($command == "show proc ---- DISABLED") {
		include($GLOBALS['CONFIGFILE']);
		print "" . fillout("PID",5) . " " . fillout("Start",20) . " " . fillout("File",20) . " " . fillout("User",15) . " " .  fillout("Status", 10) . " Repository\n";
		for ($t=0;$t<64;$t++) {
				if (DB_Connect($t, $false)&& ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
					$t2 = DB_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "processes WHERE result='running'");
					foreach ($t2 AS $row) {
						print "" . fillout($row[0],5) . " " . fillout($row[1],20) . " " . fillout($row[2],20) . " " . fillout(GetUserName($row[3]),15) . " " . fillout($row[4],10) . " " . $GLOBALS['title'] . "\n";
					}
				}
		}

	} elseif ($command == "show sync errors") {
		include($GLOBALS['CONFIGFILE']);
		for ($t=0;$t<64;$t++) {
			if ($slave[$t]) {
				if (DB_Connect($t, $false)&& ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {

						if (DB_Connect($t, true)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
							$sql = "SELECT eid, category FROM " . $GLOBALS['TBL_PREFIX'] . "entity ORDER BY eid DESC LIMIT 20";
							$res = mcq($sql, $db);
							while ($row = mysql_fetch_array($res)) {
								$string1 .= "EID: " . $row['eid'] . " CAT:" . $row['category'];
							}
						}
						if (DB_Connect($t, false)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {

							$sql = "SELECT eid, category FROM " . $GLOBALS['TBL_PREFIX'] . "entity ORDER BY eid DESC LIMIT 20";
							$res = mcq($sql, $db);
							while ($row = mysql_fetch_array($res)) {
								$string2 .= "EID: " . $row['eid'] . " CAT:" . $row['category'];
							}
						}
						if ($string1 <> $string2) {
							print " Errors in    : " . $GLOBALS['title'] . "\n";
							print " Following strings were compared:\n";
							print " Master DB : " . $string2 . "\n";
							print " Slave  DB : " . $string1 . "\n";
						} else {
							print " No errors in : " . $GLOBALS['title'] . "\n";
						}

					}
					unset($rqueue);
					unset($lqueue);
					unset($GLOBALS['RUNNING_ON_SLAVE']);
					unset($GLOBALS['RUNNING_ON_MASTER']);
					unset($GLOBALS['FO_DB_IS_DOWN']);
					unset($GLOBALS['FORCE_SLAVE_USAGE']);
					unset($string1);
					unset($string2);
					unset($status);
				}

		}
	} elseif ($command == "exec sync create" || $command == "exec sync create all") { // Create a fail-over copy
			if ($command == "exec sync create all") {
				$all = true;
			} else {
				ProcessCommand("show repos");
				print "Enter number of database to copy to fail-over database:\n";
				print "Interleave > ";
				$db_to_process = readln();
			}
			print "Enter the host name of the fail-over MySQL server:\n";
			print "Interleave > ";
			$sl_host = readln();
			print "Warning: username and password need to be the same. The database will be created. Continue? (y|n)\n";
			if ($all) {
				print "Warning: all known repositories will be copied!\n";
			}
			print "Interleave > ";
			$yesno = readln();
			if ($yesno == "y") {
				if ($all) {
					for ($t=0;$t<64;$t++) {
						if ($host[$t] != ""&& ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
							print "Working on repository " . $t . "\n";
							CreateFailOverDatabase($t, $sl_host);
						}
					}
				} else {
					CreateFailOverDatabase($db_to_process, $sl_host);
				}
			} else {
				print "OK quitting.\n";
			}

	} elseif ($command == "exec sync compare") { // Undocumented feature
		include($GLOBALS['CONFIGFILE']);
		for ($t=0;$t<64;$t++) {
			if ($slave[$t] && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				$x = CompareSyncTables($t);
				print "--- Repository " . $t . " -----------------------------------------\n";
				print " Table name                     | #Master    | #Slave    |\n";
				print "----------------------------------------------------------\n";
				foreach ($x AS $row => $value) {
					print " " . fillout($row, 30) . " | " . fillout($value['master'],10) . " | " . fillout($value['slave'],10) . "|";
					if ($value['master'] <> $value['slave']) {
						print "*";
					}
					print "\n";
				}
				print "----------------------------------------------------------\n";
			}
		}
	} elseif ($command == "exec sync check") {
		include($GLOBALS['CONFIGFILE']);
		for ($t=0;$t<64;$t++) {
			if ($slave[$t] && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				if (DB_Connect($t, $false)) {

						if (DB_Connect($t, true)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
							$sql = "SELECT eid, category FROM " . $GLOBALS['TBL_PREFIX'] . "entity ORDER BY eid";
							$res = mcq($sql, $db);
							while ($row = mysql_fetch_array($res)) {
								$string1 .= "EID: " . $row['eid'] . " CAT:" . $row['category'];
							}
							$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "triggers ORDER BY tid";
							$res = mcq($sql, $db);
							while ($row = mysql_fetch_array($res)) {
								$string1 .= "ONC: " . $row['onchage'] . " ACT:" . $row['action'];
							}

						}
						if (DB_Connect($t, false)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
							$sql = "SELECT eid, category FROM " . $GLOBALS['TBL_PREFIX'] . "entity ORDER BY eid";
							$res = mcq($sql, $db);
							while ($row = mysql_fetch_array($res)) {
								$string2 .= "EID: " . $row['eid'] . " CAT:" . $row['category'];
							}

							$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "triggers ORDER BY tid";
							$res = mcq($sql, $db);
							while ($row = mysql_fetch_array($res)) {
								$string2 .= "ONC: " . $row['onchage'] . " ACT:" . $row['action'];
							}
						}
						if ($string1 <> $string2) {
							print " Errors in    : " . $GLOBALS['title'] . "\n";
							
							print " Following strings were compared:\n";
							$x = CompareSyncTables($t);
							print "----------------------------------------------------------\n";
							print " Table name                     | #Master    | #Slave    |\n";
							print "----------------------------------------------------------\n";
							foreach ($x AS $row => $value) {
								print " " . fillout($row, 30) . " | " . fillout($value['master'],10) . " | " . fillout($value['slave'],10) . "|";
								if ($value['master'] <> $value['slave']) {
									print "*";
								}
								print "\n";
							}
							print "----------------------------------------------------------\n";
//							print " Master DB : " . $string2 . "\n";
//							print " Slave  DB : " . $string1 . "\n";
						} else {
							print " No errors in : " . $GLOBALS['title'] . "\n";
						}

					}
					unset($rqueue);
					unset($lqueue);
					unset($GLOBALS['RUNNING_ON_SLAVE']);
					unset($GLOBALS['RUNNING_ON_MASTER']);
					unset($GLOBALS['FO_DB_IS_DOWN']);
					unset($GLOBALS['FORCE_SLAVE_USAGE']);
					unset($string1);
					unset($string2);
					unset($status);
				}

		}
	} elseif ($command == "show sync") {
		include($GLOBALS['CONFIGFILE']);
		unset ($GLOBALS['CRON_RUNNING']);
		print "\n Status: OK = All fine, HALT = Sync halted, COMP = Databases not in sync, WAIT = Waiting for release\n";
		print "-----------------------------------------------------------------------------------------------------------\n";
		print "  Running on   | QS   | Local queue    | Remote queue  | Status | Repository                              |\n";
		print "-----------------------------------------------------------------------------------------------------------\n";
		for ($t=0;$t<64;$t++) {
			if ($slave[$t] && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				unset($GLOBALS['RUNNING_ON_SLAVE']);

				if (DB_Connect($t, $false) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {

					$sdu = GetSetting("SYNC_DISABLED_UNTIL");

					$lqueue = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "failoverquerystore WHERE targethost='" . mres($slave[$t]) . "'");
					//$lqueue_locks = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "failoverquerystore WHERE targethost='" . mres($slave[$t]) . "' AND lockhash<>''");

					$lqueue_records = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "failoverquerystore");



					//$lqueue[0] = $lqueue[0] . " / " . $lqueue_locks[0];

					if (!$GLOBALS['RUNNING_ON_SLAVE'] && !$GLOBALS['FO_DB_IS_DOWN']) {
						$GLOBALS['FORCE_SLAVE_USAGE'] = true;
						if (DB_Connect($t, true)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
							$rqueue = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "failoverquerystore WHERE targethost='" . mres($host[$t]) . "'");
							$rqueue_records = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "failoverquerystore");
						}  else {
							$GLOBALS['FO_DB_IS_DOWN'] = true;
							$GLOBALS['RUNNING_ON_MASTER'] = true;
						}
						unset($GLOBALS['FORCE_SLAVE_USAGE']);

					}
				} else {
					if (DB_Connect($t, true)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
						$rqueue = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "failoverquerystore WHERE targethost='" . mres($host[$t]) . "'");
						$rqueue_records = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "failoverquerystore");
						$GLOBALS['RUNNING_ON_SLAVE'] = true;
					}  else {
						print "Total panic. Cannot connect to any database.\n";
					}

				}

					$tot = $rqueue_records[0] + $lqueue_records[0];

					if ($GLOBALS['RUNNING_ON_SLAVE']) {
						$rqueue = $lqueue;
						$status = " HALT ";
						print "  Slave        | " . fillout($tot, 4) . " | master is down | " . fillout($rqueue[0], 14) . "| " . $status . " | " . fillout($GLOBALS['title'], 40) . "|\n";
					} elseif ($GLOBALS['RUNNING_ON_MASTER'] && $GLOBALS['FO_DB_IS_DOWN']) {
						$status = " HALT ";
						print "  Master       | " . fillout($tot, 4) . " | " . fillout($lqueue[0], 14) . " | slave is down | " . $status . " | " . fillout($GLOBALS['title'], 40) . "|\n";
					} else {
						$status = "  OK  ";

						if (DB_Connect($t, true)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
							$sql = "SELECT eid, category FROM " . $GLOBALS['TBL_PREFIX'] . "entity ORDER BY eid DESC LIMIT 20";
							$res = mcq($sql, $db);
							unset($string1);
							while ($row = mysql_fetch_array($res)) {
								$string1 .= "EID: " . $row['eid'] . " CAT:" . $row['category'];
							}
						}
						$GLOBALS['FORCE_SLAVE_USAGE'] = false;
						if (DB_Connect($t, false)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {

							unset($string2);
							$sql = "SELECT eid, category FROM " . $GLOBALS['TBL_PREFIX'] . "entity ORDER BY eid DESC LIMIT 20";
							$res = mcq($sql, $db);
							while ($row = mysql_fetch_array($res)) {
								$string2 .= "EID: " . $row['eid'] . " CAT:" . $row['category'];
							}
						}
						if ($string1 <> $string2) {
							$status = " COMP ";
						}

						if (is_numeric($sdu) && date('U') < $sdu) {
							$status = " WAIT ";
						}
						print "  Master       | " . fillout($tot, 4) . " | " . fillout($lqueue[0], 14) . " | " . fillout($rqueue[0], 14) . "| "  . $status . " | " . fillout($t,3) . "" . fillout($GLOBALS['title'], 37) . "|\n";
					}


			unset($rqueue);
			unset($lqueue);
			unset($GLOBALS['RUNNING_ON_SLAVE']);
			unset($GLOBALS['RUNNING_ON_MASTER']);
			unset($GLOBALS['FO_DB_IS_DOWN']);
			unset($GLOBALS['FORCE_SLAVE_USAGE']);
			unset($string1);
			unset($string2);
			unset($status);

			}
		}
		print "-----------------------------------------------------------------------------------------------------------\n";

	} elseif ($command == "show stat" || $command == "show stat csv") {
		if ($command == "show stat") {
			print "---------------------------------------------------------------------------------------------------------\n";
			print "  #Entities  | Size on disk (MB)   | Total records  | Sessions | Repository                             |\n";
			print "---------------------------------------------------------------------------------------------------------\n";
		} else {
			print "#entities;size on disk;total records;sessions;repository\n";
		}
		$tp = array();
		for ($t=0;$t<64;$t++) {
			if (DB_Connect($t, false)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {

				$res = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity");
				$ent = $res[0];
				$tent += $ent;
				$res = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "sessions");
				$users = $res[0];
				$tusers += $users;
				$sql = "SHOW TABLE STATUS";
				$result= mcq($sql,$db);

				while ($stat = @mysql_fetch_array($result))
				{
					$size += $stat["Data_length"];
					$size += $stat["Index_lenght"];

				}

				$tot_size += (($size/1024)/1024);
				$size = ceil((($size/1024)/1024)) . "";

				$tov = CountTotalNumOfRecords("");
				$ttov += $tov;

				if ($command == "show stat csv") {
					array_push($tp, number_format($ent) . ";" . number_format($size) . ";" . number_format($tov) . ";" . $users . ";" . $GLOBALS['title'] . "\n");
				} else {
					array_push($tp, "  " . fillout(number_format($ent), 11) . "| " . fillout(number_format($size),20) . "| " . fillout(number_format($tov),15) . "| " . fillout($users, 9) . "| " . fillout($GLOBALS['title'],39) . "|\n");
				}
			}
		}

		foreach ($tp AS $line) {
			print $line;
		}

		if ($command == "show stat") {
			print "---------------------------------------------------------------------------------------------------------\n";
			print "  " . fillout(number_format($tent), 11) . "| " . fillout(number_format($tot_size),20) . "| " . fillout(number_format($ttov),15) . "| " . fillout($tusers, 9) . "| " . fillout(" +",39) . "|\n";
			print "---------------------------------------------------------------------------------------------------------\n";
		} else {
			print "" . number_format($tent) . ";" . number_format($tot_size) . ";" . number_format($ttov). ";" . $tusers . ";" . " Totals" . "\n";
		}
	} elseif ($command == "exec func check") {
		for ($t=0;$t<64;$t++) {
			if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				print "=========================================================================\n";
				print "Repository \"" . $GLOBALS['title'] . "\"\n";
				print CheckDatabaseSettings();
			}
		}
	} elseif ($command == "show act" || $command == "show act csv") {
		if ($command == "show act csv") {
			print "Score;Avg hits p/day;Tot. hits;Age (days);Accounts;Repository\n";
		} else {
			print "\nThe following statistics are based on the uselog. If you ever purged the uselog table this information might not be accurate.\n";
			print "Score formula: score = (avg. hits per day) * (days used)\n\n";
			print "--------------------------------------------------------------------------------------------------------------------\n";
			print "  Score     | Avg hits p/used day | Tot. hits | Days used   | Accounts  | Repository                               |\n";
			print "--------------------------------------------------------------------------------------------------------------------\n";
		}
		$rest = array();
		$nums = 1;
		for ($t=0;$t<64;$t++) {
			if (DB_Connect($t, false)  && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				DeleteExpiredSessions();
				$res = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers");
				$users = $res[0];
				$res = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "uselog");
				$tothits = $res[0];

				unset($tt);
				$tt = array();
				$res = mcq("SELECT timestamp_last_change, UNIX_TIMESTAMP(timestamp_last_change) AS epoch, date_format(timestamp_last_change, '%a %M %e, %Y %H:%i') AS ts, date_format(timestamp_last_change, '%a %M %e') AS ts2, count(*) AS ct FROM " . $GLOBALS['TBL_PREFIX'] . "uselog GROUP BY ts2 ORDER BY id", $db);

				while ($row = mysql_fetch_array($res)) {
					if (!$arh) {
						if ($row['epoch']) {
							$tmp = (date('U') - $row['epoch']);
							$aid = ($tmp / 86400);
							$arh = true;
						//	print $GLOBALS['title'] . " Date: " . date('U') . " eopch: " . $row['epoch'] . "\n";
						//	print $GLOBALS['title'] . " Oldest is $aid days ago " . $row['epoch'] . "\n" ;
						}
					}
					$num = $num + $row['ct'];
					$count++;
				}

				$avg = $num / $count;
				$aid = $count;
				$score = ceil($aid) * ceil($avg);

				$tavg += $avg;
				$ttothits += $tothits;
				$devide++;
				$taid += $aid;
				$tusers += $users;

				if ($command == "show act csv") {
					array_push($rest,"" . (($nums)) . ";" .  (ceil($avg)) . ";" . ($tothits) . ";" . (ceil($aid)) . ";" . ($users) . ";" . ($GLOBALS['title']) . "\n");
				} else {
					array_push($rest,"  " . fillout(($nums), 10) . "| " .  fillout(ceil($avg),20) . "| " . fillout($tothits, 10) . "| " . fillout(ceil($aid),12) . "| " . fillout($users,10) . "| " . fillout($GLOBALS['title'],41) . "|\n");
				}
				$nums++;
				unset($avg);
				unset($count);
				unset($num);
				unset($aid);
				unset($arh);
				unset($score);
			}
		}
		sort($rest, SORT_NUMERIC);
		foreach ($rest AS $line) {
			print $line;
		}


		if ($command <> "show act csv") {
			print "--------------------------------------------------------------------------------------------------------------------\n";
			print "  " . fillout(("Total"), 10) . "| " .  fillout(ceil($tavg/$devide),20) . "| " . fillout($ttothits, 10) . "| " . fillout(($taid),12) . "| " . fillout($tusers,10) . "| " . fillout("+ ",41) . "|\n";
			print "--------------------------------------------------------------------------------------------------------------------\n";
		} else {
			print "Total;" .  (ceil($tavg/$devide)) . ";" . $ttothits . ";" . $taid . ";" . $tusers . ";\n";
		}

	} elseif ($command == "show repos" || $command == "show repos balance") {
		if ($command == "show repos") {
			print ReturnReposList();
		} else {
			print ReturnReposList(true);
		}
	} elseif ($command == "show logins") {
		print "\nLogins today\n";
		print "-------------------------------------------------------------------------------------------------------------\n";
		print fillout("User", 30) . "| " . fillout("IP-address",20) . "| " . fillout("Time", 20) . " | " . fillout("Repository", 30) . " |\n";
		print "-------------------------------------------------------------------------------------------------------------\n";
		$done = array();
		$totnum = 0;
		for ($t=0;$t<64;$t++) {
			if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				$p = db_GetArray("SELECT ip, qs, user, timestamp_last_change FROM " . $GLOBALS['TBL_PREFIX'] . "uselog WHERE qs LIKE 'Login %' AND LEFT(timestamp_last_change, 10) = '" . date('Y-m-d') . "'");
				foreach ($p AS $row) {
					print fillout($row['qs'], 30) . "| " . fillout($row['ip'],20) . "| " . fillout($row['timestamp_last_change'],20) . " | " . fillout($GLOBALS['title'],30) . " |\n";
					if (!in_array($row['qs'], $done)) {
						array_push($done, $row['qs']);
					}
					$totnum ++;
				}
			}
		}
		print "-------------------------------------------------------------------------------------------------------------\n";
		print "Total logins today: " . $totnum . ", unique logins: " . sizeof($done) . "\n";

	} elseif ($command == "show modules" || $command == "exec module" || $command == "edit module") {
		print "\nAvailable modules\n";
		print "------------------------------------------------------------------------------------------------------\n";
		print fillout("id", 5) . "| " . fillout("Name", 20) . " | " . fillout("Desc.",30) . " | " . fillout("Res.", 4) . " | " . fillout("Repository", 30) . " |\n";
		print "------------------------------------------------------------------------------------------------------\n";
		$done = array();
		$modules = array();
		$count = 1;
		for ($t=0;$t<64;$t++) {
			if (db_Connect($t) && ($t == $GLOBALS['REPOSLIMIT'] || $GLOBALS['REPOSLIMIT'] == "none")) {
				$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "modules ORDER BY module_name";
				$rs = mcq($sql, $db);
				while ($row = mysql_fetch_array($rs)) {
					print "" . fillout($count,5) . "| " . fillout(trim($row['module_name']),20) . " | " . fillout($row['module_description'],30) . " | " . fillout($row['module_last_run_result'], 4) . " | " . fillout($GLOBALS['title'],30) . " |\n";
					$modules[$count] = array("repository" => $t, "mid" => $row['mid']);
					$count++;
				}
			}
		}
		print "------------------------------------------------------------------------------------------------------\n";
		print ($count-1) . " modules available.\n";
		if ($command == "exec module") {
			print "\nA module will always run only within its own repository context.\nPlease enter the number of the module to run from the list above.\n";
			print "Module number or 0 to cancel > ";
			$modnum = readln();
			if ($modnum != "0") {
				print "Running module " . $modnum . " which is module " . $modules[$modnum]['mid'] . " from repository " . $modules[$modnum]['repository'] . "\n";
				db_Connect($modules[$modnum]['repository']);
				$res = RunModule($modules[$modnum]['mid'], false, true, false, false);
				$res = eregi_replace("</tr>", "\n", $res);
				$res = eregi_replace("</td><td>", "\t\t", $res);
				$res = eregi_replace("<br>", "\n", $res);	
				$res = eregi_replace("<br />", "\n", $res);	
				print strip_tags($res);
			}
		} elseif ($command == "edit module") {
			print "Module number or 0 to cancel > ";
			$modnum = readln();
			if ($modnum != "0") {
				print "Editing module " . $modnum . " which is module " . $modules[$modnum]['mid'] . " from repository " . $modules[$modnum]['repository'] . "\n";
				db_Connect($modules[$modnum]['repository']);
				$tmp = $GLOBALS['TMP_FILE_PATH'];
				print "Editor command [mcedit -c] > ";
				$editor = readln();
				if ($editor == "") $editor = "mcedit -c";
				$mod_body = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "modules WHERE mid='" . mres($modules[$modnum]['mid']) . "'");
				$epoch = date('U');
				$file = $tmp . "/" . randomstring(10, true) . "_" . $epoch . "_cmdInterleaveEdit.php";
				print "Created " . $file . "\n";
				$fp = fopen($file, "w");
				fputs($fp, $mod_body['module_code']);
				fclose($fp);
				passthru($editor . " " . $file);
				$newcode = file_get_contents($file);
				print "Save new code to database? [y|n] > ";
				$yesno = readln();
				if ($yesno == "y") {
					mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "modules SET module_code='" . mres($newcode) . "' WHERE mid='" . mres($modules[$modnum]['mid']) . "'", $db);
					print "Module saved\n";
				} else {
					print "Module not saved\n";
				}
				unlink($file);
			}
		}


	} elseif ($command == "show help" || $command == "help") {

		print "\n >>> Unless specified otherwise all actions here apply to all repostories. <<<\n\n";
		print "-----------------------------------------------------------------------------------------\n";
		print "  Command              | Explanation                                                    |\n";
		print "-----------------------------------------------------------------------------------------\n";
		print "  show repos           | Show repository list                                           |\n";
		print "  show help            | Show (this) command list                                       |\n";
		print "  show users           | Show current user sessions                                     |\n";
		print "  show logins          | Show today's logins                                            |\n";
		print "  show act             | Show activity                                                  |\n";
		print "  show act csv         | Show activity (character separated values)                     |\n";
		print "  show stat            | Show statistics                                                |\n";
		print "  show stat csv        | Show statistics (character separated values)                   |\n";
		print "  show sync            | Show synchronisation status                                    |\n";
		print "  show sync errors     | Show synchronisation error details                             |\n";
		print "                       |                                                                |\n";
		print "  ftinfo [num]         | Show flextable properties of flextable [num]                   |\n";
		print "  efinfo [num]         | Show extra field properties of field [num]                     |\n";
		print "  fields [num]         | Show fields of table [num], or 'entity' or 'customer'          |\n";
		print "  tables               | Show flextable definitions                                     |\n";
		print "                       |                                                                |\n";
		print "  show entity [num]    | Show contents of entity [num]                                  |\n";
		print "  show customer [num]  | Show contents of customer [num]                                |\n";
		print "  show ftrecord x,y    | Show contents of record y of flextable x                       |\n";
		print "  show attr user [num] | Show attributes of user [num]                                  |\n";

		print "                       |                                                                |\n";

		print "  set mm on            | Enable maintenance mode                                        |\n";
		print "  set mm off           | Disable maintenance mode                                       |\n";
		print "  set pcon on          | Enable use of persistent DB connections (default)              |\n";
		print "  set pcon off         | Disable use of persistent DB connections                       |\n";
		print "                       |                                                                |\n";
		print "  exec sql             | Execute an SQL-query                                           |\n";
		print "  exec eval            | Open PHP/Interleave prompt                                     |\n";
		print "  exec trace           | Trace a user session                                           |\n";
		print "                       |                                                                |\n";
		print "  exec db check        | Check for errors (prompt before deleting anything)             |\n";
		print "  exec db check auto   | Check for errors (auto-delete)                                 |\n";
		print "  exec db repair       | Run REPAIR TABLE commmand on all tables                        |\n";
		print "  exec db optimize     | Run OPTIMIZE TABLE commmand on all tables                      |\n";
		print "  exec db sync         | Synchronise fail-over databases                                |\n";
		print "  exec db index (all)  | Update fast search index (add 'all' to force)                  |\n";
		print "  exec db tn           | Create thumbnail index of JPEG & GIF attachments               |\n";
		print "  exec db compress     | Run the file compression archiver                              |\n";
		print "  exec db uncompress   | Uncompress all files in all repositories                       |\n";
		print "                       |                                                                |\n";
		print "  exec func check      | Check for functional warnings                                  |\n";
	//  print "  exec cache test      | Test to see if caching is useful                               |\n";
		print "  exec config fix      | Check for double config entries (and delete duplicates)        |\n";
		print "  exec maintenance     | Run check db with auto delete, OPTIMIZE and REPAIR             |\n";
		print "  exec drop users      | End all user sessions                                          |\n";
		print "  exec drop cache      | Empty all form, published page, thumbnail and access cache     |\n";
		print "  exec create cache    | Create access cache for all users (for security testing)       |\n";
		print "  exec drop lqueue     | Empty all local sync queues                                    |\n";
		print "  exec drop rqueue     | Empty all remote sync queues                                   |\n";
		print "  exec sync release    | Re-start synchronisation (when disabled)                       |\n";
		print "  exec sync unlock     | Unlock stalled records in synchronisation queue                |\n";
		print "  exec sync check      | Run in-depth consistancy check                                 |\n";
		print "  exec sync incfix     | Fix damaged tables only (incremental, since last sync)         |\n";
		print "  exec sync incfix full| Fix damaged tables only (incremental, compare all records)     |\n";
/*		print "  exec sync repair     | Repair a fail-over database                                    |\n";
		print "  exec sync repair all | Re-copy all existing fail-over instances (be careful!)         |\n";*/
		print "  exec sync create     | Create a fail-over database                                    |\n";
		print "                       |                                                                |\n";
		print "  show mailq           | Show e-mails currently queued for transport                    |\n";
		print "  exec mailq           | Process the mail queue                                         |\n";
		print "  exec drop mailq      | Set status of all unsent messages to 'error' (cancel sending)  |\n";
		print "                       |                                                                |\n";
		print "  \                    | Re-run last command                                            |\n";
		print "  exit                 | Exit program                                                   |\n";
		print "                       |                                                                |\n";
		print "  Tip of the day       | * You can use 'sh' and 'x' as replacement for 'show' and 'exec'|\n";
		print "                       | * Use 'time' as command prefix to time your command            |\n";
		print "                       | * Show entity, customer and ftrecord commands can be called    |\n";
		print "                       |   using e, c or f respectively. \"e 68\" will show entity 68.    |\n";
		print "                       | * ftinfo and efinfo can also be called usin ft and ef.         |\n";
		print "-----------------------------------------------------------------------------------------\n";
		print "\n Enter option below.\n";
	} elseif ($command == "Are there any easter eggs in this script?") {
		print "No, there aren't any down here.\n";
	} elseif ($command <> "exit" && $command <> "\q") {
		print $command . ": Unrecognized command.\n";
	}

}



function Menu() {
	if ($GLOBALS['REPOSLIMIT'] != "none") {
		db_Connect($GLOBALS['REPOSLIMIT']);
		$ins = "(only) ";
	} else {
		db_Connect($GLOBALS['REPOSINUSE']);
		$ins = "";
	}
	print " \nInterleave (" . $GLOBALS['title'] . ") " . $ins . "> ";
	$a = readln();
	return($a);
}

function printbox($void) {
}


function sh_auth() {
?>
ooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooO
ooooooooooooooooooooooooooo===oooooooDDoo=ooooooooooooooooooooooooooooooooo#
ooooooooooooooooooooooooo====ooDDDODDDDDDo==ooooooooooooooooooooooooooooooo#
oooooooooooooooooooooooo===oooDDOOOOOOODDooo===oooDooooooooooooooooooooooooN
oooooooooooooooooooDDo=====ooDDOOOOOODDoDDooo===oooDDoooooooooooooooooooooo#
oooooooooooooooDDDDooooooooDOOOOOOOOOODoo==oo====oDooDoooooooooooooooooooooO
ooooooooooooooDDDDDooo===ooDDOXXOOOOODDDoo========oDDDDDDDDDDDoDDDDDDDDDDDDN
oooooooooooooDDDDo======ooDDOOOODDOODDooo===eee===oDDDDDDoooooooooooooooooo#
ooooooooooooDDoooooo=====ooDOOODoDOODDo==eee======ooDDDOODooooooooooooooooo#
oooooooooooDDoDDooDDDDDDoo==ooDo=ooDDD=========oooooDDOOOODDoooooooooooooooN
oooooooooDDDDDDooDOOOODDooooooooo==oDooDDDoo=e==oDDDDDODOOODoooooooooooooooN
ooooooooDODDDoooDDODDo=ooo=========oooooDoooo===oDDDDDDDOOODDDoooooooooooooO
ooooooooODDDooooDDDo====eeeaaaaaeeeee=ee====oDo=ooOOOOOODDDOOOoooooooooooooO
oooooooDOOOoo=oooDo===eeaaaaaaaaaaaaaaaaaeee==o==oDOOOOOOOOOOODooooooooooooN
ooooooDOOOOoo====oo==eeeaaaaaaaaaaaaaaaaaaaaee==ooDOXXOOODDODDDDooooooooooo#
ooooooDOXOOoo====oo==eeaaaaaaaaaaaaaaaaaaaaaaa==ooOOXXXOOOOODooDoooooooooooN
oooooDOXXXXDoo===oo=eeeeaaaaaaaaaaaaaaaaaaaaaee=oDOOXXXXOODDDooooooooooooooN
ooooDOOXXXXODDoo=oo=eeeeeeaaaaaaaaaaaaaaaaaaaee=oDOXXXXOOODDDDDDoooooooooooO
ooooDOXXXXXXXODDooo=eeeeeeeaaaaaaaaaaaaaaaaaeeeeoDOXXXXOOOOODDDDDooooooooooO
oooooOXXXXXXXXXDDDo=eeeeeeaaaaaaaaaaaaaaaaaeeeee=oOXXXXXOOODDDDDDooooooooooO
oooooDOOXOOOXXXOODDo=eeeeaaaaaaaaaaaaaaaaaaaaaeeeeoOOOOODDDoDDDOODoooooooooO
oooooDDOOOOOOOXOODoo=eeeaaaaaaaaaaaaa)aaaaaaaaaeeee=oDDDDDDDDDOODDoooooooooN
ooooDOOOOOOOOOOODDo==eeeeeaaaaaaaaaaaaaaaaeeeeeeeee=oDoDDOXXOOOODDDooooooooO
=ooooDOOOOOOOOODoDDo=======o==eeeaaaaaae===oo==oo==ooDDoDOXXXOXXODooooooooo#
==ooooOOOOXXXODDoDooooooo=oDooo=eeaaaee=ooooo==ooooooooDDOXXXOOXODooooooooo#
====ooooDDOOOODoooDDoooDDoDooooDo=eee=oDo===oOOXDooooDOODDOXoDOODoooooooooo#
===oooee=e=DOOOODDoooDoDOOD==ooDo=eaaeoDo====DDDooo==o=oooDOooDODooooooooooN
===oooaa=eeDDDDooo============ooo=eaee=oo=====e=====o===ooDDee==Doooooooooo#
===oooea=eeoDooo=oo===========oo=eeaee==oo==eeee=====e==ooooeee=oDoooooooooN
===ooooe=ee=oooo=================eeaeee====ee=eeeeeeee==oo==e=e=oDooooooooo#
===oooD=eeee=oooo===eeeeeeeeee===eeaae===eeeeeeeeeeeee=oDo==e==eDDooooooooo#
===oooDD=e=e=ooDo===eeeeeeaeee===eeaae====eaaaaaeeeeee=oDo=eaeeoDDDooooooooN
===oooDDo=eee=oDo===eeeeaaaee====eeaaee====eeeaaeeeee==oDoeeaeeDDDooooooooo#
====ooDDD=eeeeoDoo==eeeeeeeee==oo=eee=o====eeeeeeeeee=ooDoeee=DDDDooooooooo#
=====ooDDD=eeeoDDo==eeeeeeeeee===o==oo====eeeeeeeeee==ooDDDDDDDDDooooooooooO
=====ooDDDDDoDODDoo==eeeeeeeee=====o===eeeeeeeeeeeee==ooDDDDDDDDoooooooooooN
=====ooDDDDDDOODDoo==eeeeeeeeee==eee=eeeeeeeeeeeeeee==ooDDDDDDDDooooooooooo#
=====ooDDDDDDOOOooo==eeeeee===================eeeeee==ooDDDDDDDDooooooooooo#
======ooDDDDDDDODoo===eee==DODooo=ee=e=e==oDDo=eeee===ooDDDDDDDDoooooooooooO
======ooDDDDDDDODDo===eee===oDDoDo=eee==ooDo===eee===ooDDDDDDDDDooooooooooo#
=======ooDDDDDDDDDoooo=ee=ee==oooooo=ooooo===eeee====ooDDDDDDDDDoooooooooooN
========oDDDDDDDDDDoooo=eeeeee====eeee======eeee====ooDoDDDDDDDDooooooooooo#
========ooDDDDDDDDDDooo==eeeeee==========eeeeeee===ooDooDDDDDDDooooooooooooO
=========oDDDDDDDDDOODoo==eee=eeeee===eeeeeeeee===ooo==o=DDDDooooooooooooooN
=========ooDDDDDDDDDDODoo==eeeeeeeeeeeeeeeeeee=o=ooo===oa=DooooooooooooooooO
===========ooooDDDDDDDDDDo==e=eeeeeeeeeeeeeee=oooDo=e===aeoOooooooooooooooo#
=============ooDDDDDDDoDDDD===eeeeeeeeeeeee==ooDo=eee==a)eeoXDoooooooooooooO
==============ooDDDDDDooDDDDo===eeeeeee====oooo=eeee==a)aeeaDXDoooooooooooo#
==============oooDDDDDDooDDDDDDoo=======oooooo=eeeee=a))aeea=XXXODoooooooooO
==============oooDDDDDDoooooooDDDoooooooooo==eeeeee=a())eaaaeXX#XXXDoooooooN
===============oooDDDD=Dooooooooooooooo=====eeeeeee)(()aaa))eXX#XXXXXODooooO
================oooDDDeoDooo=========eee==eeeeeeee)(()aaa))a=XXXXXXXXX#XXOOO
================ooooOO==DoDoo==eeeeeeeee==eeeeeaa((((a)a)))eeXX##XXXXXX#X#X#
===========oooDDOOXX#X=e=ooooo==eeeeee===eeeeaa)((((a)a)))aaoXX##XXXXXXXXX#O
======oDDOOXX########X==eeooooo============e)a)((()a)a)(()aaDX###XXXXXXXXX##
=ooDOX##############XXoeeea=oooo=======eeea)a(((()))a((()aaaOXX##XXXXXXXXX##
X###################Xooeeea)e==o=======ea)))((((a)))((()aaaaXX###XXXXXXXXXX#
###################Xoe==eeaa))e======eaa)a)((((a)a)(()a)a))eXX##XXXXXXXXXXXN
##################XO=ee==eea)()aeeeea)a)a(((()a)a)(()a)a)((DXXXXXXXXXXXXXXXN
##################X=eae==eaaaeeeeee=aa))(((())))((()a))((()XXXXXXXXXXXXXXXXN
#################XD==aa==ea=DeeDDooo=e)((((a)a)((())))((()=XXXXXXXXXXXXXXXXN
################XXeeeaa==ee=oDDODooooea)(())a)((())))((()aDXXXXXXXXXXXXXXXXN
#################oeeeaa==eeaaeoo====e===ea))(((())))((()))OXXXXXXXXXXXXXXXXO
<?php
}
function CopyEAVModel() {

	$tmp = GetSetting("DBVERSION");

	if ($tmp == "5.3.2") {

		$tmp = GetExtraFields(false, true, true, true);
		$x = 0;

		$fields = db_GetFlatArray("EXPLAIN " . $GLOBALS['TBL_PREFIX'] . "entity");


		foreach ($tmp AS $field) {
			if (!in_array("EFID" . $field['id'], $fields)) {
				mcq("ALTER TABLE " . $GLOBALS['TBL_PREFIX'] . "entity ADD `EFID" . $field['id'] . "` LONGTEXT NOT NULL", $db);
				print "E: Created column EFID" . $field['id'] . "\n";
			}
		}

		$ent = db_GetFlatArray("SELECT eid FROM " . $GLOBALS['TBL_PREFIX'] . "entity");
		print "E: Copying entity extra fields...\n";
		foreach ($ent AS $entity) {
			foreach ($tmp AS $field) {
				$val = db_GetRow("SELECT value FROM " . $GLOBALS['TBL_PREFIX'] . "customaddons WHERE name='" . $field['id'] . "' AND eid=" . $entity . " AND type='entity'");
				mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET EFID" . $field['id'] . "='" . mres($val[0]) . "',tp=tp WHERE eid=" . $entity, $db);

			}
		}

		$tmp = GetExtraCustomerFields();
		$x = 0;

		$fields = db_GetFlatArray("EXPLAIN " . $GLOBALS['TBL_PREFIX'] . "customer");

		foreach ($tmp AS $field) {
				if (!in_array("EFID" . $field['id'], $fields)) {
					mcq("ALTER TABLE " . $GLOBALS['TBL_PREFIX'] . "customer ADD `EFID" . $field['id'] . "` LONGTEXT NOT NULL", $db);
					print "C: Created column EFID" . $field['id'] . "\n";
				}
		}

		$ent = db_GetFlatArray("SELECT id FROM " . $GLOBALS['TBL_PREFIX'] . "customer");
		print "C: Copying customer extra fields...\n";
		foreach ($ent AS $customer) {
			foreach ($tmp AS $field) {
				$val = db_GetRow("SELECT value FROM " . $GLOBALS['TBL_PREFIX'] . "customaddons WHERE name='" . $field['id'] . "' AND eid=" . $customer . " AND type='cust'");
				mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "customer SET EFID" . $field['id'] . "='" . mres($val[0]) . "' WHERE id=" . $customer, $db);

			}
		}

		$flextables = GetFlextableDefinitions();

		foreach ($flextables AS $ft) {
			$tname = $GLOBALS['TBL_PREFIX'] . "flextable" . $ft['recordid'];
			mcq("DROP TABLE IF EXISTS " . $tname, $db);

			mcq("CREATE TABLE " . $tname . "(recordid INT, refer INT, readonly ENUM('no','yes'))", $db);
			print "Table " . $tname . " created\n";
			$f = GetExtraFlexTableFields($ft['recordid'], false, true);
			foreach ($f AS $field) {
					mcq("ALTER TABLE " . $tname . " ADD `EFID" . $field['id'] . "` LONGTEXT NOT NULL", $db);
					//print "FT ". $tname . ": Created column EFID" . $field['id'] . "\n";
			}
			$records = db_GetFlatArray("SELECT DISTINCT(eid) FROM " . $GLOBALS['TBL_PREFIX'] . "customaddons WHERE type='flextable" . $ft['recordid'] . "'");
			$created = 0;
			foreach ($records AS $rec) {
				$created++;
				mcq("INSERT INTO " . $tname . "(recordid) VALUES('" . mres($rec) . "')", $db);
				foreach ($f AS $field) {
					$value = db_GetRow("SELECT value FROM " . $GLOBALS['TBL_PREFIX'] . "customaddons WHERE type='flextable" . $ft['recordid'] . "' AND eid=" . $rec . " AND name='" . $field['id'] . "'");
					mcq("UPDATE " . $tname . " SET EFID" . $field['id'] . " ='" . mres($value[0]) . "' WHERE recordid=" . $rec, $db);

				}
			}
			$tmp = db_GetFlatArray("SELECT recordid FROM " . $tname);
			foreach ($tmp AS $record) {
				$tmp2 = db_GetRow("SELECT value FROM " . $GLOBALS['TBL_PREFIX'] . "customaddons WHERE name='2147483647' AND eid=" . $record . " AND type='flextable" . $ft['recordid'] . "'");
				if ($tmp2['value']) {
					mcq("UPDATE " . $tname . " SET refer='" . mres($tmp2['value']) . "' WHERE recordid=" . $record, $db);
					$ud++;
				}
				$tmp2 = db_GetRow("SELECT value FROM " . $GLOBALS['TBL_PREFIX'] . "customaddons WHERE name='2147483646' AND eid=" . $record . " AND type='flextable" . $ft['recordid'] . "'");
				if ($tmp2['value'] == "readonly") {
					mcq("UPDATE " . $tname . " SET readonly='yes' WHERE recordid=" . $record, $db);
					$ro++;
				}
			}
			print "Records copied          : " . $created . "\n";
			print "References restored     : " . $ud . "\n";
			print "Readonly state restored : " . $ro . "\n";

		}


		mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value=CONCAT(value,'-CopyEAVDone') WHERE setting='DBVERSION'", $db);
	} else {
		print "Wrong database version. Expected 5.3.2 but got " . $tmp . ". Database not touched.\n";
	}

}
function ParseCleanupExtractedAscii() {
	print "Cleaning up ExtraxtedASCII fields in binfiles table...\n";
	$sql = "SELECT fileid, extractedascii, filename FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles";
	$res = mcq($sql, $db);
	while ($row = mysql_fetch_array($res)) {
		$siz += strlen($row['extractedascii']);
		$result = CleanupExtractedAscii($row['extractedascii']);
		$ressiz += strlen($result);

		print "\015Process file " . $row['fileid'] . ": " . fillout($row['filename'],40) . "  ";
		mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "binfiles SET extractedascii='" . mres($result) . "' WHERE fileid='" . mres($row['fileid']) . "'", $db);
	}

	print "\nTotal input bytes  : " . FormatNumber($siz,0) . "\n";
	print "Total output bytes : " . FormatNumber($ressiz,0) . "\n";
	print "Space gained bytes : " . FormatNumber(($siz - $ressiz),0) . "\n";

}
?>