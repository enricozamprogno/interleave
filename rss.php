<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This is the Interleave RSS Feeder
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
require_once("initiate.php");

if ($_REQUEST['avail']) {

	ShowHeaders();
	if ($_SERVER['HTTPS']=="on") {
		$http = "https://";
	} else {
		$http = "http://";
	}
	$subdir = str_replace("rss.php","",$_SERVER['SCRIPT_NAME']);
	$link = $http . $_SERVER['SERVER_NAME'] . $subdir . "rss.php?";

	$arr = unserialize($GLOBALS['RSS_FEEDS']);
	print "</table><table style='width: 50%;'><tr><td>&nbsp;&nbsp;</td><td><fieldset><legend>&nbsp;<img src='images/crmlogosmall.gif' alt=''>&nbsp;&nbsp;Available RSS Feeds</legend>";
	print "<table style='width: 100%' class='crm'>";
	print "<tr><td>Link</td><td>Description</td><td>URL</td></tr>";
	$FN=1;
	foreach ($arr AS $feed) {
		$ThisLink = $link . "FN=" . $FN . "&rep=" . $repository_nr;
		print "<tr><td><a title='Click to copy link to clipboard' onclick=\"CopyToClipboard('" . $ThisLink . "');\">[click to copy]</a></td><td>" . base64_decode($feed['description']) . "</td><td>" . $ThisLink  . "</td></tr>";
		$FN++;
	}
	print "</table></fieldset></td></tr></table>";
	EndHTML();
	exit;
} else {
	$GLOBALS['repository_nr'] = $_REQUEST['rep'];
	$repository_nr = $_REQUEST['rep'];
	$rss = true;
	$GLOBALS['RSS'] = true;

	if (!$_REQUEST['rep']) {
		$_REQUEST['rep'] = 0;
	}
	$GLOBALS['repository_nr'] = $_REQUEST['rep'];
}
if (strlen($lang['CHARACTER-ENCODING'])>2) {
	qlog(INFO, "Character-encoding override in effect: " . $lang['CHARACTER-ENCODING']);
	$charset = $lang['CHARACTER-ENCODING'];
	$GLOBALS['CHARACTER-ENCODING'] = $lang['CHARACTER-ENCODING'];
} else {
	$charset = "ISO-8859-1";
	$GLOBALS['CHARACTER-ENCODING'] = "ISO-8859-1";
}


if (!isset($_SERVER['PHP_AUTH_USER'])) {
   ShowAuthHeadersRSS();

   exit;
  } else {
		  $GLOBALS['RSS'] = true;
		if (AuthenticateUser($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'],$GLOBALS['repository_nr'])) {
			SwitchToRepos($GLOBALS['repository_nr']);
			$authenticated = true;
			
		} else {
		   ShowAuthHeadersRSS();

		   exit;

		}
}


if ($_REQUEST['detail']) {
	ShowRSSDetail($_REQUEST['FN'], $_REQUEST['detail']);
} else {
	$_REQUEST['FN']--;
	GenerateRSSFeed($_REQUEST['FN'],10);
}

function uselogger($comment,$dummy_extra_not_used){
	global $REMOTE_ADDR, $HTTP_SERVER_VARS, $actuser, $username, $user, $HTTP_USER_AGENT,$name;

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
}
?>