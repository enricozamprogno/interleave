<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This script generates RTF entity reports (NOT Reports!)
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */

require_once("initiate.php");

$start_time = date('U');

$GLOBALS['NOINLINEPHPEVAL'] = true;

if ($_REQUEST['template'] && $_REQUEST['attach_to_dossier'] && $_REQUEST['attach_to_entity'] && $_REQUEST['SingleEntity']) {

		if (strtoupper($GLOBALS['EnableEntityReporting'])<>"YES") {
			PrintAD("Access denied");
			EndHTML();
			exit;
		}
		if (IsValidEID($_REQUEST['SingleEntity'])) {
			if (CheckEntityAccess($_REQUEST['SingleEntity'])<>"ok" && CheckEntityAccess($_REQUEST['SingleEntity'])<>"readonly") {
				PrintAD("You don't have access to this entity");
				EndHTML();
				exit;
			} else {
				// Determine customer
				$customer = GetEntityCustomer($_REQUEST['SingleEntity']);
				$entity = true;
			}
		} elseif ($_REQUEST['FlexTable'] && $_REQUEST['FlexTableRecord']) {
			if (CheckFlextableRecordAccess($_REQUEST['FlexTable'], $_REQUEST['FlexTableRecord']) == "nok") {
				PrintAD("You don't have access to this flextable record");
				EndHTML();
				exit;
			} else {
				// all ok
			}
		}

		if ($_REQUEST['CustomerID'] && IsValidCID($_REQUEST['CustomerID']) && CheckCustomerAccess($_REQUEST['CustomerID']) != "nok") {
			$customer = $_REQUEST['CustomerID'];
		}

		

		// Get template from database
		qlog(INFO, "Retrieving template " . $_REQUEST['SingleEntity'] . " from database");

		$template = GetTemplate($_REQUEST['template']);

		$fn = GetTemplateName($_REQUEST['template']);
		$tmp_fn = explode(".", $fn);
		$ext = "." . strtolower($tmp_fn[(count($tmp_fn) -1)]);

		if ($ext == ".rtf") {

			$filename = str_replace(" ", "_", str_replace(".rtf","",GetTemplateName($_REQUEST['template'])) . "-" . date("Fj-Y-Hi") . "h.rtf");
			$ct = "Application/RTF";
			$rtf = true;
			if ($entity) {
				$template = ParseTemplateEntity($template,$_REQUEST['SingleEntity'],true, false, "dontformatnumbers", "FixRTF");
				
			}
			if ($customer) {
				$template = ParseTemplateCustomer  ($template,$customer, "dontformatnumbers", "FixRTF");
			}
			$template = ParseTemplateGeneric   ($template);

			if ($_REQUEST['FlexTable'] && $_REQUEST['FlexTableRecord']) {
				$template = ParseFlexTableTemplate($_REQUEST['FlexTable'], $_REQUEST['FlexTableRecord'], $template, false, true, "dontformatnumbers", "FixRTF");
				qlog(INFO, "HH Parsed it");
			}


		} elseif ($ext != ".xlsm" && $ext != ".xlsx" && $ext != ".docx" && $ext != ".odt" && $ext != ".ods") {


			$template = ReturnTemplateStyleSheet($_REQUEST['template']) . $template;
			$filename = str_replace(" ", "_", GetTemplateName($_REQUEST['template']));

			$template = ParseTemplateEntity    ($template,$_REQUEST['SingleEntity'],true, false, "dontformatnumbers", "plain");

			$template = ParseTemplateCustomer  ($template,$customer, "dontformatnumbers", false, "plain");

			$template = ParseTemplateGeneric   ($template);

			if ($_REQUEST['FlexTable'] && $_REQUEST['FlexTableRecord']) {
				$template = ParseFlexTableTemplate($_REQUEST['FlexTable'], $_REQUEST['FlexTableRecord'], $template, false, true, "dontformatnumbers", "plain");
				qlog(INFO, "HH Parsed it");
			}

			if (substr($filename, strlen($filename) - 4, 4) == ".xml") {
				$ct = "Application/XML";
				$GLOBALS['DONTFORMATNUMBERS'] = true;
			} else {
				$ct = "Application/Unknown";
			}
		} else {
			$template = ParseZippedContent($_REQUEST['template'], $_REQUEST['SingleEntity'], GetEntityCustomer($_REQUEST['SingleEntity']), $_REQUEST['FlexTable'], $_REQUEST['FlexTableRecord']);
			$nofurtherparse = true;
			$filename = $fn;
		}

		if (!$nofurtherparse) $template = ParseTemplateCleanUp   ($template);

		if ($attach_to_customer) {
			$x = AttachFile($customer,$filename,$template,"cust","RTF report document");
		}
		if ($_REQUEST['attach_to_entity'] == "Yes" && $_REQUEST['SingleEntity']) {
			$x = AttachFile($_REQUEST['SingleEntity'],$filename,$template,"entity","RTF report document");
		}

		//$template = strip_tags($template);

		header("Content-Type: " . $ct);
		header("Content-Disposition: attachment; filename=" . str_replace(" ", "_", $filename));
		header("Content-Description: Interleave Generated Data" );
		header("Window-target: _top");
		print $template;


} elseif($_REQUEST['SingleEntity']) {
	$_REQUEST['nonavbar'] = 1;
	ShowHeaders();
		if (strtoupper($GLOBALS['EnableEntityReporting'])<>"YES") {
			PrintAD("denied: reporting is disabled");
			EndHTML();
			exit;
		}
		if (CheckEntityAccess($_REQUEST['SingleEntity']) <> "ok" && CheckEntityAccess($_REQUEST['SingleEntity'])<>"readonly") {
			print "<img src='images/error.gif' alt=''>&nbsp;Private entity";
			EndHTML();
			exit;
		}
	print "<table><tr><td><form id='SingleReport' method='post' action=''><div class='showinline'>";
	print "<input type='hidden' name='SingleEntity' value='" . $_REQUEST['SingleEntity'] . "'>";
	print "<input type='hidden' name='edate' value='01-01-8000'>";
	print "<input type='hidden' name='sdate' value='01-01-2300'>";
	print "<input type='hidden' name='FlexTableRecord' value='" . $_REQUEST['FlexTableRecord'] . "'>";
	print "<input type='hidden' name='FlexTable' value='" . $_REQUEST['FlexTable'] . "'>";
	print "<input type='hidden' name='whichcust' value='" . $_REQUEST['SingleEntity'] . "'>";
	print "<table>";
	if (!$_REQUEST['FlexTable']) {
		print "<tr><td colspan='2'><strong>" . $lang['createreport'] . " " . $_REQUEST['SingleEntity'] . "</strong><br><br></td></tr>";
	} else {
		print "<tr><td colspan='2'><strong>" . $lang['createreport'] . " </strong><br><br></td></tr>";
	}
	print "<tr><td>" . $lang['rtftemplate'] . ":</td><td><select style='width: 250px;' name='template'>";
	$sql = "SELECT templateid,templatename,timestamp_last_change,username FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE (templatetype='TEMPLATE_REPORT' OR templatetype='TEMPLATE_PLAIN') ORDER BY templatename";
	$result = mcq($sql,$db);
	while ($row = mysql_fetch_array($result)) {
		if ($_REQUEST['template']==$row['templateid']) {
			$ins = "selected='selected'";
		} else {
			unset($ins);
		}
		print "<option $ins value = '" . $row['templateid'] ."'>" . $row['templatename'] . "</option>";
	}
	print "</select></td></tr>";
	//print "<tr><td>" . $lang['includelog'] . ":</td><td><select style='width: 250px;' name='includelog'><option value='0'>$lang[no]</option><option>$lang[yes]</option></select></td></tr>";
	if (IsValidEid($_REQUEST['SingleEntity'])) {
		print "<tr><td>" . $lang['attachindividualtocustomer'] . ":</td><td><select style='width: 250px;' name='attach_to_dossier'><option value='Yes'>$lang[yes]</option><option selected='selected' value='No'>$lang[no]</option></select></td></tr>";
		print "<tr><td>" . $lang['attachindividualtoentity'] . " ". $_REQUEST['SingleEntity'] . ":</td><td><select style='width: 250px;' name='attach_to_entity'><option value='No'>$lang[no]</option><option value='Yes'>$lang[yes]</option></select></td></tr>";
	}
	print "<tr><td><input type='button' name='submitknop' value='" . $lang['go'] . "' onclick='doso();'></div></form></td></tr></table>";
		?>
		<script type="text/javascript">
		<!--
		function doso() {
			parent.location="entityreport.php?SingleEntity=" + document.forms['SingleReport'].elements['SingleEntity'].value + "&whichcust=t&template=" + document.forms['SingleReport'].elements['template'].value + "&attach_to_dossier=" + document.forms['SingleReport'].elements['attach_to_dossier'].value + "&attach_to_entity=" + document.forms['SingleReport'].elements['attach_to_entity'].value + "&FlexTable=" + document.forms['SingleReport'].elements['FlexTable'].value + "&FlexTableRecord=" + document.forms['SingleReport'].elements['FlexTableRecord'].value ;
			parent.$.fancybox.close();
			window.close();

		}
		//-->
		</script>
		<?php
	EndHTML();
} elseif ($_REQUEST['BatchReport'] && $_REQUEST['whichcust']<>"bla") {
		//ShowHeaders();
		$cust_list	= array();
		$processed = array();

		if (strtoupper($GLOBALS['EnableEntityReporting'])<>"YES") {
			PrintAD("denied: reporting is disabled");
			EndHTML();
			exit;
		}
		$td1 = explode("-",$_REQUEST['sdate']); // dd-mm-yyyy
		$td2 = explode("-",$_REQUEST['edate']); // dd-mm-yyyy

		if ($td1 && $td2) {
			$SDATE = "$td1[2]-$td1[1]-$td1[0]";
			$EDATE = "$td2[2]-$td2[1]-$td2[0]";
			$SDATE_EPOCH = @mktime(0,0,0,$td1[1],$td1[0],$td1[2]);
			$EDATE_EPOCH = @mktime(0,0,0,$td2[1],$td2[0],$td2[2]);
			$GLOBALS['CURFUNC'] = "Report::";
			qlog(INFO, "Period - " . $SDATE . " to " . $EDATE . " diff: " . ($EDATE_EPOCH-$SDATE_EPOCH));

			if (($EDATE_EPOCH-$SDATE_EPOCH)<0) {
				// period is negative, swap them, help the user :)
				$tmp = $SDATE;
				$SDATE = $EDATE;
				$EDATE = $tmp;
				qlog(INFO, "Negative period diff - dates swapped");
				$SDATE_EPOCH = @mktime(0,0,0,$td2[1],$td2[0],$td2[2]);
				$EDATE_EPOCH = @mktime(0,0,0,$td1[1],$td1[0],$td1[2]);
				qlog(INFO, "Period - " . $SDATE . " to " . $EDATE . " diff: " . ($EDATE_EPOCH-$SDATE_EPOCH));
			}
			$SDATE_EPOCH--;
			$EDATE_EPOCH++;
		}
		$GLOBALS['CURFUNC'] = "Report::";
		if (!$_REQUEST['stashid']) {
			if ($_REQUEST['whichentities'] == "All") {
				// nothin'
				qlog(INFO, "Generating report over *all* entities");
			} elseif ($_REQUEST['whichentities'] == "All but inserted (not yet assigned)") {
				$LIMIT_ENTITIES = "AND owner<>'2147483647' AND assignee<>'2147483647'";
				qlog(INFO, "Generating report over all entities except inserted entities");
			} elseif ($_REQUEST['whichentities'] == "All but deleted") {
				qlog(INFO, "Generating report over all entities except deleted entities");
				$LIMIT_ENTITIES = "AND deleted<>'y'";
			} elseif ($_REQUEST['whichentities'] == "Only non-deleted and assigned") {
				$LIMIT_ENTITIES = " AND deleted<>'y' AND owner<>'2147483647' AND assignee<>'2147483647'";
				qlog(INFO, "Generating report over all entities except inserted and deleted entities");
			}
			if ($_REQUEST['HavingStatus'] == "All") {
				// nothin'
				qlog(INFO, "Generating report over *all* statusses");
			} else {
				$LIMIT_ENTITIES .= " AND status='" . mres($_REQUEST['HavingStatus']) . "'";
				qlog(INFO, "Generating report over status " . $_REQUEST['HavingStatus']);
			}

			if ($_REQUEST['HavingPriority'] == "All") {
				// nothin'
				qlog(INFO, "Generating report over *all* priorities");
			} else {
				$LIMIT_ENTITIES .= " AND priority='" . mres($_REQUEST['HavingPriority']) . "'";
				qlog(INFO, "Generating report over priority " . $_REQUEST['HavingPriority']);
			}

			if ($_REQUEST['whichcust'] == "Only active") {

				$LIMIT_ENTITIES .= " AND " . $GLOBALS['TBL_PREFIX'] . "customer.active<>'no'";

			} elseif ($_REQUEST['whichcust'] == "All") {
				// no limit
			} elseif (is_numeric($_REQUEST['whichcust'])) {

				$LIMIT_ENTITIES .= " AND " . $GLOBALS['TBL_PREFIX'] . "entity.CRMcustomer='" . mres($_REQUEST['whichcust']) . "'";
			}

			$base_query = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "entity," . $GLOBALS['TBL_PREFIX'] . "customer WHERE " . $GLOBALS['TBL_PREFIX'] . "entity.CRMcustomer=" . $GLOBALS['TBL_PREFIX'] . "customer.id " . $LIMIT_ENTITIES;
		} else {
			// yeah cool
			$base_query = PopStashValue($_REQUEST['stashid']);
		}
		$processed = array();
		// Get template from database
		qlog(INFO, "Retrieving template " . $_REQUEST['template'] . " from database");


		$template = GetTemplate($_REQUEST['template']);
		$template_name = $result['filename'];
		// Strip the RTF header
		$template = str_replace("{\\rtf1","",$template);
		// strip the last } (RTF footer)
		$orig_template = substr($template,0,strlen($template)-1);
		if ($base_query == "") {
			qlog(INFO, "Panic! No base query!");
			log_msq("ERROR: Panic! No base query!");
			$base_query = "SELECT 1-1 AS CRMCTT_ERROR_NO_BASE_QUERY";
		}
		$result_rp = mcq($base_query,$db);
		$filename = "Interleave-" . eregi_replace(".rtf","",$template_name) . "-" . date("Fj-Y-Hi") . "h.rtf";
		header("Content-Type: RTF");
		header("Content-Disposition: attachment; filename=" . $filename);
		header("Content-Description: Interleave Generated Data" );
		header("Window-target: _top");
		ob_end_flush();
		flush();
		qlog(INFO, "Send RTF download header to browser");
		// Start to output file (limits memory usage)

		print "{\\rtf1";
		while ($row=mysql_fetch_array($result_rp)) {
			if (CheckEntityAccess($row['eid']) != "nok") {

				if ($SDATE_EPOCH && $EDATE_EPOCH) {
					$ddtmp = explode("-",$row['duedate']); // dd-mm-yyyy
					$DUEDATE_EPOCH = @mktime(0,0,0,$ddtmp[1],$ddtmp[0],$ddtmp[2]);
					if (($DUEDATE_EPOCH>$SDATE_EPOCH && $DUEDATE_EPOCH<$EDATE_EPOCH)) {
						// all ok
						$donotinclude = false;
					} else {
						$donotinclude = true;
					}
				}

				if (!$donotinclude) {

					$template = $orig_template;

					journal($row['eid'],"A report (" . $filename . ") was created based on this entity","entity");
					$template = ParseTemplateEntity    ($template,$row['eid'], false, false, false, "FixRTF");
					$template = ParseTemplateCustomer  ($template,$row['CRMcustomer'], false, "FixRTF");
					$template = ParseTemplateGeneric   ($template, "FIxRTF");
				//	$template = ParseTemplateForRTF    ($template);
					$template = ParseTemplateCleanUp   ($template);


					// Just to be sure
					$template = strip_tags($template);

					array_push($processed,$row['eid']);
					if ($attach_to_customer) {
						$x = AttachFile($customer,$filename,"{\\rtf1" . $template . "}","cust","RTF report document");
					}
					if ($_REQUEST['attach_to_entity'] == "Yes") {
						$x = AttachFile($row['eid'],$filename,"{\\rtf1" . $template . "}","entity","RTF report document");
					}

					if ($not_1st) {
						print "\page " . $template;
						$totsize += strlen($template);
						ob_end_flush();
						flush();
					} else {
						print $template;
						$totsize += strlen($template);
						ob_end_flush();
						flush();
						$not_1st = true;
					}
				} // end if !$donotinclude
			} else {
				qlog(INFO, "Access to entity " . $row['eid'] . " denied.");
			}
		}

		print "}";

		foreach ($processed as $eid) {
				journal($eid,"This entity was used for generating a report");
				$sqlx .= "eid=" . $eid . " OR ";
		}
		if (is_administrator()) {

			if ($_REQUEST['DelAfterProcess']=="1") {
				$closeepoch = date('U');
				$sqlx = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET deleted='y', closeepoch='" . $closeepoch . "' WHERE ";
				foreach ($processed as $eid) {
					journal($eid,"Entity was deleted after generating report");
					$sqlx .= "eid=" . $eid . " OR ";
					$tel++;
				}
				$sqlx .= "1=0";
				mcq($sqlx,$db);
				uselogger ("$tel entities were deleted after generating reports","");
				qlog(INFO, "$tel entities were deleted after generating reports");
			}
			if ($_REQUEST['SetStatusTo']<>"All" && $_REQUEST['SetStatusTo']<>"") {
				$sqlx = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET status='" . mres($_REQUEST['SetStatusTo']) . "' WHERE ";
				foreach ($processed as $eid) {
					journal($eid,"Status set to " . $_REQUEST['SetStatusTo'] . " after generating report");
					$sqlx .= "eid=" . $eid . " OR ";
					$tel++;
				}
				$sqlx .= "1=0";
				mcq($sqlx,$db);
				uselogger ("$tel entities got status='" . $_REQUEST['SetStatusTo'] . "' after generating reports","");
				qlog(INFO, "$tel entities got status='" . $_REQUEST['SetStatusTo'] . "' after generating reports");
			}
			if ($_REQUEST['SetReadonlyTo']<>"All" && $_REQUEST['SetReadonlyTo']<>"") {
				$sqlx = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET readonly='y' WHERE ";
				foreach ($processed as $eid) {
					journal($eid,"Readonly set after generating report");
					$sqlx .= "eid=" . $eid . " OR ";
					$tel++;
				}
				$sqlx .= "1=0";
				mcq($sqlx,$db);
				uselogger ("$tel entities got a readonly flag after generating reports","");
				qlog(INFO, "$tel entities got a readonly flag after generating reports");
			}
			if ($_REQUEST['SetOwnerTo']<>"All" && $_REQUEST['SetOwnerTo']<>"") {
				$sqlx = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET owner='" . mres($_REQUEST['SetOwnerTo']) . "' WHERE ";
				foreach ($processed as $eid) {
					journal($eid,"Owner set to " . GetUserName($_REQUEST['SetOwnerTo']) . " after generating report");
					qlog	 ("Owner of $eid set to " . GetUserName($_REQUEST['SetOwnerTo']) . " after generating report");
					$sqlx .= "eid=" . $eid . " OR ";
					$tel++;
				}
				$sqlx .= "1=0";
				mcq($sqlx,$db);
			}
			if ($_REQUEST['SetAssigneeTo']<>"All" && $_REQUEST['SetAssigneeTo']<>"") {
				$sqlx = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET assignee='" . mres($_REQUEST['SetAssigneeTo']) . "' WHERE ";
				foreach ($processed as $eid) {
					journal($eid,"Assignee set to " . GetUserName($_REQUEST['SetAssigneeTo']) . " after generating report");
					qlog(INFO, "Assignee of $eid set to " . GetUserName($_REQUEST['SetAssigneeTo']) . " after generating report");
					$sqlx .= "eid=" . $eid . " OR ";
					$tel++;
				}
				$sqlx .= "1=0";
				mcq($sqlx,$db);
			}
		} // end if user is admin
		else {
			qlog(INFO, "Skipping update parse, this user is not an admin!");
		}
		// push actual file

		$GLOBALS['CURFUNC'] = "Report::";
		$end_time = date('U') - $start_time;
		qlog(INFO, "RTF Report presented. (took " . $end_time . " seconds)");
		$GLOBALS['CURFUNC'] = "Report::";
		qlog(INFO, "Total file size of this report is " . $totsize . " bytes");
		if ($totsize > 10367084) {
 		    $GLOBALS['CURFUNC'] = "Report::";
			$totsizeMB = round(($totsize/1024)/1024,2);
			qlog(WARNING, "WARNING - A requested report was larger than 10MB - decrease your template size! It's " . $totsizeMB ."MB! (" . $template_name . ")");
			uselogger("WARNING - A requested report was larger than 10MB - decrease your template size! It's " . $totsizeMB ." MB, which took ". $end_time . " seconds to create. (" . $template_name . ")","");
		}
		EndHTML(false);
} else {
	ShowHeaders();
	if ($_REQUEST['closepopup']) {
		?>
		<script type="text/javascript">
		<!--
			statusWin = window.open('summary.php?wait=1', 'statusWin','width=200,height=70,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');
			statusWin.close();
		//-->
		</script>

		<?php
	}
	if (strtoupper($GLOBALS['EnableEntityReporting'])<>"YES") {
			PrintAD("denied: reporting is disabled");
			EndHTML();
			exit;
	}
	if ($_REQUEST['nonavbar']) {
		print "<table><tr><td>";
	}
	print "<fieldset><legend>&nbsp;<img src='images/crmlogosmall.gif' alt=''>&nbsp;&nbsp;$lang[createreports]&nbsp;</legend>";
	print "<form id='editform' method='post' action=''><div class='showinline'><input type='hidden' name='BatchReport' value='1'><table style='width: 100%;'>";
	if (!$_REQUEST['stashid']) {
			print "<tr><td style='width: 250px;'>$lang[startdate] ($lang[lefae]):</td><td><input type='hidden' name='sdate'><input type='text' name='sdateHF' size='10' value='" . $_REQUEST['sdate'] . "' onclick=\"popcalendarSelect('editform.sdate',0);\" onkeyup=\"popcalendarSelect('editform.sdate',0);\"></td></tr>";

			print "<tr><td>$lang[enddate] ($lang[lefae]):</td><td><input type='hidden' name='edate'><input size='10' type='text' name='edateHF' value='" . $_REQUEST['edate'] . "' onclick=\"popcalendarSelect('editform.edate',0);\" onkeyup=\"popcalendarSelect('editform.date',0);\"></td></tr>";

			print "<tr><td>" . $lang['customers'] . ":</td><td>";

			if ($_REQUEST['whichcust']=="bla") {
					print "<select name='whichcust' size='1'>";
							$sql= "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "customer ORDER BY custname";
							$result= mcq($sql,$db);

							if ($SetCustTo) $ea[CRMcustomer] = $SetCustTo; // pre-set customer from customers page
							while ($CRMloginusertje= mysql_fetch_array($result)) {
								if ($CRMloginusertje[id]==$ea[CRMcustomer]) {
										$a = "selected='selected'";
										$Customer = $ea[CRMcustomer];
								} else {
										$a = "";
								}
								 print "<option value='$CRMloginusertje[id]' $a size='1'>$CRMloginusertje[custname]</option>";
							}
				print "</select>";
				print "<input type='hidden' name='whichcust' value='selected'>";
			} else {
				print "<select name='whichcust' style='width: 250px;'><option value='All'>" . $lang['all'] . "</option><option value='Only active'>$lang[onlyactive]</option><option value='bla'>$lang[selectsingle]</option></select>";
			}

			print "</td></tr>";
			print "<tr><td>" . $lang['entities'] . ":</td><td>";
			print "<select style='width: 250px;' name='whichentities'>";
			print "<option value='All except deleted'>$lang[alled]</option>";
			print "<option>" . $lang['all'] . "</option>";

			if (strtoupper($GLOBALS['EnableCustInsert']) == "YES") {
				print "<option value='All but inserted (not yet assigned)'>$lang[abdnya]</option>";
				print "<option value='Only non-deleted and assigned'>$lang[ondaa]</option>";
			}

			print "</select>";
			print "</td></tr>";
			// which status
			print "<tr><td>" . $lang['status'] . ":</td><td>";
			print "<select name='HavingStatus' style='width: 250px;'>";
			print "<option value='All'>" . $lang['all'] . "</option>";
			$sql = "SELECT varname,id,color FROM " . $GLOBALS['TBL_PREFIX'] . "statusvars ORDER BY listorder, varname";
			$result= mcq($sql,$db);
			while($options = mysql_fetch_array($result)) {
				if (strtoupper(($ea[status]))==strtoupper($options[varname])) { $a="selected='selected'"; } else { $a=""; }
				print "<option style='background:" . $options[color] . "' value='$options[varname]' $a>$options[varname]</option>";
			}
			print "</select></td></tr>";
			// which priority
			print "<tr><td>" . $lang['priority'] . ":</td><td>";
			print "<select name='HavingPriority' style='width: 250px;'>";
			print "<option value='All'>" . $lang['all'] . "</option>";
			$sql = "SELECT varname,id,color FROM " . $GLOBALS['TBL_PREFIX'] . "priorityvars ORDER BY listorder, varname";
			$result= mcq($sql,$db);
			while($options = mysql_fetch_array($result)) {
				if (strtoupper(($ea[status]))==strtoupper($options[varname])) { $a="selected='selected'"; } else { $a=""; }
				print "<option style='background:" . $options[color] . "' value='$options[varname]' $a>$options[varname]</option>";
			}
			print "</select></td></tr>";

	} else {
		print "<tr><td>" . $lang['alreadyselected'] . "<input type='hidden' name='stashid' value='" . $_REQUEST['stashid'] . "'></td></tr>";
	}
	if (is_administrator()) {
		print "<tr><td colspan='3'><hr><br>Admin:</td></tr>";
		print "<tr><td>$lang[deap]:</td><td><select style='width: 250px;' name='DelAfterProcess'><option value='0'>$lang[no]</option><option value='1'>$lang[yes]</option></td></tr>";
		print "<tr><td>$lang[apsest]:</td><td>";
		print "<select name='SetStatusTo' style='width: 250px;'>";
		print "<option value='All'>$lang[donothing]</option>";
		$sql = "SELECT varname,id,color FROM " . $GLOBALS['TBL_PREFIX'] . "statusvars ORDER BY listorder, varname";
		$result= mcq($sql,$db);
		while($options = mysql_fetch_array($result)) {
			if (strtoupper(($ea[status]))==strtoupper($options[varname])) { $a="selected='selected'"; } else { $a=""; }
			print "<option style='background:" . $options[color] . "' value='$options[varname]' $a>$options[varname]</option>";
		}
		print "</select></td></tr>";

		print "<tr><td>$lang[apseot]:</td><td>";
		print "<select style='width: 250px;' name='SetOwnerTo'>";
		print "<option value='All'>$lang[donothing]</option>";
		$sql= "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE LEFT(FULLNAME,3)<>'@@@' AND active<>'no' ORDER BY FULLNAME";
			$result= mcq($sql,$db);
			while ($CRMloginusertje= mysql_fetch_array($result)) {
					if ($CRMloginusertje[id]==$ea[owner]) {
									$a = "selected='selected'";
									$ok = 1;
					} else {
									$a = "";
					}
				if (!trim($CRMloginusertje[FULLNAME])== "") {
					print "<option value='$CRMloginusertje[id]' size='1' $a>$CRMloginusertje[FULLNAME]</option>";
				}
			}
		print "</select></td></tr>";
		print "<tr><td>$lang[apseat]:</td><td>";
		print "<select style='width: 250px;' name='SetAssigneeTo'>";
		print "<option value='All'>$lang[donothing]</option>";
		$sql= "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE LEFT(FULLNAME,3)<>'@@@' AND active<>'no' ORDER BY FULLNAME";
			$result= mcq($sql,$db);
			while ($CRMloginusertje= mysql_fetch_array($result)) {
					if ($CRMloginusertje[id]==$ea[owner]) {
									$a = "selected='selected'";
									$ok = 1;
					} else {
									$a = "";
					}
				if (!trim($CRMloginusertje[FULLNAME])== "") {
					print "<option value='$CRMloginusertje[id]' size='1' $a>$CRMloginusertje[FULLNAME]</option>";
				}
			}
		print "</select></td></tr>";
		print "<tr><td>$lang[apsrft]:</td><td>";
		print "<select style='width: 250px;' name='SetReadonlyTo'>";
		print "<option value='All'>$lang[donothing]</option>";
		print "<option value='1' size='1'>Read-only</option>";
		print "</select></td></tr>";
		print "<tr><td colspan='3'><hr></td></tr>";
	} // end if only admin
	print "<tr><td>$lang[rtftemplate]:</td><td><select style='width: 250px;' name='template'>";
	$sql = "SELECT templateid,templatename,timestamp_last_change,username FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_REPORT'";
	$result = mcq($sql,$db);
	while ($row = mysql_fetch_array($result)) {
		if ($_REQUEST['template']==$row['templateid']) {
			$ins = "selected='selected'";
		} else {
			unset($ins);
		}
		print "<option $ins value = '" . $row['templateid'] ."'>" . $row['templatename'] . "</option>";
	}
	print "</select></td></tr>";
	print "<tr><td>" . $lang['attachindividualtoentity'] . " ". $_REQUEST['SingleEntity'] . ":</td><td><select style='width: 250px;' name='attach_to_entity'><option value='No'>$lang[no]</option><option value='Yes'>$lang[yes]</option></select></td></tr>";
	print "<tr><td>$lang[attachindividualtocustomer]:</td><td><select style='width: 250px;' name='attach_to_dossier'><option>$lang[no]</option><option value='Yes'>$lang[yes]</option></select></td></tr>";
	print "<tr><td><input type='submit' name='submitknop' value='$lang[go]'></div></form></td></tr></table>";

	print "</fieldset><br><br>";
	EndHTML();
}
?>