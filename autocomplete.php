<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * autocomplete.php. This script returns a JSON autocomplete array
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */

require_once("initiate.php");

if ($_GET["q"] != "" && $_GET["id"] != "") {

	// Array to hold suggestions
	$dug = array(); 

//	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
//	header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
//	header("Cache-Control: no-cache, must-revalidate" ); 
//	header("Pragma: no-cache" );
//	header("Content-type: application/x-json");

	$field = str_replace("JS_", "", $_GET["id"]);

	if ($field == "summarysearch") {

		$tmp = GetAttribute("user", "SummarySearchWords", $GLOBALS['USERID']); 
		foreach ($tmp AS $row) {
			if (stristr($row, $_GET['q'])) {
				$sug[] = $row;
			}
		}

	} elseif (substr($field, 0, 15) == "flextablesearch") {
		$ft = str_replace("flextablesearch", "", $field);

		$tmp = GetAttribute("user", "Flextable" . $ft . "SearchWords", $GLOBALS['USERID']); 

		foreach ($tmp AS $row) {
			if (stristr($row, $_GET['q'])) {
				$sug[] = $row;
			}
		}
	
	} elseif ($field == "entitysearch" || $field == "entitysearch_overall") {

		$tmp = GetAttribute("user", "EntitylistSearchWords", $GLOBALS['USERID']); 

		foreach ($tmp AS $row) {
			if (stristr($row, $_GET['q'])) {
				$sug[] = $row;
			}
		}

	} elseif ($field == "customersearch") {

		$tmp = GetAttribute("user", "CustomerlistSearchWords", $GLOBALS['USERID']); 

		foreach ($tmp AS $row) {
			if (stristr($row, $_GET['q'])) {
				$sug[] = $row;
			}
		}


	} else {
		if ($field == "category") {
			$table = "entity";
			$field = "category";
		} else {
			$table = GetExtraFieldTableType(str_replace("EFID", "", $field));

			$fieldtype = GetExtraFieldType(str_replace("EFID", "", $field));
			
			if ($fieldtype == "textbox") {
				// nothing
			} else {
				qlog(ERROR, "Autocomplete request for wrong field type!");
				log_msg("ERROR: Autocomplete request for wrong field type!");
				$die = true;
			}
		}
		
		if ($table == "entity") {
			$tablename = $GLOBALS['TBL_PREFIX'] . "entity";
			$key = "eid";
		} elseif ($table == "customer") {
			$tablename = $GLOBALS['TBL_PREFIX'] . "customer";
			$key = "id";
		} elseif (is_numeric($table)) {
			$tablename = $GLOBALS['TBL_PREFIX'] . "flextable" . $table;
			$key = "recordid";
		} else {
			qlog(ERROR, "Table type could not be determined for this autocomplete request!");
			log_msg("ERROR: Table type could not be determined for this autocomplete request!");
			$die = true;
		}


		
		if (!$die) {

			$tmp = db_GetArray("SELECT " . $key . "," . $field . " FROM " . $tablename . " WHERE ". $field . " LIKE '%" . mres($_GET["q"]) . "%' ORDER BY " . $field);

			foreach ($tmp AS $row) {
				if ($table == "entity") {
					$acc = CheckEntityAccess($row[$key]);
				} elseif ($table == "customer") {
					$acc = CheckCustomerAccess($row[$key]);
				} elseif (is_numeric($table)) {
					$acc = CheckFlextableRecordAccess($table, $row[$key]);
				}

				if ($acc != "nok" && count($sug) < 13) {
					$sug[] = $row[$field];
				}
			}
		}
	}

	
	if (!$die) {

		if (count($sug) == 0) {
			// irritating!
			$sug[] = "";
		}

		//print "{\n";
		//print "\tquery: \"" . str_replace('"', '\"', $_GET['query']) . "\",\n";
		//print "\tsuggestions:" . json_encode($sug) . "\n";
		//print "}";
		foreach($sug AS $suggestion) {
			print $suggestion . "|";
		}

	} else {
		
	}

}