<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 info@interleave.nl
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * Handles requests for diary extra fields. Needs to be opened in an IFRAME.
 * Eternal fame goes to snowboarder04 for bringing up the idea.
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
require_once("initiate.php");
$_REQUEST['nonavbar'] = 1;
$nonavbar = 1;
ShowHeaders();

unset($ins);

$type		= $_REQUEST['type'];
$eid		= $_REQUEST['eid'];
$fieldname	= $_REQUEST['fieldname'];
$flextable  = $_REQUEST['FlexTable'];

if ($type != GetExtraFieldTableType($fieldname)) {
	PrintAD("Acces denied / type error");
} else {



	if ($type == "customer") {
		$x = CheckCustomerAccess($eid);
		if ($x == "nok") {
			PrintAD("Access to this customer is denied");
			EndHTML();
			exit;
		} elseif ($x == "readonly") {
			$readonly = true;
		}
	} elseif (is_numeric($flextable)) {
		if (CheckFlexTableAccess($flextable) == "ok") {
			qlog(INFO, "Printing diary field for FlexTable " . $flextable);
			$type = "FlexTable";
		} else {
			print "No access";
			exit;
		}
	} else {
		$x = CheckEntityAccess($eid);

		if ($x == "nok") {
			if (in_array("CommentsAdd", $cl)) {
				// all ok, this user is a "limited user"
			} else {
				PrintAD("Access to this entity is denied");
				EndHTML();
				exit;
			}
		} elseif ($x == "readonly") {
			if (in_array("CommentsAdd", $cl)) {
				// all ok, this user is a "limited user"
			} else {
				$readonly = true;
				print "ROOO";
			}
		}
	}
	if ($eid == 0 || $eid == "") {
		$readonly = true;
	}
	$c_id = GetEntityCustomer($eid);
	if ($_REQUEST['diary_addition'] && !$readonly) {
		$_REQUEST['diary_addition'] = nl2br($_REQUEST['diary_addition']);

	// hier
		if ($_REQUEST['FlexTable']) {
			$type = "flextable" . $_REQUEST['FlexTable'];
		}

		UpdateDiaryField($eid, $fieldname, $type, $_REQUEST['diary_addition']);


		?>
		<script type="text/javascript">
		<!--
			parent.$.fancybox.close();
			parent.location = parent.location;
		//-->
		</script>
		<?php

	} elseif ($_REQUEST['add']) {
		print "<table class='crm'>";
		print "<tr><td><form id='diaryform' method='post' action='diary.php'><div class='showinline'>";
		print "<input type='hidden' name='eid' value='" . $eid . "'>";
		print "<input type='hidden' name='fieldname' value='" . $fieldname . "'>";
		print "<input type='hidden' name='type' value='" . $type . "'>";
		if ($_REQUEST['FlexTable']) {
			print "<input type='hidden' name='FlexTable' value='" . $_REQUEST['FlexTable'] . "'>";
		}
		print "<textarea name='diary_addition' cols='60'></textarea>&nbsp;<input type='submit' value='" . $lang['go'] . "'></div></form></td></tr>";
		print "</table>";
	} else {
		if ($type == "customer") {
			$ctype = " type='cust'";
			$table = "customer";
			$id = "id";
		} elseif ($_REQUEST['FlexTable']) {
			$ctype = " type='flextable" . $_REQUEST['FlexTable']. "'";
			$table = "flextable" . $_REQUEST['FlexTable'];
			$id = "recordid";
		} else {
			$ctype = " (type='entity' OR type='')";
			$table = "entity";
			$id = "eid";

		}
		$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE id='" . mres($fieldname) . "' AND tabletype='" . mres($type) . "'";
		$result= mcq($sql,$db);
		$extrafield = mysql_fetch_array($result);
		if (is_numeric($eid)) {
			$sql = "SELECT EFID" . $fieldname . " AS value FROM " . $GLOBALS['TBL_PREFIX'] . $table . " WHERE " . $id . "='" . mres($eid) . "'";
			$result= mcq($sql,$db);
			$ef_array = mysql_fetch_array($result);

		}
		$val = $ef_array['value'];
		$val_array = unserialize($val);
		print "<table class='crm' width='100%'>";
		print "<tr><td colspan='3'>";
		if (!$readonly) {
			if ($_REQUEST['FlexTable']) {
				$ins = "&FlexTable=" . $_REQUEST['FlexTable'];
			} else {
				unset($ins);
			}
			print "<a href='javascript:popdiarywindow(\"diary.php?add=1&eid=" . $_REQUEST['eid'] . "&fieldname=" . $_REQUEST['fieldname'] . "&type=" . $_REQUEST['type'] . $ins . "\");'>" . $lang['add'] . "</a>";
		} else {
			print "(read-only)";
		}
		print "</td></tr>";
		for ($i=sizeof($val_array)-1;$i>=0;$i--) {
			$row = $val_array[$i];
			$date = TransformDate(date("d-m-Y", $row[0]));
			$date .= " " . date("H:i", $row[0]);
			if ($row[2]) {
				if ($grey) {
					print "<tr><td valign='top'>" . GetUserName($row[1]) . "</td><td valign='top'>" . $date . "</td><td>" . ($row[2]) . "</td></tr>";
					unset($grey);
				} else {
					print "<tr style='background: #E0E0E0'><td valign='top'>" . GetUserName($row[1]) . "</td><td valign='top'>" . $date . "</td><td>" . ($row[2]) . "</td></tr>";
					$grey = true;
				}
			}
		}
		print "</table>";
	}
	EndHTML();
}
?>