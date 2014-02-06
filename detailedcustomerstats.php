<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This is the detailed customer statistics plugin for Interleave
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
require_once("initiate.php");
ShowHeaders();

// This file should generate a detailed breakdown of customer data, including extra fields.
if ($_REQUEST['qid']) {
	$q = PopStashValue($_REQUEST['qid']);
	$andstring = " AND (" . $GLOBALS['TBL_PREFIX'] . "entity.eid=";
	foreach ($q AS $eid) {
			$andstring .= $eid ." OR " . $GLOBALS['TBL_PREFIX'] . "entity.eid=";
	}
	$andstring .= $q[0] . ")";
	$wherestring = "WHERE 1=1 " . $andstring;
	}
// Customer should be given (cid=[customer_id])
if ($_REQUEST['cid']) {

	$cid = $_REQUEST['cid'];

	$sql = "SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE CRMcustomer='" . mres($cid) . "' " . $andstring;
	$result= mcq($sql,$db);
	$row= mysql_fetch_array($result);
	$tot_ent = $row[0];

	$sql = "SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE deleted<>'y' AND CRMcustomer='" . mres($cid) . "' " . $andstring;
	$result= mcq($sql,$db);
	$row= mysql_fetch_array($result);
	$tot_open = $row[0];
	$sql = "SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE deleted='y' AND CRMcustomer='" . mres($cid) . "' " . $andstring;
	$result= mcq($sql,$db);
	$row= mysql_fetch_array($result);
	$tot_del = $row[0];

	$sql = "SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE closedate<>'' AND CRMcustomer='" . mres($cid) . "' " . $andstring;
	$result= mcq($sql,$db);
	$row= mysql_fetch_array($result);
	$tot_clos = $row[0];
	$tot_notclos = $tot_ent - $tot_clos;

	print "<table style='width: 70%;'><tr><td>&nbsp;&nbsp;</td><td>";
	print "<fieldset><legend>&nbsp;<img src='images/crmlogosmall.gif' alt=''>&nbsp;&nbsp;Statistic breakdown of customer: " . GetCustomerName($cid) . "&nbsp;";
	if ($_REQUEST['qid']) {
		print "<span class='noway'>(subset)</span>";
	}
	print "</legend>";
	print "<table cellspacing='0' cellpadding='4' style='width: 100%;' border='1'>";
	print "<tr><td>Total entities</td><td colspan='3'>" . $tot_ent . "</td></tr>";
	print "<tr><td>Open vs. deleted</td><td>Open: " . $tot_open . "</td><td>Deleted: " . $tot_del . "</td></tr>";
	print "<tr><td>Open vs. closed (having closedate)</td><td>Open: " . $tot_notclos . "</td><td>Closed: " . $tot_clos . "</td></tr>";
	print "<tr><td>Avg. age (non-deleted entities)</td><td colspan='3'>" . GetAvgEntityAge($cid, "not_del") . "</td></tr>";
	print "<tr><td>Avg. age (entities with closedate)</td><td colspan='3'>" . GetAvgEntityAge($cid, "with_closedate") . "</td></tr>";
	if ($tot_del > 0) {
		print "<tr><td>Avg. duration (deleted entities)</td><td colspan='3'>" . GetAvgEntityAge($cid, "del") . "</td></tr>";
		print "<tr><td>Avg. age/duration (all entities)</td><td colspan='3'>" . GetAvgEntityAge($cid) . "</td></tr>";
	}

	print "<tr><td colspan='4'><br><strong>" . $lang['status'] . " breakdown</strong></td>";
	$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "statusvars ORDER BY listorder, varname";
	$result= mcq($sql,$db);
    while ($e= mysql_fetch_array($result)) {

			$sql = "SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE status='" . mres($e['varname']) . "' AND CRMcustomer='" . mres($cid) . "' " . $andstring;
			$result1= mcq($sql,$db);
			$maxU1 = mysql_fetch_array($result1);
			$bla = $maxU1[0];
			$pc1 = ($tot_ent/100); // dit is 1 procent

			$pc2 = ($bla/100); // dit is 1 procent van not [deleted]

			$apc = @round($bla/$pc1); // dit is het percentage

			print "<tr><td style='background-color: " . $e['color'] . ";'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$e[varname]</td><td style='width: 20%;'>$bla</td><td style='width: 20%;'>$apc%</td></tr>";
			$totaal=$totaal+$bla;
	}
	print "<tr><td colspan='4'><br><strong>" . $lang['priority'] . " breakdown</strong></td>";
	$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "priorityvars ORDER BY listorder, varname";
	$result= mcq($sql,$db);
    while ($e= mysql_fetch_array($result)) {

			$sql = "SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE priority='" . mres($e['varname']) . "' AND CRMcustomer='" . mres($cid) . "' " . $andstring;
			$result1= mcq($sql,$db);
			$maxU1 = mysql_fetch_array($result1);
			$bla = $maxU1[0];
			$pc1 = ($tot_ent/100); // dit is 1 procent

			$pc2 = ($bla/100); // dit is 1 procent van not [deleted]

			$apc = @round($bla/$pc1); // dit is het percentage

			print "<tr><td style='background-color: " . $e['color'] . "'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $e['varname'] . "</td><td style='width: 20%;'>" . $bla . "</td><td style='width: 20%;'>" . $apc . "%</td></tr>";
			$totaal=$totaal+$bla;
	}

	print "<tr><td colspan='4'><br><strong>Extra field breakdown (drop-down fields only)</strong></td>";
	$f_ar = GetExtraFields();
	foreach ($f_ar AS $field) {
		if ($field['fieldtype'] == "drop-down") {
			print "<tr><td colspan='4'>" . $field['name'] . "</td></tr>";
			$tmp = unserialize($field['options']);
			foreach($tmp AS $option) {
				print "<td></td><td style='width: 20%;'>" . $option . "&nbsp;</td><td style='width: 20%;'>";

				$sql = "SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE " . $GLOBALS['TBL_PREFIX'] . "entity.CRMcustomer='" . mres($cid) . "' AND " . $GLOBALS['TBL_PREFIX'] . "entity.EFID" . $field['id'] . "='" . mres($option) . "' " . $andstring;
				$result1= mcq($sql,$db);
				$tmp2 = mysql_fetch_array($result1);
				print $tmp2[0];
				print "&nbsp; </td></tr>";
			}
			//<td style='width: 20%;'>" . $yt . "&nbsp;</td><td style='width: 20%;'>" . $yt . "&nbsp;</td></tr>";
		}
	}
	if ($_REQUEST['inc_detail']) {
			print "<tr><td colspan='2'><strong>Category breakdown</strong></td></tr>";
			$sql = "SELECT category, status, priority FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE CRMcustomer='" . mres($cid) . "' " . $andstring;
			$result= mcq($sql,$db);
			while ($row = mysql_fetch_array($result)) {
				print "<tr><td>" . $row['category'] . "</td><td>" . $row['status'] . "</td><td>" . $row['priority'] . "</td></tr>";
			}
			//print "</table>";

	}
	if ($GLOBALS['FormFinity'] == "Yes") {
		print "<tr><td><strong>Form breakdown</strong></td></tr>";
			print "<tr><td></td><td style='width: 20%;'>Default&nbsp;</td><td style='width: 20%;'>";
			$num = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE formid=0 AND CRMcustomer='" . mres($cid) . "' " . $andstring);
			print $num[0];
			$num[0] = 0;
			"</td></tr>";

		$res = mcq("SELECT templateid, template_subject FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML_FORM'", $db);
		while ($row = mysql_fetch_array($res)) {
			print "<tr><td></td><td style='width: 20%;'>" . $row['template_subject'] . "&nbsp;</td><td style='width: 20%;'>";
			$num = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE formid=" . $row['templateid'] . " AND CRMcustomer='" . mres($cid) . "' " . $andstring);

			print $num[0];
			$num[0] = 0;
			"</td></tr>";
		}
		print "</td></tr>";
	}
	print "</table>";
	print "</fieldset>";
	print "</td></tr></table>";
} else {
	print "<table><tr><td>&nbsp;&nbsp;&nbsp;<img src='images/error.gif' alt=''> " . $lang['noaction'] . " (cid).</td></tr></table>";
}
EndHTML();
?>