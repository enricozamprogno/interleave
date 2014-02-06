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
//print_r($_SERVER);
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

	$GLOBALS['PATHTOINTERLEAVE'] = DetermineBasePath();


	extract($_REQUEST);

	// Set error reporting level
	error_reporting(E_ERROR);

	if ($_SERVER['HTTP_HOST']) {
		$web = 1;
	//	print "WEB IS AAN";
	//	print_r($_SERVER);
	//	exit;
	}

	if ($web) {
		$EnableRepositorySwitcherOverrule="n";
		require_once($GLOBALS['PATHTOINTERLEAVE'] . "header.inc.php");
		print "</td></tr></table>";
		AdminTabs();
		MainAdminTabs("datman");
	} else {
		require_once($GLOBALS['PATHTOINTERLEAVE'] . "config/config.inc.php");
		require_once($GLOBALS['PATHTOINTERLEAVE'] . "functions.php");
		require_once($GLOBALS['PATHTOINTERLEAVE'] . "config/config-vars.php");


		print "Database integrity check\n\n";
		if ($argv[1]=="-help" || $argv[1]=="--help" || $argv[1]=="help" || $argv[1]=="-h") {
			print "\nUsage:\n";
			print "\t[no arguments]\t:Interactive\n";
			print "or:\n";
			print "\t[reposnr] [user] [pass] [y|n] - (y = auto repair, n = prompt)\n";
			print "\nExample: php -q checkdb.php 0 admin admin_pwd y\n\n";
			exit;
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
		if ($argv[4] == "y") {
			$auto = $argv[4];
			$auto=1;
			print "! Auto-fix is enabled.\n";
		}
		if (!CommandlineLogin($username,$password,$repository)) {
			print "Exiting...";
			exit;
		}

		do_language();


	}

	SetTIU("");
	//print "BLA: " . $db;
	//exit;
	$tables = $GLOBALS['TABLES_IN_USE'];

	if ($web) {
		print "</table><table border=0 width='65%'><tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td><fieldset><legend>&nbsp;<img src='images/crmlogosmall.gif'>&nbsp;&nbsp;<font size=+1>$lang[adm]</font>&nbsp;</legend>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font size=+2>" . $title . "</font><table border=0 width='100%'>";
	}

MustBeAdmin();

SwitchToRepos($repository);

// Some language table management: (completely safe, so don't bother the user)
$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE TEXTID='' AND TEXT=''";
mcq($sql,$db);

// Entity table maintenance
mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET tp=tp, deleted='n' WHERE deleted<>'y'", $db);


if (!$go && $web) {
	$legend = "Check database&nbsp;";
	printbox("This script checks your current repository ($title) for errors, and it will optimize all its tables. On large repositories, it can take quite some time. This script can also be run from the command line.<BR><BR>Do you want to continue?<BR><BR><img src='images/arrow.gif'>&nbsp;<a href='checkdb.php?go=1&web=1' class='bigsort'>Yes</a><BR><BR><img src='images/arrow.gif'>&nbsp;<a href='javascript:history.back(-1);' class='bigsort'>No, take me back</a>");
} elseif ($input) {
	// OK two arrays of to-delete data were submitted.
	// Namely:
	// file_td	:	files to delete
	// cf_td 	:	custom fields to delete

	$file_td =		unserialize(base64_decode($file_td));
	$cf_td =		unserialize(base64_decode($cf_td));
	$cf_td_cust =	unserialize(base64_decode($cf_td_cust));
	$journal_td =	unserialize(base64_decode($journal_td));
	$ejournal_td =	unserialize(base64_decode($ejournal_td));
	$calendar_td =	unserialize(base64_decode($calendar_td));
	$deldoubles =	unserialize(base64_decode($deldoubles));

	$queries = array();

	foreach ($file_td as $file) {
		array_push($queries,"DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles WHERE fileid=" . $file);
		array_push($queries,"DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "blobs WHERE fileid=" . $file);
	}
	// Custom field table can get very large - consolidate the query

	$base_q = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "customaddons WHERE (";
	foreach ($cf_td as $cf) {
		$base_q .= " eid='" . $cf . "' OR";
	}
	$base_q .= " eid = '122219873875983659824645whatever') AND (type='entity' OR type='')";
	array_push($queries,$base_q);

	$base_q2 = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "customaddons WHERE (";
	foreach ($cf_td_cust as $cf) {
		$base_q2 .= " eid='" . $cf . "' OR";
	}
	$base_q2 .= " eid = '122219873875983659824645whatever') AND type='cust'";
	array_push($queries,$base_q2);

	foreach ($journal_td as $jtd) {
		array_push($queries,"DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "journal WHERE eid='" . $jtd . "' AND type='entity'");
	}
	foreach ($ejournal_td as $ejtd) {
		array_push($queries,"DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "ejournal WHERE eid='" . $ejtd . "'");
	}
	foreach ($calendar_td as $ctd) {
		array_push($queries,"DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "calendar WHERE eid='" . $ctd . "'");
	}
	if (!$given_query) { $given_query = array(); }

	if (sizeof($given_query>0)) {
		foreach ($given_query as $row) {
			array_push($queries,$row);
		}
	}
	//print_r($queries);

	$queries = array_merge($queries, $deldoubles);

	for ($x=0;$x<sizeof($tables);$x++) {
		array_push($queries, "OPTIMIZE TABLE " . $tables[$x]);
	}

	foreach($queries as $sql) {
		mcq($sql,$db);
	}



	print "<pre>" . sizeof($queries) . " database queries executed.\n</pre>";
	print "<img src='images/arrow.gif'>&nbsp;<a class='bigsort' href='admin.php?password=$password' style='cursor:pointer'>Back to main administration page</a>";

} else {


	if ($web) {
		print "<pre>";
	}


	$t = CheckDB();
	if ($t == "exit") exit;

} // end if !$go


function printbox($msg)
{
		global $printbox_size,$legend;

		if (!$printbox_size) {
			$printbox_size = "70%";
		}

		print "<table border='0' width='$printbox_size'><tr><td colspan=2><fieldset>";
		if ($legend) {
			print "<legend>&nbsp;<img src='images/crmlogosmall.gif'>&nbsp;&nbsp;$legend</legend>";
		}
		print $msg . "</fieldset></td></tr></table><br>";

		unset($printbox_size);
		$legend = "";

} // end func


function uselogger_local($comment,$dummy_extra_not_used){
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
		$qs = mres($comment);
	}

	$url = $HTTP_SERVER_VARS["PHP_SELF"];

	$query ="INSERT into " . $GLOBALS['TBL_PREFIX'] . "uselog (ip, url, useragent, qs, user) VALUES ('" . mres($ip) . "', '" . mres($url) . "', '" . mres($HTTP_USER_AGENT) . "' , '" . mres($qs) . "','" . mres($name) . "')";
	mcq($query,$db);

	if ($logqueries) {
		qlog(INFO, "'$ip', '$url', '$HTTP_USER_AGENT' , '$qs','$name'");
	}
}
