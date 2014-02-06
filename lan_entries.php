<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This file is used to edit a complete language pack at once,
 * always using the current language pack.
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
require_once("initiate.php");

ShowHeaders();
if ($_REQUEST['far']) {
	AdminTabs();
	MainAdminTabs("bla");
	$to_tabs = array("overview","upload","far","installfromwebsite");
	$tabbs["overview"] = array("dictedit.php" => "Language overview", "comment" => "List of all installed languages");
	$tabbs["upload"] = array("dictedit.php?import=1" => "Upload a pack file", "comment" => "Upload a new language pack");
	$tabbs["far"] = array("lan_entries.php?far=1" => "Find &amp; replace", "comment" => "Find and replace text in an existing language pack");
	$tabbs["installfromwebsite"] = array("dictedit.php?password=&amp;DLP=1" => "Install from project website", "comment" => "Download &amp; install a language pack directly from the Interleave website");
	InterTabs($to_tabs, $tabbs, "far");
	print "<table><tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>";
	if ($_REQUEST['ftp'] && $_REQUEST['ltp'] && $_REQUEST['ttp']) {
		$lan = DB_GetArray("SELECT * from " . $GLOBALS['TBL_PREFIX'] . "languages WHERE LANGID='" . mres($_REQUEST['ltp']) . "'");
		if (!$_REQUEST['confirmed']) {
			print "<table><tr><td colspan='2'><a class='arrow' href='lan_entries.php?ftp=" . htme($_REQUEST['ftp']) . "&amp;ttp=" . htme($_REQUEST['ttp']) . "&amp;ltp=" . htme($_REQUEST['ltp']) . "&amp;confirmed=1&amp;far=1'>Click here to actually find and replace the language tags.</a></td></tr></table>";
			}

		print "<table class='crm'>";
		if (!$_REQUEST['confirmed']) {
			print "<tr><td colspan='2'><strong>Below is the list of what will be replaced. Check carefully!</strong></td></tr>";
			print "<tr><td>From</td><td>to</td></tr>";
		}
		$_REQUEST['ftp'] = strtolower($_REQUEST['ftp']);
		$_REQUEST['ttp'] = strtolower($_REQUEST['ttp']);

		foreach ($lan AS $tag) {
			//print $tag['TEXT'];
			if (stristr($tag['TEXT'], $_REQUEST['ftp'])) {
				$uca_ft = ucfirst($_REQUEST['ftp']);
				$uca_tt = ucfirst($_REQUEST['ttp']);
				$newtag = str_replace($uca_ft, $uca_tt, $tag['TEXT']);
				$newtag = str_replace($_REQUEST['ftp'], $_REQUEST['ttp'], $newtag);
				$from = str_replace($_REQUEST['ftp'], "<span class='noway'>" . $_REQUEST['ftp'] . "</span>", $tag['TEXT']);
				$from = str_replace($uca_ft, "<span class='noway'>" . $uca_ft . "</span>", $from);
				$to = str_replace($_REQUEST['ttp'], "<span style='color: #66CC00;'>" . $_REQUEST['ttp'] . "</span>", $newtag);
				$to = str_replace($uca_tt, "<span style='color: #66CC00;'>" . $uca_tt . "</span>", $to);
				if (!$_REQUEST['confirmed']) {
					print "<tr><td>" . $from . "</td><td>" . $to . "</td></tr>";
				} else {
					mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "languages SET TEXT='" . mres($newtag) . "' WHERE TEXTID='" . mres($tag['TEXTID']) . "' AND LANGID='" . mres($_REQUEST['ltp']) . "'", $db);
					$uiopisudf++;
				}
			}
		}
		if (!$_REQUEST['confirmed']) {
			print "<tr><td colspan='2'><a class='arrow' href='lan_entries.php?ftp=" . htme($_REQUEST['ftp']) . "&amp;ttp=" . htme($_REQUEST['ttp']) . "&amp;ltp=" . htme($_REQUEST['ltp']) . "&amp;confirmed=1&amp;far=1'>Click here to actually find and replace the language tags.</a><br><br></td></tr>";
		} else {
			print "<tr><td>" . $uiopisudf . " text items adjusted.</td></tr>";
		}
	} else {
		print "<form id='far' method='post' action='lan_entries.php'><div class='showinline'>";
		print "<table width='100%'>";
	//	print "<tr><td colspan='5'>Global find and replace</td></tr>";
		print "<tr><td>Language</td>";
		$t = DB_GetArray("SELECT DISTINCT LANGID FROM " . $GLOBALS['TBL_PREFIX'] . "languages");
		print "<td><select name='ltp'>";
		foreach ($t AS $row) {
			print "<option value='" . $row['LANGID'] . "'>" . $row['LANGID'] . "</option>";
		}
		print "</select></td></tr>";
		print "<tr><td>Replace text</td><td><input type='text' name='ftp'></td></tr>";
		print "<tr><td>With text</td><td><input type='text' name='ttp'></td></tr>";
		print "<tr><td colspan='2'><br>This routine automatically replaces case-sensitive.</td></tr>";
		print "<tr><td colspan='2'>Be careful; if you want all customers to be called 'donkeys' first replace 'customers' with 'donkeys' and then 'customer' with 'donkey'.</td></tr>";
		print "<tr><td><br>After submitting this form you will see an overview of what will be replaced first.</td></tr>";
		print "<tr><td colspan='2'><br><input type='hidden' name='far' value='1'><input type='submit' value='Go!'></td></tr>";
		print "</table>";
		print "</div></form>";
	}
	print "</td></tr></table>";
}
else
{
	if ($_REQUEST['upd'])
	{
		if (!$_REQUEST['edlan'])
		{
			PrintAD("Access denied / unexpected input");
			exit;
		}
		$valuenew = $_REQUEST['valuenew'];
		$id_lan = $_REQUEST['id_lan'];

		for ($x=0;$x<(sizeof($valuenew));$x++)
		{
			
			$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "languages SET TEXT='" . mres($valuenew[$x]) . "' WHERE TEXTID='" . mres($id_lan[$x]) . "' AND LANGID='" . mres($_REQUEST['edlan']) . "'";
			mcq($sql,$db);
		}
		print "<table style='width: 90%;'>";
		print "<tr><td colspan='5'>Edit language pack</td></tr>";
		print "<tr><td colspan='5'><hr></td></tr>";
		print "<tr><td>Your changes are saved.</td></tr>";

		print "</table>";
		print "</div></body></html>";
		exit;
	}
	print "<form id='editlanentries' method='post' action=''><div class='showinline'><input type='hidden' name='edlan' value='" . $_REQUEST['edlan'] . "'>";
	print "<table>";
	print "<tr><td colspan='4'>Edit language pack</td></tr>";
	print "<tr><td colspan='4'><hr></td></tr>";
	print "<tr><td colspan='4'>Language entries for language " . $_REQUEST['edlan'] . " &nbsp;&nbsp;</td></tr>";
	print "<tr><td colspan='3'>&nbsp;</td></tr>";

	print "<tr><td>id</td><td><strong>Tag</strong></td><td>Current value</td><td><strong>New value</strong></td></tr>";

	$sql= "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE LANGID='" . mres($_REQUEST['edlan']) . "' ORDER BY TEXTID";
	$result= mcq($sql,$db);
	while ($blad= @mysql_fetch_array($result))
	{
		print "<tr>";
		print "<td>" . $blad['id'] . "</td>";
		print "<td><input type='hidden' size='50' name='namenew[]' value='" . $blad['TEXTID'] . "' disabled='disabled'>" . $blad['TEXTID'] . "</td>";
		print "<td>" . $blad['TEXT'] . "</td>";
		print "<td><input type='text' name='valuenew[]' value=\"" . htme($blad['TEXT']) . "\" size='100'><input type='hidden' name='id_lan[]' value='" . $blad['TEXTID'] . "'></td>";
		print "</tr>";
	}
	print "<tr><td colspan='5'><br><input type='hidden' value='1' name='upd'><input type='submit' name='knop' value='" . $lang['apply'] . "'></td></tr>";
	print "</table>";
	print "</div></form>";
}
EndHTML();
?>