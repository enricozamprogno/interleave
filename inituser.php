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
function InitUser($specific_user = false) {
	global $lang;

	if ($specific_user < 1) {
		$specific_user = $GLOBALS['USERID'];
	}

	// Initialize UserCache array in globals (make sure it's an array AND it's empty)

	$GLOBALS['UC'] = array();

	// Purely developement aids

	/*
	if ($GLOBALS['logtext'] == true) { // Only process in development mode
		if ($GLOBALS['LastUserInitCall'] == "") {
			$GLOBALS['LastUserInitCall'] = $specific_user;
			$GLOBALS['LastUserInitCallTrace'] = GetBackTrace();

		} elseif ($GLOBALS['LastUserInitCall'] == $specific_user) { 
			DA("User initialization called for the same user as last time!"); // ok
			print "<h1>First time:</h1><pre>";
			print_r($GLOBALS['LastUserInitCallTrace']);
			print "</h2>";
			EndHTML();
			exit;
		}
	}
	*/

	// Drop all cache currently in memory, excluding ['UC']

	ClearAllRunningCache();

	// Fetch session-wide memory

	if ($specific_user == $GLOBALS['USERID']) {

		// Fetch session cache 

		
		$GLOBALS['SesMem'] = unserialize(db_GetValue("SELECT sessioncache FROM " . $GLOBALS['TBL_PREFIX'] . "sessions WHERE temp='" . mres($GLOBALS['session_id']) . "'"));
		$GLOBALS['SesMem']["Initialized"] = "Yes";


	} else {
		
		// The user being loaded is not the user actually owning this session, so un-set SesMem
		$GLOBALS['SesMem'] = array();
		$GLOBALS['SesMemDontCommit'] = true;


	}



	// Check if reasons for access must be shown

	if (isset($_GET['reason']) || isset($_GET['reasons'])) {
		$GLOBALS['SesMem']['ShowReasons'] = true;
	} elseif (isset($_GET['noreason']) || isset($_GET['noreasons'])) {
		$GLOBALS['SesMem']['ShowReasons'] = false;
	}
	
	

	// Check if locks may be removed. If so, do so.
	if (!stristr($_SERVER['SCRIPT_NAME'],"calendar.php") && !stristr($_SERVER['SCRIPT_NAME'],"fileupload_frame.php") && !$_REQUEST['keeplocked'] && !isset($_REQUEST['AjaxHandler'])) {
		RemoveLocks();
	}

	// Check IP restrictions (wrong place, but works)
	CheckIPSourceSecurity();
	
	if ($specific_user) {
	
		// Overrule user-specific allows and disallows
	
		if ((!stristr($_SERVER['SCRIPT_NAME'],"upgrade.php")) && !stristr($_SERVER['PHP_SELF'],"install.php")) {

			$row = GetUserRow($specific_user);

			// Straight forward copies into UserCache

			$GLOBALS['UC']['PERSONALTRACE']						= $row['TRACE'];
			$GLOBALS['UC']['USEDASHBOARDASENTRY']				= $row['USEDASHBOARDASENTRY'];
			$GLOBALS['UC']['HIDEOVERDUEFROMDUELIST']			= $row['HIDEOVERDUEFROMDUELIST'];
			$GLOBALS['UC']['IMPORTANTENTITIES']					= $row['IMPORTANTENTITIES'];
			$GLOBALS['UC']['MENUTOUSE']							= $row['MENUTOUSE'];
			$GLOBALS['UC']['USERSPECTRUM']						= $row['USERSPECTRUM'];
			$GLOBALS['UC']['ENTITYACCESSEVALMODULE']			= $row['ENTITYACCESSEVALMODULE'];
			$GLOBALS['UC']['CUSTOMERACCESSEVALMODULE']			= $row['CUSTOMERACCESSEVALMODULE'];
			$GLOBALS['UC']['ADDFORMLIST']						= array();
			$GLOBALS['UC']['ALLOWEDADDFORMS']					= array();
			$GLOBALS['UC']['INTERACTIVEFIELDSLIST']				= array();
			if (CheckFunctionAccess("AllFormsAllowed") == "ok" || CheckFunctionAccess("AllFormsAllowed") == "admin") {
				$GLOBALS['UC']['ADDFORMLIST'] = db_GetFlatArray("SELECT templateid FROM " . $GLOBALS['TBL_PREFIX'] . "templates");
				$GLOBALS['UC']['ALLOWEDADDFORMS'] = $GLOBALS['UC']['ADDFORMLIST'];
			} else {

				$GLOBALS['UC']['ADDFORMLIST']						= @unserialize($row['ADDFORMS']);
				$GLOBALS['UC']['ALLOWEDADDFORMS']					= @unserialize($row['ALLOWEDADDFORMS']);
			}
			$GLOBALS['UC']['INTERACTIVEFIELDSLIST']				= @unserialize($row['INTERACTIVEFIELDS']);
			$GLOBALS['UC']['DateFormat'] = GetSetting("DateFormat");
		
			$localDF = GetAttribute("user", "DateFormat", $specific_user);
			$localIF = GetAttribute("user", "InputNumbersWithSeperators", $specific_user);
					
			if ($localDF == "") {
				SetAttribute("user", "DateFormat", "{{system}}", $GLOBALS['UC']['USERPROFILE'], array("dd-mm-yyyy", "mm-dd-yyyy", "yyyy-mm-dd", "{{system}}"));
			} elseif ($localDF != "{{system}}") {
				$GLOBALS['UC']['DateFormat'] == $localDF;
				
			}
			if ($localIF == "") {
				SetAttribute("user", "InputNumbersWithSeperators", "{{system}}", $GLOBALS['UC']['USERPROFILE'], array("Yes", "No", "{{system}}"));
			} elseif ($localIF != "{{system}}") {
				$GLOBALS['UC']['InputNumbersWithSeperators'] = $localIF;
			}


			// Check if the user has a boss

			if (is_numeric($row['BOSS']) && $row['BOSS'] != 0) {

				$GLOBALS['UC']['USER_BOSS']						= $row['BOSS'];

			}

			// Check if the user suffers from form limiting

			if ($row['FORCEFORM'] != "no_force" && $row['FORCEFORM'] != "" && $row['FORCEFORM'] != "0") {
				$GLOBALS['UC']['FORCEFORM']						= $row['FORCEFORM'];
				qlog(INFO, "This user suffers from form limiting (" . $row['FORCEFORM'] . ")");
			} 
			
			// If ViewAllRecords is set, all record-access security will be disabled

			if (CheckFunctionAccess("ViewAllRecords") == "ok") {			
				$GLOBALS['UC']['ViewAllRecords'] = true;

			}
			
			// If users may select their own list layout, load the column layout

			if (strtoupper($GLOBALS['LetUserSelectOwnListLayout'])=="YES") {
				$x = @unserialize($row['ELISTLAYOUT']);
				if (is_array($x)) {
						$GLOBALS['UC']['MainListColumnsToShow'] = $x;
				}
				unset($x);
				$x = @unserialize($row['CLISTLAYOUT']);
				if (is_array($x)) {
						$GLOBALS['UC']['CustomerListColumnsToShow'] = $x;
				}
				//DA("LOAD USER PREF");
			} else {
				$GLOBALS['UC']['CustomerListColumnsToShow'] = unserialize(GetSetting('CustomerListColumnsToShow'));
				//DA("LOAD DEF CCL");
				$GLOBALS['UC']['MainListColumnsToShow'] = unserialize(GetSetting('MainListColumnsToShow'));
			}

			$GLOBALS['UC']['LIMITTOCUSTOMERS'] = FlattenArray(explode(";", trim($row['LIMITTOCUSTOMERS'])));

			//$GLOBALS['UC']['MainListColumnsToShow'][$_REQUEST['CustomColumnLayout']]

			if ($GLOBALS['UC']['LIMITTOCUSTOMERS'] != "") {
				qlog(INFO, "This user is limited: he/she can only work with these customers: " . $row['LIMITTOCUSTOMERS'] . " (by profile)");
			} else {
				qlog(INFO, "This user has no limits (by profile)");
				unset($GLOBALS['UC']['LIMITTOCUSTOMERS']);
				//print "no mark";
			}
			if ($arr['FORCESTARTFORM'] != "0") {
				$GLOBALS['UC']['FORCESTARTFORM'] = $arr['FORCESTARTFORM'];
				qlog(INFO, "This user has a start form (" . $arr['FORCESTARTFORM'] . ")");
			}

			// Cycle through LIMITTOCUSTOMERS to see if there is an SQL query in there

			$c = sizeof($GLOBALS['UC']['LIMITTOCUSTOMERS']);

			for ($t = 0 ; $t <  $c ; $t ++) {
				if (substr($GLOBALS['UC']['LIMITTOCUSTOMERS'][$t], 0, 7) == "SELECT ") {
					
					// This element contains an SQL query

					foreach (db_GetFlatArray(ParseDefaultValueTags($GLOBALS['UC']['LIMITTOCUSTOMERS'][$t])) AS $add) {
						array_push($GLOBALS['UC']['LIMITTOCUSTOMERS'], $add);
					}
					qlog(INFO, "LIMITTOCUSTOMERS SQL Query executed: " . ($GLOBALS['UC']['LIMITTOCUSTOMERS'][$t]));
					unset($GLOBALS['UC']['LIMITTOCUSTOMERS'][$t]);
				}
			}
			if (is_array($GLOBALS['UC']['LIMITTOCUSTOMERS'])) {
				$GLOBALS['UC']['LIMITTOCUSTOMERS'] = FlattenArray($GLOBALS['UC']['LIMITTOCUSTOMERS']);
			}

			if (is_array($GLOBALS['UC']['LIMITTOCUSTOMERS']) && count($GLOBALS['UC']['LIMITTOCUSTOMERS']) > 0) {
				$tmp = false;
				for ($x=0;$x<sizeof($GLOBALS['UC']['LIMITTOCUSTOMERS']);$x++) {
					if (IsValidCID($GLOBALS['UC']['LIMITTOCUSTOMERS'][$x])) {
						$tmp = true;
					} else {
						unset($GLOBALS['UC']['LIMITTOCUSTOMERS'][$x]);
					}
				}
				if (!$tmp && !is_administrator()) {
					PrintAD("No valid customer found - access denied");
					log_msg("ERROR: This user has a customer limit but there was no valid customer found - he/she cannot enter the application!");
					PrintAD("No suitable customer access found. Access to application denied.");
					EndHTML();
					exit;
				}
			}
			
			// If no limits were found,empty variable

			if (count($GLOBALS['UC']['LIMITTOCUSTOMERS']) == 0) {
				$GLOBALS['UC']['LIMITTOCUSTOMERS'] = "";
			}

			// Now start loading the profile the user is in (if any) and let values from the profile overrule the personal values

			if ($row['PROFILE'] > 0) {
				
				$profilenum = $row['PROFILE'];

				$row = GetProfileArray($row['PROFILE']);
				
				
				$GLOBALS['UC']['USERPROFILE']					= $profilenum;
				$GLOBALS['UC']['GROUP']							= $profilenum;
				// Leave for compatibility
				$GLOBALS['USERPROFILE']							= $profilenum;
				$GLOBALS['GROUP']								= $profilenum;
				$GLOBALS['UC']['USERSPECTRUM']					= $row['USERSPECTRUM'];
				$GLOBALS['UC']['ENTITYACCESSEVALMODULE']		= $row['ENTITYACCESSEVALMODULE'];
				$GLOBALS['UC']['CUSTOMERACCESSEVALMODULE']		= $row['CUSTOMERACCESSEVALMODULE'];
				if (CheckFunctionAccess("AllFormsAllowed") == "ok" || CheckFunctionAccess("AllFormsAllowed") == "admin") {
					$GLOBALS['UC']['ADDFORMLIST'] = db_GetFlatArray("SELECT templateid FROM " . $GLOBALS['TBL_PREFIX'] . "templates");
					$GLOBALS['UC']['ALLOWEDADDFORMS'] = $GLOBALS['UC']['ADDFORMLIST'];
				} else {
					$GLOBALS['UC']['ALLOWEDADDFORMS']				= @unserialize($row['ALLOWEDADDFORMS']);
					$tmp = @unserialize($row['ADDFORMS']);
					if (sizeof($tmp) > 0) {
						qlog(INFO, "List of allowed forms to use override by profile in effect");
						$GLOBALS['UC']['ADDFORMLIST'] = $tmp;
					}
				}

				if ($row['BOSS'] != "" && $row['BOSS'] != 0 && $GLOBALS['USEINDIVIDUALBOSS'] != "Yes") {
					$GLOBALS['UC']['USER_BOSS'] = $row['BOSS'];
					qlog(INFO, "BOSS setting overrule by profile: " . $row['BOSS']);
				}
				if ($row['FORCEFORM'] != "no_force" && $row['FORCEFORM'] != "" && $row['FORCEFORM'] != "0") {
					$GLOBALS['UC']['FORCEFORM'] = $row['FORCEFORM'];
					qlog(INFO, "This user suffers from form limiting (" . $row['FORCEFORM'] . ") (by profile, which overrules user settings)");
				}
				if (is_numeric($row['ENTITYADDFORM'])) {
					$GLOBALS['ENTITY_LIMITED_ADD_FORM'] = $row['ENTITYADDFORM'];
					$GLOBALS['UC']['ENTITY_ADD_FORM'] = $row['ENTITYADDFORM'];
					qlog(INFO, "Entity add form override by profile in effect.");
				}
				if (is_numeric($row['ENTITYEDITFORM'])) {
					$GLOBALS['UC']['ENTITY_LIMITED_EDIT_FORM'] = $row['ENTITYEDITFORM'];
					$GLOBALS['UC']['ENTITY_EDIT_FORM'] = $row['ENTITYEDITFORM'];
					qlog(INFO, "Entity edit form override by profile in effect.");
				}
				if (is_numeric($row['MENUTOUSE'])) {
					$GLOBALS['UC']['MENUTOUSE'] = $row['MENUTOUSE'];
					qlog(INFO, "Menu type override by profile in effect.");
				}




				// Locale settings
				$localDF = GetAttribute("group", "DateFormat", $GLOBALS['UC']['USERPROFILE']);
				$localIF = GetAttribute("group", "InputNumbersWithSeperators", $GLOBALS['UC']['USERPROFILE']);
						
				if ($localDF == "") {
					SetAttribute("group", "DateFormat", "{{system}}", $GLOBALS['UC']['USERPROFILE'], array("dd-mm-yyyy", "mm-dd-yyyy", "yyyy-mm-dd", "{{system}}"));
				} elseif ($localDF != "{{system}}") {
					$GLOBALS['UC']['DateFormat'] == $localDF;
					
				}
				if ($localIF == "") {
					SetAttribute("group", "InputNumbersWithSeperators", "{{system}}", $GLOBALS['UC']['USERPROFILE'], array("Yes", "No", "{{system}}"));
				} elseif ($localIF != "{{system}}") {
					$GLOBALS['UC']['InputNumbersWithSeperators'] = $localIF;
				}

				$GLOBALS['UC']['INTERACTIVEFIELDSLIST'] = @unserialize($row['INTERACTIVEFIELDS']);
			}

			// Pofile properties are now loaded in $row, overruling any local settings

			// Check if user has his own dashboard setting

			if ($row['DASHBOARDFILEID'] > 0) {

				$GLOBALS['UC']['DASHBOARDTEMPLATE']			= $row['DASHBOARDFILEID'];

				qlog(INFO, "Dashboard id overridden by user setting");

				
			} else {
				$dash = GetSetting("DASHBOARDTEMPLATE");

				if ($dash > 0) {
					$GLOBALS['UC']['DASHBOARDTEMPLATE']			= $dash;
					qlog(INFO, "Dashboard id overridden by local setting");
				}


			}

			if (is_numeric($GLOBALS['UC']['FORCEFORM'])) {
				if (!in_array($GLOBALS['UC']['FORCEFORM'],$GLOBALS['UC']['ADDFORMLIST'])) {
					log_msg("Error: this user is forced to using a form to which he/she has no access!");
				}
			}
			if ($row['HIDEADDTAB'] == "n") {
					$GLOBALS['UC']['HIDEADDTAB'] = "No";
					$GLOBALS['UC']['e_HIDEADDTAB'] = "No";
					$HIDEADDTAB = "No";
			} elseif ($row['HIDEADDTAB'] == "y") {
					$GLOBALS['UC']['HIDEADDTAB'] = "Yes";
					$GLOBALS['UC']['e_HIDEADDTAB'] = "Yes";
					$HIDEADDTAB = "Yes";
			}
			if ($row['HIDECSVTAB'] == "n") {
					$GLOBALS['UC']['HIDECSVTAB'] = "No";
					$GLOBALS['UC']['e_HIDECSVTAB'] = "No";
					$HIDECSVTAB = "No";
			} elseif ($row['HIDECSVTAB'] == "y") {
					$GLOBALS['UC']['HIDECSVTAB'] = "Yes";
					$GLOBALS['UC']['e_HIDECSVTAB'] = "Yes";
					$HIDECSVTAB = "Yes";
			}
			if ($row['HIDEPBTAB'] == "n") {
					$GLOBALS['UC']['HIDEPBTAB'] = "No";
					$GLOBALS['UC']['e_HIDEPBTAB'] = "No";
					$HIDEPBTAB = "No";
			} elseif ($row['HIDEPBTAB'] == "y") {
					$GLOBALS['UC']['HIDEPBTAB'] = "Yes";
					$GLOBALS['UC']['e_HIDEPBTAB'] = "Yes";
					$HIDEPBTAB = "Yes";
			}
			if ($row['HIDESUMMARYTAB'] == "n") {
					$GLOBALS['UC']['HIDESUMMARYTAB'] = "No";
					$GLOBALS['UC']['e_HIDESUMMARYTAB'] = "No";
					$HIDESUMMARYTAB = "No";
			} elseif ($row['HIDESUMMARYTAB'] == "y") {
					$GLOBALS['UC']['HIDESUMMARYTAB'] = "Yes";
					$GLOBALS['UC']['e_HIDESUMMARYTAB'] = "Yes";
					$HIDESUMMARYTAB = "Yes";
			}
			if ($row['HIDEENTITYTAB'] == "n") {
					$GLOBALS['UC']['HIDEENTITYTAB'] = "No";
					$GLOBALS['UC']['e_HIDEENTITYTAB'] = "No";
					$HIDEENTITYTAB = "No";
			} elseif ($row['HIDEENTITYTAB'] == "y") {
					$GLOBALS['UC']['HIDEENTITYTAB'] = "Yes";
					$GLOBALS['UC']['e_HIDEENTITYTAB'] = "Yes";
					$HIDEENTITYTAB = "Yes";
			}
			if ($row['HIDECUSTOMERTAB'] == "n") {
					$GLOBALS['UC']['HIDECUSTOMERTAB'] = "No";
					$GLOBALS['UC']['e_HIDECUSTOMERTAB'] = "No";
					$HIDECUSTOMERTAB = "No";
			} elseif ($row['HIDECUSTOMERTAB'] == "y") {
					$GLOBALS['UC']['HIDECUSTOMERTAB'] = "Yes";
					$GLOBALS['UC']['e_HIDECUSTOMERTAB'] = "Yes";
					$HIDECUSTOMERTAB = "Yes";
			}
			if ($row['SHOWDELETEDVIEWOPTION'] == "n") {
					$GLOBALS['ShowDeletedViewOption'] = "No";
					$ShowDeletedViewOption = "No";
			} elseif ($row['SHOWDELETEDVIEWOPTION'] == "y") {
					$GLOBALS['ShowDeletedViewOption'] = "Yes";
					$ShowDeletedViewOption = "Yes";
			}
			if ($row['HIDECUSTOMERTAB'] == "n") {
					$GLOBALS['UC']['HIDECUSTOMERTAB'] = "No";
					$HideCustomerTab = "No";
			} elseif ($row['HIDECUSTOMERTAB'] == "y") {
					$GLOBALS['UC']['HIDECUSTOMERTAB'] = "Yes";
					$HideCustomerTab = "Yes";
			}
			$GLOBALS['UC']['USER_ALLOWED_STATUSSES']  = @unserialize($row['ALLOWEDSTATUSVARS']);
			$GLOBALS['UC']['USER_ALLOWED_PRIORITIES'] = @unserialize($row['ALLOWEDPRIORITYVARS']);

			// Some checks

			if (!is_array($GLOBALS['UC']['USER_ALLOWED_STATUSSES']) || sizeof($GLOBALS['UC']['USER_ALLOWED_STATUSSES']) == 0) {
				$GLOBALS['UC']['USER_ALLOWED_STATUSSES'] = array("All");
			}
			if (!is_array($GLOBALS['UC']['USER_ALLOWED_PRIORITIES'])) {
				$GLOBALS['UC']['USER_ALLOWED_PRIORITIES'] = array("All");
			}

			if ($GLOBALS['UC']['ENTITYACCESSEVALMODULE'] != 0 && is_numeric($GLOBALS['UC']['ENTITYACCESSEVALMODULE'])) {
				qlog(INFO, "Entity access extra code evaluation in effect (module " . $GLOBALS['UC']['ENTITYACCESSEVALMODULE'] . ")");
				$tmp = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "modules WHERE mid='" . mres($GLOBALS['UC']['ENTITYACCESSEVALMODULE']) . "'");
				$GLOBALS['UC']['ENTITYACCESSEVALMODULE_CODE'] = $tmp['module_code'];

				$cnt = GetAttribute("module", "ModuleRunCount", $GLOBALS['UC']['ENTITYACCESSEVALMODULE']);
				if (!is_numeric($cnt)) { 
					$cnt = 1;
				} else {
					$cnt++;
				}
				SetAttribute("module", "ModuleRunCount", $cnt, $GLOBALS['UC']['ENTITYACCESSEVALMODULE']);
			}

			if ($GLOBALS['UC']['CUSTOMERACCESSEVALMODULE'] != 0 && is_numeric($GLOBALS['UC']['CUSTOMERACCESSEVALMODULE'])) {
				qlog(INFO, "Customer access extra code evaluation in effect (module " . $GLOBALS['UC']['CUSTOMERACCESSEVALMODULE'] . ")");
				$tmp = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "modules WHERE mid='" . mres($GLOBALS['UC']['CUSTOMERACCESSEVALMODULE']) . "'");
				$GLOBALS['UC']['CUSTOMERACCESSEVALMODULE_CODE'] = $tmp['module_code'];
	
				$cnt = GetAttribute("module", "ModuleRunCount", $GLOBALS['UC']['CUSTOMERACCESSEVALMODULE_CODE']);
				if (!is_numeric($cnt)) { 
					$cnt = 1;
				} else {
					$cnt++;
				}
				SetAttribute("module", "ModuleRunCount", $cnt, $GLOBALS['UC']['CUSTOMERACCESSEVALMODULE_CODE']);
			}

			
			$GLOBALS['UC']['MainListColumnsToShow'] = GetEntitylistColumns();

			

			if ($row['MENUTOUSE'] == 0) {
				$row['MENUTOUSE'] = "default";
			}
		
			// Locale settings
			$localDF = GetAttribute("user", "DateFormat", $specific_user);
			$localIF = GetAttribute("user", "InputNumbersWithSeperators", $specific_user);
					
			if ($localDF == "") {
				SetAttribute("user", "DateFormat", "{{system}}", $specific_user, array("dd-mm-yyyy", "mm-dd-yyyy", "yyyy-mm-dd", "{{system}}"));
			} elseif ($localDF != "{{system}}") {
				$GLOBALS['UC']['DateFormat'] = $localDF;
			}
			if ($localIF == "") {
				SetAttribute("user", "InputNumbersWithSeperators", "{{system}}", $specific_user, array("Yes", "No", "{{system}}"));
			} elseif ($localIF != "{{system}}") {
				$GLOBALS['UC']['InputNumbersWithSeperators'] = $localIF;
			} 

			// Fetch user customer limitations
			
			if (!is_array($GLOBALS['UC']['LIMITTOCUSTOMERS'] )) {
				$GLOBALS['UC']['LIMITTOCUSTOMERS'] = FlattenArray(explode(";", trim($row['LIMITTOCUSTOMERS'])));

				if ($GLOBALS['UC']['LIMITTOCUSTOMERS'] != "") {
					qlog(INFO, "This user is limited: he/she can only work with these customers: " . $row['LIMITTOCUSTOMERS'] . " (by profile)");
				} else {
					qlog(INFO, "This user has no limits (by profile)");
					unset($GLOBALS['UC']['LIMITTOCUSTOMERS']);
					//print "no mark";
				}
				if ($arr['FORCESTARTFORM'] != "0") {
					$GLOBALS['UC']['FORCESTARTFORM'] = $arr['FORCESTARTFORM'];
					qlog(INFO, "This user has a start form (" . $arr['FORCESTARTFORM'] . ")");
				}

				// Cycle through LIMITTOCUSTOMERS to see if there is an SQL query in there

				$c = sizeof($GLOBALS['UC']['LIMITTOCUSTOMERS']);

				for ($t = 0 ; $t <  $c ; $t ++) {
					if (substr($GLOBALS['UC']['LIMITTOCUSTOMERS'][$t], 0, 7) == "SELECT ") {
						
						// This element contains an SQL query

						foreach (db_GetFlatArray(ParseDefaultValueTags($GLOBALS['UC']['LIMITTOCUSTOMERS'][$t])) AS $add) {
							array_push($GLOBALS['UC']['LIMITTOCUSTOMERS'], $add);
						}
						qlog(INFO, "LIMITTOCUSTOMERS SQL Query executed: " . ($GLOBALS['UC']['LIMITTOCUSTOMERS'][$t]));
						unset($GLOBALS['UC']['LIMITTOCUSTOMERS'][$t]);
					}
				}
				if (is_array($GLOBALS['UC']['LIMITTOCUSTOMERS'])) {
					$GLOBALS['UC']['LIMITTOCUSTOMERS'] = FlattenArray($GLOBALS['UC']['LIMITTOCUSTOMERS']);
				}

				if (is_array($GLOBALS['UC']['LIMITTOCUSTOMERS']) && count($GLOBALS['UC']['LIMITTOCUSTOMERS']) > 0) {
					$tmp = false;
					for ($x=0;$x<sizeof($GLOBALS['UC']['LIMITTOCUSTOMERS']);$x++) {
						if (IsValidCID($GLOBALS['UC']['LIMITTOCUSTOMERS'][$x])) {
							$tmp = true;
						} else {
							unset($GLOBALS['UC']['LIMITTOCUSTOMERS'][$x]);
						}
					}
					if (!$tmp && !is_administrator()) {
						PrintAD("No valid " . $lang['customer'] . " found - access denied");
						log_msg("ERROR: This user has a customer limit but there was no valid customer found - he/she cannot enter the application!");
						PrintAD("No suitable customer access found. Access to application denied.");
						EndHTML();
						exit;
					}
				}
				
				// If no limits were found,empty variable

				if (count($GLOBALS['UC']['LIMITTOCUSTOMERS']) == 0) {
					$GLOBALS['UC']['LIMITTOCUSTOMERS'] = "";
				}
			}

			// Hide tabs if set

			if (CheckFunctionAccess("HideNavigationTabs") == "ok") {
				$nonavbar = 1;
				$_REQUEST['nonavbar'] = 1;
			}

	


			// Load access cache table

			if ($GLOBALS['USE_EXTENDED_CACHE'] == "Yes" && !$GLOBALS['USE_EXTENDED_CACHE_LOADED']) {
				qlog(INFO, "Loading entity and customer access cache tables");
				$sql = "SELECT eidcid, result, type FROM " . $GLOBALS['TBL_PREFIX'] . "accesscache WHERE user='" . mres($specific_user) . "' AND (type = 'e' OR type = 'c') LIMIT 10000";
				$res = mcq($sql, $db);
				while ($row = mysql_fetch_array($res)) {
					if ($row['result'] != "") {
						if ($row['type'] == "c") {
							$GLOBALS['PageCache']['CheckedCustomerAccessArray'][$row['eidcid']][$GLOBALS['USERID']] = $row['result'];
						} elseif ($row['type'] == "e") {
							$GLOBALS['PageCache']['CheckedEntityAccessArray'][$row['eidcid']][$GLOBALS['USERID']] = $row['result'];
						}
						$t++;
					}
				}
				qlog(INFO, $t . " records loaded");
				$GLOBALS['USE_EXTENDED_CACHE_LOADED'] = true;
			}




		} else {
			qlog(INFO, "Bypassing user credentials - install/upgrade exception");
		}
		
		
	} else {
		qlog(ERROR, "ERROR: InitUser called without USERID!");

	}
	

}


?>