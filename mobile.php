<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is for mobile phone access.
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
require_once("initiate.php");
exit;

$GLOBALS['mobile'] = true;
$_REQUEST['nonavbar'] = 1;
$_REQUEST['tblogin'] = 1;
ShowHeaders();
qlog(INFO, "Entering Interleave Mobile Device mode");
if ($_REQUEST['normal'] == "1") {
			?>
				<script type="text/javascript">
				<!--
					setCookie('mobile', 'never');
					document.location='index.php';
				//-->
				</script>
<?php
} else {
			?>
				<script type="text/javascript">
				<!--
					setCookie('mobile', 'yes');
				//-->
				</script>
<?php
}
?>
<form id='mobileform' action='mobile.php' method='post'><div class='showinline'>
<table>
	<tr>
		<td class='nwrp'>
			<img src='images/crmlogosmall.gif' alt='logo'>
		&nbsp;EID: <input type='text' size='3' name='eid_direct'> S: <input type='text' size='8' name='eid_search'>
		<input type='submit' value='<?php echo $lang['go'];?>'> <a href='mobile.php'>[M]</a> <a href='mobile.php?eid_direct=_new_'>[N]</a> <a href='mobile.php?normal=1'>[F]</a> <a href='mobile.php?logout=1'>[L]</a>
		</td>
	</tr>
</table>
</div></form>
<hr>
<?php
if ($_POST['eid_search']) {
	$tmp = FullscanWildsearch($_POST['eid_search'], true, true);
	if (sizeof($tmp)>0) {
		print "<table class='crm' width='100%'><tr><td colspan='2'>" . sizeof($tmp) . " " . $lang['entitiesfound'] . "</td></tr>";
		foreach ($tmp AS $row) {
			print "<tr><td class='nwrp'><a href='mobile.php?eid_direct=" . $row . "'>" . $row . " " . fillout(GetEntityCategory($row),40) . "</a></td></tr>";
		}
		print "</table>";
	} else {
		print $lang['noresults'];
	}
} elseif (is_numeric($_REQUEST['eid_direct'])) {
	$eid = $_REQUEST['eid_direct'];
//	print "sub $eid";
	if (CheckEntityAccess($eid) == "ok" || CheckEntityAccess($eid) == "readonly") {
		if (GetTemplateSubject(GetEntityFormID($eid)) <> "") {
			print CustomEditForm(GetEntityFormID($eid),$eid);
		} else {
			print "<img src='images/error.gif' alt=''> Form " . GetEntityFormID($e) . " not found. Defaulting.";
			log_msg("ERROR: Entity " . $eid . " wants to use form " . GetEntityFormID($eid) . " - this form is not available. Falling back to default form.");
			qlog(INFO, "Building Default Emergency Edit Form ($eid)");
			print CustomEditForm($GLOBALS['DefaultForm'],$eid);
		}
	} else {
		PrintAD("This entity doesn't exist or you don't have access to it.");
	}


	// edit.php will redirect back to mobile.php when the mobile cookie is found
} elseif ($_REQUEST['eid_direct'] == "_new_") {

	if (sizeof($GLOBALS['UC']['ADDFORMLIST']) == 1) {
		print CustomEditForm(GetEntityFormID("_new_"),"_new_");
	} elseif (sizeof($GLOBALS['UC']['ADDFORMLIST']) > 1 && is_numeric($_REQUEST['form'])) {
		print CustomEditForm($_REQUEST['form'],"_new_");
	} elseif (sizeof($GLOBALS['UC']['ADDFORMLIST']) > 1) {
		print "<table>";
		foreach ($GLOBALS['UC']['ADDFORMLIST'] AS $form) {
				print "<tr><td class='nwrp'><a href='mobile.php?eid_direct=_new_&form=" . $form. "'>" . GetTemplateSubject($form) . "</a></td></tr>";
		}
		print "</table>";
	} else {
		print CustomEditForm($GLOBALS['DefaultForm'],"_new_");
	}

} elseif ($_REQUEST['msgs'] == 1 || $_REQUEST['UserMessage']) {
	UserMessage();
} else {
	print ShowRecentEntitiesMobile();
}



EndHTML();


//function
?>
