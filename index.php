<?php
/* ********************************************************************
* Interleave
* Copyright (c) 2001-2012 info@interleave.nl
* Licensed under the GNU GPL. For full terms see http://www.gnu.org/
*
* This file is one of the main handlers of Interleave.
*
* Check http://www.interleave.nl/ for more information
*
* BUILD / RELEASE :: 5.5.1.20121028
*
**********************************************************************
*/
require_once("initiate.php");

if ($_REQUEST['eid'] && !IsValidEID($_REQUEST['eid'])) { // Catch all
	
	ShowHeaders();
	PrintAD("Invalid input; access denied");
	EndHTML();
} elseif ($_REQUEST['LockMsg']) {

	$_REQUEST['nonavbar'] = 1;
	
	ShowHeaders();
	print "<div>";
	print "<table style='width: 100%; height: 100%;'>";
	print "<tr><td><center>" . $lang['lostlock'] . "</center></td></tr></table>";
	print '</a><div>';
	EndHTML();

} elseif (isset($_GET['ShowAdvancedQueryInterface'])) {
	$_REQUEST['nonavbar'] = 1;
	ShowHeaders();
	if ($_REQUEST['Scope'] == "system" && is_administrator()) {
		$header_ins = " (system-wide)";
		$box_ins = "&Scope=system";
	}
	if ($_REQUEST['ListId']) {
		$header_ins = " (" . htme($_REQUEST['ListId']) . ")";
		$box_ins .= "&ListId=" . $_REQUEST['ListId'];
	} 

	print "<h1>" . $lang['advancedselectionsbuilder'] . " " . $header_ins . "</h1>";
	print "<h2>" . $lang['buildsaveandeditselections'] . "</h2>";
	print AjaxBox("ShowAdvancedQueryInterface", true, $box_ins . "&ParentEntityListAjaxHandler=" . htme($_POST['ParentEntityListAjaxHandler']), false, $divid=false);
	EndHTML();


} elseif ($_REQUEST['DisplayMessage']) {
	
	$msg = PopStashValue($_REQUEST['DisplayMessage']);

	$_REQUEST['nonavbar'] = 1;

	ShowHeaders();
	print "<div>";
	print "<table style='width: 100%; height: 100%;'>";
	print "<tr><td>" . htme($msg) . "</td></tr></table>";
	print '</a></div>';
	EndHTML();

} elseif ($_REQUEST['qlog'] && $_REQUEST['logstash'] && is_administrator()) {
	
	$msg = PopStashValue($_REQUEST['logstash']);

	$_REQUEST['nonavbar'] = 1;

	ShowHeaders();
	print "<div>";
	print "<table style='width: 100%; height: 100%;'>";
	print "<tr><td><pre>" . $msg . "</pre></td></tr></table>";
	print '</a></div>';
	EndHTML(false);

} elseif ($_REQUEST['SwitchReposPopup']) {

	$_REQUEST['nonavbar'] = 1;
	ShowHeaders();
	print "<table><tr><td>&nbsp;</td><td><br><br><strong>Repository:</strong><br><br>";
	ShowRepositorySwitcher("index.php?ShowEntityList=1&tab=23&1093641656","1");
	print "</td></tr></table>";
	EndHTML();

} elseif ($_REQUEST['UpdateCacheTables']) {

	ShowHeaders();
	
	if ($GLOBALS['Overrides']['Logo'] != "") {
		$logo = "" . htme($GLOBALS['Overrides']['Logo']) . "";
	} else {
		$logo = "images/crm.gif";
	}
	$epoch = date('U');
	print "<img title='This image is hosted on the project site' alt='sf.net image' src='https://sourceforge.net/sflogo.php?group_id=61096&amp;type=1&amp;" . $epoch . "'>";
	print "<img title='' alt='' src='" . $logo . "'>";
	$_REQUEST['nonavbar'] = 1;

	if ($_REQUEST['UpdateCacheTables'] == "do") {

		UpdateCacheTables(false,$GLOBALS['USERID']);

	} else {
		?>
		<table style='width: 100%;'><tr><td><center>
		</center></td></tr><tr><td><center>
		<img src='images/search.gif' alt=''>
		<br>Updating your cache tables ..
		</center></td></tr></table>
		<object id="UpdateCacheTables" height="1" width="1" data="index.php?UpdateCacheTables=do" type="text/html"></object>
		<?php
		flush();
		ob_flush();

	}
	EndHTML(false);

} elseif ($_REQUEST['swrepos']) {

	$_REQUEST['nonavbar'] = 1;
	ShowHeaders();
	ShowRepositorySwitcher("index.php?" . $epoch);
	EndHTML();

} elseif ($_REQUEST['Lim_Add'] && ($_REQUEST['addcontent'] || ($_FILES['userfile']['tmp_name'] && $_FILES['userfile']['name'] && $_FILES['userfile']['size']))) {

	// This pice handles updates (comments and files) by limited users

	
	$date = date('d-m-Y H:i') . "h";

	$sql = "SELECT content FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE eid='" . mres($eid) . "'";
	$result= mcq($sql,$db);
	$maxU1 = mysql_fetch_array($result);

	if ($_FILES['userfile']['tmp_name'] && $_FILES['userfile']['name'] && $_FILES['userfile']['size']) {
		// Read contents of uploaded file into variable
		$fp=fopen($_FILES['userfile']['tmp_name'],"rb");
		$filecontent=fread($fp,filesize($_FILES['userfile']['tmp_name']));
		fclose($fp);
		$filecontenttomail = $filecontent;
		$filenametomail = $_FILES['userfile']['name'];
		$attachment = "1";
		$statusmsg="File " . $_FILES['userfile']['name'] . " " . $lang['wasadded'];
		$x = AttachFile($eid,$_FILES['userfile']['name'],$filecontent,"entity",$_FILES['userfile']['type']);
	}
	if ($statusmsg) {
		$addcontent .= "\n" . $statusmsg;
	}

	$addcontent = "Added at " . $date . " by " . $GLOBALS['USERNAME'] . ":\n" . $addcontent ."\nEnd edit by " . $GLOBALS['USERNAME'] . ".\n\n";
	// Update SQL
	$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET content=concat('" . mres($addcontent) . "', content) WHERE eid='" . mres($eid) . "'";
	//print $sql;
	journal($eid,"[limited mode] Entity updated (contents only)");
	uselogger("[limited mode] Edit entity: $eid","");
	//print $sql;
	mcq($sql,$db);
	ExpireFormCache($eid);
	ProcessTriggers("limited_update",$eid,"Miscellaneous trigger");

	qlog(INFO, "Navigating away from this page (1)");
	header("Location: edit.php?e=" . $_REQUEST['eid']);
	EndHTML();

} elseif ($_REQUEST['lan']) {

	// personal language setting changed
	$sql= "UPDATE " . $GLOBALS['TBL_PREFIX'] . "loginusers SET exptime='" . mres($_REQUEST['lan']) . "' WHERE id='" . mres($GLOBALS['USERID']) . "'";
	mcq($sql,$db);
	header("Location: index.php?lanok=1");;

} elseif ($_REQUEST['shortkeys']) {

	ShowHeaders();
	print "<table><tr><td><img src='images/crmlogosmall.gif' alt=''>&nbsp;&nbsp;Interleave Shortkey funtions&nbsp;<table style='width: 100%;' class='crm'>";
	print "<tr><td>ALT-[1..0]<br>&nbsp;</td><td>Go to tab [1..0]<br>&nbsp;</td></tr>";
	print "<tr><td>ALT-N<br>&nbsp;</td><td>Add a customer<br>&nbsp;</td></tr>";
	print "<tr><td>ALT-A<br>&nbsp;</td><td>Go to admin section<br>&nbsp;</td></tr>";
	print "<tr><td>ALT-S</td><td>Configure systems values</td></tr>";
	print "<tr><td>ALT-U</td><td>Configure accounts</td></tr>";
	print "<tr><td>ALT-L</td><td>Configure language packs</td></tr>";
	print "<tr><td>ALT-T</td><td>Configure event triggers</td></tr>";
	print "<tr><td>ALT-R<br>&nbsp;</td><td>Configure repositories<br>&nbsp;</td></tr>";
	print "<tr><td>ALT-P</td><td>Delete an entity physically</td></tr>";
	print "<tr><td>ALT-E</td><td>Edit extra entity fields</td></tr>";
	print "<tr><td>ALT-C</td><td>Calendar</td></tr>";
	print "<tr><td>ALT-F<br>&nbsp;</td><td>Configure flextables<br>&nbsp;</td></tr>";
	print "<tr><td>ALT-D<br>&nbsp;</td><td>Check database<br>&nbsp;</td></tr>";
	print "<tr><td>ALT-M<br>&nbsp;</td><td>Open admin manual <img src='images/pdf.gif' alt=''><br>&nbsp;</td></tr>";
	print "</table></td></tr></table>";
	print "Please note: by default, Firefox uses ALT-SHIFT-[key] instead of just ALT.";
	EndHTML();

} elseif ($_REQUEST['st']) {

	ShowHeaders();
	$t = GetTemplateType($_REQUEST['st']);
	if ($t == "TEMPLATE_HTML") {

		$template = ParseTemplateGeneric(EvaluateTemplatePHP(GetTemplate($_REQUEST['st'])));
		$template = ReturnTemplateStyleSheet($_REQUEST['st']) . $template;

		if ($_REQUEST['eid'] && CheckEntityAccess($eid) != "nok") {
			$template = ParseTemplateEntity($template, $_REQUEST['eid'], false, false, false, "htme");
			$template = ParseTemplateCustomer($template, GetEntityCustomer($customerid), false, "htme");
		} elseif ($_REQUEST['cid'] && CheckCustomerAccess($eid) != "nok") {
			$template = ParseTemplateCustomer($template, $_REQUEST['cid'], false, "htme");
		}

		print ParseTemplateCleanup($template);
	} else {
		PrintAD("Wrong type or non-existent template");
	}
	EndHTML();

} elseif ($_REQUEST['unlock']) {
		
	ShowHeaders();
	print "Unlocking entity......";
	RemoveLocks();
	?>
	<script type="text/javascript">
	<!--
		window.close();
	//-->
	</script>
	<?php
	EndHTML();

} elseif ($_REQUEST['if_l']) {
		
	ShowHeaders();
	print "<table style='width: 100%;'><tr><td>&nbsp;&nbsp;</td><td>";
	print "<object id='freem' type='text/html' data='" . base64_decode($_REQUEST['if_l']) . "' style='width:98%;'></object>";
	print "</td></tr></table>";
	?>
	<script type="text/javascript">
	<!--
		document.getElementById("freem").height=document.body.clientHeight-60;
	//-->
	</script>
	<?php
	EndHTML();

} elseif ($_REQUEST['if_t'] || $_REQUEST['ShowTemplate']) {

	if (is_numeric($_REQUEST['ShowTemplate'])) {
		$_REQUEST['if_t'] = base64_encode($_REQUEST['ShowTemplate']);
	}
		
	ShowHeaders();
	qlog(INFO, "Printing template " . base64_decode($_REQUEST['if_t']) . " on request.");


	$template = GetTemplate(base64_decode($_REQUEST['if_t']));
	$template = ReturnTemplateStyleSheet(base64_decode($_REQUEST['if_t'])) . $template;
	if ($_REQUEST['eid']) {
		$ret = CheckEntityAccess($_REQUEST['eid']);
		if ($ret == "ok" || $ret == "readonly") {
			$template = ParseTemplateEntity($template, $_REQUEST['eid'], false, false, false);
		} else {
			print "<!-- no access to entity - template not parsed -->";
		}

	}
	$template = ParseTemplateGeneric($template);
	$template = ParseTemplateCleanup($template);
	$template = EvaluateTemplatePHP($template);
	print html_compress($template);
	//print ($template);
	EndHTML();

} elseif (isset($_REQUEST['MassUpdateButton'])) {

	ShowHeaders();

	$GLOBALS['CURFUNC'] = "MassUpdate::";

	$action   = $_REQUEST['SelectedAction'];
	$entities = $_REQUEST['AlterObjectProperty'];

	$from_url = $_REQUEST['FromURL'];

	ExpireReportCache();

	if (substr($action,0,12) == "pressButton_") {
		$ef_button_id = str_replace("pressButton_", "", $action);
		foreach($entities AS $entity) {
			if (CheckEntityAccess($entity) == "ok") {
				ProcessTriggers("ButtonPress" . $ef_button_id, $entity,"");
			}
		}

	} elseif (substr($action,0,2) == "s_") {
		$status_to = str_replace("s_", "", $action);
		$newstatus = GetStatusName($status_to);
		print "Status to " . $status_to . " which is " . $newstatus . "<br>";
		$GLOBALS['CURFUNC'] = "MassUpdate::";
		foreach($entities AS $entity) {
			if (CheckEntityAccess($entity) == "ok") {

				DataJournal($entity, GetEntityStatus($entity), $x[$status_to], "status", "entity");

				mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET status='" . mres($newstatus) . "' WHERE eid='" . mres($entity) . "'", $db);
				ExpireFormCache($entity);
				journal($entity, "Status set to " . $newstatus . " by MassUpdate");
				
				ProcessTriggers("status",$entity,$newstatus);
				ProcessTriggers("entity_change",$entity,"");
				$GLOBALS['CURFUNC'] = "MassUpdate::";
				qlog(INFO, "Eid " . $entity . " status set to " . $newstatus . " by MassUpdate");
			} else {
				qlog(INFO, "Access to entity " . $entity . " was denied!");
			}
		}
	} elseif (substr($action,0,2) == "p_") {
		$prio_to = str_replace("p_", "", $action);
		$newprio = GetPriorityName($prio_to);
		print "Priority to " . $prio_to . " which is " . $newprio . "<br>";
		$GLOBALS['CURFUNC'] = "MassUpdate::";
		foreach($entities AS $entity) {
			if (CheckEntityAccess($entity) == "ok") {

				DataJournal($entity, GetEntityPriority($entity), $newprio, "priority", "entity");

				mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET priority='" . mres($newprio) . "' WHERE eid='" . mres($entity) . "'", $db);
				$GLOBALS['CURFUNC'] = "MassUpdate::";
				ExpireFormCache($entity);
				journal($entity, "Priority set to " . $newprio . " by MassUpdate");
				qlog(INFO, "Eid " . $entity . " priority set to " . $newprio . " by MassUpdate");
				ProcessTriggers("priority",$entity,$newprio);
				ProcessTriggers("entity_change",$entity,"");
			} else {
				qlog(INFO, "Access to entity " . $entity . " was denied!");
			}
		}
	} elseif (substr($action,0,2) == "o_" || substr($action,0,2) == "a_") {
		$field = "";

		if (substr($action,0,2) == "o_") {
			$field = "owner";
			$val_to = str_replace("o_", "", $action);
		} elseif (substr($action,0,2) == "a_") {
			$field = "assignee";
			$val_to = str_replace("a_", "", $action);
		}

		$list = ReturnListOfAllowedUsers(false, false, false, false);
		$auth = false;
		foreach ($list AS $test) {
			if ($test['id'] == $val_to) {
				$auth = true;
			} 

		}


		if ($field != "" && is_numeric($val_to) && $auth) {

			print $field . " to " . $val_to . " which is " . GetUserName($val_to) . "<br>";
			$GLOBALS['CURFUNC'] = "MassUpdate::";
			foreach($entities AS $entity) {
				if (CheckEntityAccess($entity) == "ok") {

					if ($field == "assignee") {
						DataJournal($entity, GetEntityAssignee($entity), $x[$prio_to], "assignee", "entity");
					} elseif ($field == "owner") {
						DataJournal($entity, GetEntityOwner($entity), $x[$prio_to], "owner", "entity");
					}

					mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET " . $field . "='" . mres($val_to) . "' WHERE eid='" . mres($entity) . "'", $db);
					$GLOBALS['CURFUNC'] = "MassUpdate::";
					ExpireFormCache($entity);
					journal($entity, $field . " set to " . $val_to . " by MassUpdate");
					qlog(INFO, "Eid " . $entity . " " . $field . " set to " . $val_to . " by MassUpdate");
					ProcessTriggers($field,$entity,$val_to);
					ProcessTriggers("entity_change",$entity,"");
				} else {
					qlog(INFO, "Access to entity " . $entity . " was denied!");
				}
			}
		} else {
			qlog(INFO, "MassUpdate failed; data incorrect: $field - $val_to - $auth");
		}
	} elseif (substr($action,0,2) == "c_") {
		$customer_to = str_replace("c_", "", $action);
		$GLOBALS['CURFUNC'] = "MassUpdate::";
		foreach($entities AS $entity) {
			if (CheckEntityAccess($entity) == "ok") {
				mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET CRMcustomer='" . mres($customer_to) . "' WHERE eid='" . mres($entity) . "'", $db);
				$GLOBALS['CURFUNC'] = "MassUpdate::";
				ExpireFormCache($entity);
				journal($entity, "Customer set to " . $customer_to . " by MassUpdate");
				qlog(INFO, "Eid " . $entity . " customer set to " . $customer_to . " by MassUpdate");
				ProcessTriggers("customer",$entity,$customer_to);
				ProcessTriggers("entity_change",$entity,"");
			} else {
				qlog(INFO, "Access to entity " . $entity . " was denied!");
			}
		}
	} elseif ($action == "del") {
		$GLOBALS['CURFUNC'] = "MassUpdate::";
		// delete entities
		foreach($entities AS $entity) {
			if (CheckEntityAccess($entity) == "ok") {
				DeleteEntity($entity);
				journal($entity, "Entity deleted using MassUpdate");
				ProcessTriggers("status",$entity,$x[$prio_to]);
				ProcessTriggers("entity_change",$entity,"");
				$GLOBALS['CURFUNC'] = "MassUpdate::";
				ExpireFormCache($entity);
				qlog(INFO, "Eid " . $entity . " deleted by MassUpdate");
			} else {
				qlog(INFO, "Access to entity " . $entity . " was denied!");
			}
		}
	} elseif ($action == "undel") {
		$GLOBALS['CURFUNC'] = "MassUpdate::";
		// delete entities
		foreach($entities AS $entity) {
			if (CheckEntityAccess($entity) == "ok") {
				UnDeleteEntity($entity);
				journal($entity, "Entity un-deleted using MassUpdate");
				ExpireFormCache($entity);
				ProcessTriggers("status",$entity,$x[$prio_to]);
				ProcessTriggers("entity_change",$entity,"");
				$GLOBALS['CURFUNC'] = "MassUpdate::";
				qlog(INFO, "Eid " . $entity . " un-deleted by MassUpdate");
			} else {
				qlog(INFO, "Access to entity " . $entity . " was denied!");
			}
		}
	} elseif (substr($action, 0, 5) == "EFID_") {
		$x = explode("_", $action);
		$ef = $x[1];
		$val = base64_decode($x[2]);
			foreach($entities AS $entity) {
				
				DataJournal($entity, GetExtraFieldValue($entity, $ef, false, false, false), GetExtraFieldValue($entity, $ef, true, false, $val_to), $ef, "entity");
				SetExtraFieldValue($ef, $entity, $val);
				ProcessTriggers("entity_change",$entity,"");

			}
	}
	foreach($entities AS $entity) {
			if (CheckEntityAccess($entity) == "ok") {
				CalculateComputedExtraFields($entity);
			}
	}
	// In this case the last update window was accessed from the main list,
	// after saving, go back to the list.
	$fromlisturl = $_REQUEST['fromlisturl'];

	if (strstr($fromlisturl,"____STASH-")){
		$url_to_go_to = PopStashValue(str_replace("____STASH-","",$fromlisturl));
		$url_to_go_to = "index.php?ShowEntityList=1&tab=99";
	} elseif (strstr($fromlisturl,"____b64-")){
		$fromlisturl = str_replace("____b64-","",$fromlisturl);
		$url_to_go_to = base64_decode($fromlisturl);
		$url_to_go_to = "index.php?ShowEntityList=1&tab=99";
	} else {
		//$url_to_go_to = base64_decode($fromlisturl);
		$url_to_go_to = "index.php?ShowEntityList=1&tab=99";
	}
	if ($GLOBALS['---INTERRUPTMESSAGE']) {
		print "<br><br><img src='images/crmlogosmall.gif' alt=''>&nbsp;<a href='" . $url_to_go_to . "'>Dismiss messages</a>";
		EndHTML();
		exit;
	} elseif ($from_url <> "") {
			qlog(INFO, "Redirecting this user!");
			EndHTML();
			?>
			<script type="text/javascript">
			<!--
				document.location='<?php echo $from_url;?>';
			//-->
			</script>
			<?php
			exit;
	} else {
			qlog(INFO, "Redirecting this user!");
			EndHTML();
			?>
			<script type="text/javascript">
			<!--
				document.location='<?php echo $url_to_go_to;?>';
			//-->
			</script>
			<?php
			exit;
	}


} elseif (isset($_REQUEST['ShowEntityList'])) {

	ShowHeaders();
	print AjaxBox("ShowEntityList", true, "&BrowseArray=" . $GLOBALS['BrowseArray'] . "&mainList=1&fromlistAjaxHandler=" . PushStashValue($_SERVER['REQUEST_URI']), false, "MainEntityList");
	EndHTML();

} elseif (isset($_REQUEST['ShowCalendar'])) {

	ShowHeaders();
	print AjaxBox("ShowCalendar", true, "", false, false);
	EndHTML();

} elseif (isset($_REQUEST['ShowCustomerList'])) {
	
	ShowHeaders();
	if ($_REQUEST['ShowInlineSelectTable']) {
			$uri .= "ShowInlineSelectTable=true&nonavbar=1&SelectField=" . htme($_REQUEST['SelectField']);;
	}
	print AjaxBox("ShowCustomerList", true, "&BrowseArray=" . $GLOBALS['BrowseArray'] . "&mainList=1&" . $uri, false, "MainCustomerList");
	EndHTML();

} elseif (isset($_REQUEST['Author']) || isset($_REQUEST['EasterBunny'])) {

	ShowHeaders();
	Author();
	EndHTML();

} elseif (isset($_REQUEST['UserMessage'])) {
	
	ShowHeaders();
	UserMessage();
	EndHTML();

} elseif (isset($_REQUEST['CheckForValidSession'])) {
	
	$derest = "&CheckForValidSession=" . $_REQUEST['CheckForValidSession'];
	header("Location: dashboard.php?tab=1" . $derest);

} else {
	ShowHeaders();
	ShowDashboard();
	EndHTML();
}
?>