<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 info@interleave.nl
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This is the page that pops up when setting detailed extra field access restrictions
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
if (CheckFunctionAccess("ExtrafieldAdmin") == "nok" ) {
	PrintAD("Access to this page/function denied.");
} else {
	if ($_REQUEST['submitted']) {
			if (!is_array($_REQUEST['accessarr_ro'])) {
				$_REQUEST['accessarr_ro'] = array();
			}
			for ($i=0;$i<sizeof($_REQUEST['accessarr_full']);$i++) {
						array_push($_REQUEST['accessarr_ro'], $_REQUEST['accessarr_full'][$i]);
						array_push($_REQUEST['accessarr_ro'], str_replace("fa_", "", $_REQUEST['accessarr_full'][$i]));
			}
			$acr = serialize($_REQUEST['accessarr_ro']);
			if ($acr == "a:0:{}") $acr = "";
			mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "extrafields SET accessarray='" . mres($acr) . "' WHERE id='" . mres($_REQUEST['submitted']) . "'", $db);
			//print("UPDATE " . $GLOBALS['TBL_PREFIX'] . "extrafields SET accessarray='" . mres($acr) . "' WHERE id='" . $_REQUEST['submitted']) . "'";
			$_REQUEST['field'] = $_REQUEST['submitted'];
			?>
			<script type="text/javascript">
			<!--
				parent.$.fancybox.close();
			//-->
			</script>
			<?php
	}
	$ef_name = db_GetRow("SELECT name, accessarray FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE id='" . mres($_REQUEST['field']) . "'");
	$accarr = array();
	$accarr = unserialize($ef_name['accessarray']);

	print "<form id='EditAccessRights' method='post' action=''><div class='showinline'>";
	print "<br><table style='width: 95%;'><tr><td>&nbsp;&nbsp;</td><td>";
	print "<h1>Access rights for field " . $_REQUEST['field'] . ": &quot;" . $ef_name['name'] . "&quot;</h1><h2>Uncheck all boxes to disable detailed access restrictions</h2>";
	if (in_array("AllCanSee", $accarr)) {
		print "Check this box to make this field always visible for everybody: <input type='checkbox' checked='checked' name='accessarr_ro[]' value='AllCanSee'> <br><br>";
	} else {
		print "Check this box to make this field always visible for everybody: <input type='checkbox' name='accessarr_ro[]' value='AllCanSee'> <br><br>";
	}

	print "<table class='crm' width='100%'>";
	print "<thead><tr><td colspan='2'><strong>Groups</strong></td><td>Visible</td><td>Modify</td></tr></thead>";

	$res = mcq("SELECT name, id FROM " . $GLOBALS['TBL_PREFIX'] . "userprofiles ORDER BY name",$db);
	foreach (GetGroups() AS $row) {
		$members = GetProfileMembers($row['id']);
		foreach ($members AS $member) {
			$mem .= htme(GetUserName($member)) . "<br>";
		}
		$ttc = "<img src='images/info.gif' " . PrintToolTipCode($mem) . " alt=''> <a style='cursor: help'" . PrintToolTipCode($mem) . "> [members] </a>";
		print "<tr><td colspan='1'>" . htme($row['name']) . "</td><td>" . $ttc . "</td>";
		$id = "P" . $row['id'];
		if (@in_array($id,$accarr)) {
			$ins1 = "checked='checked'";
		} else {
			unset($ins1);
		}
		$faid = "fa_P" . $row['id'];
		if (@in_array($faid,$accarr)) {
			$ins2 = "checked='checked'";
		} else {
			unset($ins2);
		}
		print "<td><input type='checkbox' " . $ins1 . " name='accessarr_ro[]' class='crm' value='" . $id . "'></td>";
		print "<td><input type='checkbox' " . $ins2 . " name='accessarr_full[]' value='" . $faid . "'></td></tr>";
	}
	$stp = "<thead><tr><td colspan='2'><strong>Group extra fields</strong></td><td>Visible</td><td>Add/edit</td></tr></thead>";

	$list = GetExtraGroupFields();
	foreach ($list AS $field) {
		if ($field['fieldtype'] == "drop-down") {
			$options = unserialize($field['options']);
			foreach ($options AS $option) {
				$id = "EF" . $field['id'] . "|" . htme($option) . "|P" . $row['id'];
				if (@in_array($id,$accarr)) {
					$ins1 = "checked='checked'";
				} else {
					unset($ins1);
				}
				$faid = "EF" . $field['id'] . "|" . htme($option) . "|fa_P" . $row['id'];
				if (@in_array($faid,$accarr)) {
					$ins2 = "checked='checked'";
				} else {
					unset($ins2);
				}
				$toid = "EF" . $field['id'] . "|" . htme($option) . "|to_P" . $row['id'];
				if (@in_array($toid,$accarr)) {
					$ins3 = "checked='checked'";
				} else {
					unset($ins3);
				}
				$stp .= "<tr><td>When group field &quot;" . htme($field['name']) . "&quot; contains value</td><td>" . htme($option) . "</td>";
				$stp .= "<td><input type='checkbox' " . $ins1 . " name='accessarr_ro[]' class='crm' value='" . $id . "'></td>";
				$stp .= "<td><input type='checkbox' " . $ins2 . " name='accessarr_full[]' value='" . $faid . "'></td>";
				$some = true;
			
			}
		}
	}
	if ($some) {
		print $stp;
}

	print "<tr><td colspan='4' align='right'><br><input type='button' name='bla' value='Cancel' onclick='parent.$.fancybox.close();'>&nbsp;<input type='submit' value='Save and close'><br>&nbsp;</td></tr>";
	$stp = "<thead><tr><td colspan='2'><strong>User extra fields</strong></td><td>Visible</td><td>Modify</td></tr></thead>";
$list = GetExtraUserFields();
foreach ($list AS $field) {
	if ($field['fieldtype'] == "drop-down") {
		$options = unserialize($field['options']);
		foreach ($options AS $option) {
			$id = "EF" . $field['id'] . "|" . htme($option) . "|U" . $row['id'];
			if (@in_array($id,$accarr)) {
				$ins1 = "checked='checked'";
			} else {
				unset($ins1);
			}
			$faid = "EF" . $field['id'] . "|" . htme($option) . "|fa_U" . $row['id'];
			if (@in_array($faid,$accarr)) {
				$ins2 = "checked='checked'";
			} else {
				unset($ins2);
			}
			$toid = "EF" . $field['id'] . "|" . htme($option) . "|to_U" . $row['id'];
			if (@in_array($toid,$accarr)) {
				$ins3 = "checked='checked'";
			} else {
				unset($ins3);
			}
			$stp .= "<tr><td>When user field &quot;" . htme($field['name']) . "&quot; contains value</td><td>" . htme($option) . "</td>";
			$stp .= "<td><input type='checkbox' " . $ins1 . " name='accessarr_ro[]' class='crm' value='" . $id . "'></td>";
			$stp .= "<td><input type='checkbox' " . $ins2 . " name='accessarr_full[]' value='" . $faid . "'></td>";
			$some = true;
		
		}
	}
}
if ($some) {
	print $stp;
}
	print "<thead><tr><td colspan='2'><strong>Users</strong></td><td>Visible</td><td>Modify</td></tr></thead>";
	$res = mcq("SELECT name, id FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE name NOT LIKE 'deleted_user_%' ORDER BY name",$db);
	while ($row = mysql_fetch_array($res)) {
		print "<tr><td>" . htme($row['name']) . "</td><td>" . htme(GetUserName($row['id'])) . "</td>";
		$id = "U" . $row['id'];
		if (@in_array($id,$accarr)) {
			$ins1 = "checked='checked'";
		} else {
			unset($ins1);
		}
		$faid = "fa_U" . $row['id'];
		if (@in_array($faid,$accarr)) {
			$ins2 = "checked='checked'";
		} else {
			unset($ins2);
		}
		print "<td><input type='checkbox' " . $ins1 . " name='accessarr_ro[]' value='" . $id . "'></td>";
		print "<td><input type='checkbox' " . $ins2 . " name='accessarr_full[]' value='" . $faid . "'></td></tr>";
	}
	print "<tr><td colspan='4' align='right'><br><input type='button' name='bla' value='Cancel' onclick='parent.$.fancybox.close();'>&nbsp;<input type='hidden' name='submitted' value='" . $_REQUEST['field'] . "'><input type='submit' value='Save and close'><br>&nbsp;</td></tr>";
	print "</table>";
	print "</td></tr></table>";
	print "</div></form>";
}
print "</div>";
EndHTML();
?>