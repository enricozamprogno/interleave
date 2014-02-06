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
MustBeAdmin();
if ($_REQUEST['submitted']) {
		if (!is_array($_REQUEST['accessarr_ro'])) {
			$_REQUEST['accessarr_ro'] = array();
		}
		if (!is_array($_REQUEST['accessarr_full'])) {
			$_REQUEST['accessarr_full'] = array();
		}
		if (!is_array($_REQUEST['accessarr_owner'])) {
			$_REQUEST['accessarr_owner'] = array();
		}
		for ($i=0;$i<sizeof($_REQUEST['accessarr_full']);$i++) {
		//	if (@in_array("fa_" . $_REQUEST['accessarr_ro'][$i],$_REQUEST['accessarr_full'])) {
					array_push($_REQUEST['accessarr_ro'], $_REQUEST['accessarr_full'][$i]);
					// Add view-rights for everybody who has add/edit rights (logically)
					array_push($_REQUEST['accessarr_ro'], str_replace("fa_", "", $_REQUEST['accessarr_full'][$i]));
		//	}
		}
		for ($i=0;$i<sizeof($_REQUEST['accessarr_owner']);$i++) {
					array_push($_REQUEST['accessarr_ro'], str_replace("to_", "", $_REQUEST['accessarr_owner'][$i]));
					array_push($_REQUEST['accessarr_ro'], str_replace("to_", "fa_", $_REQUEST['accessarr_owner'][$i]));
					array_push($_REQUEST['accessarr_ro'], $_REQUEST['accessarr_owner'][$i]);
		}
		$acr = serialize($_REQUEST['accessarr_ro']);
		mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "flextabledefs SET accessarray='" . mres($acr) . "' WHERE recordid='" . mres($_REQUEST['submitted']) . "'", $db);
		
		$_REQUEST['field'] = $_REQUEST['submitted'];
		?>
		<script type="text/javascript">
		<!--
			parent.$.fancybox.close();
		//-->
		</script>
		<?php
}
$ef_name = db_GetRow("SELECT tablename, accessarray FROM " . $GLOBALS['TBL_PREFIX'] . "flextabledefs WHERE recordid='" . mres($_REQUEST['field']) . "'");
$accarr = array();
$accarr = unserialize($ef_name['accessarray']);
print "<form id='EditAccessRights' method='post' action=''><div class='showinline'>";
print "<h1>Access rights for table " . $_REQUEST['field'] . "&nbsp;" . $ef_name['tablename'] . "</h1><h2>Uncheck all boxes to disable detailed access restrictions.";
print "Use the 'table owner' property to grant rights to all records in this table regardless of record access control.</h2>";
print "<table class='crm' width='100%'>";
print "<thead><tr><td colspan='2'><strong>Groups</strong></td><td>Visible</td><td>Add/edit</td><td class='nwrp'>Table owner</td></tr></thead>";
$res = mcq("SELECT name, id FROM " . $GLOBALS['TBL_PREFIX'] . "userprofiles ORDER BY name",$db);
foreach (GetGroups() AS $row) {
	unset($mem);
	$members = GetProfileMembers($row['id']);
	foreach ($members AS $member) {
		$mem .= GetUserName($member) . "<br>";
	}
	$ttc = "<img src='images/info.gif' " . PrintToolTipCode($mem) . " alt=''> <a style='cursor: help'" . PrintToolTipCode($mem) . "> [members] </a>";
	print "<tr><td colspan='1'>" . $row['name'] . "</td><td>" . $ttc . "</td>";
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
	$toid = "to_P" . $row['id'];
	if (@in_array($toid,$accarr)) {
		$ins3 = "checked='checked'";
	} else {
		unset($ins3);
	}

	print "<td><input type='checkbox' " . $ins1 . " name='accessarr_ro[]' class='crm' value='" . $id . "'></td>";
	print "<td><input type='checkbox' " . $ins2 . " name='accessarr_full[]' value='" . $faid . "'></td>";
	print "<td><input type='checkbox' " . $ins3 . " name='accessarr_owner[]' value='" . $toid . "'></td></tr>";
}
$stp = "<thead><tr><td colspan='2'><strong>Group extra fields</strong></td><td>Visible</td><td>Add/edit</td><td class='nwrp'>Table owner</td></tr></thead>";

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
			$stp .= "<td><input type='checkbox' " . $ins3 . " name='accessarr_owner[]' value='" . $toid . "'></td></tr>";
			$some = true;
		
		}
	}
}
if ($some) {
	print $stp;
}

print "<tr><td colspan='5' align='right'><br><input type='button' name='bla' value='Cancel' onclick='parent.$.fancybox.close();'>&nbsp;<input type='submit' value='Save and close'><br>&nbsp;</td></tr>";
$stp = "<thead><tr><td colspan='2'><strong>User extra fields</strong></td><td>Visible</td><td>Add/edit</td><td>Table owner</td></tr></thead>";
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
			$stp .= "<td><input type='checkbox' " . $ins3 . " name='accessarr_owner[]' value='" . $toid . "'></td></tr>";
			$some = true;
		
		}
	}
}
if ($some) {
	print $stp;
}
print "<thead><tr><td colspan='2'><strong>Users</strong></td><td>Visible</td><td>Add/edit</td><td>Table owner</td></tr></thead>";
$res = mcq("SELECT name, id FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE name NOT LIKE 'deleted_user_%' ORDER BY name",$db);
while ($row = mysql_fetch_array($res)) {
	print "<tr><td>" . $row['name'] . "</td><td>" . GetUserName($row['id']) . "</td>";
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
	$toid = "to_U" . $row['id'];
	if (@in_array($toid,$accarr)) {
		$ins3 = "checked='checked'";
	} else {
		unset($ins3);
	}

	print "<td><input type='checkbox' " . $ins1 . " name='accessarr_ro[]' value='" . $id . "'></td>";
	print "<td><input type='checkbox' " . $ins2 . " name='accessarr_full[]' value='" . $faid . "'></td>";
	print "<td><input type='checkbox' " . $ins3 . " name='accessarr_owner[]' value='" . $toid . "'></td></tr>";
}
print "<tr><td colspan='5' align='right'><br><input type='button' name='bla' value='Cancel' onclick='parent.$.fancybox.close();'>&nbsp;<input type='hidden' name='submitted' value='" . $_REQUEST['field'] . "'><input type='submit' value='Save and close'><br>&nbsp;</td></tr>";
print "</table>";
print "</div></form>";
print "</div>";
EndHTML();
?>