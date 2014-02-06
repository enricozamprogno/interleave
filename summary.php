<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This file does several things :)
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */

require("initiate.php");
require("show_entitylist.php");
ShowHeaders();

if ($_REQUEST['sta']) {
	

	print "<table class=\"summarylisttable\">";
	
	$tmp = GetAttribute("user", "SummarySearchWords", $GLOBALS['USERID']); 
	if (!in_array(trim($_REQUEST['sta']), $tmp)) {
		$tmp[] = trim($_REQUEST['sta']);
		SetAttribute("user", "SummarySearchWords", $tmp, $GLOBALS['USERID']);
	}

	$cl = GetClearanceLevel();

	if ($_REQUEST['include_deleted'] == "yes" && !in_array("NoViewDeleted", $cl)) {
		$includedeleted = true;
	} else {
		$includedeleted = false;
	}
	$results = false;

	$default_onclick = "$('.summarylist').hide();";

	$searched = "\"" . str_replace(" " , "\" + \"", htme($_REQUEST['sta']));
	$searched = str_replace("+ \"-", " <i>excluding</i> \"", $searched) . "\"";

	$list_orig = db_GetFlatArray(str_replace($GLOBALS['TBL_PREFIX'] . "entity.*", $GLOBALS['TBL_PREFIX'] . "entity.eid", NormalSearch($includedeleted, $_REQUEST['sta'], false, false)));
	$list = array();
	foreach ($list_orig AS $eid) {
		if (CheckEntityAccess($eid) != "nok") {
			$list[] = $eid;
		}
	}
	$toshow = "";
	print "<tr><td style=\"vertical-align: top\" class=\"nwrp\">";
	ShowSummaryForm();
	print "<br><br>";
	if (count($list) > 0) {
		print "<li class=\"summaryli\" onclick=\"" . $default_onclick . "toggleLayer('entitylistresult')\">" . $lang['entities'] . " (" . count($list) . ")</li>";
		$toshow .= "<div id=\"entitylistresult\" class=\"summarylist\">";
		$ins = "";
		if ($includedeleted) {
			$ins = "&includedeleted=true";
		} 
		$toshow .= AjaxBox("ShowEntityList", true, "&NoSelection=1&filter_id=SummaryEntityList&Source=" . PushStashValue(NormalSearch($includedeleted, $_REQUEST['sta'], false, false)) . $ins, false, false);
		$toshow .= "</div>";
		$results = true;
	}
	$list = "";
	$list_orig = db_GetFlatArray(str_replace($GLOBALS['TBL_PREFIX'] . "entity.*", $GLOBALS['TBL_PREFIX'] . "entity.eid", NormalCustomerSearch($_REQUEST['sta'], false)));
	$list = array();
	foreach ($list_orig AS $cid) {
		if (CheckCustomerAccess($cid) != "nok") {
			$list[] = $eid;
		}
	}
	if (count($list) > 0) {
		print "<li class=\"summaryli\" onclick=\"" . $default_onclick . "toggleLayer('customerlistresult')\">" . $lang['customers'] . " (" . count($list) . ")</li>";
		$toshow .= "<div id=\"customerlistresult\" class=\"summarylist\" style=\"display: none;\">";
		$toshow .= AjaxBox("ShowCustomerList", true, "&NoSelection=1&&Source=" . PushStashValue(NormalCustomerSearch($_REQUEST['sta'], false)) . $uri);
		$toshow .= "</div>";
		$results = true;
	}


	$tmp = GetFlextableDefinitions();

	foreach ($tmp AS $ft) {
		if (GetAttribute("flextable", "IncludeInSystemWideSearches", $ft['recordid']) != "No" && $ft['exclude_from_rep'] == "n" && CheckFlextableAccess($ft['recordid']) != "nok") {
			$list_orig = db_GetFlatArray(FlextableSearch($ft['recordid'], $_REQUEST['sta']));
			$list = array();
			foreach ($list_orig AS $ftrecord) {
				if (CheckFlextableRecordAccess($ft['recordid'], $ftrecord) != "nok") {
					$list[] = $eid;
				}
			}
			if (count($list) > 0) {
				print "<li class=\"summaryli\" onclick=\"" . $default_onclick . "toggleLayer('ft" . $ft['recordid'] . "results')\">" . htme($ft['tablename']) . " (" . count($list) . ")</li>";
				$toshow .= "<div id=\"ft" . $ft['recordid'] . "results\" class=\"summarylist\" style=\"display: none;\">";
				$toshow .= AjaxBox("ReturnCompleteFlextable", true, "&NoSelection=1&&ShowTable=" . $ft['recordid'] . "&Source=" . PushStashValue(FlextableSearch($ft['recordid'], $_REQUEST['sta'])));
				$toshow .= "</div>";
				$results = true;
			}
		}
	}

	if (!$results) {
		print "<br>" . $lang['noresults'];
	} else {
		print "</td><td style=\"vertical-align: top\">" . $toshow . "</td></tr>";
	}
	print "</table>";

} else {

	ShowSummaryForm();
}

EndHTML();

function ShowSummaryForm() {
	global $lang;

		
		print '<form id="searchtroughall" action="" method="post">';
		print '<div class="showinline">';
		print '<img src="images/searchbox.png" alt="" class="search_img">';
		print '<input class="search_input autocomplete" type="search" name="sta" id="summarysearch" value="' . htme($_REQUEST["sta"]) . '">';
		print '</div><input type="submit" value="' . $lang['go'] . '">';

		$cl = GetClearanceLevel();
		if (in_array("NoViewDeleted", $cl)) {
		} else {
			if ($_REQUEST['include_deleted'] == "yes") $ins2 = "checked='checked'";
			print "<br><br>" . $lang['incldel'] . ": <input type='checkbox' name='include_deleted' value='yes' " . $ins2 . ">";
		}
		print "</form><br>";

}
?>