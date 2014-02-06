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


$top = $_REQUEST['top'];
$left = $_REQUEST['left'];
$width = $_REQUEST['width'];
$height = $_REQUEST['height'];

$element = $_REQUEST['el'];
$hidden = $_REQUEST['hidden'];



if (strlen($element) > 0) {

	$tmp = db_GetRow("SELECT LASTFILTER FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE id='" . mres($GLOBALS['USERID']) . "'");
	$ell = unserialize($tmp['LASTFILTER']);

	if (is_numeric($top) && $top != "") {
		$ell['dashboard_element_positions_INTLV'][$element]['top'] = $top;
	} 
	if (is_numeric($left) && $left != "") {
		$ell['dashboard_element_positions_INTLV'][$element]['left'] = $left;
	}
	if (is_numeric($width) && $width != "") {
		$ell['dashboard_element_positions_INTLV'][$element]['width'] = $width;
	}
	if (is_numeric($height) && $height != "") {
		$ell['dashboard_element_positions_INTLV'][$element]['height'] = $height;
	}
	$ell['dashboard_element_positions_INTLV'][$element]['hidden'] = $hidden;

	mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "loginusers SET LASTFILTER='" . mres(serialize($ell)) . "' WHERE id='" . mres($GLOBALS['USERID']) . "'", $db);

	//print "LEFT: $left TOP; $top HIDDEN: $hidden EL: $el WI: $width HEI: $height";
	print "ok";

	
} else {
	PrintHTMLHeader();
	PrintHeaderJavascript();
	print "</head><body><div>";
	PrintAD("This function needs an element.");
	EndHTML();
}

?>
