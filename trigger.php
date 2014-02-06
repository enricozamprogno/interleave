<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 info@interleave.nl
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This function can set a trigger on a certain action
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
$_GET['SkipMainNavigation'] = true;
require_once("initiate.php");
	if (!is_array($GLOBALS['TriggerDays'])) {
		$GLOBALS['TriggerDays'] = explode(",", $GLOBALS['TriggerDays']);
	}
ShowHeaders();

if (strstr($_REQUEST['add'], "ButtonPress")) {
	$_REQUEST['add'] = "buttons";
} elseif (strstr($_REQUEST['add'], "DATE_EFID")) {
	$_REQUEST['add'] = "miscellaneous";
} elseif (strstr($_REQUEST['add'], "user_add")) {
	$_REQUEST['add'] = "miscellaneous";
} elseif (strstr($_REQUEST['add'], "LastUpdate_")) {
	$_REQUEST['add'] = "miscellaneous";
}
if ($_REQUEST['add'] && !$_REQUEST['filter']) {
	$_REQUEST['filter'] = $_REQUEST['add'];
}
if (CheckFunctionAccess("TriggerAdmin") == "nok") {
	PrintAD("Access to this page/function denied.");
} else {


	if (!$_REQUEST['nonavbar']) {
		AdminTabs("triggers");
	}
	AddBreadCrum("Triggers");
	$to_tabs = array("current","status","priority","customer","ownerassignee","extrafield","miscellaneous","admin");
	$tabbs["current"] = array("trigger.php?ovw=1" => "Overview", "comment" => "An overview of currently configured triggers.");
	$tabbs["status"] = array("trigger.php?add=status" => "New status trigger", "comment" => "Add a new trigger which fires when the status of an entity is altered");
	$tabbs["priority"] = array("trigger.php?add=priority" => "New priority trigger", "comment" => "Add a new trigger which fires when the priority of an entity is altered");
	$tabbs["customer"] = array("trigger.php?add=customer" => "New " . $lang['customer'] . " trigger", "comment" => "Add a new trigger which fires when the customer of an entity is altered");
	$tabbs["ownerassignee"] = array("trigger.php?add=ownerassignee" => "New owner/assignee trigger", "comment" => "Add a new trigger which fires when the owner or assignee of an entity is altered");
	$tabbs["extrafield"] = array("trigger.php?add=extrafield" => "New extra field trigger", "comment" => "Add a new trigger which fires when the value of an extra fiels is altered");
	$tabbs["miscellaneous"] = array("trigger.php?add=miscellaneous" => "New misc. trigger", "comment" => "Add a miscellaneous trigger (limited user add, limited user edit, duedate reached)");
	$tabbs["admin"] = array("trigger.php?add=admin" => "New administrative trigger", "comment" => "Add an administrative trigger.");
	if ($_REQUEST['add']) {
		$navid = $_REQUEST['add'];
	} else {
		$navid = "current";
	}
	if (!$_REQUEST['nonavbar']) {
		//InterTabs($to_tabs, $tabbs, $navid);
	}
	if ($_REQUEST['del_trigger']) {
		$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "triggers WHERE tid='" . mres($_REQUEST['del_trigger']) . "'";
		mcq($sql,$db);
		$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "triggerconditions WHERE triggerid='" . mres($_REQUEST['del_trigger']) . "'";
		mcq($sql,$db);
		$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "journal WHERE type='trigger' AND eid='" . mres($_REQUEST['del_trigger']) . "'";
		mcq($sql,$db);

		qlog(INFO, "Deleted trigger " . $_REQUEST['del_trigger']);
		uselogger("Deleted trigger " . $_REQUEST['del_trigger'],"");
	}

	if (isset($_REQUEST['toggle_trigger_off'])) {
		$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "triggers SET enabled='no'";
		mcq($sql,$db);
		?>
		<script type="text/javascript">
		<!--
			alert('All triggers are now switched off. No business rules will be applied as long as these triggers are disabled. Do no forget to enable them later!');
		//-->
		</script>
		<?php
	} elseif (isset($_REQUEST['toggle_trigger_on'])) {
		$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "triggers SET enabled='yes'";
		mcq($sql,$db);
	} elseif (isset($_REQUEST['toggle_mail_trigger_off'])) {
		$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "triggers SET enabled='no' WHERE action LIKE 'mail%'";
		mcq($sql,$db);
	}

	if ($_REQUEST['toggle_trigger']) {

		$tmp = db_GetRow("SELECT enabled FROM " . $GLOBALS['TBL_PREFIX'] . "triggers WHERE tid='" . mres($_REQUEST['toggle_trigger']) . "'");
		if ($tmp['enabled'] == "yes") {
			$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "triggers SET enabled='no' WHERE tid='" . mres($_REQUEST['toggle_trigger']) . "'";
			mcq($sql,$db);
		} else {
			$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "triggers SET enabled='yes' WHERE tid='" . mres($_REQUEST['toggle_trigger']) . "'";
			mcq($sql,$db);
		}
	}



	// ========================================================================================================================================================
	// Pre-fetch trigger content for later on
	if ($_REQUEST['fetch']) {
		$fetched_trigger = DB_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "triggers WHERE tid='" . mres($_REQUEST['fetch']) . "'");
		$_REQUEST['ExtraField'] = str_replace("EFID", "", str_replace("ButtonPress", "", $fetched_trigger['onchange']));
	}
	if ($fetched_trigger['tid'] != "") {
		$RunWithSystemRights = GetAttribute("trigger", "RunWithSystemRights", $fetched_trigger['tid']);
		if ($RunWithSystemRights == "") {
			SetAttribute("trigger", "RunWithSystemRights", "No", $fetched_trigger['tid'], array("No", "Yes"));
		}	
	}	
	$WhenAttachingFilesToMailAlsoAttachEarlierMailAttachements = GetAttribute("trigger", "WhenAttachingFilesToMailAlsoAttachEarlierMailAttachements", $fetched_trigger['tid']);
	if ($WhenAttachingFilesToMailAlsoAttachEarlierMailAttachements == "") {
		SetAttribute("trigger", "WhenAttachingFilesToMailAlsoAttachEarlierMailAttachements", "No", $fetched_trigger['tid'], array("No", "Yes"));
	}
	$WhenAttachingMailToRecordAlsoAttachAnyGeneratedReports = GetAttribute("trigger", "WhenAttachingMailToRecordAlsoAttachAnyGeneratedReports", $fetched_trigger['tid']);
	if ($WhenAttachingMailToRecordAlsoAttachAnyGeneratedReports == "") {
		SetAttribute("trigger", "WhenAttachingMailToRecordAlsoAttachAnyGeneratedReports", "Yes", $fetched_trigger['tid'], array("No", "Yes"));
	}
	
	$AlwaysAttachAllAttachmentsWhenMailing = GetAttribute("trigger", "AlwaysAttachAllAttachmentsWhenMailing", $fetched_trigger['tid']);
	if ($AlwaysAttachAllAttachmentsWhenMailing == "") {
		SetAttribute("trigger", "AlwaysAttachAllAttachmentsWhenMailing", "No", $fetched_trigger['tid'], array("No", "Yes"));
	}	
	if ($_REQUEST['AlwaysAttachAllAttachmentsWhenMailing'] == "Yes") {
		SetAttribute("trigger", "AlwaysAttachAllAttachmentsWhenMailing", "Yes", $fetched_trigger['tid'], array("No", "Yes"));
	} elseif (isset($_REQUEST['AlwaysAttachAllAttachmentsWhenMailing'])) {
		SetAttribute("trigger", "AlwaysAttachAllAttachmentsWhenMailing", "Yes", $fetched_trigger['tid'], array("No", "Yes"));
	}

	// ========================================================================================================================================================
	//PopTriggerConditionsChooser

	// Pre-set conditions or condition link
	if ($_REQUEST['fetch']) {
		$tid = $_REQUEST['fetch'];
		$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "triggerconditions WHERE triggerid='" . mres($tid) . "'";
		$t = db_GetArray($sql);

		if (sizeof($t) > 0) {
			$conditionstext .= "<tr><td>Conditions</td><td><span class='noway'>[conditions apply]</span>&nbsp;&nbsp;<a class='arrow' href='javascript:PopTriggerConditionsChooser(" . $_REQUEST['fetch'] . ");'>select</a></td></tr>";
		} else {
			$conditionstext = "<tr><td>Conditions</td><td>[none set]&nbsp;&nbsp;<a class='arrow' href='javascript:PopTriggerConditionsChooser(" . $_REQUEST['fetch'] . ");'>select</a></td></tr>";
		}
	}


	if ($_REQUEST['onchange'] && $_REQUEST['to_value'] && $_REQUEST['action']) {
		// First check if this trigger doesn't exist already
		$sql = "SELECT tid FROM " . $GLOBALS['TBL_PREFIX'] . "triggers WHERE onchange='" . mres($_REQUEST['onchange']) ."' AND action='" . mres($_REQUEST['action']) . "' AND to_value='" . mres($_REQUEST['to_value']) . "' AND template_fileid='" . mres($_REQUEST['template_fileid']) . "'";
		$result = mcq($sql,$db);
		$row = mysql_fetch_array($result);
		if ($row['tid'] && !$_REQUEST['fetched'] && $jeroen_wil_dit_niet_meer) {
			print "<table><tr><td><img src='images/error.gif' alt=''> Trigger not added, it exists already!</td></tr></table>";
			qlog(INFO, "NOT added trigger - it already exists");

		} else {
			if (substr($_REQUEST['action'],0,6) == "set ef") {
				$fieldnum = str_replace("set ef", "", $_REQUEST['action']);
				$value = $_REQUEST['EF_to_value'];
				$_REQUEST['action'] = "Update EFID" . $fieldnum . " to value " . $value;
			}
			if ($_REQUEST['fetched']) {
				$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "triggers SET onchange='" . mres($_REQUEST['onchange']) ."',action='" . mres($_REQUEST['action']) ."',to_value='" . mres($_REQUEST['to_value']) ."',template_fileid='" . mres($_REQUEST['template_fileid']) ."',attach='" . mres($_REQUEST['attach']) ."',report_fileid='" . mres($_REQUEST['report_fileid']) ."',comment='" . mres($_REQUEST['trigger_comment']) . "',on_form='" . mres($_REQUEST['on_form']) . "', mailtype='" . mres($_REQUEST['mailmethod']) . "', processorder='" . mres($_REQUEST['process_order']) . "' WHERE tid='" . mres($_REQUEST['fetched']) . "'";
				mcq($sql,$db);
				$tr = mysql_insert_id();
				qlog(INFO, "Updated trigger " . $tr);
				uselogger("Updated trigger " . $tr,"");
				if ($_REQUEST['ATTR_AllowUsersToSwitchThisTriggerOff']) {
					if ($_REQUEST['trigger_comment'] != "") {
						SetAttribute("trigger", "AllowUsersToSwitchThisTriggerOff", $_REQUEST['ATTR_AllowUsersToSwitchThisTriggerOff'], $fetched_trigger['tid'], array("No", "Yes"));
					} else {
						if ($_REQUEST['ATTR_AllowUsersToSwitchThisTriggerOff'] == "Yes") {
							PrintAD("You cannot enable the option to let users choose whether or not they want to receive e-mails generated by this trigger until you give this trigger a description in the comment field. The comment field is used to tell users what this trigger does. This text will be displayed on the profile page. The trigger was saved except for this setting.");
							EndHTML();
							exit();
						}
					}
				}
				if ($_REQUEST['closeafter']) {
?>
					<script type="text/javascript">
					<!--
						parent.$.fancybox.close();
					//-->
					</script>
					<?php
				} elseif ($_REQUEST['req_url']) {
					?>
					<script type="text/javascript">
					<!--
						document.location = '<?php echo base64_decode($_REQUEST['req_url']);?>';
					//-->
					</script>
					<?php
				}
			} else {
				// Insert the trigger
				$sql = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "triggers(onchange,action,to_value,template_fileid,attach,report_fileid,comment,on_form, mailtype) VALUES('" . mres($_REQUEST['onchange']) ."','" . mres($_REQUEST['action']) . "','" . mres($_REQUEST['to_value']) . "','" . mres($_REQUEST['template_fileid']) . "','" . mres($_REQUEST['attach']) . "','" . mres($_REQUEST['report_fileid']) . "','" . mres($_REQUEST['trigger_comment']) . "','" . mres($_REQUEST['on_form']) . "','" . mres($_REQUEST['mailmethod']) . "')";
				mcq($sql,$db);
				$tr = mysql_insert_id();
				mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "triggers SET processorder=" . ($tr * 10) . " WHERE tid=" . $tr, $db);
				if ($_REQUEST['ATTR_AllowUsersToSwitchThisTriggerOff']) {
					if ($_REQUEST['trigger_comment'] != "") {
						SetAttribute("trigger", "AllowUsersToSwitchThisTriggerOff", $_REQUEST['ATTR_AllowUsersToSwitchThisTriggerOff'], $fetched_trigger['tid'], array("No", "Yes"));
					} else {
						PrintAD("You cannot enable the option to let users choose whether or not they want to receive e-mails generated by this trigger until you give this trigger a description in the comment field. The comment field is used to tell users what this trigger does. This text will be displayed on the profile page. The trigger was saved except for this setting.");
						EndHTML();
						exit();
					}
				}
				qlog(INFO, "Added trigger " . $tr);
				uselogger("Added trigger " . $tr,"");
				$_REQUEST['fetch'] = $tr;
			}

		}
	}
	$actionlist = array();
	if ($_REQUEST['add']) {

		if (substr($_REQUEST['add'], 0, 10) == "user_add_p" || substr($fetched_trigger['onchange'], 0, 10) == "user_add_p") {
			array_push($actionlist,strtolower("<option $ins value='mail new user'>[mail newly created user]</option>"));
			if ($fetched_trigger['action'] == "mail admin") {
			$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,strtolower("<option $ins value='mail admin'>[mail admin]</option>"));

		} else {
			if ($fetched_trigger['action'] == "mail ower") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,strtolower("<option $ins value='mail owner'>[mail $lang[owner]]</option>"));
			if ($fetched_trigger['action'] == "mail assignee") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,strtolower("<option $ins value='mail assignee'>[mail $lang[assignee]]</option>"));
			if ($fetched_trigger['action'] == "mail customer_owner") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,strtolower("<option $ins value='mail customer_owner'>[mail $lang[customer] $lang[owner]]</option>"));

			if ($fetched_trigger['action'] == "mail admin") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,strtolower("<option $ins value='mail admin'>[mail admin]</option>"));
			if ($fetched_trigger['action'] == "mail customer") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,strtolower("<option $ins value='mail customer'>[mail $lang[customer]]</option>"));
			if ($fetched_trigger['action'] == "mail [users boss]") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='mail [users boss]'>[mail user's boss]</option>");
			//------------------
			if ($fetched_trigger['action'] == "mail [assignees boss]") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='mail [assignees boss]'>[mail assignee's boss]</option>");
			//------------------
			if ($fetched_trigger['action'] == "mail [owners boss]") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='mail [owners boss]'>[mail owner's boss]</option>");
			if ($fetched_trigger['action'] == "mail [email sender]") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='mail [email sender]'>[mail email sender] (only for email updates &amp; inserts)</option>");

			$groups = GetGroups();
			foreach ($groups AS $group) {
				
				if ($fetched_trigger['action'] == "mail group @" . $group['id'] . "@") {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}

				array_push($actionlist,"<option $ins value='mail group @" . $group['id'] . "@'>Mail to all users in group [" . htme($group['name']) . "]</option>");
			
			}


			$eflist = GetExtraFields();
			foreach ($eflist AS $field) {
				if ($field['fieldtype'] == "mail") {
					if ($fetched_trigger['action'] == "mail user @MEFID" . $field['id'] . "@") {
						$ins = "selected='selected'";
					} else {
						unset($ins);
					}

					array_push($actionlist,"<option $ins value='mail user @MEFID" . $field['id'] . "@'>Mail to address specified in extra field [" . htme($field['name']) . "]</option>");
				}
			}
			$eflist = GetExtraCustomerFields();
			foreach ($eflist AS $field) {
				if ($field['fieldtype'] == "mail") {
					if ($fetched_trigger['action'] == "mail user @MEFID" . $field['id'] . "@") {
						$ins = "selected='selected'";
					} else {
						unset($ins);
					}

					array_push($actionlist,"<option $ins value='mail user @MEFID" . $field['id'] . "@'>Mail to address specified in extra field [" . htme($field['name']) . "]</option>");
				}
			}
			$eflist = GetExtraUserFields();
			foreach ($eflist AS $field) {
				if ($field['fieldtype'] == "mail") {
					if ($fetched_trigger['action'] == "mail user @UEFID" . $field['id'] . "@") {
						$ins = "selected='selected'";
					} else {
						unset($ins);
					}
					array_push($actionlist,"<option $ins value='mail user @UEFID" . $field['id'] . "@'>Mail to address specified in extra user field [" . htme($field['name']) . "] of current user</option>");

				}
			}
			$eflist = GetExtraGroupFields();
			foreach ($eflist AS $field) {
				if ($field['fieldtype'] == "mail") {
					if ($fetched_trigger['action'] == "mail user @GEFID" . $field['id'] . "@") {
						$ins = "selected='selected'";
					} else {
						unset($ins);
					}
					array_push($actionlist,"<option $ins value='mail user @GEFID" . $field['id'] . "@'>Mail to address specified in extra group field [" . htme($field['name']) . "] of current user</option>");
				}
			}

			$eflist = GetFlexTableDefinitions(false, "one to many", true);
		//	print "<pre>";

		//	print_r($eflist);
			foreach ($eflist AS $table) {
				$tmp = GetExtraFlexTableFields($table['recordid']);
				//print_r($tmp);
				foreach ($tmp AS $field)
				{
					if ($field['fieldtype'] == "mail")
					{
						if ($fetched_trigger['action'] == "mail user @FTEFID" . $table['recordid'] . "#" . $field['id'] . "@")
						{
							$ins = "selected='selected'";
						}
						else
						{
							unset($ins);
						}
						array_push($actionlist,"<option " . $ins . " value='mail user @FTEFID" . $table['recordid'] . "#" . $field['id'] . "@'>Mail to address specified in extra field [" . htme($field['name']) . "] in flextable " . $table['tablename'] . "</option>");
					}
				}
			}


			array_push($actionlist,strtolower("<option value=''>--------- global entity changes -----------</option>"));
			if ($fetched_trigger['action'] == "delete entity") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,strtolower("<option $ins value='delete entity'>[delete the entity]</option>"));
			if ($fetched_trigger['action'] == "undelete entity") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,strtolower("<option $ins value='undelete entity'>[undelete the entity]</option>"));
			if ($fetched_trigger['action'] == "make entity read-only") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,strtolower("<option $ins value='make entity read-only'>[make entity/flexrecord read-only]</option>"));
			if ($fetched_trigger['action'] == "make entity read-write") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,strtolower("<option $ins value='make entity read-write'>[make entity/flexrecord read-write]</option>"));
			if ($fetched_trigger['action'] == "make entity private") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,strtolower("<option $ins value='make entity private'>[make entity private]</option>"));
			if ($fetched_trigger['action'] == "make entity public") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,strtolower("<option $ins value='make entity public'>[make entity public]</option>"));
			if ($fetched_trigger['action'] == "set startdate") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,strtolower("<option $ins value='set startdate'>[set entity start date to current date]</option>"));
			if ($fetched_trigger['action'] == "set closedate") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,strtolower("<option $ins value='set closedate'>[set entity closure date (stop-clock)]</option>"));
			if ($fetched_trigger['action'] == "unset closedate") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,strtolower("<option $ins value='unset closedate'>[delete entity closure date (start-clock)]</option>"));
			if ($fetched_trigger['action'] == "re-set opendate") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,strtolower("<option $ins value='re-set opendate'>[re-set entity open date (restart-clock)]</option>"));
			array_push($actionlist,strtolower("<option value=''>---------- data alteration-- --------------</option>"));
			
			
			if ($_REQUEST['ExtraField'] && is_numeric(GetExtraFieldTableType($_REQUEST['ExtraField']))) {
				$eflist = GetExtraFlexTableFields(GetExtraFieldTableType($_REQUEST['ExtraField']));
			} elseif ($_REQUEST['ExtraField'] && GetExtraFieldTableType($_REQUEST['ExtraField']) == "customer") {
				$eflist = GetExtraCustomerFields();
			} else {
				$eflist = GetExtraFields();
			}
			if ($_REQUEST['add'] == "miscellaneous") {
				$eflist = array_merge(GetExtraFields(), GetExtraCustomerFields());
				foreach (GetFlexTableDefinitions() AS $ft) {
					$eflist = array_merge($eflist, GetExtraFlexTableFields($ft['recordid']));
				}
			}
			foreach ($eflist AS $field) {
				$subs = explode(" to value ", $fetched_trigger['action']);
				if (strstr("Update EFID " . $field['id'], $subs[0])) {
					$ins = "selected='selected'";
					$Update_to_value = htme($subs[1]);
				} else {
					unset($ins);
				}
				if ($field['fieldtype'] <> "Button") {
					array_push($actionlist,strtolower("<option $ins value='set EF " . $field['id'] . "'>update extra field [" . htme($field['name']) . "]</option>"));
				}
			}
		
			array_push($actionlist,strtolower("<option value=''>---------- report popups ------------------</option>"));
			$sql = "SELECT templateid,templatename,timestamp_last_change,username FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_REPORT'";
			$result = mcq($sql,$db);
			while ($row = mysql_fetch_array($result)) {
					array_push($actionlist,"<option value = 'popup report:" . $row['templateid'] ."'>[pop-up parsed report: " .htme($row['templatename']) . "]</option>");
			}
			array_push($actionlist,strtolower("<option value=''>---------- status changes -----------------</option>"));
			$sql = "SELECT varname,id,color FROM " . $GLOBALS['TBL_PREFIX'] . "statusvars ORDER BY listorder,varname";
			$result= mcq($sql,$db);
			while($options = mysql_fetch_array($result)) {
				if ($fetched_trigger['action'] == "Set status to " . $options['varname']) {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				array_push($actionlist,"<option $ins style='background-color: " . $options['color'] . ";' value='Set status to " . htme($options['varname']) . "'>Set status to " . htme($options['varname']) . "</option>");
			}
			array_push($actionlist,strtolower("<option value=''>---------- priority changes ---------------</option>"));
			$sql = "SELECT varname,id,color FROM " . $GLOBALS['TBL_PREFIX'] . "priorityvars ORDER BY listorder,varname";
			$result= mcq($sql,$db);
			while($options = mysql_fetch_array($result)) {
				if ($fetched_trigger['action'] == "Set priority to " . $options['varname']) {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				array_push($actionlist,"<option $ins style='background-color: " . $options['color'] . ";' value='Set priority to " . htme($options['varname']) . "'>Set priority to " . htme($options['varname']) . "</option>");
			}
			array_push($actionlist,strtolower("<option value=''>---------- owner changes ------------------</option>"));
			if ($fetched_trigger['action'] == "Set owner to [users boss]") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='Set owner to [users boss]'>Set owner to [user's boss]</option>");
			// -------------------
			if ($fetched_trigger['action'] == "Set owner to [assignees boss]") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='Set owner to [assignees boss]'>Set owner to [assignee's boss]</option>");
			// -------------------
			if ($fetched_trigger['action'] == "Set owner to [owners boss]") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='Set owner to [owners boss]'>Set owner to [owner's boss]</option>");

			// -------------------

			if ($fetched_trigger['action'] == "Set owner to [current user]") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='Set owner to [current user]'>Set owner to [current user]</option>");

			// -------------------

			if ($fetched_trigger['action'] == "Set owner to [current assignee]") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='Set owner to [current assignee]'>Set owner to [current assignee]</option>");

			// -------------------
			if ($fetched_trigger['action'] == "Set owner to [current owner]") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='Set owner to [current owner]'>Set owner to [current owner]</option>");

			// -------------------
			if ($fetched_trigger['action'] == "Set owner to customer_owner") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='Set owner to customer_owner'>Set owner to [customer owner]</option>");
			$sql = "SELECT id, name, FULLNAME FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE LEFT(FULLNAME,3) <>'@@@' AND name NOT LIKE 'deleted_user%' ORDER BY FULLNAME";
			$result = mcq($sql,$db);
			while ($row = mysql_fetch_array($result)) {
				if ($row['FULLNAME'] == "") {
					$row['FULLNAME'] = $row['name'];
				}
				if ($fetched_trigger['action'] == "Set owner to " . $row['id']) {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				array_push($actionlist,"<option $ins value='Set owner to " . $row['id'] . "'>Set owner to " . htme($row['FULLNAME']) . "</option>");
			}

			$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE fieldtype LIKE 'User-list%' OR fieldtype LIKE 'Users of%'";
			$result = mcq($sql,$db);
			while ($row = mysql_fetch_array($result)) {
				if ($fetched_trigger['action'] == "Set owner to @EFID" . $row['id'] . "@") {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				if ($row['tabletype'] == "entity") {
					array_push($actionlist,"<option $ins value='Set owner to @EFID" . $row['id'] . "@'>Set owner to the user selected in " . $lang['entity'] . "-field " . htme($row['name']) . "</option>");
				} elseif (is_numeric($row['tabletype'])) {
					array_push($actionlist,"<option $ins value='Set owner to @EFID" . $row['id'] . "@'>Set owner to the user selected in flextable-field " . htme($row['name']) . "</option>");
				} else {
					array_push($actionlist,"<option $ins value='Set owner to @EFID" . $row['id'] . "@'>Set owner to the user selected in " . $lang['customer'] . "-field " . htme($row['name']) . "</option>");
				}
			}
			array_push($actionlist,strtolower("<option value=''>---------- assignee changes ---------------</option>"));
			if ($fetched_trigger['action'] == "Set assignee to [user's boss]") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='Set assignee to [users boss]'>Set assignee to [user's boss]</option>");
			// -------------------

			if ($fetched_trigger['action'] == "Set assignee to [current user]") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='Set assignee to [current user]'>Set assignee to [current user]</option>");

			// -------------------

			if ($fetched_trigger['action'] == "Set assignee to [current assignee]") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='Set assignee to [current assignee]'>Set assignee to [current assignee]</option>");

			// -------------------
			if ($fetched_trigger['action'] == "Set assignee to [current owner]") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='Set assignee to [current owner]'>Set assignee to [current owner]</option>");

			$sql = "SELECT id, name, FULLNAME FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE LEFT(FULLNAME,3)<>'@@@' AND name NOT LIKE 'deleted_user%' ORDER BY FULLNAME";
			$result = mcq($sql,$db);
			if ($fetched_trigger['action'] == "Set assignee to customer_owner") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option " . $ins . " value='Set assignee to customer_owner'>Set assignee to [customer owner]</option>");
			while ($row = mysql_fetch_array($result)) {
				if ($row['FULLNAME'] == "") {
					$row['FULLNAME'] = $row['name'];
				}
				if ($fetched_trigger['action'] == "Set assignee to " . $row['id']) {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				array_push($actionlist,"<option $ins value='Set assignee to " . $row['id'] . "'>Set assignee to " . htme($row['FULLNAME']) . "</option>");
			}
			$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE fieldtype LIKE 'User-list%' OR fieldtype LIKE 'Users of%'";
			$result = mcq($sql,$db);
			while ($row = mysql_fetch_array($result)) {

				if ($fetched_trigger['action'] == "Set assignee to @EFID" . $row['id'] . "@") {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				if ($row['tabletype'] == "entity") {
					array_push($actionlist,"<option $ins value='Set assignee to @EFID" . $row['id'] . "@'>Set assignee the user selected in " . $lang['entity'] . "-field " . htme($row['name']) . "</option>");
				} elseif (is_numeric($row['tabletype'])) {
					array_push($actionlist,"<option $ins value='Set assignee to @EFID" . $row['id'] . "@'>Set assignee to the user selected in flextable-field " . htme($row['name']) . "</option>");
				} else {
					array_push($actionlist,"<option $ins value='Set assignee to @EFID" . $row['id'] . "@'>Set assignee the user selected in " . $lang['customer'] . "-field " . htme($row['name']) . "</option>");
				}
			}
			array_push($actionlist,strtolower("<option value=''>---------- mail user actions --------------</option>"));

			$sql = "SELECT id, name, FULLNAME FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE EMAIL<>'' AND LEFT(FULLNAME,3) <>'@@@' AND name NOT LIKE 'deleted_user%' ORDER BY FULLNAME";
			$result = mcq($sql,$db);
			while ($row = mysql_fetch_array($result)) {
				if ($row['FULLNAME'] == "") {
					$row['FULLNAME'] = $row['name'];
				}
				if ($fetched_trigger['action'] == "mail user @" . $row['id'] . "@") {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				array_push($actionlist,"<option $ins value='mail user @" . $row['id'] . "@'>Mail user " . htme($row['FULLNAME']) . "</option>");
			}
			$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE fieldtype LIKE 'User-list%' OR fieldtype LIKE 'Users of%' AND deleted!='y'";
			$result = mcq($sql,$db);
			while ($rij = mysql_fetch_array($result)) {
				if ($fetched_trigger['action'] == "Mail user @EFID" . $rij['id'] . "@" || $fetched_trigger['action'] == "Mail user @FTEFID" . $rij['tabletype'] . "#" . $rij['id'] . "@") {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				if ($rij['tabletype'] == "entity") {
					array_push($actionlist,"<option $ins value='Mail user @EFID" . $rij['id'] . "@'>Mail the user selected in " . $lang['entity'] . "-field " . htme($rij['name']) . "</option>");
				} elseif (is_numeric($rij['tabletype'])) {
					$tmp = GetFlextableDefinitions($rij['tabletype']);
					array_push($actionlist,"<option " . $ins . " value='Mail user @FTEFID" . $rij['tabletype'] . "#" . $rij['id'] . "@'>Mail the user selected in flextable-field " . htme($rij['name']) . " in flextable " . $rij['tabletype'] . ": " . $tmp[0]['tablename'] . "</option>");
				} else {
					array_push($actionlist,"<option " . $ins . " value='Mail user @EFID" . $rij['id'] . "@'>Mail the user selected in " . $lang['customer'] . "-field " . htme($rij['name']) . "</option>");
				}
			}
			array_push($actionlist,strtolower("<option value=''>---------- mail customer actions ----------</option>"));
			$sql = "SELECT id,custname,contact_email FROM " . $GLOBALS['TBL_PREFIX'] . "customer WHERE contact_email<>'' ORDER BY custname";
			$result = mcq($sql,$db);
			while ($row = mysql_fetch_array($result)) {
				if ($fetched_trigger['action'] == "mail cust @" . $row['id'] . "@") {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				array_push($actionlist,"<option $ins value='mail cust @" . $row['id'] . "@'>Mail " . strtolower($lang['customer']) . " " . htme($row['custname']) . "</option>");
			}



			/*
		Set duedate to today
		Set duedate to tomorrow
		Set duedate 2 days from now
		Set duedate 3 days from now
		Set duedate 4 days from now
		Set duedate 5 days from now
		Set duedate 6 days from now
		Set duedate to one week from now
		Set duedate to two weeks from now
		*/
			array_push($actionlist,strtolower("<option value=''>------------ set duedate actions ----------</option>"));
			//-------
			if ($fetched_trigger['action'] == "duedate_set days 0") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_set days 0'>Set duedate to today</option>");
			//-------
			if ($fetched_trigger['action'] == "duedate_set days 1") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_set days 1'>Set duedate to tomorrow</option>");
			//-------
			if ($fetched_trigger['action'] == "duedate_set days 2") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_set days 2'>Set duedate 2 days from now</option>");
			//-------
			if ($fetched_trigger['action'] == "duedate_set days 3") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_set days 3'>Set duedate 3 days from now</option>");
			//-------
			if ($fetched_trigger['action'] == "duedate_set days 4") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_set days 4'>Set duedate 4 days from now</option>");
			//-------
			if ($fetched_trigger['action'] == "duedate_set days 5") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_set days 5'>Set duedate 5 days from now</option>");
			//-------
			if ($fetched_trigger['action'] == "duedate_set days 6") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_set days 6'>Set duedate 6 days from now</option>");
			//-------
			if ($fetched_trigger['action'] == "duedate_set days 7") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_set days 7'>Set duedate one week from now</option>");
			//-------
			if ($fetched_trigger['action'] == "duedate_set days 14") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_set days 14'>Set duedate two weeks from now</option>");
			//-------
			if ($fetched_trigger['action'] == "duedate_set days delete") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_set days delete'>Delete duedate</option>");
			$t = GetExtraCustomerFields();
			foreach ($t AS $field) {
				if ($field['fieldtype'] == "date" || GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Date") {
					if ($fetched_trigger['action'] == "duedate_set CEFID" . $field['id']) {
						$ins = "selected='selected'";
					} else {
						unset($ins);
					}
					array_push($actionlist,"<option $ins value='duedate_set CEFID" . $field['id'] . "'>Set duedate to date set in " . $lang['customer'] . " field [" . htme($field['name']) . "]</option>");
				}
			}
		//--------------------------------------------------------------------------------------------------
			array_push($actionlist,strtolower("<option value=''>---------- extend duedate actions ---------</option>"));
			if ($fetched_trigger['action'] == "duedate_extent days 1") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_extent days 1'>Extend duedate with 1 day</option>");
			//-------
			if ($fetched_trigger['action'] == "duedate_extent days 2") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_extent days 2'>Extend duedate with 2 days</option>");
			//-------
			if ($fetched_trigger['action'] == "duedate_extent days 3") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_extent days 3'>Extend duedate with 3 days</option>");
			if ($fetched_trigger['action'] == "duedate_extent days 4") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_extent days 4'>Extend duedate with 4 days</option>");
			if ($fetched_trigger['action'] == "duedate_extent days 5") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_extent days 5'>Extend duedate with 5 days</option>");
			if ($fetched_trigger['action'] == "duedate_extent days 7") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_extent days 7'>Extend duedate with 7 days</option>");
			if ($fetched_trigger['action'] == "duedate_extent days 10") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_extent days 10'>Extend duedate with 10 days</option>");
			if ($fetched_trigger['action'] == "duedate_extent days 14") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_extent days 14'>Extend duedate with 14 days</option>");

			if ($fetched_trigger['action'] == "duedate_extent days 20") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_extent days 20'>Extend duedate with 20 days</option>");


			if ($fetched_trigger['action'] == "duedate_extent days 21") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_extent days 21'>Extend duedate with 21 days</option>");

			if ($fetched_trigger['action'] == "duedate_extent days 28") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_extent days 28'>Extend duedate with 28 days</option>");

			if ($fetched_trigger['action'] == "duedate_extent days 30") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_extent days 30'>Extend duedate with 30 days</option>");
			
			if ($fetched_trigger['action'] == "duedate_extent days 42") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			array_push($actionlist,"<option $ins value='duedate_extent days 42'>Extend duedate with 42 days</option>");

			array_push($actionlist,strtolower("<option value=''>---------- change entity form id ----------</option>"));
			$sql = "SELECT templateid,templatename,template_subject FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML_FORM'";
			$result = mcq($sql,$db);
			while ($row = mysql_fetch_array($result)) {
				if ($fetched_trigger['action'] == "set form-id to " . $row['templateid']) {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				array_push($actionlist,"<option $ins value='set form-id to " . $row['templateid'] . "'>Set entity form to " . htme($row['templatename']) . " (" . htme($row['template_subject']) . ")</option>");
			}
			// Available modules
			array_push($actionlist,strtolower("<option value=''>---------- run module actions -------------</option>"));
			$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "modules ORDER BY module_name";
			$rs = mcq($sql, $db);
			while ($row = mysql_fetch_array($rs)) {
				if ($fetched_trigger['action'] == "run module " . $row['mid'] . "") {
					$ins = "selected='selected'";
					$show_module_code = $row['mid'];
				} else {
					unset($ins);
				}
				array_push($actionlist,"<option $ins value='run module " . $row['mid'] . "'>Run module " . htme($row['module_name']) . "</option>");
			}

			array_push($actionlist,strtolower("<option value=''>---------- interrupt navigation -----------</option>"));
			$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML' ORDER BY templatename";
			$rs = mcq($sql, $db);
			while ($row = mysql_fetch_array($rs)) {
				if ($fetched_trigger['action'] == "display template " . $row['templateid'] . "") {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				array_push($actionlist,"<option $ins value='display template " . $row['templateid'] . "'>Display template " . htme($row['templatename']) . " (stops navigation)</option>");
			}



			
		}
	}
	unset($tabbs);

	$triggerlist = array();
	array_push($triggerlist,"status");
	array_push($triggerlist,"priority");
	array_push($triggerlist,"owner");
	array_push($triggerlist,"assignee");
	array_push($triggerlist,"customer");
	array_push($triggerlist,"@EF_");

	$templatelist = array();
	$sql = "SELECT templateid,templatename,templatetype FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML'";
	$result = mcq($sql,$db);
	while ($row = mysql_fetch_array($result)) {
		if ($fetched_trigger['template_fileid'] == $row['templateid']) {
			$ins = "selected='selected'";
		} else {
			unset($ins);
		}
		array_push($templatelist,"<option $ins value='" . $row['templateid'] . "'>" . htme($row['templatename']) . "</option>");
	}

	$tabbs["status"] =			array("trigger.php?add=status","Status", "Status triggers");
	$tabbs["priority"] =		array("trigger.php?add=priority","Priority", "Priority triggers");
	$tabbs["customer"] =		array("trigger.php?add=customer", $lang['customer'], "Customer triggers");
	$tabbs["ownerassignee"] =	array("trigger.php?add=ownerassignee", "Owner & assignee", "Owner & assignee triggers");
	$tabbs["extrafield"] =		array("trigger.php?add=extrafield", "Extra field", "Triggers on extra fields");
	$tabbs["buttons"] =			array("trigger.php?add=buttons", "Buttons", "Triggers on buttons");
	$tabbs["miscellaneous"] =	array("trigger.php?add=miscellaneous", "Miscellaneous", "Other trigger types");
	$tabbs["admin"] =			array("trigger.php?add=admin", "Administrative", "System maintenance triggers");


	$report_select = "<select name='report_fileid'><option value=''>Don't attach</option>";
	if ($fetched_trigger['report_fileid']=="2147483647") {
		$ins = "selected='selected'";
	} else {
		unset($ins);
	}
	$report_select .= "<option value='2147483647' $ins>Std. PDF-report</option>";
	$sql = "SELECT templateid,templatename,timestamp_last_change,username FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE (templatetype='TEMPLATE_REPORT' OR templatetype='TEMPLATE_REPORT_PDF' OR templatetype='TEMPLATE_PLAIN')";
	$result = mcq($sql,$db);
	while ($row = mysql_fetch_array($result)) {
		if ($fetched_trigger['report_fileid']==$row['templateid']) {
			$ins = "selected='selected'";
		} else {
			unset($ins);
		}
		$report_select .= "<option $ins value = '" . $row['templateid'] ."'>" . $row['templatename'] . "</option>";
	}
	if ($fetched_trigger['report_fileid']=="2147483646") {
		$ins = "selected='selected'";
	} else {
		unset($ins);
	}

	$report_select .= "<option $ins value = '2147483646'>Attach all record attachments</option>";
	$report_select .= "</select>";

	$mailoptionsdiv = "<tr><td></td><td>";
	$mailoptionsdiv .= "<div id=\"mailoptionsdiv\">";
	$mailoptionsdiv .= "<table>";
	$mailoptionsdiv .= "<tr><td>Attach report to mail</td><td>";
	$mailoptionsdiv .= $report_select;
	$mailoptionsdiv .= "</td></tr>";
	$mailoptionsdiv .= "<tr><td>Mail template</td><td><select name='template_fileid'>";
	$mailoptionsdiv .= "<option value=''>[default]</option>";
			foreach ($templatelist AS $template) {
				$mailoptionsdiv .= $template;
			}
	$mailoptionsdiv .= "</select></td></tr>";
	$mailoptionsdiv .= "<tr><td>Attach all file attachments to mail</td><td>";
//
	$mailoptionsdiv .= "<input type=\"checkbox\" name=\"AlwaysAttachAllAttachmentsWhenMailing\" value=\"Yes\"";
	if ($AlwaysAttachAllAttachmentsWhenMailing == "Yes") {
		$mailoptionsdiv .= " checked=\"checked\"";
	}	
	$mailoptionsdiv .= "></td></tr>";
	//$mailoptionsdiv .= $report_select;
	$mailoptionsdiv .= "</td></tr>";
	
		
	
	$mailoptionsdiv .= "<tr><td>Mail method </td><td><select name='mailmethod'>";
	if ($fetched_trigger['mailtype'] == "inmail") {
		$in1 = "selected='selected'";
	} else {
		$in2 = "selected='selected'";
	}
	$mailoptionsdiv .= "<option value='email' " . $in2 . ">E-mail</option>";
	$mailoptionsdiv .= "<option value='inmail' " . $in1 . ">Interleave message</option>";
	$mailoptionsdiv .= "</select></td></tr>";
	if ($fetched_trigger['attach'] == "y") {
		$ins = "selected='selected'";
	} else {
		unset($ins);
	}
	$mailoptionsdiv .= "<tr><td>Attach mail to entity/customer</td><td><select name='attach'><option value='n'>No</option><option $ins value='y'>Yes</option></select></td></tr>";
	if (stristr($fetched_trigger['action'], "mail")) {

		$AllowUsersToSwitchThisTriggerOff = GetAttribute("trigger", "AllowUsersToSwitchThisTriggerOff", $fetched_trigger['tid']);
		if ($AllowUsersToSwitchThisTriggerOff == "") {
			SetAttribute("trigger", "AllowUsersToSwitchThisTriggerOff", "No", $fetched_trigger['tid'], array("No", "Yes"));
		}
		if ($AllowUsersToSwitchThisTriggerOff == "Yes") {
			$ins = " selected='selected'";
		} else {
			$ins = "";
		}
		$mailoptionsdiv .= "<tr><td>Allow users to switch this mail trigger off for themselves only, when receiving e-mail generated by this trigger</td><td><select name='ATTR_AllowUsersToSwitchThisTriggerOff'><option value='No'>No</option><option $ins value='Yes'>Yes</option></select></td></tr>";

	} else {
		SetAttribute("trigger", "AllowUsersToSwitchThisTriggerOff", "", $fetched_trigger['tid']);
	}

	$mailoptionsdiv .= "</table></div></td></tr>";

	print "<table style='width: 100%'><tr><td valign='top' class='nwrp'>";
	// LEFT NAVIGATION PANE
	if (!$_REQUEST['nonavbar']) {
		print "<div class='light-small'>Search triggers:<br><form id='fsform1' method='get' action=''><div class='showinline'><input type='hidden' name='templates' value='1'><img src='images/searchbox.png' alt='' class='search_img'><input class='search_input' type='search' onchange=\"document.forms['fsform1'].submit();\" name='fssearch' value='" . htme($_REQUEST['fssearch']) . "'>&nbsp;<input type='submit' name='Go' value='Go'></div></form></div>";

		if ($_REQUEST['fssearch'] != "") {
			$q = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "triggers WHERE comment LIKE '%" . mres($_REQUEST['fssearch']). "%' OR action LIKE '%" . mres($_REQUEST['fssearch']). "'";
		
			$sres= db_GetArray($q);

			if (count($sres) == 1 && !$_REQUEST['add']) {
				$_REQUEST['editextrafield'] = $sres[0]['id'];
				if ($sres[0]['tablename']) {
					$_REQUEST['tabletype'] = $sres[0]['tablename'];
				} else {
					$_REQUEST['tabletype'] = $sres[0]['tabletype'];
				}

			} elseif (count($sres) == 0) {
				print "<div class='light-small'>No triggers found matching criteria</div>";
			}
		}


		print "<ul class='normal'>";

		foreach ($tabbs AS $nav => $val) {
			$localul = "<li>";

			if ($_REQUEST['nav'] == $nav) {
				$disp1 = "none";
				$disp2 = "inline";
			} else {
				$disp1 = "inline";
				$disp2 = "none";
			}

			$link = str_replace("add", "filter" , $val[0]);
			$addlink = $val[0];

			//$localul .= "<div id='". $nav . "divplus' style='display: " . $disp1 . ";'><a onclick=\"document.getElementById('". $nav . "div').style.display='block';document.getElementById('". $nav . "divplus').style.display='none';document.getElementById('". $nav . "divmin').style.display='inline';\"><img src='images/t_plus.jpg' alt=''></a></div><div style='display: " . $disp2 . ";' id='". $nav . "divmin' style='display: inline;'><a onclick=\"document.getElementById('". $nav . "div').style.display='none';document.getElementById('". $nav . "divmin').style.display='none';document.getElementById('". $nav . "divplus').style.display='inline';\"><img src='images/t_minus.jpg' alt=''></a></div>&nbsp;<a href='" . $link . "' " . PrintToolTipCode($val[2]) . ">" . str_replace(" ", "&nbsp;", $val[1]) . "</a> [<a href='" . $addlink . "'>new</a>]";

			$localul .= "<img class=\"expand\" title=\"INTERLEAVE" . md5($nav) . "div\" style='display: inline; cursor: pointer' src='images/t_plus.jpg' alt=''>&nbsp;&nbsp;<a href='" . $link . "' " . PrintToolTipCode($val[2]) . ">" . str_replace(" ", "&nbsp;", $val[1]) . "</a> [<a href='" . $addlink . "'>new</a>]";

			switch ($nav) {
				case "extrafield":
						$qins = " AND onchange LIKE 'EFID%'";
				break;
				case "buttons":
						$qins = " AND onchange LIKE 'ButtonPress%'";
				break;

				case "status":
					$qins = " AND onchange = 'status'";
				break;
				case "priority":
					$qins = " AND onchange = 'priority'";
				break;
				case "customer":
					$qins = " AND onchange = 'customer'";
				break;
				case "ownerassignee":
					$qins = " AND (onchange = 'owner' OR onchange='assignee')";
				break;
				case "admin":
					$qins = " AND (onchange = 'log_warning' OR onchange='log_error' OR onchange='admin_request' OR onchange='user_login')";
					$qins = " AND to_value='Administrative trigger'";
				break;

				case "miscellaneous":

					$qins = " AND to_value='miscellaneous trigger' AND onchange NOT LIKE 'ButtonPress%'";

				break;
			}



			if ($_REQUEST['fssearch'] != "") {
				$q = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "triggers WHERE(comment LIKE '%" . mres($_REQUEST['fssearch']). "%' OR onchange LIKE '%" . mres($_REQUEST['fssearch']). "%' OR to_value LIKE '%" . mres($_REQUEST['fssearch']). "%') " . $qins . " ORDER BY onchange, processorder, tid";
				$sres = db_GetArray($q);
			}

			$q = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "triggers WHERE 1=1 " . $qins . " ORDER BY onchange, processorder, tid";
			$sresdiv = db_GetArray($q);

		//	print $qins . "<br>";

			$maxtriggers = db_GetRow("SELECT MAX(id) FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields");


			if (count($sres) > 0) {
				foreach ($sres AS $res) {
					if (!$nf) {
						$localul .= "<ul>";
					}
					$nf = true;
					for ($t=$maxtriggers[0];$t>0;$t--) {
						$res['action'] = str_replace("EFID " . $t, GetExtraFieldName($t), $res['action']);
					}
					if (strstr($res['onchange'], "ButtonPress")) {
						$res['onchange'] = "\"" . GetExtraFieldName(str_replace("ButtonPress", "", $res['onchange'])) . "\"";
					}

					if ($res['tid'] == $_REQUEST['fetch']) {
						$localul .= "<li>" . fillout($res['onchange'],20) . "::" . fillout($res['action'],20) . "</li>";
					} else {
						$link = "trigger.php?add=" . $nav . "&amp;fetch=" . $res['tid'];
						$localul .= "<li><a " . PrintToolTipCode("Onchange:&nbsp;" . $res['onchange'] . "<br>To value:&nbsp;" . $res['to_value'] . "<br>Action:&nbsp;" . $res['action'] . "<br>Comments:&nbsp;" . $res['comment']) . " href='" . $link . "'>" . fillout($res['onchange'], 05) . "::" . fillout($res['action'], 20) . "</a></li>";
					}
				}

				if ($nf) {
					unset($nf);
					$localul .= "</ul>";
				}
			}

			foreach ($sresdiv AS $res) {
					if ($_REQUEST['add'] == $nav || $_REQUEST['filter'] == $nav) {
						$disp = "block";
					} else {
						$disp = "none";
					}
					for ($t=$maxtriggers[0];$t>0;$t--) {
						$res['action'] = str_replace("EFID " . $t, GetExtraFieldName($t), $res['action']);
					}
					if (strstr($res['onchange'], "ButtonPress")) {
						$res['onchange'] = "\"" . GetExtraFieldName(str_replace("ButtonPress", "", $res['onchange'])) . "\"";
					}


					if (!$nf) {
						$localul .= "<div id='INTERLEAVE". md5($nav) . "div' style='display: " . $disp . ";'><ul>";
					}
					$nf = true;
					if ($res['tid'] == $_REQUEST['fetch']) {
							$localul .= "<li>" . fillout($res['onchange'],20) . "::" . fillout($res['action'],20) . "</li>";
					} else {
						$link = "trigger.php?add=" . $nav . "&amp;fetch=" . $res['tid'];
						$localul .= "<li><a " . PrintToolTipCode("Onchange:&nbsp;" . $res['onchange'] . "<br>To value:&nbsp;" . $res['to_value'] . "<br>Action:&nbsp;" . $res['action'] . "<br>Comments:&nbsp;" . $res['comment']) . " href='" . $link . "'>" . fillout($res['onchange'],20) . "::" . fillout($res['action'], 20) . "</a></li>";
					}
			}
			if ($nf) {
				unset($nf);
				$tp = true;
				$localul .= "</ul></div>";
			}

			$localul .= "</li>";
			print $localul;
		}

		print "</ul>";
	} // End if !nonavbar

	print "</td><td valign='top'>";
	if ($_REQUEST['fetch']) {
		print "<h1><a title='Journal' href=\"javascript:poptriggerjournal(" . $_REQUEST['fetch'] . ");\"><img src='images/journal.gif' alt=''></a> &nbsp;Editing trigger " . htme($_REQUEST['fetch']) . "</h1>";
		print "&nbsp;&nbsp;" . AttributeLink("trigger", $fetched_trigger['tid']);
		
	}

	if ($RunWithSystemRights == "Yes") {
		print "&nbsp;&nbsp;<p><span class=\"noway\">Warning: this trigger will be ran with system rights!</span></p>";
	} elseif ($fetched_trigger['tid'] != "") {
		print "&nbsp;&nbsp;<p><span class=\"grey\">This trigger will be ran with the credentials of the user who triggers it. Keep that in mind when configuring data changes or use elevation to avoid access denied issues (see attributes).</span></p>";
	}




	// ========================================================================================================================================================
	if ($_REQUEST['add'] == "status") {
		print "<form method='post' action='' id='TriggerAddForm'><div class='showinline'>";
		if ($_REQUEST['fetch']) {
			print "<input type='hidden' name='fetched' id='JS_fetched' value='" . htme($_REQUEST['fetch']) . "'>";
		}
		print "<table class='nicetable'>";
		//print "<tr><td colspan='5'><br><strong><span class='underln'>" . $lang['status'] . "</span></strong></td></tr>";
		print "<tr><td>On value change of field</td><td><strong>$lang[status]<input type='hidden' name='onchange' value='status'></strong></td></tr>";
		print "<tr><td>When the value is updated to</td><td><select name='to_value' id='JS_to_value'>";
		print "<option value='@SE@'>[something else]</option>";
		$sql = "SELECT varname,id,color FROM " . $GLOBALS['TBL_PREFIX'] . "statusvars ORDER BY listorder, varname";
			$result= mcq($sql,$db);
			while($options = mysql_fetch_array($result)) {
				if ($options['varname'] == $fetched_trigger['to_value']) {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				print "<option " . $ins . " style='background-color: " . $options['color'] . ";' value='" . htme($options['varname']) . "'>" . htme($options['varname']) . "</option>";
			}
		print "</select>";
		print ReturnDropDownSearchField("JS_to_value");
		print "</td></tr><tr><td class='nwrp'>Perform action</td><td class='nwrp'><select name='action' id='JS_action' onchange='CheckMailTrigger();'>";
		foreach ($actionlist AS $action) {
			print $action;
		}
		print "</select>";
		print ReturnDropDownSearchField("JS_action");
		print "</td></tr>";
		
		print "<tr><td>Comment</td><td><textarea cols='50' rows='3' name='trigger_comment'>" . htme($fetched_trigger['comment']) . "</textarea></td></tr>";

		// Execute on form element
		print "<tr><td>Execute only on form</td><td>";
		print "<select name='on_form'>";
		print "<option value='all'>[all forms]</option>";
		$sql = "SELECT templateid, templatename, template_subject FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML_FORM'";
		$result = mcq($sql, $db);
		while ($row = mysql_fetch_array($result)) {
			if ($row['templateid'] == $fetched_trigger['on_form']) {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			$x++;
			print "<option " . $ins . " value='" . $row['templateid'] . "'>" . htme($row['templatename']) . " (" . htme($row['template_subject']) . ")</option>";
		}
		print "</select>";
		print "</td></tr>";
		// End execute on form element
		if ($fetched_trigger['tid']) print "<tr><td>Process order</td><td " . PrintToolTipCode("Triggers are processed in this order") . "><input type='text' size='3' name='process_order' value='" . htme($fetched_trigger['processorder']) . "'></td></tr>";

		print $conditionstext;
		print "<tr><td>Update to value:</td><td><div id='EF_Update' style='display: none'>";
		print "<input type='text' name='EF_to_value' size='50' value='" . htme($Update_to_value) . "'>";
		print "<br><br>you can use template tags like @CUSTOMER@ and @EID@ for text, @TODAY@ for date fields and @NOW@ for date/time fields.";
		print "</div></td></tr>";
		print "<tr><td colspan='2'><input type='hidden' name='add' value=''><input type='hidden' name='closeafter' value='" . htme($_REQUEST['closeafter']) . "'><input type='hidden' name='req_url' value='" . htme($_REQUEST['req_url']) . "'>";
		print "<br><br><input type='submit' value='Save'>&nbsp;";
		print "<input type='submit' value='Save as new trigger' onclick=\"document.getElementById('JS_fetched').value='';\">&nbsp;";
		if (is_numeric($_REQUEST['fetch'])) {
			print "&nbsp;<input type='button' onclick=\"if (confirm('Sure?')) { document.location='trigger.php?del_trigger=" . htme($_REQUEST['fetch']) . "'; }\" value='Delete this trigger'>";
		}

		print "</td></tr>";
		print "</table>";
		print "</div></form>";
		print "</td></tr>";
		print "<tr><td colspan='10'>&nbsp;</td></tr>";
	}
	// ========================================================================================================================================================
	if ($_REQUEST['add'] == "priority") {
		print "<form method='post' action='' id='TriggerAddForm'><div class='showinline'>";
		if ($_REQUEST['fetch']) {
			print "<input type='hidden' name='fetched' id='JS_fetched' value='" . htme($_REQUEST['fetch']) . "'>";
		}
		print "<table class='nicetable'>";
		//print "<tr><td colspan='5'><br><strong><span class='underln'>" . $lang[''] . "</span></strong></td></tr>";
		//print "<tr><td>On value change of field</td><td>When the value is updated to</td><td class='nwrp'>Perform action</td><td>Attach report to mail (mail only)</td><td>Mail template</td><td>Attach to entity/customer</td></tr>";

		print "<tr><td>On value change of field</td><td><strong>$lang[priority]<input type='hidden' name='onchange' value='priority'></strong></td></tr>";
		print "<tr><td>When the value is updated to</td><td><select name='to_value' id='JS_to_value'>";
		print "<option value='@SE@'>[something else]</option>";
		$sql = "SELECT varname,id,color FROM " . $GLOBALS['TBL_PREFIX'] . "priorityvars ORDER BY listorder, varname";
			$result= mcq($sql,$db);
			while($options = mysql_fetch_array($result)) {
				if ($options['varname'] == $fetched_trigger['to_value']) {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				print "<option " . $ins . " style='background-color: " . $options['color'] . ";' value='" . htme($options['varname']) . "'>" . htme($options['varname']) . "</option>";
			}
		print "</select>";
		print ReturnDropDownSearchField("JS_to_value");
		print "</td></tr><tr><td class='nwrp'>Perform action</td><td class='nwrp'><select name='action' id='JS_action' onchange='CheckMailTrigger();'>";
		foreach ($actionlist AS $action) {
			print $action;
		}
		print "</select>";
		print ReturnDropDownSearchField("JS_action");
		print "</td></tr>";
		print $mailoptionsdiv;

		print "<tr><td>Comment</td><td><textarea cols='50' rows='3' name='trigger_comment'>" . htme($fetched_trigger['comment']) . "</textarea></td></tr>";
		// Execute on form element
		print "<tr><td>Execute only on form</td><td>";
		print "<select name='on_form'>";
		print "<option value='all'>[all forms]</option>";
		$sql = "SELECT templateid, templatename, template_subject FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML_FORM'";
		$result = mcq($sql, $db);
		while ($row = mysql_fetch_array($result)) {
			if ($row['templateid'] == $fetched_trigger['on_form']) {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			$x++;
			print "<option " . $ins . " value='" . $row['templateid'] . "'>" . htme($row['templatename']) . " (" . htme($row['template_subject']) . ")</option>";
		}		print "</select>";
		print "</td></tr>";
		// End execute on form element
		if ($fetched_trigger['tid']) print "<tr><td>Process order</td><td " . PrintToolTipCode("Triggers are processed in this order") . "><input type='text' size='3' name='process_order' value='" . htme($fetched_trigger['processorder']) . "'></td></tr>";

		print $conditionstext;
		print "<tr><td>Update to value:</td><td><div id='EF_Update' style='display: none'>";
		print "<input type='text' name='EF_to_value' style='background-color: #EBB8B8' size='50' value='" . htme($Update_to_value) . "'>";
		print "<br><br>you can use template tags like @CUSTOMER@ and @EID@.";
		print "</div></td></tr>";
		print "<tr><td colspan='2'><input type='hidden' name='add' value=''><input type='hidden' name='req_url' value='" . htme($_REQUEST['req_url']) . "'>";
		print "<br><br><input type='submit' value='Save'>&nbsp;";
		print "<input type='submit' value='Save as new trigger' onclick=\"document.getElementById('JS_fetched').value='';\">&nbsp;";
		if (is_numeric($_REQUEST['fetch'])) {
			print "&nbsp;<input type='button' onclick=\"if (confirm('Sure?')) { document.location='trigger.php?del_trigger=" . htme($_REQUEST['fetch']) . "'; }\" value='Delete this trigger'>";
		}
		print "</td></tr>";
		print "</table>";
		print "</div></form>";
		print "</td></tr>";
		print "<tr><td colspan='10'>&nbsp;</td></tr>";
	}
	// ========================================================================================================================================================
	//print "<tr><td colspan='5'><br><strong><span class='underln'>" . $lang['owner'] . "/" . $lang['assignee'] . "</span></strong></td></tr>";
	if ($_REQUEST['add'] == "ownerassignee" || $_REQUEST['add'] == "assignee" || $_REQUEST['add'] == "owner") {
		print "<form method='post' action='' id='TriggerAddForm'><div class='showinline'>";
		if ($_REQUEST['fetch']) {
			print "<input type='hidden' name='fetched' id='JS_fetched' value='" . htme($_REQUEST['fetch']) . "'>";
		}
		print "<table class='nicetable'>";
		//print "<tr><td>On value change of field</td><td>When the value is updated to</td><td class='nwrp'>Perform action</td><td>Attach report to mail (mail only)</td><td>Mail template</td><td>Attach to entity/customer</td></tr>";
		print "<tr><td>On value change of field</td><td>";
		print "<select name='onchange'>";
		if ($fetched_trigger['onchange'] == "assignee") {
			$ins = "selected='selected'";
		}
		print "<option value='owner'>" . $lang['owner'] . "</option>";
		print "<option value='assignee' " . $ins . ">" . $lang['assignee'] . "</option>";
		print "</select>";
		print "</td></tr><tr><td>When the value is updated to</td><td><select name='to_value' id='JS_to_value'>";
		print "<option value='@SE@'>[something else]</option>";
		$sql = "SELECT FULLNAME,id FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE name NOT LIKE 'deleted_user%' ORDER BY FULLNAME";
			$result= mcq($sql,$db);
			while($options = mysql_fetch_array($result)) {
				if ($options['id'] == $fetched_trigger['to_value']) {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				print "<option value='" . $options['id'] . "' " . $ins . ">" . htme($options['FULLNAME']) . "</option>";
			}
		$sql = "SELECT name,id FROM " . $GLOBALS['TBL_PREFIX'] . "userprofiles WHERE name NOT LIKE 'deleted_user%' ORDER BY id";
		$result= mcq($sql,$db);
		while($options = mysql_fetch_array($result)) {
			if ("G:" . $options['id'] == $fetched_trigger['to_value']) {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option value='G:" . $options['id'] . "' " . $ins . ">Someone in group " . htme($options['name']) . "</option>";
		}
		print "</select>";
		print ReturnDropDownSearchField("JS_to_value");
		print "</td></tr><tr><td class='nwrp'>Perform action</td><td class='nwrp'><select name='action' id='JS_action' onchange='CheckMailTrigger();'>";
		foreach ($actionlist AS $action) {
			print $action;
		}
		print "</select>";
		print ReturnDropDownSearchField("JS_action");
		print "</td></tr>";
		
		print $mailoptionsdiv;

		print "<tr><td>Comment</td><td><textarea cols='50' rows='3' name='trigger_comment'>" . htme($fetched_trigger['comment']) . "</textarea></td></tr>";
		// Execute on form element
		print "<tr><td>Execute only on form</td><td>";
		print "<select name='on_form'>";
		print "<option value='all'>[all forms]</option>";
		$sql = "SELECT templateid, templatename, template_subject FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML_FORM'";
		$result = mcq($sql, $db);
		while ($row = mysql_fetch_array($result)) {
			if ($row['templateid'] == $fetched_trigger['on_form']) {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			$x++;
			print "<option " . $ins . " value='" . $row['templateid'] . "'>" . htme($row['templatename']) . " (" . htme($row['template_subject']) . ")</option>";
		}
		print "</select>";
		print "</td></tr>";
		if ($fetched_trigger['tid']) print "<tr><td>Process order</td><td " . PrintToolTipCode("Triggers are processed in this order") . "><input type='text' size='3' name='process_order' value='" . htme($fetched_trigger['processorder']) . "'></td></tr>";

		// End execute on form element
		if ($fetched_trigger['tid']) print "<tr><td>Process order</td><td " . PrintToolTipCode("Triggers are processed in this order") . "><input type='text' size='3' name='process_order' value='" . htme($fetched_trigger['processorder']) . "'></td></tr>";

		print $conditionstext;
		print "<tr><td>Update to value:</td><td><div id='EF_Update' style='display: none'>";
		print "<input type='text' name='EF_to_value' style='background-color: #EBB8B8' size='50' value='" . htme($Update_to_value) . "'>";
print "<br><br>you can use template tags like @CUSTOMER@ and @EID@.";
		print "</div></td></tr>";
		print "<tr><td colspan='2'><input type='hidden' name='add' value=''><input type='hidden' name='req_url' value='" . htme($_REQUEST['req_url']) . "'>";
		print "<br><br><input type='submit' value='Save'>&nbsp;";
		print "<input type='submit' value='Save as new trigger' onclick=\"document.getElementById('JS_fetched').value='';\">&nbsp;";
		if (is_numeric($_REQUEST['fetch'])) {
			print "&nbsp;<input type='button' onclick=\"if (confirm('Sure?')) { document.location='trigger.php?del_trigger=" . htme($_REQUEST['fetch']) . "'; }\" value='Delete this trigger'>";	
		}
		print "</td></tr>";
		print "</table>";
		print "</div></form>";
		print "</td></tr>";
		print "<tr><td colspan='10'>&nbsp;</td></tr>";
	}
	// ========================================================================================================================================================
	if ($_REQUEST['add'] == "customer") {
		print "<form method='post' action='' id='TriggerAddForm'><div class='showinline'>";
		if ($_REQUEST['fetch']) {
			print "<input type='hidden' name='fetched' id='JS_fetched' value='" . htme($_REQUEST['fetch']) . "'>";
		}
		print "<table class='nicetable'>";
		//print "<tr><td>On value change of field</td><td>When the value is updated to</td><td class='nwrp'>Perform action</td><td>Attach report to mail (mail only)</td><td>Mail template</td><td>Attach to entity/customer</td></tr>";
		print "<tr><td>On value change of field</td><td>";
		print "<strong>" . $lang['customer'] . "</strong>";
		print "</td></tr>";
		$tot_opt = 0;
		print "<tr><td>When the value is updated to</td><td><input type='hidden' name='onchange' value='customer'><select name='to_value' id='JS_to_value'>";
		print "<option value='@SE@'>[something else]</option>";
		$sql = "SELECT id,custname FROM " . $GLOBALS['TBL_PREFIX'] . "customer ORDER BY custname";
			$result= mcq($sql,$db);
			while($options = mysql_fetch_array($result)) {
				if ($options['id'] == $fetched_trigger['to_value']) {
						$a = "selected='selected'";
				} else {
						unset($a);
				}
				print "<option value='" . $options['id'] . "' " . $a . ">" . htme($options['custname']) . "</option>";
				$tot_opt++;
			}
		print "</select>";
		print ReturnDropDownSearchField("JS_to_value");
		print "</td></tr>";
		print "<tr><td class='nwrp'>Perform action</td><td class='nwrp'><select name='action' id='JS_action' onchange='CheckMailTrigger();'>";
		foreach ($actionlist AS $action) {
			print $action;
		}
		print "</select>";
		print ReturnDropDownSearchField("JS_action");
		print "</td></tr>";
		
		print $mailoptionsdiv;

		print "<tr><td>Comment</td><td><textarea cols='50' rows='3' name='trigger_comment'>" . htme($fetched_trigger['comment']) . "</textarea></td></tr>";
		// Execute on form element
		print "<tr><td>Execute only on form</td><td>";
		print "<select name='on_form'>";
		print "<option value='all'>[all forms]</option>";
		$sql = "SELECT templateid, templatename, template_subject FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML_FORM'";
		$result = mcq($sql, $db);
		while ($row = mysql_fetch_array($result)) {
			if ($row['templateid'] == $fetched_trigger['on_form']) {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			$x++;
			print "<option " . $ins . " value='" . $row['templateid'] . "'>" . htme($row['templatename']) . " (" . htme($row['template_subject']) . ")</option>";
		}		print "</select>";
		print "</td></tr>";
		if ($fetched_trigger['tid']) print "<tr><td>Process order</td><td " . PrintToolTipCode("Triggers are processed in this order") . "><input type='text' size='3' name='process_order' value='" . htme($fetched_trigger['processorder']) . "'></td></tr>";

		// End execute on form element
		if ($fetched_trigger['tid']) print "<tr><td>Process order</td><td " . PrintToolTipCode("Triggers are processed in this order") . "><input type='text' size='3' name='process_order' value='" . htme($fetched_trigger['processorder']) . "'></td></tr>";

	print $conditionstext;
		print "<tr><td>Update to value:</td><td><div id='EF_Update' style='display: none'>";
		print "<input type='text' name='EF_to_value' style='background-color: #EBB8B8' size='50' value='" . htme($Update_to_value) . "'>";
print "<br><br>you can use template tags like @CUSTOMER@ and @EID@.";
		print "</div></td></tr>";
		print "<tr><td colspan='2'><input type='hidden' name='add' value=''><input type='hidden' name='req_url' value='" . htme($_REQUEST['req_url']) . "'>";
		print "<br><br><input type='submit' value='Save'>&nbsp;";
		print "<input type='submit' value='Save as new trigger' onclick=\"document.getElementById('JS_fetched').value='';\">&nbsp;";
		if (is_numeric($_REQUEST['fetch'])) {
			print "&nbsp;<input type='button' onclick=\"if (confirm('Sure?')) { document.location='trigger.php?del_trigger=" . htme($_REQUEST['fetch']) . "'; }\" value='Delete this trigger'>";
		}
		print "</td></tr>";
		print "</table>";
		print "</div></form>";
		print "</td></tr>";
		print "<tr><td colspan='10'>&nbsp;</td></tr>";
	}
	// ========================================================================================================================================================
	if ($_REQUEST['add'] == "extrafield" || (strstr($_REQUEST['add'],"EFID") && !strstr($_REQUEST['add'],"DATE_EFID"))) {
		print "<form method='get' action='' id='TriggerAddForm'><div class='showinline'><input type='hidden' name='add' value='extrafield'>";
		if ($_REQUEST['fetch']) {
			print "<input type='hidden' name='fetched' id='JS_fetched' value='" . htme($_REQUEST['fetch']) . "'>";
		}
		print "<table class='nicetable'>";

		//print "<tr><td>On value change of field</td><td>When the value is updated to</td><td class='nwrp'>Perform action</td><td>Attach report to mail (mail only)</td><td>Mail template</td><td>Attach to entity/customer</td></tr>";

		if (!$_REQUEST['ExtraField'] && !$_REQUEST['fetch']) {
			print "<tr><td>On value change of field</td><td>";
			print "<select name='ExtraField' id='JS_ExtraField'>";
			$efl = GetExtraFields();
			foreach ($efl AS $ef) {
				if ($ef['fieldtype']=="Button") {
					//	print "<option value='" . $ef['id'] . "'>[BUTTON] " . $ef['name'] . "</option>";
				} else {
					print "<option value='" . $ef['id'] . "'>" . htme($ef['name']) . "</option>";
				}
			}
			$ft = GetFlexTableDefinitions(false, false, true);
			foreach ($ft AS $flextable) {
				$efl = GetExtraFlexTableFields($flextable['recordid']);
				foreach ($efl AS $ef) {
					if ($ef['fieldtype']=="Button") {
						//	print "<option value='" . $ef['id'] . "'>[BUTTON] " . $ef['name'] . "</option>";
					} else {
						print "<option value='" . $ef['id'] . "'>" . htme($ef['name']) . "</option>";
					}
				}
			}
			print "</select>";
			print ReturnDropDownSearchField("JS_ExtraField");
			print "</td></tr>";
			print "<tr><td>When the value is set to</td><td>[select field first and click 'go']</td></tr>";
			print "<tr><td class='nwrp'>Perform action</td><td>[select field first and click 'go']</td></tr>";
			print "<tr><td>Comment</td><td>[select field first and click 'go']</td></tr>";
		} else {
			print "<tr><td>On value change of field</td><td><select disabled='disabled' name='onchange'>";
			if ($_REQUEST['fetch']) {
				$ctrigger = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "triggers WHERE tid='" . mres($_REQUEST['fetch']) . "'");
				$_REQUEST['ExtraField'] = str_replace("EFID","",$ctrigger['onchange']);
			}
			$list = GetExtraFields();
			unset($ok);
			foreach ($list AS $field) {
				if ($field['id'] == $_REQUEST['ExtraField']) {
					$ok = 1;
					$tf = $field;
				}
			}
			if (!$ok) {
				$ft = GetFlexTableDefinitions(false, false, true);
				foreach ($ft AS $flextable) {
					$efl = GetExtraFlexTableFields($flextable['recordid']);
					foreach ($efl AS $field) {
						if ($field['id'] == $_REQUEST['ExtraField']) {
							$tf = $field;
						}
					}
				}
			}
			print "<option>" . $_REQUEST['ExtraField'] . ":" . $tf['name'] . "</option></select></td></tr>";
			print "<tr><td>When the value is set to</td><td><input type='hidden' name='onchange' value='EFID" . htme($_REQUEST['ExtraField']) . "'>";
			print "<select name='to_value' id='JS_to_value'>";
			print "<option value='@SE@'>[something else]</option>";

			if ($tf['fieldtype'] == "drop-down") {
				$tmp = unserialize($tf['options']);
				foreach ($tmp AS $option) {
					if ($ctrigger['to_value'] == $option) {
						$ins = "selected='selected'";
					} else {
						unset($ins);
					}
					print "<option " . $ins . " value='" . htme($option) . "'>" . htme($option) . "</option>";
					$tot_opt++;
				}
			} elseif ($tf['fieldtype'] == "checkbox") {
				$tmp = array($field['options'], $tf['defaultval']);
				foreach ($tmp AS $option) {
					if ($ctrigger['to_value'] == $option) {
						$ins = "selected='selected'";
					} else {
						unset($ins);
					}
					print "<option " . $ins . " value='" . htme($option) ."'>" . htme($option) . "</option>";
					$tot_opt++;
				}

			} elseif ($tf['fieldtype'] == "User-list of all CRM-CTT users") {
				$res = mcq("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE name NOT LIKE '%deleted_user%' ORDER BY FULLNAME",$db);
				while ($row = mysql_fetch_array($res)) {
					if ($ctrigger['to_value'] == $row['id']) {
						$ins = "selected='selected'";
					} else {
						unset($ins);
					}
					print "<option " . $ins . " value='" . $row['id'] . "'>" . htme(GetUserName($row['id'])) . "</option>";
				}
			} elseif ($tf['fieldtype'] == "User-list of limited CRM-CTT users") {
				$res = mcq("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE name NOT LIKE '%deleted_user%' AND FULLNAME LIKE '%@@@%' ORDER BY FULLNAME",$db);
				while ($row = mysql_fetch_array($res)) {
					if ($ctrigger['to_value'] == $row['id']) {
						$ins = "selected='selected'";
					} else {
						unset($ins);
					}
					print "<option " . $ins . " value='" . $row['id'] . "'>" . htme(GetUserName($row['id'])) . "</option>";
				}
			} elseif ($tf['fieldtype'] == "User-list of administrative CRM-CTT users") {
				$res = mcq("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE name NOT LIKE '%deleted_user%' AND administrator='yes' ORDER BY FULLNAME",$db);
				while ($row = mysql_fetch_array($res)) {
					if ($ctrigger['to_value'] == $row['id']) {
						$ins = "selected='selected'";
					} else {
						unset($ins);
					}
					print "<option " . $ins . " value='" . $row['id'] . "'>" . htme(GetUserName($row['id'])) . "</option>";
				}
			}
			print "</select>";
			print "</td></tr><tr><td class='nwrp'>Perform action</td><td class='nwrp'><select name='action' id='JS_action' onchange='CheckMailTrigger();'>";
			foreach ($actionlist AS $action) {
				/*
				$action_arr = split("value='",$action);
				$action_arr2 = split("'>",$action_arr[1]);
				$val = $action_arr2[0];
				if ($val == $ctrigger['action']) {
					$action = str_replace("<option","<option SELECTED",$action);
				}
				*/
				print $action;
			}
			print "</select>";
			print ReturnDropDownSearchField("JS_action");
			print "</td></tr>";
			print $mailoptionsdiv;

		print "<tr><td>Comment</td><td><textarea cols='50' rows='3' name='trigger_comment'>" . htme($fetched_trigger['comment']) . "</textarea></td></tr>";
		// Execute on form element
		print "<tr><td>Execute only on form</td><td>";
		print "<select name='on_form'>";
		print "<option value='all'>[all forms]</option>";
		$sql = "SELECT templateid, templatename, template_subject FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML_FORM'";
		$result = mcq($sql, $db);
		while ($row = mysql_fetch_array($result)) {
			if ($row['templateid'] == $fetched_trigger['on_form']) {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			$x++;
			print "<option " . $ins . " value='" . $row['templateid'] . "'>" . htme($row['templatename']) . " (" . htme($row['template_subject']) . ")</option>";
		}
		print "</select>";
		print "</td></tr>";
		// End execute on form element
		if ($fetched_trigger['tid']) print "<tr><td>Process order</td><td " . PrintToolTipCode("Triggers are processed in this order") . "><input type='text' size='3' name='process_order' value='" . htme($fetched_trigger['processorder']) . "'></td></tr>";

		print $conditionstext;

		print "<tr><td>Update to value:</td><td><div id='EF_Update' style='display: none'>";
		print "<input type='text' name='EF_to_value' style='background-color: #EBB8B8' size='50' value='" . htme($Update_to_value) . "'>";
		print "<br><br>you can use template tags like @CUSTOMER@ and @EID@.";
		print "</div></td></tr>";
		}

		print "<tr><td colspan='2'>";
		if ($_REQUEST['ExtraField']) {
			print "<input type='hidden' name='add' value=''><input type='hidden' name='req_url' value='" . htme($_REQUEST['req_url']) . "'>";
		}

		if (is_numeric($_REQUEST['fetch'])) {
			print "<input type='submit' value='Save'>";
			print "<input type='submit' value='Save as new trigger' onclick=\"document.getElementById('JS_fetched').value='';\">&nbsp;";
			print "&nbsp;<input type='button' onclick=\"if (confirm('Sure?')) { document.location='trigger.php?del_trigger=" . htme($_REQUEST['fetch']) . "'; }\" value='Delete this trigger'>";
		} else {
			print "<input type='submit' value='" . $lang['go'] . "'>";
		}
		print "</td></tr>";
		print "</table>";
		print "</div></form>";
		print "</td></tr>";
		print "<tr><td colspan='10'>&nbsp;</td></tr>";
	}
	// ========================================================================================================================================================
	if ($_REQUEST['add'] == "miscellaneous" || $_REQUEST['add'] == "duedate_expired" || $_REQUEST['add'] == "duedate_infuture" || $_REQUEST['add'] == "buttons"|| $_REQUEST['add'] == "entity_add" || $_REQUEST['add'] == "entity_change" || $_REQUEST['add'] == "entity_change_by_not_owner" || $_REQUEST['add'] == "entity_change_by_not_assignee" || $_REQUEST['add'] == "entity_change_by_not_owner_nor_assignee" || $_REQUEST['add'] == "customer_change" || $_REQUEST['add'] == "customer_add" || $_REQUEST['add'] == "entity_email_update" || $_REQUEST['add'] == "entity_change_select_assignee" || $_REQUEST['add'] == "entity_change_select_owner" || $_REQUEST['add'] == "limited_add" || $_REQUEST['add'] == "duedate_change" || $_REQUEST['add'] == "limited_update" || $_REQUEST['add'] == "duedate_reached" || stristr($_REQUEST['add'], "ButtonPress") || stristr($_REQUEST['add'], "EntityAge") || stristr($_REQUEST['add'], "FlexTable") || stristr($_REQUEST['add'], "DuedateReached_minus_")|| stristr($_REQUEST['add'], "every") || stristr($_REQUEST['add'], "First day") || stristr($_REQUEST['add'], "DATE_EFID") || stristr($_REQUEST['add'], "EntityLastUpdate")) {
		print "<form method='post' action='' id='TriggerAddForm'><div class='showinline'>";
		if ($_REQUEST['fetch']) {
			print "<input type='hidden' name='fetched' id='JS_fetched' value='" . htme($_REQUEST['fetch']) . "'>";
		}
		print "<table class='nicetable'>";
		print "<tr><td>On event occurring</td><td class='nwrp'>";
		print "<select name='onchange' id='JS_onchange'>";
		if ($_REQUEST['add'] != "buttons") {
			if ($fetched_trigger['onchange'] == "limited_add") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='limited_add'>Limited user adds an entity</option>";
			if ($fetched_trigger['onchange'] == "limited_update") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='limited_update'>Limited user updates an entity</option>";

			if ($fetched_trigger['onchange'] == "startdate_reached") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='startdate_reached'>Entity reaches start-date</option>";

			if ($fetched_trigger['onchange'] == "duedate_reached") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='duedate_reached'>Entity reaches due-date</option>";


			
			if ($fetched_trigger['onchange'] == "duedate_expired") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='duedate_expired'>Due-date is in the past (expired)</option>";
			if ($fetched_trigger['onchange'] == "duedate_infuture") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='duedate_infuture'>Due-date is in the future (not expired)</option>";

			foreach($GLOBALS['TriggerDays'] AS $day) {

				if ($fetched_trigger['onchange'] == "DuedateReached_minus_" . $day . "_days") {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				if ($day > 0) {
					$text = $day . " days before duedate";
				} else {
					$text = str_replace("-", "", $day) . " days after duedate";
				}
				print "<option " . $ins . " value='DuedateReached_minus_" . $day . "_days'>" . $text . "</option>";
			}

			$list = GetExtraFields();
			foreach ($list AS $field) {
				if ($field['fieldtype'] == "date" || $field['fieldtype'] == "date/time" || GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Date") {

					if ($fetched_trigger['onchange'] == "DATE_EFID" . $field['id'] . "_expired") {
						$ins = "selected='selected'";
					} else {
						unset($ins);
					}
					print "<option " . $ins . " value='DATE_EFID" . $field['id'] . "_expired'>Field " . htme($field['name']) . " has date in the past (expired)</option>";

					if ($fetched_trigger['onchange'] == "DATE_EFID" . $field['id'] . "_infuture") {
						$ins = "selected='selected'";
					} else {
						unset($ins);
					}
					print "<option " . $ins . " value='DATE_EFID" . $field['id'] . "_infuture'>Field " . htme($field['name']) . " has date in the future</option>";

					foreach($GLOBALS['TriggerDays'] AS $day) {
						if ($fetched_trigger['onchange'] == "DATE_EFID" . $field['id'] . "_" . $day . "_days") {
							$ins = "selected='selected'";
						} else {
							unset($ins);
						}
						if ($day > 0) {
							$text = $day . " days before date in field " . htme($field['name']) . " is reached";
						} else {
							$text = str_replace("-", "", $day) . " days after date in field " . htme($field['name']) . " is reached";
						}
						print "<option " . $ins . " value='DATE_EFID" . $field['id'] . "_" . $day . "_days'>" . $text . "</option>";
					}
				}
			}
			$list = GetExtraCustomerFields();
			foreach ($list AS $field) {
				if ($field['fieldtype'] == "date" || $field['fieldtype'] == "date/time" || GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Date") {

					if ($fetched_trigger['onchange'] == "DATE_EFID" . $field['id'] . "_expired") {
						$ins = "selected='selected'";
					} else {
						unset($ins);
					}
					print "<option " . $ins . " value='DATE_EFID" . $field['id'] . "_expired'>Field " . htme($field['name']) . " has date in the past (expired)</option>";

					if ($fetched_trigger['onchange'] == "DATE_EFID" . $field['id'] . "_infuture") {
						$ins = "selected='selected'";
					} else {
						unset($ins);
					}
					print "<option " . $ins . " value='DATE_EFID" . $field['id'] . "_infuture'>Field " . htme($field['name']) . " has date in the future</option>";

					foreach($GLOBALS['TriggerDays'] AS $day) {
						if ($fetched_trigger['onchange'] == "DATE_EFID" . $field['id'] . "_" . $day . "_days") {
							$ins = "selected='selected'";
						} else {
							unset($ins);
						}
						if ($day > 0) {
							$text = $day . " days before date in field " . htme($field['name']) . " is reached";
						} else {
							$text = str_replace("-", "", $day) . " days after date in field " . htme($field['name']) . " is reached";
						}
						print "<option " . $ins . " value='DATE_EFID" . $field['id'] . "_" . $day . "_days'>" . $text . "</option>";
					}
				}
			}

			$fts = GetFlexTableDefinitions(false, false, true);

			foreach ($fts AS $flextable) {
				$list = GetExtraFlexTableFields($flextable['recordid'], false);
				foreach ($list AS $field) {
					
					$COT = GetAttribute("extrafield", "ComputationOutputType", $field['id']);

					if ($field['fieldtype'] == "date"  || $field['fieldtype'] == "date/time" || $COT == "Date") {
						if ($fetched_trigger['onchange'] == "DATE_EFID" . $field['id'] . "_expired") {
							$ins = "selected='selected'";
						} else {
							unset($ins);
						}
						print "<option " . $ins . " value='DATE_EFID" . $field['id'] . "_expired'>Field " . htme($field['name']) . " has date in the past (expired)</option>";

						if ($fetched_trigger['onchange'] == "DATE_EFID" . $field['id'] . "_infuture") {
							$ins = "selected='selected'";
						} else {
							unset($ins);
						}
						print "<option " . $ins . " value='DATE_EFID" . $field['id'] . "_infuture'>Field " . htme($field['name']) . " has date in the future</option>";

						foreach($GLOBALS['TriggerDays'] AS $day) {
							if ($fetched_trigger['onchange'] == "DATE_EFID" . $field['id'] . "_" . $day . "_days") {
								$ins = "selected='selected'";
							} else {
								unset($ins);
							}
							if ($day > 0) {
								$text = $day . " days before date in field " . htme($field['name']) . " is reached";
							} else {
								$text = str_replace("-", "", $day) . " days after date in field " . htme($field['name']) . " is reached";
							}
							print "<option " . $ins . " value='DATE_EFID" . $field['id'] . "_" . $day . "_days'>" . $text . "</option>";
						}
					} else {
						//print "<option " . $ins . " value='DATE_EFID" . $field['id'] . "_" . $day . "_days'>Niet meegenomen: " . $field['id'] .  " COT |$COT|</option>";
					}
				}

			}

			if ($fetched_trigger['onchange'] == "entity_add") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}

			print "<option " . $ins . " value='entity_add'>Entity add (new " . $lang['entity'] . ")</option>";
			if ($fetched_trigger['onchange'] == "entity_change") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='entity_change'>Entity change</option>";
			if ($fetched_trigger['onchange'] == "entity_change_by_not_owner") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='entity_change_by_not_owner'>Entity change by somebody else than the owner</option>";
			if ($fetched_trigger['onchange'] == "entity_change_by_not_assignee") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='entity_change_by_not_assignee'>Entity change by somebody else than the assignee</option>";

			if ($fetched_trigger['onchange'] == "entity_change_by_not_owner_nor_assignee") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='entity_change_by_not_owner_nor_assignee'>Entity change by somebody else than the owner or assignee</option>";


			if ($fetched_trigger['onchange'] == "entity_email_insert") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='entity_email_insert'>Entity insert by e-mail</option>";

			if ($fetched_trigger['onchange'] == "entity_email_update") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='entity_email_update'>Entity update by e-mail</option>";
			if ($fetched_trigger['onchange'] == "entity_change_select_owner") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='entity_change_select_owner'>Entity change for owner (email update selectbox, action ignored)</option>";
			if ($fetched_trigger['onchange'] == "entity_change_select_assignee") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='entity_change_select_assignee'>Entity change for assignee (email update selectbox, action ignored)</option>";
			if ($fetched_trigger['onchange'] == "customer_add") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option value='customer_add' " . $ins . ">Customer add (new " . $lang['customer'] . ") (mail actions only)</option>";
			if ($fetched_trigger['onchange'] == "customer_change") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option value='customer_change' " . $ins . ">Customer change (mail actions only)</option>";

			if ($fetched_trigger['onchange'] == "duedate_change") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='duedate_change'>Duedate change</option>";

			print "<option value=''>--- user administration ---</option>";
			$la = GetUserProfiles();
			foreach ($la AS $profile) {
				if ($profile[0] != 0) {
					if ($fetched_trigger['onchange'] == "user_add_p" . $profile[0]) {
						$ins = "selected='selected'";
					} else {
						unset($ins);
					}

					print "<option $ins value='user_add_p" . $profile[0] . "'>A user is added to profile " . $profile[1] . " by a non-admin user</option>";
				}
			}

			



			print "<option value=''>--- auto-escalation ---</option>";

			$minutes = array(1,2,3,4,5,6,7,8,9,10,15,20,25,30,35,40,45,50,55);
			foreach($minutes AS $min) {
				$sec = $min * 60;
				if ($fetched_trigger['onchange'] == "EntityAge" . $sec) {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				print "<option " . $ins . " value='EntityAge" . $sec . "'>Entity is " . $min . " minutes old</option>";
			}

			$hours = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23);
			foreach($hours AS $hour) {
				$sec = $hour * 3600;
				if ($fetched_trigger['onchange'] == "EntityAge" . $sec) {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				print "<option " . $ins . " value='EntityAge" . $sec . "'>Entity is " . $hour . " hours old</option>";
			}
			$days = array(1,2,3,4,5,6,7,8,9,10,14,21);
			foreach($days AS $day) {
				$sec = $day * 24 * 3600;
				if ($fetched_trigger['onchange'] == "EntityAge" . $sec) {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				print "<option " . $ins . " value='EntityAge" . $sec . "'>Entity is " . $day . " days old</option>";
			}

/*
			$days = array(1,2,3,4,5,6,7,8,9,10,14,21);
			foreach($days AS $day) {
				$sec = $day * 24 * 3600;
				if ($fetched_trigger['onchange'] == "EntityLastUpdate" . $sec) {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				print "<option " . $ins . " value='EntityLastUpdate" . $sec . "'>Entity was last updated " . $day . " days ago</option>";
			}
*/
			print "<option value=''>--- flextable actions ---</option>";
			$flextables = GetFlexTableDefinitions(false, false, true);
			foreach ($flextables AS $ft) {
				if ($fetched_trigger['onchange'] == "FlexTable" . $ft['recordid'] . "-Add") {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				print "<option " . $ins . " value='FlexTable" . $ft['recordid'] . "-Add'>FlexTable " . htme($ft['tablename']) . " record add (no entity-update actions!)</option>";
				if ($fetched_trigger['onchange'] == "FlexTable" . $ft['recordid'] . "-Change") {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				print "<option " . $ins . " value='FlexTable" . $ft['recordid'] . "-Change'>FlexTable " . htme($ft['tablename']) . " record change (no entity-update actions!)</option>";
			}
		} // end if !buttons
		if ($_REQUEST['add'] == "buttons") {

			$arr = GetAllButtons();
			foreach ($arr AS $button) {
				if ($fetched_trigger['onchange'] == "ButtonPress" . $button['id']) {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				print "<option " . $ins . " value='ButtonPress" . $button['id'] . "'>Button [" . htme($button['name']) . "] is pressed</option>";
			}
		}
		if ($_REQUEST['add'] != "buttons") {
			print "<option value=''>--- run on specific moment ---</option>";

			if ($fetched_trigger['onchange'] == "Every minute") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='Every minute'>Run every minute</option>";

			if ($fetched_trigger['onchange'] == "Every day") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='Every day'>Run every day</option>";

			if ($fetched_trigger['onchange'] == "Every working day") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='Every working day'>Run every working day (mon - fri)</option>";

			if ($fetched_trigger['onchange'] == "Every Monday") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='Every Monday'>Run every Monday</option>";
			if ($fetched_trigger['onchange'] == "Every Tuesday") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='Every Tuesday'>Run every Tuesday</option>";
			if ($fetched_trigger['onchange'] == "Every Wednesday") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='Every Wednesday'>Run every Wednesday</option>";
			if ($fetched_trigger['onchange'] == "Every Thursday") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='Every Thursday'>Run every Thursday</option>";
			if ($fetched_trigger['onchange'] == "Every Friday") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='Every Friday'>Run every Friday</option>";
			if ($fetched_trigger['onchange'] == "Every Saturday") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='Every Saturday'>Run every Saturday</option>";
			if ($fetched_trigger['onchange'] == "Every Sunday") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='Every Sunday'>Run every Sunday</option>";
			if ($fetched_trigger['onchange'] == "First day of the month") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='First day of the month'>Run the first day of the month</option>";
			
			for ($x=1;$x<13;$x++) {
				if ($fetched_trigger['onchange'] == "First day of month " . $x) {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				print "<option " . $ins . " value='First day of month " . $x . "'>Run the first day of month " . $x . " (" . $lang['month' . $x] . ")</option>";
			}

			if ($fetched_trigger['onchange'] == "First day of the year") {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='First day of the year'>Run the first day of the year</option>";

			foreach($GLOBALS['TriggerDays'] AS $day) {

				if ($fetched_trigger['onchange'] == "LastUpdate_" . $day . "_days_ago") {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				if ($day < 0) {
					$day = $day * -1;
					$text = "Last update was " . $day . " days ago";
					print "<option " . $ins . " value='LastUpdate_" . $day . "_days_ago'>" . $text . "</option>";
				}
				
			}

		} // end if !buttons


		print "</select>";
		print ReturnDropDownSearchField("JS_onchange");
		print "</td></tr>";
		$tot_opt = 0;
		print "<tr><td class='nwrp'>Perform action</td><td class='nwrp'><input type='hidden' name='to_value' value='miscellaneous trigger'><select name='action' id='JS_action' onchange='CheckMailTrigger();'>";
		foreach ($actionlist AS $action) {
			print $action;
		}
		print "</select>";
		print ReturnDropDownSearchField("JS_action");
		print "</td></tr>";
		
		print $mailoptionsdiv;

		print "<tr><td>Comment</td><td><textarea cols='50' rows='3' name='trigger_comment'>" . htme($fetched_trigger['comment']) . "</textarea></td></tr>";
		// Execute on form element
		print "<tr><td>Execute only on form</td><td>";
		print "<select name='on_form'>";
		print "<option value='all'>[all forms]</option>";
		$sql = "SELECT templateid, templatename, template_subject FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML_FORM'";
		$result = mcq($sql, $db);
		while ($row = mysql_fetch_array($result)) {
			if ($row['templateid'] == $fetched_trigger['on_form']) {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			$x++;
			print "<option " . $ins . " value='" . $row['templateid'] . "'>" . htme($row['templatename']) . " (" . htme($row['template_subject']) . ")</option>";
		}		print "</select>";
		print "</td></tr>";
		// End execute on form element
		if ($fetched_trigger['tid']) print "<tr><td>Process order</td><td " . PrintToolTipCode("Triggers are processed in this order") . "><input type='text' size='3' name='process_order' value='" . htme($fetched_trigger['processorder']) . "'></td></tr>";

		print $conditionstext;
		print "<tr><td>Update to value:</td><td><div id='EF_Update' style='display: none'>";
		print "<input type='text' name='EF_to_value' style='background-color: #EBB8B8' size='50' value='" . htme($Update_to_value) . "'>";
		print "<br><br>you can use template tags like @CUSTOMER@ and @EID@.";
		print "</div></td></tr>";
		print "<tr><td colspan='2'><input type='hidden' name='add' value=''><input type='hidden' name='req_url' value='" . htme($_REQUEST['req_url']) . "'>";
		print "<br><br><input type='submit' value='Save'>&nbsp;";
		print "<input type='submit' value='Save as new trigger' onclick=\"document.getElementById('JS_fetched').value='';\">&nbsp;";
		if (is_numeric($_REQUEST['fetch'])) {
			print "&nbsp;<input type='button' onclick=\"if (confirm('Sure?')) { document.location='trigger.php?del_trigger=" . htme($_REQUEST['fetch']) . "'; }\" value='Delete this trigger'>";
		}
		print "</td></tr>";
		print "</table>";
		print "</div></form>";
		print "</td></tr>";
		print "<tr><td colspan='10'>&nbsp;</td></tr>";
	}
	// ========================================================================================================================================================
	if ($_REQUEST['add'] == "admin" || $_REQUEST['add'] == "log_error" || $_REQUEST['add'] == "log_warning" || $_REQUEST['add'] == "admin_request" || $_REQUEST['add'] == "user_login") { 
		print "<form method='post' action='' id='TriggerAddForm'><div class='showinline'>";
		if ($_REQUEST['fetch']) {
			print "<input type='hidden' name='fetched' id='JS_fetched' value='" . htme($_REQUEST['fetch']) . "'>";
		}
		print "<table class='nicetable'>";
	//	print "<tr><td>On event occurring</td></tr><tr><td>When the value is updated to</td><td class='nwrp'>Perform action</td><td>Mail template</td><td>Mail template</td><td>&nbsp;</td></tr>";
		print "<tr><td>On event occurring</td><td>";
		print "<input type='hidden' name='onchange' value='customer'>";
		print "<select name='onchange'>";
		if ($_REQUEST['add'] == "log_warning") {
			$ins = "selected='selected'";
		} else {
			unset($ins);
		}
		print "<option " . $ins . " value='log_warning'>Warning issued in log</option>";
		if ($_REQUEST['add'] == "log_error") {
			$ins = "selected='selected'";
		} else {
			unset($ins);
		}
		print "<option " . $ins . " value='log_error'>Error issued in log</option>";
		if ($_REQUEST['add'] == "admin_request") {
			$ins = "selected='selected'";
		} else {
			unset($ins);
		}
		print "<option " . $ins . " value='admin_request'>Administrative action request</option>";

		if ($_REQUEST['add'] == "user_login") {
			$ins = "selected='selected'";
		} else {
			unset($ins);
		}
		print "<option " . $ins . " value='user_login'>A user logs in</option>";

		print "</select><input type='hidden' name='to_value' value='Administrative trigger'></td></tr>";
		print "<tr><td class='nwrp'>Perform action</td><td class='nwrp'><select name='action' id='JS_action' onchange='CheckMailTrigger();'>";
		foreach ($actionlist AS $action) {
			if (!strstr($action,"'mail owner'") && !strstr($action,"'mail assignee'") && !strstr($action,"'mail customer'")) {
				print $action;
			}
		}
		print "</select>";
		print ReturnDropDownSearchField("JS_action");
		print "</td></tr>";
		print "<tr><td>Mail template</td><td><select disabled='disabled' name='template_fileid'>";
		print "<option value='0'>Notification (no template)</option>";
		print "</select></td></tr>";
		print "<tr><td>Comment</td><td><textarea cols='50' rows='3' name='trigger_comment'>" . htme($fetched_trigger['comment']) . "</textarea></td></tr>";
		print "<tr><td colspan='2'><input type='hidden' name='add' value=''><input type='hidden' name='req_url' value='" . htme($_REQUEST['req_url']) . "'>";
		print "<br><br><input type='submit' value='Save'>&nbsp;";
		print "<input type='submit' value='Save as new trigger' onclick=\"document.getElementById('JS_fetched').value='';\">&nbsp;";
		if (is_numeric($_REQUEST['fetch'])) {
			print "&nbsp;<input type='button' onclick=\"if (confirm('Sure?')) { document.location='trigger.php?del_trigger=" . htme($_REQUEST['fetch']) . "'; }\" value='Delete this trigger'>";
		}
		print "</td></tr>";
		print "</table>";
		print "</div></form>";
		print "</td></tr>";
		print "<tr><td colspan='10'>&nbsp;</td></tr>";
	}
	// ========================================================================================================================================================
	//print "</table>";

	if (!$_REQUEST['add']) {

		$tp = "<table class='nicetableclear'><tr><td>";
		if (!$_REQUEST['filter']) {
			$tp .= "<p><a class='arrow' href='trigger.php?toggle_trigger_off'>Disable all triggers</a> <a class='arrow' href='trigger.php?toggle_mail_trigger_off'>Disable all e-mailing triggers</a> <a class='arrow' href='trigger.php?toggle_trigger_on'>Enable all triggers</a></p>";
		}


		$tp .= "<table class='sortable' width='100%'>";
		$tp .= "<tr><td>Order</td><td>Id</td><td>Fire count</td><td>On event or value change of field</td><td>when the value is updated to</td><td>perform action</td><td>Mail template</td><td>Conditions</td><td>Form</td><td>Comment</td><td>Delete</td><td>Enabled</td></tr>";
		switch ($_REQUEST['filter']) {
			case "extrafield":
					$qins = " AND onchange LIKE 'EFID%'";
			break;
			case "status":
				$qins = " AND onchange = 'status'";
			break;
			case "priority":
				$qins = " AND onchange = 'priority'";
			break;
			case "ownerassignee":
				$qins = " AND (onchange = 'owner' OR onchange='assignee')";
			break;
			case "buttons":
				$qins = " AND onchange LIKE '%ButtonPress%'";
			break;
			case "customer":
				$qins = " AND onchange ='customer'";
			break;

			case "admin":
				$qins = " AND (onchange = 'log_warning' OR onchange='log_error' OR onchange='admin_request')";
				$qins = " AND to_value='Administrative trigger'";
			break;
			case "miscellaneous":
				$qins = " AND to_value='miscellaneous trigger' AND onchange NOT LIKE 'ButtonPress%'";
			break;
			default:
				$qins = "";
			break;
		}


			$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "triggers WHERE 1=1 " . $qins . " ORDER BY onchange, processorder, tid";
			$result = mcq($sql,$db);
			while ($row = mysql_fetch_array($result)) {

				$count = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "journal WHERE type='trigger' AND eid='" . $row['tid'] . "'");
				if ($row['enabled'] == "yes") {
					$oo = "style='cursor: pointer'";
				} else {
					$oo = "style='cursor: pointer; background-color: #E8E8E8;'";
				}
				if (strstr($row['onchange'],"DATE_EFID")) {
					$tmp = explode("_",$row['onchange']);
					$num = str_replace("EFID", "", $tmp[1]);
					$tp .= "<tr " . $oo . " onclick=\"document.location='trigger.php?add=" . $row['onchange'] . "&amp;fetch=" . $row['tid'] . "';\"><td>" . htme($row['processorder']) . "</td><td>" . $row['tid'] . "</td><td>" . $count[0] . "</td><td>";
					if ($tmp[2] == "expired") {
						$tp .= "Must be in the past: ";
					} elseif ($tmp[2] == "infuture") { 
						$tp .= "Must be in the future: ";
					} elseif ($tmp[2] < 0) {
						$tp .= "" . str_replace("-", "" , $tmp[2]) . " days after "; 
					} elseif ($tmp[2] > 0) {
						$tp .= "" . $tmp[2] . " days before ";
					} else {
						$tp .= "On date ";
					}
					
					$tp .= "\"" . GetExtraFieldName($num) . "\"</td>";
				} elseif (strstr($row['onchange'],"EFID")) {
					$num = str_replace("EFID","",$row['onchange']);
					$tp .= "<tr " . $oo . " onclick=\"document.location='trigger.php?add=" . $row['onchange'] . "&amp;fetch=" . $row['tid'] . "';\"><td>" . htme($row['processorder']) . "</td><td>" . $row['tid'] . "</td><td>" . $count[0] . "</td><td>" . GetExtraFieldName($num) . "</td>";
				} elseif (strstr($row['onchange'],"ButtonPress")) {
					$num = str_replace("ButtonPress","",$row['onchange']);
					$tp .= "<tr " . $oo . " onclick=\"document.location='trigger.php?add=" . $row['onchange'] . "&amp;fetch=" . $row['tid'] . "';\"><td>" . htme($row['processorder']) . "</td><td>" . $row['tid'] . "</td><td>" . $count[0] . "</td><td class='nwrp'>Button [" . GetExtraFieldName($num) . "]</td>";
				} else {
					$tp .= "<tr " . $oo . " onclick=\"document.location='trigger.php?add=" . $row['onchange'] . "&amp;fetch=" . $row['tid'] . "';\" ><td>" . htme($row['processorder']) . "</td><td>" . $row['tid'] . "</td><td>" . $count[0] . "</td><td>" . $row['onchange'] . "</td>";
				}

				if (strstr($row['onchange'], "DATE_EFID")) {
					$tp .= "<td>Date trigger</td>";
				} elseif ($row['to_value']=="@SE@") {
					$tp .= "<td>[something else]</td>";
				} elseif ($row['onchange']=="owner" || $row['onchange']=="assignee") {
					if (substr($row['to_value'],0,2) == "G:") {
						$tp .= "<td>Someone in group " . htme(GetGroupName(str_replace("G:", "", $row['to_value']))) . "</td>";
					} else {
						$tp .= "<td>" . htme(GetUserName($row['to_value'])) . "</td>";
					}
				} elseif ($row['onchange']=="customer") {
					$tp .= "<td>" . htme(GetCustomerName($row['to_value']));
				} elseif ($row['onchange']=="status") {
					$tp .= "<td style=\"background-color: " . htme(GetStatusColor($row['to_value'])) . ";\">" . htme($row['to_value']) . "</td>";
				} elseif ($row['onchange']=="priority") {
					$tp .= "<td style=\"background-color: " . htme(GetPriorityColor($row['to_value'])) . ";\">" . htme($row['to_value']) . "</td>";
				} elseif (strstr($field['fieldtype'], "User-list of")) {
					$tp .= "<td>" . htme(GetUserName($row['to_value'])) . "</td>";
				} elseif (strstr($row['onchange'], "EntityAge")) {
					$age = str_replace("EntityAge", "", $row['onchange']);
					if ($age > 3600) {
						$hours = "(" . $age / 3600 . " hours)";
					} else {
						unset($hours);
					}
					$tp .= "<td>" . $age / 60 . " minutes old " . $hours . "</td>";

				} else {
					$tp .= "<td>" . $row['to_value'] . "</td>";
				}

				$tp .= "<td>";
				 if (strstr($row['action'],"mail user @")) {
					$user_ar = split("@",$row['action']);
					$user = $user_ar[1];
					if (strstr($user, "GEFID")) {
						$x = GetExtraFieldName(trim(str_replace("GEFID", "",$user)));
						$tp .= "mail to address in group EF " . $x;
					} elseif (strstr($user, "UEFID")) {
						$x = GetExtraFieldName(trim(str_replace("UEFID", "",$user)));
						$tp .= "mail to address in user profile EF " . $x;
					} elseif (strstr($user, "MEFID")) {
						$x = GetExtraFieldName(trim(str_replace("MEFID", "",$user)));
						$tp .= "mail user in EF " . $x;
					} else {
						$tp .= "mail user " . htme(GetUserName($user));
					}
				} elseif (strstr($row['action'],"mail cust @")) {
					$cust_ar = split("@",$row['action']);
					$cust = $cust_ar[1];
					$tp .= "mail " . strtolower($lang['customer']) . " " . htme(GetCustomerName($cust));
				} elseif (strstr($row['action'],"Update EFID")) {
					$subs = explode(" to value ", $row['action']);
					$ar = explode(" ", $subs[0]);

					$tp .= "Update " . GetExtraFieldName($ar[2]);
				} else {
					$tp .= "" . $row['action'] . "";
				}
				$tp .= "</td>";

				$tp .= "<td>";
				if (stristr($row['action'],"mail")) {
					if ($row['template_fileid']) {
						$tp .= GetTemplateName($row['template_fileid']);
					} else {
						if ($row['action']=="mail owner" || $row['action']=="mail assignee") {
							$tp .= "BODY_ENTITY_EDIT";
						} elseif ($row['to_value'] == "Administrative trigger") {
							$tp .= "Notification (no template)";
						} elseif ($row['action']=="mail admin") {
							$tp .= "BODY_TEMPLATE_CUSTOMER";
						} elseif ($row['action']=="status") {
							$tp .= "BODY_ENTITY_EDIT";
						} elseif (strstr($row['action'],"mail user @")) {
							$tp .= "BODY_ENTITY_EDIT";
						} elseif (strstr($row['action'],"mail cust @") || ($row['action']=="mail customer")) {
							$tp .= "BODY_TEMPLATE_CUSTOMER";
						} else {
							$tp .= "BODY_ENTITY_EDIT";
						}
					}
				} else {
					$tp .= "n/a";
				}
				$tp .= "</td>";
				if (is_numeric($row['on_form'])) {
					$form = "<td class='nwrp' style='background-color: #FFFFCC;'>" . htme(GetTemplateName($row['on_form'])) . "</td>";
				} else {
					$form = "<td>[all forms]</td>";
				}
				$tmp = GetTriggerConditions($row['tid']);
				if (is_array($tmp[0])) {

					//print_r($tmp);
					$html = "<table class='crm'>";
					$html .= "<tr><td>ID</td><td>Field</td><td class='nwrp'></td><td>value</td></tr>";
					foreach ($tmp AS $con) {
					if ($con['trueorfalse'] == "true") {
						$ins = "must be";
					} else {
						$ins = "must <strong>not</strong> be";
					}
					if ($con['field'] == "priority") {
						$con['value'] = htme(GetPriorityName($con['value']));
					}
					if ($con['field'] == "status") {
						$con['value'] = htme(GetStatusName($con['value']));
					}
					if (substr($con['field'],0 ,4) == "EFID") {
						$con['field'] = GetExtraFieldName(str_replace("EFID", "", $con['field']));
					}


					$html .= "<tr><td>" . $con['conid'] . "</td><td>" . htme($con['field']) . "</td><td class='nwrp'>" . $ins . "</td><td>" . htme($con['value']) . "</td></tr>";
					}
					$html .= "</table>";
					$tp .= "<td class='nwrp' style='background-color: #FFFFCC;' " . PrintToolTipCode($html) . "> Apply";


				} else {
					$tp .= "<td class='nwrp'> None set";
				}
				$tp .= "</td>";

				$tp .= $form;
				$tp .= "<td>";

				$tp .= str_replace("\n","<br>",htme($row['comment']));
				$tp .= "</td>";
				$tp .= "<td><a href='trigger.php?del_trigger=" . $row['tid'] . "'><img src='images/delete.gif' alt=''></a></td>";
				if ($row['enabled'] == "yes") {
					$tp .= "<td><a href='trigger.php?toggle_trigger=" . htme($row['tid']) ."&amp;filter=" . htme($_REQUEST['filter']) . "'><img src='images/ok.gif' alt=''></a></td></tr>";
				} else {
					$tp .= "<td><a href='trigger.php?toggle_trigger=" . htme($row['tid']) ."&amp;filter=" . htme($_REQUEST['filter']) . "'><img src='images/smallerrorsmall.gif' alt=''></a></td></tr>";
				}
				$c++;
			}
			if ($c<1) {
				if (!$_REQUEST['trigger_filter']) {
					$tp .= "<tr><td colspan='12'><strong>No triggers defined</strong>";
					$tp .= ($_REQUEST['filter']) ? "(" . htme($_REQUEST['filter']) . ")" : "";
					$tp .= "</td></tr>";
				} else {
					$tp .= "<tr><td colspan='10'>No triggers defined matching your filter</td></tr>";
				}
				$noprint = true;
			}
			$tp .= "</table></td></tr></table></td></tr>";
		//}
		//if (!$noprint) {
			print $tp;
		//}
	}

	if ($_REQUEST['fetch']) {
		if ($show_module_code) {
			$_REQUEST['mid'] = $show_module_code;
			print "<tr><td></td><td>";
			ShowModuleEditScreen();
			print "</td></tr>";
		}
		print "<tr><td></td><td>";
		print "<img class=\"expand\" title=\"TriggerLogDiv\" style='display: inline; cursor: pointer' src='images/t_plus.jpg' alt=''> Last 25 journal entries";
		print "<div id='TriggerLogDiv' style='display: none;'>";
		print "<table class='interleave-table'><thead><tr><td><strong>Time</strong></td><td><strong>User</strong></td><td><strong>Message</strong></td></tr></thead>";
		$tmp = db_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "journal WHERE type='trigger' AND eid='" . mres($_REQUEST['fetch']) . "' ORDER BY timestamp_last_change DESC LIMIT 25");
		foreach ($tmp AS $lr) {
			print "<tr><td valign='top'>" . $lr['timestamp_last_change'] . "</td><td valign='top'>" . htme(GetUserName($lr['user'])) . "</td><td>" . nl2br($lr['message']) . "</td></tr>";
		}
		print "</table></div>";
		print "</td></tr>";

	}

	print "</table>";
	$tot_opt+=6;
	$tot_act = sizeof($actionlist);
	$tot_opt2 = pow($tot_act,$tot_opt);


	if ($_REQUEST['add']) {
		?>
		<script type="text/javascript">
		<!--
			CheckMailTrigger();
		//-->
		</script>
		<?php
	}
} // end if access
EndHTML();
?>
