 <?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 info@interleave.nl
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This is the page that pops up when setting detailed menu item access restrictions
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

$GLOBALS['PersonalTabs'] = unserialize(GetSetting("PersonalTabs"));



if (isset($_REQUEST['submitted'])) {

		if (!is_array($_REQUEST['accessarr_ro'])) {
			$_REQUEST['accessarr_ro'] = array();
		}
		for ($i=0;$i<sizeof($_REQUEST['accessarr_full']);$i++) {
					array_push($_REQUEST['accessarr_ro'], $_REQUEST['accessarr_full'][$i]);
					array_push($_REQUEST['accessarr_ro'], str_replace("fa_", "", $_REQUEST['accessarr_full'][$i]));
		}


		$GLOBALS['PersonalTabs'][$_REQUEST['item']]['accarr'] = serialize($_REQUEST['accessarr_ro']);

		UpdateSetting("PersonalTabs", serialize($GLOBALS['PersonalTabs']));

		?>
		<script type="text/javascript">
		<!--
			 parent.$.fancybox.close();
		//-->
		</script>
		<?php
}


$accarr =unserialize($GLOBALS['PersonalTabs'][$_REQUEST['item']]['accarr']);


print "<form id='EditAccessRights' method='post' action=''><div class='showinline'>";
print "<table width='95%'><tr><td>&nbsp;&nbsp;</td><td>";
print "<h1>Access rights for menu item " . $_REQUEST['item'] . "</h1><h2>" . $GLOBALS['PersonalTabs'][$_REQUEST['item']]['name'] . "</h2><br><br>Uncheck all boxes to disable detailed access restrictions.<br>";
print "<table class='crm' width='100%'>";
print "<tr><td colspan='2'><strong>Groups</strong></td><td>Show</td></tr>";
foreach (GetGroups() AS $row) {
	unset($mem);
	$members = GetProfileMembers($row['id']);
	foreach ($members AS $member) {
		$mem .= GetUserName($member) . "<br>";
	}
	$ttc = "<img src='images/info.gif' " . PrintToolTipCode($mem) . " alt=''> <a style='cursor: help'" . PrintToolTipCode($mem) . "> [members] </a>";
	print "<tr><td colspan='1'>" . $row['name'] . "</td><td>" . $ttc . "</td>";
	$id = "P" . $row['id'];
	if (in_array($id,$accarr)) {
		$ins1 = "checked='checked'";
	} else {
		unset($ins1);
	}
	print "<td><input type='checkbox' " . $ins1 . " name='accessarr_ro[]' class='crm' value='" . $id . "'></td>";

}
print "<tr><td colspan='4' align='right'><br><input type='button' name='bla' value='Cancel' onclick='parent.$.fancybox.close();'>&nbsp;<input type='submit' value='Save and close'><br>&nbsp;</td></tr>";
print "<tr><td colspan='2'><strong>Users</strong></td><td>Show</td></tr>";
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
	print "<td><input type='checkbox' " . $ins1 . " name='accessarr_ro[]' value='" . $id . "'></td>";
	//print "<td><input type='checkbox' " . $ins2 . " name='accessarr_full[]' value='" . $faid . "'></td></tr>";
}
print "<tr><td colspan='4' align='right'><br><input type='button' name='bla' value='Cancel' onclick='parent.$.fancybox.close();'>&nbsp;<input type='hidden' name='submitted' value='" . $_REQUEST['item'] . "'><input type='submit' value='Save and close'><br>&nbsp;</td></tr>";
print "</table>";
print "</fieldset></td></tr></table>";
print "</div></form></div>";
EndHTML();
?>