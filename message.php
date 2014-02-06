<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This script handels administrative action requests
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
require_once("initiate.php");
ShowHeaders();

if ($_REQUEST['req_adm_act']) {
	$body  = "Administrative action request submitted by " . GetUserName($GLOBALS['USERID']) . "\n";
	$body .= "Repository: " . $_REQUEST['from_repos'] . "\n";
	$body .= "Location: " . $_REQUEST['url'] . "\n";
	$body .= "Message: " . $_REQUEST['message'] . "\n";
	$body .= "Body content:\n " . $_REQUEST['content'] . "\n";
	$body .= "\n\nTrace:\n" . PopStashValue($_REQUEST['trace']) . "\n";

	ProcessTriggers("admin_request",0,"Administrative trigger",$body);

	AddMessage("admin", $GLOBALS['USERID'], "Administrative action request", $body);
	print "<div class='printAd'><div class='ErrorMsg'>Administrative action request submitted.</div></div>";
	
}

EndHTML();
?>
