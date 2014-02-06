<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
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
require_once("initiate.php");

if ($_REQUEST['CheckCustomer']) {
	
	$_REQUEST['nonavbar'] = 1;
	ShowHeaders();
	print "<table cellspacing='0' cellpadding='4' border='1' width='90%'>";
	$sql = "select id,custname from " . $GLOBALS['TBL_PREFIX'] . "customer WHERE SOUNDEX('" . mres($_REQUEST['CheckCustomer']) . "')=SOUNDEX(custname)";
	//qlog(INFO, $sql);
	$result = mcq($sql,$db);
	while ($row= mysql_fetch_array($result)) {
		$ins .= "<tr><td>" . $row['id'] . "</td><td>" . $row['custname'] . "</td></tr>";
	}
	$a = mysql_affected_rows();
	if ($a>0) {
		print "<tr><td colspan='2'>Interleave Thinks this customer already exists in your database.<br><br>The following similar customers were found:</td></tr>";
		print $ins;
	} else {
		print "<tr><td colspan='2'>Interleave doesn't think this customer already exists in your database.";
	}
	print "</table>";
	EndHTML();

} elseif ($_REQUEST['pdf']) {

	require("createcustomerpdf.php");
	exit;

} elseif ($_REQUEST['ActivityCustomerGraph']) {

	DisplayCustomerActivityGraph($ActivityCustomerGraph);
	EndHTML(false);

} elseif (strtoupper($GLOBALS['UC']['HIDECUSTOMERTAB'])=="YES" && !is_administrator()  && !$_REQUEST['ShowInlineSelectTable']) {

	ShowHeaders();
	PrintAD("Access to this page is denied");
	EndHTML();

} elseif ($_REQUEST['deleteconfirm']) {

	if (CheckCustomerAccess($_REQUEST['deleteconfirm']) == "ok") {

		// Count the no. of entities still bound to this customer
		$count = db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE CRMcustomer='" . mres($_REQUEST['deleteconfirm']) . "'");
		
		
		if ($count>0) {
			// There are some, so the customer shall not be deleted 

			print $lang['custdelexplain'];
			log_msg("Someone tried to delete a customer with entities: denied (tampered!)","");
			PrintAD("You cannot delete customer with coupled entities");


		} else {
			
			// No entities found bound to this customer, destroy it

			$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "customer WHERE id='" . mres($_REQUEST['deleteconfirm']) . "'";
			mcq($sql,$db);
			$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "journal WHERE eid='" . mres($_REQUEST['deleteconfirm']) . "' AND type='customer'";
			mcq($sql,$db);

			$sql = "DELETE " . $GLOBALS['TBL_PREFIX'] . "binfiles.*, " . $GLOBALS['TBL_PREFIX'] . "blobs.* FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles, " . $GLOBALS['TBL_PREFIX'] . "blobs WHERE " . $GLOBALS['TBL_PREFIX'] . "binfiles.fileid=" . $GLOBALS['TBL_PREFIX'] . "blobs.fileid AND " . $GLOBALS['TBL_PREFIX'] . "binfiles.type='cust' AND " . $GLOBALS['TBL_PREFIX'] . "binfiles.koppelid='" . mres($_REQUEST['deleteconfirm']) . "'";

			mcq($sql, $db);
			log_msg("Entry " . $_REQUEST['deleteconfirm'] . " deleted from customer table","");

		}
	}

	header("Location: index.php?ShowCustomerList");

} elseif ($_REQUEST['delete']) {

	ShowHeaders();

	$count = db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE CRMcustomer='" . mres($_REQUEST['delid']) . "'");

	if (CheckCustomerAccess($_REQUEST['delid']) <> "ok") {

		PrintAD("Access to this page is denied");

	} else {

		if ($count>0) {
		
			print $lang['custdelexplain'];
			log_msg("Someone tried to delete a customer with entities: denied","");
			PrintAD("You cannot delete customer with coupled entities");

		} else {

			print "<br><form id='delconf' method='post' action=''><div class='showinline'>";
			print $lang['pbdelconf'] . "<br><br>";
			print $lang['customer'] . ": " .  GetCustomerName($_REQUEST['delid']);
			
			print "<br><br><input type='hidden' name='deleteconfirm' value='" . htme($_REQUEST['delid']) . "'><input type='submit' name='knoppie' value='" . $lang['deletepb'] . "'></div></form>";

		}
	}

} else {

	ShowHeaders();
	
	
	if ($_REQUEST['addfilled'] || $_REQUEST['editfilled']) {

			$returntolist = true;

			if ($_REQUEST['addfilled']) {

				if (CheckCustomerAccess("_new_") == "ok") {
					
					$unique = $_REQUEST['hash'];
					$recentlyadded = db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "cache WHERE value='" . mres($unique) . "'");
					// Create new (and empty) customer record here
					if ($recentlyadded > 0 && ($GLOBALS['CHECKFORDOUBLEADDS'] == "Yes")) {
						qlog(INFO, "CheckExistence:: The same - " . $unique . " NOT SAVING THIS");
						print "<span class=\"noway\">Avoided adding the same record twice.</span>";
						$_REQUEST['editfilled'] = $cid;
					} else {
						if ($GLOBALS['CHECKFORDOUBLEADDS'] == "Yes")  {	// do not push this value if it is disabled
							PushStashValue($unique);
						} else {
							qlog(INFO, "Flextable double-add check is disabled! (not saving MD5)");
						}
					
						mcq("INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "customer(active) VALUES('yes')", $db);
						$cid = mysql_insert_id ();
						journal($cid,"Customer added", "customer");
						$GLOBALS['PageCache']['CheckedCustomerAccessArray'][$cid] = "ok";
						ClearAllRunningCache();
						$added_record = true;
						$_REQUEST['editfilled'] = $cid;
						AddDefaultExtraCustomerFields($cid);
					}

				} else {
					PrintAD("You're not allowed to add customers!");
					$_REQUEST['editfilled'] = "_new_";
				}



			}
				
			if ($_REQUEST['editfilled'] && !$fconfirmed) {
				// First, collect extra fields list

				if (CheckCustomerAccess($_REQUEST['editfilled']) <> "ok") {
					PrintAD("Editing this customer is not allowed for you");
					$returntolist = false;
				} else {
					$gh = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "customer WHERE id='" . mres($_REQUEST['editfilled']) . "'");
					$gh_orig = $gh;
					if (($gh['customer_owner'] <> $GLOBALS['USERID']) && (!is_administrator()) && ($gh['readonly']=='yes')) {
						PrintAD("Access to this page is denied");
						log_msg("WARNING: Somebody tried a direct post to adjust customer dossier " . $_REQUEST['editfilled'],"");
					} else {
						$eid = $_REQUEST['editfilled'];
						$list = GetExtraCustomerFields();

						foreach ($list AS $extrafield) {
							$varname = "EFID" . $extrafield['id'];

							if (isset($_REQUEST[$varname])) {
									qlog(INFO, "Found extra field " . $_REQUEST[$varname] . ": " . $varname);

									$sql = "SELECT EFID" . $extrafield['id'] . " FROM " . $GLOBALS['TBL_PREFIX'] . "customer WHERE id='" . mres($eid) . "'";
									$result = mcq($sql,$db);
									$gh = mysql_fetch_array($result);
									$value = $gh[0];

									if (strstr(GetExtraFieldType($extrafield['id']), "multiselect") && isset($_REQUEST[$varname]) && count($_REQUEST[$varname]) == 1 && $_REQUEST[$varname][0] == "{{{null}}}") {
										$_REQUEST[$varname] = array();
									}

									if (is_array($_REQUEST[$varname])) {
										qlog(WARNING, "WARNING - THIS IS AN EXTRA ARRAY FIELD!");
										$tmp = array();
										foreach($_REQUEST[$varname] AS $row) {
											if ($row <> "" && $row != "{{{null}}}") {
												array_push($tmp, base64_encode($row));
											}
										}
										$_REQUEST[$varname] = serialize($tmp);
									}
									if ($extrafield['fieldtype'] == "date" && $_REQUEST[$varname] != "") {
										$_REQUEST[$varname] = FormattedDateToNLDate($_REQUEST[$varname]);
									} elseif ($extrafield['fieldtype'] == "date/time") {
										$_REQUEST[$varname] = FormattedDateTimeToSQLDateTime($_REQUEST[$varname]);
										if ($_REQUEST[$varname] == "") {
											$_REQUEST[$varname] = "0000-00-00 00:00:00";
										}
									}

									if ($added_record && $extrafield['fieldtype'] == "diary" && $_REQUEST[$varname] != "") {
										$_REQUEST[$varname] = serialize(array(array(date('U'), $GLOBALS['USERID'], $_REQUEST[$varname])));
									} elseif ($extrafield['fieldtype'] == "diary" && $_REQUEST[$varname] != "") {
										UpdateDiaryField($eid, $extrafield['id'], $dummy, $_REQUEST[$varname], $_REQUEST['commenthash']);
										continue;
									} elseif ($extrafield['fieldtype'] == "diary") {
										continue;
									}

									if (isset($_REQUEST[$varname]) && ValidateFieldInput(str_replace("EFID", "", $varname), $_REQUEST[$varname], false,true,$eid) != $_REQUEST[$varname]) {
										print "Input check failed for $efield_varname. Reverting to old value.<br>";
										log_msg("ERROR: Input check failed for $varname; " . $_REQUEST[$varname] . " didn't validate. Reverting to old value. Reason: " . ValidateFieldInput(str_replace("EFID", "", $varname), $_REQUEST[$varname], false,true,$eid));
										$_REQUEST[$varname] = $value;
									}


									if ($value != "") {
											qlog(INFO, "Found earlier value for extra field " . $varname . " (" . $value . ")");
											if ($value != $_REQUEST[$varname] && $_REQUEST[$varname] != "") {
												$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "customer SET EFID" . $extrafield['id'] . "='" . mres($_REQUEST[$varname]) . "' WHERE id='" . mres($eid)  . "'";
												$add_to_journal .= "\n" . CleanExtraFieldName($extrafield['name']) . " updated from [" . $value . "] to [" . mres($_REQUEST[$varname]) . "]";
												DataJournal($eid, $value, $_REQUEST[$varname], $extrafield['id'], "customer");
												qlog(INFO, "NOT Cathing emptied extra customer field (1)");
											} elseif ($value . " " == $_REQUEST[$varname] . " ") {
												qlog(INFO, "Not saving extra field " . $varname . " - same value");
											} elseif (isset($_REQUEST[$varname]) && $_REQUEST[$varname] == "") {
												qlog(INFO, "Cathing emptied extra customer field");
												$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "customer SET EFID" . $extrafield['id'] . "='' WHERE id='" . mres($eid)  . "'";
												mcq($sql, $db);
												DataJournal($eid, $value, "", $extrafield['id'], "customer");
											} else {
												qlog(INFO, "Dunno what to do with extra field " . $varname);
												log_msg("ERROR: don't know what to do with extra field " . $varname);
											}

									} elseif ($_REQUEST[$varname] != "") {
												qlog(INFO, "This is a new extra customer field value (2)");
												$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "customer SET EFID" . $extrafield['id'] . "='" . mres($_REQUEST[$varname]) . "' WHERE id='" . mres($eid)  . "'";
												$add_to_journal .= "\nEFID" . $extrafield['id'] . " updated from [] to [" . $_REQUEST[$varname] . "]";
									}
									// And finally, execute the statement.
									mcq($sql,$db);

								} else {
									qlog(INFO, "Extra customer field " . $varname . " not found in REQUEST");
								}
						}
					
						$add_to_journal = "Customer " . $eid . " edited\n" . $add_to_journal;
						
						if ($_REQUEST['activesubmitted'] == 1 && !isset($_REQUEST['activenew'])) {
							$_REQUEST['activenew'] = "no";
						} elseif ($_REQUEST['activesubmitted'] == 1) {
							$_REQUEST['activenew'] = "yes";
						}
						$fields = array("custnamenew","contactnew","contact_titlenew","contact_phonenew","cust_homepagenew","cust_addressnew","cust_remarksnew","contact_emailnew","activenew","email_owner_upon_adds","customer_ownernew","readonlycust");

						foreach ($fields AS $field) {
							if (isset($_REQUEST[$field])) {
								qlog(INFO, "$field is submitted!");
								$dbfield = str_replace("new", "", $field);
								$dbfield = str_replace("readonlycust","readonly",$dbfield);

								if ($gh_orig [$dbfield] != $_REQUEST[$field]) {
									$sql_ins .= ", " . str_replace("new","",str_replace("readonlycust","readonly",$field)) . "='" . mres($_REQUEST[$field]) . "'";
									$add_to_journal .= "\n" . $field . " from [" . $gh_orig [$dbfield] . "] to [" . $_REQUEST[$field] . "]";
									DataJournal($eid, $gh_orig [$dbfield], $_REQUEST[$field], $dbfield, "customer");
								}
								
							}

						}

						journal($_REQUEST['editfilled'], $add_to_journal,"customer");
						$GLOBALS['ChangeLogLastSave'] = $add_to_journal;
						$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "customer SET id=id " . $sql_ins . " WHERE id='" . mres($eid) . "'";
					
						mcq($sql,$db);
						log_msg("Customer " . $_REQUEST['editfilled'] . " edited","");
						FindAndRecalculateAllRelatedRecords($_REQUEST['editfilled'], "customer");
						
						CalculateComputedExtraCustomerFields($eid);
						if ($added_record) {
							ProcessTriggers("customer_add", $_REQUEST['editfilled'], "", false, false);
						} else {
							ProcessTriggers("customer_change", $_REQUEST['editfilled'], "", false, false);
						}
						// Clear the access cache tables
						//ClearAccessCache($_REQUEST['editfilled'],'c');
						ExpireFormCache($_REQUEST['editfilled'], "Customer save", "customer", false);
						CleanUpCacheTablesAfterSave();
			//			print $sql;

						$det = 1;
						$c_id = $_REQUEST['editfilled'];
						$saved = true;
					}
				}
			}


		if ($_REQUEST['close_on_next_load'] ) {
		// In this case the last update window was loaded in a pop window. Now the form is
		// submitted, the window may close itself
					?>
					<script type="text/javascript">
					<!--
						parent.refresh_<?php echo $_REQUEST['ParentAjaxHandler'];?>();
						parent.$.fancybox.close();
					//-->
					</script>
					<?php
		} elseif ($_REQUEST['ShowInlineAddScreen'] == "CloseOnNextLoad") {
					?>
					<script type="text/javascript">
					<!--
						PutCustomerInEntityForm('<?php echo htme($_REQUEST['SelectField']);?>','<?php echo htme($_REQUEST['ShowField']);?>', <?php echo $eid;?>,'<?php echo htme($_REQUEST['custnamenew']);?>');
					//-->
					</script>
					<?php 
		} elseif ($_REQUEST['fromlist'] && $_REQUEST['editfilled'] != "" && !$stayhere) {
		?>
			<script type="text/javascript">
			<!--
				document.location='<?php echo base64_decode($_REQUEST['fromlist']);?>';
			//-->
			</script>
			<?php

		}
	} 
	
	if ($_REQUEST['e_button']) {
		$x = GetAllButtons($_REQUEST['e_button']);

		if ($x[0]['fieldtype'] == "Button") {
				// So, a button was pressed (and the user has the rights to press it)
				qlog(INFO, "An extra field button was pressed. Processing triggers.");
				journal($_REQUEST['editfilled'], "User pressed button " . $x[0]['id'] . "::" . $x[0]['name'], "customer");
//		function ProcessTriggers($onchange,$eid,$to_value,$log=false,$flextableid=false, $customer_trigger=false) {
				ProcessTriggers("ButtonPress" . $_REQUEST['e_button'],$_REQUEST['editfilled'],"", false, false, true);
				$tmp = GetAttribute("extrafield", "BackToListAfterSave", $x[0]['id']);
				if ($tmp == "No") {
					$returntolist = false;
					$_REQUEST['editcust'] = $_REQUEST['editfilled'];
					$_REQUEST['custid'] = $_REQUEST['editfilled'];
				} elseif ($tmp == "Yes") {
					$returntolist = true;
				} 
			}
	}

	if ($_REQUEST['fromlist'] && $_REQUEST['editfilled'] != '' && !$stayhere) {
		?>
			<script type="text/javascript">
			<!--
				document.location='<?php echo base64_decode($_REQUEST['fromlist']);?>';
			//-->
			</script>
			<?php
	}

	if (($_REQUEST['add'] || $_REQUEST['editcust']) && !$returntolist) {

		if (!$_REQUEST['custid']) $_REQUEST['custid'] = "_new_";

		if (CheckCustomerAccess($_REQUEST['custid'])=="readonly") {
			$readonly = true;
			$roins = "disabled='disabled'";
			$formaction = "index.php?logout=1";
		} elseif (CheckCustomerAccess($_REQUEST['custid'])<>"ok") {
			PrintAD("You are not authorized to open this record (U" . $_REQUEST['custid'] . "::RET" . CheckCustomerAccess($_REQUEST['custid']) . ")");
			$noaccess = true;
		} else {
			$formaction = "customers.php";
			$readonly = false;
		}

		if (!$noaccess) {

			if (!is_numeric($GLOBALS['CUSTOMCUSTOMERFORM'])) {
				$tmp = db_GetFlatArray("SELECT templateid FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML_CFORM'");
				if (count($tmp) > 1) {
					qlog(ERROR, "No configured customer form found, but multiple templates found. Guessing.");
				} else {
					qlog(WARNING, "No configured customer form found, but one template found. Correcting.");
				}
				$GLOBALS['CUSTOMCUSTOMERFORM'] = $tmp[0];
			} else {

			}

			if (is_numeric($GLOBALS['CUSTOMCUSTOMERFORM'])) {
				if ($_REQUEST['add']) {
					$c_add = "YES";
				}
				print html_compress(CustomCustomerForm($GLOBALS['CUSTOMCUSTOMERFORM'], $_REQUEST['custid'], $c_add));
			} else {
				PrintAD("No valid form found. Please set a customer form in the global CUSTOMCUSTOMERFORM variable");
			}
			
			$returntolist = false;
		}

	} else { // end if editcust
		$returntolist = true;
	}
	if ($returntolist) {
		print AjaxBox("ShowCustomerList", true, "&BrowseArray=" . $GLOBALS['BrowseArray'] . $uri);
	}
	
	EndHTML(true);
}