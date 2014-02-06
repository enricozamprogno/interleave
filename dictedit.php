<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This file handles language administration stuff like exporting
 * and importing of language packs, completion, adding, etc.
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
$_GET['SkipMainNavigation'] = true;
require_once("initiate.php");

if ($_REQUEST['export']) {
		MustBeAdmin();
		if (!$_REQUEST['pack_to_export']) {
			die('No language pack to export given! (' . $_REQUEST['pack_to_export'] . ')');
		}
		$u=1;

	$sql= "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE LANGID='" . mres($_REQUEST['pack_to_export']) . "'";
	$result= mcq($sql,$db);
	$t = array();
	while ($resarr=mysql_fetch_array($result)){
//		print $resarr['LANGID'] . "|||" . $resarr['TEXTID'] . "|||" . $resarr['TEXT'] . "";
		$t[$u] = $resarr['LANGID'] . "|||" . $resarr['TEXTID'] . "|||" . $resarr['TEXT'] . "";
		$t[$u] = str_replace("\n","",$t[$u]);

		$u++;

	}


	header("Content-Type: text/plain");
	header("Content-Disposition: attachment; filename=" . $_REQUEST['pack_to_export'] . ".CRM.txt" );
	header("Content-Description: Interleave Generated Data" );
	header("Window-target: _top");
//	print "<pre>";
	print "# Interleave LANGUAGE PACK EXPORT FILE - Pack " . $_REQUEST['pack_to_export'] . ".\n";
	print "# Generated " . date("F j, Y, H:i") . " on Interleave version " . $GLOBALS['CRM_VERSION'] . ".\n";
	print "PACK|||" . $_REQUEST['pack_to_export'] . "|||" . ($u-1) . "\n";
	for ($x=1;$x<$u;$x++ ) {
//		print $t[$x] . "\015\012";
	//	$t[$x] = str_replace("\n","",$t[$x]);
	//	$t[$x] = str_replace("\015","",$t[$x]);
	//	$t[$x] = str_replace("\012","",$t[$x]);
//		$t[$x] = urlencode($t[$x]);
		print $t[$x] . "\n";
	}
	uselogger("Language export: " . $_REQUEST['pack_to_export'],"");
	//EndHTML(false);
	exit;
}
if ($_FILES['userfile']['tmp_name'] || $_REQUEST['packurl']) {
	require_once($GLOBALS['PATHTOINTERLEAVE'] . "config/config.inc.php");
	ShowHeaders();
	// Read contents of uploaded file into variable
	if ($_REQUEST['packurl']) {
		$file = base64_decode($_REQUEST['packurl']);
	
	} else {
	
		$file = $_FILES['userfile']['tmp_name'];
	}
	

	$fc = file($file);

	for ($x=0;$x<sizeof($fc);$x++ ) {
			$tmp=split("\|\|\|",$fc[$x]);
			$fc1[$x] = (trim($tmp[0]));
			$fc2[$x] = (trim($tmp[1]));
			$fc3[$x] = (trim($tmp[2]));
	}
	$bla = $fc[2];
	$header=split("\|\|\|",$bla);
	$pack = $header[1];
	print("<br>Processing language pack " . $header[1] . " containing " . $header[2] . " entries.<br>");


	$sql= "SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE LANGID='ENGLISH'";
	if ($debug) { print "\nSQL: $sql\n"; }
	$result= mcq($sql,$db);
	$result= mysql_fetch_array($result);
	
	$outp = "";
	$outp .= "Your current default language pack (ENGLISH) has " . $result[0] . " entries.<br>";
	if ($result[0]==0) {
		$JUST_DO_IT=1;
		$result[0]=$header[2];
		$outp .= "Overridden - you have no language packs at all<br>";
	}
	if (!$result[0]==$header[2]) {
		$outp .= "<span class='noway'>W A R N I N G - Values don't match!</span><br>";
		}
	print($outp);
	unset($outp);


	$sql= "SELECT TEXTID FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE LANGID='ENGLISH'";
	if ($debug) { print "\nSQL: $sql\n"; }
	$result= mcq($sql,$db);
	while ($resarr=mysql_fetch_array($result)){
				if (!in_array($resarr['TEXTID'],$fc2)) {
						$outp .= "<span class='noway'>Missing text identifier \"" . $resarr['TEXTID'] . "\"</span><br>";
						$wrong=1;
				}
	}
	if ($wrong && !$JUST_DO_IT) {

	} else {
		if ($JUST_DO_IT) {
			$outp .= "Installing base language pack<br>";
		} else {
		$outp .= "Pack is OK, all TEXTID's match.<br>";
		}
	}
	$sql= "SELECT DISTINCT LANGID FROM " . $GLOBALS['TBL_PREFIX'] . "languages";
	$result= mcq($sql,$db);
	$p = 0;
	while ($resarr=mysql_fetch_array($result)){
		$tmparr[$p++] = $resarr['LANGID'];	// All LANGID's in array
	}
	if (in_array($pack,$tmparr)) {
		$outp.="You already have a language pack $pack installed. Only new/unknown tags will be added.<br>";
	}
	print($outp);
	unset($outp);
	$imported=0;
	for ($p=3;$p<sizeof($fc2);$p++) {
		
		$tmp = db_GetValue("SELECT TEXTID FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE LANGID='" . mres(trim($fc1[$p])) . "' AND TEXTID = '" . mres(trim($fc2[$p])) . "'");
		if ($tmp[0] == "") { 
			$sql = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "languages(LANGID,TEXTID,TEXT) VALUES('" . mres(trim($fc1[$p])) . "','" . mres(trim($fc2[$p])) . "','" . mres(trim($fc3[$p])) . "')";
			 mcq($sql,$db);
			 $imported++;
		} else {
			print "Skipped tag " .  mres(trim($fc1[$p])) . "\\" .  mres(trim($fc2[$p])) . ", it already exists.<br>";
		}
	
	}
	$outp .= "<br><span class='noway'>" . $imported . " Language entries were imported</span>";

	print($outp);
	log_msg("Language or language pack deployed ($pack, $p records)","");
	EndHTML();
	exit;
}
if ($_REQUEST['lanlist']) {
	print $GLOBALS['doctype'];
	print $GLOBALS['htmlopentag'];
	print '<head>';
	print '<title>Interleave</title>';
	DisplayCSS();
	print '</head>';
	print '<body>';
	print '<div>';
	print '<div>';
	$printbox_size = "100%";
	print "<table border='1' width='" . $printbox_size . "' cellspacing='0' cellpadding='4'>";
	print "<tr><td><strong>Identifier</strong></td><td><strong>English value</strong></td></tr>";
	$sql= "SELECT DISTINCT TEXTID FROM " . $GLOBALS['TBL_PREFIX'] . "languages";
	$result= mcq($sql,$db);
	while ($resarr=mysql_fetch_array($result)){

			$sql1 = "SELECT TEXT FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE TEXTID='" . $resarr['TEXTID'] . "' AND LANGID='" . mres($_REQUEST['lanlist']) . "'";
			$result1= mcq($sql1,$db);
			$resarr1=mysql_fetch_array($result1);
			if ($resarr1[0]) {
				print "<tr>";
				print "<td>" . $resarr['TEXTID'] . "</td>";
				print "<td>" . $resarr1[0] . "</td>";
				print "</tr>";
				$p++;
			}
	}
	print "<tr><td colspan='2'>$p entries found.</td></tr>";
	print "</table>";
	EndHTML();
	exit;
}
ShowHeaders();

AdminTabs();
MainAdminTabs("bla");
AddBreadCrum("Language packs");
$to_tabs = array("overview","upload","far","installfromwebsite");
$tabbs["overview"] = array("dictedit.php" => "Language overview", "comment" => "List of all installed languages");
$tabbs["upload"] = array("dictedit.php?import=1" => "Upload a pack file", "comment" => "Upload a new language pack");
$tabbs["far"] = array("lan_entries.php?far=1" => "Find &amp; replace", "comment" => "Find and replace text in an existing language pack");
$tabbs["installfromwebsite"] = array("dictedit.php?DLP=1" => "Install from project website", "comment" => "Download &amp; install a language pack directly from the Interleave website");
if ($_REQUEST['DLP']) {
	$navid = "installfromwebsite";
} elseif ($_REQUEST['upload']) {
	$navid = "upload";
} elseif ($_REQUEST['import']) {
	$navid = "upload";
} else {
	$navid = "overview";
}
InterTabs($to_tabs, $tabbs, $navid);
if (!is_administrator()) {
	PrintAD("Access to this page/function denied");
} else {
	?>
	<script type="text/javascript">
	<!--
	function poplanlist(i)	{
					newWindow = window.open('dictedit.php?lanlist=' + i,'HelpWindow' ,'width=600,height=300,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');
			}
	//-->
	</script>
	<?php


	?>
	<?php
	if ($_REQUEST['DLP']) {
		GetPacksFromProjectPage();
		EndHTML();
		exit;
	}
	if ($_REQUEST['deletepack']) {
			$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE LANGID='" . mres($_REQUEST['deletepack']) . "'";
			mcq($sql,$db);
			print("Language pack " . $_REQUEST['deletepack'] . " was deleted!");
			uselogger("Delete language pack " . $_REQUEST['deletepack'],"");
	}
	if ($_REQUEST['pack_to_delete']) {
			$a = "Are you sure you want to delete language pack " . $_REQUEST['pack_to_delete'] . "?";
			$a .= "<br><br><a class='arrow' href='dictedit.php?deletepack=" . $_REQUEST['pack_to_delete'] . "'>yes</a>";
			print($a);
			EndHTML();
			exit;
	}
	if ($_REQUEST['add'] && $_REQUEST['val'] && $_REQUEST['id_new']) {
			$sql= "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "languages(LANGID,TEXTID,TEXT) VALUES ('" . mres($_REQUEST['add']) . "','" . mres($_REQUEST['id_new']) . "','" . mres($_REQUEST['val']) . "')";
			mcq($sql,$db);
			print "Entry added";
	}
	if ($_REQUEST['add']) {
			print "<form id='addlan' method='post' action=''><div class='showinline'>";
			print "<table border='1' cellspacing='0' cellpadding='4'>";
			print "<tr><td colspan='2'><strong>Add a language entry for language " . $_REQUEST['add'] . "</strong></td></tr>";
			print "<tr><td>Identifier</td><td><input type='text' name='id_new' size='20'></td></tr>";
			print "<tr><td>" . $_REQUEST['add'] . "</td><td><input type='text' name='val' size='100'><input type='hidden' name='add' value='" . $_REQUEST['add'] . "'></td></tr>";
			print "<tr><td colspan='2'><input type='submit' name='blabla' value='add'></td></tr>";
			print "<tr><td colspan='2'><a class='arrow topnav' href='dictedit.php'>back</a></td></tr>";
			print "</table>";
			print "</div></form>";
			EndHTML();
			exit;
	}
	if ($_REQUEST['newlang']) {
					$sql= "SELECT DISTINCT LANGID FROM " . $GLOBALS['TBL_PREFIX'] . "languages";
					$result= mcq($sql,$db);
						while ($resarr=mysql_fetch_array($result)){
							$tmparr[$p] = $resarr[LANGID];	// All LANGID's in array
							$p++;
						}
						if (in_array($_REQUEST['newlang'],$tmparr)) {
								$outp.="You already have a language pack " . $_REQUEST['newlang'] . " installed. It will not be added again.";
								print($outp);

						} else {

								$sql= "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "languages(LANGID) VALUES('" . mres($_REQUEST['newlang']) . "')";
								mcq($sql,$db);

								print("Your language entry has been added....");
						}
					$_REQUEST['newlan'] = "";
	}
	if ($_REQUEST['languagecomplete']) {
					print "<br><br>";
					if ($_REQUEST['val']) {
							if (!$_REQUEST['val']=="") {
									$outp = "Added your text to pack " . $_REQUEST['languagecomplete'] . ", TEXTID " . $_REQUEST['TEXTID'] . "<br><br>";
									$sql= "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "languages(LANGID,TEXTID,TEXT) VALUES('" . mres($_REQUEST['languagecomplete']) . "','" . mres($_REQUEST['TEXTID']) . "','" . mres($_REQUEST['val']) . "')";
									mcq($sql,$db);
									print($outp);
									unset($outp);
							}
					}
					 $sql= "SELECT TEXTID FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE LANGID='" . mres($_REQUEST['languagecomplete']) . "'";
					if ($debug) { print "\nSQL: $sql\n"; }
						$result= mcq($sql,$db);
						$t=1;
						while ($resarr=mysql_fetch_array($result)){
							$fc2[$t] = $resarr[TEXTID];
							$fc1[$t] = $resarr[TEXT];
							$t++;
						}


					$sql= "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE LANGID='ENGLISH'";
					$result= mcq($sql,$db);
					while ($resarr=mysql_fetch_array($result)){
								if (!in_array($resarr[TEXTID],$fc2)) {
									$outp = "";
									$outp .= "<form id='bladiebla' method='post' action=''><div class='showinline'><input type='hidden' name='languagecomplete' value='" . $_REQUEST['languagecomplete'] . "'>";
									$outp .= "<table border='0' width='100%'><tr><td><strong>TEXTID</strong></td><td class='nwrp'><strong>English value</strong></td><td><strong>" . $_REQUEST['languagecomplete'] . " value</strong></td></tr>";
									$outp .= "<tr><td>";
									$sql= "SELECT TEXT FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE TEXTID='" . mres($resarr['TEXTID']) . "' AND LANGID='ENGLISH'";

									$result1= mcq($sql,$db);
									$result1= mysql_fetch_array($result1);

									$outp .= $resarr['TEXTID'] . "<input type='hidden' name='TEXTID' value='" . $resarr['TEXTID'] . "'></td><td>" . $result1['TEXT'] . "</td><td><input type='text' name='val' size='75'>";
									$outp .= "</td><td><input type='submit' name='knop' value='next'></td></tr></table>";
									$outp .= "</div></form>";
									$printbox_size="75%";
									print($outp);
									?>
									<script type="text/javascript">
									<!--
										 document.forms['bladiebla'].elements['val'].focus();
									//-->
									</script>
									<?php
									EndHTML();
									exit;
								}
				}
										?>
							<script type="text/javascript">
							<!--
							document.location='dictedit.php?end=all';
							//-->
							</script>
							<?php
			EndHTML();
			exit;
	}
	if ($_REQUEST['import']) {
				$a = "";
				$a .= "<form method='post' action='' id='bla' enctype='multipart/form-data'><div class='showinline'><input type='hidden' name='max_file_size' value='52428800'>";
				$a .= "<table>";
				$a .= "<tr><td colspan='6'><input name='userfile' type='file'></td></tr>";
				$a .= "<tr><td><input class='txt' type='submit' name='sb' value='Upload'></td></tr></table></div></form>";

				print $a;
				EndHTML();
				exit;
	}
	if (!$_REQUEST['newlang']) {
			//$legend = "Interleave language pack management and translation procedure";

			$sql= "SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE LANGID='ENGLISH'";
			if ($debug) { print "\nSQL: $sql\n"; }
			$result= mcq($sql,$db);
			$result= mysql_fetch_array($result);
			$maxlan = $result[0];

			unset($tmparr);
			unset($p);
			$p=1;
			$sql= "SELECT DISTINCT LANGID FROM " . $GLOBALS['TBL_PREFIX'] . "languages";
			$result= mcq($sql,$db);
			while ($resarr=mysql_fetch_array($result)){
				if ($resarr['LANGID']<>"GLOBAL") {
					$tmparr[$p] = $resarr[LANGID];	 // All LANGID's in array
					$p++;
				}
			}


			$bla = "<table class='crm'>";
			$cs = array();
			for ($c=0;$c<sizeof($tmparr)+1;$c++) {
					if ($tmparr[$c]<>"") {
						$sql= "SELECT TEXTID,TEXT FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE LANGID='" . mres($tmparr[$c]) . "'";
						$result= mcq($sql,$db);
						$t=1;
						unset($fc2);
						while ($resarr=mysql_fetch_array($result)){
							$fc2[$t] = $resarr[TEXTID];
							$t++;
							if ($resarr['TEXTID']=="CHARACTER-ENCODING") {
								$cs[$c] = $resarr['TEXT'] . " (from pack file)";
							}
						}
	//				print "<pre>$tmparr[$c]";
	//				print_r($fc2);
					$sql= "SELECT TEXTID FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE LANGID='ENGLISH'";
					$result= mcq($sql,$db);
					$missing = 0;
					while ($resarr=mysql_fetch_array($result)){
								if (!in_array($resarr['TEXTID'],$fc2)) {
										$missing++;
										$wrong=1;
								}
					}
					$sql= "SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE LANGID='" . mres($tmparr[$c]) . "'";
					$result= mcq($sql,$db);
					$result= mysql_fetch_array($result);
					if (!$missing) {
						$bla .= "<tr><td>Language " . $tmparr[$c] . " is complete.</td><td><a class='plainlink' href='dictedit.php?export=1&amp;pack_to_export=" . $tmparr[$c] . "'>export</a>&nbsp;&nbsp;<a class='plainlink' href='dictedit.php?pack_to_delete=" . $tmparr[$c] . "'>delete</a>&nbsp;&nbsp;<a class='plainlink' href='dictedit.php?add=" . $tmparr[$c] . "'>add an entry</a>&nbsp;&nbsp;<a class='plainlink' href=\"javascript:poplanlist('" . $tmparr[$c] . "');\">language id list</a>&nbsp;&nbsp;<a class='plainlink' href='lan_entries.php?edlan=" . $tmparr[$c] . "'>edit</a><br>";
					} else {
						$ins = "";
						$bla .= "<tr><td>Language " . $tmparr[$c] . " is missing " . $missing . " entries!</td><td><a class='plainlink' href='dictedit.php?export=1&amp;pack_to_export=" . $tmparr[$c] . "'>export</a>&nbsp;&nbsp;<a class='plainlink' href='dictedit.php?pack_to_delete=" . $tmparr[$c] . "'>delete</a>&nbsp;&nbsp;<a class='plainlink' href='dictedit.php?add=" . $tmparr[$c] . "'>add an entry</a>&nbsp;&nbsp;<a class='plainlink' href=\"javascript:poplanlist('" . $tmparr[$c] . "');\">language id list</a>&nbsp;&nbsp;<a class='plainlink' href='lan_entries.php?edlan=" . $tmparr[$c] . "'>edit</a>&nbsp;&nbsp;<a class='plainlink' href='dictedit.php?languagecomplete=" . $tmparr[$c] . "'> complete </a>&nbsp;<br>";
					}
					unset($missing);
					if (!$cs[$c]) {
					$cs[$c] = "ISO-8859-1 (by default)";
					 }
					 if ($c==0) {
						$cs[0] = "";
					 }
					 $bla.= "</td><td class='nwrp'>" . $cs[$c] . "</td></tr>";
				 } else {

				 }

			}
				$bla .= "</table>";
				$bla .= "<br><span class='underln'>Create new language or language mask</span>: <form id='new' method='post' action=''><div class='showinline'><input type='text' name='newlang'>&nbsp;&nbsp;<input type='submit' name='bla' value='Submit'></div></form>";
				$bla .= "<br><br>If you want to use an other character encoding to be used than ISO-8859-1, add a text field called 'CHARACTER-ENCODING' (in capitals) to your language pack and give it the correct character encoding version.";
				$bla .= "<br><br>If you want a clear view of all used language tags, you can go into translation mode. This will cause all language tag names to be displayed behind the actual value. It will only be visible by you e.g. you won't bother other users.<br><br>&nbsp;&nbsp;<a class='arrow' onclick=\"setCookie('language_display','yes');alert('You are now in tag display mode. Refresh the screen or click a link to see all tagnames.');\" >go to tag display mode</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a onclick=\"setCookie('language_display','');alert('You left tag display mode. Refresh the screen or click a link to hide the tagnames.');\" class='arrow' >leave tag display mode</a>";

				$printbox_size = "90%";
				print $bla;
				EndHTML();
	}
	
}
function GetPacksFromProjectPage() {
	global $legend,$printbox_size;

	if (!$fd = @fopen("http://download.interleave.nl/get_packs.php", "r")) {
		$printbox_size = "100%";
		$legend = "<img src='images/error.gif' alt=''>";
		printbox("Connect to remote site failed. Your <span class='underln'>server</span> needs to be able to connect to the internet for this function - or maybe the service is unavailable.");
		EndHTML();
		exit;
	} else {
		$listarr = array();
		$urlarr = array();
		$y = 0;
		$check = 0;
		while ($line=fgets($fd,1000))
		{
			if (!$check) {
				//crm_language_pack_remote_install // check if response is OK
				if (trim($line)!="crm_language_pack_remote_install") {
					$printbox_size = "100%";
					$legend = "<img src='images/error.gif' alt=''>";
					printbox("The received response was not expected. Please try again later.");
					EndHTML();
					exit;
				} else {
					$check = 1;
				}
			} else {
				$line = trim($line);
				$tmp = split(",",$line);
				$tmp[1] = str_replace(".CRM","",$tmp[1]);
				$tmp[1] = str_replace("_"," ",$tmp[1]);

				$listarr[$y]['url'] = $tmp[0];
				$listarr[$y]['name'] = $tmp[1];
				$y++;
				unset($tmp);
//				print_r($listarr);
			}
			}
			fclose ($fd);
	}

	sort($listarr);

	$legend = "Choose pack to install";
	$t = "The following language packs are available at the project page. Click on a pack to install. <br>";
	for ($i=0;$i<sizeof($listarr);$i++) {
		$t .= "<a href='dictedit.php?&amp;DLP=1&amp;packurl=" . base64_encode($listarr[$i]['url']) . "' class='arrow'>" . $listarr[$i]['name'] . "</a><br>";
	}
	$printbox_size = "100%";


	print $t;

}
?>