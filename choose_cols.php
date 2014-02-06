<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 info@interleave.nl
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * Allows the admin to change the columns shown in the main list
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
require_once("initiate.php");
if ($_REQUEST['dothis'] <> "global" && $_REQUEST['dothis'] <> "global_shortlist" && !$_REQUEST['cur']) {
	$_REQUEST['nonavbar'] = 1;
}
ShowHeaders();
//print "<pre>";
//print_r($_REQUEST);

$what = $_REQUEST['what'];

if (is_administrator() && $_REQUEST['dothis']<>"personal" && $_REQUEST['dothis']<>"profile") {
	AdminTabs();
	MainAdminTabs("sysman");
}
AddBreadCrum("List layout");

if ($_REQUEST['BaseList'] == "customer") {
	$_REQUEST['what'] = "CUST";
} elseif (substr($_REQUEST['BaseList'],0,9) == "flextable") {
	$_REQUEST['dothis'] == "flextable";
	$_REQUEST['flextable'] = str_replace("flextable", "", $_REQUEST['BaseList']);
}


if ($_REQUEST['dothis']=="global" && !is_administrator()) {
		// security
		PrintAdminError();
		EndHTML();
		exit;
}
$cl = GetClearanceLevel();

if (!is_administrator() && !in_array("MaySelectColumns", $cl) && $_REQUEST['DownloadSpreadSheetStashId']=='') {
		// security
		PrintAD("Access to this functionality not allowed.");
		EndHTML();
		exit;
}
if (IsValidFlexTable($_REQUEST['flextable'])) {
		$_REQUEST['dothis'] = "flextable";
		$tableprop = GetFlexTableDefinitions($_REQUEST['flextable']);
		if ($tableprop[0]['orientation'] == "one_entity_to_many") {
			if ($tableprop[0]['refers_to'] == "entity") {
				$also_show = "entity";
			} elseif ($tableprop[0]['refers_to'] == "customer") {
				$also_show = "customer";
			} elseif (substr($tableprop[0]['refers_to'],0,9) == "flextable") {
				$also_show = str_replace("flextable", "", $tableprop[0]['refer']);
			} else {
				$also_show = "";
			}

		}
} elseif (is_administrator() && !$_REQUEST['dothis'] && $_REQUEST['what']!="CUST" && !$_REQUEST['flextable']) {
		$legend = "?&nbsp;";
		$printbox_size = "30%";
		printbox("<strong>Please choose...</strong><br><br><a class='plainlink' href='choose_cols.php?dothis=personal'>Edit your <span class='underln'>personal</span> preference</a><br>&nbsp;&nbsp;<br><a class='plainlink' href='choose_cols.php?dothis=global'>Edit the <span class='underln'>global</span> settings</a>");
		EndHTML();
		exit;
} elseif (is_administrator() && $_REQUEST['dothis']) {
		//$_REQUEST['dothis'] = $_REQUEST['dothis'];
} elseif (in_array("MaySelectColumns", $cl)) {
		$_REQUEST['dothis'] = "personal";

} elseif (is_administrator()) {
		$_REQUEST['dothis'] = "global";
}
if ($_REQUEST['dothis']=="global" && !$what) {
	print "<table width='30%'><tr><td>&nbsp;</td><td>";

	print "<br>Which list do you like to configure?<br><br><a class='plainlink' href='choose_cols.php?global=1&amp;dothis=global&amp;what=ML'>The entity lists</a><br><a class='plainlink' href='choose_cols.php?global=1&amp;dothis=global&amp;what=CUST&amp;dothis=global'>The customer list</a>";
	print "<br><a class='plainlink' href='choose_cols.php?global=1&amp;dothis=global_shortlist'>The short lists (dashboard overdue and recently-accessed lists)</a>";
	print "</td></tr></table>";
	EndHTML();
	exit;
} elseif ($_REQUEST['what'] == "CUST")  {
	$origlist = $GLOBALS['UC']['CustomerListColumnsToShow'];
	if ($_REQUEST['CustomColumnOverrule'] != "" && $_REQUEST['CustomColumnOverrule'] != "none") {

		$myname = $_REQUEST['CustomColumnOverrule'];
		$OKlan = $myname;
		if (is_array($GLOBALS['UC']['CustomerListColumnsToShow'][$_REQUEST['CustomColumnOverrule']])) {
			$GLOBALS['UC']['CustomerListColumnsToShow'] = $GLOBALS['UC']['CustomerListColumnsToShow'][$_REQUEST['CustomColumnOverrule']];
		}
		
		$form_ins = "<input type='hidden' name='CustomColumnOverrule' value='" . $_REQUEST['CustomColumnOverrule'] . "'>";
		if ($GLOBALS['UC']['CustomerListColumnsToShow']['NoPersonalOverrule']) {
			PrintAD("You're not allowed to alter this list (1)");
			EndHTML();
			exit();
		}
	} else {
		//$OKlan = $lang['briefover'] . ", " . strtolower($lang['delentities']) . ", " . strtolower($lang['viewinsertedentities']) . " layout &nbsp;<span class='noway'>(personal setting)</span>&nbsp;";
		$OKlan = $lang['customers'];
	}
	$start = "<h1>" . $OKlan . "</h1>";
	if (is_numeric($_REQUEST['DownloadSpreadSheetStashId'])) {
		$start .= "<h2>" . $lang['selectfields'] . "</h2>"; 
	} elseif ($_REQUEST['dothis'] == "global") {
		$start .= "<h2>Global customer list setting</h2>"; 
		$GLOBALS['UC']['CustomerListColumnsToShow'] = GetSetting('CustomerListColumnsToShow');
	} else {
		$start .= "<h2>" . $lang['personallistsettingcolumnstosh'] . "</h2>"; 
	}


		/*
		  `customer_owner` int(11) NOT NULL default '0',
		  `email_owner_upon_adds` enum('no','yes') NOT NULL default 'no',
		*/
		if ($_REQUEST['form_sub']) {
			$GLOBALS['UC']['CustomerListColumnsToShow'] = array();
			$GLOBALS['UC']['CustomerListColumnsToShow']['id'] = true;

			if ($_REQUEST['cb_custname']) {
				$GLOBALS['UC']['CustomerListColumnsToShow']['cb_custname'] = true;
			}
			if ($_REQUEST['cb_contact']) {
				$GLOBALS['UC']['CustomerListColumnsToShow']['cb_contact'] = true;
			}
			if ($_REQUEST['cb_contact_title']) {
				$GLOBALS['UC']['CustomerListColumnsToShow']['cb_contact_title'] = true;
			}
			if ($_REQUEST['cb_contact_phone']) {
				$GLOBALS['UC']['CustomerListColumnsToShow']['cb_contact_phone'] = true;
			}
			if ($_REQUEST['cb_contact_email']) {
				$GLOBALS['UC']['CustomerListColumnsToShow']['cb_contact_email'] = true;
			}
			if ($_REQUEST['cb_cust_address']) {
				$GLOBALS['UC']['CustomerListColumnsToShow']['cb_cust_address'] = true;
			}
			if ($_REQUEST['cb_cust_remarks']) {
				$GLOBALS['UC']['CustomerListColumnsToShow']['cb_cust_remarks'] = true;
			}
			if ($_REQUEST['cb_cust_homepage']) {
				$GLOBALS['UC']['CustomerListColumnsToShow']['cb_cust_homepage'] = true;
			}
			if ($_REQUEST['cb_active']) {
				$GLOBALS['UC']['CustomerListColumnsToShow']['cb_active'] = true;
			}
			if ($_REQUEST['cb_owner']) {
				$GLOBALS['UC']['CustomerListColumnsToShow']['cb_owner'] = true;
			}


			$list = GetExtraCustomerFields();
			foreach ($list AS $field) {
				if ($field['fieldtype'] <> "List of values" && $field['fieldtype'] <> "text area" && $field['fieldtype'] <> "text area (rich text)" && $field['underwaterfield'] != 'y') {
					$varname = "EFID" . $field['id'];
					if ($_REQUEST[$varname]) {
						$GLOBALS['UC']['CustomerListColumnsToShow'][$varname] = true;
					}
				}
			}
			
			if ($_REQUEST['CustomColumnOverrule'] != "" && $_REQUEST['CustomColumnOverrule'] != "none") {
				$origlist[$_REQUEST['CustomColumnOverrule']] = $GLOBALS['UC']['CustomerListColumnsToShow'];
				$GLOBALS['UC']['CustomerListColumnsToShow'] = $origlist;
			}


			if ($_REQUEST['dothis']=="global") {
				MustBeAdmin();
				$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value='" . mres(serialize($GLOBALS['UC']['CustomerListColumnsToShow'])) . "' WHERE setting='CustomerListColumnsToShow'";
				mcq($sql,$db);
			
			} elseif ($_REQUEST['dothis']=="personal") {
				mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "loginusers SET CLISTLAYOUT='" . mres(serialize($GLOBALS['UC']['CustomerListColumnsToShow'])) . "' WHERE id=" . $GLOBALS['USERID'], $db);
			} else {

				print "<img src='images/error.gif' alt=''>&nbsp;&nbsp;&nbsp;Error encountered (1)<br>";
				print "</div></body></html>";
				exit;
			}
			unset($GLOBALS['UC']['CustomerListColumnsToShow']);
			print "<table><tr><td>Values are being saved ...</td></tr></table>";
			if ($_REQUEST['cur']) {
					$_REQUEST['cur'] = base64_decode($_REQUEST['cur']);
			?>
				<script type="text/javascript">
				<!--
					document.location = '<?php echo $_REQUEST['cur'] . "&" . $epoch;?>';
				//-->
				</script>
			<?php
			} elseif ($_REQUEST['dothis'] == "global") {
				?>
				<script type="text/javascript">
				<!--
					document.location = 'choose_cols.php?dothis=global';
				//-->
				</script>
			<?php
			} elseif ($_REQUEST['DownloadSpreadSheetStashId']) {
				if ($_REQUEST['BaseList'] == "entity") {
					$call = "DlSs";
				} elseif ($_REQUEST['BaseList'] == "customer") {
					$call = "DlSsC";
				}
			?>
				<script type="text/javascript">
				<!--
					parent.document.location = <?php print "'csv.php?" . $call . "&CustomColumnLayoutStash=" . PushStashValue($origlist[$_REQUEST['CustomColumnOverrule']]) . "&EaCSV=" . $_REQUEST['EaCSV'] . "&QiD=" . $_REQUEST['DownloadSpreadSheetStashId'] . "&separator=RealExcel';";
					?>
					parent.$.fancybox.close();
				//-->
				</script>
			<?php
			
			} else {
			?>
				<script type="text/javascript">
				<!--
					parent.refresh_<?php echo $_REQUEST['ParentAjaxHandler'];?>();
					parent.$.fancybox.close();
				//-->
				</script>
			<?php
			}
			print "</div></body></html>";
			exit;
		}
		$OKlan = $lang['briefover'] . ", " . strtolower($lang['delentities']) . ", " . strtolower($lang['viewinsertedentities']) . " layout";

		if ($_REQUEST['dothis']=="global") {
			$legend = "<img src='images/error.gif' alt=''>&nbsp;";
			$printbox_size = "100%";


			$sql= "SELECT value FROM " . $GLOBALS['TBL_PREFIX'] . "settings WHERE setting='CustomerListColumnsToShow'";
			$result= mcq($sql,$db);
			$resarr=mysql_fetch_array($result);
			$GLOBALS['UC']['CustomerListColumnsToShow'] = unserialize($resarr[0]);

		} elseif ($_REQUEST['dothis']=="personal") {


		} elseif ($_REQUEST['dothis']=="group") {

			//$start = "Select visible " . strtolower($lang['customer']) . " columns &nbsp;<span class='noway'>(profile setting)</span>&nbsp;";

		} else {
			PrintAD("Error (2)");
			EndHTML();
			exit;
		}
		print "<form id='choose_colums' method='post' action=''><div class='showinline'>";
		print $form_ins;
		print $start;

		print "<table class='crm'>";
//HIER
		print "<thead><tr><td>" . $lang['name'] . "</td><td><div><input type=\"checkbox\" class=\"checkall\"> [" . $lang['all'] . "]</div></td></tr></thead>";
		print "<tr><td>id</td><td>[always]<input type='hidden' name='form_sub' value='1'><input type='hidden' name='cur' value='" . htme($_REQUEST['cur']) . "'><input type='hidden' name='dothis' value='" . $_REQUEST['dothis'] . "'></td></tr>";
		if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_custname']) {
			$a = "checked='checked'";
		} else {
			unset($a);
		}
		print "<tr><td>" . $lang['customer'] .   "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_custname'></td></tr>";
		if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_contact']) {
			$a = "checked='checked'";
		} else {
			unset($a);
		}

		print "<tr><td>" . $lang['contact'] .      "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_contact'></td></tr>";
		if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_contact_title']) {
			$a = "checked='checked'";
		} else {
			unset($a);
		}
		print "<tr><td>" . $lang['contacttitle'] .   "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_contact_title'></td></tr>";
		if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_contact_phone']) {
			$a = "checked='checked'";
		} else {
			unset($a);
		}
		print "<tr><td>" . $lang['contactphone'] .     "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_contact_phone'></td></tr>";
		if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_contact_email']) {
			$a = "checked='checked'";
		} else {
			unset($a);
		}
		print "<tr><td>" . $lang['contactemail'] .   "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_contact_email'></td></tr>";
		if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_cust_address']) {
			$a = "checked='checked'";
		} else {
			unset($a);
		}
		print "<tr><td>" . $lang['customeraddress'] .   "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_cust_address'></td></tr>";
		if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_cust_remarks']) {
			$a = "checked='checked'";
		} else {
			unset($a);
		}
		print "<tr><td>" . $lang['custremarks'] .    "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_cust_remarks'></td></tr>";
		if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_cust_homepage']) {
			$a = "checked='checked'";
		} else {
			unset($a);
		}
		print "<tr><td>" . $lang['custhomepage'] .  "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_cust_homepage'></td></tr>";
		if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_active']) {
			$a = "checked='checked'";
		} else {
			unset($a);
		}
		print "<tr><td>Active</td><td><input type='checkbox' " . $a . " class='radio' name='cb_active'></td></tr>";
		
		if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_owner']) {
			$a = "checked='checked'";
		} else {
			unset($a);
		}
		
		
		print "<tr><td>" . $lang['owner'] . "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_owner'></td></tr>";

		$list = GetExtraCustomerFields();

		foreach ($list AS $field) {
				$varname = "EFID" . $field['id'];
				if ($field['fieldtype'] <> "List of values" && $field['underwaterfield'] != 'y') {
					if ($GLOBALS['UC']['CustomerListColumnsToShow'][$varname]) {
						$a = "checked='checked'";
					} else {
						$a = "";
					}
					$cf .= "<tr><td>" . $field['displaylistname'] . "</td><td><input type='checkbox' " . $a . " class='radio' name='EFID" . $field['id'] . "'></td></tr>";
				}
		}


		print $cf;
		print "<tr><td></td><td align='right'>";
		if (is_numeric($_REQUEST['DownloadSpreadSheetStashId'])) {
			print "<input type='hidden' name='DownloadSpreadSheetStashId' value='" . htme($_REQUEST['DownloadSpreadSheetStashId']) . "'>";
			print "<input type='hidden' name='BaseList' value='" . htme($_REQUEST['BaseList']) . "'>";
			print "Export as tab-delimited instead of Excel: <input type='checkbox' name='EaCSV' id='JS_EaCSV'  value='1'>";
			
			print "<input type='submit' name='whatever' value='" . $lang['downloadexport'] . "'></td></tr>";
		} else {
			print "<input type='hidden' name='what' value='CUST'><input type='submit' name='whatever' value='" . $lang['save'] . "'></td></tr>";
		}
		
		print "</table>";
		print "</div></form>";
	EndHTML();
	exit;
}
if ($_REQUEST['form_sub']) {
	$MainListColumnsToShowNew = array();


	if ($_REQUEST['cb_cust']) {
		$MainListColumnsToShowNew['cb_cust'] = true;
	}
	if ($_REQUEST['cb_owner']) {
		$MainListColumnsToShowNew['cb_owner'] = true;
	}
	if ($_REQUEST['cb_assignee']) {
		$MainListColumnsToShowNew['cb_assignee'] = true;
	}
	if ($_REQUEST['cb_ownergroup']) {
		$MainListColumnsToShowNew['cb_ownergroup'] = true;
	}
	if ($_REQUEST['cb_assigneegroup']) {
		$MainListColumnsToShowNew['cb_assigneegroup'] = true;
	}

	if ($_REQUEST['cb_status']) {
		$MainListColumnsToShowNew['cb_status'] = true;
	}
	if ($_REQUEST['cb_priority']) {
		$MainListColumnsToShowNew['cb_priority'] = true;
	}
	if ($_REQUEST['cb_category']) {
		$MainListColumnsToShowNew['cb_category'] = true;
	}
	if ($_REQUEST['cb_contents']) {
		$MainListColumnsToShowNew['cb_contents'] = true;
	}

	if ($_REQUEST['cb_duedate']) {
		$MainListColumnsToShowNew['cb_duedate'] = true;
	}
	if ($_REQUEST['cb_startdate']) {
		$MainListColumnsToShowNew['cb_startdate'] = true;
	}
	if ($_REQUEST['cb_lastupdate']) {
		$MainListColumnsToShowNew['cb_lastupdate'] = true;
	}
	if ($_REQUEST['cb_duration']) {
		$MainListColumnsToShowNew['cb_duration'] = true;
	}
	if ($_REQUEST['cb_creationdate']) {
		$MainListColumnsToShowNew['cb_creationdate'] = true;
	}
	if ($_REQUEST['cb_closedate']) {
		$MainListColumnsToShowNew['cb_closedate'] = true;
	}
	if ($_REQUEST['cb_numofattachments']) {
		$MainListColumnsToShowNew['cb_numofattachments'] = true;
	}

// CUSTOMER FIELDS FROM HERE
	if ($_REQUEST['dothis'] != "global_shortlist") {
		if ($_REQUEST['cb_contact']) {
			$MainListColumnsToShowNew['cb_contact'] = true;
		}
		if ($_REQUEST['cb_contact_title']) {
			$MainListColumnsToShowNew['cb_contact_title'] = true;
		}
		if ($_REQUEST['cb_contact_phone']) {
			$MainListColumnsToShowNew['cb_contact_phone'] = true;
		}
		if ($_REQUEST['cb_contact_email']) {
			$MainListColumnsToShowNew['cb_contact_email'] = true;
		}
		if ($_REQUEST['cb_cust_address']) {
			$MainListColumnsToShowNew['cb_cust_address'] = true;
		}
		if ($_REQUEST['cb_cust_remarks']) {
			$MainListColumnsToShowNew['cb_cust_remarks'] = true;
		}
		if ($_REQUEST['cb_cust_homepage']) {
			$MainListColumnsToShowNew['cb_cust_homepage'] = true;
		}
	}
	if ($_REQUEST['NoPersonalOverrule']) {
		$MainListColumnsToShowNew['NoPersonalOverrule'] = true;
	}

	$list = GetExtraFields();
	foreach ($list AS $field) {
		$varname = "EFID" . $field['id'];
		if ($_REQUEST[$varname]) {
			$MainListColumnsToShowNew[$varname] = true;
		}
	}

	$list = GetExtraCustomerFields();
	foreach ($list AS $field) {
		$varname = "EFID" . $field['id'];
		if ($_REQUEST[$varname]) {
			$MainListColumnsToShowNew[$varname] = true;
		}
	}

	$fts = GetFlexTableDefinitions(false,"many-to-one", false, "entity");
	foreach ($fts AS $ft) {
		$list = GetExtraFlexTableFields($ft['recordid'], false, false);
		foreach ($list AS $field) {
			$varname = "EFID" . $field['id'];
			if ($_REQUEST[$varname]) {
				$MainListColumnsToShowNew[$varname] = true;
			}
		}
	}

	$fts = GetFlexTableDefinitions(false,"one-to-many", false, "entity");
	foreach ($fts AS $ft) {
		$list = GetExtraFlexTableFields($ft['recordid'], false, false);
		if ($_REQUEST["SUMFT" . $ft['recordid']]) {
			$MainListColumnsToShowNew["SUMFT" . $ft['recordid']] = true;
		}
		foreach ($list AS $field) {
			$varname = "EFID" . $field['id'];
			if ($_REQUEST[$varname]) {
				$MainListColumnsToShowNew[$varname] = true;
			}
			if ($_REQUEST["SUM" . $varname]) {
				$MainListColumnsToShowNew["SUM" . $varname] = true;
			}
		}
	}



	if (sizeof($MainListColumnsToShowNew) > 0) {
		if ($_REQUEST['dothis'] != "global_shortlist") {
			$MainListColumnsToShowNew['id'] = true;
		}
		$MainListColumnsToShowNew = serialize($MainListColumnsToShowNew);
	} else {
		$MainListColumnsToShowNew = "";
	}

	if ($_REQUEST['dothis']=="global") {
		$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value='" . mres($MainListColumnsToShowNew) . "' WHERE setting='MainListColumnsToShow'";
		mcq($sql,$db);
	} elseif ($_REQUEST['dothis']=="global_shortlist") {
		$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value='" . mres($MainListColumnsToShowNew) . "' WHERE setting='SHORTLISTLAYOUT'";
		mcq($sql,$db);

	} elseif ($_REQUEST['dothis']=="personal") {
		if ($_REQUEST['CustomColumnOverrule']) {
			// This is regarding a custom list
			$mlc = $GLOBALS['UC']['MainListColumnsToShow'];

			$mlc[$_REQUEST['CustomColumnOverrule']] = array();
			$mlc[$_REQUEST['CustomColumnOverrule']] = unserialize($MainListColumnsToShowNew);
			$MainListColumnsToShowNew = serialize($mlc);


		}
			

		mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "loginusers SET ELISTLAYOUT='" . mres($MainListColumnsToShowNew) . "' WHERE id=" . $GLOBALS['USERID'], $db);


	} elseif ($_REQUEST['dothis']=="profile") {
		// choose_cols.php?dothis=profile&profile=7&type=group&nonavbar=true&height=400&width=550&
		if ($_REQUEST['profiletype'] == "group") {
			$table = $GLOBALS['TBL_PREFIX'] . "userprofiles";
		} else {
			$table = $GLOBALS['TBL_PREFIX'] . "loginusers";
		}
		mcq("UPDATE " . $table . " SET ELISTLAYOUT='" . mres($MainListColumnsToShowNew) . "' WHERE id=" . $_REQUEST['profilenum'], $db);
		$_REQUEST['cur'] = '';
	} elseif ($_REQUEST['dothis']=="CustomTab") {
		$tabnr = str_replace("Tab", "", $_REQUEST['TabId']);
		$GLOBALS['PersonalTabs'][$tabnr]['ColumnsToShow'] = $MainListColumnsToShowNew;
		UpdateSetting("PersonalTabs", serialize($GLOBALS['PersonalTabs']));
		$_REQUEST['dothis'] = "profile";
	} else {
		PrintAD("Error: cannot determine what to do.");
		EndHTML();
		exit;
	}
	unset($MainListColumnsToShowNew);
	print "<table><tr><td>Values are being saved ...</td></tr></table>";
	if ($_REQUEST['cur']) {
			$_REQUEST['cur'] = base64_decode($_REQUEST['cur']);
	?>
		<script type="text/javascript">
		<!--
			document.location = '<?php echo $_REQUEST['cur'] . "&" . $epoch;?>';
		//-->
		</script>
	<?php
	} elseif ($_REQUEST['dothis'] == "profile") {
	?>
		<script type="text/javascript">
		<!--
			parent.$.fancybox.close();
		//-->
		</script>
	<?php

	} elseif ($_REQUEST['dothis'] == "global") {
				?>
				<script type="text/javascript">
				<!--
					document.location = 'choose_cols.php?dothis=global';
				//-->
				</script>
			<?php
	} elseif ($_REQUEST['dothis'] == "global_shortlist") {
				?>
				<script type="text/javascript">
				<!--
					document.location = 'choose_cols.php?dothis=global_shortlist';
				//-->
				</script>
			<?php

	} else {
		$CCO = $_REQUEST['CustomColumnOverrule'];
		if ($_REQUEST['scope'] == "system") {
			$tmp = GetAttribute("system", "SavedEntityListSelections", 1);
		} else {
			$tmp = GetAttribute("user", "SavedEntityListSelections", $GLOBALS['USERID']);
		}
		if (is_array($tmp[$CCO])) {
			$func_inst = "'&loadSelection=" . urlencode($CCO) . "'";
		}

		if ($_REQUEST['DownloadSpreadSheetStashId']) {
			if ($_REQUEST['BaseList'] == "entity") {
				$call = "DlSs";
			} elseif ($_REQUEST['BaseList'] == "customer") {
				$call = "DlSsC";
			}
		?>
			<script type="text/javascript">
			<!--
				parent.document.location = <?php print "'csv.php?" . $call . "&CustomColumnLayoutStash=" . PushStashValue($mlc[$_REQUEST['CustomColumnOverrule']]) . "&QiD=" . $_REQUEST['DownloadSpreadSheetStashId'] . "&separator=RealExcel';";
				?>
				parent.$.fancybox.close();
			//-->
			</script>
		<?php
		} else {
		?>
			<script type="text/javascript">
			<!--
				parent.refresh_<?php echo $_REQUEST['ParentAjaxHandler'];?>(<?php echo $func_inst;?>);
				parent.$.fancybox.close();
			//-->
			</script>
		<?php
		}
	}
	print "</div></body></html>";
	exit;
}

if ($_REQUEST['dothis']=="personal" || $_REQUEST['dothis']=="SystemSelection") {
	$MainListColumnsToShow = $GLOBALS['UC']['MainListColumnsToShow'];

	if ($_REQUEST['CustomColumnOverrule']) {
		$tabnr = str_replace("Tab", "", $_REQUEST['CustomColumnOverrule']);
		if (is_array($GLOBALS['PersonalTabs'][$tabnr]) && $GLOBALS['PersonalTabs'][$tabnr]['name']) {
			$myname = $GLOBALS['PersonalTabs'][$tabnr]['name'];
		} elseif ($_REQUEST['CustomColumnOverrule']) {
			$myname = $_REQUEST['CustomColumnOverrule'];
		}
		$OKlan = $myname;
		if (is_array($GLOBALS['UC']['MainListColumnsToShow'][$_REQUEST['CustomColumnOverrule']])) {
			$MainListColumnsToShow = $GLOBALS['UC']['MainListColumnsToShow'][$_REQUEST['CustomColumnOverrule']];
		}
		
		$form_ins = "<input type='hidden' name='CustomColumnOverrule' value='" . $_REQUEST['CustomColumnOverrule'] . "'>";
		if ($MainListColumnsToShow['NoPersonalOverrule']) {
			PrintAD("You're not allowed to alter this list (2)");
			EndHTML();
			exit();
		}
	} else {
		//$OKlan = $lang['briefover'] . ", " . strtolower($lang['delentities']) . ", " . strtolower($lang['viewinsertedentities']) . " layout &nbsp;<span class='noway'>(personal setting)</span>&nbsp;";
		$OKlan = $lang['briefover'];
	}
	$start = "<h1>" . $OKlan . "</h1>";
	if (is_numeric($_REQUEST['DownloadSpreadSheetStashId'])) {
		$start .= "<h2>" . $lang['selectfields'] . "</h2>"; 
	} else {
		$start .= "<h2>" . $lang['personallistsettingcolumnstosh'] . "</h2>"; 
	}



} elseif ($_REQUEST['dothis']=="global") {
	$legend = "<img src='images/error.gif' alt=''>&nbsp;";
	$printbox_size = "100%";
	printbox("WARNING - the setting you enter here applies to all lists; the main list, the deleted entities list, the inserted customers list, the insert-only limited interface list, and the managementinterface list! Please be aware of the consequences. You can also enable the LetUserSelectOwnListLayout configuration option to let users choose their own preferred lay-out.");
	$sql= "SELECT value FROM " . $GLOBALS['TBL_PREFIX'] . "settings WHERE setting='MainListColumnsToShow'";
	$result= mcq($sql,$db);
	$resarr=mysql_fetch_array($result);
	$MainListColumnsToShow = unserialize($resarr[0]);
	$start = "Select visible columns <span class='noway'>Global setting</span>";

} elseif ($_REQUEST['dothis']=="global_shortlist") {
	$tmp = db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "settings WHERE setting='SHORTLISTLAYOUT'");
	if ($tmp == 0) {
		mcq("INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value) VALUES('SHORTLISTLAYOUT', '')", $db);
	}
	$sql= "SELECT value FROM " . $GLOBALS['TBL_PREFIX'] . "settings WHERE setting='SHORTLISTLAYOUT'";
	$result= mcq($sql,$db);
	$resarr=mysql_fetch_array($result);
	$MainListColumnsToShow = unserialize($resarr[0]);
	$start = "Select visible columns (<span class='noway'>recent and overdue dashboard lists</span>)";

} elseif ($_REQUEST['dothis']=="profile") {
	$start = "Select visible columns &nbsp;<span class='noway'>(user/group setting)</span>&nbsp;";

	$form_ins  = "<input type='hidden' name='profiletype' value='" . htme($_REQUEST['type']) . "'>";
	$form_ins .= "<input type='hidden' name='profilenum'  value='" . htme($_REQUEST['profile']) . "'>";
	$form_ins .= "<input type='hidden' name='dothis' value='profile'>";

	if ($_REQUEST['type'] == "user") {
		$row = db_GetRow("SELECT ELISTLAYOUT FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE id='" . mres($_REQUEST['profile']) . "'");
		$MainListColumnsToShow = unserialize($row['ELISTLAYOUT']);
	} elseif ($_REQUEST['type'] == "group") {
		$row = db_GetRow("SELECT ELISTLAYOUT FROM " . $GLOBALS['TBL_PREFIX'] . "userprofiles WHERE id='" . mres($_REQUEST['profile']) . "'");
		$MainListColumnsToShow = unserialize($row['ELISTLAYOUT']);
	} else {
		PrintAD("Do not know what to do!");
		EndHTML();
		exit;
	}
} elseif ($_REQUEST['dothis'] == "CustomTab") {
	$tabnr = str_replace("Tab", "", $_REQUEST['TabId']);
	$MainListColumnsToShow = unserialize($GLOBALS['PersonalTabs'][$tabnr]['ColumnsToShow']);
	$form_ins = "<input type='hidden' name='TabId' value='" . $_REQUEST['TabId'] . "'>";
} elseif ($_REQUEST['dothis'] == "flextable") {
	$ft = $_REQUEST['flextable'];
	if ($_REQUEST['CustomColumnOverrule']) {
		$tabnr = str_replace("Tab", "", $_REQUEST['CustomColumnOverrule']);
		if ($GLOBALS['PersonalTabs'][$tabnr]['name']) {
			$myname = $GLOBALS['PersonalTabs'][$tabnr]['name'];
		} elseif ($_REQUEST['CustomColumnOverrule']) {
			$myname = $_REQUEST['CustomColumnOverrule'];
		}
		$OKlan = $myname;
		if (is_array($GLOBALS['UC']['MainListColumnsToShow'][$_REQUEST['CustomColumnOverrule']])) {
			$MainListColumnsToShow = $GLOBALS['UC']['MainListColumnsToShow'][$_REQUEST['CustomColumnOverrule']];
		}
		
		$form_ins = "<input type='hidden' name='CustomColumnOverrule' value='" . $_REQUEST['CustomColumnOverrule'] . "'>";
//		if ($MainListColumnsToShow['NoPersonalOverrule']) {
//			PrintAD("You're not allowed to alter this list (3) " . string_r($MainListColumnsToShow));
//			EndHTML();
//			exit();
//		}
		
		if ($_REQUEST['scope'] == "system") {
			$curlayout = GetAttribute("system", "FlexTableColumns FT" . $ft . " " . $_REQUEST['CustomColumnOverrule'], 1);
		} else {
			$curlayout = GetAttribute("user", "FlexTableColumns FT" . $ft . " " . $_REQUEST['CustomColumnOverrule'], $GLOBALS['USERID']);
		}

	} else {
		//$OKlan = $lang['briefover'] . ", " . strtolower($lang['delentities']) . ", " . strtolower($lang['viewinsertedentities']) . " layout &nbsp;<span class='noway'>(personal setting)</span>&nbsp;";
		$bla = GetFlexTableNames($ft);
		$OKlan = $bla[0];
		if ($_REQUEST['scope'] == "system") {
			$curlayout = GetAttribute("system", "FlexTableColumns FT" . $ft, $GLOBALS['USERID']);
		} else {
			$curlayout = GetAttribute("user", "FlexTableColumns FT" . $ft, $GLOBALS['USERID']);
			if ($curlayout == "") {
				$curlayout = GetAttribute("system", "FlexTableColumns FT" . $ft, $GLOBALS['USERID']);
			}

		}
	}
	$start = "<h1>" . $OKlan . "</h1>";
	
	if (is_numeric($_REQUEST['DownloadSpreadSheetStashId'])) {
		$start .= "<h2>" . $lang['selectfields'] . "</h2>"; 
	} else {
		$start .= "<h2>" . $lang['personallistsettingcolumnstosh'] . "</h2>"; 
	}

	
	print $start;

	print "<form id='choose_colums_flextable' method='post' action=''><div class='showinline'>";
	print "<input type='hidden' name='flextable' value='" . $ft . "'>";
	print "<table width='100%' border='0' class='crm'>";
	print "<thead><tr><td>" . $lang['name'] . "</td><td><div><input type=\"checkbox\" class=\"checkall\"> [" . $lang['all'] . "]</div></td></tr></thead>";
	
	$tmp = GetExtraFlexTableFields($ft, false, false);




	$changed = false;

	foreach ($tmp AS $ef) {
		if ($_REQUEST['EFID' . $ef['id']] == "y") {
			$newcurlayout[] = $ef['id'];
			$changed = true;
		} 
	}
	if (is_numeric($also_show)) {
		$tmp1 = GetExtraFlexTableFields($also_show, false, false);
		foreach ($tmp1 AS $ef) {
			if ($_REQUEST['EFID' . $ef['id']] == "y") {
				$newcurlayout[] = $ef['id'];
				$changed = true;
			} 
		}
	} elseif ($also_show == "entity") {
		$tmp1 = GetExtraFields();
		foreach ($tmp1 AS $ef) {
			if ($_REQUEST['EFID' . $ef['id']] == "y") {
				$newcurlayout[] = $ef['id'];
				$changed = true;
			} 
		}
		foreach (array("category", "duedate", "duetime", "assignee", "owner") AS $ef) {
			if ($_REQUEST["cb_" . $ef] == "y") {
				$newcurlayout[] = $ef;
				$changed = true;
			} 
		}

	} elseif ($also_show == "customer") {
		$tmp1 = GetExtraCustomerFields();
		foreach ($tmp AS $ef) {
			if ($_REQUEST['EFID' . $ef['id']] == "y") {
				$newcurlayout[] = $ef['id'];
				$changed = true;
			} 
		}
		foreach (array("custname") AS $ef) {
			if ($_REQUEST["cb_" . $ef] == "y") {
				$newcurlayout[] = $ef;
				$changed = true;
			} 
		}
	}
	if ($_REQUEST['EFIDrecordid'] == "y") {
			$newcurlayout[] ="recordid";
			$changed = true;
	} 
	if ($_REQUEST['showreferfield'] == "y") {
			$newcurlayout[] = "refer";
			$changed = true;
	} 

	if ($changed) {
		if ($_REQUEST['CustomColumnOverrule']) {
			if ($_REQUEST['scope'] == "system" && is_administrator()) {
				SetAttribute("system", "FlexTableColumns FT" . $ft . " " . $_REQUEST['CustomColumnOverrule'], $newcurlayout, 1);
			} else {
				SetAttribute("user", "FlexTableColumns FT" . $ft . " " . $_REQUEST['CustomColumnOverrule'], $newcurlayout, $GLOBALS['USERID']);
			}

			
		} else {
			if ($_REQUEST['scope'] == "system" && is_administrator()) {
				SetAttribute("system", "FlexTableColumns FT" . $ft, $newcurlayout, 1);
			} else {
				SetAttribute("user", "FlexTableColumns FT" . $ft, $newcurlayout, $GLOBALS['USERID']);
			}
		}
		// HIER
		if ($_REQUEST['DownloadSpreadSheetStashId']) {
				
				$call = "DlSsFT=" . $ft;
				
			?>
				<script type="text/javascript">
				<!--
					parent.document.location = <?php print "'csv.php?" . $call . "&CustomColumnLayoutStash=" . PushStashValue($newcurlayout) . "&EaCSV=" . $_REQUEST['EaCSV'] . "&QiD=" . $_REQUEST['DownloadSpreadSheetStashId'] . "&separator=RealExcel';";
					?>
					parent.$.fancybox.close();
				//-->
				</script>
			<?php
		} else {

			?>
			<script type="text/javascript">
			<!--
					parent.refresh_<?php echo $_REQUEST['ParentAjaxHandler'];?>();
					parent.$.fancybox.close();
			//-->
			</script>
			<?php
		}
	}
	
	if (in_array("recordid", $curlayout)) {
		$a = "checked='checked'";
	} else {
		unset($a);
	}
	print "<tr><td>id</td><td><input type='checkbox' " . $a . " class='radio' value='y' name='EFIDrecordid'></td></tr>";

	if (in_array("refer", $curlayout)) {
		$a = "checked='checked'";
	} else {
		unset($a);
	}

	print "<tr><td>Refer field</td><td><input type='checkbox' " . $a . " class='radio' value='y' name='showreferfield'></td></tr>";
	
	foreach ($tmp AS $ef) {
		if (in_array($ef['id'], $curlayout)) {
			$a = "checked='checked'";
		} else {
			unset($a);
		}
		print "<tr><td>" . $ef['displaylistname'] . "</td><td><input type='checkbox' " . $a . " class='radio' value='y' name='EFID" . $ef['id'] . "'></td></tr>";
	}

	if ($also_show != "") {
		if (is_numeric($also_show)) {
			$tmp1 = GetExtraFlexTableFields($also_show, false, false);
			foreach ($tmp1 AS $ef) {
				if (in_array($ef['id'], $curlayout)) {
					$a = "checked='checked'";
				} else {
					unset($a);
				}
				print "<tr><td>" . $ef['displaylistname'] . "</td><td><input type='checkbox' " . $a . " class='radio' value='y' name='EFID" . $ef['id'] . "'></td></tr>";
			}
		} elseif ($also_show == "entity") {
			$tmp1 = GetExtraFields();
			foreach (array("category", "duedate", "duetime", "assignee", "owner") AS $ef) {
				if (in_array($ef, $curlayout)) {
					$a = "checked='checked'";
				} else {
					unset($a);
				}
				print "<tr><td>" . $lang[$ef] .   "</td><td><input type='checkbox' value=\"y\" " . $a . " class='radio' name='cb_" . $ef . "'></td></tr>";
			}
			foreach ($tmp1 AS $ef) {
				if (in_array($ef['id'], $curlayout)) {
					$a = "checked='checked'";
				} else {
					unset($a);
				}
				print "<tr><td>" . $ef['displaylistname'] . "</td><td><input type='checkbox' " . $a . " class='radio' value='y' name='EFID" . $ef['id'] . "'></td></tr>";
			}
		} elseif ($also_show == "customer") {
			foreach (array("custname") AS $ef) {
				if (in_array($ef, $curlayout)) {
					$a = "checked='checked'";
				} else {
					unset($a);
				}
				print "<tr><td>" . $lang[$ef] .   "</td><td><input type='checkbox' value=\"y\" " . $a . " class='radio' name='cb_" . $ef . "'></td></tr>";
			}

			$tmp1 = GetExtraCustomerFields();
			foreach ($tmp1 AS $ef) {
				if (in_array($ef['id'], $curlayout)) {
					$a = "checked='checked'";
				} else {
					unset($a);
				}
				print "<tr><td>" . $ef['displaylistname'] . "</td><td><input type='checkbox' " . $a . " class='radio' value='y' name='EFID" . $ef['id'] . "'></td></tr>";
			}

		}
	}
	print "</table><br>";
	print "<input type='hidden' value='" . htme($_REQUEST['CustomColumnOverrule']) . "' name='CustomColumnOverrule'>";
	if (is_numeric($_REQUEST['DownloadSpreadSheetStashId'])) {
			print "<input type='hidden' name='DownloadSpreadSheetStashId' value='" . htme($_REQUEST['DownloadSpreadSheetStashId']) . "'>";
			print "<input type='hidden' name='BaseList' value='" . htme($_REQUEST['BaseList']) . "'>";
			print "Export as tab-delimited instead of Excel: <input type='checkbox' name='EaCSV' id='JS_EaCSV'  value='1'>";
			print "<input type='submit' name='whatever' value='" . $lang['downloadexport'] . "'></td></tr>";
	} else {
		print "<input type='submit' name='SaveButton' value='" . $lang['save'] . "'>";
	}
	print "</form>";

	


	EndHTML();
	exit;

} else {
	PrintAD("Error (4)");
	EndHTML();
	exit;
}
print "<form id='choose_colums' method='post' action=''><div class='showinline'>";
print $start;

print "<table width='100%' border='0' class='sortable'>";
print "<thead><tr><td>" . $lang['name'] . "</td><td><div><input type=\"checkbox\" class=\"checkall\"> [" . $lang['all'] . "]</div></td></tr></thead>";
if ($_REQUEST['dothis'] == "CustomTab") {

	if ($MainListColumnsToShow['NoPersonalOverrule']) {
		$a = "checked='checked'";
	} else {
		unset($a);
	}
	print "<tr><td colspan='2'><strong>Check to allow no changes by users for this list:</strong> <input type='checkbox' " . $a . " class='radio' name='NoPersonalOverrule'><br><br><br></td></tr>";
	print "<tr><td colspan='2'><span style='color: #ff0000';'>Warning: the list layout is not a security setting, it is just for cosmetics! Use extra field access rights to really block access to fields for users.</span></td></tr>";
}
print "<tr><td colspan='2'><strong>Regular fields:</strong></td></tr>";
print "<tr><td>id</td><td>[always]<input type='hidden' name='form_sub' value='1'><input type='hidden' name='cur' value='" . htme($_REQUEST['cur']) . "'><input type='hidden' name='dothis' value='" . $_REQUEST['dothis'] . "'>" . $form_ins . "</td></tr>";


if ($MainListColumnsToShow['cb_cust']) {
	$a = "checked='checked'";
} else {
	unset($a);
}
print "<tr><td>" . $lang['customer'] .   "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_cust'></td></tr>";
if ($MainListColumnsToShow['cb_owner']) {
	$a = "checked='checked'";
} else {
	unset($a);
}
print "<tr><td>" . $lang['owner'] .      "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_owner'></td></tr>";
if ($MainListColumnsToShow['cb_ownergroup']) {
	$a = "checked='checked'";
} else {
	unset($a);
}
print "<tr><td>" . $lang['group'] . " (" . $lang['owner'] . ")</td><td><input type='checkbox' " . $a . " class='radio' name='cb_ownergroup'></td></tr>";

if ($MainListColumnsToShow['cb_assignee']) {
	$a = "checked='checked'";
} else {
	unset($a);
}
print "<tr><td>" . $lang['assignee'] .   "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_assignee'></td></tr>";

if ($MainListColumnsToShow['cb_assigneegroup']) {
	$a = "checked='checked'";
} else {
	unset($a);
}
print "<tr><td>" . $lang['group'] . " (" . $lang['assignee'] . ")</td><td><input type='checkbox' " . $a . " class='radio' name='cb_assigneegroup'></td></tr>";

if ($MainListColumnsToShow['cb_status']) {
	$a = "checked='checked'";
} else {
	unset($a);
}
print "<tr><td>" . $lang['status'] . "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_status'></td></tr>";
if ($MainListColumnsToShow['cb_priority']) {
	$a = "checked='checked'";
} else {
	unset($a);
}
print "<tr><td>" . $lang['priority'] .   "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_priority'></td></tr>";
if ($MainListColumnsToShow['cb_category']) {
	$a = "checked='checked'";
} else {
	unset($a);
}
print "<tr><td>" . $lang['category'] .   "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_category'></td></tr>";
if ($_REQUEST['DownloadSpreadSheetStashId']) {
	if ($MainListColumnsToShow['cb_contents']) {
		$a = "checked='checked'";
	} else {
		unset($a);
	}
	print "<tr><td>" . $lang['contents'] .   "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_contents'></td></tr>";
}
if ($MainListColumnsToShow['cb_startdate']) {
	$a = "checked='checked'";
} else {
	unset($a);
}
print "<tr><td>" . $lang['startdate'] .    "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_startdate'></td></tr>";
if ($MainListColumnsToShow['cb_duedate']) {
	$a = "checked='checked'";
} else {
	unset($a);
}
print "<tr><td>" . $lang['duedate'] .    "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_duedate'></td></tr>";
if ($MainListColumnsToShow['cb_lastupdate']) {
	$a = "checked='checked'";
} else {
	unset($a);
}
print "<tr><td>" . $lang['lastupdate'] . "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_lastupdate'></td></tr>";

if ($MainListColumnsToShow['cb_creationdate']) {
	$a = "checked='checked'";
} else {
	unset($a);
}
print "<tr><td>" . $lang['creationdate'] . "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_creationdate'></td></tr>";

if ($MainListColumnsToShow['cb_closedate']) {
	$a = "checked='checked'";
} else {
	unset($a);
}
print "<tr><td>" . $lang['closedate'] . "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_closedate'></td></tr>";


if ($MainListColumnsToShow['cb_duration']) {
	$a = "checked='checked'";
} else {
	unset($a);
}
print "<tr><td>Age/duration</td><td><input type='checkbox' " . $a . " class='radio' name='cb_duration'></td></tr>";
if ($MainListColumnsToShow['cb_numofattachments']) {
	$a = "checked='checked'";
} else {
	unset($a);
}
print "<tr><td>#Files</td><td><input type='checkbox' " . $a . " class='radio' name='cb_numofattachments'></td></tr>";

$list = GetExtraFields();
foreach ($list AS $field) {
		$varname = "EFID" . $field['id'];
		if ($field['fieldtype'] <> "List of values" && $field['underwaterfield'] != 'y') {
			if ($MainListColumnsToShow[$varname]) {
				$a = "checked='checked'";
			} else {
				$a = "";
			}
			$cf .= "<tr><td>" . $field['displaylistname'] . "</td><td><input type='checkbox' " . $a . " class='radio' name='EFID" . $field['id'] . "'></td></tr>";
		}
}

$fts = GetFlexTableDefinitions(false,"many-to-one", false, "entity");
foreach ($fts AS $ft) {
	$list = GetExtraFlexTableFields($ft['recordid'], false, false);

	$reffield = GetReferencesToTable($ft['recordid'], "entity");

	if ($reffield) { // The entity extra field contain a reference to this table

		$cf .=  "<tr><td colspan='2'><br><strong>" . $ft['tablename'] . " fields:</strong></td></tr>";
		foreach ($list AS $field) {
				$varname = "EFID" . $field['id'];
				if ($field['fieldtype'] <> "List of values" && $field['underwaterfield'] != 'y') {
					if ($MainListColumnsToShow[$varname]) {
						$a = "checked='checked'";
					} else {
						$a = "";
					}
					$cf .= "<tr><td>" . $field['displaylistname'] . "</td><td><input type='checkbox' " . $a . " class='radio' name='EFID" . $field['id'] . "'></td></tr>";
				}
		}
	}
}
$fts = GetFlexTableDefinitions(false,"one-to-many", false, "entity");
foreach ($fts AS $ft) {
	
	
	if ($ft['refers_to'] == "entity") {
		
		$list = GetExtraFlexTableFields($ft['recordid'], false, false);
		$cf .=  "<tr><td colspan='2'><br><strong>" . $ft['tablename'] . " fields: (summarized)</strong></td></tr>";
		foreach ($list AS $ddfield) {
			//if ($ddfield['fieldtype'] == "drop-down") { 
				$varname = "EFID" . $ddfield['id'];

				if ($MainListColumnsToShow[$varname]) {
					$a = "checked='checked'";
				} else {
					$a = "";
				}
				$cf .= "<tr><td>" . $ddfield['displaylistname'] . "</td><td><input type='checkbox' " . $a . " class='radio' name='EFID" . $ddfield['id'] . "'></td></tr>";
		}
		if ($MainListColumnsToShow["SUMFT" . $ft['recordid']]) {
			$a = "checked='checked'";
		} else {
			$a = "";
		}
/*		$cf .= "<tr><td>Total number of underlying records</td><td><input type='checkbox' " . $a . " class='radio' name='SUMFT" . $ft['recordid'] . "'></td></tr>";
*/
		foreach ($list AS $ddfield) {
			if ($ddfield['fieldtype'] == "numeric" || (GetExtraFieldType($ddfield['id']) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $ddfield['id']) == "Numeric")) { 
				$varname = "SUMEFID" . $ddfield['id'];
				if ($MainListColumnsToShow[$varname]) {
					$a = "checked='checked'";
				} else {
					$a = "";
				}
				$cf .= "<tr><td><i>Sum of underlying records, field</i> " . $ddfield['displaylistname'] . "</td><td><input type='checkbox' " . $a . " class='radio' name='SUMEFID" . $ddfield['id'] . "'></td></tr>";
			}
		}

	}
}

if ($_REQUEST['dothis'] != "global_shortlist") {
	$cf .=  "<tr><td colspan='2'><br><strong>" . $lang['customer'] . " fields:</strong></td></tr>";

	if ($MainListColumnsToShow['cb_contact']) {
		$a = "checked='checked'";
	} else {
		unset($a);
	}
	$cf .= "<tr><td>" . $lang['contact'] .      "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_contact'></td></tr>";
	if ($MainListColumnsToShow['cb_contact_title']) {
		$a = "checked='checked'";
	} else {
		unset($a);
	}
	$cf .= "<tr><td>" . $lang['contacttitle'] .   "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_contact_title'></td></tr>";
	if ($MainListColumnsToShow['cb_contact_phone']) {
		$a = "checked='checked'";
	} else {
		unset($a);
	}
	$cf .= "<tr><td>" . $lang['contactphone'] .     "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_contact_phone'></td></tr>";
	if ($MainListColumnsToShow['cb_contact_email']) {
		$a = "checked='checked'";
	} else {
		unset($a);
	}
	$cf .= "<tr><td>" . $lang['contactemail'] .   "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_contact_email'></td></tr>";
	if ($MainListColumnsToShow['cb_cust_address']) {
		$a = "checked='checked'";
	} else {
		unset($a);
	}
	$cf .= "<tr><td>" . $lang['customeraddress'] .   "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_cust_address'></td></tr>";
	if ($MainListColumnsToShow['cb_cust_remarks']) {
		$a = "checked='checked'";
	} else {
		unset($a);
	}
	$cf .= "<tr><td>" . $lang['custremarks'] .    "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_cust_remarks'></td></tr>";
	if ($MainListColumnsToShow['cb_cust_homepage']) {
		$a = "checked='checked'";
	} else {
		unset($a);
	}
	$cf .= "<tr><td>" . $lang['custhomepage'] .  "</td><td><input type='checkbox' " . $a . " class='radio' name='cb_cust_homepage'></td></tr>";

	$list = GetExtraCustomerFields();
	foreach ($list AS $field) {
			$varname = "EFID" . $field['id'];
			if ($field['fieldtype'] <> "List of values" && $field['fieldtype'] <> "text area" && $field['fieldtype'] <> "text area (rich text)" && $field['underwaterfield'] != 'y') {
				if ($MainListColumnsToShow[$varname]) {
					$a = "checked='checked'";
				} else {
					$a = "";
				}
				$cf .= "<tr><td>" . $field['displaylistname'] . "</td><td><input type='checkbox' " . $a . " class='radio' name='EFID" . $field['id'] . "'></td></tr>";
			}
	}
}
print $cf;
print "<tr><td></td><td align='right'><input type='hidden' name='what' value='ML'>";
if (is_numeric($_REQUEST['DownloadSpreadSheetStashId'])) {
	print "<input type='hidden' name='DownloadSpreadSheetStashId' value='" . htme($_REQUEST['DownloadSpreadSheetStashId']) . "'>";
	print "<input type='hidden' name='BaseList' value='" . htme($_REQUEST['BaseList']) . "'>";
	print "<input type='submit' name='whatever' value='" . $lang['downloadexport'] . "'></td></tr>";
	print "Export as tab-delimited instead of Excel: <input type='checkbox' name='EaCSV' id='JS_EaCSV'  value='1'>";
} else {
	print "<input type='submit' name='whatever' value='" . $lang['save'] . "'></td></tr>";
}
print "</table>";
print "</div></form>";

EndHTML();



function printbox($msg)
{
		global $printbox_size,$legend;

		if (!$printbox_size) {
			$printbox_size = "70%";
		}

		print "<table border='0' width='$printbox_size'><tr><td colspan='2'><fieldset>";
		if ($legend) {
			print "<legend>&nbsp;<img src='images/crmlogosmall.gif' alt=''>&nbsp;&nbsp;" . $legend . "</legend>";
		}
		print $msg . "</fieldset></td></tr></table><br>";

		unset($printbox_size);
		$legend = "";
} // end func
?>