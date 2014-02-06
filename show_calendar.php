<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 info@interleave.nl
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This script can show defined calendars on screen
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */

function ShowCalendar($calObjId="{{all}}", $view="month") {
	global $lang;


	if ($calObjId == "selectOnly") {
		$view = strtolower(GetAttribute("extrafield", "UsePlanningCalendarView", $_REQUEST['selectField']));
	}
	
	if ($view == "") $view = "month";

	$func1 = "refresh_" . $_REQUEST['AjaxHandler'] . "('";
	$func2 = "');";
	
	$calendars = GetAccessibleCalendars();

	$calObj = $calendars[$calObjId];

	if ($calObjId == "selectOnly") $calObj = "selectOnly";
	
	$total_calendar = array();

	if ($calObj == "{{all}}" || $calObj == "") {
		$total_calendar = array();
		$cc = 1;
		$colors = array();

		$scheme = GetScheme();

		foreach ($calendars AS $id => $values) {
			if ($id != "") {
				if ($view == "month") {
					if ($_REQUEST['year']) {
						$curyear = $_REQUEST['year'];
					} else {
						$curyear = date('Y');
					}

					if (isset($_REQUEST['month'])) {
						$curmonth = $_REQUEST['month'];

						if ($curmonth == 0) {
							$curmonth = 12;
							$curyear--;
						}
						if ($curmonth == 13) {
							$curmonth = 1;
							$curyear++;
						}
						if (strlen($curmonth) == 1) {
							$curmonth = "0" . $curmonth;
						}

					} else {
						$curmonth = date('m');
					}
					$monthyear = $curyear . "-" . $curmonth;
					
				} else {
					$monthyear = false;

				}
				

				$local_calendar = GetCalendarEvents($id, true, true, $monthyear);
				
				foreach ($local_calendar AS $loc_entry) {
					$total_calendar[] = $loc_entry;
				}
				if ($values['calendar_color'] != "") {
					$colors[$id] = $values['calendar_color'];
				} else {
					$colors[$id] = $scheme[$cc];
				}
				$cc++;
						
			}
		}

	} else {
		if ($calObjId == "selectOnly" && $_REQUEST['selectField']) {

			$table = GetExtraFieldTableType($_REQUEST['selectField']);

			$SelectAction = true;
			$sql_add = "";

			$UsePlanningCalendarMatchOnFields = GetAttribute("extrafield", "UsePlanningCalendarMatchOnFields", $_REQUEST['selectField']);
			$UsePlanningCalendarDescription = GetAttribute("extrafield", "UsePlanningCalendarDescription", $_REQUEST['selectField']);
			$UsePlanningCalendarLayoutTemplate = GetAttribute("extrafield", "UsePlanningCalendarLayoutTemplate", $_REQUEST['editextrafield']);

			if ($UsePlanningCalendarDescription  == "") $UsePlanningCalendarDescription  = "@RECORDID@ -- No UsePlanningCalendarDescription attribute set!";
			
			if ($UsePlanningCalendarMatchOnFields != "" && $UsePlanningCalendarMatchOnFields != "{{none}}") {
				$tmp = explode(",", $UsePlanningCalendarMatchOnFields);

				foreach ($tmp AS $cond) {

					if (is_numeric($cond) && is_numeric($_REQUEST['recordnum'])) {

						switch ($table) {
							case "entity":
								$sql = "SELECT EFID" . $cond . " AS value FROM " . $GLOBALS['TBL_PREFIX'] . $table . " WHERE eid='" . htme($_REQUEST['recordnum']) . "'";
							break;
							case "customer":
								$sql = "SELECT EFID" . $cond . " AS value FROM " . $GLOBALS['TBL_PREFIX'] . $table . " WHERE id='" . htme($_REQUEST['recordnum']) . "'";
							break;
							default:
								$sql = "SELECT EFID" . $cond . " AS value FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $table . " WHERE recordid='" . htme($_REQUEST['recordnum']) . "'";
							break;
						}
							
						$val_cur = db_GetValue($sql);

						if ($val_cur != "") {
							$sql_add .= " AND EFID" . $cond . "='" . mres($val_cur) . "'";
						}
					}
				}
			}


			if ($_REQUEST['year']) {
				$curyear = $_REQUEST['year'];
			} else {
				$curyear = date('Y');
			}

			if (isset($_REQUEST['month'])) {
				$curmonth = $_REQUEST['month'];

				if ($curmonth == 0) {
					$curmonth = 12;
					$curyear--;
				}
				if ($curmonth == 13) {
					$curmonth = 1;
					$curyear++;
				}

			} else {
				$curmonth = date('n');
			}
			$sql_add .= " AND MONTH(STR_TO_DATE(EFID" . mres($_REQUEST['selectField']) . ", '%d-%m-%Y')) = '" . $curmonth . "'";
			$sql_add .= " AND YEAR(STR_TO_DATE(EFID" . mres($_REQUEST['selectField']) . ", '%d-%m-%Y')) = '" . $curyear . "'";


			switch ($table) {
				case "entity":
					$sql = "SELECT EFID" . mres($_REQUEST['selectField']) . " AS duedate,EFID" . mres($_REQUEST['selectField']) . " AS startdate, eid AS BASE_RECORD FROM " . $GLOBALS['TBL_PREFIX'] . $table . " WHERE deleted!='y' AND EFID" . mres($_REQUEST['selectField']) . " != ''" . $sql_add;
				break;
				case "customer":
					$sql = "SELECT EFID" . mres($_REQUEST['selectField']) . " AS duedate,EFID" . mres($_REQUEST['selectField']) . " AS startdate, id AS BASE_RECORD FROM " . $GLOBALS['TBL_PREFIX'] . $table . " WHERE active='yes' AND EFID" . mres($_REQUEST['selectField']) . " != ''" . $sql_add;
				break;
				default:
					$sql = "SELECT EFID" . mres($_REQUEST['selectField']) . " AS duedate,EFID" . mres($_REQUEST['selectField']) . " AS startdate, recordid AS BASE_RECORD FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $table . " WHERE deleted!='y' AND EFID" . mres($_REQUEST['selectField']) . " != ''" . $sql_add;
				break;
			}
				
		//		print $sql;

			$total_calendar = db_GetArray($sql);


			for ($i=0;$i<count($total_calendar);$i++) {
				$total_calendar[$i]['calendarname'] = "selectOnly";
				switch ($table) {

					case "entity":
						$total_calendar[$i]['summary'] = ParseTemplateEntity($UsePlanningCalendarDescription, $total_calendar[$i]['BASE_RECORD'], false, false, false, "htme");
					break;
					case "customer":
						$total_calendar[$i]['summary'] = ParseTemplateCustomer($UsePlanningCalendarDescription, $total_calendar[$i]['BASE_RECORD'], false, false, false, "htme");
					break;
					default:
						$total_calendar[$i]['summary'] = ParseFlexTableTemplate($table, $total_calendar[$i]['BASE_RECORD'], $UsePlanningCalendarDescription, false, false, false, "htme");
					break;
				}


			}

		} else {
			$total_calendar = GetCalendarEvents($_REQUEST['calObj'], true);
		}
		

	}
//	DA($total_calendar);
//	exit;
	$per_day = array();
	
	$no = 1;

	foreach ($total_calendar AS $key => $event) {
		$no++;
		if (!is_array($per_day[date('U', strtotime($event['duedate']))])) {
			$per_day[date('U', strtotime($event['duedate']))] = array();
		}
		if (!is_array($per_day[date('U', strtotime($event['startdate']))])) {
			$per_day[date('U', strtotime($event['startdate']))] = array();
		}
		$per_day[date('U', strtotime($event['startdate']))][$no] = $event;
		if ($event['startdate'] != $event['duedate']) {
			$per_day[date('U', strtotime($event['startdate']))][$no]['duetime'] = "";
		}
		$per_day[date('U', strtotime($event['duedate']))][$no] = $event;
		if ($event['startdate'] != $event['duedate']) {
			$per_day[date('U', strtotime($event['duedate']))][$no]['starttime'] = "";
		}
		
		for ($i=date('U', strtotime($event['startdate']) + 86400);$i<date('U', strtotime($event['duedate']));$i+=86400) {

			$per_day[$i][$no] = $event;
			$per_day[$i][$no]['starttime'] = "";
			$per_day[$i][$no]['duetime'] = "";
			$per_day[$i][$no]['allday'] = true;
		}

		$per_day[date('U', strtotime($event['startdate']))][$no]['allday'] = false;
	}

	ksort($per_day);
	if ($calObjId != "selectOnly") {
		switch ($view) {
			case "month":
				print "<input type='button' value='event list view' onclick=\"" . $func1 . "&view=events&month=" . ($curmonth) . "&year=" . $curyear . $func2 ."\">&nbsp;";
				print "<input type='button' value='week view' onclick=\"" . $func1 . "&view=week" . $func2 ."\">";
			break;
			case "events":
				print "<input type='button' value='month view' onclick=\"" . $func1 . "&view=month" . $func2 ."\">&nbsp;";
				print "<input type='button' value='week view' onclick=\"" . $func1 . "&view=week" . $func2 ."\">";
			break;
			case "week":
				print "<input type='button' value='month view' onclick=\"" . $func1 . "&view=month" . $func2 ."\">&nbsp;";
				print "<input type='button' value='event list view' onclick=\"" . $func1 . "&view=events&month=" . ($curmonth) . "&year=" . $curyear . $func2 ."\">&nbsp;";
			break;

		}
	
		if (is_array($colors)) {
			foreach ($calendars AS $id => $cal) {
				print "<div class=\"showinline event_main\" style=\"background-color: " . $colors[$id] . ";\"><span class=\"" . ReturnClassnameForTextColorBasedOnBackgroundColor($colors[$id]) . "\">" . $cal['description'] . "</span></div>&nbsp;";
			}
			print "<br><br>";
		}
	}

	switch($view) {
		default: 
			PrintAD("Calendar cannot be shown; unkown calendar view ($view).");
			break;
		case "week":
			if ($_REQUEST['year']) {
				$curyear = $_REQUEST['year'];
			} else {
				$curyear = date('Y');
			}

			if (isset($_REQUEST['week'])) {
				$curweek = $_REQUEST['week'];

				if ($curweek == 0) {
					$curweek = 12;
					$curyear--;
				}
				if ($curweek == 13) {
					$curweek = 1;
					$curyear++;
				}


			} else {
				$curweek = date('W');
			}



			
			print "<div class=\"calendar_div\">";
			print "<table border='1' class=\"calendar\">";
			
			$buttons = "<input type='button' value='&lt;&lt;' onclick=\"" . $func1 . "&week=" . ($curweek-1) . "&year=" . $curyear . "&view=week" . $func2 ."\"> <input type='button' value='&gt;&gt;' onclick=\"" . $func1 . "&week=" . ($curweek+1) . "&year=" . $curyear . "&view=week" . $func2 ."\">";

			print "<thead><tr><td colspan=\"2\">" . $buttons . "</td><td></td><td colspan=\"3\" class=\"calendar_main_month\"> " . $lang['month' . ($curmonth*1)] . " " . $curyear . "</td><td></td><td class=\"rightalign\" colspan=\"2\">" . $buttons . "</td>";
			
			print "</tr></thead>";
			print "<thead><tr>";
			print "<td class=\"cal_week\">" . $lang['week'] . "&nbsp;&nbsp;&nbsp;</td>";
			print "<td><strong>" . $lang['monday'] . "</strong></td>";
			print "<td><strong>" . $lang['tuesday'] . "</strong></td>";
			print "<td><strong>" . $lang['wednesday'] . "</strong></td>";
			print "<td><strong>" . $lang['thursday'] . "</strong></td>";
			print "<td><strong>" . $lang['friday'] . "</strong></td>";
			print "<td><strong>" . $lang['saturday'] . "</strong></td>";
			print "<td><strong>" . $lang['sunday'] . "</strong></td>";
			print "</tr></thead>";
			
			$calAr = array();
			$day1 = "1";
			
			$lines = 0;
			for ($dc=0;$dc<=7;$dc++) {
				
				$weekpos = $dc;
				$calAr[$lines][$weekpos] = date('Y-m-d', strtotime($curyear . "W" . $curweek . " +" . $dc . " days"));
			}
			
			foreach ($calAr AS $line) {

				print "<tr class=\"weekrow\"><td class=\"cal_week\">" . date('W', strtotime($curyear . "W" . $curweek)) . "</td>";

				for ($t=0;$t<7;$t++) {
					$day = $line[$t];

					if ($day == "") {
						print "<td></td>";
					} else {
						$epoch = strtotime($day);
						if ($SelectAction) {
							$td_onclick = " onclick=\"PutSelectedDateInParentForm('JS_EFID" . htme($_REQUEST['selectField']) . "', '" . TransformDate(date('d-m-Y', $epoch)) . "'," . htme($_REQUEST['selectField']) . ");\" style=\"cursor: pointer;\"";
						} else {
							$td_onclick = "";
						}
						print "<td class=\"calendar-cell\">";
							print "<div class=\"event_div_week\">";
								
									print "<div " . $td_onclick ." class=\"cal_day\"><table style=\"width: 100%;border: 0;\"><tr><td style=\"border: 0;\" " . $td_onclick . ">" . $day . "</td><td style=\"border: 0;\" " . $td_onclick . " class=\"rightalign\">" . count($per_day[$epoch]) . "</td></tr></table></div>";
							
			
									if (1==1) {
										
										$to_print_1st = "";
										$to_print = "";
										$firstdone = false;

										for ($hour=$GLOBALS['CAL_MINHOUR'];$hour<=$GLOBALS['CAL_MAXHOUR'];$hour++) {
											foreach ($per_day[$epoch] AS $event) {

												if ($event['starttime'] == "" && $event['duetime'] == "" && !$event['allday']) {
													$event['allday'] = true;
													$event['alldayclass'] = "event_main_allday_clear";
												} elseif ($event['allday'] == true) {
													$event['alldayclass'] = "event_main_allday";
												}

												
												$html = "";
												$c++;
												if (is_array($colors)) {
													$divcolor = "style=\"background-color: " . $colors[$event['id']] . "\";";
													$loccol = $colors[$event['id']];
												} else {
													$divcolor = "";
													$loccol = "";
												}
												$html = "<table>";
													$html .= "<tr><td>" . $event['startdate'] . " " . $event['starttime'] . " - " . $event['duedate'] . " " . $event['duetime'] . "</td></tr>";
													if ($event['comment'] != "") {
														$html .= "<tr><td>" . $event['comment'] . "</td></tr>";
													}
													if ($event['user'] != "") {
														$html .= "<tr><td>" . $event['user'] . "</td></tr>";
													}
													if ($event['resource'] != "") {
														$html .= "<tr><td>" . $event['resource'] . "</td></tr>";
													}
													if ($event['location'] != "") {
														$html .= "<tr><td>" . $event['location'] . "</td></tr>";
													}
												$html .= "</table>";

												$timing = "";
												if ($event['starttime']) {
													$timing = $event['starttime'];
													$anchortime = $event['starttime'];
												} 
												if ($event['duetime']) {
													if ($timing) $timing .= "-";
													$timing .= $event['duetime'];
													if ($event['starttime']) {
														$anchortime = $event['duetime'];
													}
												}

												if ($event['allday'] != true) {
												
													
													if ($hour == (substr($anchortime,0,2)*1)) {
														$to_print .= "<div class=\"cal_hourblock\">" . $hour . ":00</div>";
														$to_print .= "<div class=\"event_main\" " . PrintToolTipCode($html) . " " . $divcolor . " ><span class=\"" . ReturnClassnameForTextColorBasedOnBackgroundColor($loccol) . "\">" . $ins . $timing . "<strong>" . $event['summary'] . "</strong></span></div>";
													}
												

													$line_done = true;


												} elseif (!$firstdone) {
													$to_print_1st .= "<div class=\"" . $event['alldayclass'] . "\" " . $divcolor . " ><span class=\"" . ReturnClassnameForTextColorBasedOnBackgroundColor($loccol) . "\">" . $ins . $timing . "<strong>" . $event['summary'] . "</strong></span></div";
												}
											}
											$firstdone = true;
											
											if (!$line_done) {
												//$to_print .= "<div class=\"cal_hourblock\">" . $hour . ":00</div>";
												//$to_print .= "<div class=\"event_main_empty\">deze hiezo</div>";
											} else {
												$line_done = false;
											}
										}
									if ($to_print_1st) {
										print $to_print_1st;
									} else {
										$to_print_1st .= "<tr><td><div class=\"event_main_allday_clear\">hierr &nbsp;</div></td></tr>";
									}
									print $to_print;
									print "</td>";
									}

							
						}
				}
				print "</td>";
			
			}


			print "</table>";

			print "</div>";

			break;

		case "month":
			if ($_REQUEST['year']) {
				$curyear = $_REQUEST['year'];
			} else {
				$curyear = date('Y');
			}

			if (isset($_REQUEST['month'])) {
				$curmonth = $_REQUEST['month'];

				if ($curmonth == 0) {
					$curmonth = 12;
					$curyear--;
				}
				if ($curmonth == 13) {
					$curmonth = 1;
					$curyear++;
				}
				if (strlen($curmonth) == 1) {
					$curmonth = "0" . $curmonth;
				}

			} else {
				$curmonth = date('m');
			}



			
			print "<div class=\"calendar_div\">";
			print "<table class=\"calendar\">";
			$buttons = "<input type='button' value='&lt;&lt;' onclick=\"" . $func1 . "&month=" . ($curmonth-1) . "&year=" . $curyear . $func2 ."\"> <input type='button' value='&gt;&gt;' onclick=\"" . $func1 . "&month=" . ($curmonth+1) . "&year=" . $curyear . $func2 ."\">";

			print "<thead><tr><td colspan=\"2\">" . $buttons . "</td><td></td><td colspan=\"3\" class=\"calendar_main_month\"> " . $lang['month' . ($curmonth*1)] . " " . $curyear . "</td><td></td><td class=\"rightalign\" colspan=\"2\">" . $buttons . "</td>";
			
			print "</tr></thead>";
			print "<thead><tr>";
			print "<td></td>";
			print "<td class=\"dayname\">" . $lang['monday'] . "</td>";
			print "<td class=\"dayname\">" . $lang['tuesday'] . "</td>";
			print "<td class=\"dayname\">" . $lang['wednesday'] . "</td>";
			print "<td class=\"dayname\">" . $lang['thursday'] . "</td>";
			print "<td class=\"dayname\">" . $lang['friday'] . "</td>";
			print "<td class=\"dayname\">" . $lang['saturday'] . "</td>";
			print "<td class=\"dayname\">" . $lang['sunday'] . "</td>";
			print "<td></td>";
			print "</tr></thead>";
			
			$calAr = array();
			$day1 = date('N', strtotime($curyear . "-" . $curmonth . "-01 00:00:00"));
			
			$lines = 0;
			for ($dc=1;$dc<=date('t',strtotime($curyear . "-" . $curmonth . "-01 00:00:00"));$dc++) {
				
				$weekpos = date('N', strtotime($curyear . "-" . $curmonth . "-" . $dc . " 00:00:00"));
				
				if ($weekpos == 1) {
					$lines++;
				}
				$calAr[$lines][$weekpos] = date('Y-m-d', strtotime($curyear . "-" . $curmonth . "-" . $dc . " 00:00:00"));
			}
			
			foreach ($calAr AS $line) {
				
				print "<tr class=\"dayrow\">";
				
				foreach ($line AS $el) {
					if ($el != "") {
						$firstday = $el;
						break;
					}
				}

				print "<td class=\"cal_week\">" . date('W', strtotime($firstday . " 00:00:00")) . "</td>";

				for ($t=1;$t<8;$t++) {
					$day = $line[$t];
					
					if ($day == "") {
						print "<td></td>";
					} else {
						$epoch = strtotime($day);
						if ($SelectAction) {
							$td_onclick = " onclick=\"PutSelectedDateInParentForm('JS_EFID" . htme($_REQUEST['selectField']) . "', '" . TransformDate(date('d-m-Y', $epoch)) . "'," . htme($_REQUEST['selectField']) . ");\" style=\"cursor: pointer;\"";
						} else {
							$td_onclick = "";
						}
						print "<td class=\"calendar-cell\" " . $td_conclick . ">";
							print "<div class=\"event_div\">";
								print "<table class=\"calceltable\">";
			
									print "<thead><tr><td " . $td_onclick . " class=\"cal_day\"><table style=\"width: 100%;\"><tr><td " . $td_onclick . ">" . $day . "</td><td " . $td_onclick . " class=\"rightalign\">" . count($per_day[$epoch]) . "</td></tr></table></td></tr></thead>";
									
									

									if (is_array($per_day[$epoch])) {
										$to_print_1st = "";
										$to_print = "";
										foreach ($per_day[$epoch] AS $event) {

											$html = "";
											$c++;
											if (is_array($colors)) {
												$divcolor = "style=\"background-color: " . $colors[$event['id']] . "\";";
												$loccol = $colors[$event['id']];
											} else {
												$divcolor = "";
												$loccol = "";
											}
											$html = "<table>";
												$html .= "<tr><td " . $td_onclick . ">" . $event['startdate'] . " " . $event['starttime'] . " - " . $event['duedate'] . " " . $event['duetime'] . "</td></tr>";
												if ($event['comment'] != "") {
													$html .= "<tr><td " . $td_onclick . ">" . $event['comment'] . "</td></tr>";
												}
												if ($event['user'] != "") {
													$html .= "<tr><td " . $td_onclick . ">" . $event['user'] . "</td></tr>";
												}
												if ($event['resource'] != "") {
													$html .= "<tr><td " . $td_onclick . ">" . $event['resource'] . "</td></tr>";
												}
												if ($event['location'] != "") {
													$html .= "<tr><td " . $td_onclick . ">" . $event['location'] . "</td></tr>";
												}
											$html .= "</table>";

											$timing = "";
											if ($event['starttime']) {
												$timing = $event['starttime'];
											} 
											if ($event['duetime']) {
												if ($timing) $timing .= "-";
												$timing .= $event['duetime'];
											}

											if ($event['allday'] != true) {

												$to_print .= "<tr><td " . PrintToolTipCode($html) . "><div class=\"event_main\" " . $divcolor . " ><span class=\"" . ReturnClassnameForTextColorBasedOnBackgroundColor($loccol) . "\">" . $ins . $timing . " <strong>" . $event['summary'] . "</strong></span></div></td></tr>";
											} else {
												$to_print_1st .= "<tr><td " . PrintToolTipCode($html) . "><div class=\"event_main_allday\" " . $divcolor . " ><span class=\"" . ReturnClassnameForTextColorBasedOnBackgroundColor($loccol) . "\">" . $ins . $timing . " <strong>" . $event['summary'] . "</strong></span></div></td></tr>";
											}
										}
									print $to_print_1st;
									print $to_print;
									}

								print "</table>";
						}
				}
			print "<td class=\"cal_week\">" . date('W', strtotime($firstday . " 00:00:00")) . "</td>";
			print "</tr>";
			
			}


			print "</table>";

			print "</div>";

			break;
		case "events":
			$c = 0;
			print "<table class=\"crm\">";
			foreach ($per_day AS $epoch => $events) {
				if (is_array($colors)) {
					$ins = "<span style=\"background-color: " . $colors[$event['id']] . "; padding: 2px 3px 0px 3px;\">&nbsp;</span>&nbsp;";
				}
				print "<thead><tr><td colspan=\"2\"><h2>" . htme(TransformDate(date('d-m-Y', $epoch))) . "</h2></td></tr></thead>";
				foreach ($events AS $event) {
					$c++;
					print "<tr><td>" . $ins . "<strong>" . $event['summary'] . "</strong></td></tr>";
					if (trim($event['comment']) != "") {
						print "<tr><td><a onclick=\"toggleLayer('commentdiv" . $c . "');\"><img src=\"images/info.gif\"></a><div id=\"commentdiv" . $c . "\" style=\"display: none\">" . $event['comment'] . "</div></td></tr>";
					}
					if ($event['user'] != "") {
						print "<tr><td>" . $event['user'] . "</td></tr>";
					}
					if ($event['resource'] != "") {
						print "<tr><td>" . $event['resource'] . "</td></tr>";
					}
					if ($event['location'] != "") {
						print "<tr><td>" . $event['location'] . "</td></tr>";
					}

					print "<tr><td>" . $event['startdate'] . " " . $event['starttime'] . " - " . $event['duedate'] . " " . $event['duetime'] . "</td></tr>";
				}
			}
			print "</table>";
			break;
	}
}
function GetScheme() {
	
	$colors = array();

	for ($r=40;$r<256;$r+=140) {
		for ($g=40;$g<256;$g+=90) {	
			for ($b=40;$b<256;$b+=100) {
				$red = dechex($r);
				$green = dechex($g);
				$blue = dechex($b);

				$red = (strlen($red)==1) ? "0" . $red : $red;
				$green = (strlen($green)==1) ? "0" . $green : $green;
				$blue = (strlen($blue)==1) ? "0" . $blue : $blue;

				$colors[] = "#" . $red . $green . $blue;

			}
		}
	}
	return($colors);
}
?>