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
function ReturnNumericfieldRangeSelectOptions($field, $selectedval, $includedeleted=false, $refer=false) {
		global $lang;
	    
	$steps = 6;

	$table = GetExtraFieldTableType($field);

	if ($table == "entity") {
		$id = "eid";
	} elseif ($table == "customer") {
		$id = "id";
	} else {
		$id = "recordid";
		$flextableid = $table;
		$table = "flextable" . $table;
	}


	if (!$includedeleted && $table == "entity") {
		$entity_select_ins = " AND deleted != 'y'";
	}

	if ($refer) {
		$entity_select_ins .= " AND refer='" . mres($refer) . "' ";
	}


	$list = (db_GetFlatArray("SELECT DISTINCT(EFID" . $field . ") FROM " . $GLOBALS['TBL_PREFIX'] . $table . " WHERE EFID" . $field . " != '' AND EFID" . $field . " != '0.0' " . $entity_select_ins));


	sort($list);

	$min = $list[0];
	$max = $list[count($list) -1];

	$i=0;

	$ok = false;
	while (!$ok) {
		$eMin = db_GetFlatArray("SELECT " . $id . " FROM " . $GLOBALS['TBL_PREFIX'] . $table . " WHERE EFID" . $field . "='" . mres($min) . "' " . $entity_select_ins);
		foreach ($eMin AS $eid) {
			if ($table == "entity") {
				$acc = CheckEntityAccess($eid);
			} elseif ($table == "customer") {
				$acc = CheckCustomerAccess($eid);
			} else {
				$acc = CheckFlextableRecordAccess($flextableid, $eid);
			}
			if ($acc == "readonly" || $acc == "ok") {
				$ok = true;
				continue;
			}
		}
		if (!$ok) {
			unset($list[$i]);
			$i++;
			$min = $list[$i];
		}
		if ($i >= count($list)) {
			return(false);
		}

	}
	$ok = false;
	$i = count($list);

	while (!$ok) {
		$eMax = db_GetFlatArray("SELECT " . $id . " FROM " . $GLOBALS['TBL_PREFIX'] . $table . " WHERE EFID" . $field . "='" . mres($max) . "' " . $entity_select_ins);
		foreach ($eMax AS $eid) {
			if ($table == "entity") {
				$acc = CheckEntityAccess($eid);
			} elseif ($table == "customer") {
				$acc = CheckCustomerAccess($eid);
			} else {
				$acc = CheckFlextableRecordAccess($flextableid, $eid);
			}

			if ($acc == "readonly" || $acc == "ok") {
				$ok = true;
				continue;
			}
		}
		if (!$ok) {
			unset($list[$i]);
			$i--;
			$max = $list[$i];
		}
		if ($i == 0) {
			return(false);
		}

	}


	$tel = count($list);
	$interval = $tel / $steps;

	$van = 1;
	$tot = 1;
	$vanvalue = 0;
	$totvalue = 0;

	$indexflt = 0;
	$indexint = 0;

	$range = "";
	$GT = "";
	$LT = "";

//blank
//> 0

	$vanvalue = "";
	$selected = "";
	if ($selectedval == "EQ:" . $vanvalue) $selected="selected='selected'";
	$GT    .= "<option " . $selected . " value='EQ:" . htme($vanvalue) . "'>[" . $lang['showwhenempty'] . "]</option>";


	$vanvalue = "0";
	$selected = "";
	if ($selectedval == "GTNE:" . $vanvalue) $selected="selected='selected'";
	$GT    .= "<option " . $selected . " value='GTNE:" . htme($vanvalue) . "'>&gt; 0</option>";

	for ($i = 0; $i < $steps; $i++)
	{
		$indexflt += $interval;
		$tot = round($indexflt);

		$totvalue = $list[$tot-1];
		$vanvalue = $list[$van-1];

		$selected = "";
		if ($selectedval == "RA:" . $vanvalue . ":" . $totvalue) $selected="selected='selected'";
		$range .= "<option " . $selected . " value='RA:" . htme($vanvalue) . ":" . htme($totvalue) . "'>" . FormatNumber($vanvalue,2,$field) . " - " . FormatNumber($totvalue,2,$field) . "</option>";
		$selected = "";
		if ($selectedval == "GT:" . $vanvalue) $selected="selected='selected'";
		$GT    .= "<option " . $selected . " value='GT:" . htme($vanvalue) . "'>&gt;= " . FormatNumber($vanvalue,2,$field) . "</option>";
		$selected = "";
		if ($selectedval == "LT:" . $vanvalue) $selected="selected='selected'";
		$LT    .= "<option " . $selected . " value='LT:" . htme($vanvalue) . "'>&lt; " . FormatNumber($vanvalue,2,$field) . "</option>";
		$van = $tot;
	}

	return($GT . "<option value=''>-------</option>" . $range . "<option value=''>-------</option>" . $LT);
}




function CreateSQLFromDateFilterArray($fa) {
	global $lang;

	$efl = CreateDateFieldsList();
	$query = "";
	foreach ($efl AS $field => $name) {
		$sec = false;

		$date_epoch_before = NLDate2Epoch($fa['BEFORE_' . $field]);
		$date_epoch_after = NLDate2Epoch($fa['AFTER_' . $field]);

		// Check relative settings
		if (isset($fa['REL_BEFORE_' . $field])) {
			if (strstr($fa['REL_BEFORE_' . $field], "weeksbeforenow_")) {
				$date_epoch_before = date('U') - (7 * 86400 * str_replace("weeksbeforenow_", "", $fa['REL_BEFORE_' . $field]));
			} elseif (strstr($fa['REL_BEFORE_' . $field], "daysbeforenow_")) {
				$date_epoch_before = date('U') - (86400 * str_replace("daysbeforenow_", "", $fa['REL_BEFORE_' . $field]));
			} elseif (strstr($fa['REL_BEFORE_' . $field], "weeksfromnow_")) {
				$date_epoch_before = date('U') + (7 * 86400 * str_replace("weeksfromnow_", "", $fa['REL_BEFORE_' . $field]));
			} elseif (strstr($fa['REL_BEFORE_' . $field], "daysfromnow_")) {
				$date_epoch_before = date('U') + (86400 * str_replace("daysfromnow_", "", $fa['REL_BEFORE_' . $field]));
			}
		}
		if (isset($fa['REL_AFTER_' . $field])) {
			if (strstr($fa['REL_AFTER_' . $field], "weeksbeforenow_")) {
				$date_epoch_after = date('U') - (7 * 86400 * str_replace("weeksbeforenow_", "", $fa['REL_AFTER_' . $field]));
			} elseif (strstr($fa['REL_AFTER_' . $field], "daysbeforenow_")) {
				$date_epoch_after = date('U') - (86400 * str_replace("daysbeforenow_", "", $fa['REL_AFTER_' . $field]));
			} elseif (strstr($fa['REL_AFTER_' . $field], "weeksfromnow_")) {
				$date_epoch_after = date('U') + (7 * 86400 * str_replace("weeksfromnow_", "", $fa['REL_AFTER_' . $field]));
			} elseif (strstr($fa['REL_AFTER_' . $field], "daysfromnow_")) {
				$date_epoch_after = date('U') + (86400 * str_replace("daysfromnow_", "", $fa['REL_AFTER_' . $field]));
			}
		}



//		print "DEB: " . $date_epoch_before . " DEA: " . $date_epoch_after . "(" . $field . ")<br>";

		if (is_numeric($field)) {
			$type = GetExtraFieldTableType($field);
			if ($type == "") {
				$type = "entity";
			}
			$dbfield = $GLOBALS['TBL_PREFIX'] . $type . ".EFID" . $field;
		} else {
			$dbfield = $GLOBALS['TBL_PREFIX'] . "entity." . $field;
		}
		$localquery = "";
		if (is_numeric($date_epoch_before)) {
			if ($sec) {
				$localquery .= " AND ";
			}
			if (strstr($field, "epoch")) {
				$localquery .=  " " . mres($dbfield) . " < " . $date_epoch_before;
			} else {
				$localquery .=  " UNIX_TIMESTAMP(CONCAT(SUBSTR(" . mres($dbfield) . ",7,4), SUBSTR(" . mres($dbfield) . ",4,2), SUBSTR(" . mres($dbfield) . ", 1,2))) < " . $date_epoch_before;
			}
			if ($text) $text .= ", ";
			$text .= $efl[$field] . " " . $lang['before'] . " " . TransformDate(date('d-m-Y', $date_epoch_before));

			$sec = true;
		}
		if (is_numeric($date_epoch_after)) {
			if ($sec) {
				$localquery .= " AND ";
			}
			if (strstr($field, "epoch")) {
				$localquery .=  " " . mres($dbfield) . " > " . $date_epoch_after;
			} else {
				$localquery .=  " UNIX_TIMESTAMP(CONCAT(SUBSTR(" . mres($dbfield) . ",7,4), SUBSTR(" . mres($dbfield) . ",4,2), SUBSTR(" . mres($dbfield) . ", 1,2))) > " . $date_epoch_after;
			}

			if ($text) $text .= ", ";
			$text .= $efl[$field] . " " . $lang['after'] . " " . TransformDate(date('d-m-Y', $date_epoch_after));

			$sec = true;
		}

		if ($fa['SHOWEMPTY_' . $field] == "yes" && $sec) {
			$localquery = " (" . mres($dbfield) . " = '' OR (" . $localquery . "))";
			$text .= " (" . $lang['orblank'] . ")";
		}
		if (!$firstdone && trim($localquery) != "") {
			$query .= $localquery;
			$firstdone = true;
		} elseif (trim($localquery) != "") {
			$query .= " AND " . $localquery;
		}
	}

//	print "<h1>" . $query . "</h1>";
	if ($firstdone && $GLOBALS['From_Summary']) {
		print $lang['datefilteractive'] . " : " . $text;
	}
	return($query);
}

function RelativeEnglishDateToSQL($fieldname, $tag) {

	/*
	$datefilter['@CURDAY@']
	$datefilter['@LASTDAY@']
	$datefilter['@CURWEEK@']
	$datefilter['@LASTWEEK@']
	$datefilter['@CURMONTH@']
	$datefilter['@LASTMONTH@']
	$datefilter['@CURQUARTER@']
	$datefilter['@CURYEAR@']
	$datefilter['@LASTYEAR@']
	$datefilter['@CURYEARQ1@']
	$datefilter['@CURYEARQ2@']
	$datefilter['@CURYEARQ3@']
	$datefilter['@CURYEARQ4@']
	*/

	$date_from = "";
	$date_until = "";

	if ($tag == "@CURQUARTER@")
	{
		$month = date('m');
		if ($month < 4)
		{
			$tag = "@CURYEARQ1@";
		}
		else if ($month < 7)
		{
			$tag = "@CURYEARQ2@";
		}
		else if ($month < 10)
		{
			$tag = "@CURYEARQ3@";
		}
		else
		{
			$tag = "@CURYEARQ4@";
		}
	}

	switch($tag) {
		case "@CURDAY@":
			$date_from = date('Y-m-d');
			$date_until = $date_from;
		break;
		case "@B4TODAY@":
			$date_until = date('Y-m-d');
		break;
		case "@AFTERTODAY@":
			$date_from = date('Y-m-d', strtotime("+1 days"));
		break;
		case "@LASTDAY@":
			$date_from = date('Y-m-d', strtotime("-1 days"));
			$date_until = $date_from;
		break;
		case "@NEXTDAY@":
			$date_from = date('Y-m-d', strtotime("+1 days"));
			$date_until = $date_from;
		break;
		case "@CURWEEK@":
			$weekday = date("N");
			//Jeroen: als het vandaag maandag is, dan geeft last monday niet vandaag maar vorige week terug, vandaar deze check
			if ($weekday != 1)
			{
				$date_from = date('Y-m-d', strtotime("last monday"));
				$date_until = date('Y-m-d', strtotime("last monday +6 days"));
			}
			else
			{
				$date_from = date('Y-m-d');
				$date_until = $date_until = date('Y-m-d', strtotime($date_from . " +6 days"));
			}
		break;
		case "@LASTWEEK@":
			$weekday = date("N");
			//Jeroen: als het vandaag maandag is, dan geeft last monday niet vandaag maar vorige week terug, vandaar deze check
			if ($weekday != 1)
			{
				$date_from = date('Y-m-d', strtotime("last monday -7 days"));
				$date_until = date('Y-m-d', strtotime("last monday -1 days"));
			}
			else
			{
				$date_from = date('Y-m-d', strtotime("last monday"));
				$date_until = date('Y-m-d', strtotime("last monday +6 days"));
			}
		break;
		case "@NEXTWEEK@":
			$date_from = date('Y-m-d', strtotime("next monday"));
			$date_until = date('Y-m-d', strtotime("next monday +6 days"));
		break;
		case "@CURMONTH@":
			$date_from = date('Y-m-01');
			$date_until = date('Y-m-d', strtotime($date_from . " +1 month -1 day"));
		break;
		case "@LASTMONTH@":
			$date_from = date('Y-m-01', strtotime("-1 month"));
			$date_until = date('Y-m-d', strtotime($date_from . " +1 month -1 day"));
		break;
		case "@NEXTMONTH@":
			$date_from = date('Y-m-01', strtotime("+1 month"));
			$date_until = date('Y-m-d', strtotime($date_from . " +1 month -1 day"));
		break;
		case "@CURYEAR@":
			$date_from = date('Y-01-01');
			$date_until = date('Y-12-31');
		break;
		case "@LASTYEAR@":
			$date_from = date('Y')-1 . "-01-01";
			$date_until = date('Y')-1 . "-12-31";
		break;
		case "@NEXTYEAR@":
			$date_from = date('Y')+1 . "-01-01";
			$date_until = date('Y')+1 . "-12-31";
		break;

		case "@CURYEARQ1@":
			$date_from = date('Y') . "-01-01";
			$date_until = date('Y') . "-03-31";
		break;
		case "@CURYEARQ2@":
			$date_from = date('Y') . "-04-01";
			$date_until = date('Y') . "-06-30";
		break;
		case "@CURYEARQ3@":
			$date_from = date('Y') . "-07-01";
			$date_until = date('Y') . "-09-30";
		break;
		case "@CURYEARQ4@":
			$date_from = date('Y') . "-10-01";
			$date_until = date('Y') . "-12-31";
		break;
		case "@LASTYEARQ1@":
			$date_from = date('Y')-1 . "-01-01";
			$date_until = date('Y')-1 . "-03-31";
		break;
		case "@LASTYEARQ2@":
			$date_from = date('Y')-1 . "-04-01";
			$date_until = date('Y')-1 . "-06-30";
		break;
		case "@LASTYEARQ3@":
			$date_from = date('Y')-1 . "-07-01";
			$date_until = date('Y')-1 . "-09-30";
		break;
		case "@LASTYEARQ4@":
			$date_from = date('Y')-1 . "-10-01";
			$date_until = date('Y')-1 . "-12-31";
		break;
		default:
			// nothin'
		break;
	}
	

	if (strstr($tag, "@WEEK")) {
		$weeknumber = str_replace("WEEK", "", str_replace("@", "", $tag));
		$date_from = year_weeknumber_to_mondaydate(date('Y'), $weeknumber);
		$date_until = date('Y-m-d', strtotime($date_from . " 6 day"));
	}

	if (strstr($tag, "@LYWEEK")) {
		$weeknumber = str_replace("LYWEEK", "", str_replace("@", "", $tag));
		$date_from = year_weeknumber_to_mondaydate(date('Y') - 1, $weeknumber);
		$date_until = date('Y-m-d', strtotime($date_from . " 6 day"));
	}

	if (strstr($tag, "@MONTH")) {
		// Month filter
		$month = str_replace("MONTH", "", str_replace("@", "", $tag));
		$date_from = date('Y') . "-" . $month . "-01";
		$date_until = date('Y-m-d', strtotime($date_from . " +1 month -1 day"));
	}

	if (strstr($tag, "@LYMONTH")) {
		// Last year month filter
		$month = str_replace("LYMONTH", "", str_replace("@", "", $tag));
		$date_from = date('Y') . "-" . $month . "-01";
		$date_until = date('Y-m-d', strtotime($date_from . " +1 month -1 day"));
	}

	if (strstr($tag, "@YEAR")) {
		// Year filter
		$year = str_replace("YEAR", "", str_replace("@", "", $tag));
		$date_from = $year . "-01-01";
		$date_until = $year . "-12-31";
	}
	if (substr($fieldname, strlen($fieldname)-12,12) == "creationdate" || substr($fieldname, strlen($fieldname)-5,5) == "cdate") {
		$fieldname = "cdate";
		$format = "%Y-%m-%d";
	} elseif (substr($fieldname, strlen($fieldname)-9,9) == "closedate") {
		$foverride = "closedate";
	} else {
		$format = "%d-%m-%Y";
	} 
	
	$and_sql_ins = "";
	$tmp = explode("EFID", $fieldname);
	$fieldnum = $tmp[1];


	if ($foverride != "closedate" && (GetExtraFieldType($fieldnum) == "date" || (!is_numeric($fieldnum) && GetExtraFieldType($fieldname) != "date/time") || (GetExtraFieldType($fieldnum) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $fieldnum) == "Date"))) {
		
		if (($date_from == $date_until) && ($date_from != "")) {
			//small optimization for mysql for selections today, yesterday and tomorrow
			$and_sql_ins .= " AND STR_TO_DATE(" . $fieldname . ",'" . $format . "')='" . $date_from . "'";
		} else {
			if ($date_from != "") {
				$and_sql_ins .= " AND STR_TO_DATE(" . $fieldname . ",'" . $format . "')>='" . $date_from . "'";
			}
			if ($date_until != "") {
				$and_sql_ins .= " AND STR_TO_DATE(" . $fieldname . ",'" . $format . "')<='" . $date_until . "'";
			}
		}
	} elseif ($foverride == "closedate" || GetExtraFieldType($fieldnum) == "date/time" || GetExtraFieldType($fieldname) == "date/time" ) {
//		DA("bingo 2");
		if (($date_from == $date_until) && ($date_from != "")) {
			//small optimization for mysql for selections today, yesterday and tomorrow
			$and_sql_ins .= " AND DATE(" . $fieldname . ")='" . $date_from . "'";
		} else {
			if ($date_from != "") {
				$and_sql_ins .= " AND DATE(" . $fieldname . ")>='" . $date_from . "'";
			}
			if ($date_until != "") {
				$and_sql_ins .= " AND DATE(" . $fieldname . ")<='" . $date_until . "'";
			}
		}
	} else {
		print "<h2> $fieldname not found </h2>";
	}

//print '<h1>' . $tag . ' ' . $date_from . ' - ' . $date_until . ' ' . $and_sql_ins . '</h1>';

	if ($tag == "@EMPTY@") {
		$and_sql_ins = " AND " . $fieldname . "=''";
		qlog(INFO, "Range based on " . $fieldname . " == empty");
	} elseif ($tag == "@NOTEMPTY@") {
		$and_sql_ins = " AND " . $fieldname . "!=''";
		qlog(INFO, "Range based on " . $fieldname . " == empty");

	} else {
		qlog(INFO, "Range based on " . $fieldname . " from " . $date_from . " until " . $date_until);
	}

	return($and_sql_ins);
}
function CreateDateFilterOptionsList() {
	global $lang;
	$datefilter = array();

	$datefilter['@EMPTY@'] = '[empty]';
	$datefilter['@NOTEMPTY@'] = '[NOT empty]';

	$datefilter['@CURDAY@'] = $lang['today'];
	$datefilter['@B4TODAY@'] = $lang['edd'];
	$datefilter['@AFTERTODAY@'] = "In the future";
	$datefilter['@CURWEEK@'] = $lang['thisweek'];
	$datefilter['@CURMONTH@'] = $lang['thismonth'];
	$datefilter['@CURQUARTER@'] = $lang['thisquarter'];
	$datefilter['@CURYEAR@'] = $lang['thisyear'] . " (" . date('Y') . ")";
	$datefilter['zz'] = "---";
	$datefilter['@NEXTDAY@'] = $lang['tomorrow'];
	$datefilter['@NEXTWEEK@'] = $lang['nextweek'];
	$datefilter['@NEXTMONTH@'] = $lang['nextmonth'];
	$datefilter['@NEXTYEAR@'] = $lang['nextyear'] . " (" . (date('Y') + 1) . ")";;
	$datefilter['zzz'] = "---";
	$datefilter['@LASTDAY@'] = $lang['yesterday'];
	$datefilter['@LASTWEEK@'] = $lang['lastweek'];
	$datefilter['@LASTMONTH@'] = $lang['lastmonth'];
	$datefilter['@LASTYEAR@'] = $lang['lastyear'] . " (" . (date('Y') - 1) . ")";

	$y = date('Y');

	for ($x=($y-2);$x>($y-5);$x--) {
		$datefilter["@YEAR" . $x . "@"] = $x;
	}

	$datefilter['zzzz'] = "---";
	$datefilter['@CURYEARQ1@'] = $lang['thisyearq1'];
	$datefilter['@CURYEARQ2@'] = $lang['thisyearq2'];
	$datefilter['@CURYEARQ3@'] = $lang['thisyearq3'];
	$datefilter['@CURYEARQ4@'] = $lang['thisyearq4'];
	$datefilter['zzzzzy'] = "---";
	$datefilter['@LASTYEARQ1@'] = $lang['lastyearq1'];
	$datefilter['@LASTYEARQ2@'] = $lang['lastyearq2'];
	$datefilter['@LASTYEARQ3@'] = $lang['lastyearq3'];
	$datefilter['@LASTYEARQ4@'] = $lang['lastyearq4'];

	$datefilter['zzzzz'] = "---";
	for ($i=1;$i<13;$i++) {
		$datefilter['@MONTH' . $i . '@'] = $lang['thisyear'] . " " . $lang['month' . $i];
	}
	$datefilter['zzzzzz'] = "---";
	for ($i=1;$i<13;$i++) {
		$datefilter['@LYMONTH' . $i . '@'] = $lang['lastyear'] . " " . $lang['month' . $i];
	}

	$datefilter['zzzzzzz'] = "---";
	for ($i=1;$i<=53;$i++) {
		$datefilter['@WEEK' . $i . '@'] = $lang['thisyear'] . " week " . $i;
	}
	
	$datefilter['zzzzzzzz'] = "---";
	for ($i=1;$i<=53;$i++) {
		$datefilter['@LYWEEK' . $i . '@'] = $lang['lastyear'] . " week " . $i;
	}
	

	$datefilter_optionslist = "";

	foreach ($datefilter AS $val => $text) {
		$datefilter_optionslist .= "<option value='" . htme($val) . "'>" . htme($text) . "</option>";
	}
	return($datefilter);
}

?>
