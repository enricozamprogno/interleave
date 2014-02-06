<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This script is for cleaning up Interleave databases
 *
 * It will PHYSICALLY delete entity records (deleted before a given date)
 * after which the std. "check database" function should do the rest
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
require_once("initiate.php");
$EnableRepositorySwitcherOverrule="n";
ShowHeaders();
AdminTabs();
MainAdminTabs("datman");
print "</table><table border='0' width='90%'><tr><td>&nbsp;&nbsp;</td><td><fieldset><legend>&nbsp;<img src='images/crmlogosmall.gif' alt=''>&nbsp;&nbsp;Database cleanup&nbsp;</legend>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $title . "<table border='0' width='100%'>";

MustBeAdmin();
SafeModeInterruptCheck();

// Steps

if (!$_GET['step']) {			// Warning message

	print "<tr><td><strong>This function is very dangerous. With it, you can actually erase data which cannot be recovered.</strong></td></tr>";
	print "<tr><td></td></tr>";
	print "<tr><td>It will <span class='underln'>physically</span> delete entities which were deleted before a date you can select later on. It will delete <strong>only</strong> the entity records - you will have to run the \"check database\" function after using this function to make sure all referencing data is deleted properly.</td></tr>";
	print "<tr><td></td></tr>";
	print "<tr><td><img src='images/info.gif' alt=''>&nbsp;Needless to say you have to back-up first!</td></tr>";
	print "<tr><td></td></tr>";
	print "<tr><td>For now, you can safely continue; Interleave will not delete anything before warning you.<br><br></td></tr>";

	NextStep("b","(select the date)");

} elseif ($_GET['step'] == "b") {	// Select date


	?>
		<tr><td>Please enter the date - entities <strong>closed before this date</strong> will later on be deleted</td></tr>
		<tr><td><form id='SelectDate' method='get' action=''><div class='showinline'><table>

		<tr><td>DAY OF MONTH (01-31, 2-char!)</td><td><input type='text' size='2' name='dom'></td></tr>
		<tr><td>MONTH (01-12, 2-char!)</td><td><input type='text' size='2' name='month'></td></tr>
		<tr><td>YEAR (4-char)</td><td><input type='text' size='4' name='year'>
		<input type='hidden' name='step' value='c'>
		</td></tr>
		</table>
		</div></form>
		</td></tr>
	<?php

	print "<tr><td><a class='arrow' href=\"javascript:document.forms['SelectDate'].submit();\">Next step</a> (look at what <em>would</em> be deleted)</td></tr>";


} elseif ($_GET['step'] == "c") {	// Show summary of entities which will be deleted
	if (!$_GET['dom'] || !$_GET['month'] || !$_GET['year'] || strlen($_GET['year'])<>4 || strlen($_GET['dom'])<>2 || strlen($_GET['month'])<>2 || !checkdate($_GET['month'],$_GET['dom'],$_GET['year']) ) {

		// date = false

		print "<tr><td><img src='images/error.gif' alt=''>&nbsp;You know what you did wrong. If not, don't use this function!</td></tr>";

	} else {

		$timestamp = @mktime(0,0,0,$_GET['month'],$_GET['dom'],$_GET['year']);


		if ($timestamp > date('U')) {
			print "<tr><td><img src='images/info.gif' alt=''>&nbsp;<strong><span class='noway'>Warning! This date is in the future!</span></strong></td></tr>";
		}


		print "<tr><td>Date is valid: " . date("r",$timestamp) . "<br><br></td></tr>";
		print "<tr><td><strong>Entities which will be deleted: (including attachements, journal, e-mails, etc)</strong></td></tr>";
		print "<tr><td><table border='1' width='100%' cellpadding='2' cellspacing='2'>";
		print "<tr><td>EID</td><td>" . $lang['owner'] . "</td><td>" . $lang['assignee'] . "</td><td>" . $lang['customer'] . "</td><td>" . $lang['category'] . "</td><td><strong><span class='noway'># of attachments</span></strong><td>close/delete date</td><td>last update</td><td>by...</td></tr>";


		$sql = "SELECT eid,owner,assignee,CRMcustomer,category,closeepoch,timestamp_last_change,lasteditby FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE deleted='y' AND closeepoch<>'' AND closeepoch<'" . mres($timestamp) . "' ORDER BY closeepoch DESC";
		$result = mcq($sql,$db);
		while ($row = mysql_fetch_array($result)) {

			if ($countit < 200) {
				$t = $row['timestamp_last_change']; // timestamp last edit
				$tp[jaar] = substr($t,0,4);
				$tp[maand] = substr($t,4,2);
				$tp[dag] = substr($t,6,2);
				$tp[uur] = substr($t,8,2);
				$tp[min] = substr($t,10,2);

				$numfiles = GetNumOfAttachments($row['eid']);
				$countfiles += $numfiles;
				print "<tr><td>" . $row['eid'] . "</td><td>" . GetUserName($row['owner']) . "</td><td>" . GetUserName($row['assignee']) . "</td><td>" . GetCustomerName($row['CRMcustomer']) . "</td><td>" . $row['category'] . "</td><td>" . $numfiles ."</td><td>" . date("Y-m-d",$row['closeepoch']) . "</td><td>" . $tp['jaar'] . "-" . $tp['maand'] . "-" . $tp['dag'] . "</td><td>" . GetUserName($row['lasteditby']) . "</td></tr>";
			}
			$countit++;

		}


		print "</table><br><br></td></tr>";

		if ($countit) {
			print "<tr><td>";
			print "<form id='SumAll' method='get' action=''><div class='showinline'>";
			print "<input type='hidden' name='step' value='d'>";
			print "<input type='hidden' name='stamp' value='" . $timestamp . "'>";
			print "<input type='hidden' name='numofentities' value='" . $countit ."'>";
			print "<input type='hidden' name='numoffiles' value='" . $countfiles . "'>";
			print "</div></form>";
			if ($countit>199) {
				print "<strong>Too much entities to print. A total of " . $countit . " entities will be deleted.</strong><br><br>";
			}
			print "<a class='arrow' href=\"javascript:document.forms['SumAll'].submit();\">Next step</a> (summary of what will be done)</td></tr>";

		} else {
			print "<tr><td><img src='images/error.gif' alt=''>&nbsp;Nothing to do!</td></tr>";
		}

	}
} elseif ($_GET['step'] == "d") {	// (optional) show list of files

		print "<tr><td><strong>This is the last screen before actually deleting something!</strong><br><br></td></tr>";

		print "<tr><td>Query: <strong>delete all entities which were logically deleted on " . date("r",$_GET['stamp']) . " or before<br><br></td></tr>";
		print "<tr><td>Database query: DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE deleted='y' AND closeepoch<'" . mres($_GET['stamp']) . "'<br><br></td></tr>";

		print "<tr><td><span class='noway'>" . $_GET['numofentities'] . " entities (containing " . $_GET['numoffiles'] . " attachments) will be deleted!</span><br><br></td></tr>";

		print "<tr><td>";
		print "<form id='SumAll' method='get' action=''><div class='showinline'>";
		print "<input type='hidden' name='step' value='e'>";
		print "<input type='hidden' name='stamp' value='" . $_GET['stamp'] . "'>";
		print "</div></form>";

		print "<a class='arrow' href=\"javascript:document.forms['SumAll'].submit();\">Delete them!</a></td></tr>";

} elseif ($_GET['step'] == "e") {	// delete the entities

		print "<tr><td>";
		if ($_GET['stamp']>100) {
			$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE deleted='y' AND closeepoch<'" . mres($_GET['stamp']) . "'";
			mcq($sql,$db);
			//print $sql;

			print "Your query was executed. There's no way back.<br><br><strong>You now have to run the database check to actually delete all data. Go <a href='admin.php?checkdb=1&web=1'>here</a> to do so.</td></tr>";
		}


}

print "</table>";
EndHTML();

function NextStep($step,$expl,$js="")
{
	print "<tr><td><a class='arrow' href='db_clean.php?step=" . $step . "&js=" . $js . "'>Next step</a> " . $expl . "</td></tr>";
}
?>
