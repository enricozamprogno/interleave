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
require("initiate.php");
ShowHeaders();
MustBeAdmin();

CompareProfiles($_REQUEST['s1'], $_REQUEST['s2']);

function CompareProfiles($s1, $s2) {

	$p8 = GetAccessrightsArray($s1);
	$p7 = GetAccessrightsArray($s2);

	print "<table class=\"crm\">";
	print "<thead><tr><td>Item</td><td>" . $s1 . "</td><td>" . $s2 . "</td></tr></thead>";
	foreach ($p8 AS $key => $accArr) {
		if ($accArr != $p7[$key]) {
			print "<tr><td>" . $key . "</td><td>";
			foreach ($accArr AS $right => $dummy) {
				print $right . " ";
			}
			print "</td><td>";
			foreach ($p7[$key] AS $right => $dummy) {
				print $right . " ";
			}
			print "</td></tr>";
		} else {
			//print "EQ $key " . string_r($accArr);
		}
	}

	print "</table>";
}


function GetAccessrightsArray($userorprofile) {
	$search = $userorprofile;
	$tmp1 = db_GetArray("SELECT recordid,accessarray FROM " . $GLOBALS['TBL_PREFIX'] . "flextabledefs");
	$tmp2 = db_GetArray("SELECT id,accessarray FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE deleted!='y'");
	$tmp3 = db_GetArray("SELECT mid,module_accessarray, module_name FROM " . $GLOBALS['TBL_PREFIX'] . "modules");
	$tmp4 = GetSetting("PersonalTabs");

	$ts = array();

	if (substr($search,0,1) == "P") {
		$table = $GLOBALS['TBL_PREFIX'] . "userprofiles";
		$id = str_replace("P", "", $search);
	} elseif (substr($search,0,1) == "U") {
		$table = $GLOBALS['TBL_PREFIX'] . "loginusers";
		$id = str_replace("U", "", $search);
	} else {
		return(false);
	}
	$val = unserialize(db_GetValue("SELECT ALLOWEDADDFORMS FROM " . $table . " WHERE id='" . $id . "'"));
	$tmp = db_GetFlatArray("SELECT templateid FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML_FORM'");
	foreach ($tmp AS $tid) {
		if (in_array($tid, $val)) {
			$ts['template' . $tid . "- view access"] = array("view access" => true);
		} else {
			$ts['template' . $tid . "- view access"] = array("no view access" => true);
		}
	}

	
	$val = unserialize(db_GetValue("SELECT ADDFORMS FROM " . $table . " WHERE id='" . $id . "'"));
	foreach ($tmp AS $tid) {
		if (in_array($tid, $val)) {
			$ts['template' . $tid . "- add access"] = array("add access" => true);
		} else {
			$ts['template' . $tid . "- add access"] = array("no add access" => true);
		}
	}

	foreach ($tmp1 AS $ftar) {
		$ar = unserialize($ftar['accessarray']);
		$sr = false;
		foreach ($ar AS $key) {
			$right = array();
			if (substr($key, strlen($key) - strlen($search), strlen($search)) == $search) {
				if (substr($key, 0, 3) == "fa_") {
					$right['modify'] = true;
					$sr = true;
				} elseif (substr($key, 0, 3) == "to_") {
					$right['table_owner'] = true;
					$sr = true;
				} elseif ($key == $search) {
					$right['read'] = true;
				} else {
					$right['unknown key'] = true;
					$sr = true;
				}
				
			}
		}
		
		if (!$sr && count($ar) > 0) {
			$ts['flextable' . $ftar['recordid']] = array("no access" => true);
		} elseif (count($right) > 0) {
			$ts['flextable' . $ftar['recordid']] = $right;
		} else {
			$ts['flextable' . $ftar['recordid']] = array("plain access" => true);
		}

	}
	$right = "";
	$sr = false;
	

	foreach ($tmp2 AS $efar) {
		$ar = unserialize($efar['accessarray']);
		$sr = false;
		foreach ($ar AS $key) {
			$right = array();
			if (substr($key, strlen($key) - strlen($search), strlen($search)) == $search) {
				if (substr($key, 0, 3) == "fa_") {
					$right['modify'] = true;
					$sr = true;
				} elseif (substr($key, 0, 3) == "to_") {
					$right['table_owner'] = true;
					$sr = true;
				} elseif ($key == $search) {
					$right['read'] = true;
				} else {
					$right['unknown key'] = true;
					$sr = true;
				}
			}
		}
		
		if (!$sr && count($ar) > 0) {
			$ts['extrafield' . $efar['id']] = array("no access" => true);
		} elseif (count($right) > 0) {
			$ts['extrafield' . $efar['id']] = $right;
		} else {
			$ts['extrafield' . $efar['id']] = array("plain access" => true);
		}

	}
	$right = "";
	$sr = false;

	foreach ($tmp3 AS $efar) {
		$sr = false;
		$ar = unserialize($efar['module_accessarray']);
		foreach ($ar AS $key) {
			$right = array();
			if (substr($key, strlen($key) - strlen($search), strlen($search)) == $search) {
				if (substr($key, 0, 3) == "fa_") {
					$right['execute'] = true;
					$sr = true;
				} elseif (substr($key, 0, 3) == "to_") {
					$right['table_owner'] = true;
					$sr = true;
				} elseif ($key == $search) {
					$right['read'] = true;
				} else {
					$right['unknown key'] = true;
					$sr = true;
				}
			}
		}
		
		if (!$sr && count($ar) > 0) {
			$ts['module' . $efar['mid']] = array("no access" => true);
		} elseif (count($right) > 0) {
			$ts['module' . $efar['mid']] = $right;
		} else {
			$ts['module' . $efar['mid']] = array("plain access" => true);
		}

	}
	return($ts);
}

?>