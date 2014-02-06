<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is for saving dashboard elements
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
require_once("initiate.php");

$_REQUEST['keeplocked'] = true;
$_REQUEST['AjaxAssist'] = true;
$_REQUEST['RSS'] = true;

if ($_REQUEST['msg']) {
	$tmp = unserialize(GetSetting("CHAT_HISTORY"));
	if (!is_array($tmp)) $tmp = array();
	array_push($tmp, "<span class='INTLV_ChatTextName'>" . GetUserName($GLOBALS['USERID']) . " " . date("H:i") . "h: </span> " . htme($_REQUEST['msg']) . "<br>");
	$newtmp = array();
	for ($x = sizeof($tmp) - 10; $x < sizeof($tmp) ; $x ++) {
		$newtmp[] = $tmp[$x];
	}
	
	UpdateSetting("CHAT_HISTORY", serialize($newtmp));

	print "<span class='INTLV_ChatTextName'>" . GetUserName($GLOBALS['USERID']) . " " . date("H:i") . "h: </span> " . $_REQUEST['msg'] . "<br>";
} elseif ($_REQUEST['check']) {

		$tmp = unserialize(GetSetting("CHAT_HISTORY"));
		foreach ($tmp AS $row) {
			$tot .= $row;
		}

		$md = strlen($tot);

		if ($_REQUEST['check'] != $md) {
			print $tot;
			//print $_REQUEST['check'] . " != " . $md;
		} else {
			print "same";
		}
}
EndHTML(false);

?>