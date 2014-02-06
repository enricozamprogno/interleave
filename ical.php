<?php
/* ********************************************************************
* Interleave
* Copyright (c) 2001-2012 info@interleave.nl
* Licensed under the GNU GPL. For full terms see http://www.gnu.org/
*
* This file handles iCal conversion / requests
*
* Check http://www.interleave.nl/ for more information
*
* BUILD / RELEASE :: 5.5.1.20121028
*
**********************************************************************
*/
if (isset($_REQUEST['admin'])) {
	$_GET['SkipMainNavigation'] = true;
	require("initiate.php");
	ShowHeaders();
	MustBeAdmin();
	AddBreadCrum("Calendar definitions");
	AdminTabs("cal");
	
	$calendars = unserialize(GetSetting("CalendarDefinitions"));
	
	if ($_REQUEST['delCalObj']) {
		unset($calendars[$_REQUEST['delCalObj']]);
		UpdateSetting("CalendarDefinitions", serialize($calendars));
	}

	if (!is_array($calendars)) {
		UpdateSetting("CalendarDefinitions", serialize(array()));
	}
	if (isset($_REQUEST['cal_obj'])) {
		$calObj = PopStashValue($_REQUEST['cal_obj']);

		$calObj['commentfield']   = $_REQUEST['cal_commentfield'];
		$calObj['summaryfield']   = $_REQUEST['cal_summaryfield'];
		$calObj['startdatefield'] = $_REQUEST['cal_startdatefield'];
		$calObj['starttimefield'] = $_REQUEST['cal_starttimefield'];
		$calObj['duetimefield']   = $_REQUEST['cal_duetimefield'];
		$calObj['userfield']      = $_REQUEST['cal_userfield'];
		$calObj['locationfield']  = $_REQUEST['cal_locationfield'];
		$calObj['resourcefield']  = $_REQUEST['cal_resourcefield'];
		$calObj['available']	  = $_REQUEST['cal_available'];
		$calObj['useselection']	  = $_REQUEST['cal_useselection'];
		$calObj['description']	  = $_REQUEST['cal_description'];
		$calObj['show_popup']	  = $_REQUEST['cal_showpopup'];
		$calObj['calendar_color'] = $_REQUEST['calendar_color'];
		
		$calObj['show_popup_before'] = $_REQUEST['cal_showpopup_before'];

		// Disabled, see below
		// $calObj['reminder_in_minutes']	  = $_REQUEST['cal_reminder_in_minutes'];
		
		$calendars[$calObj['id']] = $calObj;

		// Clean up

		$cleancal = array();

		foreach ($calendars AS $id => $cal) {
			if ($id != "") {
				$cleancal[$id] = $cal;
			}
		}

		UpdateSetting("CalendarDefinitions", serialize($cleancal));

		unset($_REQUEST['add']);
		unset($_REQUEST['editCalObj']);

		mcq("DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "entityformcache WHERE tabletype LIKE '" . $calObj['id'] . "%'", $db);

		$saved = true;


	} 

	

	print "<table><tr><td>";
	print "<h1>iCal Calendars</h1>";
	print "<h2>With iCal you can publish calendar information to any iCal compatible calendar application</h2>";
	print "<p>iCal calendars can be used from any application or device compatible with the iCal standard. In this section you can create iCal views on your Interleave database.</p>";

	if (isset($_REQUEST['add']) || $_REQUEST['editCalObj']) {
		print "<form name=\"addCal\" method=\"post\" action=\"\">";
		print "<table class=\"crm\">";
		
		
		if (isset($_REQUEST['cal_description']) || $_REQUEST['editCalObj']) {

			// Create initial calendar object
			if ($_REQUEST['editCalObj']) {
				$calObj = $calendars[$_REQUEST['editCalObj']];
			} else {
				$calObj = array();
				$calObj['description'] = $_REQUEST['cal_description'];
				$calObj['duefield'] = $_REQUEST['cal_duedatefield'];
				$calObj['id'] = randomstring(8);
			}
			$calObjNo = PushStashValue($calObj);

			$table = GetExtraFieldTableType(str_replace("EFID", "", $calObj['duefield']));
			
			print "<thead><tr><td>Description</td><td><input type='text' name='cal_description' size='50' value='" . htme($calObj['description']) . "'><input type='hidden' name='cal_obj' value='" . $calObjNo . "'></td></tr></thead>";
			$naam = $calObj['duefield'];
			if (substr($naam, 0, 4) == "EFID") {
				$naam = GetExtraFieldName(str_replace("EFID", "", $naam));
			}
			print "<tr><td>End date</td><td>" . $naam . "</td></tr>";
			print "<tr><td>Summary/description (use tags)</td><td>";
			print "<input type='text' size='70' name='cal_summaryfield' value='" . htme($calObj['summaryfield']) . "'>";
			print "</td></tr>";
			$ins = "";
			print "<tr><td>Start date</td><td><select name=\"cal_startdatefield\">";
			print "<option " . $ins . " value=\"\">{{ignore}}</option>";
			if ($table == "entity") {
				$ins = ($calObj['startdatefield'] == "duedate") ? " selected='selected' " : "";
				print "<option " . $ins . " value=\"duedate\">Due-date (entity)</option>";
				$ins = ($calObj['startdatefield'] == "startdate") ? " selected='selected' " : "";
				print "<option " . $ins . " value=\"startdate\">Start-date (entity)</option>";
			}
			
			foreach (db_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE deleted !='y' AND (fieldtype='date' OR fieldtype='date/time' OR fieldtype='Computation') AND tabletype='" . $table . "'") AS $df) {
				$tt = GetExtraFieldTableType($df['id']);
				if (is_numeric($tt)) {
					$tt = GetFlextableName($tt);
				}
				$tt .= ": ";
				$ins = ($calObj['startdatefield'] == "EFID" . $df['id']) ? " selected='selected' " : "";
				print "<option " . $ins . " value=\"EFID" . $df['id'] . "\">" . $tt . $df['name'] . "</option>";
			}
			print "</select></td></tr>";
			
			print "<tr><td>Start time</td><td><select name=\"cal_starttimefield\">";
			$ins = "";
			print "<option " . $ins . " value=\"\">{{ignore}}</option>";
			if ($table == "entity") {
				$ins = ($calObj['starttimefield'] == "starttime") ? " selected='selected' " : "";
				print "<option " . $ins . " value=\"starttime\">Starttime (entity)</option>";
				$ins = ($calObj['starttimefield'] == "duetime") ? " selected='selected' " : "";
				print "<option " . $ins . " value=\"duetime\">Due-time (entity)</option>";
			}
			foreach (db_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE deleted !='y' AND (fieldtype='numeric' OR fieldtype='date/time' OR fieldtype='Computation') AND tabletype='" . $table . "'") AS $df) {
				$tt = GetExtraFieldTableType($df['id']);
				if (is_numeric($tt)) {
					$tt = GetFlextableName($tt);
				}
				$tt .= ": ";
				$ins = ($calObj['starttimefield'] == "EFID" . $df['id']) ? " selected='selected' " : "";
				print "<option " . $ins . " value=\"EFID" . $df['id'] . "\">" . $tt . $df['name'] . "</option>";
			}
			print "</select></td></tr>";
			$ins = "";
			print "<tr><td>End time</td><td><select name=\"cal_duetimefield\">";
			print "<option " . $ins . " value=\"\">{{ignore}}</option>";
			if ($table == "entity") {
				$ins = ($calObj['duetimefield'] == "duetime") ? " selected='selected' " : "";
				print "<option " . $ins . " value=\"duetime\">Due-time (entity)</option>";
				$ins = ($calObj['duetimefield'] == "startime") ? " selected='selected' " : "";
				print "<option " . $ins . " value=\"starttime\">Starttime (entity)</option>";
			}

			foreach (db_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE deleted !='y' AND (fieldtype='numeric' OR fieldtype='date/time' OR fieldtype='Computation') AND tabletype='" . $table . "'") AS $df) {
				$tt = GetExtraFieldTableType($df['id']);
				if (is_numeric($tt)) {
					$tt = GetFlextableName($tt);
				}
				$tt .= ": ";
				$ins = ($calObj['duetimefield'] == "EFID" . $df['id']) ? " selected='selected' " : "";
				print "<option " . $ins . " value=\"EFID" . $df['id'] . "\">" . $tt . $df['name'] . "</option>";
			}
			print "</select></td></tr>";
			$ins = "";
			print "<tr><td>Organiser</td><td><select name=\"cal_userfield\">";
			print "<option " . $ins . " value=\"\">{{ignore}}</option>";
			if ($table == "entity") {
				$ins = ($calObj['userfield'] == "assignee") ? " selected='selected' " : "";
				print "<option " . $ins . " value=\"assignee\">Assignee</option>";
				$ins = ($calObj['userfield'] == "owner") ? " selected='selected' " : "";
				print "<option " . $ins . " value=\"owner\">Owner</option>";
			} elseif ($table == "customer") {
				$ins = ($calObj['userfield'] == "customer_owner") ? " selected='selected' " : "";
				print "<option " . $ins . " value=\"customer_owner\">Customer owner</option>";
			}


			foreach (db_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE deleted !='y' AND fieldtype LIKE 'User-%' AND tabletype='" . $table . "'") AS $df) {
				$tt = GetExtraFieldTableType($df['id']);
				if (is_numeric($tt)) {
					$tt = GetFlextableName($tt);
				}
				$tt .= ": ";
				$ins = ($calObj['userfield'] == "EFID" . $df['id']) ? " selected='selected' " : "";
				print "<option " . $ins . " value=\"EFID" . $df['id'] . "\">" . $tt . $df['name'] . "</option>";
			}
			print "</select></td></tr>";
			$ins = "";
			print "<tr><td>Resource (use tags)</td><td>";
			print "<input type='text' size='70' name='cal_resourcefield' value='" . htme($calObj['resourcefield']) . "'>";
			print "</td></tr>";

			print "<tr><td>Comment (use tags)</td><td>";
			print "<input type='text' size='70' name='cal_commentfield' value='" . htme($calObj['commentfield']) . "'>";
			print "</td></tr>";

			print "<tr><td>Location (use tags)</td><td>";
			print "<input type='text' size='70' name='cal_locationfield' value='" . htme($calObj['locationfield']) . "'>";
			print "</td></tr>";

			print "<tr><td>Limit results based on selection</td><td>";
			
			if ($table == "entity") {
				$tmp = GetAttribute("system", "SavedEntityListSelections", 1);
			} elseif ($table == "customer") {
				$tmp = GetAttribute("system", "SavedCustomerListSelections", 1);
			} else {
				$tmp = GetAttribute("system", "SavedSelectionsFlextable" . $table, 1);
			}
			
			print "<select name=\"cal_useselection\">";

			print "<option value=\"\">{{none}}</option>";

			foreach ($tmp AS $selection => $ar) {
				$ins = ($calObj['useselection'] == $selection) ? " selected='selected' " : "";
				print "<option " . $ins . " value=\"" . htme($selection) . "\">" . htme($selection) . "</option>";
			}

			print "</select>";
			
			print "</td></tr>";
			
			/*
			// Disabled, see below

			print "<tr><td>Reminder</td><td>";
			
			print "<select name=\"cal_reminder_in_minutes\">";

			print "<option value=\"\">{{none}}</option>";
			
			$tmp = array("15 Minutes before event" => "15", "30 Minutes before event" => "30", "45 Minutes before event" => "45", "1 hour before event" => "60", "2 Hours before event" => "120", "6 Hours before event" => "360", "12 Hours before event" => "720", "24 Hours before event" => "1440");

			foreach ($tmp AS $rem => $ar) {
				$ins = ($calObj['cal_reminder_in_minutes'] == $rem) ? " selected='selected' " : "";
				print "<option " . $ins . " value=\"" . htme($ar) . "\">" . htme($rem) . "</option>";
			}

			print "</select>";
			
			print "</td></tr>";
			*/

			$ins = "";
			print "<tr><td>Available for users/groups</td><td>";
			print "<select name=\"cal_available\">";

			print "<option value=\"{{all}}\">{{all}}</option>";

			foreach (GetProfiles() AS $profile) {
				$ins = ($calObj['available'] == "G:" . $profile['id']) ? " selected='selected' " : "";
				print "<option " . $ins . " value=\"G:" . $profile['id'] . "\">All users in group \"" . htme(GetGroupName($profile['id'])) . "\"</option>";
			}

			foreach (GetUserList() AS $user) {
				$ins = ($calObj['available'] == "U:" . $user['id']) ? " selected='selected' " : "";
				print "<option " . $ins . " value=\"U:" . $user['id'] . "\">" . htme(GetUserName($user['id'])) . "</option>";
			}
			print "</select>";
			print "</td></tr>";
			print "<tr><td>Display notification popup for this calendar (only when using a duetime field)</td><td>";
			if ($calObj['show_popup'] == "yes") {
				$ins = "checked=\"checked\"";
			} else {
				$ins = "";
			}
			print "<input " . $ins . " type=\"checkbox\" name=\"cal_showpopup\" value=\"yes\">";
			if ($calObj['show_popup_before'] == "") $calObj['show_popup_before'] = "30,5,0";
			print "<input " . $ins . " type=\"text\" size=\"5\" name=\"cal_showpopup_before\" value=\"" . htme($calObj['show_popup_before']) . "\"> minutes before (comma-separated list)";
			print "</td></tr>";

			print "<tr><td>Color</td><td><input name=\"calendar_color\" id=\"JS_calendar_color\" value=\"" . htme($calObj['calendar_color']) . "\" class=\"ColorPickerField\"></td></tr>";

		} else {
			print "<thead><tr><td colspan=\"2\">Create a new calendar</td></tr></thead>";
			print "<tr><td>Description</td><td><input type=\"text\" name=\"cal_description\" value=\"" . htme($cal['description']) . "\" size=\"50\"></td></tr>";
			print "<tr><td>End date</td><td><select name=\"cal_duedatefield\">";
			print "<option " . $ins . " value=\"duedate\">entity: due-date</option>";
			foreach (db_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE deleted !='y' AND (fieldtype='date' OR fieldtype='date/time' OR fieldtype='Computation') ORDER BY FIELD(tabletype, 'entity', 'customer')") AS $df) {
				$tt = GetExtraFieldTableType($df['id']);
				if (is_numeric($tt)) {
					$tt = GetFlextableName($tt);
				}
				$tt .= ": ";
				print "<option " . $ins . " value=\"EFID" . $df['id'] . "\">" . $tt . $df['name'] . "</option>";
			}
			print "</select></td></tr>";
		}

		print "</table>";
		if (isset($_REQUEST['cal_description']) || $_REQUEST['editCalObj']) {
			print "<br><input type=\"submit\" value=\"Save\">";
		} else {
			print "<br><input type=\"submit\" value=\"next step\">";
		}
		print "</form>";
	} else {

		print "<a href=\"ical.php?admin&amp;add\">Create a new calendar</a>";
		print "<table class=\"crm\">";
		print "<thead><tr><td>id</td><td>URL</td><td>Description</td><td>Visible for</td><td>Delete</td></tr></thead>";
		$set = "";
		foreach ($calendars AS $id => $cal) {

			if ($id != "") {
			
				if ($cal['available'] == "{{all}}" || $cal['available'] == "") {
					$who = "Everyone";
				} elseif (substr($cal['available'],0,1) == "U") {
					$who = "Only for " . GetUserName(str_replace("U:", "", $cal['available']));
				} elseif (substr($cal['available'],0,1) == "G") {
					$who = "Only for users in group " . GetGroupName(str_replace("G:", "", $cal['available']));
				} else {
					$who = "Everyone";
				}


			
				$name = preg_replace("/\W/", "", str_replace(" ", "_", $cal['description']));
				print "<tr><td>" . $id . "</td><td>" . $GLOBALS['BASEURL'] . "ical.php?repository=" . $GLOBALS['repository_nr'] . "&amp;calObj=" . $cal['id'] . "&amp;Cal=" . $name . "</td><td><a href='ical.php?admin&amp;editCalObj=" . htme($cal['id']) . "'>" . $cal['description'] . "</a></td><td>" . $who . "</td><td><a href='ical.php?admin&amp;delCalObj=" . $cal['id'] . "'><img src='images/deletes.gif'></a></td></tr>";
				$set = true;
			}
		}
		if (!$set) {
			print "<tr><td colspan=\"2\">No calendars defined</td></tr>";
		}
		print "</table>";

		
	}
	

	if ((isset($_REQUEST['cal_description']) || $_REQUEST['editCalObj']) && !$saved) {
		
		$type = GetExtraFieldTableType($calObj['duefield']);

		if ($type == "entity") {
			$list = GetExtraFields();
		} elseif ($type == "customer") {
			$list = GetExtraCustomerFields();
		} else {
			$list = GetExtraFlextableFields($type);
		}
		print "<br><br><h2>Available fields</h2><table class='crm'><thead><tr><td>Field id</td><td>Name</td><td>Type</td><td>Tag</td><td>Alias</td></tr></thead>";
		foreach ($list AS $f) {
			print "<tr><td>" . $f['id'] . "</td><td>" . $f['name'] . "</td><td>" . strtolower($f['fieldtype']) . "</td><td>@EFID" . $f['id'] . "@" . "</td><td>@" . htme(strtoupper(str_replace(" ", "_", $f['name']))) . "@</td></tr>";
		}
		print "</table>";
		
	}
	print "</td></tr></table>";
	EndHTML();

} else {
	require_once("config/config-vars.php");
	
	require_once("functions.php");
	require($GLOBALS['CONFIGFILE']);
	if (!isset($_REQUEST['repository']) || $host[$_REQUEST['repository']] == "" || !isset($_REQUEST['calObj'])) {

		print "Error";
		exit;

	} else {

		db_Connect($_REQUEST['repository'], false);

		if (!isset($_SERVER['PHP_AUTH_USER'])) {
		   ShowAuthHeadersRSS();
		   exit;
		  } else {
			$GLOBALS['RSS'] = true;

			if (AuthenticateUser($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'], true)) {
				$GLOBALS['USERNAME'] = $_SERVER['PHP_AUTH_USER'];
				$GLOBALS['USERID'] = GetUserID($_SERVER['PHP_AUTH_USER']);
				SwitchToRepos($_REQUEST['repository']);
				$authenticated = true;
			} else {
	
				ShowAuthHeadersRSS();
				exit;
			}
		};

		require_once( 'lib/icalcreator/iCalcreator.class.php' );
		
		$config = array( 'unique_id' => $GLOBALS['title'] );
		$v = new vcalendar( $config );

		$v->setProperty( 'method', 'PUBLISH' );
		$v->setProperty( "X-WR-CALDESC", "Interleave iCal Calendar " . $GLOBALS['title']);
		$v->setProperty( "x-wr-calname", "Interleave::" . $calObj['description'] );
		$v->setProperty( "X-WR-TIMEZONE", "Europe/Stockholm" );

		// in loop
		
		foreach (GetCalendarEvents($_REQUEST['calObj'], false) AS $event) {
				
				$startime_hour = substr($event['starttime'], 0, 2);
				$startime_minute = substr($event['starttime'], 2, 2);
				$duetime_hour = substr($event['duetime'], 0, 2);
				$duetime_minute = substr($event['duetime'], 2, 2);

				$date = explode("-", NLDate2INTLDate($event['duedate']));
				$startdate = explode("-", NLDate2INTLDate($event['startdate']));

				$vevent = & $v->newComponent( 'vevent' );

				$vevent->setProperty( 'dtstart', array( 'year'=>$startdate[0], 'month'=>$startdate[1], 'day'=>$startdate[2], 'hour'=>$startime_hour, 'min'=>$startime_minute,  'sec'=>0 ));
				$vevent->setProperty( 'dtend', array( 'year'=>$date[0], 'month'=>$date[1], 'day'=>$date[2], 'hour'=>$duetime_hour, 'min'=>$duetime_minute,  'sec'=>0 ));

				$vevent->setProperty( 'organizer' , GetUserEmail($event['user']) );
				$vevent->setProperty( 'summary', $event['summary'] );
				$vevent->setProperty( 'description', $event['comment'] );
				$vevent->setProperty( 'resources', $event['resource'] );

				$vevent->parse( 'LOCATION:'. $event['location'] );

		}

		// end loop
		EndHTML(false);
		$v->returnCalendar();
		//CalObjOutput = $v->createCalendar();
		//print $CalObjOutput;
	}

	
}




				/*

				// This function is currently disabled because alarms cannot be set automatically in the same time zone as the event. Fixing this would mean making Interleave
				// timezone-aware (which it currently isn't) and do a lot of nasty calculation to figure out what the correct alarm time is.

				if ($reminder != "") {
					$to_date = strtotime(NLDate2INTLDate($this_startdate) . " " . $this_starttime);
					if ($to_date == "") {
						$to_date = strtotime(NLDate2INTLDate($this_duedate) . " " . $this_duetime);
					}
					$alarm_moment = $to_date - (60 * $reminder);
					$alarm_date = date('Ymd', $alarm_moment);
					$alarm_time = date('Hi', $alarm_moment);

					$valarm = new valarm();
					$valarm->parse( 'TRIGGER;VALUE=DATE-TIME:' . $alarm_date . 'T' . $alarm_time . '00');

					$valarm->setProperty( "trigger" , array( "year" => date('Y', $alarm_moment) , "month" => date('m', $alarm_moment) , "day" => date('d', $alarm_moment) , "hour" => date('H', $alarm_moment) , "min" => date('i', $alarm_moment) , "sec" => 0 ));

					$valarm->SetProperty( "tzid", "Europe/Stockholm" );
					$valarm->parse( 'ACTION:DISPLAY' );
					$vevent->setComponent( $valarm );
				}
				*/
?>