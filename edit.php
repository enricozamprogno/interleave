<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 info@interleave.nl
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * Handles new entity forms (e=_new_) and the edit of existing entities (e=[entity_nr])
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */

require_once("initiate.php");
$tmp_stash = $_REQUEST['e'];  // stash the entity number!
$GLOBALS['CURFUNC'] = "Edit::";

if ($_REQUEST['NoMenu']) {
	$_REQUEST['nonavbar'] = 1;
}

if ($_REQUEST['ActivityGraph']) {

	DisplayEntityActivityGraph($_REQUEST['ActivityGraph']);

} elseif ($_REQUEST['ActivityGraph2']) {
	DisplayEntityActivityGraph2($_REQUEST['ActivityGraph2']);

} elseif ($_REQUEST['journal']) {		// Show journal of a certain entity
	
	$_REQUEST['nonavbar'] = 1;
	ShowHeaders();
	if ($_REQUEST['FlexTable'] && $_REQUEST['recordid']) {
		print ShowJournal($_REQUEST['recordid'], $_REQUEST['FlexTable']);
	} elseif ($_REQUEST['trigger'] && $_REQUEST['recordid']) {
		print ShowJournal($_REQUEST['recordid'], "trigger");
	} elseif ($_REQUEST['type'] == "group" && $_REQUEST['recordid']) {
		print ShowJournal($_REQUEST['recordid'], "group");
	} elseif ($_REQUEST['type'] == "user" && $_REQUEST['recordid']) {
		print ShowJournal($_REQUEST['recordid'], "user");
	} elseif ($_REQUEST['custid']) {
		print ShowJournal($_REQUEST['custid']);
	} else {
		print ShowJournal($_REQUEST['eid']);
	}
	EndHTML();


} else {

	 

	

	if ($_REQUEST['saveasnew']) {
		$_REQUEST['action'] = "add";
		$_REQUEST['e'] = "_new_";
		$_REQUEST['eid'] = "_new_";
		$tmp_stash = "_new_";
		qlog(INFO, "Entity " . $_REQUEST['eid'] . " used as template for new entity :: " . $_REQUEST['action']);
	} elseif (IsValidEID($_REQUEST['e'])) {
		journal($_REQUEST['e'], "Entity viewed", "entity");
	}

	if (!$_REQUEST['action'] == "edit" && is_numeric($GLOBALS['UC']['FORCESTARTFORM']) && IsValidEID($eid)) {
		$GLOBALS['LOCALFORCEFORM'] = $GLOBALS['UC']['FORCESTARTFORM'];
		qlog(INFO, "Form reset to " . $GLOBALS['UC']['FORCESTARTFORM'] . " (FORCESTARTFORM)");
	} else {
		$GLOBALS['LOCALFORCEFORM'] = "";
	}

	
	if ($_REQUEST['e']) {
		$eid = trim($_REQUEST['e']);
		qlog(INFO, "EID is (e) " . $_REQUEST['e']);
		if ($_REQUEST['customer'] == "") {
			$_REQUEST['customer'] = GetEntityCustomer($eid);
		}

	} elseif ($_REQUEST['eid']) {
		$eid = $_REQUEST['eid'];
		qlog(INFO, "EID Corrected! (" . $eid . ")");
		if ($_REQUEST['customer'] == "") {
			$_REQUEST['customer'] = GetEntityCustomer($eid);
		}

	}




	if ($_REQUEST['entity'] && $_REQUEST['newform'] && is_administrator()) {
		if ($_REQUEST['newform'] == "default") {
			$_REQUEST['newform'] = 0;
		}
		qlog(INFO, "Administrative form change of entity " . $_REQUEST['entity'] . " requested");
		mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET formid = " . $_REQUEST['newform'] . " WHERE eid=" . $_REQUEST['entity'], $db);
		CleanUpCacheTablesAfterSave();

		log_msg("Form change of entity " . $_REQUEST['entity'] . " to " . $_REQUEST['newform'] . " by administrator");
		
		header("Location: edit.php?e=" . $_REQUEST['entity']);
	} elseif ($_REQUEST['fconfirmed']) {
		
		if (CheckEntityAccess($_REQUEST['e']) <> "ok") {
			printAD("You're are not allowed to delete this file(s)");
			EndHTML();
			exit;
		} else {
			for ($c=0;$c<sizeof($_REQUEST['deletefile']);$c++) {
				$eid_coup = db_GetValue("SELECT koppelid FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles WHERE fileid='" . $_REQUEST['deletefile'][$c] . "'");
				if ($eid_coup == $_REQUEST['e']) {
					DeleteFile($_REQUEST['deletefile'][$c]);
				} else {
					log_msg("ERROR: Some tried to delete a file coupled to " . $eid_coup . " while accessing " . $_REQUEST['e'] . ". Possible break-in attempt?");
				}
			}
		}
		unset($_REQUEST['deletefile']);
		header("Location: edit.php?e=" . $_REQUEST['e']);

	
	} elseif (is_numeric($eid) && !IsValidEID($eid)) { // Invalid entity number

		ShowHeaders();
		PrintAD("Invalid request");
		EndHTML();

	} elseif ($e=="_new_" && (db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "customer WHERE active='yes'") < 1)) { // No active customers!
		
		ShowHeaders();
		PrintAD("You cannot add entities if you have no customers in your database.");
		EndHTML();

	} elseif ($_REQUEST['e'] == "_new_" && $_REQUEST['action'] == "add" && !IsValidCID($_REQUEST['customer'])) { // Not a valid customer

		ShowHeaders();
		PrintAD("Invalid " . $lang['customer'] . " submitted. Entity not saved!");
		log_msg(ERROR, "Invalid " . $lang['customer'] . " submitted. Entity not saved!");
		EndHTML();

	} elseif (CheckEntityAccess($eid)=="nok" && IsValidEID($eid)) { // User doesn't have access to this page

		ShowHeaders();
		PrintAD("Access to this page is denied.");
		EndHTML();

	} elseif ($_REQUEST['e'] == "_new_" && !in_array($_REQUEST['ftu'], $GLOBALS['UC']['ALLOWEDADDFORMS']) && is_numeric($_REQUEST['ftu'])) { // Form cannot be used to add entities

		ShowHeaders();
		PrintAD("You cannot add new entities using this form (" . $_REQUEST['ftu'] . ")");
		EndHTML();

	} elseif ($_REQUEST['action'] == "edit" && CheckLock($eid)) { // A lock exists on this entity; abort!

		ShowHeaders();
		qlog(WARNING, "WARNING: Somehow this user (" . GetUserName($GLOBALS['USERID']) . ") tried to save an entity which was locked ($eid).");
		log_msg("WARNING: Somehow this user (" . GetUserName($GLOBALS['USERID']) . ") tried to save an entity which was locked. ($eid)","");
		PrintAD("This entity is currently locked");
		EndHTML();
		
	} elseif ($_REQUEST['action'] == "edit" && CheckEntityAccess($eid) <> "ok") { // User wants to edit/save, but has no access; abort!

		ShowHeaders();
		uselogger("WARNING: User " . GetUserName($GLOBALS['USERID']) . " tried to post data to an entity (" . $eid . ") to which he/she has no access!","");
		qlog(INFO, "User " . GetUserName($GLOBALS['USERID']) . " tried to post data to an entity (" . $eid . ") to which he/she has no access!");
		PrintAD("You don't have sufficient rights (anymore) to access this entity.");
		EndHTML();

	} elseif ($_REQUEST['action']== "add" && $GLOBALS['ONEENTITYPERCUSTOMER'] == "Yes" && db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX']  . "entity WHERE deleted<>'y' AND CRMcustomer='" . mres($_REQUEST['customer']) . "'") > 1) { // This customer already has an entity; not allowed

		ShowHeaders();
		qlog(INFO, "This customer alread has an non-deleted entity and ONEENTITYPERCUSTOMER is enabled. Not adding this.");
		printAD("Double add is not allowed. Close other entity of " . $lang['customer'] . " \"" . GetCustomerName($cust) . "\" first");
		EndHTML();


	} else {

		ShowHeaders();
//		DA();

		if ($_REQUEST['e'] == "_new_") {
				CheckPageAccess("add");
		}


		if (CheckEntityAccess($eid) == "readonly") {
			$roins = "disabled='disabled'";
			$readonly = 1;
			$GLOBALS['nolocking'] = true;
		}


		print '<script type="text/javascript">';
		print 'function AlertUser(whichLayer) {';
		print "document.forms['EditEntity'].elements['changed'].value = '1';";

		if ($GLOBALS['USE_AUTOSAVE'] == "Yes") {
			print "if (document.forms['EditEntity'].elements['sb2']) {";
			print "document.forms['EditEntity'].elements['sb2'].disabled=true;";
			print "}";
		}
		print "}</script>";

		
		// Form Handling / Corrections

		if ($_REQUEST['duedate'] == "undefined") {
			$_REQUEST['duedate'] = "";
			$sqldate = "3003-01-01"; // Far, far away
		} elseif ($_REQUEST['duedate']) {
			$_REQUEST['duedate'] = FormattedDateToNLDate($_REQUEST['duedate']);
			$sqldate = NLDate2INTLDate(FormattedDateToNLDate($_REQUEST['duedate']));
		}

		if ($_REQUEST['startdate'] == "undefined") {
			$_REQUEST['startdate'] = "";
			$sqlstartdate = "3003-01-01";
		} elseif ($_REQUEST['startdate']) {
			$_REQUEST['startdate'] = FormattedDateToNLDate($_REQUEST['startdate']);
			$sqlstartdate = NLDate2INTLDate(FormattedDateToNLDate($_REQUEST['startdate']));
		}



		
		// Non-entered values mean no in this case:
		$keys = explode(" ", "deleted_posted obsolete_posted waiting readonly_posted private_posted notify_owner_posted notify_assignee_posted");
		foreach ($keys AS $key) {
			if (array_key_exists($key,$_REQUEST)) {
				$keyval = str_replace("_posted", "", $key);
				if ($_REQUEST[$keyval] != "y") {
					$_REQUEST[$keyval] = "n";
				}
			}
		}

		$skipsec = false;

		if ($_REQUEST['action']== "add") {
			
			$skipsec = true;
			
			$unique = $_REQUEST['hash'];
			$recentlyadded = db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "cache WHERE value='" . mres($unique) . "'");

			if ($recentlyadded > 0 && ($GLOBALS['CHECKFORDOUBLEADDS'] == "Yes")) {
				qlog(INFO, "CheckExistence:: The same - " . $unique . " NOT SAVING THIS");
				print "<span class=\"noway\">Avoided adding the same record twice.</span>";
			} else {
				if ($GLOBALS['CHECKFORDOUBLEADDS'] == "Yes")  {	// do not push this value if it is disabled
					PushStashValue($unique);
				} else {
					qlog(INFO, "Entity double-add check is disabled! (not saving MD5)");
				}
				
				// Actually add a new entity here 
				// Insert a new entity into SQL

				if (CheckFunctionAccess("NoOwnNoAssign") == "ok") {
					if ($_REQUEST['assignee'] == "" || $_REQUEST['assignee'] == $GLOBALS['USERID']) {
						$_REQUEST['assignee'] = "2147483647";
					}
					if ($_REQUEST['owner'] == "" || $_REQUEST['owner'] == $GLOBALS['USERID']) {
						$_REQUEST['owner'] = "2147483647";
					}
				}

				$openepoch = date('U');
				
				$sql = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "entity(deleted,openepoch,cdate,createdby,formid,owner,assignee) VALUES('n', " . date('U') . ",'" . date('Y-m-d') . "','" . $GLOBALS['USERID'] . "','" . mres($_REQUEST['formid']) . "','" . mres($_REQUEST['owner']) . "','" . mres($_REQUEST['assignee']) . "')";
				mcq($sql, $db);

				$eid = mysql_insert_id();
				$_REQUEST['eid'] = $eid;

				array_push($GLOBALS['PageCache']['ValidEIDs'], $eid);

				Journal($eid, "Entity created");
	
				AddDefaultExtraFields($eid);

				$_REQUEST['action'] = "edit";
				
				// Set variable to make sure the new-entity triggers are ran after the regular update
				$ThisIsANewEntity = true;
								
				if ($GLOBALS['PrintJSAfterAddingEntity'] == true) {
					print '<script type="text/javascript" src="csv.php?GetJS&amp;ent=' . $eid . '"></script>';
				}

				//print "<strong>" . $lang['entrysaved'] . " " . $eid . "</strong><br>";

			}
		}

		if (count($_FILES['userfile']['name']) > 0) {

			for ($tel=0;$tel<sizeof($_FILES['userfile']['name']);$tel++) {
					
				$tmpfile = $_FILES['userfile']['tmp_name'][$tel];
				$size    = $_FILES['userfile']['size'][$tel];
				$type    = $_FILES['userfile']['type'][$tel];
				$name    = $_FILES['userfile']['name'][$tel];
				
				if ($tmpfile != "") {	
					// A file was attached
					// Read contents of uploaded file into variable
					$x = AttachFile($_REQUEST['eid'], $name, file_get_contents($tmpfile), "entity",$type, false, false, false, false, $skipsec);


				}
			}
		} else {
			qlog(INFO, "No file received!");
		}

		if ($_REQUEST['action'] == 'edit') {
			

			// Select old values

			$old_values = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE eid='" . mres($_REQUEST['eid']) ."'");

			$AccArr = GetClearanceLevel();

			if ($_REQUEST['owner'] == "" || in_array("NoOwnNoAssign",$AccArr)) {
				$_REQUEST['owner'] = $old_values['owner'];
				qlog(ERROR, "ERROR - No owner received - reset to old value");
			}
			if ($_REQUEST['assignee'] == "" || in_array("NoOwnNoAssign",$AccArr)) {
				$_REQUEST['assignee'] = $old_values['assignee'];
			}
			if ($_REQUEST['owner']<>"" && $_REQUEST['assignee']<>"") {
				$int = " assignee='" . mres($_REQUEST['assignee']) . "', owner='" . mres($_REQUEST['owner']) . "', ";
			}

			// If this entity was deleted in this action, set the close date
			if ($old_values['deleted'] != $_REQUEST['deleted'] && $_REQUEST['deleted']=="y") {

					$sql = "SELECT closedate,closeepoch FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE eid='" . $eid . "'";
					$result = mcq($sql,$db);
					$row = mysql_fetch_array($result);

					if ($row['closeepoch']==0) {
						$closedate = date('Y-m-d');
						$closeepoch = date('U');
						$addon = ",closedate='" . mres($closedate) . "',closeepoch='" . mres($closeepoch) . "'";
					} else {
						qlog(INFO, "NOT Resetting the close date!");

					}
			} else {
	//			print "OV:" .  $old_values['deleted'] . " != " . $_REQUEST['deleted']  . "&&" .  $_REQUEST['deleted'] . "=='y'";
			}
			$add_to_journal = "Entity values " . $eid . " saved";
			if (is_numeric($_REQUEST['parent']) && ($_REQUEST['parent']<>0)) {
				if (ValidateParentalRights($_REQUEST['parent'], $eid) == false) {
					print "Not possible to set this entity as child from " . $_REQUEST['parent'];
					log_msg("WARNING: Not possible to set this entity ($eid) as child from " . $_REQUEST['parent'] , "");
					$_REQUEST['parent'] = 0;
					$parent = 0;
					$add_to_journal .= "\nParent NOT set to " . $_REQUEST['parent'] . " (access denied)";
				}
				$add_to_journal .= "\nParent set to " . $_REQUEST['parent'];
			}


			// Check which fields are actually set
			// Check which variables were passed through (we don't want to delete non-posted fields)
			// CRMcustomer, assigneeNEW and ownerNEW will always be posted!

			$int .= (array_key_exists('status',$_REQUEST))			? "status='" . mres(trim($_REQUEST['status'])) . "'," : "";
			$int .= (array_key_exists('priority',$_REQUEST))		? "priority='" . mres(trim($_REQUEST['priority'])) . "'," : "";
			$int .= (array_key_exists('category',$_REQUEST))		? "category='" . mres($_REQUEST['category']) . "'," : "";
			$int .= (array_key_exists('content',$_REQUEST))			? "content='" . mres($_REQUEST['content']) . "'," : "";
			$int .= (array_key_exists('readonly',$_REQUEST))		? "readonly='" . mres($_REQUEST['readonly']) . "'," : "";
			$int .= (array_key_exists('notify_owner',$_REQUEST))	? "notify_owner='" . mres($_REQUEST['notify_owner']) . "'," : "";
			$int .= (array_key_exists('notify_assignee',$_REQUEST)) ? "notify_assignee='" . mres($_REQUEST['notify_assignee']) . "'," : "";
			$int .= (array_key_exists('private',$_REQUEST))			? "private='". mres($_REQUEST['private']) . "'," : "";
			$int .= (array_key_exists('duetime',$_REQUEST))			? "duetime='". mres($_REQUEST['duetime']) ."'," : "";
			$int .= (array_key_exists('duedate',$_REQUEST))			? "duedate='" . mres($_REQUEST['duedate']) ."'," : "";
			$int .= (array_key_exists('parent',$_REQUEST))			? "parent='" . mres($_REQUEST['parent']) . "'," : "";
			$int .= (array_key_exists('obsolete',$_REQUEST))		? "obsolete='" . mres($_REQUEST['obsolete']) . "'," : "";
			$int .= (array_key_exists('deleted',$_REQUEST))			? "deleted='" . mres($_REQUEST['deleted']) . "'," : "";
			$int .= (array_key_exists('waiting',$_REQUEST))			? "waiting='" . mres($_REQUEST['waiting']) ."'," : "";
			$int .= (array_key_exists('startdate',$_REQUEST))		? "startdate='" . mres($_REQUEST['startdate']) ."', sqlstartdate='" . mres(NLDate2INTLDate($_REQUEST['startdate'])) . "'," : "";

			// Update to new values
			$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET CRMcustomer='" . mres($_REQUEST['customer']) . "', " . $int . " sqldate='" . mres($sqldate) . "' " . $addon . ",lasteditby='" . mres($user_id) ."', assignee='" . mres($_REQUEST['assignee']) . "',owner='" . mres($_REQUEST['owner']) . "', timestamp_last_change=NOW() WHERE eid='" . mres($eid) . "'";

			// Execute query
			mcq($sql,$db);

			// Now see if there were any extra fields added:
			// First, collect extra fields list
			$list = GetExtraFields();
			// Second, get all extra fields of this entity
			$af = array();
	//		$allfield_sql = "SELECT eid,value,name FROM " . $GLOBALS['TBL_PREFIX'] . "c1ustomaddons WHERE eid='" . mres($eid) . "' AND type='entity'";
	//		$res = mcq($allfield_sql, $db);
	//		while ($row = mysql_fetch_array($res)) {
	//			$af[$row['name']] = $row;
	//		}
			$af = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE eid='" . mres($eid) . "'");

			# >X - Check each possible extra field submitted by the edit form.
			# Insert, update, or delete as appropriate.
			#
			
			$efield_trigger_varnames = array ();
			$efield_trigger_ent_ids = array ();
			$efield_trigger_field_vals = array ();
			foreach ($list as $extrafield) {
				$efield_id = $extrafield['id'];
				$efield_name = $extrafield['name'];
				$efield_varname = "EFID" . $efield_id;
				$efield_form_value = $_REQUEST[$efield_varname];
				$efield_type = $extrafield['fieldtype'];

				$efield_curr_value = $af[$efield_varname];

				if ($efield_type == "date" && $efield_form_value == "undefined") {
					//print $efield_form_value;
					//print $efield_type;
					$efield_form_value = "";
				}

				if (strstr(GetExtraFieldType($efield_id), "multiselect") && isset($_REQUEST[$efield_varname]) && count($_REQUEST[$efield_varname]) == 1 && $_REQUEST[$efield_varname][0] == "{{{null}}}") {
					$_REQUEST[$efield_varname] = array();
				}


				# debugging output
				# print "efield_curr_value: '" . $efield_curr_value . "'<br>";
				if (is_array($efield_form_value)) {
					$tmp = array();
					foreach($_REQUEST[$efield_varname] AS $row) {
						if ($row <> ""  && $row != "{{{null}}}") {
							array_push($tmp, base64_encode($row));
						}
					}
					$efield_form_value = serialize($tmp);
				}

			

				# If form has no value and database has no value, then there's no possible update (do nothing).
				# If form has value and database has value and values MATCH, then user has made no change (do nothing).
				# If form has no value and database has value, then user wants to delete current value (delete).
				# If form has value and database has no value, then user wants to set value for first time (insert).
				# If form has value and database has value and values DO NOT match, then user wants to change value (update).


				if (GetExtraFieldType($efield_id) == "date" && $efield_form_value != "") {
					$efield_form_value = FormattedDateToNLDate($efield_form_value);
				} elseif (GetExtraFieldType($efield_id) == "date/time") {
					$efield_form_value = FormattedDateTimeToSQLDateTime($efield_form_value);
					if ($efield_form_value == "") {
						$efield_form_value = "0000-00-00 00:00:00";
					}
				} 
				
				if ($ThisIsANewEntity && $efield_type == "diary" && $efield_form_value != "") {
					$efield_form_value = serialize(array(array(date('U'), $GLOBALS['USERID'], $efield_form_value)));
				} elseif ($efield_type == "diary" && $efield_form_value != "") {
					UpdateDiaryField($eid, $efield_id, $dummy, $efield_form_value, $_REQUEST['commenthash']);
					continue;
				} elseif ($efield_type == "diary") {
					continue;
				} 
				
				if (isset($_REQUEST[$efield_varname]) && ValidateFieldInput(str_replace("EFID", "", $efield_varname), $efield_form_value, false,true,$eid) != $efield_form_value) {
					//print "Input check failed for $efield_varname. Reverting to old value.<br>";
					log_msg("ERROR: Input check failed for $efield_varname; $efield_form_value didn't validate. Reverting to old value. Reason: " . ValidateFieldInput(str_replace("EFID", "", $efield_varname), $efield_form_value, false,true,$eid));
					

				} else {
					
					//print "Input check SUCCES for $efield_varname. : " . ValidateFieldInput(str_replace("EFID", "", $efield_varname), $efield_form_value, false,true,$eid) . "<br>";
				}

				if ($efield_form_value . " " == $efield_curr_value . " ") {
					qlog(INFO, "Field " . $efield_varname . " left alone, no change in value.");
					continue;
				} elseif (!array_key_exists($efield_varname, $_REQUEST)) {
					qlog(INFO, "Ignoring extra field " . $efield_varname . " - it was not posted to the webserver");
					continue;
				} elseif (($efield_form_value=="") && ($efield_curr_value!="")) {
					if (CheckExtraFieldAccess($efield_id) == "ok") {
						$efield_sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET EFID" . $efield_id . "='' WHERE eid=" . mres($eid);
						$add_to_journal .= "\n" . $efield_name . " updated from [" . FunkifyLOV($efield_curr_value) . "] to *nothing*";
						
						DataJournal($eid, FunkifyLOV($efield_curr_value), "", $efield_id, "entity");

						qlog(INFO, "Field " . $efield_varname . " was emptied.");

					} else {
						// log_msg("ERROR: User " . $GLOBALS['USERID'] . " tried to empty field " . $efield_varname . ", but has no access to this field!");
					}
				} elseif ($efield_curr_value != "") {
					if (CheckExtraFieldAccess($efield_id) == "ok") {
						$efield_sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET EFID" . $efield_id . "='" . mres($efield_form_value) . "' WHERE eid=" . mres($eid);
						$add_to_journal .= "\n" . $efield_name . " updated from [" . FunkifyLOV($efield_curr_value) . "] to [" . FunkifyLOV($efield_form_value) . "]";
						DataJournal($eid, $efield_curr_value, $efield_form_value, $efield_id, "entity");
						qlog(INFO, "Field " . $efield_varname . " was updated.");
					} else {
						// log_msg("ERROR: User " . $GLOBALS['USERID'] . " tried to update field " . $efield_varname . ", but has no access to this field!");
					}
				} else {
					if (CheckExtraFieldAccess($efield_id) == "ok") {
						$efield_sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET EFID" . $efield_id . "='" . mres($efield_form_value) . "' WHERE eid=" . mres($eid);
						$add_to_journal .= "\n" . $efield_name . " updated from *nothing* to [" . FunkifyLOV($efield_form_value) . "]";
						DataJournal($eid, "", $efield_form_value, $efield_id, "entity");
						qlog(INFO, "Field " . $efield_varname . " was updated.");
					} else {
						// log_msg("WARNING: User " . $GLOBALS['USERID'] . " tried to set field " . $efield_varname . ", but has no access to this field!");
					}

				}
				if ($efield_sql) {
				//	print "<h2>" . $efield_sql . "</h2>";
					mcq($efield_sql, $db);
					
				}
				$efield_trigger_varname = "EFID" . $efield_id;
				$efield_trigger_ent_id = $eid;
				$efield_trigger_field_val = $efield_form_value;
				array_push ($efield_trigger_varnames, $efield_trigger_varname);
				array_push ($efield_trigger_ent_ids, $efield_trigger_ent_id);
				array_push ($efield_trigger_field_vals, $efield_trigger_field_val);
			}
			# >X - Process the extra field triggers, if any.
			#
			for ($trigger_count = 0; $efield_trigger_varnames[$trigger_count]; $trigger_count++) {
				$efield_trigger_varname = $efield_trigger_varnames[$trigger_count];
				$efield_trigger_ent_id = $efield_trigger_ent_ids[$trigger_count];
				$efield_trigger_field_val = $efield_trigger_field_vals[$trigger_count];
				ProcessTriggers ($efield_trigger_varname, $efield_trigger_ent_id, $efield_trigger_field_val);
			}

			if (EmailTriggerForOwnerSet($eid)) {
				ProcessTriggers("entity_change_select_owner",$eid,"");
			}
			if (EmailTriggerForAssigneeSet($eid)) {
				ProcessTriggers("entity_change_select_assignee",$eid,"");
			}

			// Let's see what has changed
			// T R A C K  C H A N G E S  F O R  J O U R N A L -------------------------------------------------------------
			if ($old_values['notify_owner']<>$_REQUEST['notify_owner'] && $_REQUEST['notify_owner']<>'') {
				$add_to_journal .= "\nOwner e-mail notification switched to " . $_REQUEST['notify_owner'];
			}
			if ($old_values['notify_assignee']<>$_REQUEST['notify_assignee'] && $_REQUEST['notify_assignee']<>'') {
				$add_to_journal .= "\nAssignee e-mail notification switched to " . $_REQUEST['notify_assignee'];
			}
			if (($old_values['assignee'] != $_REQUEST['assignee'] && $_REQUEST['assignee'] != "") || $ThisIsANewEntity) {
				$add_to_journal .= "\n" . $lang['assignee'] . " updated from [";
				if (!$ThisIsANewEntity) {
					$add_to_journal .= GetUserName($old_values['assignee']);
				}
				$add_to_journal .= "] to [" . GetUserName($_REQUEST['assignee']) . "]";

				DataJournal($eid, $old_values['assignee'], $_REQUEST['assignee'], "assignee", "entity");
				
				ProcessTriggers("assignee",$eid,$_REQUEST['assignee']);
			}
			if (($old_values['owner'] != $_REQUEST['owner'] && $_REQUEST['owner'] != "") || $ThisIsANewEntity) {
				$add_to_journal .= "\n" . $lang['owner'] . " updated from [";
				if (!$ThisIsANewEntity) {
					$add_to_journal .= GetUserName($old_values['owner']);
				}
				$add_to_journal .= "] to [" . GetUserName($_REQUEST['owner']) . "]";
				ProcessTriggers("owner",$eid,$_REQUEST['owner']);
				DataJournal($eid, $old_values['owner'], $_REQUEST['owner'], "owner", "entity");
			}

			if (($old_values['category']<>$_REQUEST['category'] && isset($_REQUEST['category'])) || $ThisIsANewEntity) {
				$add_to_journal .= "\n" . $lang['category'] ." updated from [" . $old_values['category'] . "] to [" . $_REQUEST['category'] . "]";
			}
			if (($old_values['status'] <> $_REQUEST['status'] && isset($_REQUEST['status']))  || $ThisIsANewEntity) {
				$add_to_journal .= "\n" . $lang['status'] . " updated from [" . $old_values['status'] . "] to [" . $_REQUEST['status'] . "]";
				DataJournal($eid, $old_values['status'], $_REQUEST['status'], "status", "entity");
				ProcessTriggers("status",$eid,$_REQUEST['status']);
			}

			if (($old_values['private']<>$_REQUEST['private']) && isset($_REQUEST['private'])) $add_to_journal .= "\nprivate updated from [" . $old_values['private'] . "] to " . $_REQUEST['private'];
			
			if (($old_values['priority']<>$_REQUEST['priority'] && isset($_REQUEST['priority'])) || $ThisIsANewEntity) {
				$add_to_journal .= "\n" . $lang['priority'] . " updated from [" . $old_values['priority'] . "] to " . $_REQUEST['priority'];
				ProcessTriggers("priority",$eid,$_REQUEST['priority']);
				DataJournal($eid, $old_values['priority'], $_REQUEST['priority'], "priority", "entity");
			}
			if ($old_values['CRMcustomer']<>$_REQUEST['customer']) {
					$add_to_journal .= "\n$lang[customer] updated from [" . GetCustomerName($old_values['CRMcustomer']) . "] to [" . GetCustomerName($_REQUEST['customer']) . "]";
					
					DataJournal($eid, $old_values['CRMcustomer'], $_REQUEST['customer'], "CRMcustomer", "entity");

					// for now, the next line will only send a mail to the customer when requested
					ProcessTriggers("customer",$eid,$_REQUEST['customer']);
					// TRIGGER
					// The entity is coupled to a new customer which must be logged
					// in the customer journal and maybe someone must get an e-mail
					if (EmailTriggerForCustomerOwnerSet($_REQUEST['customer'])) {
						SendEmail($_REQUEST['customer'],"customer_owner","new",$eid,"","","");
						journal($_REQUEST['customer'],"Entity #" . $eid . " was coupled to this customer\nNotification e-mail send to " . GetUserName($row['customer_owner']), "customer");
					} else {
						journal($_REQUEST['customer'],"Entity #" . $eid . " was coupled to this customer", "customer");
					}
			}

			if ($ThisIsANewEntity) {
				$cl = GetClearanceLevel();
				if (in_array("NoOwnNoAssign", $cl)) {
					ProcessTriggers("limited_add",$eid,"");
				} else {
					ProcessTriggers("entity_add",$eid,"");
				}
			}

			if (($old_values['deleted'] != $_REQUEST['deleted']) && isset($_REQUEST['deleted'])) {
				$add_to_journal .= "\n" . $lang['deleted'] . "updated from " . $old_values['deleted'] . " to " . $_REQUEST['deleted'];
				DataJournal($eid, $old_values['deleted'], $_REQUEST['deleted'], "deleted", "entity");
			}
			if (($old_values['duedate']<>$_REQUEST['duedate']) && isset($_REQUEST['duedate'])) {
				$add_to_journal .= "\n" . $lang['duedate'] . " updated from [" . $old_values['duedate'] . "] to " . $_REQUEST['duedate'];
				ProcessTriggers("duedate_change",$eid,$_REQUEST['duedate']);
				
				DataJournal($eid, $old_values['duedate'], $_REQUEST['duedate'], "duedate", "entity");

			}
			if (($old_values['readonly']<>$_REQUEST['readonly']) && isset($_REQUEST['readonly'])) {
				$add_to_journal .= "\nRead-only updated from [" . $old_values['readonly'] . "] to [" . $_REQUEST['readonly'] . "]";
				DataJournal($eid, $old_values['readonly'], $_REQUEST['readonly'], "readonly", "entity");
			}
			if (($old_values['duetime']<>$_REQUEST['duetime']) && isset($_REQUEST['duetime'])) {
				$add_to_journal .= "\nDue time updated from [" . $old_values['duetime'] . "] to [" . $_REQUEST['duetime'] . "]";
				DataJournal($eid, $old_values['duetime'], $_REQUEST['duetime'], "duetime", "entity");
			}
			if (($old_values['content']<>$_REQUEST['content']) && isset($_REQUEST['content'])) {
				$add_to_journal .= "\nContents updated";
				DataJournal($eid, $old_values['content'], $_REQUEST['content'], "content", "entity");
			}


			// Make sure computed extra fields have the right value
			FindAndRecalculateAllRelatedRecords($eid, "entity");
			
			if (!$ThisIsANewEntity) {
				// Check if there are any "old" triggers to do (due to autosave)
				AddToDo("entity_change", "", $eid); // Add entity change to todo list if it wasn't there
			}

			// Laten staan, is voor JH van ES om te gebruiken in e-mails (werkt alleen als de mail niet gequeued wordt)
			$GLOBALS['JHES_ATJ'] = $add_to_journal;

			$GLOBALS['ChangeLogLastSave'] = $add_to_journal;

			$Todo = GetTodos($eid);
			foreach($Todo AS $do) {
				ProcessTriggers($do['onchange'],$eid,$do['to_value']);
				qlog(INFO, "TODO ProcessTrigger: " . $do['onchange'] . " value " . $do['to_value']);
			}
			DropTodos($eid);

			// Clear the access cache tables
			ClearAccessCache($eid,'e');

			// Disable entity access cache for rest of this run;
			unset($GLOBALS['CheckedEntityAccessArray']);

			// Clear form cache of parents, childs and sisters (if any)
			if (is_numeric($_REQUEST['parent']) && $_REQUEST['parent']<>0) {
				ExpireFormCache($_REQUEST['parent']);
				$ret = GetEntityChilds($_REQUEST['parent']); // fetch sisters
				foreach ($ret AS $child) {
					ExpireFormCache($child);
				}
			}

			$ret = GetEntityChilds($eid);
			foreach ($ret AS $child) {
				ExpireFormCache($child);
			}

			uselogger("Entity $eid updated","");

			// Process any buttons (must be the last action)s

			if ($_REQUEST['e_button']) {
				
				$x = GetButtons($_REQUEST['e_button']);
				if ($x['fieldtype'] == "Button") {
						// So, a button was pressed (and the user has the rights to press it)
						qlog(INFO, "An extra field button was pressed. Processing triggers.");
						journal($eid, "User pressed button " . $x['id'] . "::" . $x['name']);
						ProcessTriggers("ButtonPress" . $_REQUEST['e_button'],$eid,"");
						$tmp = GetAttribute("extrafield", "BackToListAfterSave", $x['id']);
						if ($tmp == "Yes") {
							$GLOBALS['STICKYENTITY'] = "No";
							//Alert('go');
						} elseif ($tmp == "No") {
							//Alert('stay');
							$GLOBALS['STICKYENTITY'] = "Yes";
						} 
					}

			}


		}
		
		if ($_REQUEST['deletefile']) {
		
			if (CheckEntityAccess($_REQUEST['e']) <> "ok") {
				printAD("You're not allowed to delete this file(s)");
			} else {
				print "<table><tr><td>";
				print $lang['deleting1'] . " " . sizeof($_REQUEST['deletefile']) . " " . $lang['deleting2'] . "<br>";
				print "<form id='confirm' method='post' action=''><div class='showinline'>";
				for ($c=0;$c<sizeof($_REQUEST['deletefile']);$c++) {
						print "<input type='hidden' name='deletefile[]' value='" . $_REQUEST['deletefile'][$c] . "'>";
				}
				print "<br><input type='hidden' name='fconfirmed' value='1'><input type='hidden' name='nonavbar' value='" . htme($_REQUEST['nonavbar']) . "'>";
				print "<input type='submit' name='knopje' value='" . $lang['confdel'] . "'>";
				print "<pre>";
				for ($r=0;$r<sizeof($_REQUEST['deletefile']);$r++) {
						print $lang[delete] . " " . $_REQUEST['deletefile'][$r] . " - " . GetFileName($_REQUEST['deletefile'][$r]) . "\n";
				}
				print "</pre>";
				print "<input type='hidden' name='e' value='" . $_REQUEST['e'] . "'>";
				print "</div></form></td></tr></table>";
			}
			EndHTML();

		} else {

			if (($_REQUEST['action']=='edit' || $_REQUEST['action']=='add') && $add_to_journal != "") {
				journal($eid,$add_to_journal);
			}

			
			if (IsValidEID($eid) || $eid == "_new_") {

				if ($_REQUEST['close_on_next_load'] && CheckFunctionAccess("HideNavigationTabs") != "ok") {

					// In this case the last update window was loaded in a pop window. Now the form is
					// submitted, the window may close itself
					print '<script type="text/javascript">';
					print 'parent.refresh_' . $_REQUEST['ParentAjaxHandler']. '("&Pag_Moment=' . addslashes($_REQUEST['Pag_Moment']) . '&fs=' . addslashes($_REQUEST['fs']) . '");';
					print 'parent.$.fancybox.close();';
					print '</script>';
					EndHTML();
					exit;
				} elseif ($_REQUEST['fromlistnow'] && $GLOBALS['STICKYENTITY'] <> "Yes") {


					// In this case the last update window was accessed from the main list,
					// after saving, go back to the list.

					$fromlisturl = $_REQUEST['fromlisturl'];

					if (strstr($fromlisturl,"____STASH-")){
						$url_to_go_to = PopStashValue(str_replace("____STASH-","",$fromlisturl));
					} elseif (strstr($fromlisturl,"____b64-")){
						$fromlisturl = str_replace("____b64-","",$fromlisturl);
						$url_to_go_to = base64_decode($fromlisturl);
					} else {
						$url_to_go_to = "index.php?ShowEntityList";
					}
		
					
		
					qlog(INFO, "Redirecting this user!");
					if ($GLOBALS['---INTERRUPTMESSAGE']) {
						print "<br><br><img src='images/crmlogosmall.gif' alt=''>&nbsp;<a href='" . $url_to_go_to . "'>Dismiss messages</a>";
						EndHTML();
						exit;
					} else {
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
				} 

				// Determine form type to use

				if (is_array($GLOBALS['UC']['ADDFORMLIST']) && $eid == "_new_") { // This means that the user may choose from different forms
						
					if ($GLOBALS['UC']['FORCEFORM']) {
						$lof = array($GLOBALS['UC']['FORCEFORM']);
					} else {
						$lof = $GLOBALS['UC']['ADDFORMLIST'];
					}
					$bla = array();	
					foreach ($lof AS $f) {
						if (db_GetValue("SELECT show_on_add_list FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templateid='" . mres($f) . "'") == "y") {
							$bla[] = $f;
						}
					}

					$to_tabs = array();
					$formlist = array();
					foreach ($bla AS $form) {
						if ($form <> "default" && CheckIfFormMainBePrintedOnAddList($form)) {
							$subj = GetTemplateSubject($form);
							if ($subj) {
								$formlist[] = $form;
								array_push($to_tabs, $form);
								qlog(INFO, "Added " . $subj . " to tab list!");
								$tabbs[$form] = array("edit.php?e=_new_&amp;AiM=1&amp;ftu=" . htme($form) . "&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "&amp;SetCustTo=" . htme($_REQUEST['SetCustTo']) . "&amp;CalendarAdd=" . htme($_REQUEST['CalendarAdd']) . "&amp;CalendarField=" . htme($_REQUEST['CalendarField']) => htme(GetTemplateSubject($form)), "comment" => "");
								$added_tabs++;
								if (!$first_usable_form) {
									$first_usable_form = $form;
								}
							}
						}
					}


					if (is_numeric($_REQUEST['ftu'])) {
						$navid = $_REQUEST['ftu'];
						$first_usable_form = $_REQUEST['ftu'];
						$GLOBALS['HIDEENTITYADDTABS'] = "Yes";
					} else {
						$navid = $first_usable_form ;
					}

			
					if (sizeof($formlist)>1  && $GLOBALS['navtype'] <> "PULLDOWN" && $GLOBALS['HIDEENTITYADDTABS'] <> "Yes" || $_REQUEST['AiM']) {
						if ($added_tabs > 1 && !$_REQUEST['noformtabs'] ) {
							InterTabs($to_tabs, $tabbs, $navid);
						}
					}

					if ($first_usable_form > 0) {
						print CustomEditForm($first_usable_form, $eid);
						qlog(INFO, "Building Custom Edit Form (" . $eid . ")");
					} else {
						print CustomEditForm($GLOBALS['DefaultForm'], $eid);
					}

				} elseif ($eid != "_new_" && is_numeric($_REQUEST['ftu'])) {

					qlog(INFO, "FormType: edit - Using form template from REQUEST");
					print CustomEditForm($_REQUEST['ftu'],$eid);

				} elseif ($eid <> "_new_" && is_numeric(GetEntityFormID($eid)) && GetEntityFormID($eid) <> 0 && ($GLOBALS['FormFinity']=="Yes")) {

					qlog(INFO, "FormType: edit - Using form template from entity");

					if (GetTemplateSubject(GetEntityFormID($eid)) <> "") {

							print html_compress(CustomEditForm(GetEntityFormID($eid),$eid));

						} else {
		
							print "<img src='images/error.gif' alt=''> Form " . GetEntityFormID($eid) . " not found. Defaulting.";
							log_msg("ERROR: Entity " . $eid . " wants to use form " . GetEntityFormID($eid) . " - this form is not available. Falling back to default form.");
							qlog(INFO, "Building Default Emergency Edit Form ($eid)");
							print CustomEditForm($GLOBALS['DefaultForm'],$eid);

						}


				} elseif ($eid=="_new_" && is_numeric($GLOBALS['UC']['ENTITY_ADD_FORM']) && (CheckEntityAccess($eid) == "ok")) {

					qlog(INFO, "FormType: add [full-access] - Using form template " . $GLOBALS['UC']['ENTITY_ADD_FORM']);
					print CustomEditForm($GLOBALS['UC']['ENTITY_ADD_FORM'],$eid);

				} elseif ($eid != "_new_" && is_numeric($GLOBALS['UC']['FORCEFORM'])) {

					qlog(INFO, "Building Forced Form (" . $eid . ")");
					print CustomEditForm($GLOBALS['UC']['FORCEFORM'],$eid);

				} else {

					qlog(INFO, "Building Default Emergency Edit Form (" . $eid . ")");
					print CustomEditForm($GLOBALS['DefaultForm'],$eid);

				}
		
				printAdminFormChanger($eid);
				EndHTML();

			} else {
				qlog(WARNING, "WARNING: Don't know what to do! Redirecting this user (" . GetUserName($GLOBALS['USERID']) . ") to the main page!");
				?>
					<script type="text/javascript">
					<!--
					document.location='index.php';
					//-->
					</script>
				<?php
				EndHTML();
			}

		}
	}
}
?>