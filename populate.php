<?php
/* ********************************************************************
* Interleave
* Copyright (c) 2001-2012 info@interleave.nl
* Licensed under the GNU GPL. For full terms see http://www.gnu.org/
*
* This file does several things :)
*
* Check http://www.interleave.nl/ for more information
*
* BUILD / RELEASE :: 5.5.1.20121028
*
**********************************************************************
*/
$_REQUEST['keeplocked'] = "1";
$_REQUEST['AjaxAssist'] = true;
$_REQUEST['nonavbar'] = 1;

require_once("initiate.php");

$GLOBALS['BrowseArray'] = $_REQUEST['BrowseArray'];

header("Content-Type: text/html; charset=UTF-8;");

$ret = "";
if ($_POST['getfieldvalue'] == 1) {
	if ($_POST['formatted'] == 1) {
		if (CheckElementAccess($_POST['record'], $_POST['field']) != "nok") {
			$value = GetExtraFieldValue($_POST['record'], $_POST['field'], true, false, $_POST['newvalue']);
			if ($value != "") {
				print $_POST['field'] . "|||" . $_POST['formelementToSet'] . "|||" . $value;
				$ok = true;
			}
		}
	}
} elseif ($_REQUEST['ReturnInteractiveFieldBox'] == 1) {
	
	$table = GetExtraFieldTableType($_REQUEST['field']);
	if ($table == "entity") {
		$acc = CheckEntityAccess($_REQUEST['eid']);
	} elseif ($table == "customer") {
		$acc = CheckCustomerAccess($_REQUEST['eid']);
	} elseif (is_numeric($table)) {
		$acc = CheckFlextableRecordAccess($table, $_REQUEST['eid'], false);
	}
	if ($acc == "ok") {
		if ((is_numeric($_REQUEST['field']) && CheckExtraFieldAccess($_REQUEST['field']) == "ok") || !is_numeric($_REQUEST['field'])) {
			print ReturnInteractiveFieldBox($_REQUEST['eid'], $_REQUEST['field']);
			$ok = true;
		} else {
			print "n/a";
		}
	} else {
		print "n/a hiero $acc -- $table";
	}
} elseif ($_REQUEST['UpdateDiaryField']) {
	qlog(INFO, "A request to update a diary field was submitted: eid " . $_REQUEST['eid'] . ", field " . $_REQUEST['efid'] . ", addition " . $_REQUEST['value']);
	UpdateDiaryField($_REQUEST['eid'], $_REQUEST['efid'], false, $_REQUEST['value'], $_REQUEST['commenthash']);
	$ok = true;
} elseif ($_REQUEST['checkuniqueness'] != "") {
	$field = $_POST['efid'];
	$value = $_POST['value'];
	$eid = $_POST['eid'];
	$odlvalue = $_POST['oldvalue'];

	$acc = CheckElementAccess($eid, $field);
	$sql_add = "";
	$tt = GetExtraFieldTableType($field);
	if ($tt == "entity") {
		$table = $GLOBALS['TBL_PREFIX'] . "entity";
		$id = "eid";
		$sql_add .= " AND deleted!='y'";
	} elseif ($tt == "customer") {
		$table = $GLOBALS['TBL_PREFIX'] . "customer";
		$id = "id";
	} else {
		$table = $GLOBALS['TBL_PREFIX'] . "flextable" . $tt;
		if ($MustBeUnique == "Yes (within refer)") {
			$refer = db_GetValue("SELECT refer FROM " . $table . " WHERE recordid='" . mres($eid) . "'");
			$sql_add = " AND refer='" . $refer . "'";
		}
		$id = "recordid";
		$sql_add .= " AND deleted!='y'";
	}
	if ($acc == "ok") {
	
		if (CheckUniqueness($eid, $field, $value)) {
			print "ok|||" . $field . "|||" . $value;
		} else {
			if ($eid != "_new_") {
				mcq("UPDATE " . $table . " SET EFID" . $field . " = '" . mres($oldvalue) . "' WHERE " . $id . " = " . $eid, $db);
			}
			print "nok|||" . $field . "|||" . $oldvalue;
		}
	}


	$ok = true;
} elseif ($_REQUEST['validatebyajax'] != "") {

	$field = $_REQUEST['efid'];
	$value = $_REQUEST['value'];
	$eid = $_REQUEST['eid'];
	$oldvalue = $_REQUEST['oldvalue'];

	$acc = CheckElementAccess($eid, $field);
	$sql_add = "";
	$type = GetExtraFieldTableType($field);
	
	if (is_numeric($field)) {
		$code = GetAttribute("extrafield", "CustomValidationFunctionPHP", $field);
	} elseif ($field == "category" || $field == "JS_category") {
		$code = GetAttribute("system", "CategoryCustomValidationFunctionPHP", 2);
		$type = "entity";
	} elseif ($field == "owner" || $field == "JS_owner") {
		$code = GetAttribute("system", "OwnerCustomValidationFunctionPHP", 2);
		$type = "entity";
	} elseif ($field == "assignee" || $field == "JS_assignee") {
		$code = GetAttribute("system", "AssigneeCustomValidationFunctionPHP", 2);
		$type = "entity";
	} elseif ($field == "duedate" || $field == "JS_duedate") {
		$code = GetAttribute("system", "DuedateCustomValidationFunctionPHP", 2);
		$type = "entity";
	} elseif ($field == "customer" || $field == "JS_customer") {
		$code = GetAttribute("system", "EntityCustomerCustomValidationFunctionPHP", 2);
		$type = "entity";
	} elseif ($field == "JS_custnamenew" || $field == "custnamenew") {
		$code = GetAttribute("system", "CustomernameCustomValidationFunctionPHP", 2);
		$type = "customer";
	} elseif ($field == "JS_status" || $field == "status") {
		$code = GetAttribute("system", "StatusCustomValidationFunctionPHP", 2);
		$type = "entity";
	} elseif ($field == "JS_priority" || $field == "priority") {
		$code = GetAttribute("system", "StatusCustomValidationFunctionPHP", 2);
		$type = "entity";
	}
	if (is_numeric($field)) {
		$field = "EFID" . $field;
	}
	if (substr($field, 0, 2) != "JS") {
		$field = "JS_" . $field;
	}

	if (trim($code) == "" || $code == "{{none}}") {
		print "ok|||" . $field . "|||" . $value;
		log_msg("ERROR: A server-side PHP field validation check failed because no code was found! (field " . $field . ")");
	} else {

		if (is_numeric($type)) {
			$recordid = $eid;
			$record = $eid;
			$flextable = $type;
			$flextableid = $type;
			$code = ParseFlextableTemplate($flextable, $recordid, $code);

			if (is_numeric($_REQUEST['refer'])) {
				$refer = $_REQUEST['refer'];
			} elseif (db_GetValue("SELECT refer FROM " . $GLOBALS['TBL_PREFIX'] . $flextable . " WHERE recordid='" . $recordid . "'") > 0) {
				$refer = db_GetValue("SELECT refer FROM " . $GLOBALS['TBL_PREFIX'] . $flextable . " WHERE recordid='" . $recordid . "'");
			}
			if ($refer > 0) {
				$ft = GetFlexTableDefinitions($flextable);
				if ($ft[0]['refers_to'] == "entity") {
					$code = ParseTemplateEntity($code, $refer, false, false);
				} elseif ($ft[0]['refers_to'] == "customer") {
					$code = ParseCustomerEntity($code, $refer, false, false);
				} elseif ($ft[0]['refers_to'] > 0) {
					$code = ParseFlextableTemplate($ft[0]['refers_to'], $refer, $code);
				}
			}
		} elseif ($type == "customer") {
			$cid = $eid;
			$eid = "";
			$code = ParseTemplateCustomer($code, $cid, false);
		} else {
			$code = ParseTemplateEntity($code, $eid, false, false);
		}
		unset($result);

		@eval($code);
		
		if (is_array($result)) {
			$res = $result[0];
			$msg = $result[1];
			$updatevalue = $result[2];
		} else {
			$res = $result;
			$msg = false;
		}
		if (trim($msg) == "") $msg = false;

		if ($acc == "ok") {
			if ($res == "ok") {
				print "ok|||" . $field . "|||" . $value;
				print "|||" . $msg;
				print "|||" . $updatevalue;
			} else {
				if ($res == "") $msg = "Validaton returned no result. Defaulting to NOK.";
				print "nok|||" . $field . "|||" . $oldvalueeee;
				print "|||" . $msg;
				print "|||" . $updatevalue;
			}
		} else {
		    //print "nok|||" . $field . "|||" . $oldvalue . "|||NO_ACCESS";
		}
	}
	$ok = true;

} elseif ($_REQUEST['single']) { 


		$eid   = $_REQUEST['eid'];
		$field = $_REQUEST['efid'];
		$value = $_REQUEST['value'];

		if (strstr($field, "-row")) { // inline form
			$tmp = explode("-", $field);
			$field = $tmp[0];
		}

		$table = GetExtraFieldTableType($field);

		qlog(INFO, "Response request received for eid " . $eid . ", field " . $field . ", table " . $table . " to set to value " . $value);
		
		if ($table == "entity") {
			$acc = CheckEntityAccess($_REQUEST['eid']);
			$id = "eid";
		} elseif ($table == "customer") {
			$acc = CheckCustomerAccess($_REQUEST['eid']);
			$id = "id";
		} elseif (is_numeric($table)) {
			$acc = CheckFlextableRecordAccess($table, $_REQUEST['eid'], false);
			$flextable = $table;
			$table = "flextable" . $table;
			$id = "recordid";
		}

		

		if ($acc == "ok") {
			$cur = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . $table . " WHERE " . $id . "='" . mres($eid) . "'");
		
			if (is_numeric($field)) {
				if (CheckExtraFieldAccess($field) == "ok") {
					$curvalue = $cur['EFID' . $field];
					$type = GetExtraFieldType($field);


					if ($curvalue != $value) {

						if (!$color = GetExtraFieldColor($field, $value)) {
							$color = "#ffffff";
						}
						
						if ($type == "date") {
							if (is_numeric(NLDate2Epoch(FormattedDateToNLDate($value)))) {
								SetExtraFieldValueSimple($field, $eid, FormattedDateToNLDate($value));
							} else {
								// New value is not a date
								$value = $curvalue;
							}
						} elseif ($type == "numeric") {
							if (is_numeric($value)) {
								SetExtraFieldValueSimple($field, $eid, $value);
							} else {
								// New value is not numeric, but it should be
								$value = $curvalue;
							}
						} elseif ($type == "drop-down") {
							if (in_array($value, GetExtraFieldOptions($field))) {
								SetExtraFieldValueSimple($field, $eid, $value);
							} else {
								// New value is not in field options list, denied
								$value = $curvalue;
							}
						} elseif ($type == "mail") {
							if (ValidateEmail($value)) {
								SetExtraFieldValueSimple($field, $eid, $value);
							} else {
								// New value is not an e-mail address, but it should be
								$value = $curvalue;
							}
						} elseif ($type == "hyperlink" || $type == "textbox") {
							
							SetExtraFieldValueSimple($field, $eid, $value);

						} elseif (strstr($type, "User-list")) {
							if (IsValidUser($value)) {
								SetExtraFieldValueSimple($field, $eid, $value);
								$value = GetUserName($value);
							} else {
								// Not a valid interleave user, denied
								$value = GetUserName($curvalue);
							}

						} elseif ($type == "List of all groups") {
							
							SetExtraFieldValueSimple($field, $eid, $value);
							$value = GetGroupName($value);

						} else {
							$value = $curvalue;
							log_msg("ERROR: An interactive field was posted, though it is not allowed to post the kind of fields (" . $field['fieldtype'] . ")");
						}

						
						if ($type == "drop-down") {
							
							$color = $fieldoptioncolors[$value];
						}
						//AddTodo("EFID" . $field, $value, $eid, $curvalue);
						journal($eid, "Field EFID" . $field . " updated from [" . $curvalue . "] to [" . $value . "]", $table);

						if ($type == "date") {
							ProcessTriggers("EFID" . $field,$eid,FormattedDateToNLDate($value),false,$flextable);
						} else {
							ProcessTriggers("EFID" . $field,$eid,$value,false,$flextable);
						}

						if ($type == "numeric" || (GetExtraFieldType($field) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $field) == "Numeric")) {
							$value = FormatNumber($value, 2, $field);
							
						}
						$color = GetExtraFieldColor($field, $value);
					} else {
						if ($type == "numeric" || (GetExtraFieldType($field) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $field) == "Numeric")) {
							$value = FormatNumber($value, 2, $field);
							
						}
						$color = GetExtraFieldColor($field, $value);
					}
				}	

				
			} else {

				$origvalue = $value;

				switch($field) {
					case "customer":
						if (IsValidCID($value)) {
							mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET CRMcustomer='" . mres($value) . "' WHERE eid='" . mres($eid) . "'", $db);
							$value = GetCustomerName($value);
						} else {
							$value = $cur['CRMcustomer'];
						}
					break;
					case "owner":
						if (IsValidUser($value)) {
							mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET owner='" . mres($value) . "' WHERE eid='" . mres($eid) . "'", $db);
							$value = GetUserName($value);
						} else {
							$value = $cur['owner'];
						}
					
					break;
					case "assignee":
						if (IsValidUser($value)) {
							mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET assignee='" . mres($value) . "' WHERE eid='" . mres($eid) . "'", $db);
							$value = GetUserName($value);
						} else {
							$value = $cur['assignee'];
						}
					break;
					case "status":
						if (IsValidStatus($value)) {
							mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET status='" . mres($value) . "' WHERE eid='" . mres($eid) . "'", $db);
							$color = GetStatusColor($value);
						} else {
							$value = $cur['status'];
						}
					break;
					case "priority":
						if (IsValidPriority($value)) {
							mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET priority='" . mres($value) . "' WHERE eid='" . mres($eid) . "'", $db);
							$color = GetPriorityColor($value);
						} else {
							$value = $cur['priority'];
						}
					break;
					case "category":
						mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET category='" . mres($value) . "' WHERE eid='" . mres($eid) . "'", $db);
						$value = htme($value);
					break;
					case "duedate":
						if (is_numeric(NLDate2Epoch(FormattedDateToNLDate($value)))) {
							mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET duedate='" . mres(FormattedDateToNLDate($value)) . "' WHERE eid='" . mres($eid) . "'", $db);
							
							if (NLDate2Epoch(FormattedDateToNLDate($value)) < strtotime(date('Y-m-d'))) {
								$color = "redunderline";
							} elseif (FormattedDateToNLDate($value) == date('d-m-Y')) {
								$color = "red";
							} else { 
								$color = "normal";
							}

						} else {
							$value = $cur['duedate'];
						}
					break;
					case "startdate":
						if (is_numeric(NLDate2Epoch(FormattedDateToNLDate($value)))) {
							mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET startdate='" . mres(FormattedDateToNLDate($value)) . "' WHERE eid='" . mres($eid) . "'", $db);
							if (NLDate2Epoch(FormattedDateToNLDate($value)) > strtotime(date('Y-m-d'))) {
								$color = "green";
							} else { 
								$color = "normal";
							}
							$value = htme($value);
						} else {
							$value = $cur['startdate'];
						}
					break;

				}

				if ($field == "duedate" || $field == "startdate") {
					ProcessTriggers($field . "_change",$eid,FormattedDateToNLDate($origvalue),false,false);
				} else {
					ProcessTriggers($field,$eid,$origvalue,false,false);
				}
				
				journal($eid, "Field " . $field . " updated from [" . $cur[$field] . "] to [" . $origvalue . "]");

				
			}

			if ($table == "entity") {
				ProcessTriggers("entity_change", $eid, "", false, false);
				CalculateComputedExtraFields($eid);
				ExpireFormCache($eid, "Interactive list item update", "entity", false);
			} elseif ($table == "customer") {
				ProcessTriggers("customer_change", $eid, "", false, false);
				CalculateComputedExtraCustomerFields($eid);
				ExpireFormCache($eid, "Interactive list item update", "customer", false);
			} else {
				ProcessTriggers("FlexTable" . $flextable . "-Change", $eid, "", false, $flextable);
				CalculateComputedFlextableFields($flextable, $eid);
				ExpireFormCache($eid, "Interactive list item update", $flextable, false);
			}

			
			

			$new = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . $table . " WHERE " . $id . "='" . mres($eid) . "'");

			$changed = "";

			// Check differences
			if ($field == "CRMcustomer") {
				$field = "customer";
			}
			$locfield = $field;
			if (is_numeric($locfield)) {
				$locfield = "EFID" . $locfield;
			}
			

			foreach ($cur AS $key => $old_value) {

				if ($old_value != $new[$key]) {
					if ($key != "content" && $key != $locfield && !is_numeric($key)) {
						if ($key == "deleted" && $new[$key] == "y") {
							$value = "deleted";
						} else {
							$loccol = "";
							

							if ($key == "status") $loccol = GetStatusColor($new[$key]);
							if ($key == "priority") $loccol = GetPriorityColor($new[$key]);
							if ($key == "duedate") {
								if (NLDate2Epoch(FormattedDateToNLDate($new[$key])) < strtotime(date('Y-m-d'))) {
									$loccol = "redunderline";
								} elseif (FormattedDateToNLDate($new[$key]) == date('d-m-Y')) {
									$loccol = "red";
								} else { 
									$loccol = "normal";
								}
							}
							if ($key == "startdate") {
								if (NLDate2Epoch(FormattedDateToNLDate($new[$key])) > strtotime(date('Y-m-d'))) {
									$loccol = "green";
								} else { 
									$loccol = "normal";
								}

							}
							$stripkey = str_replace("EFID","", $key);

							if (is_numeric($stripkey)) {
								//$loccol = GetExtraFieldColor($stripkey, $new[$key]);
								$dspval = GetExtraFieldValue($new[$id], $stripkey, true, false, $new[$key]);
							} else {
								$dspval = $new[$key];
								
							}

							

							$changed .= $stripkey . "%%%" . $dspval . "%%%" . $loccol . "$$$";
						}
					}
				} else {
					//$changed .= $key . "=" . $new[$i] . " key " . KeyName($cur, $i) . "|||";
				}

			}


		} else {
			$value = $value . " [change access denied: $acc record: $eid field: $field value: $value]";
			$color = "redunderline";
		}

		qlog(INFO, "Response to update-field request: " . $field . "|||" . $eid . "|||" . htme($value) . "|||" . $color . "|||" . $changed);
		print $field . "|||" . $eid . "|||" . htme($value) . "|||" . $color . "|||" . $changed;
		
		$ok = true;

} elseif (!$_REQUEST['efid']) {
	$ret .= "|||error";
} else {
	

	$ef	= str_replace("EFID", "", $_REQUEST['efid']);

	if (GetExtraFieldTableType($ef) == "entity") {
			$eft = GetExtraFields($ef);
			$eid = $_REQUEST['eidcid'];
			$cid = GetEntityCustomer($eid);
	} elseif (GetExtraFieldTableType($ef) == "customer") {
			$eft = GetExtraCustomerFields($ef, true);
			$cid = $_REQUEST['eidcid'];
			$eid = "";
	} elseif (is_numeric(GetExtraFieldTableType($ef))) {
			$tablenum = GetExtraFieldTableType($ef);
			$recordid = $_REQUEST['eidcid'];
			$eft = GetExtraFlexTableFields($tablenum, $ef);
	}

	if (substr($eft[0]['options'], 0, 18) == "%POPULATE_BY_CODE%" && $eft[0]['fieldtype'] == "drop-down") { // Drop down populated by code
		$code = str_replace("%POPULATE_BY_CODE%", "", $eft[0]['options']);
		if (IsValidEID($eid)) {
			$code = ParseTemplateEntity($code, $eid, true, false, false, "plain");
		}
		if (IsValidCID($cid)) {
			$code = ParseTemplateEntity($code, $eid, true, false, false, "plain");
		}
		if (IsValidFlexTableRecord($recordid, $tablenum)) {
			$code = ParseFlexTableTemplate($tablenum, $recordid, $code,	false, true, true, "plain");
		}
		$code = ParseTemplateCleanUp($code);
		@eval($code);
		foreach ($result AS $row) {
			if (trim(strip_tags($row)) != "") {
				if (str_replace("+", " ", $row) == trim($_REQUEST['EFID' . $ef])) {
						$row = "{selected}" . $row;
				} else {
//					print " $row IS NOTEQUAL TO " . $_REQUEST['EFID' . $ef] . "\n";
				}
				if ($nf) {
					$ret .= "|||";
				}
				$ret .= strip_tags($row);
				$nf = true;

			}
		}
	} elseif ($eft[0]['fieldtype'] == "Computation (ajax autorefresh)") {
		
		$result = "";

		$code = ParseTemplateAliases($eft[0]['options']);
		if (IsValidEID($eid)) {
			$code = ParseTemplateEntity($code, $eid, true, false, false, "plain");
		}
		if (IsValidCID($cid)) {
			$code = ParseTemplateCustomer($code, $eid, true, "plain", false);
		}

		if (IsValidFlexTableRecord($recordid, $tablenum)) {
			$code = ParseFlexTableTemplate($tablenum, $recordid, $code, false, true, true, "plain");
		} 
		@eval($code);

		$ret = $result;

		if ($_REQUEST['type'] == "entity" && IsValidEID($eid) && CheckEntityAccess($eid) == "ok") {
			SetExtraFieldValueSimple($ef, $eid, $ret);
		} elseif ($_REQUEST['type'] == "customer" && IsValidCID($cid) && CheckCustomerAccess($cid) == "ok") {
			SetExtraCustomerFieldValueSimple($ef, $cid, $ret);
		} elseif (substr($_REQUEST['type'],0,9) == "flextable" && IsValidFlexTableRecord($recordid, $tablenum) && CheckFlextableRecordAccess($tablenum, $recordid) == "ok") {
			SetExtraFlextableFieldValueSimple($ef, $recordid, $tablenum, $ret);
		} else {
			
		}

		if (GetAttribute("extrafield", "ComputationOutputType", $ef) == "Numeric" && is_numeric($ret)) {
			$ret = FormatNumber($ret, false, $ef, true);
		}
		
	} elseif ($eft[0]['fieldtype'] == "comment") {

		$fileid = $eft[0]['options'];
		if (is_numeric($fileid) && $fileid!=0 && $fileid!="") {

				$template = ParseTemplateAliases(GetTemplate($fileid));

				preg_match_all('/(#|@)[A-Za-z0-9_]+(#|@)/', $template, $matches);
				$list_of_tags = $matches[0];

			// This must be done before all tags are replaced
				if ($GLOBALS['USECUSTOMERSELECTPOPUP'] != "Yes") {
					if (in_array("#CUSTOMER#", $list_of_tags))	$template = str_replace('#CSBOX#',ReturnDropDownSearchField("JS_customer"), $template);
				}
				if (!in_array("NoOwnNoAssign",$cl) && !in_array("CannotChangeOwner",$cl)) {
					if (in_array("#OWNER#", $list_of_tags))		$template = str_replace('#OSBOX#',ReturnDropDownSearchField("JS_owner"), $template);
				}
				if (!in_array("NoOwnNoAssign",$cl) && !in_array("CannotChangeAssignee",$cl)) {
					if (in_array("#ASSIGNEE#", $list_of_tags))	$template = str_replace('#ASBOX#',ReturnDropDownSearchField("JS_assignee"), $template);
				}

				if (in_array("#STATUS#", $list_of_tags))		$template = str_replace('#SSBOX#',ReturnDropDownSearchField("JS_status"), $template);
				if (in_array("#PRIORITY#", $list_of_tags))	$template = str_replace('#PSBOX#',ReturnDropDownSearchField("JS_priority"), $template);

				if ($eid == "_new_") {
					$template = StripExistingOnlyTags($template);
				} else {
					$template = StripNewOnlyTags($template);
				}

				if ($readonly) {
					$template = StripRWOnlyTags($template);
				} else {
					$template = StripROOnlyTags($template);
				}
				
				if ($recordid == "_new_" || IsValidFlexTableRecord($recordid, $tablenum)) {

					$template = ParseFlexTableTemplate($tablenum, $recordid, $template, false, true, true, "plain");

					$tmp = GetExtraFlexTableFields($tablenum, false, true);
					foreach ($tmp AS $field) {
						if (in_array("#EFID" . $field['id'] . "#", $list_of_tags)) {
							$template = str_replace("#EFID" . $field['id'] . "#", GetSingleExtraFieldFormBox($recordid,$field['id'],$readonly,false, $directives), $template);
						}
					}
					$template = EvaluateTemplatePHP($template, false, $tablenum, $recordid);

				}
				
				if (IsValidEID($eid) || $eid == "_new") {
					$template = ParseTemplateEntity($template, $eid, false, false, false, "plain");

					$tmp = GetExtraFields(false, true);
					foreach ($tmp AS $field) {
						if (in_array("#EFID" . $field['id'] . "#", $list_of_tags)) {
							$template = str_replace("#EFID" . $field['id'] . "#", GetSingleExtraFieldFormBox($eid,$field['id'],$readonly,false, $directives), $template);
						}
					}
					$template = EvaluateTemplatePHP($template, $eid, false, false);

				} else {

				}
				
				if (IsValidCID($cid) || $cid == "_new") {
					$tmp = GetExtraCustomerFields(false, true);
					foreach ($tmp AS $field) {
						if (in_array("#EFID" . $field['id'] . "#", $list_of_tags)) {
							$template = str_replace("#EFID" . $field['id'] . "#", GetSingleExtraFieldFormBox($cid,$field['id'],$readonly,false, $directives), $template);
						}
					}


					$template = ParseTemplateCustomer($template, $cid, true, "plain", false);
					$template = EvaluateTemplatePHP($template, false, false, false);
				}
				
				

				$ret = ParseTemplateCleanUp(html_compress($template));

		}

	} else {
		$ret .= "|||error 2";
	}

}
if (!$ok) {
	//if ($ret == "") $ret = "[error/nok]";
	print $ef . "|||" . $ret . "";
	qlog(INFO, "Response: " . $ef . "|||" . $ret);
}
EndHTML(false);
?>