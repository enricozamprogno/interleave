<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This file handles personalised start pages
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
require_once("initiate.php");
ShowHeaders();

if ($_REQUEST['done_subm'] == "True") {

	if ($_REQUEST['sticky'] == "") {
		$_REQUEST['sticky'] = "n";
	}
	if ($_REQUEST['hideoverdue'] == "") {
		$_REQUEST['hideoverdue'] = "n";
	}

	mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "loginusers SET USEDASHBOARDASENTRY='" . mres($_REQUEST['sticky']) . "' WHERE id='" . mres($GLOBALS['USERID']) . "'", $db);
	mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "loginusers SET HIDEOVERDUEFROMDUELIST='" . mres($_REQUEST['hideoverdue']) . "',IMPORTANTENTITIES='" . mres($_REQUEST['importantentities']) . "' WHERE id='" . mres($GLOBALS['USERID']) . "'", $db);

	$GLOBALS['UC']['USEDASHBOARDASENTRY']    = $_REQUEST['sticky'];
	$GLOBALS['UC']['HIDEOVERDUEFROMDUELIST'] = $_REQUEST['hideoverdue'];
	$GLOBALS['UC']['IMPORTANTENTITIES']      = $_REQUEST['importantentities'];

	ExpireDashboardCache();


}

if (strlen($_GET['change_language']) > 2) {
	// personal language setting changed
	$sql= "UPDATE " . $GLOBALS['TBL_PREFIX'] . "loginusers SET exptime='" . mres($_REQUEST['change_language']) . "' WHERE id='" . mres($GLOBALS['USERID']) . "'";
	mcq($sql,$db);
	$GLOBALS['language'] = $_GET['change_language'];
	ExpireDashboardCache();
}

ShowDashboard();


EndHTML();
?>