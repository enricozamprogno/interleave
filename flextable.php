<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file handles flextables
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
//extract($_REQUEST);

if ($_REQUEST['AddInPopup']) {
	// Make sure entity locks are not removed when editing a flextable record in a popup!
	$_REQUEST['keeplocked'] = "1";
}
if ($_REQUEST['TableAdmin'] || $_REQUEST['EditFlexTable'] || $_REQUEST['DeleteFlexTable'] || $_REQUEST['EditFlexTableFormID'] || $_REQUEST['TruncateFlexTable'] || $_REQUEST['PurgeFlexTable'] || $_REQUEST['AtOnce']) {
	$_GET['SkipMainNavigation'] = true;
}
require_once("initiate.php");
ShowHeaders();
$to_tabs = array("overview", "new_manual", "new_auto");
$tabbs["overview"] = array("flextable.php?TableAdmin=true" => "Flextables");
$tabbs["new_manual"] = array("flextable.php?EditFlexTable=new" => "Create new flextable (manual)");
$tabbs["new_auto"] = array("flextable.php?AtOnce=true" => "Create new flextable (CSV import or SQL)");

if ($_REQUEST['EditFlexTable']) {
	$to_tabs[] = "current";
	$tabbs["current"] = array("" => "Editing flextable " . htme($_REQUEST['EditFlexTable']) ." :: " . htme(GetFlexTableName($_REQUEST['EditFlexTable'])));
	$selected_tab = "current";
}


if ($_REQUEST['InlineForm']) {
	if (CheckFlexTableAccess($_REQUEST['InlineForm']) == "ok") {
		qlog(INFO, "INLINEFLEXTABLE : " . $_REQUEST['InlineForm'] . " showing");
		$list = GetExtraFlextableFields($_REQUEST['InlineForm']);
		foreach ($list AS $field) {
			if ($_REQUEST['EFID' . $field] != "") {
				$to_post .= "&EFID" . $field . "=" . $_REQUEST['EFID' . $field];
			}
		}
		
		$template = GetAttribute("flextable", "InlineFormHeaderHTML", $_GET['InlineForm']);
		$ftdef = GetFlexTableDefinitions($_GET['InlineForm']);
		$ftdef = $ftdef[0];
		
		if ($ftdef['refers_to'] == "entity") {
			$template = ParseTemplateEntity($template, $_REQUEST['refer'], false, true, false, "htme");
		} elseif ($ftdef['refers_to'] == "customer") {
			$template = ParseTemplateCustomer($template, $_REQUEST['refer'], false, "htme", "ref" . $flextableid . "-" . $row['id']);
		} else {
			$flextable = str_replace("flextable", "", $ftdef['refers_to']);
			$template = ParseFlexTableTemplate($flextable, $_REQUEST['refer'], $template , true, false, false, "htme");
		}
		
		print ParseTemplateGeneric($template);
		
		$rep = AjaxBox("ReturnInlineFlextableForm", true, "&NotInline=true&ilft=" . $_GET['InlineForm'] . "&refer=" . $_GET['refer'] . $to_post, false, false, 0);
		print $rep;
	}
} elseif ($_REQUEST['AtOnce']) {
	MustBeAdmin();
	AdminTabs("ft");
	print PlainNav($to_tabs, $tabbs, $selected_tab);
	print "<table style='width: 75%;' class='nicetableclear'><tr class='nicerow'><td>";
	if ($_REQUEST['ImportStash']) {
		$arr = PopStashValue($_REQUEST['ImportStash']);
		$tname = $_REQUEST['TableName'];
		$fields = explode("\t",$arr[0]);
		unset($arr[0]);

		$sql = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "flextabledefs(refers_to, tablename, formid, refer_field_layout, orientation, table_layout, maxrowsperpage, headerhtml, tableheaderrepeat, addlinktext) VALUES('" . mres($_REQUEST['refers_to']) . "','" . mres(trim($_REQUEST['TableName'])) . "','','@EID@: @CATEGORY@','" . mres($_REQUEST['orientation']) . "','','100','<strong>" . mres(htme(trim($_REQUEST['TableName']))) . "</strong>','0', '" . mres($lang['pbaddrec']) . "')";
		mcq($sql, $db);
		$table_id = mysql_insert_id();

		mcq("CREATE TABLE " . $GLOBALS['TBL_PREFIX'] . "flextable" . $table_id . " (recordid INT(11) NOT NULL auto_increment, refer INT(11) NOT NULL, readonly ENUM('no','yes') NOT NULL DEFAULT 'no', deleted ENUM('n','y') NOT NULL DEFAULT 'n', formid INT NOT NULL , timestamp_last_change TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (recordid)) ENGINE=MYISAM DEFAULT CHARSET=UTF8", $db);


		$temp_names = array();
		$order = 10;
		foreach ($fields AS $field) {
				$order++;
				$sql = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "extrafields(options,name,ordering,fieldtype,hidden,tabletype,forcing,defaultval,sort,storetype,size,showsearchbox,limitddtowidth,allowuserstoaddoptions,excludefromfilters) VALUES ('','" . mres(trim($field)) . "'," . $order . ",'textbox','n'," . $table_id . ",'n','','n','default','','n','','n','y')";
				mcq($sql,$db);
				$new_field_name = mysql_insert_id();
				array_push($temp_names, array($field, "#EFID" . $new_field_name . "#"));
				mcq("ALTER TABLE " . $GLOBALS['TBL_PREFIX'] . "flextable" . $table_id . " ADD `EFID" . $new_field_name . "` LONGTEXT NOT NULL", $db);
//				print "Push : " . "#" . str_replace(" ", "_", strtoupper($field)) . "#<br>";
		}

		$template = "<h1>" . $tname . "</h1>";
		$template .= "<table class='interleave-table'>";
		foreach ($temp_names AS $fa) {
			$template .= "<tr><td>" . $fa[0] . "</td><td>" . $fa[1] . "</td></tr>";
		}
		$template .= "<tr><td colspan='2'>#SAVEBUTTON# &nbsp; @XONLY@#DELETEBUTTON#@ENDXONLY@</td></tr></table>";


		mcq("INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "templates (templatename, templatetype, template_subject, content) VALUES('" . mres($tname . "-form") . "','" . mres("TEMPLATE_HTML_FORM") . "','" . mres($tname . "-form") . "','" . mres($template) . "')", $db);

		$form = mysql_insert_id();

		$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "flextabledefs SET formid=" . $form . " WHERE recordid=" . $table_id;
		mcq($sql, $db);
		print "Done creating table, form and fields. Please wait for import to finish (don't close or navigate away!).";

		foreach ($arr AS $row) {
			$importbody .= str_replace("\n","", $row) . "\n";
			$t++;
		}


		$t = PushStashValue($importbody);
		?>
			<script type="text/javascript">
				document.location='flextable.php?Msg=1&DataInStash=<?php echo $t;?>&ImportIntoTable=<?php echo $table_id;?>';
			</script>
		<?php




	} elseif ($_POST['importBody']) {
		$farr = explode("\n", trim($_POST['importBody']));
		$st1 = explode("\t",$farr[0]);
		$stash = PushStashValue($farr);
		print "<form id='AutoDoIets' method='post' action=''><div class='showinline'><table class=' class='nicetable'><tr><td>Table name</td><td><input type='text' name='TableName' value=''><input type='hidden' name='ImportStash' value='" . $stash . "'><input type='hidden' name='AtOnce' value='true'></td></tr>";
		print "<tr><td>Refers to</td><td><select name='refers_to'>";
		print "<option value='entity'>Entity table</option>";
		print "<option value='customer'>Customer table</option>";

		foreach (GetFlextableDefinitions() AS $tmp) {
			print "<option value='flextable" . $tmp['recordid'] . "'>Flextable " . htme($tmp['tablename']) . "</option>";
		}
		print "<option value='no_refer' >No refer</option>";
		print "</select></td></tr>";

		print "<tr><td>Orientation</td><td><select name='orientation'>";
		print "<option value='many_entities_to_one'>Multiple entities, customers or flexrecords refer to 1 record in this table</option>";
		print "<option " . $ins . " value='one_entity_to_many'>Multiple records in this table refer to 1 entity, customer or flexrecord</option>";
		print "</select>";
		print "</td></tr>";



		print "<tr><td valign='top'>Fields to add</td><td>";
		foreach($st1 AS $field) {
			print $field . "<br>";
		}
		print "</td></tr>";
		print "<tr><td>Number of records to import</td><td>" . (sizeof($farr)-1) . "</td></tr>";
		print "<tr><td>Number of flextable forms to generate</td><td>1</td></tr>";
		print "<tr><td colspan='2'><input type='submit' name='Go' value='go'></td></tr>";
		print "</div></form>";
	} elseif ($_REQUEST['import_sql_host'] && $_REQUEST['import_sql_user']) {
		//print_r($_REQUEST);

		if (!$db = mysql_connect($_REQUEST['import_sql_host'], $_REQUEST['import_sql_user'], $_REQUEST['import_sql_pass'])) {
			print "<img src='images/error.gif' alt=''> Database connection failed (probably wrong user/pass combination).<br>";
			$cancel = true;
		} elseif (!mysql_select_db($_REQUEST['import_sql_name'],$db)) {
			print "<img src='images/error.gif' alt=''> Connection is OK but database could not be selected<br>";
			$cancel = true;
		}
		if (!$cancel) {
			$st1 = array();
			$farr = array();
			print "<pre>";
			$thisrow = "";
			$sql = $_REQUEST['import_sql_query'];
			$a = mysql_query($sql, $db) or (handle_error(mysql_error(),$sql));
			while ($row = mysql_fetch_array($a)) {
				unset($y);
				foreach ($row AS $head => $data) {
					if ($y && !is_numeric($head))	$thisrow .= ";";
					if (!is_numeric($head))	{
						$thisrow .= str_replace(";","###SEMICOLON###", $data);
						$y = true;
					}
					if (!is_numeric($head)) {
						if ($s) $headrow .= ";";
						$headrow .= $head;
						$s = true;
					}
				}
				if (!$done) {
					array_push($farr, $headrow);
				}
				array_push($farr, $thisrow);
				unset($thisrow);
				unset($headrow);
				$done = true;
			}



			unset($GLOBALS['LAST_DB_CONN']);
			DB_Connect($GLOBALS['ORIGINAL_REPOSITORY'], false);
			if (SwitchToRepos($GLOBALS['ORIGINAL_REPOSITORY']) == false) {
				print "ERROR!";
				exit;

			}

			
			$st1 = explode(";",$farr[0]);
			$stash = PushStashValue($farr);
			print "<form id='AutoDoIets' method='post' action=''><div class='showinline'><table class='nicetable'><tr><td>Table name</td><td><input type='text' name='TableName' value=''><input type='hidden' name='ImportStash' value='" . $stash . "'><input type='hidden' name='AtOnce' value='true'></td></tr>";
			print "<tr><td>Refers to</td><td><select name='refers_to'>";
			print "<option value='entity'>Entity table</option>";
			print "<option value='customer' " . $ins . ">Customer table</option>";
			foreach (GetFlextableDefinitions() AS $tmp) {
				print "<option value='flextable" . $tmp['recordid'] . "'>Flextable " . htme($tmp['tablename']) . "</option>";
			}
			print "<option value='no_refer' " . $ins . ">No refer</option>";
			print "</select></td></tr>";

			print "<tr><td>Orientation</td><td><select name='orientation'>";
			print "<option value='many_entities_to_one'>Multiple entities, customers or flexrecords refer to 1 record in this table</option>";
			print "<option " . $ins . " value='one_entity_to_many'>Multiple records in this table refer to 1 entity, customer or flexrecord</option>";
			print "</select>";
			print "</td></tr>";

			print "<tr><td valign='top'>Fields to add</td><td>";
			foreach($st1 AS $field) {
				print $field . "<br>";
			}
			print "</td></tr>";
			print "<tr><td>Number of records to import</td><td>" . (sizeof($farr)-1) . "</td></tr>";
			print "<tr><td>Number of flextable forms to generate</td><td>1</td></tr>";
			print "<tr><td colspan='2'><input type='submit' name='Go' value='go'></td></tr>";
			print "</div></form>";
		}

	} else {
		
		print "Paste tab-delimited values (from Excel or OO/LO Calc)<br>";
		print "<form id='uploadFlextable' method='post' enctype='multipart/form-data' action=''><div class='showinline'>";
		print "<textarea name=\"importBody\" id=\"JS_importBody\" cols=\"100\" rows=\"4\"></textarea><br>";
		print "<input type='submit' value='Go'></div></form><br>";
		print "&nbsp;&nbsp; File layout:<br>";
		print "<ul><li>CSV Layout with tab as separator</li>";
		print "<li>First row must contain field names</li></ul>";
		//print "<hr>";
		print "</td></tr><tr class='nicerow'><td>";
		print "Or create table by SQL Query:&nbsp;&nbsp;&nbsp;";
		print "<form id='SQLquery' method='post' action=''><div class='showinline'><table>";
		print "<tr><td>Database host</td><td><input type='text' name='import_sql_host'></td></tr>";
		print "<tr><td>Database user</td><td><input type='text' name='import_sql_user'></td></tr>";
		print "<tr><td>Database password</td><td><input type='password' name='import_sql_pass'></td></tr>";
		print "<tr><td>Database name</td><td><input type='text' name='import_sql_name'></td></tr>";
		print "<tr><td>SQL query</td><td><textarea rows='5' cols='40' name='import_sql_query'></textarea></td></tr>";
		print "<tr><td><input type='submit' name='submit' value='Go'></td><td></td></tr></table></div></form>";
	}
	print "</td></tr></table>";
} elseif ($_GET['DeleteFlexTableRecord'] && $_REQUEST['recordid'] != "0") {
		if (CheckFlexTableAccess($_REQUEST['flextableid']) == "ok") {
			if (CheckReferencesToFlextableRecord($_REQUEST['flextableid'], $_REQUEST['recordid'])) {
				journal($_REQUEST['recordid'], "Record deleted", "flextable" . $_GET['flextableid']);
				
				DataJournal($_REQUEST['recordid'], "?", "y", "deleted", $_REQUEST['flextableid']);

				DeleteFlexTableRow($_REQUEST['recordid'], $_GET['flextableid']);
				print "&nbsp;&nbsp;&nbsp;Record " . $_REQUEST['recordid'] . " " . $lang['wasdeleted'] . "<br>";
				//ExpireFormCache($_REQUEST['recordid'], "flextable change", "flextable" . $_REQUEST['flextableid']);
				//ExpireFormCache($_REQUEST['recordid'], "flextable change", "flextable" . $_REQUEST['flextableid'] . "ref");
				CleanUpCacheTablesAfterSave();
				//mcq("DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "journal WHERE type='flextable" . mres($_REQUEST['flextableid']) . "' AND eid='" . mres($_REQUEST['recordid']) . "'", $db);
				$deleted = true;

			} else {
				PrintAD("This record cannot be deleted. Other records depend on it.");
				EndHTML();
				exit;
			}
		} else {
			PrintAD("You don't have sufficient access to delete this record");
		}
		$Todo = GetTodos($_REQUEST['recordid']);
		foreach($Todo AS $do) {
			if (GetExtraFieldType(str_replace("EFID", "", $do['onchange'])) == $_REQUEST['flextableid']) {
				ProcessTriggers($do['onchange'],$_REQUEST['recordid'],$do['to_value'],false,$_REQUEST['flextableid']);
				DropTodos($_REQUEST['recordid'], $do['onchange']);
				qlog(INFO, "TODO ProcessTrigger: " . $do['onchange'] . " value " . $do['to_value']);
			}
		}
		

		if ($_REQUEST['AddInPopup']) {
			?>
			<script type="text/javascript">
				<?php
					if (strlen($_REQUEST['ParentAjaxHandler']) > 1) {
					?>
					// 1
					parent.refresh_<?php echo $_REQUEST['ParentAjaxHandler'];?>();
					<?php
				}
				?>

				parent.$.fancybox.close();
			</script>
			<?php
		} elseif ($_REQUEST['fromlist'] && !$stayhere) {
		?>
			<script type="text/javascript">
				document.location='<?php echo base64_decode($_REQUEST['fromlist']) . "&Pag_Moment=" . htme($_REQUEST['Pag_Moment']) . "&FilterTable=" . htme($_REQUEST['FilterTable']);?>';
			</script>
			<?php

		} elseif ($_REQUEST['refer']) {

			//ExpireFormCache($_REQUEST['refer'], "Flextable update", "entity");
			CleanUpCacheTablesAfterSave();
			$ftd = GetFlexTableDefinitions($_REQUEST['flextableid']);
			if ($ftd[0]['refers_to'] == "entity") {
				?>
				<script type="text/javascript">
					document.location='edit.php?e=<?php echo $_REQUEST['refer'];?>';
				</script>
				<?php
			} elseif ($ftd[0]['refers_to'] == "customer") {
				?>
				<script type="text/javascript">
					document.location='customers.php?editcust=1&custid=<?php echo $_REQUEST['refer'];?>';
				</script>
				<?php
			} else {
				$ft = str_replace("flextable", "", $ftd[0]['refers_to']);
				?>
				<script type="text/javascript">
					document.location='flextable.php?EditRecord=<?php echo $_REQUEST['refer'];?>&FlexTable=<?php echo $ft;?>';
				</script>
				<?php
			}
		} else {
			?>
			<script type="text/javascript">
				document.location='flextable.php?ShowTable=<?php echo $_GET['flextableid'] . "&Pag_Moment=" . htme($_REQUEST['Pag_Moment']) . "&FilterTable=" . htme($_REQUEST['FilterTable']);?>';
			</script>
			<?php
		}


} elseif ($_POST['FlexTableFormSubmit']) {
	// A flextable form was submitted

	$st = $_POST['flextableid'];
	$tmp = GetFlexTableDefinitions($st, false, false, "both");

	$flexdef = GetFlexTableDefinitions($st);
	if ($flexdef[0]['stayinformaftersave'] == "y") {
		$stayhere = true;
	} 

	if ($_REQUEST['DeleteFlexTableRecord'] && $_REQUEST['recordid'] != "0") {
		if (CheckFlexTableAccess($st) == "ok") {
			journal($_REQUEST['recordid'], "Record deleted", "flextable" . $st);
			DataJournal($_REQUEST['recordid'], "?", "y", "deleted", $st);
			DeleteFlexTableRow($_REQUEST['recordid'], $st);
			print "&nbsp;&nbsp;&nbsp;Record " . $_REQUEST['recordid'] . " " . $lang['wasdeleted'] . "<br>";
			$deleted = true;

		}

	} elseif ($_POST['recordid'] == "_new_") {

		$list = GetExtraFlexTableFields($st);

		$unique = $_REQUEST['hash'];
		$recentlyadded = db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "cache WHERE value='" . mres($unique) . "'");

		if ($recentlyadded > 0 && ($GLOBALS['CHECKFORDOUBLEADDS'] == "Yes")) {
			qlog(INFO, "CheckExistence:: The same - " . $unique . " NOT SAVING THIS");
			print "<span class=\"noway\">Avoided adding the same record twice.</span>";
		} else {
			if ($GLOBALS['CHECKFORDOUBLEADDS'] == "Yes")  {	// do not push this value if it is disabled
				PushStashValue($unique);
			} else {
				qlog(INFO, "Flextable double-add check is disabled! (not saving MD5)");
			}
		
			if ($flexdef[0]['orientation'] == "one_entity_to_many") {
				mcq("INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . "(refer,readonly,deleted) VALUES('" . mres($_POST['FLEXTABLEREFERFIELD']) . "','no','n')", $db);
				$nextid = mysql_insert_id();
				//ExpireFormCache($_POST['FLEXTABLEREFERFIELD'], "Flextable update", "entity");
				CleanUpCacheTablesAfterSave();
			} else {
				mcq("INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . "(readonly,deleted) VALUES('no','n')", $db);
				$nextid = mysql_insert_id();
			}



			$_REQUEST['recordid'] = $nextid;

			$added_record = true;
			$GLOBALS['FLEXTABLERECORDACESSCACHE'][$st][$nextid] = "ok";

			// Journal this
			journal($_REQUEST['recordid'], "Record created", "flextable" . $st);
			
			// Create mandatory fields immediately
			AddDefaultExtraFlexTableFields($st, $nextid);
			

			if (count($_FILES['userfile']['name']) > 0) {

				for ($tel=0;$tel<sizeof($_FILES['userfile']['name']);$tel++) {
					
					
	
					$tmpfile = $_FILES['userfile']['tmp_name'][$tel];
					$size    = $_FILES['userfile']['size'][$tel];
					$type    = $_FILES['userfile']['type'][$tel];
					$name    = $_FILES['userfile']['name'][$tel];
					
					if ($tmpfile != "") {	
						// A file was attached
						// Read contents of uploaded file into variable
		
						$x = AttachFile($_REQUEST['recordid'],$name,file_get_contents($tmpfile), "flextable" . $st,$type);
					}
	
					
				}
				unset($filecontent);
				unset($_FILES);
								
									
			} else {
				qlog(INFO, "No file received!");
			}
		}





	} else {
		$list = GetExtraFlexTableFields($st);
//		$nextid = GetMaxFlexTableRecordId($st) +1;
		if ($_POST['FLEXTABLEREFERFIELD'] > 0) {
			mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . " SET refer='" . mres($_POST['FLEXTABLEREFERFIELD']) . "' WHERE recordid='" . mres($_POST['recordid']) . "'", $db);
			$_REQUEST['recordid'] = $_POST['recordid'];
			ClearAccessCache($_POST['recordid'],"ft" . $st, "all");
		}
	}


	if (!$deleted) {

		if ($_REQUEST['recordid'] == "0") {
			PrintAD("ERROR: You're working with record 0 (zero), which is illegal.");
			EndHTML();
			exit;
		}

		if ($_REQUEST['CalendarAdd'] && $_REQUEST['CalendarField']) {
			$_REQUEST['record_id']		= $_REQUEST['recordid'];
			$_REQUEST['id']				= $_REQUEST['recordid'];
			$_REQUEST['table']			= "flextable" . $st;
			$_REQUEST['NewAppointment'] = $_REQUEST['CalendarAdd'];
			$_REQUEST['extrafield']		= $_REQUEST['CalendarField'];
			$_REQUEST['ef']				= $_REQUEST['CalendarField'];
			CheckAndSetPlanning();
		}


		// Now see if there were any extra fields added:
		// First, collect extra fields list
		$list = GetExtraFlexTableFields($st);


		// Second, get all extra fields of this entity
		$af = array();
		$tabletype = "flextable" . $st;


		$af = GetFlexTableRow($_REQUEST['recordid'], $st);

//		print $allfield_sql;
//		$res = mcq($allfield_sql, $db);
//		while ($row = mysql_fetch_array($res)) {
//			$af[$row['name']] = $row;
//		}


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
			$efield_form_value = $_POST[$efield_varname];
			$efield_type = $extrafield['fieldtype'];
			$efield_curr_value = $af[$efield_varname];

			if ($efield_type == "date" && $efield_form_value == "undefined") {
				$efield_form_value = "";
			}

			# debugging output
//			print "efield_curr_value: '" . $efield_curr_value . "' form : $efield_form_value tag: $efield_varname<br><pre>";

			if (strstr(GetExtraFieldType($efield_id), "multiselect") && isset($_REQUEST[$efield_varname]) && count($_REQUEST[$efield_varname]) == 1 && $_REQUEST[$efield_varname][0] == "{{{null}}}") {
				$_REQUEST[$efield_varname] = array();
			}

			if (is_array($_REQUEST[$efield_varname])) {
				$tmp = array();
				foreach($_REQUEST[$efield_varname] AS $row) {
					if ($row <> "" && $row != "{{{null}}}") {
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
			$add_to_journal = "Flextable record " . $_REQUEST['recordid'] . " saved";

			if ($added_record && $efield_type == "diary" && $efield_form_value != "") {
				$efield_form_value = serialize(array(array(date('U'), $GLOBALS['USERID'], $efield_form_value)));
			} elseif ($efield_type == "diary" && $efield_form_value != "") {
				UpdateDiaryField($_REQUEST['recordid'], $efield_id, $dummy, $efield_form_value, $_REQUEST['commenthash']);
				continue;
			} elseif ($efield_type == "diary") {
				continue;
			}

			
			if ($efield_type == "date" && $efield_form_value != "") {
				$efield_form_value = FormattedDateToNLDate($efield_form_value);
			} elseif ($efield_type == "date/time") {
				$efield_form_value = FormattedDateTimeToSQLDateTime($efield_form_value);
				if ($efield_form_value == "") {
					$efield_form_value = "0000-00-00 00:00:00";
				}
			}
			if (isset($_REQUEST[$efield_varname]) && ValidateFieldInput(str_replace("EFID", "", $efield_varname), $efield_form_value, false,true,$_REQUEST['recordid']) != $efield_form_value) {
				print "Input check failed for $efield_varname. Reverting to old value.<br>";
				log_msg("ERROR: Input check failed for $efield_varname; $efield_form_value didn't validate. Reverting to old value. Reason: " . ValidateFieldInput(str_replace("EFID", "", $efield_varname), $efield_form_value, false,true,$_REQUEST['recordid']));
				$efield_form_value = $efield_curr_value;
			}
			if ($efield_form_value . " " == $efield_curr_value . " ") {
				qlog(INFO, "Field " . $efield_varname . " left alone, no change in value. $efield_form_value == $efield_curr_value");
				continue;
			} elseif (!array_key_exists($efield_varname, $_REQUEST)) {
				qlog(INFO, "Ignoring extra field " . $efield_varname . " - it was not posted to the webserver");
				continue;
			} elseif (($efield_form_value == "") && ($efield_curr_value != "")) {
				$efield_sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . " SET EFID" . $efield_id . "='' WHERE recordid='" . mres($_REQUEST['recordid']) . "'";
				$add_to_journal .= "\n" . $efield_name . " updated from [" . FunkifyLOV($efield_curr_value) . "] to *nothing*";
				DataJournal($_REQUEST['recordid'], $efield_curr_value, "", $efield_id, $st);
				qlog(INFO, "Field " . $efield_varname . " was emptied.");
			} elseif ($efield_curr_value != "") {
				$efield_sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . " SET EFID" . $efield_id . "='" . mres($efield_form_value) . "' WHERE recordid='" . mres($_REQUEST['recordid']) . "'";
				$add_to_journal .= "\n" . $efield_name . " updated from [" . FunkifyLOV($efield_curr_value) . "] to [" . FunkifyLOV($efield_form_value) . "]";
				DataJournal($_REQUEST['recordid'], $efield_curr_value, $efield_form_value, $efield_id, $st);
				qlog(INFO, "Field " . $efield_varname . " was updated.");
			} else {
				$efield_sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . " SET EFID" . $efield_id . "='" . mres($efield_form_value) . "' WHERE recordid='" . mres($_REQUEST['recordid']) . "'";
				$add_to_journal .= "\n" . $efield_name . " updated from *nothing* to [" . FunkifyLOV($efield_form_value) . "]";
				DataJournal($_REQUEST['recordid'], "", $efield_form_value, $efield_id, $st);
				qlog(INFO, "Field " . $efield_varname . " was updated.");
			}
			mcq($efield_sql, $db);

			journal($_REQUEST['recordid'], $add_to_journal, "flextable" . $st);
			$GLOBALS['ChangeLogLastSave'] = $add_to_journal;


			$efield_trigger_varname = "EFID" . $efield_id;
			$efield_trigger_ent_id = $_REQUEST['recordid'];
			$efield_trigger_field_val = $efield_form_value;
			array_push ($efield_trigger_varnames, $efield_trigger_varname);
			array_push ($efield_trigger_ent_ids, $efield_trigger_ent_id);
			array_push ($efield_trigger_field_vals, $efield_trigger_field_val);
		}

		ClearAccessCache($_REQUEST['recordid'],"ft" . $st, "all");

		FindAndRecalculateAllRelatedRecords($_REQUEST['recordid'], $st);
	
		journal($_REQUEST['recordid'], "Flextablerecord " . $_REQUEST['recordid'] . " saved", "flextable" . $st);

		# >X - Process the extra field triggers, if any.
		#
		for ($trigger_count = 0; $efield_trigger_varnames[$trigger_count]; $trigger_count++) {
			$efield_trigger_varname = $efield_trigger_varnames[$trigger_count];
			$efield_trigger_ent_id = $efield_trigger_ent_ids[$trigger_count];
			$efield_trigger_field_val = $efield_trigger_field_vals[$trigger_count];
			qlog(INFO, "trigger call $efield_trigger_varname, $efield_trigger_ent_id, $efield_trigger_field_val");
//			qlog(INFO, "Kick ProcessTriggers ; $efield_trigger_varname, $efield_trigger_ent_id, $efield_trigger_field_val, false, $st");
			ProcessTriggers($efield_trigger_varname, $efield_trigger_ent_id, $efield_trigger_field_val, false, $st);

		}

		if ($added_record) {
			qlog(INFO, "Calling trigger subsystem... $st (ADD)");
//			ProcessTriggers("FlexTable" . $st . "-Add",$_REQUEST['recordid'],"",false,$st);
			AddToDo("FlexTable" . $st . "-Add", "", $_REQUEST['recordid']);
		} else {

			qlog(INFO, "Calling trigger subsystem... (CHANGE)");
			//ProcessTriggers("FlexTable" . $st . "-Change",$_REQUEST['recordid'],"",false,$st);
			AddToDo("FlexTable" . $st . "-Change", "", $_REQUEST['recordid']);
		}

		if ($_REQUEST['e_button']) {
			$x = GetAllButtons($_REQUEST['e_button']);
			if ($x[0]['fieldtype'] == "Button") {

				// So, a button was pressed (and the user has the rights to press it)
					qlog(INFO, "An extra field button was pressed. Processing triggers.");
					ProcessTriggers("ButtonPress" . $_REQUEST['e_button'],$_REQUEST['recordid'],"",false, $st);
					journal($_REQUEST['recordid'], "User pressed button " . $x[0]['id'] . "::" . $x[0]['name'], "flextable" . $st);
					// Since a button was pressed, we don't go back to the list but stay here
					$stayhere = true;
					$_REQUEST['EditRecord'] = $_REQUEST['recordid'];
					$_REQUEST['FlexTable'] = $st;
					$tmp = GetAttribute("extrafield", "BackToListAfterSave", $x[0]['id']);
					
					if ($tmp == "Yes") {
						$gobacktolist = true;
					
					} elseif ($tmp == "No") {
						$gobacktolist = false;
					
					} 
			} else {
				qlog(INFO, "Submitted button field is not a button, not triggering! (" . $_REQUEST['e_button'] . ")");
				qlog(INFO, $x[0]);
			}
		} else {
			qlog(INFO, "No buttons in submit, not triggering for buttonse!");
		}

		$Todo = GetTodos($_REQUEST['recordid']);
		foreach($Todo AS $do) {
			if ($do['onchange'] == "FlexTable" . $st . "-Change" || $do['onchange'] == "FlexTable" . $st . "-Add" || GetExtraFieldTableType(str_replace("EFID", "", $do['onchange'])) == $st) {
				ProcessTriggers($do['onchange'],$_REQUEST['recordid'],$do['to_value'],false,$st);
				DropTodos($_REQUEST['recordid'], $do['onchange']);
				qlog(INFO, "TODO ProcessTrigger: " . $do['onchange'] . " value " . $do['to_value']);
			} else {
				qlog(INFO, "WRONG TODO ProcessTrigger: " . $do['onchange'] . " value " . $do['to_value'] . " type " . GetExtraFieldTableType(str_replace("EFID", "", $do['onchange'])));
			}
		}

	} // end if !delete


	$_REQUEST['keeplocked'] = "";
	RemoveLocks(false, "flextable" . $st);



	$_REQUEST['ShowTable'] = $st;
	if ($_REQUEST['SelectField']) {
			if ($_REQUEST['PlainField']!="") {
				?>
				<script type="text/javascript">
					<?php
					$tmp = GetAttribute("extrafield", "BlindReferenceFieldLayout", str_replace("EFID", "", $_REQUEST['SelectField']));
					if ($tmp != "" && $tmp != "-- set blind reference field layout in extra field attributes --") {
						$tag = ParseFlextableTemplate($st, $_REQUEST['recordid'], $tmp, true, true, false, "htme")  . "";
					} else {
						$tag = GetParsedFlexRef($st, $_REQUEST['recordid'], true) . "";
					}
					print "PutReferInFlextableForm('JS_" . $_REQUEST['SelectField'] . "','JS_" . $_REQUEST['SelectField'] . "ts','" . $_REQUEST['recordid'] . "','" . htme($tag) . "');";
					?>
					parent.$.fancybox.close();
				</script>
				<?php
				
			} else {
			?>
			<script type="text/javascript">
				<?php
				$tmp = GetAttribute("extrafield", "BlindReferenceFieldLayout", str_replace("EFID", "", $_REQUEST['SelectField']));
				if ($tmp != "" && $tmp != "-- set blind reference field layout in extra field attributes --") {
					$tag = ParseFlextableTemplate($st, $_REQUEST['recordid'], $tmp, true, true, false, "htme");
				} else {
					$tag = GetParsedFlexRef($st, $_REQUEST['recordid'], true);
				}
				print "SelectField(parent.document.getElementById('JS_" . $_REQUEST['SelectField'] . "'),'" . $_REQUEST['recordid'] . "','" . htme($tag) . "');";
				?>
				parent.$.fancybox.close();
			</script>
			<?php
			}

	} elseif ($_REQUEST['AddInPopup']) {
			
			?>
			<script type="text/javascript">
				<?php
					if (strlen($_REQUEST['ParentAjaxHandler']) > 1) {
					?>
					// 2
					parent.refresh_<?php echo $_REQUEST['ParentAjaxHandler'];?>();
    
					<?php
				}

				if (!$stayhere) {
					?>
		
				parent.$.fancybox.close();
					<?php
				}
					?>
				
			</script>
			<?php
			if (!$stayhere) {
				EndHTML();
				exit;
			} else {

				$_REQUEST['EditRecord'] = $_REQUEST['recordid'];
				$_REQUEST['FlexTable'] = $st;

			}
	}



		if ($_REQUEST['AddInPopup']) {


			?>
			<script type="text/javascript">
				<?php
					if (strlen($_REQUEST['ParentAjaxHandler']) > 1) {
					?>
					// 3
					parent.refresh_<?php echo $_REQUEST['ParentAjaxHandler'];?>();
					<?php
				}
			
					if (!$stayhere) {
					?>
						
						parent.$.fancybox.close();
					<?php
				} else {

					$_REQUEST['EditRecord'] = $_REQUEST['recordid'];
					$_REQUEST['FlexTable'] = $st;
					$stay = true;
				}
					?>
			</script>
			<?php
			if (!$stay) {
				EndHTML();
				exit;
			}
		} elseif ($gobacktolist && !$_REQUEST['refer']) {
		?>
			<script type="text/javascript">

				document.location='<?php echo "flextable.php?ShowTable=" . $st . "&Pag_Moment=" . htme($_REQUEST['Pag_Moment']) . "&FilterTable=" . htme($_REQUEST['FilterTable']);?>';


			</script>
		<?php
		} elseif ($_REQUEST['fromlist'] && !$stayhere) {

		?>
			<script type="text/javascript">


				document.location='<?php echo base64_decode($_REQUEST['fromlist']) . "&Pag_Moment=" . htme($_REQUEST['Pag_Moment']) . "&FilterTable=" . htme($_REQUEST['FilterTable']);?>';

			</script>
			<?php

		} elseif ($_REQUEST['refer'] && !$stayhere) {

			$ftd = GetFlexTableDefinitions($_REQUEST['flextableid']);
			if ($ftd[0]['refers_to'] == "entity") {
				?>
				<script type="text/javascript">
					document.location='edit.php?e=<?php echo $_REQUEST['refer'];?>';
				</script>
				<?php
			} elseif ($ftd[0]['refers_to'] == "customer") {
				?>
				<script type="text/javascript">
					document.location='customers.php?editcust=1&custid=<?php echo $_REQUEST['refer'];?>';
				</script>
				<?php
			} else {
				$ft = str_replace("flextable", "", $ftd[0]['refers_to']);
				?>
				<script type="text/javascript">
					document.location='flextable.php?EditRecord=<?php echo $_REQUEST['refer'];?>&FlexTable=<?php echo $ft;?>';
				</script>
				<?php
			}
			
			
	} elseif (!$stayhere) {

		?>
			<script type="text/javascript">
				document.location='flextable.php?ShowTable=<?php echo $st . "&Pag_Moment=" . htme($_REQUEST['Pag_Moment']) . "&FilterTable=" . htme($_REQUEST['FilterTable']);?>';
			</script>
		<?php

		} else {
			$_REQUEST['EditRecord'] = $_REQUEST['recordid'];
			$_REQUEST['FlexTable'] = $st;
		}
}


if (is_numeric($_REQUEST['EditRecord'])) {
	//DA(CheckFlextableRecordAccess($_REQUEST['FlexTable'], $_REQUEST['EditRecord']));
	if ($_REQUEST['EditRecord'] == "0") {
		PrintAD("ERROR: You're working with record 0 (zero), which is illegal.");
		EndHTML();
		exit;
	}
	if ($_REQUEST['refer'] == "") $_REQUEST['refer'] = false;

	if (CheckFlextableRecordAccess($_REQUEST['FlexTable'], $_REQUEST['EditRecord']) != "nok") {
		if (IsValidFlexTableRecord($_REQUEST['EditRecord'], $_REQUEST['FlexTable'])) {

			journal($_REQUEST['EditRecord'], "Flextable record viewed/edited", "flextable" . $_REQUEST['FlexTable']);
			print ParseTemplateCleanUp(ParseFlexTableForm($_REQUEST['FlexTable'], $_REQUEST['EditRecord'], $_REQUEST['refer'], $_REQUEST['templateRecord']));
	
		} else {
			PrintAD("This is not a valid FlexTable record (doesn't exist!)");
		}
		unset($_REQUEST['ShowTable']);
	} elseif (CheckFlexTableAccess($_REQUEST['FlexTable']) == "readonly") {
		if (IsValidFlexTableRecord($_REQUEST['EditRecord'], $_REQUEST['FlexTable'])) {

			journal($_REQUEST['EditRecord'], "Flextable record viewed (readonly)", "flextable" . $_REQUEST['FlexTable']);
			print ParseTemplateCleanUp(ParseFlexTableForm($_REQUEST['FlexTable'], $_REQUEST['EditRecord'], $_REQUEST['refer'], $_REQUEST['templateRecord']));
		} else {
			PrintAD("This is not a valid FlexTable record (doesn't exist!)");
		}
		unset($_REQUEST['ShowTable']); 
	} else {
		PrintAD("You're not allowed to edit records in this table. Check result: " . CheckFlexTableAccess($_REQUEST['FlexTable']));
	}

} elseif (is_numeric($_REQUEST['AddToTable'])) {
	if (CheckFlexTableAccess($_REQUEST['AddToTable']) == "ok") {
		$form = db_GetValue("SELECT formid FROM " . $GLOBALS['TBL_PREFIX'] . "flextabledefs WHERE recordid='" . mres($_REQUEST['AddToTable']) . "'");
		$max_set = "";
		if (is_numeric($_REQUEST['refer'])) {
			$max_per_parent = GetAttribute("flextable", "MaxNumOfRecordsPerParentRecord", $_REQUEST['AddToTable']);

			if (is_numeric($max_per_parent) && $max_per_parent > 0) {
				$cnt = db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . mres($_REQUEST['AddToTable']) . " WHERE refer='" . mres($_REQUEST['refer']) . "' AND deleted!='y'");
				$max_set = true;
			}
		}
		if ($cnt >= $max_per_parent && $max_set) {
			PrintAD("You're not allowed to add records for this master record to this table. The maximum allowed number of child records cannot be exceeded. Maximum is " . $max_per_parent . ", current is " . $cnt. ".");
		} elseif (is_array($GLOBALS['UC']['ALLOWEDADDFORMS']) && !in_array($form,$GLOBALS['UC']['ALLOWEDADDFORMS']) && (!is_administrator())) {
			PrintAD("You're not allowed to add records to this table (no suitable form found).");
		} else {
			print ParseTemplateCleanUp(ParseFlexTableForm($_REQUEST['AddToTable'], "_new_", $_REQUEST['refer'], $_REQUEST['templateRecord']));
			unset($_REQUEST['ShowTable']);
		}
	} else {
		PrintAD("You're not allowed to add records to this table. Check result: " . CheckFlexTableAccess($_REQUEST['AddToTable']));
	}

}

if (is_numeric($_REQUEST['ShowTable'])) {
	print "<div id=\"MainFlextableList\">";
	print AjaxBox("ReturnCompleteFlextable", true, "&ShowTable=" . $_REQUEST['ShowTable'] . "&Pag_Moment=" . htme($_REQUEST['Pag_Moment']) . "&FilterTable=" . htme($_REQUEST['FilterTable']));
	print "</div>";
} elseif ($_REQUEST['ImportIntoTable']) {
	MustBeAdmin();
	AdminTabs("ft");
	if ($_REQUEST['DataInStash']) {
		$_REQUEST['ImportBody'] = PopStashValue($_REQUEST['DataInStash']);
		$_REQUEST['DirectLoad'] = "Yes";
	}
	$tabledef = GetFlexTableDefinitions($_REQUEST['ImportIntoTable']);
	print "<table style='width: 75%;'><tr><td>&nbsp;&nbsp;</td><td><fieldset><legend>Import data into table '" . $tabledef[0]['tablename'] . "'</legend><table style='width: 75%;'>";
	if ($_REQUEST['ImportArray'] || ($_REQUEST['ImportBody'] && $_REQUEST['DirectLoad'] == "Yes")) {

		if ($_REQUEST['ImportBody'] && $_REQUEST['DirectLoad'] == "Yes") {
			$rb = explode("\n", $_REQUEST['ImportBody']);
			$_REQUEST['ImportArray'] = array();
			foreach ($rb AS $row) {
				$r = explode("\t", $row);
				foreach($r AS $element) {
					array_push($_REQUEST['ImportArray'], $element);
				}
			}
		}
		unset($_REQUEST['ImportBody']);

		$IA = $_REQUEST['ImportArray'];
		$IE = $_REQUEST['EidArray'];
		$fields = GetExtraFlexTableFields($_REQUEST['ImportIntoTable']);

		$numoffields = sizeof($fields);

		$x = 0;
		$totttt = sizeof($IA);
		for ($i=0;$i<$totttt;$i+=$numoffields) {
			$st = $_REQUEST['ImportIntoTable'];
			$c = 0;
			$udquery = "";
			if ($tabledef[0]['orientation'] == "one_entity_to_many") {
				mcq("INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . "(refer,readonly,deleted) VALUES('" . mres($_POST['FLEXTABLEREFERFIELD']) . "','no','n')", $db);
			} else {
				mcq("INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . "(refer,readonly,deleted) VALUES(0,'no','n')", $db);
			}

			$record = mysql_insert_id();
	
			$udquery = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . " SET recordid=" . mres($record);

			for ($p=$i;$p<$i+$numoffields;$p++) {
				$udquery .= ", EFID" . $fields[$c]['id'] . "='" . mres(trim(str_replace("###SEMICOLON###", ";" , $IA[$p]))) . "'";
				unset($IA[$p]);
				$c++;
			}

			$udquery .= " WHERE recordid=" . $record;

			mcq($udquery, $db);
			$x++;

		}
		print $x . " records imported";

		if ($_REQUEST['Msg']) {
			print "<br><br >";
			print "The table is created, as are the extra fields in it. A form was also created and all records were imported<br>";
			print "You now need to set some <a class='plainlink' href='flextable.php?EditFlexTable=" . $_REQUEST['ImportIntoTable'] . "'>FlexTable settings and permissons</a> and off you go!<br>";
		}

		// Array ImportArray contains one big list of values. We'll devide this array by the numbers of
		// fields in the table

	} elseif ($_REQUEST['ImportBody']) {
		print "<tr><td>Please check your data.</td></tr>";
		print "<tr><td><form id='ImportData' method='post' action=''><div class='showinline'>";
		print "<input type='hidden' name='ImportIntoTable' value='" . $_REQUEST['ImportIntoTable'] ."'>";
		print "<table class='crm' style='width: 100%;'><tr>";
		if ($tabledef[0]['orientation'] == "one_entity_to_many") {
			print "<td>Refer to eid</td>";
		}
		foreach (GetExtraFlexTableFields($_REQUEST['ImportIntoTable']) AS $field) {
			print "<td>" . $field['name'] . "</td>";
		}
		print "</tr>";

		$rb = $_REQUEST['ImportBody']; // Save memory on variable name alone

		$rb = explode("\n", $rb);
		foreach ($rb AS $row) {
			print "<tr>";
			$el = explode(";", $row);
			if ($tabledef[0]['orientation'] == "one_entity_to_many" && $el[0] <> "") {
				//print "<td>REF" . $el[0] . "</td>";
				print "<td><select name='EidArray[]'>";
				$sql = "SELECT CONCAT(eid,': ', category) AS ccat, eid FROM " . $GLOBALS['TBL_PREFIX'] . "entity ORDER BY eid";
				$res = mcq($sql, $db);
				while ($row = mysql_fetch_array($res)) {
					if ($el[0] == $row['eid']) {
						$ins="selected='selected'";
					} else {
						unset($ins);
					}
					print "<option " . $ins . " value='" . $row['eid'] . "'>" . htme($row['ccat']) . "</option>";
				}
				print "</select></td>";
				unset($el[0]);
			}
				$count=0;
			foreach ($el AS $element) {

				$ts = GetExtraFlexTableFields($_REQUEST['ImportIntoTable']);

				if (strstr($ts[$count]['fieldtype'], "User-list")) {
					if (!is_numeric($element)) {
						$num = GetUserID($element);
						if (!is_numeric($num)) {
							$num = GetUserIDByFullname($element);

						}
						if (!is_numeric($num)) {
							$element = "NO USER REF FOUND";
						} else {
							$element = $num;
						}
					}
				}
				$count++;
				if (1==1) {
					if ($element == "") {
						$element = " ";
					}
					print "<td><input type='text' name='ImportArray[]' value='" . htme($element) . "'></td>";
				}
			}
			print "</tr>";
		}
		print "</table>";
		print "<br><input type='submit' name='importbutton' value='Import!'>";
		print "</div></form>";

		print "</td></tr>";



	} else {
		print "<tr><td>Make sure your record layout is as follows:</td></tr><tr><td><br>";
		if ($tabledef[0]['orientation'] == "one_entity_to_many") {
			print "Entity ID";
			$done = true;
		}
		foreach (GetExtraFlexTableFields($_REQUEST['ImportIntoTable']) AS $field) {
			if (!$done) {
				print $field['name'];
				$done = true;
			} else {
				print ";" . $field['name'];
			}
		}
		print "<br><br></td></tr>";
		print "<tr><td><form id='ImportData' method='post' action=''><div class='showinline'>";
		print "<input type='hidden' name='ImportIntoTable' value='" . $_REQUEST['ImportIntoTable'] ."'>";
		print "<textarea rows='3' cols='100' name='ImportBody'>" . $lang['paste'] . "</textarea>";
		print "<input type='submit' name='importbutton' value='Import!'>";
		print "<br>Check this box to import directly without on-screen check: <input type='checkbox' name='DirectLoad' value='Yes'>";
		print "</div></form>";
		print "</td></tr>";
	}
	print "</table></fieldset>";
	print "</td></tr></table>";

} elseif ($_REQUEST['TableAdmin'] || $_REQUEST['EditFlexTable'] || $_REQUEST['DeleteFlexTable'] || $_REQUEST['EditFlexTableFormID'] || $_REQUEST['TruncateFlexTable'] || $_REQUEST['PurgeFlexTable']) {

	MustBeAdmin();
	AdminTabs("ft");
	AddBreadCrum("Flextable definitions");



	if ($_REQUEST['TruncateFlexTable']) {
		if (!$_REQUEST['Sure']) {
			print "<strong>Truncate FlexTable " . htme($_REQUEST['TruncateFlexTable']) . " " . htme(GetFlextableName($_REQUEST['TruncateFlexTable'])) . " - All data in this table will be lost, including journal information and attachments.</strong><br><br>Are you sure? <a href='flextable.php?TruncateFlexTable=" . htme($_REQUEST['TruncateFlexTable']) . "&amp;Sure=yes' class='plainlink'>Yes, truncate this table.</a> (no way back)";
			EndHTML();
			exit;
		} else {
			print "<strong>FlexTable " . htme($_REQUEST['TruncateFlexTable']) . " was truncated.</strong><br><br>";
			$sql = "TRUNCATE TABLE " . $GLOBALS['TBL_PREFIX'] . "flextable" . $_REQUEST['TruncateFlexTable'] . "";
			mcq($sql, $db);
			$sql = "DELETE " . $GLOBALS['TBL_PREFIX'] . "binfiles.*, " . $GLOBALS['TBL_PREFIX'] . "blobs.* FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles, " . $GLOBALS['TBL_PREFIX'] . "blobs WHERE " . $GLOBALS['TBL_PREFIX'] . "binfiles.fileid=" . $GLOBALS['TBL_PREFIX'] . "blobs.fileid AND " . $GLOBALS['TBL_PREFIX'] . "binfiles.type='flextable" . mres($_REQUEST['TruncateFlexTable']) . "'";
			mcq($sql, $db);
			$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "journal WHERE type='flextable" . mres($_REQUEST['TruncateFlexTable']) . "'";
			mcq($sql, $db);
		}
	} elseif ($_REQUEST['PurgeFlexTable']) {
		if (!$_REQUEST['Sure']) {
			print "<strong>Purge FlexTable " . $_REQUEST['PurgeFlexTable'] . " - All prior deleted records in table will be physically deleted!</strong><br><br>Are you absolutely sure? <a href='flextable.php?PurgeFlexTable=" . $_REQUEST['PurgeFlexTable'] . "&amp;Sure=yes' class='plainlink'>Yes, purge deleted records in this table.</a> (no way back)";
			EndHTML();
			exit;
		} else {
			print "<strong>FlexTable " . $_REQUEST['PurgeFlexTable'] . " was purged.</strong><br><br>";
			$listofids = db_GetFlatArray("SELECT recordid FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $_REQUEST['PurgeFlexTable'] . " WHERE deleted='y'");

			foreach ($listofids AS $id) {
				$sql = "DELETE " . $GLOBALS['TBL_PREFIX'] . "binfiles.*, " . $GLOBALS['TBL_PREFIX'] . "blobs.* FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles, " . $GLOBALS['TBL_PREFIX'] . "blobs WHERE " . $GLOBALS['TBL_PREFIX'] . "binfiles.fileid=" . $GLOBALS['TBL_PREFIX'] . "blobs.fileid AND " . $GLOBALS['TBL_PREFIX'] . "binfiles.type='flextable" . mres($_REQUEST['PurgeFlexTable']) . "' AND " . $GLOBALS['TBL_PREFIX'] . "binfiles.koppelid=" . $id;
				mcq($sql, $db);
				
				$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "journal WHERE type='flextable" . mres($_REQUEST['TruncateFlexTable']) . "' AND eid=" . $id;
				mcq($sql, $db);
			}
			$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $_REQUEST['PurgeFlexTable'] . " WHERE deleted='y'";
			mcq($sql, $db);
		}
	} elseif ($_GET['DeleteFlexTable']) {
		$ViewOnTable = GetAttribute("flextable", "ViewOnTable", $_GET['DeleteFlexTable']);
		if ($ViewOnTable != "") {
			$sql = "DROP VIEW " . $GLOBALS['TBL_PREFIX'] . "flextable" . mres($_GET['DeleteFlexTable']) ."";
			mcq($sql, $db);
			
			print "<span class=\"noway\">View dropped</span>.";
			if ($_REQUEST['Recreate'] == 1) {
				$ViewOnTableSelectCondition = GetAttribute("flextable", "ViewOnTableSelectCondition", $_GET['DeleteFlexTable']);
				if ($ViewOnTableSelectCondition != "" && $ViewOnTableSelectCondition != "{{none}}") {
					$extra = " WHERE " . $ViewOnTableSelectCondition;
				} else {
					$extra = "";
				}
				mcq("CREATE VIEW " . $GLOBALS['TBL_PREFIX'] . "flextable" . mres($_GET['DeleteFlexTable']) . " AS SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . mres($ViewOnTable) . $extra, $db);
				print "<br><span class=\"noway\">View created</span>.<br>";
			}
		} elseif (!$_REQUEST['Sure']) {
			print "<strong>Delete FlexTable " . $_REQUEST['DeleteFlexTable'] . " - All data in this table will be lost!</strong><br><br>Are you absolutely sure? <a href='flextable.php?DeleteFlexTable=" . $_REQUEST['DeleteFlexTable'] . "&amp;Sure=yes' class='plainlink'>Yes, delete this table.</a> (no way back)";
			EndHTML();
			exit;
		} else {
			print "<strong>FlexTable " . $_REQUEST['DeleteFlexTable'] . " was deleted, including attachments, the field definitions and the field values. Forms were preserved.</strong><br><br>";
			$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "flextabledefs WHERE recordid='" . mres($_GET['DeleteFlexTable']) ."'";
			mcq($sql, $db);
			$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE tabletype='" . mres($_GET['DeleteFlexTable']) ."'";
			mcq($sql, $db);
			$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "attributes WHERE entity='" . mres($_GET['DeleteFlexTable']) ."' AND identifier='flextable'";
			mcq($sql, $db);
			$sql = "DROP TABLE " . $GLOBALS['TBL_PREFIX'] . "flextable" . mres($_GET['DeleteFlexTable']) ."";
			mcq($sql, $db);

			$sql = "DELETE " . $GLOBALS['TBL_PREFIX'] . "binfiles.*, " . $GLOBALS['TBL_PREFIX'] . "blobs.* FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles, " . $GLOBALS['TBL_PREFIX'] . "blobs WHERE " . $GLOBALS['TBL_PREFIX'] . "binfiles.fileid=" . $GLOBALS['TBL_PREFIX'] . "blobs.fileid AND " . $GLOBALS['TBL_PREFIX'] . "binfiles.type='flextable" . mres($_REQUEST['DeleteFlexTable']) . "'";
			mcq($sql, $db);
		}

	} elseif ($_POST['EditFlexTableFormID']) {
		if ($_POST['table_layout']) {
			$table_layout = serialize($_POST['table_layout']);
		}
		if ($_REQUEST['sumnumrows'] != "y") {
			$_REQUEST['sumnumrows'] = "n";
		}
		if ($_REQUEST['showfilters'] != "y") {
			$_REQUEST['showfilters'] = "n";
		}

		if ($_REQUEST['exclude_from_rep'] != "y") {
			$_REQUEST['exclude_from_rep'] = "n";
		}
		
	
		if ($_REQUEST['compact_view'] != "y") {
			$_REQUEST['compact_view'] = "n";
		}
		if ($_REQUEST['add_in_popup'] != "y") {
			$_REQUEST['add_in_popup'] = "n";
		}
		if ($_REQUEST['skip_security'] != "y") {
			$_REQUEST['skip_security'] = "n";
		}
		if ($_REQUEST['users_may_select_columns'] != "y") {
			$_REQUEST['users_may_select_columns'] = "n";
		}



		if (is_numeric($_POST['EditFlexTableFormID'])) {

			$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "flextabledefs SET sort_on='" . mres($_REQUEST['sort_on']) . "',sort_direction='" . mres($_REQUEST['sort_direction']) . "',access_controlled_by_field='" . mres($_REQUEST['access_controlled_by_field']) . "', access_denied_method='" . mres($_REQUEST['access_denied_method']) . "', refers_to='" . mres($_POST['refers_to']) . "',tablename='" . mres($_POST['tablename']) . "',formid='" . mres($_POST['formid']) . "', refer_field_layout='" . mres($_POST['refer_field_layout']) . "',table_layout='" . mres($table_layout) . "',maxrowsperpage='" . mres($_REQUEST['maxrowsperpage']) . "', headerhtml='" . mres($_REQUEST['headerhtml']) . "', sumnumrows='" . mres($_REQUEST['sumnumrows']) . "',showfilters='" . mres($_REQUEST['showfilters']) . "',exclude_from_rep='" . mres($_REQUEST['exclude_from_rep']) . "', addlinktext='" . mres($_REQUEST['addlinktext']) . "', compact_view='" . mres($_REQUEST['compact_view']) . "', add_in_popup='" . mres($_REQUEST['add_in_popup']) . "',tableheaderrepeat='" . mres($_REQUEST['tableheaderrepeat']) . "', skip_security='" . mres($_REQUEST['skip_security']) . "',users_may_select_columns='" . mres($_REQUEST['users_may_select_columns']) . "', stayinformaftersave='" . mres($_REQUEST['stayinformaftersave']) . "' WHERE recordid='" . mres($_POST['EditFlexTableFormID']) . "'";
			mcq($sql, $db);

			unset($GLOBALS['PageCache']);


//			print $sql;

		} elseif ($_POST['EditFlexTableFormID'] == "new") {
			if ($_POST['refer_field_layout'] == "@EID@: @CATEGORY@" && $_POST['refers_to'] == "customer") {
				$_POST['refer_field_layout'] = "@CID@: @CUSTOMER@";
			}
			$sql = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "flextabledefs(refers_to,tablename,formid,refer_field_layout,orientation, table_layout, maxrowsperpage, headerhtml,sumnumrows,addlinktext,showfilters,exclude_from_rep,skip_security,users_may_select_columns,stayinformaftersave) VALUES('" . mres($_POST['refers_to']) . "','" . mres($_POST['tablename']) . "','" . mres($_POST['formid']) . "','" . mres($_POST['refer_field_layout']) . "','" . mres($_POST['orientation']) . "','" . mres($table_layout) . "','" . mres($_REQUEST['maxrowsperpage']) . "','" . mres($_REQUEST['headerhtml']) . "','" . mres($_REQUEST['sumnumrows']) . "','" . mres($_REQUEST['addlinktext']) . "','" . mres($_REQUEST['showfilters']) . "','" . mres($_REQUEST['exclude_from_rep']) . "','" . mres($_REQUEST['skip_security']) . "','" . mres($_REQUEST['users_may_select_columns']) . "','" . mres($_REQUEST['stayinformaftersave']) . "')";
			mcq($sql, $db);
			$newtable = mysql_insert_id();

			if ($_REQUEST['ViewOnTable'] != "") {
				
				SetAttribute("flextable", "ViewOnTable", $_REQUEST['ViewOnTable'], $newtable);
				$ViewOnTableSelectCondition = GetAttribute("flextable", "ViewOnTableSelectCondition", $_REQUEST['ViewOnTable']);
				if ($ViewOnTableSelectCondition != "" && $ViewOnTableSelectCondition != "{{none}}") {
					$extra = " WHERE " . $ViewOnTableSelectCondition;
				} else {
					$extra = "";
				}
				mcq("CREATE VIEW " . $GLOBALS['TBL_PREFIX'] . "flextable" . $newtable . " AS SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . mres($_REQUEST['ViewOnTable']) . " " . $extra, $db);
				print "View created";
			} else {
				mcq("CREATE TABLE " . $GLOBALS['TBL_PREFIX'] . "flextable" . $newtable . " (recordid INT(11) NOT NULL auto_increment, refer INT(11) NOT NULL, readonly ENUM('no','yes') NOT NULL DEFAULT 'no', deleted ENUM('n','y') NOT NULL DEFAULT 'n', formid INT NOT NULL, timestamp_last_change TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (recordid)) ENGINE=MYISAM DEFAULT CHARSET=UTF8", $db);
			}

			$_GET['EditFlexTable'] = $newtable;
			unset($GLOBALS['PageCache']);

		}
		if ($_REQUEST['req_url']) {
		?>
			<script type="text/javascript">
				document.location='<?php echo base64_decode($_REQUEST['req_url']);?>';
			</script>
		<?php
			
		}
		

	}

	$arr = GetFlexTableDefinitions();

	if ($_GET['EditFlexTable'] == "new") {
		$arr = array();
		$arr[0]['recordid'] = "new";
	}
	if ($_GET['EditFlexTable']) {

		if (!is_numeric($_GET['EditFlexTable']) && $_GET['EditFlexTable'] != 'new') { // dirty security check
			PrintAD("Wrong input!");
			EndHTML();
			exit;
		}

		$ExtraSelectConditionWhenSelectingReferOptions = GetAttribute("flextable", "ExtraSelectConditionWhenSelectingReferOptions", $_GET['EditFlexTable']);
		if ($ExtraSelectConditionWhenSelectingReferOptions == "") {
			SetAttribute("flextable", "ExtraSelectConditionWhenSelectingReferOptions", "{{none}}", $_GET['EditFlexTable']);
		}

		// Attributes for inclusion in sysrtem-wide search
		$IncludeInSystemWideSearches = GetAttribute("flextable", "IncludeInSystemWideSearches", $_GET['EditFlexTable']);
		if ($IncludeInSystemWideSearches == "") {
			SetAttribute("flextable", "IncludeInSystemWideSearches", "Yes", $_GET['EditFlexTable'], array("Yes", "No"));
		}
		$DownloadRules = GetAttribute("flextable", "DownloadRules", $_GET['EditFlexTable']);
		if ($DownloadRules == "") {
			SetAttribute("flextable", "DownloadRules", "{{none}}", $_GET['EditFlexTable']);
		}

		$DeleteConfirmationMessage = GetAttribute("flextable", "DeleteConfirmationMessage", $_GET['EditFlexTable']);
		if ($DeleteConfirmationMessage == "") {
			SetAttribute("flextable", "DeleteConfirmationMessage", "{{none}}", $_GET['EditFlexTable']);
		}
		
		// Attributes for in-line forms
		$InlineFormFieldsToShow = GetAttribute("flextable", "InlineFormFieldsToShow", $_GET['EditFlexTable']);
		if ($InlineFormFieldsToShow == "") {
			SetAttribute("flextable", "InlineFormFieldsToShow", "1,2,3", $_GET['EditFlexTable']);
		}
		$InlineFormShowVerticalNumericTotal = GetAttribute("flextable", "InlineFormShowVerticalNumericTotal", $_GET['EditFlexTable']);
		if ($InlineFormShowVerticalNumericTotal == "") {
			SetAttribute("flextable", "InlineFormShowVerticalNumericTotal", "Yes", $_GET['EditFlexTable'], array("Yes", "No"));
		}
		$InlineFormShowHorizontalNumericTotal = GetAttribute("flextable", "InlineFormShowHorizontalNumericTotal", $_GET['EditFlexTable']);
		if ($InlineFormShowHorizontalNumericTotal == "") {
			SetAttribute("flextable", "InlineFormShowHorizontalNumericTotal", "Yes", $_GET['EditFlexTable'], array("Yes", "No"));
		}
		$InlineFormNumOfSpareLines = GetAttribute("flextable", "InlineFormNumOfSpareLines", $_GET['EditFlexTable']);
		if ($InlineFormNumOfSpareLines == "") {
			SetAttribute("flextable", "InlineFormNumOfSpareLines", "3", $_GET['EditFlexTable']);
		}
		$InlineFormHeaderHTML = GetAttribute("flextable", "InlineFormHeaderHTML", $_GET['EditFlexTable']);
		if ($InlineFormHeaderHTML == "") {
			SetAttribute("flextable", "InlineFormHeaderHTML", "<!-- no headerhtml //-->", $_GET['EditFlexTable']);
		}
		$InlineFormSaveButtonText = GetAttribute("flextable", "InlineFormSaveButtonText", $_GET['EditFlexTable']);
		if ($InlineFormSaveButtonText == "") {
			SetAttribute("flextable", "InlineFormSaveButtonText", $lang['save'], $_GET['EditFlexTable']);
		}

		$ShowSortLinks = GetAttribute("flextable", "ShowSortLinks", $_GET['EditFlexTable']);
		if ($ShowSortLinks == "") {
			SetAttribute("flextable", "ShowSortLinks", "Yes", $_GET['EditFlexTable'], array("Yes", "No"));
		}
		$ShowSelectionsWhenInline = GetAttribute("flextable", "ShowSelectionsWhenInline", $_GET['EditFlexTable']);
		if ($ShowSelectionsWhenInline == "") {
			SetAttribute("flextable", "ShowSelectionsWhenInline", "No", $_GET['EditFlexTable'], array("No", "Yes"));
		}
		$ShowSelectionsWhenNotInline = GetAttribute("flextable", "ShowSelectionsWhenNotInline", $_GET['EditFlexTable']);
		if ($ShowSelectionsWhenNotInline == "") {
			SetAttribute("flextable", "ShowSelectionsWhenNotInline", "Yes", $_GET['EditFlexTable'], array("No", "Yes"));
		}
		$UsePopupAlsoWhenViewingPlainList = GetAttribute("flextable", "UsePopupAlsoWhenViewingPlainList", $_GET['EditFlexTable']);
		if ($UsePopupAlsoWhenViewingPlainList == "") {
			SetAttribute("flextable", "UsePopupAlsoWhenViewingPlainList", "No", $_GET['EditFlexTable'], array("No", "Yes"));
		}
		$ShowInlineDeleteLink = GetAttribute("flextable", "ShowInlineDeleteLink", $_GET['EditFlexTable']);
		if ($ShowInlineDeleteLink == "") {
			SetAttribute("flextable", "ShowInlineDeleteLink", "Yes", $_GET['EditFlexTable'], array("No", "Yes"));
		}
		$ExtraSelectCondition = GetAttribute("flextable", "ExtraSelectCondition", $_GET['EditFlexTable']);
		if ($ExtraSelectCondition == "") {
			SetAttribute("flextable", "ExtraSelectCondition", "None", $_GET['EditFlexTable']);
		}
		$RemoveExtraSelectCondionFromQueryLinkText = GetAttribute("flextable", "RemoveExtraSelectCondionFromQueryLinkText", $_GET['EditFlexTable']);
		if ($RemoveExtraSelectCondionFromQueryLinkText == "") {
			SetAttribute("flextable", "RemoveExtraSelectCondionFromQueryLinkText", "None", $_GET['EditFlexTable']);
		}
		$AddExtraSelectCondionToQueryLinkText = GetAttribute("flextable", "AddExtraSelectCondionToQueryLinkText", $_GET['EditFlexTable']);
		if ($AddExtraSelectCondionToQueryLinkText == "") {
			SetAttribute("flextable", "AddExtraSelectCondionToQueryLinkText", "None", $_GET['EditFlexTable']);
		}


		$MaxNumOfRecordsPerParentRecord = GetAttribute("flextable", "MaxNumOfRecordsPerParentRecord", $_GET['EditFlexTable']);
		if ($MaxNumOfRecordsPerParentRecord == "") {
			SetAttribute("flextable", "MaxNumOfRecordsPerParentRecord", "n/a", $_GET['EditFlexTable']);
		}
		$ShowInlineDuplicateLink = GetAttribute("flextable", "ShowInlineDuplicateLink", $_GET['EditFlexTable']);
		if ($ShowInlineDuplicateLink == "") {
			SetAttribute("flextable", "ShowInlineDuplicateLink", "Yes", $_GET['EditFlexTable'], array("No", "Yes"));
		}
		$AllowReferChanges = GetAttribute("flextable", "AllowReferChanges", $_GET['EditFlexTable']);
		if ($AllowReferChanges == "") {
			SetAttribute("flextable", "AllowReferChanges", "No", $_GET['EditFlexTable'], array("Yes", "No"));
		}
		$DenyDownloads = GetAttribute("flextable", "DenyDownloads", $_GET['EditFlexTable']);
		if ($DenyDownloads == "") {
			SetAttribute("flextable", "DenyDownloads", "No", $_GET['EditFlexTable'], array("Yes", "No"));
		}

		$ReferFieldSelectInPopup = GetAttribute("flextable", "ReferFieldSelectInPopup", $_GET['EditFlexTable']);
		if ($ReferFieldSelectInPopup == "") {
			SetAttribute("flextable", "ReferFieldSelectInPopup", "No", $_GET['EditFlexTable'], array("Yes", "No"));
		}
		$ViewOnTable = GetAttribute("flextable", "ViewOnTable", $_GET['EditFlexTable']);
		if ($ViewOnTable == "") {
			SetAttribute("flextable", "ViewOnTable", "", $_GET['EditFlexTable']);
		}
		$ViewOnTableSelectCondition = GetAttribute("flextable", "ViewOnTableSelectCondition", $_GET['EditFlexTable']);
		if ($ViewOnTableSelectCondition == "") {
			SetAttribute("flextable", "ViewOnTableSelectCondition", "{{none}}", $_GET['EditFlexTable']);
		}
		
		$IncludeThisTableInSearchesFromParentTable = GetAttribute("flextable", "IncludeThisTableInSearchesFromParentTable", $_GET['EditFlexTable']);
		if ($IncludeThisTableInSearchesFromParentTable == "") {
			SetAttribute("flextable", "IncludeThisTableInSearchesFromParentTable", "Yes", $_GET['EditFlexTable'], array("Yes", "No"));
		}

		$IncludeParentTableInSearches = GetAttribute("flextable", "IncludeParentTableInSearches", $_GET['EditFlexTable']);
		if ($IncludeParentTableInSearches == "") {
			SetAttribute("flextable", "IncludeParentTableInSearches", "No", $_GET['EditFlexTable'], array("No", "Yes"));
		}

		$DontRecalculateConnectedTablesOnRecalc = GetAttribute("flextable", "DontRecalculateConnectedTablesOnRecalc", $_GET['EditFlexTable']);
		if ($DontRecalculateConnectedTablesOnRecalc == "") {
			SetAttribute("flextable", "DontRecalculateConnectedTablesOnRecalc", "No", $_GET['EditFlexTable'], array("No", "Yes"));
		}
		$DontRecalculateOnRecalcOfParent = GetAttribute("flextable", "DontRecalculateOnRecalcOfParent", $_GET['EditFlexTable']);
		if ($DontRecalculateOnRecalcOfParent == "") {
			SetAttribute("flextable", "DontRecalculateOnRecalcOfParent", "No", $_GET['EditFlexTable'], array("No", "Yes"));
		}

		$NoResultsMessage = GetAttribute("flextable", "NoResultsMessage", $_GET['EditFlexTable']); // {{language default}}
		if ($NoResultsMessage == "") {
			SetAttribute("flextable", "NoResultsMessage", "{{language default}}", $_GET['EditFlexTable']);
		}

		print PlainNav($to_tabs, $tabbs, $selected_tab);
		print "<table><tr><td>";
		print "<form id='EditFlexTableForm' method='post' action=''><div class='showinline'>";
		print "<input type='hidden' name='EditFlexTableFormID' value='" . $_GET['EditFlexTable'] . "'>";
		
			foreach ($arr AS $ar) {
				$ar['table_layout'] = unserialize($ar['table_layout']);
				if ($ar['recordid'] == $_GET['EditFlexTable']) {
					if ($ViewOnTable != "") {
//						print "<h1>View properties</h1>";
//						print "<h2>View " . $ar['recordid'] . ": " . htme($ar['tablename']) . " which is a view on flextable " . str_replace("flextable", "", $ViewOnTable) . "</h2>";
					} else {
//						print "<h1>Flextable properties</h1>";
//						print "<h2>Flextable " . $ar['recordid'] . ": " . htme($ar['tablename']) . "</h2>";
					}

					print "<input type='submit' value='Save'>&nbsp;" . AttributeLink("flextable", $ar['recordid']) . " &nbsp;&nbsp;<a class=\"arrow\" href=\"flextable.php?ShowTable=" . $ar['recordid'] . "\">Show table</a>";
					if ($_GET['EditFlexTable'] <> "new") {
						if ($ViewOnTable == "") {
							print "&nbsp;&nbsp;<a href='flextable.php?TruncateFlexTable=" . $_GET['EditFlexTable'] . "' class=\"arrow\">Empty (truncate)</a>&nbsp;";
						}
						$tmp = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $_GET['EditFlexTable'] . " WHERE deleted='y'");

						if ($tmp[0] > 0 && $ViewOnTable=="") {
							print "&nbsp;&nbsp;<a href='flextable.php?PurgeFlexTable=" . $_GET['EditFlexTable'] . "' class='arrow'>Purge deleted records (" . $tmp[0] . ")</a>&nbsp;&nbsp;";
						}
						if ($ViewOnTable != "") {
							print "&nbsp;&nbsp;<a href='flextable.php?DeleteFlexTable=" . $_GET['EditFlexTable'] . "' class='arrow'>Drop view (delete)</a>";
							print "&nbsp;&nbsp;<a href='flextable.php?DeleteFlexTable=" . $_GET['EditFlexTable'] . "&amp;Recreate=1' class='arrow'>Recreate view</a>";
						} else {
							print "&nbsp;&nbsp;<a href='flextable.php?DeleteFlexTable=" . $_GET['EditFlexTable'] . "' class='arrow'>Delete whole table</a>";
						}
					}
					//print "&nbsp;&nbsp;<a href='flextable.php?TableAdmin=true&navid=ft' class='arrow'>Back to flextable overview</a>";
					print "<br><br>";

					print "<table>";
					print "<tr class=\"nicerow\"><td>Table name</td><td><input type='text' name='tablename' value='" . htme($ar['tablename']) . "'></td></tr>";
					if ($_GET['EditFlexTable'] == "new") {
						print "<tr class=\"nicerow\"><td>Type</td><td><select name='ViewOnTable'>";
						print "<option value=''>Physical table</option>";


						if ($ViewOnTable == "entity") {
						
						}
						print "<option " . $ins . " value='entity'>View on entity table</option>";

						if ($ViewOnTable == "customer") {
							$ins = "selected='selected'";
						}
						print "<option " . $ins . " value='customer'>View on customer table</option>";

						foreach (GetFlextableDefinitions() AS $tmp) {

							if ($tmp['recordid'] != $_GET['EditFlexTable']) {
								if ($ar['refers_to'] == "flextable" . $tmp['recordid']) {
									$ins = "selected='selected' ";
								} else {
									$ins = "";
								}
								print "<option " . $ins . " value='flextable" . $tmp['recordid'] . "'>View on flextable " . htme($tmp['tablename']) . "</option>";
							}
						}
						print "</select></td></tr>";
					}
					print "<tr class=\"nicerow\"><td>Refers to</td><td><select name='refers_to'>";
					if ($ar['refers_to'] == "customer") {
						$ins = "selected='selected'";
						if ($ar['refer_field_layout'] == "") {
							$ar['refer_field_layout'] = "@CID@: @CUSTOMER@";
						}
					} elseif ($ar['refers_to'] == "entity") {
						if ($ar['refer_field_layout'] == "") {
							$ar['refer_field_layout'] = "@EID@: @CATEGORY@";
						}
					} elseif ($ar['refer_field_layout'] == "") {
						$ar['refer_field_layout'] = "Please set refer field! (check flextable)";
					}

					print "<option value='entity'>Entity table</option>";

					print "<option value='customer' " . $ins . ">Customer table</option>";
					foreach (GetFlextableDefinitions() AS $tmp) {
						if ($tmp['recordid'] != $_GET['EditFlexTable']) {
							if ($ar['refers_to'] == "flextable" . $tmp['recordid']) {
								$ins = "selected='selected' ";
							} else {
								$ins = "";
							}
							print "<option " . $ins . " value='flextable" . $tmp['recordid'] . "'>Flextable " . htme($tmp['tablename']) . "</option>";
						}
					}
					if ($ar['refers_to'] == "no_refer") {
						$ins = "selected='selected' ";
					} else {
						$ins = "";
					}
					print "<option value='no_refer' " . $ins . ">No refer</option>";
					print "</select></td></tr>";
//					 print "<tr class=\"nicerow\"><td></td><td></td></tr>";
					print "<tr><td colspan=\"2\">";
						print "<table border=0><tr><td valign='top'><table border=0>";
							if ($ar['compact_view'] == "y") {
								$ins = "checked='checked'";
							} else {
								unset($ins);
							}
							print "<tr><td>Show compact table in forms (without link, excel-icon and count)</td><td><input type='checkbox' name='compact_view' value='y' " . $ins . "></td></tr>";
							if ($ar['stayinformaftersave'] == "y") {
								$ins = "checked='checked'";
							} else {
								unset($ins);
							}
							print "<tr><td>Stay in form after save (don't jump back to the list)</td><td><input type='checkbox' name='stayinformaftersave' value='y' " . $ins . "></td></tr>";
							if ($ar['add_in_popup'] == "y") {
								$ins = "checked='checked'";
							} else {
								unset($ins);
							}
							print "<tr><td>Show forms in popup when working from an entity/customer form</td><td><input type='checkbox' name='add_in_popup' value='y' " . $ins . "></td></tr>";

							if ($ar['users_may_select_columns'] == "y") {
								$ins = "checked='checked'";
							} else {
								unset($ins);
							}


							print "<tr><td>Allow users to select columns in lists</td><td><input type='checkbox' name='users_may_select_columns' value='y' " . $ins . "></td></tr>";
						print "</table></td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td valign='top'><table>";
							if ($ar['skip_security'] == "y") {
							$ins = "checked='checked'";
							} else {
							unset($ins);
							}
							print "<tr><td>Skip all security checks except basic table access (for performance only)</td><td><input type='checkbox' name='skip_security' value='y' " . $ins . "></td></tr>";

							if ($ar['sumnumrows'] == "y") {
							$ins = " checked='checked' ";
							} else {
							unset($ins);
							}
							print "<tr><td>Add up numeric columns in lists</td><td><input " . $ins . " type='checkbox' name='sumnumrows' value='y'></td></tr>";

							if ($ar['showfilters'] == "y") {
							$ins = " checked='checked' ";
							} else {
							unset($ins);
							}

							print "<tr><td>Show filters in lists</td><td><input " . $ins . " type='checkbox' name='showfilters' value='y'></td></tr>";
							if ($ar['exclude_from_rep'] == "y") {
								$ins = " checked='checked' ";
								$ins2 = " class='error' ";
								$ins3 = " This table will not be synchronized. This makes synchronization faster, though only enable this option when a) this table is not needed at all in a fail-over situation or b) this table is read-only and you've synchronized it once already. To synchronize this table, uncheck this box and run &quot;exec sync incfix&quot; on the command line or wait 24hrs.";
							} else {
								unset($ins);
							}

							require($GLOBALS['CONFIGFILE']);
							if ($slave[$GLOBALS['ORIGINAL_REPOSITORY']]) {
								print "<tr><td valign='top'>Exclude table from synchronization</td><td><div style='width: 400px' " . $ins2 . "><input " . $ins . " type='checkbox' name='exclude_from_rep' value='y'></td></tr>";
							}

						print "</table></td></tr></table>";
					//print $ins3;
					print "</td></tr>";



					print "<tr class=\"nicerow\">";
					if ($_GET['EditFlexTable'] <> "new") {
						print "<td>Orientation (immutable)</td>";
							if ($ar['orientation'] == "many_entities_to_one") {
								if ($ar['refers_to'] == "entity") {
									print "<td>Many entities refer to 1 flexrecord</td>";
								} elseif ($ar['refers_to'] == "customer") {
									print "<td>Many customers refer to 1 flexrecord</td>";
								} else {
									print "<td>Many flexrecords in the other table refer to 1 flexrecord in this table</td>";
								}

							} else {
								if ($ar['refers_to'] == "entity") {
									print "<td>Many flexrecords refer to 1 entity</td>";
								} elseif ($ar['refers_to'] == "customer") {
									print "<td>Many flexrecords refer to 1 customer</td>";
								} else {
									print "<td>Many flexrecords in this table refer to 1 flexrecord in the other table</td>";
								}


							}

					} else {
						print "<td>Orientation (cannot be altered once table is created)</td><td><select name='orientation'>";

						if ($ar['orientation'] == "one_entities_to_many") {
							$ins = "selected='selected'";
						} else {
							unset($ins);
						}
						print "<option value='many_entities_to_one'>Multiple entities or customers refer to 1 record in this table</option>";
						print "<option " . $ins . " value='one_entity_to_many'>Multiple records in this table refer to 1 entity or customer</option>";
						print "</select></td>";
					}
					print "</tr>";




					print "<tr class=\"nicerow\"><td>Reference field layout</td><td><input type='text' style='width: 350px;' name='refer_field_layout' value='" . htme($ar['refer_field_layout']) . "'></td></tr>";
					if ($_GET['EditFlexTable'] <> "new") {
						if (is_array(GetFlexTableAccessRestrictions($_REQUEST['EditFlexTable']))) {
							print "<tr class=\"nicerow\"><td>Access restrictions (for whole table)</td><td colspan='2' " . PrintToolTipCode('Specific access rights are set for this FlexTable.') . ">";
							print "<span class='noway'>[restrictions apply]</span>";
						} else {
							print "<tr class=\"nicerow\"><td>Access restrictions (for whole table)</td><td colspan='2' " . PrintToolTipCode('No specific access rights are set for this FlexTable.') . ">";
							print "[none set]";
						}
						print "&nbsp;&nbsp;<a class='arrow' href='javascript:PopRightsChooserFlexTable(" . $_REQUEST['EditFlexTable'] . ");'>select</a></td></tr>";

					}
					

					$acbf = "<tr class=\"nicerow\"><td valign='top'>Record access controlled by field<br /><br /><em>Based on extra fields of type 'User-list' (the user must<br />be selected to gain access) or 'List of all groups' (the<br />user must be in this group to gain access).</em></td>";
					$acbf .= "<td><select name='access_controlled_by_field'>";
					$acbf .= "<option value='-'>No access control by field</option>";
					$list = GetExtraFlexTableFields($ar['recordid']);
					foreach ($list AS $extrafield) {
						if ($extrafield['fieldtype'] == "List of all groups" || $extrafield['fieldtype'] == "User-list of all CRM-CTT users" || $extrafield['fieldtype'] == "User-list of administrative CRM-CTT users" || strstr($extrafield['fieldtype'] , "Users of profile ")) {
							if ($ar['access_controlled_by_field'] == $extrafield['id']) {
								$ins = "selected='selected'";
							} else{
								unset($ins);
							}
							$acbf .= "<option value='" . $extrafield['id'] . "' " . $ins . ">" . htme($extrafield['name']) . "</option>";
							$acf = true;
						}
					}
					$acbf .= "</select>";
					$acbf .= "&nbsp;<select name='access_denied_method'>";
					$acbf .= "<option value='readonly'>All others (except table owners) have read-only access</option>";
					if ($ar['access_denied_method'] == "invisible") {
						$ins = "selected='selected'";
					} else {
						unset($ins);
					}

					$acbf .= "<option value='invisible' " . $ins . ">All others (except table owners) don't see the record at all</option>";
					$acbf .= "</select></td></tr>";

					if ($acf) {
						print $acbf;
					}

					unset($acbf);

					$acbf = "<tr class=\"nicerow\"><td valign='top'>Sort by</td>";
					$acbf .= "<td><select name='sort_on'>";
					$acbf .= "<option value='-'>No sort / default</option>";
					$list = GetExtraFlexTableFields($ar['recordid']);
					foreach ($list AS $extrafield) {
							if ($ar['sort_on'] == $extrafield['id']) {
								$ins = "selected='selected'";
							} else{
								unset($ins);
							}
							$acbf .= "<option value='" . $extrafield['id'] . "' " . $ins . ">" . htme($extrafield['name']) . "</option>";
							$acf = true;
					}
					$acbf .= "</select>";
					if ($ar['sort_direction'] == "Descending") {
						$ins = "selected='selected'";
					} else{
						unset($ins);
					}
					$acbf .= "&nbsp;&nbsp;<select name='sort_direction'><option value='Ascending'>Ascending</option><option " . $ins . " value='Descending'>Descending</option></select>";

					$acbf .= "</td></tr>";
					if ($acf) {
						print $acbf;
					}

					
					print "<tr class=\"nicerow\"><td>System selections (visible for all users)</td><td>[ <a onclick=\"PopFancyBoxLarge('Interleave advanced selection builder', 'index.php?ShowAdvancedQueryInterface&amp;ListId=SavedSelectionsFlextable" . $ar['recordid'] . "&Scope=system');\">select</a> ]</td></tr>";

					
					print "<tr class=\"nicerow\"><td>Maximum rows to show on one page</td><td><input type='text' size='3' name='maxrowsperpage' value='" . $ar['maxrowsperpage'] . "'> Use 0 for all rows</td></tr>";
					print "<tr class=\"nicerow\"><td>Table header repeat every</td><td><input type='text' size='3' name='tableheaderrepeat' value='" . $ar['tableheaderrepeat'] . "'> Use 0 for no repeating table header</td></tr>";

					//if ($ViewOnTable == "") {
						print "<tr class=\"nicerow\"><td>Text for 'add record' link</td><td><input type='text' style='width: 350px;' name='addlinktext' value='" . htme($ar['addlinktext']) . "'></td></tr>";

						
		

						print "<tr class=\"nicerow\"><td>Form to use</td><td><select name='formid'>";
							$sql = "SELECT templateid, templatename, template_subject FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML_FORM'";
							$result = mcq($sql, $db);
							while ($row = mysql_fetch_array($result)) {
								if ($row['templateid'] == $ar['formid']) {
									$ins = "selected='selected'";
									$printlink = true;
								} else {
									unset($ins);
								}
								print "<option " . $ins . " value='" . $row['templateid'] . "'>" . $row['templatename'] . " (" . $row['template_subject'] . ")</option>";
							}
							print "</select>";
							if ($printlink) {
								print "&nbsp;&nbsp;<a class='arrow' href='admin.php?templates=1&amp;editHTMLtemplate=" . $ar['formid'] . "&amp;fromlist=" . base64_encode("flextable.php?EditFlexTable=" . $ar['recordid']) . "'>edit form</a>";
							}
							print "</td></tr>";
					//}
				print "<tr class=\"nicerow\"><td valign='top'>Fields to show in tables</td><td>";

					$inshtml = "<table class=\"crm\"><thead><tr><td>Show</td><td>Name</td><td>Type</td><td>Edit field</td></tr></thead>";
					
					$list = GetExtraFlexTableFields($ar['recordid']);

					if (in_array("recordid", $ar['table_layout'])) {
						$ins = "checked='checked'";
					} else {
						unset($ins);
					}
					$inshtml .= "<tr><td><input " . $ins . " type='checkbox' name='table_layout[]' value='recordid'>ID</td><td>Primary id</td><td>numeric</td><td>n/a</td></tr>";

					if (in_array("refer", $ar['table_layout'])) {
						$ins = "checked='checked'";
					} else {
						unset($ins);
					}
					$inshtml .= "<tr><td><input " . $ins . " type='checkbox' name='table_layout[]' value='refer'>Refer</td><td>Refer field</td><td>templates</td><td>n/a</td></tr>";


					foreach($list AS $field) {
						if ($field['fieldtype'] != "Button") {
							if (in_array($field['id'], $ar['table_layout'])) {
								$ins = "checked='checked'";
							} else {
								unset($ins);
							}
							$inshtml .= "<tr><td><input " . $ins . " type='checkbox' name='table_layout[]' value='" . $field['id'] . "'> " . $field['id'] . "</td><td>" . $field['name'] . "</td><td>" . strtolower(GetExtraFieldType($field['id'])) . "</td><td>";
							if ($ViewOnTable == "") {
								$inshtml .= "[<a href=\"extrafields.php?editextrafield=" . $field['id'] . "&amp;tabletype=" . $ar['recordid'] . "&amp;req_url=" . base64_encode("flextable.php?EditFlexTable=" . $ar['recordid']) . "\">edit</a>]";
							}
							
							$inshtml .= "</td></tr>";
							$printedone = true;
						}
					}
					$inshtml .= "</table>";
					if (!$printedone) {
						print "[this table has no fields yet]";
					} else {
						print $inshtml;
					}
					
					print "</td></tr>";
					if ($ar['users_may_select_columns'] == "y") {
						$ins = " checked='checked'";
					} else{
						unset($ins);
					}
					print "<tr class=\"nicerow\"><td>Table header text (HTML)</td><td><textarea id='JS_headerhtml' name='headerhtml' cols=100 rows=10>" . htme($ar['headerhtml']) . "</textarea>";
					print make_html_editor("JS_headerhtml", "", "", true, "300", "600");
					print "</td></tr>";


					print "<tr class=\"nicerow\"><td colspan='2'><input type='submit' value='Save'>&nbsp;&nbsp;";
					print "" . AttributeLink("flextable", $ar['recordid']) . " &nbsp;&nbsp;<a class=\"arrow\" href=\"flextable.php?ShowTable=" . $ar['recordid'] . "\">Show table</a>";
					if ($_GET['EditFlexTable'] <> "new") {
						if ($ViewOnTable == "") {
								print "&nbsp;&nbsp;<a href='flextable.php?TruncateFlexTable=" . $_GET['EditFlexTable'] . "' class=\"arrow\">Empty (truncate)</a>&nbsp;";
						}
						$tmp = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $_GET['EditFlexTable'] . " WHERE deleted='y'");
						
						if ($tmp[0] > 0 && $ViewOnTable=="") {
							print "&nbsp;&nbsp;<a href='flextable.php?PurgeFlexTable=" . $_GET['EditFlexTable'] . "' class='arrow'>Purge deleted records (" . $tmp[0] . ")</a>&nbsp;&nbsp;";
						}
						if ($ViewOnTable != "") {
							print "&nbsp;&nbsp;<a href='flextable.php?DeleteFlexTable=" . $_GET['EditFlexTable'] . "' class='arrow'>Drop view (delete)</a>";
						} else {
							print "&nbsp;&nbsp;<a href='flextable.php?DeleteFlexTable=" . $_GET['EditFlexTable'] . "' class='arrow'>Delete whole table</a>";
						}
						
					}
					print "&nbsp;&nbsp;<a href='flextable.php?TableAdmin=true&navid=ft' class='arrow'>Back to flextable overview</a>";
					print "</td></tr>";
					print "</table>";
					print "</div>";
					print "</form></td></tr></table>";
				}
			}


	} else {
		
		print PlainNav($to_tabs, $tabbs, "overview");

	//	print "<a href='flextable.php?EditFlexTable=new' class='arrow'>Add a new table (manual)</a>&nbsp;&nbsp;<a href='flextable.php?AtOnce=true' class='arrow'>Create table, fields and form based on a CSV-file or SQL query</a><br><br>";
		print "<table class='sortable'>";
		print "<tr><td>id</td><td>Table name</td><td>Refers to</td><td>Orientation</td><td>Form template</td><td># Fields</td><td># Records</td><td>Edit</td><td>View</td><td>Import</td><td>Deduplicate</td></tr>";
		
		$worker = PushStashValue(array("table" => "unknown"));

		foreach ($arr AS $ar) {

			$ViewOnTable = GetAttribute("flextable", "ViewOnTable", $ar['recordid']);

			$link = " class='pointer' onclick=\"document.location='flextable.php?EditFlexTable=" . $ar['recordid'] . "&SkipMainNavigation';\"";
			if ($ViewOnTable != "") {
				$link = str_replace("pointer", "pointer INTLV_ChatTextName", $link);
				$vlink = "VIEW: ";
			} else {
				$vlink = "";
			}
			print "<tr><td " . $link . ">" . $ar['recordid'] . "</td><td " . $link . ">" . $vlink . htme($ar['tablename']) . "</td><td " . $link . ">" . $ar['refers_to'] . " table</td>";

			if ($ar['orientation'] == "many_entities_to_one") {
				if ($ar['refers_to'] == "entity") {
					print "<td " . $link . ">Many entities refer to 1 flexrecord</td>";
				} elseif ($ar['refers_to'] == "customer") {
					print "<td " . $link . ">Many customers refer to 1 flexrecord</td>";
				} else {
					print "<td " . $link . ">Many flexrecords in the other table refer to 1 flexrecord in this table</td>";
				}
			} else {
				if ($ar['refers_to'] == "entity") {
					print "<td " . $link . ">Many flexrecords refer to 1 entity</td>";
				} elseif ($ar['refers_to'] == "customer") {
					print "<td " . $link . ">Many flexrecords refer to 1 customer</td>";
				} else {
					print "<td " . $link . ">Many flexrecords in this table refer to 1 flexrecord in the other table</td>";
				}

			}
			//print "<td>" . $ar['refer_field_layout'] . "</td>";
			print "<td><a class='plainlink' href='admin.php?templates=1&amp;editHTMLtemplate=" . $ar['formid'] . "&amp;fromlist=" . base64_encode($_SERVER['REQUEST_URI']) . "'>" . GetTemplateSubject($ar['formid']) . "</a></td>";
			$numfields = count(GetExtraFlextableFields($ar['recordid']));
			
			if ($ViewOnTable == "") {
				print "<td><a href='extrafields.php?tabletype=ft_" . $ar['recordid'] . "&amp;ti=" . htme($ar['tablename']) . "' class='plainlink'>" . $numfields . "</a></td>";
			} else {
				print "<td>" . $numfields . "</a></td>";
			}

			print "<td " . $link . ">" . FormatNumber(db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $ar['recordid'] . " WHERE (deleted='n' OR deleted IS NULL)"),0) . "</td>";

			print "<td><a href='flextable.php?EditFlexTable=" . $ar['recordid'] . "' class='plainlink'>Edit</a></td><td><a href='flextable.php?ShowTable=" . $ar['recordid'] . "' class='plainlink'>View table contents</a></td>";
			if ($ViewOnTable == "") {
				print "<td><a href='import.php?tableId=" . $ar['recordid'] . "&amp;Worker=" . $worker . "' class='plainlink'>Import data</a></td>";
			} else {
				print "<td></td>";
			}
			if ($ViewOnTable == "") {
				print "<td><a href='dedup.php?tableId=" . $ar['recordid'] . "&amp;Worker=" . $worker . "' class='plainlink'>Deduplicate</a></td>";
			} else {
				print "<td></td>";
			}

			print "</tr>";
		}
		print "</table>";
	}


}

EndHTML();
?>