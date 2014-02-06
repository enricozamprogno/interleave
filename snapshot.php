<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * Configuration snap-shots / restore points
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
$small = $_REQUEST['small'];
$_GET['SkipMainNavigation'] = true;
require_once("initiate.php");
if ($_REQUEST['DownloadSnapshot']) {
	log_msg("Trying to download a complete database dump");
	MustBeAdmin();
	SafeModeInterruptCheck();
	header("Content-Type: text/sql");
	header("Content-Disposition: attachment; filename=snapshot" . $_REQUEST['DownloadSnapshot'] . ".sql" );
	header("Window-target: _top");
	$q = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "configsnapshots WHERE id='" . mres($_REQUEST['DownloadSnapshot']) . "'");

	print $q['config'];

	
	EndHTML(false);
	exit;
} elseif ($_REQUEST['dldump']) {
	MustBeAdmin();
	log_msg("Trying to download a complete database dump");
	SafeModeInterruptCheck();
	
	CreateConfigurationSnapshot($GLOBALS['ORIGINAL_REPOSITORY'], "", false, "dumpdb");
	log_msg("Complete database dump downloaded");
	EndHTML();
	exit;
} elseif ($_REQUEST['popup'] ==  "ndr" && !$_REQUEST['ParentAjexHandler'] && !$_REQUEST['bla']) {
	$_REQUEST['nonavbar'] = 1;
	$_REQUEST['popup'] = 1;
	ShowHeaders();
	MustBeAdmin();
	if (is_administrator()) {
		print ReturnAddRestorePointElement();
	}
	exit;
} elseif (!$small) {
	ShowHeaders();
	MustBeAdmin();
	SafeModeInterruptCheck();
	AdminTabs();
	MainAdminTabs("ieb");
} elseif ($_REQUEST['SNDescription'] && is_administrator()) {
	MustBeAdmin();
	$wholedb = false;
	CreateConfigurationSnapshot($GLOBALS['ORIGINAL_REPOSITORY'], $_REQUEST['SNDescription'], false, $_REQUEST['typeofsnapshot']);
	 if ($small) {
		print "<span style='color: #33FF00;'><strong>OK</strong></span> Restore point created!";
		exit;
	 }
} else {
	ShowHeaders();
	MustBeAdmin();
	SafeModeInterruptCheck();
	AdminTabs();
	MainAdminTabs("ieb");
}

MustBeAdmin();

if ($_REQUEST['DeleteRestorePoint']) {
	SafeModeInterruptCheck();
	mcq("DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "configsnapshots WHERE id='" . mres($_REQUEST['DeleteRestorePoint']) . "'", $db);

} elseif ($_REQUEST['ApplyRestorePoint']) {

	SafeModeInterruptCheck();
	RestoreConfigurationSnaphot($GLOBALS['ORIGINAL_REPOSITORY'], $_REQUEST['ApplyRestorePoint']);
	print "<span style='color: #33FF00;'><strong>OK</strong></span> Restore point applied (a safety copy was automatically created)";
	?>
	<script type="text/javascript">
		document.location = 'snapshot.php';
	</script>
	<?php
}
$tmp = db_GetArray("SELECT id, timestamp_last_change , comment, snapshottype, LENGTH(config) AS bytes FROM " .  $GLOBALS['TBL_PREFIX'] . "configsnapshots ORDER BY timestamp_last_change DESC");

$bla = db_GetRow("SELECT SUM(LENGTH(config) / 1024 / 1024) as totmbytes FROM " .  $GLOBALS['TBL_PREFIX'] . "configsnapshots");

if (!$small) print "<h1 class='h1interleave'>Configuration restore points</h1>";

print "Restore points are snapshots of all settings<br><br>";


if ($_REQUEST['bla']) {
	print "Create a new configuration restore point:<br>&nbsp;<br><form id='snnameform' method='get' action=''><div class='showinline'><input type='text' size='30' name='SNDescription' value='[description]'>&nbsp;<select name='typeofsnapshot'><option value='nousers'>Ex. users</option><option value='withusers'>Incl. users</option><option value='wholedb'>Whole database (excluding snapshots)</option></select><br>&nbsp;<br><input type='button' name='CreateSN' value='Create' onclick=\"refresh_" . $_REQUEST['AjaxHandler'] . "('&SNDescription=' + document.forms['snnameform'].elements['SNDescription'].value + '&typeofsnapshot=' + document.forms['snnameform'].elements['typeofsnapshot'].options[document.forms['snnameform'].elements['typeofsnapshot'].selectedIndex].value);\"></div></form>";
	//document.forms['chgbox'].elements['popselectbox'][document.forms['chgbox'].elements['popselectbox'].selectedIndex].value
} else {
	print "<a class='arrow' href='#' onclick='javascript:PopAddRestorePointWindow();'>Create new restore point</a><br>&nbsp;<br>";
}

if (!$small) {
	print "<table class='crm'>";
	print "<tr><thead><td>id</td><td>Date/time</td><td>Description</td><td>Snapshot type</td><td>Size</td><td>Delete</td><td>Apply</td><td>Download</td></tr></thead>";

	foreach ($tmp AS $row) {
		if ($row['snapshottype'] == "withusers") {
			$row['snapshottype'] = "Configuration snapshot with users & profiles";
		} elseif ($row['snapshottype'] == "nousers") {
			$row['snapshottype'] = "Configuration snapshot without users & profiles";
		} elseif ($row['snapshottype'] == "wholedb") {
			$row['snapshottype'] = "Complete database snapshot";
		}
		print "<tr><td>" . $row['id'] . "</td><td>" . $row['timestamp_last_change'] . "</td><td>" . $row['comment'] . "</td><td>" . $row['snapshottype'] . "</td><td>" . FormatNumber($row['bytes'] / 1024, 0) . "K</td><td><a href='snapshot.php?DeleteRestorePoint=" . $row['id'] . "'><img src='images/delete.gif' alt=''></td><td><a href='snapshot.php?ApplyRestorePoint=" . $row['id'] . "'>[apply]</a></td><td><a href='snapshot.php?DownloadSnapshot=" . $row['id'] . "'>[download]</a></td></tr>";
	}

	print "<tr><td></td><td></td><td></td><td></td><td>~ " . FormatNumber($bla[0], 0) . "M</td><td></td><td></td></tr>";;
}
?>