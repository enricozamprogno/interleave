<?php
/* ********************************************************************
* Interleave
* Copyright (c) 2001-2012 info@interleave.nl
* Licensed under the GNU GPL. For full terms see http://www.gnu.org/
*
* This script is one of two main AJAX call handlers (the other one is populate.php)
*
* Check http://www.interleave.nl/ for more information
*
* BUILD / RELEASE :: 5.5.1.20121028
*
**********************************************************************
*/	
require_once("initiate.php");

$_REQUEST['keeplocked'] = true;
$_REQUEST['AjaxAssist'] = true;

$GLOBALS['BrowseArray'] = $_REQUEST['BrowseArray'];


header("Content-Type: text/html; charset=UTF-8;");

$PAGE_START = microtime_float();

if ($_REQUEST['checkUserEvents']) {
	
	$tp = "MSG:"; // Leave for client side check, will not be printed

	if (GetSetting("DisableAllInternalMessagePopups") == "Yes") {
		$stp = false;
	} else {

		if (!is_array($GLOBALS['SesMem']['ShownMessages'])) {
			$GLOBALS['SesMem']['ShownMessages'] = array();
		}
			if (!is_array($GLOBALS['SesMem']['ShownMessages']['internalmessages'])) {
			$GLOBALS['SesMem']['ShownMessages']['internalmessages'] = array();
		}

		$calendars = GetAccessibleCalendars();
		
		foreach ($calendars AS $calKey => $calObj) {

			$local_calendar = GetCalendarEvents($calKey, true, true, date('Y-m'));
			
			foreach ($local_calendar AS $event) {
				$start_hour = substr($event['starttime'], 0, 2);
				$start_day = substr($event['startdate'], 0, 2);
				$start_month = substr($event['startdate'], 3, 2);
				if ($start_day == date('d') && $start_month == date('m')) {
					if ($start_hour == date('H')) {
						$start_minute = substr($event['starttime'], 3, 2);
						
						if ($event['show_popup_before'] == "") $event['show_popup_before'] = "30,5,0";

						$list = explode(",", $event['show_popup_before']);

						foreach ($list AS $minute_before) {
							if ($start_minute < (date('i ') + $minute_before) && $start_minute > (date('i') -1)) {

								$id = md5($event['startdate'] . $event['summary'] . $minute_before);

								if ($GLOBALS['SesMem']['ShownMessages'][$id] != "done") {

									$tp .=  $event['calendarname'] . " " . $minute_before . " : " . $event['summary'] . "\n";
									$tp .=  $event['starttime'] . " - " . $event['duetime'] . "";
									if ($event['location'] != "") {
										$tp .=  ", location: " . $event['location'] . "";
									}
									$tp .=  "\n";
									if ($event['comment'] != "") {
										$tp .=  "Comment: " . $event['comment'] . "\n";
									}
									//DA($event); 
									$tp .=  " ------ ------ ------- ------ ------ -------\n";

									$GLOBALS['SesMem']['ShownMessages'][$id] = "done";

									$stp = true;

								} else {

									// print "not showing again";
								}

								//DA($event);
							} else {
									// print "wrong minute: " . $start_minute . " == =" . (date('i') + 5); 
						//		DA($event);
							}
						}
					} else {
						// print "wrong hour";
					}
				} else {
					// print "wrong day";
				}
			}
		}
		
		if (is_administrator()) {
			$sql_ins = "(`to`='" . $GLOBALS['USERID'] . "' OR `to`=0)";
		} else {
			$sql_ins = "`to`='" . $GLOBALS['USERID'] . "'";
		}
		$result = db_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "internalmessages WHERE ". $sql_ins . " AND UNIX_TIMESTAMP(timestamp_last_change) > " . (date('U') - (5 * 60)));

		if (count($result) > 0) {
			$tp = $tp. $lang['messageinbox'] . ": " . count($result) . " " . strtolower($lang['incomingmessages']) . "\n\n";

			foreach ($result AS $message) {

				if ($GLOBALS['SesMem']['ShownMessages']['internalmessages'][$message['id']] != "done") {
					$stp = true;
					$GLOBALS['SesMem']['ShownMessages']['internalmessages'][$message['id']] = "done";
				} 
				if (GetUserName($message['from']) != "") {
					$tp .= "From: " . addslashes(GetUserName($message['from'])) . ", ";
				}
				$tp .= $message['id'] . " Subject: " . addslashes(strip_tags(htmlspecialchars_decode($message['subject']))). "\n";
						
			}
		}
	}


	if ($stp) {
		print $tp;
	} else {
		ob_end_clean();
		exit;
	}


} elseif ($_REQUEST['Function']) {
	$func = $_REQUEST['Function'];
	if ($_REQUEST['Run'] && $_REQUEST['Function'] <> "DDFieldVal") {

		if (isset($_REQUEST['qid'])) {

			if ($func == "ManagementInformationSummary") {		print ManagementInformationSummary($_REQUEST['qid']); }
			if ($func == "ShowDiary")	{						print ShowDiary($_REQUEST['qid']); }
			if ($func == "entities_owned_by_users") {			print entities_owned_by_users($_REQUEST['qid']); }
			if ($func == "entities_assigned_to_users") {		print entities_assigned_to_users($_REQUEST['qid']); }
			if ($func == "self_assigned") {						print self_assigned($_REQUEST['qid']); }
			if ($func == "overdue_per_assignee") {				print overdue_per_assignee($_REQUEST['qid']); }
			if ($func == "entities_per_customer") {				print entities_per_customer($_REQUEST['qid']); }
			if ($func == "Top10_Most_active") {					print Top10_Most_active($_REQUEST['qid']); }
			if ($func == "Top10_Most_active_users") {			print Top10_Most_active_users($_REQUEST['qid']); }
			if ($func == "Top20_Most_Slow") {					print Top20_Most_Slow($_REQUEST['qid']); }
			if ($func == "Top20_Most_Slow_Deleted") {			print Top20_Most_Slow_Deleted($_REQUEST['qid']); }
			if ($func == "summonth") {							print summonth($_REQUEST['qid']); }
		} else {
			
			if ($func == "ShowAdvancedQueryInterface") {		print ShowAdvancedQueryInterface($_REQUEST['Scope'], $_REQUEST['ListId']); }
			if ($func == "ShowCalendar")	{
				require("show_calendar.php");
				print ShowCalendar($_REQUEST['calObjId'], $_REQUEST['view']); 
			}
			
			if ($func == "ReturnInlineFlextableForm") {			
				if ($_REQUEST['ilft'] > 0) {
					print ReturnInlineFlextableForm($_REQUEST['ilft'], $_POST['refer']); 
				} else {
					PrintAD("No table received. Quitting.");
				}
			}
			if ($func == "AutoSave") {							print AutoSave(); }
			if ($func == "ValidateInput") {						print ValidateInputHelper($_POST['Worker'], $_POST['Header'], $_POST['SelectedField'], $_POST['Table']); }
			if ($func == "AutoSaveFlexTableForm") {				print AutoSaveFlexTableForm(); }
			if ($func == "BuildCustomEditForm2") {				print BuildCustomEditForm2(); }
			if ($func == "CheckAndSetPlanning") {				print CheckAndSetPlanning(); }
			if ($func == "CreateRestorePoint") {				print CreateRestorePoint(); }
			if ($func == "DisplayFileList") {					print DisplayFileList(); }
			if ($func == "PrintEntityVincinity") {				print PrintEntityVincinity(); }
			if ($func == "PrintReposOptions") {					print PrintReposOptions(); }
			if ($func == "ReturnCompleteFlextable") {			print ReturnCompleteFlextable($_REQUEST['ShowTable'], PopStashValue($_POST['Source'])); }
			if ($func == "ReturnInlinePlanningPart") {			print ReturnInlinePlanningPart(); }
			if ($func == "ReturnPlanningPart") {				print ReturnPlanningPart(); }
			if ($func == "ReturnRepositorySwitcher") {			print ReturnRepositorySwitcher(); }
			if ($func == "RunModule") {							print RunModule(); }
			if ($func == "ShowCustomerList") {					print ShowCustomerList(PopStashValue($_POST['Source'])); }
			if ($func == "ShowDashboardOptions") {				print ShowDashboardOptions(); }
			if ($func == "ShowEntityList") {					print ShowEntityList(PopStashValue($_POST['Source'])); }
			if ($func == "ShowFlexTableContents") {				print ShowFlexTableContents(); }
			if ($func == "ShowMessagesList") {					print ShowMessagesList(); }
			if ($func == "ShowPersonalStats") {					print ShowPersonalStats(); }
			if ($func == "ShowRecentEntities") {				print ShowRecentEntities(); }
			if ($func == "ShowShortCalendar") {					print ShowShortCalendar(); }
			if ($func == "ShowTodaysEntities") {				print ShowTodaysEntities(); }
			if ($func == "StandAloneCustomerSearchbox") {		print StandAloneCustomerSearchbox(); }
			if ($func == "StandAloneEntitySearchbox") {			print StandAloneEntitySearchbox(); }
		}
	} else {
		switch($func) {
			case "ShowFlexTableContents":
				print ShowFlexTableContents($_REQUEST['ft'],$_REQUEST['eid'],$_REQUEST['filter'],false,false,$_REQUEST['pdf']);
			break;
			case "RunModule":
				RunModule($_REQUEST['ExecInsModule']);
			break;
			case "DisplayFileList":
				if ($_REQUEST['FlexTable']) $_REQUEST['flextable'] = $_REQUEST['FlexTable'];
				if (strtolower($_REQUEST['show_uploadbox']) == "no") {
						$_REQUEST['show_uploadbox'] = false;
				} else {
						$_REQUEST['show_uploadbox'] = true;
				}
				print DisplayFileList($_REQUEST['eid'], $_REQUEST['flextable'], $_REQUEST['Cust'], $_REQUEST['show_uploadbox']);
			break;
			case "DDFieldVal":
				print GetExtraFieldValue($_REQUEST['eid'], $_REQUEST['Field']);
			break;
			case "BuildCustomEditForm2":
				print BuildCustomEditForm2($_REQUEST['editformID'], $_REQUEST['eid']);
			break;
			case "CreateRestorePoint":
				$_REQUEST['small'] = 1;
				require("snapshot.php");
			break;
			case "AutoSaveSingleField":
				if (!is_numeric($_POST['FlextableId'])) {
					AutoSave();
				} else {
					AutoSaveFlexTableForm();
				}
			break;


		}
	}
} else {
	print "error!";
}


EndHTML(false);

function ShowFileList() {
	require_once("fileupload_frame.php");
	exit;
}

function DisplayFileUploadBox($eid, $divid, $flextableid=false, $cust=false, $folder=false) {
	$func = "refresh_" . $_REQUEST['AjaxHandler'];

	$cl = GetClearanceLevel();
	if (in_array("CommentsAdd", $cl) && CheckEntityAccess($eid) == "readonly") {
		// all ok, this user is a "limited user"
		$t = "ok";
		$EntityAccess = "ok";
		$limited = true;
	}

	if ((CheckEntityAccess($eid)=="ok" && !is_numeric($flextableid) && !$cust) || ($eid == 0 && is_administrator()) || $limited) {

		if (strtoupper($GLOBALS['FileUploadMethod']) == "FLASH") {
			$out .= 'UNSOPPRTED';

		} else {
			$out .= "<iframe class=\"fileuploadiframe\"' frameborder='0' src='fileupload_frame.php?folder=" . htme($folder) . "&amp;eid=" . htme($eid) . "&amp;divid=" . htme($divid) . "'></iframe>";
		}
  
	} elseif (CheckCustomerAccess($eid)=="ok" && !is_numeric($flextableid) && $cust) {

		$out .= "<iframe class=\"fileuploadiframe\"' frameborder='0' src='fileupload_frame.php?folder=" . htme($folder) . "&amp;eid=" . $eid . "&amp;Cust=true&amp;divid=" . $divid . "'></iframe>";


	} elseif ($flextableid) {
		if (CheckFlexTableAccess($flextableid, $GLOBALS['USERID']) == "ok" && CheckFlextableRecordAccess($flextableid, $eid) == "ok") {

            $out .= "<iframe class=\"fileuploadiframe\"' frameborder='0' src='fileupload_frame.php?folder=" . htme($folder) . "&amp;eid=" . $eid . "&amp;divid=" . $divid . "&amp;flextableid=" . $flextableid . "'></iframe>";		
		}
	} else {
		qlog(INFO, "Not displaying file upload box - entity is not writeable");
	}
	return($out);
}

function DisplayFileList($eid, $flextableid=false, $cust=false, $show_uploadbox=true) {
	global $lang;

	$func = "refresh_" . $_REQUEST['AjaxHandler'];

	if (!$flextableid && !$cust) {
		if ($eid == 0 && is_administrator()) {
			$t = "ok";
			$EntityAccess = "ok";
			$GeneralFiles = true;
			$show_uploadbox = true;
		} else {
			$t = CheckEntityAccess($eid);
			$EntityAccess = $t;
			$cl = GetClearanceLevel();
			if (in_array("CommentsAdd", $cl) && $t == "readonly") {
				// all ok, this user is a "limited user"
				$t = "ok";
				$EntityAccess = "ok";
				$limited = true;
			} elseif ($t == "readonly") {
				$roins = "disabled='disabled'";
			} elseif ($t == "nok") {
				PrintAD("You don't have access to this information (e)");
				EndHTML();
				exit;
			}
		}
	} elseif ($cust) {
		$t = CheckCustomerAccess($eid);
		$EntityAccess = $t;
		if ($t == "readonly") {
			$roins = "disabled='disabled'";
		} elseif ($t == "nok") {
			PrintAD("You don't have access to this information (c)");
			EndHTML();
			exit;
		}
	} elseif ($flextableid) {
		$t = CheckFlexTableAccess($flextableid);
		if ($t == "readonly") {
			$roins = "disabled='disabled'";
		} elseif ($t == "nok") {
			PrintAD("You don't have access to this information (ft)");
			EndHTML();
			exit;
		}
		if ($_REQUEST['flextablerecord']) {
			$eid = $_REQUEST['flextablerecord'];
		}
		$t = CheckFlextableRecordAccess($flextableid, $eid);
		if ($t == "readonly") {
			$roins = "disabled='disabled'";
		} elseif ($t == "nok") {
			PrintAD("You don't have access to this information (ft)");
			EndHTML();
			exit;
		}
		$EntityAccess = $t;
		


	} else {
		PrintAD("Unable to determine your access level, defaulting to no access.");
		EndHTML();
		exit;
	}

	if (!is_numeric($_REQUEST['folder'])) {
		$_REQUEST['folder'] = PushStashValue(array());
		$andstring = " AND folder=''";
//		print "CREATE WORKER";
	} else {
		$path = PopStashValue($_REQUEST['folder']);
		$folder = $path[count($path)-1];
		$andstring = " AND folder='" . mres($folder) . "'";
//		print "USE KNOWN WORKER";
	}
	if ($_REQUEST['OpenFolder']) {
		$path[] = $_REQUEST['OpenFolder'];
		$folder = $_REQUEST['OpenFolder'];
		$andstring = " AND folder='" . mres($folder) . "'";
		UpdateStashValue($_REQUEST['folder'], $path);
	} elseif ($_REQUEST['GotoFolder']) {

		if ($_REQUEST['GotoFolder'] == "Root") {
			$path = array();
			UpdateStashValue($_REQUEST['folder'], $path);
		} else {
			$Tpath = $path;
			$path = array();
			foreach ($Tpath AS $tmp) {
				$path[] = $tmp;
				if ($tmp == $_REQUEST['GotoFolder']) {
					break;
				}
			}
			UpdateStashValue($_REQUEST['folder'], $path);
			
		}
		
		$folder = $path[count($path)-1];
		$andstring = " AND folder='" . mres($folder) . "'";
		UpdateStashValue($_REQUEST['folder'], $path);
	}
//	DA($path);

	if ($_REQUEST['delfile_immed'] && $t == "ok" && !$limited) {
		journal($eid, "File " . GetFileName($_REQUEST['delfile_immed']) . " deleted", GetFileTableType($_REQUEST['delfile_immed']));
		DeleteFile($_REQUEST['delfile_immed']);
	}
	if ($show_uploadbox) {
		$filebox .= DisplayFileUploadBox($eid, $_REQUEST['AjaxHandler'], $flextableid, $cust, $folder);
	}

	$filebox .= $tmp;

	if ($GLOBALS['FILELISTSORTORDER'] == "Date") {
		$order = "timestamp_last_change, filename";
	} elseif ($GLOBALS['FILELISTSORTORDER'] == "Date DESC") {
		$order = "timestamp_last_change DESC, filename";
	} elseif ($GLOBALS['FILELISTSORTORDER'] == "Type") {
		$order = "filetype, filename, timestamp_last_change";
	} else {
		$order = "filename, timestamp_last_change";
	}

	if ($_REQUEST['filesort'] == "fileid") {
		if ($_REQUEST['desc']) {
			$order = "fileid DESC, timestamp_last_change";
		} else {
			$order = "fileid, timestamp_last_change";
		}
	} elseif ($_REQUEST['filesort'] == "filename") {
		if ($_REQUEST['desc']) {
			$order = "filename DESC, timestamp_last_change";
		} else {
			$order = "filename, timestamp_last_change";
		}
	} elseif ($_REQUEST['filesort'] == "filetype") {
		if ($_REQUEST['desc']) {
			$order = "filetype DESC, timestamp_last_change";
		} else {
			$order = "filetype, timestamp_last_change";
		}
	} elseif ($_REQUEST['filesort'] == "filesize") {
		if ($_REQUEST['desc']) {
			$order = "filesize DESC, timestamp_last_change";
		} else {
			$order = "filesize, timestamp_last_change";
		}
	} elseif ($_REQUEST['filesort'] == "creationdate") {
		if ($_REQUEST['desc']) {
			$order = "timestamp_last_change DESC, timestamp_last_change";
		} else {
			$order = "timestamp_last_change, timestamp_last_change";
		}
	} elseif ($_REQUEST['filesort'] == "owner") {
		if ($_REQUEST['desc']) {
			$order = "username DESC, timestamp_last_change";
		} else {
			$order = "username, timestamp_last_change";
		}
	}

	if ($_REQUEST['searchfile'] == "{WS}") {
		$_REQUEST['searchfile'] = "";
	}
	
	if (!$GeneralFiles) {
		$excl = " AND " . $GLOBALS['TBL_PREFIX'] . "binfiles.koppelid<>'0' ";
	}

	if ($_REQUEST['searchfile']) {
			$sf = $_REQUEST['searchfile'];

			$searchunknown = "SELECT " . $GLOBALS['TBL_PREFIX'] . "binfiles.fileid FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles WHERE " . $GLOBALS['TBL_PREFIX'] . "binfiles.koppelid='" . mres($eid) . "' " . $excl . " AND " . $GLOBALS['TBL_PREFIX'] . "binfiles.filename NOT LIKE '%" . mres($sf) . "%' AND version_belonging_to=0 AND (" . $GLOBALS['TBL_PREFIX'] . "binfiles.extractedascii='{{locked/error}}' OR " . $GLOBALS['TBL_PREFIX'] . "binfiles.extractedascii='')";

			//print "<h1>" . $searchunknown . "</h1>";
			$dontknow = db_GetFlatArray($searchunknown);

			$ins = "";

			if (count($dontknow) > 0) {
				$ins = " AND " . $GLOBALS['TBL_PREFIX'] . "binfiles.fileid NOT IN (";
				foreach ($dontknow AS $id) {
					if ($nf) $ins .= ",";
					$ins .= $id;
					$nf = true;
				}
				$ins .= ")  ";
			}

			$searchsql = "SELECT " . $GLOBALS['TBL_PREFIX'] . "binfiles.fileid FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles WHERE 1=1 " . $ins . ""; // ends with NO AND
			
			$sflist = explode(" ", $sf);
			foreach ($sflist AS $sf) {
				$searchsql .= " AND (" . $GLOBALS['TBL_PREFIX'] . "binfiles.koppelid='" . mres($eid) . "' " . $excl . "  AND version_belonging_to=0 AND (" . $GLOBALS['TBL_PREFIX'] . "binfiles.extractedascii LIKE '%" . mres($sf) . "%' OR " . $GLOBALS['TBL_PREFIX'] . "binfiles.filename LIKE '%" . mres($sf) . "%'))";
			}

			
			//print "<h1>$searchsql</h1>";
			$matchingfiles = db_GetFlatArray($searchsql);

			//print "<h1>" . $searchsql . "</h1>";

	}
	



	if ($_REQUEST['Cust']) {
		$sql= "SELECT filename,timestamp_last_change,filetype,UNIX_TIMESTAMP(timestamp_last_change) AS cd,filesize,fileid,username FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles WHERE koppelid='" . mres($eid) . "' AND koppelid<>0 AND type='cust' AND version_belonging_to=0 " . $andstring . " ORDER BY " . $order;
	} elseif (!is_numeric($flextableid)) {
		$sql= "SELECT filename,timestamp_last_change,filetype,UNIX_TIMESTAMP(timestamp_last_change) AS cd,filesize,fileid,username FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles WHERE koppelid='" . mres($eid) . "'  " . $excl . "  AND type='entity' AND version_belonging_to=0 " . $andstring . " ORDER BY " . $order;
	} else {
		$sql= "SELECT filename,timestamp_last_change,filetype,UNIX_TIMESTAMP(timestamp_last_change) AS cd,filesize,fileid,username FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles WHERE koppelid='" . mres($eid) . "' AND koppelid<>0 AND type='flextable" . $flextableid . "' AND version_belonging_to=0 " . $andstring . " ORDER BY " . $order;
	
	}


	//print "<h1>" . $sql . "</h1>";

	$result= mcq($sql,$db);

	if ($_REQUEST['filesort'] == "fileid" && $_REQUEST['desc']) {
		$fi_link = "<a onclick=\"" . $func . "('searchfile=" . htme($_REQUEST['searchfile']) . "&folder=" . htme($_REQUEST['folder']) . "&filesort=fileid');\"><img src='images/sorted_up.gif' width='11' height='13'  alt=''></a>&nbsp;";
	} elseif ($_REQUEST['filesort'] == "fileid") {
		$fi_link = "<a onclick=\"" . $func . "('searchfile=" . htme($_REQUEST['searchfile']) . "&folder=" . htme($_REQUEST['folder']) . "&filesort=fileid&amp;desc=1');\"><img src='images/sorted_down.gif' width='11' height='13'  alt=''></a>&nbsp;";
	} else {
		$fi_link = "<a onclick=\"" . $func . "('searchfile=" . htme($_REQUEST['searchfile']) . "&folder=" . htme($_REQUEST['folder']) . "&filesort=fileid&amp;desc=1');\"><img src='images/sort.gif' width='11' height='13'  alt=''></a>&nbsp;";
	}
	if ($_REQUEST['filesort'] == "filename" && $_REQUEST['desc']) {
		$fn_link = "<a onclick=\"" . $func . "('searchfile=" . htme($_REQUEST['searchfile']) . "&folder=" . htme($_REQUEST['folder']) . "&filesort=filename');\"><img src='images/sorted_up.gif' width='11' height='13'  alt=''></a>&nbsp;";
	} elseif ($_REQUEST['filesort'] == "filename") {
		$fn_link = "<a onclick=\"" . $func . "('searchfile=" . htme($_REQUEST['searchfile']) . "&folder=" . htme($_REQUEST['folder']) . "&filesort=filename&amp;desc=1');\"><img src='images/sorted_down.gif' width='11' height='13'  alt=''></a>&nbsp;";
	} else {
		$fn_link = "<a onclick=\"" . $func . "('searchfile=" . htme($_REQUEST['searchfile']) . "&folder=" . htme($_REQUEST['folder']) . "&filesort=filename&amp;desc=1');\"><img src='images/sort.gif' width='11' height='13'  alt=''></a>&nbsp;";
	}
	if ($_REQUEST['filesort'] == "filetype" && $_REQUEST['desc']) {
		//$ft_link = "11<a onclick=\"" . $func . "('searchfile=" . htme($_REQUEST['searchfile']) . "&folder=" . htme($_REQUEST['folder']) . "&filesort=filetype');\"><img src='images/sort.gif' width='11' height='13'  alt=''></a>&nbsp;";
	} else {
		//$ft_link = "11<a onclick=\"" . $func . "('searchfile=" . htme($_REQUEST['searchfile']) . "&folder=" . htme($_REQUEST['folder']) . "&filesort=filetype&desc=1');\"><img src='images/sort.gif' width='11' height='13'  alt=''></a>&nbsp;";
	}
	if ($_REQUEST['filesort'] == "filesize" && $_REQUEST['desc']) {
		$fs_link = "<a onclick=\"" . $func . "('searchfile=" . htme($_REQUEST['searchfile']) . "&folder=" . htme($_REQUEST['folder']) . "&filesort=filesize');\"><img src='images/sorted_up.gif' width='11' height='13'  alt=''></a>&nbsp;";
	} elseif ($_REQUEST['filesort'] == "filesize") {
		$fs_link = "<a onclick=\"" . $func . "('searchfile=" . htme($_REQUEST['searchfile']) . "&folder=" . htme($_REQUEST['folder']) . "&filesort=filesize&amp;desc=1');\"><img src='images/sorted_down.gif' width='11' height='13'  alt=''></a>&nbsp;";
	} else {
		$fs_link = "<a onclick=\"" . $func . "('searchfile=" . htme($_REQUEST['searchfile']) . "&folder=" . htme($_REQUEST['folder']) . "&filesort=filesize&amp;desc=1');\"><img src='images/sort.gif' width='11' height='13'  alt=''></a>&nbsp;";
	}
	if ($_REQUEST['filesort'] == "creationdate" && $_REQUEST['desc']) {
		$fc_link = "<a onclick=\"" . $func . "('searchfile=" . htme($_REQUEST['searchfile']) . "&folder=" . htme($_REQUEST['folder']) . "&filesort=creationdate');\"><img src='images/sorted_up.gif' width='11' height='13'  alt=''></a>&nbsp;";
	} elseif ($_REQUEST['filesort'] == "creationdate") {
		$fc_link = "<a onclick=\"" . $func . "('searchfile=" . htme($_REQUEST['searchfile']) . "&folder=" . htme($_REQUEST['folder']) . "&filesort=creationdate&amp;desc=1');\"><img src='images/sorted_down.gif' width='11' height='13'  alt=''></a>&nbsp;";
	} else {
		$fc_link = "<a onclick=\"" . $func . "('searchfile=" . htme($_REQUEST['searchfile']) . "&folder=" . htme($_REQUEST['folder']) . "&filesort=creationdate&desc=1');\"><img src='images/sort.gif' width='11' height='13'  alt=''></a>&nbsp;";
	}
	if ($_REQUEST['filesort'] == "owner" && $_REQUEST['desc']) {
		$fo_link = "<a onclick=\"" . $func . "('searchfile=" . htme($_REQUEST['searchfile']) . "&folder=" . htme($_REQUEST['folder']) . "&filesort=owner');\"><img src='images/sorted_up.gif' width='11' height='13'  alt=''></a>&nbsp;";
	} elseif ($_REQUEST['filesort'] == "owner") {
		$fo_link = "<a onclick=\"" . $func . "('searchfile=" . htme($_REQUEST['searchfile']) . "&folder=" . htme($_REQUEST['folder']) . "&filesort=owner&amp;desc=1');\"><img src='images/sorted_down.gif' width='11' height='13'  alt=''></a>&nbsp;";
	} else {
		$fo_link = "<a onclick=\"" . $func . "('searchfile=" . htme($_REQUEST['searchfile']) . "&folder=" . htme($_REQUEST['folder']) . "&filesort=owner&desc=1');\"><img src='images/sort.gif' width='11' height='13'  alt=''></a>&nbsp;";
	}

	// Print folder path

	$toprint .= "<div id=\"file-list\">";
	if (count($path) > 0) {
		$toprint .= "<div class=\"folderpath\">";
		$toprint .= "<a onclick=\"" . $func . "('searchfile=" . htme($_REQUEST['searchfile']) . "&GotoFolder=Root&folder=" . $_REQUEST['folder'] . "&filesort=" . $_REQUEST['filesort'] . "');\">*</a>/";
		$cnt =1;
		foreach ($path AS $p) {
			if ($cnt < count($path)) { // Link all but last
				$toprint .= "<a onclick=\"" . $func . "('searchfile=" . htme($_REQUEST['searchfile']) . "&GotoFolder=" . htme($p) . "&folder=" . $_REQUEST['folder'] . "&filesort=" . $_REQUEST['filesort'] . "');\">" . GetFileName($p) . "</a>/";
			} else {
				$toprint .= "" . GetFileName($p) . "/";
			}
		}
		$toprint .= "</div>";
	}
	$toprint .= "<table class='crm'><thead><tr><td class=\"th_fileid\">" . $fi_link . " ID</td><td class=\"td_fileicon\">" . $ft_link . "</td><td class='nwrp name'>" . $fn_link . "" . $lang['filename'] . "</td>";

	$toprint.= "<td class=\"th_filesize\">" . $fs_link . "" . $lang['filesize'] . "</td><td class=\"th_filecreationdate\">" . $fc_link . "" . $lang['creationdate'] . "</td><td class=\"th_fileowner\">" . $fo_link . "" . $lang['owner'] . "</td>";
	if ($GLOBALS['ENABLEFILEVERSIONING'] == "Yes") {
		$toprint .= "<td class=\"th_fileversion\">" . $lang['version'] . "</td>";
	}
	
	if ($EntityAccess == "ok" && !$limited) {
		$toprint .= "<td  class=\"th_filedelete\">" . $lang['deletefile'] . "</td>";
	} else {

	}



	$toprint .= "<td class=\"th_filesearch nowrap\"><strong><img src='images/searchbox.png' alt='' class='search_img'><input type='search' id='s_input' class='search_input' name='searchinput' onkeyup=\"if (GetKeyCode(event) == 13) event.keyCode=null;\" onchange=\"" . $func . "('filesort=" . htme($_REQUEST['filesort']) . "&desc=" . htme($_REQUEST['desc']) . "&searchfile=" . htme($_REQUEST['searchfile']) . "&filesort=" . $_REQUEST['filesort'] . "&searchfile=' + this.value);\" value='" . htme($_REQUEST['searchfile']) . "'><input type='button' name='startsearch' onclick=\"" . $func . "('filesort=" . htme($_REQUEST['filesort']) . "&desc=" . htme($_REQUEST['desc']) . "&searchfile=" . htme($_REQUEST['searchfile']) . "&filesort=" . $_REQUEST['filesort'] . "&searchfile=' + document.getElementById('s_input').value);\" value='" . $lang['go'] . "'></strong></td>";

	$toprint .= "</tr></thead>";
	while ($files= mysql_fetch_array($result)) {
		$acc = CheckFileAccess($files['fileid']);
		if ($acc == "ok" || $acc = "readonly") {
			$ownert = GetUserName($files['username']);
			$toprint.= "\n";

			if (GetFileType($files['fileid']) == "{{{folder}}}") {
				$toprint.= "<tr style='background: #f4f4f4;'><td class=\"td_fileid\"></td>";
			} elseif (!in_array($files['fileid'], $matchingfiles) && is_array($matchingfiles)) {
				$toprint.= "<tr style='background: #D3D3D3;'><td class=\"td_fileid\">" . $files['fileid'] . "</td>";
			} else {
				$toprint.= "<tr><td class=\"td_fileid\">" . $files['fileid'] . "</td>";
			}
			unset($ins_rec1);
			$fdispl = true;

			$toprint .= "<td class=\"td_fileicon\" align='center' " . PrintToolTipCode($files['filename']) . "><a href='csv.php?fileid=" . $files['fileid'] . "' class='imageThumbnailLink' id=\"tn" . $files['fileid'] . "\">" . GetImageFileTypeIcon($files['fileid']) . "</a></td>";


			unset($filename);

			$toprint .= "<td class='nwrp td_filename'>";

			if (($files['filetype'] == "image/jpeg" || $files['filetype'] == "image/gif") && $GLOBALS['ENABLEIMAGETHUMBNAILS'] == "Yes") {

				$toprint .= "<div class=\"imageThumbnail\" id=\"contenttn" . $files['fileid'] . "\"><img src=\"csv.php?tn=" . $files['fileid'] . "\" alt=\"&nbsp;&nbsp;thumbnail not available&nbsp;&nbsp;\"></div>";

				$toprint .= "<a href='csv.php?fileid=" . $files['fileid'] . "' class='imageThumbnailLink' id=\"tn" . $files['fileid'] . "\">" . htme($files['filename']) . "</a> $ins_rec1";
			} elseif ($files['filetype'] == "application/zip") {

				$names = GetFilenamesFromZipArchive($files['fileid']);

				$tbcontent = "<strong>Files in archive:</strong><br>";
				$counter = 1;
				foreach ($names AS $name) {
					$tbcontent .= $counter++ . " " . $name . "<br>";
				}



				$toprint .= "<a href='csv.php?fileid=" . $files['fileid'] . "' " . PrintToolTipCode($tbcontent) . ">" . htme($files['filename']) . "</a> $ins_rec1";

			} elseif (GetFileType($files['fileid']) == "{{{folder}}}") {

				$toprint .= "<a onclick=\"" . $func . "('searchfile=" . htme($_REQUEST['searchfile']) . "&folder=" . htme($_REQUEST['folder']) . "&OpenFolder=" . $files['fileid'] . "&filesort=" . $_REQUEST['filesort'] . "');\">" . htme($files['filename']) . "</a>";
				
				
			} else {
				$toprint .= "<a href='csv.php?fileid=" . $files['fileid'] . "' " . PrintToolTipCode("Click to download " . htme($files['filename'])) . ">" . htme($files['filename']) . "</a> $ins_rec1";

			}
				$tmp_fn = explode(".", $files['filename']);
				$ext = "." . strtolower($tmp_fn[(count($tmp_fn) -1)]);
				if ($ext == ".pdf") {
		//			$toprint .= "<img src='images/fullscreen_maximize.gif' style=\"cursor: pointer;\"onclick=\"PopFancyBoxLarge('','csv.php?ReadPDF=" . $files['fileid'] . "');\">";
				} 

			$toprint .="</td>";


			$toprint .= "<td class='nwrp td_filesize' align='right'>";
			if (GetFileType($files['fileid']) != "{{{folder}}}") {
				$toprint .= ceil(($files[filesize]/1024)). "K";
			}

			$filedate = TransformDate(date('d-m-Y', $files['cd']));

			$filedate .= " " . date('H:i', $files['cd']) . "h";

			$toprint .= "</td><td class='nwrp td_filecreationdate'>" . $filedate . "</td>";
			$toprint .= "<td class='nwrp td_fileowner'>" . $ownert . "</td>";
			if ($GLOBALS['ENABLEFILEVERSIONING'] == "Yes") {
					$toprint .= "<td  class=\"td_fileversion\">";
					if (GetFileType($files['fileid']) != "{{{folder}}}") {

						$filestp = "<table class=\"crm4\">";
						$filestp .= "<tr ><td colspan=\"4\"><img src=\"images/crmlogosmall.gif\" alt=''>&nbsp;<strong>" . $lang['earlierversions'] . "</strong></td></tr>";
						foreach(GetFileEarlierVersions($files['fileid']) AS $earlier_version) {
							$filestp .= "<tr><td class='nwrp'>" . $earlier_version['version_no'] . "</td><td class='nwrp'><a href=\"csv.php?fileid=" . $earlier_version['fileid'] . "\">" . $earlier_version['filename'] . "</a></td><td class='nwrp'>" . GetUserName($earlier_version['username']) . "</td><td class='nwrp'>" . $earlier_version['timestamp_last_change'] . "</td></tr>";
							$ev = true;
						}
						$filestp .= "</table>";

						$toprint .= "<div class=\"imageThumbnail\" id=\"contentver" . $files['fileid'] . "\">" . $filestp . "</div>";

						if ($ev) {
							unset($ev);
							$toprint .= "<img src='images/graph_version.gif' class='imageThumbnailLink' alt='' id=\"ver" . $files['fileid'] . "\">";
						} else {
							$toprint .= "<img src='images/graph_version_grey.gif'  alt=''>";
						}
					}
					$toprint .= "</td>";
			}
			
			if ($EntityAccess == "ok") {
				$acc = CheckFileAccess($files['fileid']);
				if ($acc == "ok" && !$limited) {
					$toprint .= "<td class='nwrp td_filedelete'><input type='checkbox' class='radio' name='deletefile[]' value='" . $files['fileid'] . "' $roins>";
					$toprint .= "<a onclick='refresh_" . $_REQUEST['AjaxHandler'] . "(\"&folder=" . htme($_REQUEST['folder']) . "&delfile_immed=" . $files['fileid'] . "\");' " . PrintToolTipCode("Delete file (no confirmation)") ."><img  src='images/deletes.gif' alt=''></a>";
					$toprint .= "</td>";
				}

			}
			

			if (in_array($files['fileid'], $matchingfiles) && is_array($matchingfiles) && $_REQUEST['searchfile']) {
				$toprint .= "<td class=\"td_filesearch nowrap\" align='center'><img src='images/ok.gif' alt=''></td>";
			} elseif (in_array($files['fileid'], $dontknow) && is_array($dontknow) && $_REQUEST['searchfile']) {
				$toprint .= "<td class=\"td_filesearch nowrap\" align='center'><img src='images/questionmark.png' alt='This file could not be indexed' title='This file could not be indexed and therefore it was not searched.'></td>";
			} elseif ($_REQUEST['searchfile']) {
				$toprint .= "<td class=\"td_filesearch nowrap\" align='center'><img src='images/notfound.gif' alt='The word was not found in this file.' title='The word was not found in this file.'></td>";
			} else {
				$toprint .= "<td class=\"td_filesearch nowrap\">&nbsp;</td>";
			}

			$toprint .= "</tr>";
			$ftel++;
		}
	}


	if ($fdispl || $folder!="") {
		$toprint .= "</table></div>";


		$filelist = $toprint;
		unset($toprint);
	} else {
		// nothin'
	}

	return($filebox . $filelist);
}

?>