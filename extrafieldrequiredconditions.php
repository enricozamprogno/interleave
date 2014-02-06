<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 info@interleave.nl
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This is the page that pops up when setting coditions for requirement on extra fields
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
require_once("initiate.php");
$_REQUEST['nonavbar'] = 1;
ShowHeaders();
if (CheckFunctionAccess("ExtrafieldAdmin") == "nok") {
	PrintAD("Access to this page/function denied.");
} else {
	print "<table><tr><td>&nbsp;&nbsp;&nbsp;</td><td>";



	if ($_REQUEST['efid']) {
		if ($_REQUEST['DelCondition']) {
			mcq("DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "extrafieldrequiredconditions WHERE conid='" . mres($_REQUEST['DelCondition']) . "' AND efid='" . mres($_REQUEST['efid']) . "'", $db);
		} elseif ($_REQUEST['AddCondition']) {
			$sa = $_REQUEST['SelectedAction'];

			if (substr($sa,0,1) == "!") {
				$trueorfalse="false";
				$sa = substr($sa, 1, strlen($sa)-1);
			} else {
				$trueorfalse="true";
			}
			$sa = explode("_", $sa);

			if ($sa[0] == "EFID") {
				$field = "EFID" . $sa[1];
				$value = base64_decode($sa[2]);
			} else {
				$field = $sa[0];
				$value = $sa[1];
			}


	//		print "\n\nVELD ---> " . $field . "\n\n";
	//		print "\n\nWAARDE ---> " . $value . "\n\n";
	//		print "\n\nTRUEORFALSE ---> " . $trueorfalse . "\n\n";
	//		print "\n\nTRIGGER ---> " . $_REQUEST['efid'] . "\n\n";

			if ($value != "" && $value != "-") {
				$sql = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "extrafieldrequiredconditions(efid, field, value, trueorfalse,deletetemplaterow,displayvalueintext) VALUES('" . mres($_REQUEST['efid']) . "','" . mres($field) . "','" . mres($value) . "','" . mres($trueorfalse) . "','" . mres($_REQUEST['deletetemplaterow']) . "','" . mres($_REQUEST['displayvalueintext']) . "')";
	//			print $sql;
				mcq($sql, $db);
			}

			if ($_REQUEST['deletetemplaterow'] == "") $_REQUEST['deletetemplaterow'] = "n";
			if ($_REQUEST['displayvalueintext'] == "") $_REQUEST['displayvalueintext'] = "n";

			mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "extrafieldrequiredconditions SET deletetemplaterow='" . mres($_REQUEST['deletetemplaterow']) . "',displayvalueintext='" . mres($_REQUEST['displayvalueintext']) . "' WHERE efid='" . mres($_REQUEST['efid']) . "'", $db);



		}
		$tid = $_REQUEST['efid'];
		$tabletype = GetExtraFieldTableType($tid);
		$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafieldrequiredconditions WHERE efid='" . mres($tid) . "'";
		$t = db_GetArray($sql);
		print "Add conditions. If all conditions are met, the extra field will be required. Please note that the \"required\" box on the extra field property page must also be checked.<br><br>";
		print "<table class='sortable' width='100%'>";
		print "<tr><td>Field</td><td>Must be...</td><td>Value</td><td>Delete</td></tr>";
		foreach($t AS $row) {
			if ($row['deletetemplaterow'] == "y") {
				$deltemplaterow = true;
			}
			if ($row['displayvalueintext'] == "y") {
				$displayvalueintext = true;
			}

			if ($row['field'] == "status") {
				$row['value'] = GetStatusName($row['value']);
			}
			if ($row['field'] == "priority") {
				$row['value'] = GetPriorityName($row['value']);
			}
			if ($row['field'] == "CRMcustomer" && is_numeric($row['value'])) {
				$row['value'] = GetCustomerName($row['value']);
			}
			if ($row['field'] == "group") {
				$tmp = GetProfileArray($row['value']);
				$row['value'] = "\"" . $tmp['name'] . "\"";
			}
			if (strstr($row['field'], "EFID")) {
				$row['field'] = GetExtraFieldName(str_replace("EFID", "", $row['field']));
			}
			print "<tr><td>" . $row['field'] . "</td>";
			if ($row['trueorfalse'] == "false") {
				print "<td>must <strong>not</strong> be</td>";
			} else {
				print "<td>must be</td>";
			}
			print "<td>" . $row['value'] . "</td><td><a href='extrafieldrequiredconditions.php?DelCondition=" . $row['conid'] . "&efid=" .  $tid . "'><img src='images/delete.gif' alt=''></a></td></tr>";
			$pt = true;
		}
		if (!$pt) {
			print "<tr><td colspan='5'>No conditions defined</td></tr>";
		}
		print "</table>";
		print "<br>Add a condition:<br>";
		print "<form id='AddCondition' method='get' action=''><div class='showinline'>";
		print "<input type='hidden' name='efid' value='" . htme($tid) . "'>";
		print "<input type='hidden' name='AddCondition' value='True'>";
		$totalbuffer .= "<select name='SelectedAction' id='JS_SelectedAction'><option>-</option>\n\n";
		if (!is_numeric($tabletype)) {
			$a = db_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "statusvars ORDER BY listorder, varname");
			foreach ($a AS $sv) {
				$totalbuffer .= "<option style='background-color: " . GetStatusColor($sv['id']) . ";' value='status_" . htme($sv['id']) . "'>Status must be " . htme($sv['varname']) . "</option>\n\n";
				$totalbuffer .= "<option style='background-color: " . GetStatusColor($sv['id']) . ";' value='!status_" . htme($sv['id']) . "'>Status must NOT be " . htme($sv['varname']) . "</option>\n\n";
			}
			$a = db_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "priorityvars ORDER BY listorder, varname");
			foreach ($a AS $pv) {
				$totalbuffer .= "<option value='priority_" . htme($pv['id']) . "'>Priority must be " . htme($pv['varname']) . "</option>\n\n";
				$totalbuffer .= "<option value='!priority_" . htme($pv['id']) . "'>Priority must NOT be " . htme($pv['varname']) . "</option>\n\n";
			}

			$totalbuffer .= "<option value='deleted_y'>Must be deleted</option>\n\n";
			$totalbuffer .= "<option value='deleted_n'>Must NOT be deleted</option>\n\n";

			foreach (GetProfiles() AS $group) {
				$totalbuffer .= "<option value='group_" . htme($group['id']) . "'>[group] must be [" . htme($group['name']) . "]</option>\n\n";
				$totalbuffer .= "<option value='!group_" . htme($group['id']) . "'>[group] must NOT be [" . htme($group['name']) . "]</option>\n\n";
			}

			$a = GetExtraFields();
			foreach ($a AS $ef) {
				if ($ef['fieldtype'] == "drop-down" || $ef['fieldtype'] == "checkbox") {
					$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_NOT_EMPTY_") . "'>Value of [" . $ef['name'] . "] must NOT BE EMPTY</option>\n\n";
					$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_EMPTY_") . "'>Value of [" . $ef['name'] . "] must BE EMPTY</option>\n\n";

					if ($ef['fieldtype'] == "checkbox") {
						$options = array($ef['options'], $ef['defaultval']);
					} else {
						$options = unserialize($ef['options']);
					}

					foreach ($options AS $option) {
						$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode($option) . "'>Value of [" . $ef['name'] . "] must be [" . htme($option) . "]</option>\n\n";
						$totalbuffer .= "<option value='!EFID_" . $ef['id'] . "_" . base64_encode($option) . "'>Value of [" . $ef['name'] . "] must NOT be [" . htme($option) . "]</option>\n\n";
					}
				} elseif ($ef['fieldtype'] == "Reference to FlexTable") {
					$tmpft = GetExtraFlextableFields($ef['options']);
					$tmp = array();
					foreach ($tmpft AS $field) {
						$tmp = array_merge($tmp, db_GetArray("SELECT DISTINCT(EFID" . $field['id'] . ") FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $ef['options'] . " WHERE deleted='n'"));
					}

					foreach ($tmp AS $record) {
						$val = $record[0];
						$fieldid = str_replace("EFID", "", GetArrayKeyName($record, 1));
						if (!is_array(unserialize($record[0]))) {
							$totalbuffer .= "<option value='EFID_" . $fieldid . "_" . base64_encode($val) . "'>Value of [" . GetExtraFieldName($fieldid) . "] must be [" . htme($val) . "]</option>\n\n";
							$totalbuffer .= "<option value='!EFID_" . $fieldid . "_" . base64_encode($val) . "'>Value of [" .  GetExtraFieldName($fieldid) . "] must NOT be [" . htme($val) . "]</option>\n\n";
						}
					}

				} elseif ($ef['fieldtype'] != "Booking calendar" && $ef['fieldtype'] != "Calendar planning group") {
					$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_NOT_EMPTY_") . "'>Value of [" . $ef['name'] . "] must NOT BE EMPTY</option>\n\n";
					$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_EMPTY_") . "'>Value of [" . $ef['name'] . "] must BE EMPTY</option>\n\n";
				}
			}
			foreach (LoadCustomerCache() AS $cust) {
				$totalbuffer .= "<option value='CRMcustomer_" . $cust['id'] . "'>[". $lang['customer'] . "] must be [" . htme($cust['custname']) . "]</option>\n\n";
				$totalbuffer .= "<option value='!CRMcustomer_" . $cust['id'] . "'>[". $lang['customer'] . "] must NOT be [" . htme($cust['custname']) . "]</option>\n\n";
			}
			$a = GetExtraCustomerFields();
			$totalbuffer .= "<option value=''>--- " . $lang['customer'] . " values ---</option>\n\n";

			foreach ($a AS $ef) {
				if ($ef['fieldtype'] == "drop-down" || $ef['fieldtype'] == "checkbox") {
					$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_NOT_EMPTY_") . "'>Value of [" . $ef['name'] . "] must NOT BE EMPTY</option>\n\n";
					$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_EMPTY_") . "'>Value of [" . $ef['name'] . "] must BE EMPTY</option>\n\n";
					if ($ef['fieldtype'] == "checkbox") {
						$options = array($ef['options'], $ef['defaultval']);
					} else {
						$options = unserialize($ef['options']);
					}
					foreach ($options AS $option) {
						$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode($option) . "'>Value of [" . $ef['name'] . "] must be [" . htme($option) . "]</option>\n\n";
						$totalbuffer .= "<option value='!EFID_" . $ef['id'] . "_" . base64_encode($option) . "'>Value of [" . $ef['name'] . "] must NOT be [" . htme($option) . "]</option>\n\n";
					}
				} elseif ($ef['fieldtype'] == "Reference to FlexTable") {


				} elseif ($ef['fieldtype'] != "Booking calendar" && $ef['fieldtype'] != "Calendar planning group") {
					$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_NOT_EMPTY_") . "'>Value of [" . $ef['name'] . "] must NOT BE EMPTY</option>\n\n";
					$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_EMPTY_") . "'>Value of [" . $ef['name'] . "] must BE EMPTY</option>\n\n";
				}
			}
		} else {
			$a = GetExtraFlexTableFields($tabletype);
			foreach ($a AS $ef) {
				if ($ef['fieldtype'] == "drop-down" || $ef['fieldtype'] == "checkbox") {
					$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_NOT_EMPTY_") . "'>Value of [" . $ef['name'] . "] must NOT BE EMPTY</option>\n\n";
					$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_EMPTY_") . "'>Value of [" . $ef['name'] . "] must BE EMPTY</option>\n\n";
					if ($ef['fieldtype'] == "checkbox") {
						$options = array($ef['options'], $ef['defaultval']);
					} else {
						$options = unserialize($ef['options']);
					}
					foreach ($options AS $option) {
						$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode($option) . "'>Value of [" . $ef['name'] . "] must be [" . htme($option) . "]</option>\n\n";
						$totalbuffer .= "<option value='!EFID_" . $ef['id'] . "_" . base64_encode($option) . "'>Value of [" . $ef['name'] . "] must NOT be [" . htme($option) . "]</option>\n\n";
					}
				} elseif ($ef['fieldtype'] == "Reference to FlexTable") {
					$tmpft = GetExtraFlextableFields($ef['options']);
					$tmp = array();
					foreach ($tmpft AS $field) {
						$tmp = array_merge($tmp, db_GetArray("SELECT DISTINCT(EFID" . $field['id'] . ") FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $ef['options'] . " WHERE deleted='n'"));
					}

					foreach ($tmp AS $record) {
						$val = $record[0];
						$fieldid = str_replace("EFID", "", GetArrayKeyName($record, 1));
						if (!is_array(unserialize($record[0]))) {
							$totalbuffer .= "<option value='EFID_" . $fieldid . "_" . base64_encode($val) . "'>Value of [" . GetExtraFieldName($fieldid) . "] must be [" . htme($val) . "]</option>\n\n";
							$totalbuffer .= "<option value='!EFID_" . $fieldid . "_" . base64_encode($val) . "'>Value of [" .  GetExtraFieldName($fieldid) . "] must NOT be [" . htme($val) . "]</option>\n\n";
						}
					}



				} elseif ($ef['fieldtype'] != "Booking calendar" && $ef['fieldtype'] != "Calendar planning group") {
					$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_NOT_EMPTY_") . "'>Value of [" . $ef['name'] . "] must NOT BE EMPTY</option>\n\n";
					$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_EMPTY_") . "'>Value of [" . $ef['name'] . "] must BE EMPTY</option>\n\n";
				}
			}
		}




		$totalbuffer .= "</select>";
		print $totalbuffer;
		print " " . ReturnDropDownSearchField("JS_SelectedAction");
		if ($deltemplaterow) {
			$ins = "checked='checked'";
		} else {
			unset($ins);
		}
		if ($displayvalueintext) {
			$ins2 = "checked='checked'";
		} else {
			unset($ins2);
		}
		
		print "<br><br><input type='submit' value='Go'>";
		print "</div></form>";


	} else {

		PrintAD("No extra field ID received, cannot continue.");
	}
}
print "</td></tr></table>";
EndHTML();
