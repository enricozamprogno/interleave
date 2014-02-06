<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 info@interleave.nl
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This is the page that pops up when setting detailed module access restrictions
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
require_once("initiate.php");
$_REQUEST['nonavbar'] = 1;
ShowHeaders();
print "</div><div id=\"MainAdminContents\">";
MustBeAdmin();

$fields = array();

$fields['customer'] = $lang['customer'];
$fields['category'] = $lang['category'];
$fields['duedate'] = $lang['duedate'];
$fields['startdate'] = $lang['startdate'];
$fields['owner'] = $lang['owner'];
$fields['assignee'] = $lang['assignee'];
$fields['priority'] = $lang['priority'];
$fields['status'] = $lang['status'];

$tmp = GetExtraFields();
foreach ($tmp AS $field) {
	if (($field['fieldtype'] == "drop-down" || $field['fieldtype'] == "hyperlink" || $field['fieldtype'] == "textbox" || $field['fieldtype'] == "numeric" || strstr($field['fieldtype'], "User-list") || $field['fieldtype'] == "mail" || $field['fieldtype'] == "date" || $field['fieldtype'] == "List of all groups") && $field['israwhtml'] != "y") {
		$fields[$field['id']] = $field['name'];
	}
}


if ($_REQUEST['submitted']) {
		$tmp = GetExtraCustomerFields();
		foreach ($tmp AS $field) {
			if (($field['fieldtype'] == "drop-down" || $field['fieldtype'] == "hyperlink" || $field['fieldtype'] == "textbox" || $field['fieldtype'] == "numeric" || strstr($field['fieldtype'], "User-list") || $field['fieldtype'] == "mail" || $field['fieldtype'] == "date" || $field['fieldtype'] == "List of all groups") && $field['israwhtml'] != "y") {
				$fields[$field['id']] = $field['name'];
			}
		}
		$tables = GetFlexTableDefinitions();
		foreach ($tables AS $table) {
			$tmp = GetExtraFlextableFields($table['recordid']);
			foreach ($tmp AS $field) {
				if (($field['fieldtype'] == "drop-down" || $field['fieldtype'] == "hyperlink" || $field['fieldtype'] == "textbox" || $field['fieldtype'] == "numeric" || strstr($field['fieldtype'], "User-list") || $field['fieldtype'] == "mail" || $field['fieldtype'] == "date" || $field['fieldtype'] == "List of all groups") && $field['israwhtml'] != "y") {
					$fields[$field['id']] = $field['name'];
				}
			}
		}

		if (!is_array($_REQUEST['interactivefield'])) {
			$_REQUEST['interactivefield'] = array();
		}
		$newlist = array();
		for ($i=0;$i<sizeof($_REQUEST['interactivefield']);$i++) {
			foreach ($fields AS $name => $trans) {
				if ($_REQUEST['interactivefield'][$i] == $name) {
				array_push($newlist, $name);
				print "push " . $name;
				}
			}
		}
		$acr = serialize($newlist);
		if ($_REQUEST['type'] == "profile") {
			mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "userprofiles SET INTERACTIVEFIELDS='" . mres($acr) . "' WHERE id='" . mres($_REQUEST['submitted']) . "'", $db);
		} elseif ($_REQUEST['type'] == "user") {
			mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "loginusers SET INTERACTIVEFIELDS='" . mres($acr) . "' WHERE id='" . mres($_REQUEST['submitted']) . "'", $db);
		}

		?>
		<script type="text/javascript">
		<!--
			  parent.$.fancybox.close();
		//-->
		</script>
		<?php
}

if ($_REQUEST['type'] == "profile") {
	$cur = db_GetValue("SELECT INTERACTIVEFIELDS FROM " . $GLOBALS['TBL_PREFIX'] . "userprofiles WHERE id='" . mres($_REQUEST['account']) . "'");
} else {
	$cur = db_GetValue("SELECT INTERACTIVEFIELDS FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE id='" . mres($_REQUEST['account']) . "'");
}
$accarr = array();
$accarr = @unserialize($cur);

print "<form id='EditInteractiveFields' method='post' action=''><input type='hidden' name='submitted' value='" . $_REQUEST['account'] . "'><input type='hidden' name='type' value='" . $_REQUEST['type'] . "'><div class='showinline'>";
print "<table style='width: 95%;'><tr><td>&nbsp;&nbsp;</td><td>";
print "<h1>Select interactive fields for this " . htme($_REQUEST['type']) . "</h1>";
print "<h2>Interactive fields are fields which can be altered directly in the list by clicking on the value</h2>";

print "<table class='crm' style='width: 100%;'>";
print "<tr><td colspan='4' align='right'><br><input type='button' name='bla' value='Cancel' onclick='parent.$.fancybox.close();'>&nbsp;<input type='submit' value='Save and close'><br>&nbsp;</td></tr>";

print "<tr><td colspan='2'><strong>" . $lang['entities'] . "</strong></td></tr>";
print "<tr><td colspan='1'><strong>Field</strong></td><td><strong>Interactive</strong></td></tr>";

foreach ($fields AS $name => $trans) {
	unset($mem);

	print "<tr><td colspan='1'>" . $trans . "</td>";

	if (@in_array($name,$accarr)) {
		$ins1 = "checked='checked'";
	} else {
		unset($ins1);
	}

	print "<td><input type='checkbox' " . $ins1 . " name='interactivefield[]' class='crm' value='" . htme($name) . "'></td>";

}
print "<tr><td colspan='2'>&nbsp;</td></tr>";
print "<tr><td colspan='2'><strong>" . $lang['customers'] . "</strong></td></tr>";
print "<tr><td colspan='1'><strong>Field</strong></td><td><strong>Interactive</strong></td></tr>";
$fields = array();
$tmp = GetExtraCustomerFields();
foreach ($tmp AS $field) {
	if (($field['fieldtype'] == "drop-down" || $field['fieldtype'] == "hyperlink" || $field['fieldtype'] == "textbox" || $field['fieldtype'] == "numeric" || strstr($field['fieldtype'], "User-list") || $field['fieldtype'] == "mail" || $field['fieldtype'] == "date" || $field['fieldtype'] == "List of all groups") && $field['israwhtml'] != "y") {
		$fields[$field['id']] = $field['name'];
	}
}


foreach ($fields AS $name => $trans) {
	unset($mem);

	print "<tr><td colspan='1'>" . $trans . "</td>";

	if (@in_array($name,$accarr)) {
		$ins1 = "checked='checked'";
	} else {
		unset($ins1);
	}

	print "<td><input type='checkbox' " . $ins1 . " name='interactivefield[]' class='crm' value='" . htme($name) . "'></td>";

}

$tables = GetFlexTableDefinitions();
foreach ($tables AS $table) {
	print "<tr><td colspan='2'>&nbsp;</td></tr>";
	print "<tr><td colspan='2'><strong>" . htme($table['tablename']) . "</strong></td></tr>";
	print "<tr><td colspan='1'><strong>Field</strong></td><td><strong>Interactive</strong></td></tr>";
	$fields = array();
	$tmp = GetExtraFlextableFields($table['recordid']);
	foreach ($tmp AS $field) {
		if (($field['fieldtype'] == "drop-down" || $field['fieldtype'] == "hyperlink" || $field['fieldtype'] == "textbox" || $field['fieldtype'] == "numeric" || strstr($field['fieldtype'], "User-list") || $field['fieldtype'] == "mail" || $field['fieldtype'] == "date" || $field['fieldtype'] == "List of all groups") && $field['israwhtml'] != "y") {
			$fields[$field['id']] = $field['name'];
		}
	}


	foreach ($fields AS $name => $trans) {
		unset($mem);

		print "<tr><td colspan='1'>" . $trans . "</td>";

		if (@in_array($name,$accarr)) {
			$ins1 = "checked='checked'";
		} else {
			unset($ins1);
		}

		print "<td><input type='checkbox' " . $ins1 . " name='interactivefield[]' class='crm' value='" . htme($name) . "'></td>";
	}
}


print "<tr><td colspan='4' align='right'><br><input type='button' name='bla' value='Cancel' onclick='parent.$.fancybox.close();'>&nbsp;<input type='submit' value='Save and close'><br>&nbsp;</td></tr>";
print "</table>";
print "</td></tr></table>";
print "</div></form></div>";

EndHTML();
?>