<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file shows some basic statistics
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */

require_once("initiate.php");
$_GET['SkipMainNavigation'] = true;
ShowHeaders();
MustBeAdmin();
SafeModeInterruptCheck();
AdminTabs();

$tmp = db_GetArray("SELECT CONCAT(WEEK(timestamp_last_change)-1,'-', YEAR(timestamp_last_change)) AS Week, COUNT(*) AS hits FROM " . $GLOBALS['TBL_PREFIX'] . "uselog GROUP BY CONCAT(WEEK(timestamp_last_change),'-',YEAR(timestamp_last_change)) ORDER BY timestamp_last_change");

print "<table width='800'><tr><td>";
print "<h1>Hits per week</h1>";
print "<div class=\"scroll\">";
print "<table class='crm'>";
print "<thead><tr><td width='200'>Week</td><td>Hits</td></tr></thead>";
foreach ($tmp AS $row) {
	print "<tr><td>" . $row['Week'] . "</td><td align='right'>" . $row['hits'] . "</td></tr>";
}
print "</table>";
print "</div>";

$tmp = db_GetArray("SELECT CONCAT(WEEK(timestamp_last_change)-1,'-', YEAR(timestamp_last_change)) AS Week, COUNT(*) AS logins FROM " . $GLOBALS['TBL_PREFIX'] . "uselog WHERE qs LIKE '%Login %' GROUP BY CONCAT(WEEK(timestamp_last_change),'-',YEAR(timestamp_last_change)) ORDER BY timestamp_last_change;");
print "<h1>Logins per week</h1>";
print "<div class=\"scroll\">";
print "<table class='crm'>";
print "<thead><tr><td width='200'>Week</td><td>Logins</td></tr></thead>";
foreach ($tmp AS $row) {
	print "<tr><td>" . $row['Week'] . "</td><td align='right'>" . $row['logins'] . "</td></tr>";
}
print "</table>";
print "</div>";

$tmp = GetFlextableDefinitions();

foreach ($tmp AS $flextable) {
	$ft = $flextable['recordid'];
	$tmp = db_GetArray("select distinct(eid) AS cmp,COUNT(*) AS views from " . $GLOBALS['TBL_PREFIX'] . "journal WHERE type='flextable" . $ft . "' GROUP BY eid;");
	print "<h1>Flextable " . htme($flextable['tablename']) . " log lines per record</h1>";
	print "<div class=\"scroll\">";
	print "<table class='crm'>";
	print "<thead><tr><td width='200'>Record id</td><td>Log lines</td></tr></thead>";
	foreach ($tmp AS $row) {
		print "<tr><td>" . $row['cmp'] . "</td><td align='right'>" . $row['views'] . "</td></tr>";
	}
	print "</table></div>";
}


print "</td></tr></table>";

EndHTML();
?>