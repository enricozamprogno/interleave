<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file enables users to import into tables
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */

require("initiate.php");

ShowHeaders();



print "<form id=\"importForm\" method=\"post\" action=\"\">";

if (!$_REQUEST['Worker'] && !$_REQUEST['tableId']) { // No session exists, create one and ask some questions

	$worker = PushStashValue(array("table" => "unknown"));

	
	if (is_administrator()) {
		$ft = GetFlextableDefinitions();
		$tables = array("entity" => $lang['entities'], "customer" => $lang['customers']);
		foreach ($ft AS $f) {
			$tables[$f['recordid']] = $f['tablename'];
		}
		$tables['users'] = "Users";
		$set = true;
	} else {
		$tables = array();
		$set = false;
		if (CheckFunctionAccess("AllowedToImportEntities") == "ok") {
			$tables['entity'] = $lang['entities'];
			$set = true;
		}
		if (CheckFunctionAccess("AllowedToImportCustomers") == "ok") {
			$tables['customer'] = $lang['customers'];
			$set = true;
		}
		foreach (GetFlextableDefinitions() AS $ft) {
			$configname = "AllowedToImportFT" . $ft['recordid'];
			if (CheckFunctionAccess($configname) == "ok") {
				$tables[$ft['recordid']] = $ft['tablename'];
				$set = true;
			}
		}
		if (CheckFunctionAccess("AllowedToImportUsers") == "ok") {
			$tables['users'] = "Users";
			$set = true;
		}
			
	}

	if ($set) {
		if (count($tables) > 1) {
			print "Choose table to update or to import into: ";
			print "<select name=\"tableId\" id=\"JS_table\">";
			foreach ($tables AS $table => $name) {
				print "<option value=\"" . $table . "\">" . htme($name) . "</option>";
			}
			print "</select>";
		} else {
			
		}
	} else {
		PrintAD("You're not allowed to use this functionality");
		$error = true;
	}
} else {
	if ($_REQUEST['tableId'] && !$_REQUEST['Worker']) {
		$worker = PushStashValue(array("table" => $_REQUEST['Table']));
		// print "Created worker $worker";
	} else {
		$worker = $_REQUEST['Worker'];
	}

	$mem = PopStashValue($worker);

	if ($mem['table'] && $mem['table'] != "unknown" && !CheckIfUserIsAllowedToImportInTable($mem['table'])) {
		
		PrintAD("You're not allowed to use this functionality");
		$error = true;

	} else {

		if ($_REQUEST['tableId'] && !$_POST['importBody'] && !isset($_POST['Matched'])) {
			$mem['table'] = $_REQUEST['tableId'];
			if ($mem['table'] && !CheckIfUserIsAllowedToImportInTable($mem['table'])) {
				PrintAD("You're not allowed to use this functionality");
				$error = true;
			} else {
				if ($mem['table'] == "entity" && GetAttribute("system", "EntityImportTableHTML", 2) != "" && GetAttribute("system", "EntityImportTableHTML", 2) != "{{none}}") {
					print GetAttribute("system", "EntityImportTableHTML", 2);
				} elseif ($mem['table'] == "customer" && GetAttribute("system", "CustomerImportTableHTML", 2) != "" && GetAttribute("system", "CustomerImportTableHTML", 2) != "{{none}}") {
					print GetAttribute("system", "CustomerImportTableHTML", 2);
				} elseif ($mem['table'] == "users" && GetAttribute("system", "UserImportTableHTML", 2) != "" && GetAttribute("system", "UserImportTableHTML", 2) != "{{none}}") {
					print GetAttribute("system", "UserImportTableHTML", 2);
				} else {
					print "Paste values from excel or writer: (tab-delimited):<br>";
					print "<p>A few rules:</p>";
					print "<ul><li>Cell data should not contain a TAB-character</li>";
					print "<li>When cell data contains newline characters (enter), you should add a last column to your sheet with all cells (including header) having value [[LASTCELL]]</li>";
					print "</ul>";
				}
				print "<textarea name=\"importBody\" id=\"JS_importBody\" cols=\"100\" rows=\"4\"></textarea><br>";
			}
		} elseif ($_POST['importBody']) {
			
			$mem['rawData'] = $_POST['importBody'];
			$mem['arrayData'] = array();
			if (strstr($mem['rawData'], "[[LASTCELL]]")) {
				$delim = "\t[[LASTCELL]]";
			} else {
				$delim = "\n";
			}
			foreach (explode($delim, $mem['rawData']) AS $row) {
				$result = explode("\t", $row);
				array_walk($result, 'trim');
				$mem['arrayData'][] = $result;
			}
			$mem['rawData'] = "";
			$mem['headerData'] = $mem['arrayData'][0];

			
			if ($mem['table'] == "entity") {
				$fields = array("none" => "none/ignore", "category" => $lang['category'], "status" => $lang['status'], "priority" => $lang['priority'], "duedate" => $lang['duedate'], "startdate" => $lang['startdate'], "owner" => $lang['owner'], "assignee" => $lang['assignee'], "content" => "Main text contents");

				if (db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity") > 0) { // Only if there are entities
					$fields['updatEntity'] = "Map to current " . $lang['entity'] . ", update instead of import";
				}
				if (db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "customer") > 1) { // Only if there is more than 1 customer
					$fields['refercustomer'] = "Reference to customer";
				}

				$extrafields = db_GetArray("SELECT CONCAT('EFID', id) AS id, name FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE (fieldtype IN ('textbox', 'drop-down', 'numeric', 'mail', 'text area', 'text area (rich text)', 'hyperlink', 'date', 'checkbox', 'diary', 'date/time', 'Reference to FlexTable', 'Computation', 'Computation (ajax autorefresh)')  OR fieldtype LIKE '[copyfield%') AND deleted='n' AND tabletype='entity' AND SUBSTR(options, 1, 18) != '%POPULATE_BY_CODE%' ORDER BY ordering");
				
				foreach ($extrafields AS $row) {
					$fields[$row['id']] = $row['name'];
				}
				foreach ($extrafields AS $row) {
					$fields["updateEntity-" . $row['id']] = "Map to " . $row['name'] . ", update instead of import";
				}
				$mem['SQLtable'] = $GLOBALS['TBL_PREFIX'] . "entity";
				
		
			} elseif ($mem['table'] == "customer") {
				$fields = array("none" => "none/ignore", "updateCustomer" => "Map to current " . $lang['customer'] . ", update instead of import","custname" => $lang['customer'], "contact" => $lang['contact'], "contact_title" => $lang['contacttitle'], "contact_phone" => $lang['contactphone'], "contact_email" => $lang['contactemail'], "cust_address" => $lang['customeraddress'], "cust_remarks" => $lang['custremarks'], "cust_homepage" => $lang['custhomepage'], "active" => "active");
				$extrafields = db_GetArray("SELECT CONCAT('EFID', id) AS id, name FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE (fieldtype IN ('textbox', 'drop-down', 'numeric', 'mail', 'text area', 'text area (rich text)', 'hyperlink', 'date', 'checkbox', 'diary', 'date/time', 'Reference to FlexTable', 'Computation', 'Computation (ajax autorefresh)')  OR fieldtype LIKE '[copyfield%') AND deleted='n' AND tabletype='customer' AND SUBSTR(options, 1, 18) != '%POPULATE_BY_CODE%' ORDER BY ordering");
				
				foreach ($extrafields AS $row) {
					$fields[$row['id']] = $row['name'];
				}
				foreach ($extrafields AS $row) {
					$fields["updateCustomer-" . $row['id']] = "Map to " . $row['name'] . ", update instead of import";
				}
				$mem['SQLtable'] = $GLOBALS['TBL_PREFIX'] . "customer";

			} elseif (is_numeric($mem['table'])) {
				$fields = array("none" => "none/ignore");
				$tab = GetFlextableDefinitions($mem['table']);
				$tab = $tab[0];
				$fields = array("none" => "none/ignore", "updateFlexRecord" => "Map to current record-id, update instead of import");
				if ($tab['orientation'] == "one_entity_to_many") {
					if ($tab['refers_to'] == "entity") {
						$fields["refer"] = "reference to " . $lang['entity'] . " table";
					} elseif ($tab['refers_to'] == "customer") {
						$fields["refer"] = "reference to " . $lang['customer'] . " table";
					} elseif (substr($tab['refers_to'], 0, 9) == "flextable") {
						$fields["refer"] = "reference to table " . GetFlexTableName(str_replace("flextable", "", $tab['refers_to']));
					}
					$textadd = " Please note: matching one of the fields as refer field is mandatory!";
				}
				$extrafields = db_GetArray("SELECT CONCAT('EFID', id) AS id, name FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE (fieldtype IN ('textbox', 'drop-down', 'numeric', 'mail', 'text area', 'text area (rich text)', 'hyperlink', 'date', 'checkbox', 'diary', 'date/time', 'Reference to FlexTable', 'Computation', 'Computation (ajax autorefresh)')  OR fieldtype LIKE '[copyfield%' OR fieldtype LIKE 'User-%') AND deleted='n' AND tabletype='" . $mem['table'] . "' AND SUBSTR(options, 1, 18) != '%POPULATE_BY_CODE%' ORDER BY ordering");
				
				
				//$fields['refer'] = "refer-field";
				foreach ($extrafields AS $row) {
					$fields[$row['id']] = $row['name'];
				}
				foreach ($extrafields AS $row) {
					$fields["updateFlexRecord-" . $row['id']] = "Map to " . $row['name'] . ", update instead of import";
				}
				$mem['SQLtable'] = $GLOBALS['TBL_PREFIX'] . "flextable" . $mem['table'];

			} elseif ($mem['table'] == "users") {
				$fields = array("none" => "none/ignore", "name" => "Account name", "FULLNAME" => "Full name", "PASSWORD" => "Password", "PROFILE" => "Group ID", "EMAIL" => "E-mail address");

				$mem['SQLtable'] = $GLOBALS['TBL_PREFIX'] . "loginusers";
			}
			
			if (count($mem['arrayData']) > 1) {
				print "Received a sheet with " . (count($mem['arrayData']) - 1) . " rows. The first line will be interpreted as header line and thus be ignored while importing!" . $textadd . "<br><br>";
			
				
			
				$_POST['importBody'] = "";


				print "<table class=\"crm-nomax\"><thead><tr><td>Column from import</td><td>matches field</td><td>Search</td><td>Sheet validates?</td></tr></thead>";
				$headerId = 0;
				$js_add = "";
				foreach ($mem['headerData'] AS $headerCell) {

					$matchArray = "<select name=\"matchArray" . $headerId . "\" id=\"JS_matchArray" . $headerId . "\" onchange=\"refresh_AjaxDivHeader" . $headerId . "('&SelectedField=' + document.getElementById('JS_matchArray" . $headerId . "').options[document.getElementById('JS_matchArray" . $headerId . "').selectedIndex].value);\">";
					
					foreach ($fields AS $fieldname => $desc) {
						if (trim($headerCell) == trim($desc)) {
							$ins = " selected=\"selected\"";
							$js_add .= "refresh_AjaxDivHeader" . $headerId . "('&SelectedField=' + document.getElementById('JS_matchArray" . $headerId . "').options[document.getElementById('JS_matchArray" . $headerId . "').selectedIndex].value);\n";
						} else {
							$ins = "";
						}
						$matchArray .= "<option " . $ins . " value=\"" . $fieldname . "\">" . htme($desc) . "</option>";
					}
					$matchArray .= "</select></td><td>" . ReturnDropDownSearchField("JS_matchArray" . $headerId . "");

					print "<tr><td>" . htme($headerCell) . "</td><td>" . $matchArray . "</td><td>";


					print AjaxBox("ValidateInput", true, "&Worker=" . $worker . "&Header=" . ($headerId) . "&Table=" . $mem['table'], false, "AjaxDivHeader" . $headerId);
					print "</td></tr>";
					$headerId++;
				}
				print "</table><p>";
				print "<p id=\"kick_recalc\"><input type=\"checkbox\" name=\"kick_recalculate\" value=\"true\" checked=\"checked\"> Immediately recalculate after add or update (when appliccable)</p>";
				print "<p id=\"kick_update\"><input type=\"checkbox\" name=\"kick_trigger\" value=\"true\" checked=\"checked\"> Kick record_update and/or record_add trigger.</p>";
				if ($tab['refers_to'] == "customer") {
					print "<p id=\"autoinsert_customers\"><input type=\"checkbox\" name=\"autoinsert_customers\" value=\"true\"> Auto-insert unknown customers (customer name only).</p>";
					$mem['reffing_to_customer'] = true;
				}
				print "<br><br>When you click \"go\" you will actually import or update. Use with care!<br><br>";
				print "<input type=\"hidden\" name=\"Matched\" value=\"true\"></p>";
			} else {
				print "<p>Only one row found. Since the first row must be the header row, this sheet effectively contains zero rows. Quitting.</p>";
				$error = true;
			}

		} elseif ($_POST['Matched'] == "true") {
			$counter =0;
			foreach ($mem['headerData'] AS $headerCell) {
				// print "Match: " . $headerCell . " to " . $_POST['matchArray' . $counter] . "<br>";
				$mem['headerMatchArray'][trim($headerCell)] = trim($_POST['matchArray' . $counter]);

				if (substr($_POST['matchArray' . $counter], 0, 5) == "refer" && !$refers_to) {
					//$refers_to = str_replace("refer", "", $_POST['matchArray' . $counter]);
					$refers_to = true;
				}
				$counter++;
				$name = trim($headerCell);
				if (substr($mem['headerMatchArray'][$name],0,12) == "updateEntity" || substr($mem['headerMatchArray'][$name],0,14) == "updateCustomer" || substr($mem['headerMatchArray'][$name],0,16) == "updateFlexRecord") {
					$upd=true;
				}
			}

			$error = false;
			$errors = "";

			if (is_numeric($mem['table']) && !$refers_to) {
				 if (!$upd) {
					$tab = GetFlextableDefinitions($mem['table']);
					$tab = $tab[0];
					if ($tab['orientation'] == "one_entity_to_many") {
						$errors .= "<span class=\"noway\">A ";
						if ($tab['refers_to'] == "entity") {
							$errors .= "reference to " . $lang['entity'] . " table";
							$error = true;
						} elseif ($tab['refers_to'] == "customer") {
							$errors .= "reference to " . $lang['customer'] . " table";
							$error = true;
						} elseif (substr($tab['refers_to'], 0, 9) == "flextable") {
							$errors .= "reference to table " . GetFlexTableName(str_replace("flextable", "", $tab['refers_to']));
						}

						$errors .= " must be made.</span> <br><br>";
						
					}
				}
			}


			// Double-field check
			$done = array();
			foreach ($mem['headerMatchArray'] AS $header => $destination) {
				if (in_array($destination, $done) && $destination != "none") {
					$error = true;
					$errors .= "<span class=\"noway\">Field \"" . htme($header) . "\" was mapped to field \"" . htme(GetExtraFieldName(str_replace("EFID", "", $destination))) . "\" but another field was already mapped to this destination.</span><br><br>";
				}
				$done[] = $destination;
			}

			$mem['sql'] = array();

			if (!$error) {
				$errors = "<table class=\"crm\"><thead><tr><td>Field</td><td>Destination</td><td>Type</td><td>Input value</td><td>Row</td><td>Cell</td><td>Remarks</td></tr></thead>";

				$rowcounter = 1;
				
				foreach ($mem['arrayData'] AS $row) {
						
					

					if (count($row) == count($mem['headerMatchArray']) && $row != $mem['headerData']) {

						
			
						$tmpsql = "INSERT INTO " . $mem['SQLtable'] . "(";
						$tmpsql_update_front = "UPDATE " . $mem['SQLtable'] . " SET ";
						$tmpsql_update_args = array();

						$nf = false;
						foreach ($mem['headerMatchArray'] AS $header => $destination) {
							if ($destination != "none") {
								if ($nf) $tmpsql .= ",";
								if ($destination == "refercustomer") {
									$destination = "CRMcustomer";
								}
								$tmpsql .= $destination;
								$nf = true;
								if (substr($destination,0,12) == "updateEntity" || substr($destination,0,14) == "updateCustomer" || substr($destination,0,16) == "updateFlexRecord") {
									$update_row = true;
								}
							}
						}
						if (!in_array("refercustomer", $mem['headerMatchArray']) && $mem['table'] == "entity" && db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "customer") == 1) {
							$tmpsql .= ",CRMcustomer";
						}
						if (!in_array("assignee", $mem['headerMatchArray']) && $mem['table'] == "entity") {
							$tmpsql .= ",assignee";
						}
						if (!in_array("owner", $mem['headerMatchArray']) && $mem['table'] == "entity") {
							$tmpsql .= ",owner";
						}
						$tmpsql .= ") VALUES(";
						$nf = false;
						$cnt = 0;
						foreach ($row AS $origdata) {
							$name = trim($mem['headerData'][$cnt]);
							if ($name == "refercustomer") {
									$name = "CRMcustomer";
							}
							$field = str_replace("EFID", "", $mem['headerMatchArray'][$name]);
							$cnt++;
							if ($mem['headerMatchArray'][$name] != "none") {
								$data = ValidateFieldInput($field, trim($origdata), $mem['table']);
								if (substr($data,0,10) == "{{{nok}}}}" && $field == "refer" && $mem['reffing_to_customer'] && $_REQUEST['autoinsert_customers'] == "true") {
									$sql = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "customer(custname) VALUES('" . htme(trim($origdata)) . "')";
									mcq($sql, $db);
									
									print "Customer " . htme($origdata) . " added prior to import.<br>";

									$data = mysql_insert_id();
								} elseif ($field == "refer" && $mem['reffing_to_customer'] && $_REQUEST['autoinsert_customers'] == "true") {
									// print "Customer " . $origdata . " already known in database: $data<br>";
								} 

								if (GetExtraFieldType($field) == "diary") {
									$tmp = explode("\n", $data);
									$data = array();
									foreach ($tmp AS $el) {
										if (trim($el) != "") {
											$data[] = array(date('U'), $GLOBALS['USERID'], $el);
										}
									}
									$data = serialize($data);
									
								} elseif (GetExtraFieldType($field) == "date/time") {
									$data = FormattedDateTimeToSQLDateTime($data);
								}
								if (substr($data,0,10) != "{{{nok}}}}") {
									if ($nf) $tmpsql .= ",";
									$tmpsql .= "'" . mres($data) . "'";

									if ($update_row) {
										if (substr($mem['headerMatchArray'][$name],0,12) == "updateEntity") {

											if ($mem['headerMatchArray'][$name] == "updateEntity" && IsValidEID($data)) {
												$tmpsql_update_back = " WHERE " . $GLOBALS['TBL_PREFIX'] . "entity.eid = '" . mres($data) . "'";
											} else {
												$to_field = str_replace("updateEntity-", "", $mem['headerMatchArray'][$name]);
												$tmpsql_update_back = " WHERE " . $GLOBALS['TBL_PREFIX'] . "entity." . $to_field . " = '" . mres($data) . "'";
											}

										} elseif (substr($mem['headerMatchArray'][$name],0,14) == "updateCustomer") {
											if ($mem['headerMatchArray'][$name] == "updateCustomer" && IsValidCID($data)) {
												$tmpsql_update_back = " WHERE " . $GLOBALS['TBL_PREFIX'] . "customer.id = '" . mres($data) . "'";;
											} else {
												$to_field = str_replace("updateCustomer-", "", $mem['headerMatchArray'][$name]);
												$tmpsql_update_back = " WHERE " . $GLOBALS['TBL_PREFIX'] . "customer." . $to_field . " = '" . mres($data) . "'";
											}


										} elseif (substr($mem['headerMatchArray'][$name],0,16) == "updateFlexRecord") {

											if ($mem['headerMatchArray'][$name] == "updateFlexRecord" && IsValidFlextableRecord($data, $mem['table'])) {
												
												$tmpsql_update_back = " WHERE " . $GLOBALS['TBL_PREFIX'] . "flextable" . $mem['table'] . ".recordid = '" . mres($data) . "'";

											} else {
												$to_field = str_replace("updateFlexRecord-", "", $mem['headerMatchArray'][$name]);
												$tmpsql_update_back = " WHERE " . $GLOBALS['TBL_PREFIX'] . "flextable" . $mem['table'] . "." . $to_field . " = '" . mres($data) . "'";

											}

										} else {


										
											$tmpsql_update_args[] = $mem['headerMatchArray'][$name] . " = '" . mres($data) . "'";
										}
									}
									$nf = true;
								} else {
									$error = true;
									if (substr($field, 0, 5) == "refer") {
										$ref = str_replace("refer", "", $field);
										if ($ref == "entity") {
											$displayfield = "reference to " . $lang['entity'] . " table";
										} elseif ($ref == "customer") {
											$displayfield = "reference to " . $lang['customer'] . " table";
										} elseif (is_numeric($ref)) {
											$displayfield = "reference to table " . GetFlexTableName($ref);
										} 
										$type = "refer";
									} else {
										$displayfield = $field;
										$type = GetExtraFieldType($field);
									}
									$errors .= "<tr><td>" . $name . "</td><td>" . GetExtraFieldName($displayfield) . "</td><td>" . $type . "</td><td>\"" . htme(trim($origdata)) .  "\" </td><td>" . $rowcounter . "</td><td>" . $cnt . "</td><td>";
									if (GetExtraFieldType($field) == "drop-down") {
										$errors .= " Valid values for this field are: ";
										foreach (GetExtraFieldOptions($field) AS $option) {
											$errors .= htme($option) . " ";
										}
									} elseif ($field == "customer") {
										$errors .= "Field must contain either a complete valid customer name or a valid customer id";
									} elseif ($field == "owner" || $field == "assignee") {
										$errors .= "Field must contain either a complete valid user login name, a user's full name or a valid user id";
									} elseif (GetExtraFieldType($field) == "numeric") {
										$errors .= "Field must be numeric";
									} elseif (GetExtraFieldType($field) == "mail") {
										$errors .= "Field must contain a valid email address";
									} elseif (GetExtraFieldType($field) == "date") {
										$errors .= "Field must contain a valid date in format: " . $GLOBALS['UC']['DateFormat'];
									} elseif (substr($field, 0, 5) == "refer") {
										$refers_to = str_replace("refer", "", $field);
										if (is_numeric($refers_to)) {
											$errors .= "Field must contain a valid record id of a record in table " . $refers_to . ": " . GetFlextableName($refers_to);
										} elseif ($refers_to == "entity") {
											$errors .= "Field must contain a valid entity id";
										} elseif ($refers_to == "customer") {
											$errors .= "Field must contain a valid record id of a " . $lang['customer'] . " or a valid " . $lang['custname'];
										}
									} else {
										$errors .= "unknown error.";
									}
									$errors .= "</td></tr>";
								}
							} else {
								// print "skip";
							}

						}
						if (!in_array("refercustomer", $mem['headerMatchArray']) && $mem['table'] == "entity" && db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "customer") == 1) {
								$tmpsql .= "," . db_GetValue("SELECT id FROM " . $GLOBALS['TBL_PREFIX'] . "customer");
						}
						if (!in_array("assignee", $mem['headerMatchArray']) && $mem['table'] == "entity") {
							$tmpsql .= "," . $GLOBALS['USERID'];
						}
						if (!in_array("owner", $mem['headerMatchArray']) && $mem['table'] == "entity") {
							$tmpsql .= "," . $GLOBALS['USERID'];
						}
						$tmpsql .= ")";
						$nnf = false;
						if ($update_row) { 
							$tmp = $tmpsql_update_front;
							foreach ($tmpsql_update_args AS $ar) {
								if ($nnf) $tmp .= ",";
								$tmp .= $ar;
								$nnf = true;
							}
							$tmp .= $tmpsql_update_back;
							if ($nnf) {
								$mem['sql'][] = $tmp;
							}
						} else {
							$mem['sql'][] = $tmpsql;
						}
						
					} else {
						// skip!
						//print "skip";
						//print "<tr><td colspan='7'>Incorrect row element length: " . count($row) . " != " . count($mem['headerMatchArray']) . "</td><td>";
						//$errors .= "<tr><td colspan='7'>Incorrect row element length: " . count($row) . " != " . count($mem['headerMatchArray']) . "</td><td>";
						//$error = true;
					}
					$rowcounter++;
				}
				$errors .= "</table>";
			}


			if ($error) {
				print "This data cannot be imported because errors were encountered:<br><br>" . $errors;
			} else {
				$GLOBALS['CRON_RUNNING'] = true;
				$sqlc = 0;
				print "Number of generated queries: " . count($mem['sql']) . "<br>";
				foreach ($mem['sql'] AS $sql) {
					mcq($sql, $db); 
					// print $sql . "<br>";
					$new_id = mysql_insert_id();
					

					$sqlc+= mysql_affected_rows($db);
					
					// Create default fields and kick triggers
					
					if ($update_row) {
						// triggering not supported
					} else {
						if ($mem['table'] == "entity") {
							array_push($GLOBALS['PageCache']['ValidEIDs'], $new_id);
							
							AddDefaultExtraFields($new_id);

							
							if ($_REQUEST['kick_recalculate'] == "true") CalculateComputedExtraFields($new_id, false);
							if ($_REQUEST['kick_trigger'] == "true") ProcessTriggers("entity_add",$new_id,"",false, false);
							if ($_REQUEST['kick_recalculate'] == "true") CalculateComputedExtraFields($new_id, false);
							journal($new_id, "Entity added by automated import done by " . $GLOBALS['USERNAME'], "entity");

						} elseif ($mem['table'] == "customer") {

							array_push($GLOBALS['PageCache']['ValidCIDs'], $new_id);
							AddDefaultExtraCustomerFields($new_id);


							if ($_REQUEST['kick_recalculate'] == "true") CalculateComputedExtraCustomerFields($new_id);
							if ($_REQUEST['kick_trigger'] == "true") ProcessTriggers("customer_add",$new_id,"",false, false);
							if ($_REQUEST['kick_recalculate'] == "true") CalculateComputedExtraCustomerFields($new_id);

							journal($new_id, "Customer added by automated import done by " . $GLOBALS['USERNAME'], "cust");

						} elseif (is_numeric($mem['table'])) {

							AddDefaultExtraFlexTableFields($mem['table'], $new_id);
							if ($_REQUEST['kick_recalculate'] == "true") CalculateComputedFlextableFields($mem['table'], $new_id);
							if ($_REQUEST['kick_trigger'] == "true") ProcessTriggers("FlexTable" . $mem['table'] . "-Add",$new_id,"",false,$mem['table']);
							if ($_REQUEST['kick_recalculate'] == "true") CalculateComputedFlextableFields($mem['table'], $new_id);

						} elseif ($mem['table'] == "users") {

							mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "loginusers SET `PASSWORD`=PASSWORD(`PASSWORD`), FORCEPASSCHANGE='y' WHERE id=" . $new_id, $db);
							journal($new_id, "User added by automated import done by " . $GLOBALS['USERNAME'], "user");

						}
						
					}
				}
				if ($update_row) {
					print "Done; " . $sqlc . " records updated.";
				} else {
					print "Done; " . $sqlc . " records imported.";
				}
				$error = true;
			}
		}

		
	}
}
UpdateStashvalue($worker, $mem);
print "<input type=\"hidden\" name=\"Worker\" value=\"" . $worker . "\">";
if (!$error) {
	print "<input type=\"submit\" name=\"submitButton\" value=\"" . $lang['go'] . "\">";
}
print "</form>";
if ($js_add != "") {
	print "<script type=\"text/javascript\">\n";
	print "<!--\n";
	print "var t = setTimeout('StartInit()', 1500);\n";
	print "function StartInit() {\n";
	print "alert('This procedure will now try to validate each auto-matched field.');\n";
	print $js_add;
	print "}\n";
	print "//-->\n";
	print "</script>\n";
}
EndHTML();

function CheckIfUserIsAllowedToImportInTable($tableid) {
	if (is_administrator()) {
		return true;
	} elseif ($tableid == "entity" && CheckFunctionAccess("AllowedToImportEntities") == "ok") {
		return(true);
	} elseif ($tableid == "customer" && CheckFunctionAccess("AllowedToImportCustomers") == "ok") {
		return(true);
	} elseif ($tableid == "users" && CheckFunctionAccess("AllowedToImportUsers") == "ok") {
		return(true);
	} elseif (is_numeric($tableid) &&CheckFunctionAccess("AllowedToImportFT" . $tableid) == "ok") {
		return(true);
	} else {
		return(false);
	}
}
?>