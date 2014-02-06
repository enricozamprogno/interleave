<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 info@interleave.nl
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This is the page that pops up when setting detailed module access restrictions
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
MustBeAdmin();

print "<table><tr><td>&nbsp;&nbsp;&nbsp;</td><td>";



if ($_REQUEST['triggerid']) {
	if ($_REQUEST['DelCondition']) {
		mcq("DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "triggerconditions WHERE conid='" . mres($_REQUEST['DelCondition']) . "' AND triggerid='" . mres($_REQUEST['triggerid']) . "'", $db);
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

		if ($field) {
			$sql = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "triggerconditions(triggerid, field, value, trueorfalse, failmessage) VALUES('" . mres($_REQUEST['triggerid']) . "','" . mres($field) . "','" . mres($value) . "','" . $trueorfalse . "','" . mres($_REQUEST['failmessage']) . "')";
			mcq($sql, $db);
		}
		mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "triggerconditions SET failmessage='" . mres($_REQUEST['failmessage']) . "', successmessage='" . mres($_REQUEST['successmessage']) . "' WHERE triggerid='" . mres($_REQUEST['triggerid']) . "'", $db);

	}
	$tid = $_REQUEST['triggerid'];
	$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "triggerconditions WHERE triggerid='" . mres($tid) . "'";
	$t = db_GetArray($sql);
	print "If one of these conditions fails, the trigger will not go off.<br><br>";
	print "<table class='sortable' width='100%'>";
	print "<tr><td>Field</td><td>Must be...</td><td>Value</td><td>Delete</td></tr>";
	foreach($t AS $row) {
		if ($row['field'] == "status") {
			$row['value'] = GetStatusName($row['value']);
		}
		if ($row['field'] == "priority") {
			$row['value'] = GetPriorityName($row['value']);
		}
		if ($row['field'] == "group") {
			$tmp = GetProfileArray($row['value']);
			$row['value'] = "\"" . $tmp['name'] . "\"";
		}
		if ($row['field'] == "CRMcustomer" && is_numeric($row['value'])) {
			$row['value'] = GetCustomerName($row['value']);
		}
		if (substr($row['field'],0,4) == "EFID") {
			$row['field'] = GetExtraFieldName(str_replace("EFID", "", $row['field']));
			$row['value'] = str_replace("_", "", $row['value']);
		}

		print "<tr><td>" . htme($row['field']) . "</td>";
		if ($row['trueorfalse'] == "false") {
			print "<td>must <strong>not</strong> be</td>";
		} else {
			print "<td>must be</td>";
		}
		print "<td>" . htme($row['value']) . "</td><td><a href='triggerconditions.php?DelCondition=" . $row['conid'] . "&amp;triggerid=" .  $tid . "'><img src='images/delete.gif' alt=''></a></td></tr>";
		$pt = true;

		if ($row['failmessage']) {
			$failmessage = $row['failmessage'];
		}
		if ($row['successmessage']) {
			$successmessage = $row['successmessage'];
		}
	}
	if (!$pt) {
		print "<tr><td colspan='5'>No conditions defined</td></tr>";
	}
	print "</table>";
	print "<br>Add a condition:<br>";
	print "<form id='AddCondition' method='get' action=''><div class='showinline'>";
	print "<input type='hidden' name='triggerid' value='" . htme($tid) . "'>";
	print "<input type='hidden' name='AddCondition' value='True'>";
	$totalbuffer .= "<select name='SelectedAction' id='JS_SelectedAction'><option value=''>-</option>\n\n";
	$a = db_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "statusvars ORDER BY listorder, varname");
	foreach ($a AS $sv) {
		$totalbuffer .= "<option style='background-color: " . GetStatusColor($sv['id']) . ";' value='status_" . $sv['id'] . "'>Status must be " . htme($sv['varname']) . "</option>\n\n";
		$totalbuffer .= "<option style='background-color: " . GetStatusColor($sv['id']) . ";' value='!status_" . $sv['id'] . "'>Status must NOT be " . htme($sv['varname']) . "</option>\n\n";
	}
	$a = db_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "priorityvars ORDER BY listorder, varname");
	foreach ($a AS $pv) {
		$totalbuffer .= "<option value='priority_" . $pv['id'] . "'>Priority must be " . htme($pv['varname']) . "</option>\n\n";
		$totalbuffer .= "<option value='!priority_" . $pv['id'] . "'>Priority must NOT be " . htme($pv['varname']) . "</option>\n\n";
	}
	$totalbuffer .= "<option value='deleted_y'>Must be deleted</option>\n\n";
	$totalbuffer .= "<option value='deleted_n'>Must NOT be deleted</option>\n\n";
	$totalbuffer .= "<option value='duedate" . ("_EMPTY") . "'>Duedate must BE EMPTY</option>\n\n";
	$totalbuffer .= "<option value='duedate" . ("_NOT EMPTY") . "'>Duedate must NOT BE EMPTY</option>\n\n";
	$totalbuffer .= "<option value='duedate" . ("_AFTER") . "'>Duedate must AFTER process date</option>\n\n";
	$totalbuffer .= "<option value='duedate" . ("_BEFORE") . "'>Duedate must BEFORE process date</option>\n\n";

	$totalbuffer .= "<option value='startdate" . ("_EMPTY") ."'>Startdate must BE EMPTY</option>\n\n";
	$totalbuffer .= "<option value='startdate" . ("_NOT_EMPTY") . "'>Startdate must NOT BE EMPTY</option>\n\n";
	$totalbuffer .= "<option value='startdate" . ("_AFTER") . "'>Startdate must AFTER process date</option>\n\n";
	$totalbuffer .= "<option value='startdate" . ("_BEFORE") . "'>Startdate must BEFORE process date</option>\n\n";


	$sql = "SELECT FULLNAME,id FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE name NOT LIKE 'deleted_user%' ORDER BY FULLNAME";
	$result= mcq($sql,$db);

	$totalbuffer .= "<option value='owner_CURUSER' " . $ins . ">Owner must be the current user</option>";
	$totalbuffer .= "<option value='assignee_CURUSER' " . $ins . ">Assignee must be the current user</option>";
	$totalbuffer .= "<option value='!owner_CURUSER' " . $ins . ">Owner must NOT be the current user</option>";
	$totalbuffer .= "<option value='!assignee_CURUSER' " . $ins . ">Assignee must NOT be the current user</option>";

	while($options = mysql_fetch_array($result)) {
		$totalbuffer .= "<option value='assignee_" . $options['id'] . "' " . $ins . ">Assignee must be " . htme($options['FULLNAME']) . "</option>";
		$totalbuffer .= "<option value='!assignee_" . $options['id'] . "' " . $ins . ">Assignee must NOT " . htme($options['FULLNAME']) . "</option>";
		$totalbuffer .= "<option value='owner" . $options['id'] . "' " . $ins . ">Owner must be " . htme($options['FULLNAME']) . "</option>";
		$totalbuffer .= "<option value='!owner_" . $options['id'] . "' " . $ins . ">Owner must NOT " . htme($options['FULLNAME']) . "</option>";
	}
	$sql = "SELECT name,id FROM " . $GLOBALS['TBL_PREFIX'] . "userprofiles WHERE name NOT LIKE 'deleted_user%' ORDER BY id";
	$result= mcq($sql,$db);
	while($options = mysql_fetch_array($result)) {
		$totalbuffer .= "<option value='assigneegroup_" . $options['id'] . "' " . $ins . ">Assignee must be in group " . htme($options['name']) . "</option>";
		$totalbuffer .= "<option value='!assigneegroup_" . $options['id'] . "' " . $ins . ">Assignee must NOT be in group " . htme($options['name']) . "</option>";
		$totalbuffer .= "<option value='ownergroup" . $options['id'] . "' " . $ins . ">Owner must be in group " . htme($options['name']) . "</option>";
		$totalbuffer .= "<option value='!ownergroup_" . $options['id'] . "' " . $ins . ">Owner must NOT be in group " . htme($options['name']) . "</option>";
	}

	$a = GetExtraFields();
	foreach ($a AS $ef) {
	//qlog(INFO, "TEST:" . $ef['$fieldtype']);
		if ($ef['fieldtype'] == "drop-down" || $ef['fieldtype'] == "checkbox" || $ef['fieldtype'] == "date" || GetAttribute("extrafield", "ComputationOutputType", $ef['id']) == "Date" || $ef['fieldtype'] == "textbox" || $ef['fieldtype'] == "mail" || substr($ef['fieldtype'], 0, 9)  == "User-list") {

			if ($ef['fieldtype'] == "checkbox") {
				$options = array($ef['options'], $ef['defaultval']);
			} elseif (substr($ef['fieldtype'], 0, 9)  == "User-list") {
				$options = db_GetFlatArray("SELECT id FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE name NOT LIKE 'deleted_user%' ORDER BY id");
			} else {
				$options = unserialize($ef['options']);
			}
			$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_NOT_EMPTY_") . "'>Value of [" . htme($ef['name']) . "] must NOT BE EMPTY</option>\n\n";
			$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_EMPTY_") . "'>Value of [" . htme($ef['name']) . "] must BE EMPTY</option>\n\n";
			if (substr($ef['fieldtype'], 0, 9)  == "User-list") {
				$options = db_GetFlatArray("SELECT id FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE name NOT LIKE 'deleted_user%' ORDER BY id");
			}
			foreach ($options AS $option) {
				if (substr($ef['fieldtype'], 0, 9)  == "User-list") {
					$name = GetUserName($option);
				} else {
					$name = $option;
				}
				$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode($option) . "'>Value of [" . htme($ef['name']) . "] must be [" . htme($name) . "]</option>\n\n";
				$totalbuffer .= "<option value='!EFID_" . $ef['id'] . "_" . base64_encode($option) . "'>Value of [" . htme($ef['name']) . "] must NOT be [" . htme($name) . "]</option>\n\n";
			}
/*		} elseif ($ef['fieldtype'] == "Reference to FlexTable") {
			$tmp = db_GetArray("SELECT DISTINCT(EFID" . $ef['id'] . ") FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $ef['options']);
	    	foreach ($tmp AS $val) {
				$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode($val) . "'>Value of [" . $ef['name'] . "] must be [" . $val . "]</option>\n\n";
				$totalbuffer .= "<option value='!EFID_" . $ef['id'] . "_" . base64_encode($val) . "'>Value of [" . $ef['name'] . "] must NOT be [" . $val . "]</option>\n\n";
			}
			*/
		} else {
			$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_NOT_EMPTY_") . "'>Value of [" . htme($ef['name']) . "] must NOT BE EMPTY</option>\n\n";
		}
	}

	foreach (GetProfiles() AS $group) {
		$totalbuffer .= "<option value='group_" . $group['id'] . "'>[group] of triggering user must be [" . htme($group['name']) . "]</option>\n\n";
		$totalbuffer .= "<option value='!group_" . $group['id'] . "'>[group] of triggering user must NOT be [" . htme($group['name']) . "]</option>\n\n";
	}
	foreach (LoadCustomerCache() AS $cust) {
		$totalbuffer .= "<option value='CRMcustomer_" . $cust['id'] . "'>[". $lang['customer'] . "] must be [" . htme($cust['custname']) . "]</option>\n\n";
		$totalbuffer .= "<option value='!CRMcustomer_" . $cust['id'] . "'>[". $lang['customer'] . "] must NOT be [" . htme($cust['custname']) . "]</option>\n\n";
	}
	$a = GetExtraCustomerFields();
	$totalbuffer .= "<option value=''>--- " . $lang['customer'] . " values ---</option>\n\n";

	foreach ($a AS $ef) {
		if ($ef['fieldtype'] == "drop-down" || $ef['fieldtype'] == "checkbox" || $ef['fieldtype'] == "date" || GetAttribute("extrafield", "ComputationOutputType", $ef['id']) == "Date" || $ef['fieldtype'] == "textbox" || $ef['fieldtype'] == "mail") {
			$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_NOT_EMPTY_") . "'>Value of [" . htme($ef['name']) . "] must NOT BE EMPTY</option>\n\n";
			$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_EMPTY_") . "'>Value of [" . htme($ef['name']) . "] must BE EMPTY</option>\n\n";
			$options = unserialize($ef['options']);
			if (substr($ef['fieldtype'], 0, 9)  == "User-list") {
				$options = db_GetFlatArray("SELECT id FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE name NOT LIKE 'deleted_user%' ORDER BY id");
			}
			foreach ($options AS $option) {
				if (substr($ef['fieldtype'], 0, 9)  == "User-list") {
					$name = GetUserName($option);
				} else {
					$name = $option;
				}
				$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode($option) . "'>Value of [" . htme($ef['name']) . "] must be [" . htme($name) . "]</option>\n\n";
				$totalbuffer .= "<option value='!EFID_" . $ef['id'] . "_" . base64_encode($option) . "'>Value of [" . htme($ef['name']) . "] must NOT be [" . htme($name) . "]</option>\n\n";
			}
		/*
		} elseif ($ef['fieldtype'] == "Reference to FlexTable") {
				$tmp = db_GetArray("SELECT DISTINCT(value) FROM " . $GLOBALS['TBL_PREFIX'] . "customfaddons WHERE type='flextable" . $ef['options'] . "'");
				foreach ($tmp AS $val) {
					$val = $val[0];
					$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode($val) . "'>Value of [" . $ef['name'] . "] must be [" . $val . "]</option>\n\n";
					$totalbuffer .= "<option value='!EFID_" . $ef['id'] . "_" . base64_encode($val) . "'>Value of [" . $ef['name'] . "] must NOT be [" . $val . "]</option>\n\n";
				}
		*/
		} else {
			$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_NOT_EMPTY_") . "'>Value of [" . htme($ef['name']) . "] must NOT BE EMPTY</option>\n\n";
		}
	}
	$tmp = GetFlextableDefinitions();
	foreach ($tmp AS $ft) {
		$a = GetExtraFlextableFields($ft['recordid']);
		$totalbuffer .= "<option value=''>--- Flextable " . htme($ft['tablename']) . " values ---</option>\n\n";

		foreach ($a AS $ef) {
			if ($ef['fieldtype'] == "drop-down" || $ef['fieldtype'] == "checkbox" || $ef['fieldtype'] == "date" || GetAttribute("extrafield", "ComputationOutputType", $ef['id']) == "Date" || $ef['fieldtype'] == "textbox" || $ef['fieldtype'] == "mail" || substr($ef['fieldtype'], 0, 9)  == "User-list") {
				$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_NOT_EMPTY_") . "'>Value of [" . htme($ef['name']) . "] must NOT BE EMPTY</option>\n\n";
				$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_EMPTY_") . "'>Value of [" . htme($ef['name']) . "] must BE EMPTY</option>\n\n";
				$options = unserialize($ef['options']);
				if (substr($ef['fieldtype'], 0, 9)  == "User-list") {
					$options = db_GetFlatArray("SELECT id FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE name NOT LIKE 'deleted_user%' ORDER BY id");
				}
				foreach ($options AS $option) {
					if (substr($ef['fieldtype'], 0, 9)  == "User-list") {
						$name = GetUserName($option);
					} else {
						$name = $option;
					}
					$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode($option) . "'>Value of [" . htme($ef['name']) . "] must be [" . htme($name) . "]</option>\n\n";
					$totalbuffer .= "<option value='!EFID_" . $ef['id'] . "_" . base64_encode($option) . "'>Value of [" . htme($ef['name']) . "] must NOT be [" . htme($name) . "]</option>\n\n";
				}
			} elseif ($ef['fieldtype'] == "Reference to FlexTable") {
					// Nested flextable value conditions are not supported
				/*
				$tmp = db_GetArray("SELECT DISTINCT(value) FROM " . $GLOBALS['TBL_PREFIX'] . "customadddons WHERE type='flextable" . $ef['options'] . "'");
							foreach ($tmp AS $val) {
					$val = $val[0];
					$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode($val) . "'>Value of [" . $ef['name'] . "] must be [" . $val . "]</option>\n\n";
					$totalbuffer .= "<option value='!EFID_" . $ef['id'] . "_" . base64_encode($val) . "'>Value of [" . $ef['name'] . "] must NOT be [" . $val . "]</option>\n\n";
					}
					*/

			} else {
				$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_NOT_EMPTY_") . "'>Value of [" . htme($ef['name']) . "] must NOT BE EMPTY</option>\n\n";
			}
		}
	}
	$totalbuffer .= "<option value=''>--- Module output conditions ---</option>\n\n";
	$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "modules ORDER BY module_name";
	$rs = mcq($sql, $db);
	while ($row = mysql_fetch_array($rs)) {
		if ($fetched_trigger['action'] == "run module " . $row['mid'] . "") {
			$ins = "selected='selected'";
		} else {
			unset($ins);
		}
		$totalbuffer .= "<option $ins value='module " . $row['mid'] . "_true'>Plain printed output of the module " . htme($row['module_name']) . " must be TRUE</option>";
		$totalbuffer .= "<option $ins value='!module " . $row['mid'] . "_true'>Plain printed output of the module " . htme($row['module_name']) . " must NOT be TRUE</option>";
	}
		$a = GetExtraUserFields();
		$totalbuffer .= "<option value=''>--- user values ---</option>\n\n";

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

			} elseif ($ef['fieldtype'] != "Booking calendar" && $ef['fieldtype'] != "Calendar planning group") {
				$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_NOT_EMPTY_") . "'>Value of [" . $ef['name'] . "] must NOT BE EMPTY</option>\n\n";
				$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_EMPTY_") . "'>Value of [" . $ef['name'] . "] must BE EMPTY</option>\n\n";
			}
		}
		$a = GetExtraGroupFields();
		$totalbuffer .= "<option value=''>--- group values ---</option>\n\n";

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

			} elseif ($ef['fieldtype'] != "Booking calendar" && $ef['fieldtype'] != "Calendar planning group") {
				$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_NOT_EMPTY_") . "'>Value of [" . $ef['name'] . "] must NOT BE EMPTY</option>\n\n";
				$totalbuffer .= "<option value='EFID_" . $ef['id'] . "_" . base64_encode("_EMPTY_") . "'>Value of [" . $ef['name'] . "] must BE EMPTY</option>\n\n";
			}
		}
	$totalbuffer .= "</select>";
	print $totalbuffer;
	print " " . ReturnDropDownSearchField("JS_SelectedAction");

	print "<br><br>";
	print "When the conditions are met, display this message to the user: <br><input type='text' name='successmessage' style='width: 400px;' value='" . htme($successmessage) . "'> (optional)";
	print "<br><br>When the conditions fail, display this message to the user: <br><input type='text' name='failmessage' style='width: 400px;' value='" . htme($failmessage) . "'> (optional)";
	print "<br><br>Use can use template tags like @EID@ and @CUSTOMER@ in these messages.";
	print "<br><br><br><input type='submit' value='Go'>";
	print "</div></form>";


} else {

	PrintAD("No trigger ID received, cannot continue.");
}

print "</td></tr></table>";
EndHTML();


?>
