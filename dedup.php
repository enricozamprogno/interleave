<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file enables users to compare tables
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */

require("initiate.php");
if (isset($_REQUEST['debug']) && is_administrator()) {
	$debug = true;
}

ShowHeaders();
//$debug = true;




print "<form id=\"dedupForm\" method=\"post\" action=\"dedup.php\">";

if (!$_REQUEST['Worker'] && !$_REQUEST['Reloaded']) { // No session exists, create one and ask some questions

		$worker = PushStashValue(array("table" => "unknown"));

	if (is_administrator()) {
		$ft = GetFlextableDefinitions();
		$tables = array("entity" => $lang['entities'], "customer" => $lang['customers']);
		foreach ($ft AS $f) {
			$tables[$f['recordid']] = $f['tablename'];
		}
		//$tables['users'] = "Users";
		$set = true;
	} else {
		$tables = array();
		$set = false;
		if (CheckFunctionAccess("AllowedTodedupEntities") == "ok") {
			$tables['entity'] = $lang['entities'];
			$set = true;
		}
		if (CheckFunctionAccess("AllowedTodedupCustomers") == "ok") {
			$tables['customer'] = $lang['customers'];
			$set = true;
		}
		foreach (GetFlextableDefinitions() AS $ft) {
			$configname = "AllowedTodedupFT" . $ft['recordid'];
			if (CheckFunctionAccess($configname) == "ok") {
				$tables[$ft['recordid']] = $ft['tablename'];
				$set = true;
			}
		}
		if (CheckFunctionAccess("AllowedTodedupUsers") == "ok") {
			//$tables['users'] = "Users";
			//$set = true;
		}
			
	}

	if ($set) {
		print "Choose table to de-duplicate: ";
		print "<select name=\"tableId\" id=\"JS_table\">";
		foreach ($tables AS $table => $name) {
			print "<option value=\"" . $table . "\">" . htme($name) . "</option>";
		}
		print "</select>";
	} else {
		PrintAD("You're not allowed to use this functionality");
		$error = true;
	}
} else {
	print "<a href=\"dedup.php\">Stop / start over</a><br>";

	$worker = $_REQUEST['Worker'];
	if ($_REQUEST['tableId'] != "") {
		$mem['table'] = $_REQUEST['tableId'];
	}
	if ($worker == "new") {
		$worker = PushStashValue($mem);
	}
	
	
	
	
		
	
	if (isset($_REQUEST['Reloaded'])) {
		$worker = $_REQUEST['Reloaded'];
		$mem = PopStashValue($worker);
		print "<h1>Processed " . count($mem['processed']) . " records but no " . $mem['percentage'] . "% duplicates found in the last set</h1><h2>Please wait while the system searches.</h2>"; 
		$stillbuisy = true;
	} else {
		$mem = PopStashValue($worker);
	}
	
	if ($_REQUEST['tableId'] == "" && ($mem['table'] == "unknown" || $mem['table'] == "")) {
		
		PrintAD("No table id received");

	} elseif (is_array($_REQUEST['fieldName']) || $mem['field']) {
	
		if (is_numeric($_REQUEST['percentage']) && $_REQUEST['percentage'] > 0 && $_REQUEST['percentage'] < 101 && $mem['percentage'] == "") {
			$mem['percentage'] = $_REQUEST['percentage'];
		} elseif ($mem['percentage'] == "") {
			$mem['percentage'] = 95;
		}


		if (!$mem['enrich']) {
			if ($_REQUEST['Enrich'] != "") {
				$mem['enrich'] = $_REQUEST['Enrich'];
				if ($mem['enrich'] == "yes-auto") {
					$mem['AutoFeed'] = true;
					$mem['enrich'] = "yes";
				} elseif ($mem['enrich'] == "no-auto") {
					$mem['AutoFeed'] = true;
					$mem['enrich'] = "no";
				} elseif ($mem['enrich'] == "yes-auto-dry") {
					$mem['AutoFeed'] = true;
					$mem['DryRun'] = true;
					$mem['enrich'] = "yes";
				} elseif ($mem['enrich'] == "no-auto-dry") {
					$mem['AutoFeed'] = true;
					$mem['DryRun'] = true;
					$mem['enrich'] = "no";
				} elseif ($mem['enrich'] == "yes-confirm") {
					$mem['AutoFeed'] = false;
					$mem['DryRun'] = false;
					$mem['enrich'] = "yes";
					$mem['enrich-confirm'] = true;
				} else {
					$mem['AutoFeed'] = false;
					$mem['DryRun'] = false;
				}

			} else {
				$mem['enrich'] = "yes";
			}
		}

		if (!$mem['field']) {
			
			if (count($_REQUEST['fieldName']) > 1) {
				$mem['compareFields'] = array();
				$mem['field'] = " CONCAT(''";
				foreach ($_REQUEST['fieldName'] AS $f) {
					$mem['field'] .= ", " . mres($f) . ", ' '";
					$mem['compareFields'][] = $f;
					$mem['extra_where'] .= " AND " . mres($f) . " != ''";
				}
				$mem['field'] .= ")";

				
				
			} else {
				$mem['compareFields'] = array($_REQUEST['fieldName'][0]);
				$mem['field'] = "`" . mres($_REQUEST['fieldName'][0]) . "`";
			}

		}
		if (!$mem['rowcounter']) {
			$mem['rowcounter'] = 0;
		}

		
		$ins = "";
		if ($mem['table'] == "entity") {
			
			$id = "eid";

			$items = "SELECT eid AS rec, " . ($mem['field']) . " AS value FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE 1=1 " . $mem['extra_where'];
			
			if ($mem['percentage'] == 100) {

				$tmplist = db_GetArray("SELECT " . $mem['field'] . ", COUNT(*) as cnt FROM " . $GLOBALS['TBL_PREFIX'] . "entity GROUP BY " . $mem['field'] . " HAVING cnt > 1");
				$items .= " AND " . $mem['field'] . " IN(''";
				foreach ($tmplist AS $proc) {
					$items .= ",'" . mres($proc[0]) . "'";
				}
				$items .= ")";
				
			} else {

				$items .= " AND " . $id . " NOT IN(0";
				foreach ($mem['processed'] AS $proc) {
					$items .= "," . $proc;
				}
				$items .= ")";
			}
			if ($_REQUEST['restoresession']) {
				$mem['restoresession'] = GetAttribute("user", "LastRecordDedup" . $mem['table'], $GLOBALS['USERID']);
			} 
			if ($mem['restoresession']) {
				$items .= " AND eid>=" . $mem['restoresession'];  
			}
			

			
			$table = "entity";
		} elseif ($mem['table'] == "customer") {
			$items = "SELECT id AS rec, " . ($mem['field']) . " AS value FROM " . $GLOBALS['TBL_PREFIX'] . "customer WHERE 1=1 " . $mem['extra_where'];
			$id = "id";
			if ($mem['percentage'] == 100) {
				$tmplist = db_GetArray("SELECT " . $mem['field'] . ", COUNT(*) as cnt FROM " . $GLOBALS['TBL_PREFIX'] . "customer GROUP BY " . $mem['field'] . " HAVING cnt > 1");
				$items .= " AND " . $mem['field'] . " IN(''";
				foreach ($tmplist AS $proc) {
					$items .= ",'" . mres($proc[0]) . "'";
				}
				$items .= ")";
			} else {
				$items .= " AND " . $id . " NOT IN(0";
				foreach ($mem['processed'] AS $proc) {
					$items .= "," . $proc;
				}
				$items .= ")";
			}
			
			if ($_REQUEST['restoresession']) {
				$mem['restoresession'] = GetAttribute("user", "LastRecordDedup" . $mem['table'], $GLOBALS['USERID']);
			} 
			if ($mem['restoresession']) {
				$items .= " AND eid>=" . $mem['restoresession'];
			}


			$table = "customer";
		} else {
			$items = "SELECT recordid AS rec, " . ($mem['field']) . " AS value FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . ($mem['table']) . " WHERE deleted!='y' " . $mem['extra_where'];
			$id = "recordid";
			if ($mem['percentage'] == 100) {
				$tmplist = db_GetArray("SELECT " . $mem['field'] . ", COUNT(*) as cnt FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $mem['table'] . " GROUP BY " . $mem['field'] . " HAVING cnt > 1");
				$items .= " AND " . $mem['field'] . " IN(''";
				foreach ($tmplist AS $proc) {
					$items .= ",'" . mres($proc[0]) . "'";
				}
				$items .= ")";
			} else {
				$items .= " AND " . $id . " NOT IN(0";
				foreach ($mem['processed'] AS $proc) {
					$items .= "," . $proc;
				}
				$items .= ")";
			}

			$table = "flextable" . $mem['table'];
			$ins = " WHERE deleted != 'y'";

			if ($_REQUEST['restoresession']) {
				$mem['restoresession'] = GetAttribute("user", "LastRecordDedup" . $mem['table'], $GLOBALS['USERID']);
				//print "RESTORE: " . $mem['restoresession'];
			} 
			if ($mem['restoresession']) {
				$items .= " AND recordid>=" . $mem['restoresession'];
			}


		}
		//print $items;
		// Process commands
				
		if (is_numeric($_REQUEST['Keep']) && is_array($_REQUEST['processingRecords'])) {
			print "<h1>Deleted " . (count($_REQUEST['processingRecords'])-1) . " records, kept 1</h1>";
			$keep = $_REQUEST['Keep'];
			$keeping = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . $table . " WHERE " . $id . " = " . $keep);

			$to_del = $_REQUEST['processingRecords']; // Except $keep!
			$sql = array();
			$confirm = array();

			foreach ($to_del AS $rtd) {

				if ($rtd != $keep) {

					if ($mem['table'] == "customer") {
						$sql[] = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET CRMcustomer=" . $keep . " WHERE CRMcustomer=" . $rtd;
						$sql[] = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "journal(eid, user, message, type) VALUES(" . $rtd . "," . $GLOBALS['USERID'] . ",'Customer edited / deleted (dedup)', 'customer')";
					} 
					foreach (GetFlextableDefinitions() AS $ft) {
						//print $ft['refers_to'] . " ==  " . $table . " && " . $ft['orientation'] . "== one_entity_to_many <br>";
						if ($ft['refers_to'] == $table && $ft['orientation'] == "one_entity_to_many") {
							$sql[] = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "flextable" . $ft['recordid'] . " SET refer=" . $keep . " WHERE refer=" . $rtd . "";
							$sql[] = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "journal(eid, user, message, type) VALUES(" . $keep . "," . $GLOBALS['USERID'] . ",'Flextable record edited / kept (dedup)', 'flextable" . $ft['recordid'] . "')";
						} elseif ("flextable" . $ft['recordid'] == $table) { // It's me!

							if ($ft['orientation'] == "many_entities_to_one") {
						
								$refs = db_GetArray("SELECT id, tabletype FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE fieldtype='Reference to FlexTable' AND options='" .
								str_replace("flextable", "", $table) . "'");

								foreach ($refs AS $ref) {
									if (is_numeric($ref[1])) $ref[1] = "flextable" . $ref[1];
									$sql[] = "UPDATE " . $GLOBALS['TBL_PREFIX'] . $ref[1] . " SET EFID" . $ref[0] . "=" . $keep . ", timestamp_last_change=timestamp_last_change WHERE EFID" . $ref[0] . "='" . $rtd . "'";
									$sql[] = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "journal(eid, user, message, type) VALUES(" . $keep . "," . $GLOBALS['USERID'] . ",'Flextable record edited / kept (dedup)', 'flextable" . $ref[1] . "')";
								}
							} 
						} 
					}
				
					if ($mem['enrich'] == "yes") {
						$rtdRow = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . $table . " WHERE " . $id . " = " . $rtd);
						foreach ($rtdRow AS $rtdField => $rtdValue) {
							if (!is_numeric($rtdField)) {
								if ($keeping[$rtdField] == "" && $rtdValue != "") {
									$sql[] = "UPDATE " . $GLOBALS['TBL_PREFIX'] . $table . " SET " . $rtdField . "= CONCAT(" . $rtdField . ", '" . mres($rtdValue) . "'), timestamp_last_change=timestamp_last_change WHERE " . $id . " = " . $keep;
									$sql[] = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "journal(eid, user, message, type) VALUES(" . $keep . "," . $GLOBALS['USERID'] . ",'Record edited / enriched (dedup)', '" . $table . "')";
									if ($mem['enrich-confirm']) {
										$confirm[] = array("Field" => GetExtraFieldName(str_replace("EFID", "", $rtdField)), "OldValue" => db_GetValue("SELECT ". $rtdField . " FROM " . $GLOBALS['TBL_PREFIX'] . $table . " WHERE " . $id . " = " . $keep), "NewValue" => db_GetValue("SELECT ". $rtdField . " FROM " . $GLOBALS['TBL_PREFIX'] . $table . " WHERE " . $id . " = " . $keep) . ", " . $rtdValue);
									}
								}
							}
						}
					}
					if ($table == "entity") {
						$sql[] = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET deleted='y' WHERE eid=" . $rtd;
						$sql[] = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "journal(eid, user, message, type) VALUES(" . $rtd . "," . $GLOBALS['USERID'] . ",'Entity edited / deleted (dedup)', 'entity')";
					} elseif ($table == "customer") {
						$sql[] = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "customer WHERE id=" . $rtd;
						$sql[] = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "journal(eid, user, message, type) VALUES(" . $rtd . "," . $GLOBALS['USERID'] . ",'Customer edited / deleted (dedup)', 'customer')";
					} else {
						$sql[] = "UPDATE " . $GLOBALS['TBL_PREFIX'] . $table . " SET deleted='y' WHERE recordid=" . $rtd;
						$sql[] = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "journal(eid, user, message, type) VALUES(" . $rtd . "," . $GLOBALS['USERID'] . ",'Record edited / deleted (dedup)', '" . $table . "')";

					}
				}
		
			}
//DA($sql);
			if ($mem['DryRun']) {

				foreach ($sql AS $q) {
					$mem['queries'][] = $q;
				}
				print count($mem['queries']) . " queries generated but not executed. Queries will be shown when deduplication is finished.<br>";
			} else {
				foreach ($sql AS $q) {
					mcq($q, $db);
					
				}
				print count($sql) . " queries executed.<br>";
			}
		}
//		DA($mem['queries']);

		// End process commands
		if (!$stillbuisy) {
			if ($mem['AutoFeed']) {
				print "<h1>Processing...</h1>";
			} else {
				print "<h1>Records shown below are " . $mem['percentage'] . "% or more alike based on the compare field(s). Select the one you wish to keep. <input type=\"submit\" value=\"skip this set\"></h1>"; 
				print "Only showing matches of " . $mem['percentage'] . "% and higher.";
				if ($mem['enrich'] == "yes") {
					print " Missing data which is available in records to be deleted will be added to the record being kept.";
				}

			}
			$nobutton = true;
		}
		print "<br><br>";

		$count =0;
		$rescount =0;
		if (!is_array($mem['processed'])) $mem['processed'] = array();

		$hit = false;
		$result = array();
		$res1 = mcq($items, $db);
		$start = date('U');
		//print "<h1>" . $items . "</h1>";
		while ($item1 = mysql_fetch_array($res1)) {
			if (!in_array($item1[0], $mem['processed'])) {
				$mem['processed'][] = $item1[0];
				$res2 = mcq($items, $db);
				while ($item2 = mysql_fetch_array($res2)) {
					if (!in_array($item2[0],$mem['processed'])) {
						$count++;
						if ($item1[0] != $item2[0]) {
							if ($mem['percentage'] < 100) {
								similar_text(strtolower($item1[1]), strtolower($item2[1]), $perc);
							} elseif ($mem['percentage'] == 100) {
								if ($item1[1] == $item2[1]) {
									$perc = 100;
								} else {
									$perc = 0; // doesn't matter
								}
							} else {
								print "<span class=\"noway\">Error: percentage not understood</span>";
								$perc = -1;
							}
							if ($perc >= $mem['percentage']) {
								if (!in_array($item1[0], $result)) $result[] = $item1[0];
								if (!in_array($item2[0], $result)) $result[] = $item2[0];
								$mem['processed'][] = $item2[0];
								$hit = true;
							}
						}
						
					}
				}
			}
			$mem['rowcounter']++;
			if ($hit) {
				break;
			}
			if (date('U') > ($start + 20)) {
				$reload = true;
				$submitform = true;
				break;
			}
		}

		
		
		if ($reload) {
			print "<input type=\"hidden\" name=\"Reloaded\" value=\"" . $worker . "\">";
		} else {
			if (count($result) > 0) {
				
				print "<table class=\"interleave-table\">";
				print "<thead><tr><td>Record</td><td>";
				foreach ($mem['compareFields'] AS $cf) {
					print GetExtraFieldName(str_replace("EFID", "", $cf)) . " " ;
				}
				print "</td><td>Last modification</td><td>Data record completeness</td><td>Referring records</td><td>Match</td><td>Exclude</td><td><strong>Choose record to keep</strong></td></tr></thead>";
				$first = "";
				$num = 0;
				foreach ($result AS $record) {

					$row = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . $table . " WHERE " . $id . "=" . $record);
					$score = 0;
					foreach ($row AS $test) {
						if ($test != "") {
							$score++;
						}
					}
					$firstrow = false;
					if ($first == "") {
						$firstrow = true;
						SetAttribute("user", "LastRecordDedup" . $mem['table'],$record, $GLOBALS['USERID']);
						foreach ($mem['compareFields'] AS $cf) {
							$first .= $row[$cf] . " ";
						}
					}
					print "<tr id=\"ROW" . $row[$id] . "\"><td>" . $row[$id] . "<input type=\"hidden\" name=\"processingRecords[]\" value=\"" . $row[$id] . "\"></td><td>";
					foreach ($mem['compareFields'] AS $cf) {
						print htme($row[$cf]) . " ";
					}

					print "</td>";
					print "<td>" . $row['timestamp_last_change'] . "</td>";

					print "<td>" . $score . "/" . count($row) . "</td>";
					
					$refrec = 0;

					if ($mem['table'] == "customer") {
						$refrec += db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE CRMcustomer=" . $record);
					} 
					foreach (GetFlextableDefinitions() AS $ft) {
						if ($ft['refers_to'] == $table && $ft['orientation'] == "one_entity_to_many") {
							
							$sql = "SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $ft['recordid'] . " WHERE refer='" . $record . "'";


							$refrec += db_GetValue($sql);
	

						} elseif ("flextable" . $ft['recordid'] == $table) { // It's me!

							if ($ft['orientation'] == "many_entities_to_one") {
						
								$refs = db_GetArray("SELECT id, tabletype FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE fieldtype='Reference to FlexTable' AND options='" . str_replace("flextable", "", $table) . "'");

								foreach ($refs AS $ref) {
									if (is_numeric($ref[1])) $ref[1] = "flextable" . $ref[1];
									$sql = "SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . $ref[1] . " WHERE EFID" . $ref[0] . "='" . $record . "'";
									$refrec += db_GetValue($sql);
									
								}
							} else {
								$refrec = "";
								if ($ft['refers_to'] == "customer") {
									$ref_ins = $row['refer'] . ": " . GetCustomerName($row["refer"]);
								} elseif ($ft['refers_to'] == "entity") {
									$ref_ins = $row['refer'] . ": " . GetEntityCategory($row["refer"]);
								} else {
									$ref_ins = $row['refer'] . ": " . GetParsedFlexRef(str_replace("flextable", "", $table), $row["refer"], "");
								}
							}
						} 
					}

					print "<td>" . $refrec . $ref_ins . "</td>";

					$tcp = "";
					foreach ($mem['compareFields'] AS $cf) {
						$tcp .= $row[$cf] . " ";
					}
					similar_text(strtolower($first), strtolower($tcp), $perc);
				
					print "<td>" . FormatNumber($perc) . " %</td>";
					print "<td><a onclick=\"$(document.getElementById('ROW" . $row[$id] . "')).remove();\">[exclude]</td>";
					if ($firstrow && $mem['AutoFeed']) {
						$ins = " checked=\"checked\"";
						$submitform = true;
					} else {
						$ins = "";
					}
					print "<td><input type=\"radio\" " . $ins . " name=\"Keep\" value=\"" . $row[$id] . "\" onclick=\"document.forms['dedupForm'].submit();\"></td>";
					print "</tr>";
				}


				print "</table>";
			} else {
				print "Done.";
				if ($mem['DryRun']) {
					print " This was a dry run; no changes were made. The generated queries are printed below.<br>";
					print "<textarea rows='10' cols='100'>";
					foreach ($mem['queries'] AS $q) {
						print htme($q) . "\n";
					}
					print "</textarea>";
					if (isset($_REQUEST['Snd'])) {
						$body = "";
						foreach ($mem['queries'] AS $q) {
							$body .= ($q) . "\n";
						}
						AddMessage("admin", $GLOBALS['USERID'], "Deduplication result.", $body);
						AddMessage($GLOBALS['USERID'], $GLOBALS['USERID'], "Deduplication result.", $body);

						print "<br><br>OK: administrator notified (message cc'd to " . GetUserName($GLOBALS['USERID']) . ")";
					} else {
						print "<br><br><a href='dedup.php?Worker=" . $worker . "&Snd'>Send this set of queries to your Interleave administrator.</a>";
					}
				}
			}
		}
		

		
		
	} else {
		$mem['table'] = $_REQUEST['tableId'];
		if ($mem['table'] && $mem['table'] != "unknown" && !CheckIfUserIsAllowedTodedupInTable($mem['table'])) {
			
			PrintAD("You're not allowed to use this functionality");
			$error = true;

		} else {
			$deffields = array();
			print "<h1>Deduplicate ";
			if (is_numeric($mem['table'])) {
				print "&quot;" . GetFlextableName($mem['table']) . "&quot;";
			} else {
				print "&quot;" . $lang[$mem['table']] . "&quot; table";
			}
			print "</h1>";
			if ($mem['table'] == "entity") {
				$deffields = array("category" => $lang['category'], "status" => $lang['status'], "priority" => $lang['priority'], "duedate" => $lang['duedate'], "startdate" => $lang['startdate'], "owner" => $lang['owner'], "assignee" => $lang['assignee'], "content" => "Main text contents");
				$list = GetExtraFields();
			} elseif ($mem['table'] == "customer") {
				$deffields = array("custname" => $lang['customer'], "contact" => $lang['contact'], "contact_title" => $lang['contacttitle'], "contact_phone" => $lang['contactphone'], "contact_email" => $lang['contactemail'], "cust_address" => $lang['customeraddress'], "cust_remarks" => $lang['custremarks'], "cust_homepage" => $lang['custhomepage'], "active" => "active");
				$list = GetExtraCustomerFields();
			} else {
				$list = GetExtraFlexTableFields($mem['table'], false, false, true);
			}
			
			

			print "<h2>Select field or a combination of fields to compare on</h2><div class=\"scrolldiv\" style=\"width: 400px; border: 1px solid #808080;\">";
			foreach ($deffields AS $field => $tag) {
				print "<input type=\"checkbox\" name=\"fieldName[]\" value=\"" . htme($field) . "\">" . htme($tag) . "<br>";
			}
			foreach ($list AS $ef) {
				print "<input type=\"checkbox\" name=\"fieldName[]\" value=\"EFID" . htme($ef['id']) . "\">" . htme($ef['name']) . "<br>";
			}
			print "</div>";

			print "<br>...and show fields with <input type=\"text\" name=\"percentage\" value=\"95\" size=\"2\"> % match and up.<br><br>Method : <select name=\"Enrich\"><option value=\"yes\">ask me every time and try to add missing data to the record which is kept</option><option value=\"yes-confirm\">ask me every time and try to add missing data to the record which is kept after confirmation from me</option><option value=\"no\">ask me every time and just delete duplicate records</option><option value=\"yes-auto-dry\">process automatically, keep oldest record, and try to add missing data to the record which is kept but don't make any changes (dry run)</option><option value=\"no-auto-dry\">process automatically, keep oldest record, and just delete the duplicates but don't make any changes  (dry run)</option><option value=\"yes-auto\">process automatically, keep oldest record, and try to add missing data to the record which is kept</option><option value=\"no-auto\">process automatically, keep oldest record, and just delete the duplicates</option></select>.<br>";
			
			
			$LastRecord = GetAttribute("user", "LastRecordDedup" . $mem['table'], $GLOBALS['USERID']);
			if ($LastRecord > 0) {
				print "<br><br><input type=\"submit\" name=\"restoresession\" value=\"Restore last session; start at row " . $LastRecord . "\"><br><br>";
			}
		}
			
	}
}


if (!$error && !$submitform && !$nobutton) {
	print "<input type=\"submit\" name=\"submitButton\" value=\"" . $lang['go'] . "\">";
}

print "<input type=\"hidden\" id=\"JS_Worker\" name=\"Worker\" value=\"" . $worker . "\">";

print "</form>";

UpdateStashvalue($worker, $mem);
if ($debug) DA($mem);

if ($submitform) {
	?>
	<script type="text/javascript">
	<!--
		document.forms['dedupForm'].submit();
	//-->
	</script>
	<?php
}

EndHTML();

function CheckIfUserIsAllowedTodedupInTable($tableid) {
	if (is_administrator()) {
		return true;
	} elseif ($tableid == "entity" && CheckFunctionAccess("AllowedTodedupEntities") == "ok") {
		return(true);
	} elseif ($tableid == "customer" && CheckFunctionAccess("AllowedTodedupCustomers") == "ok") {
		return(true);
	} elseif ($tableid == "users" && CheckFunctionAccess("AllowedTodedupUsers") == "ok") {
		return(true);
	} elseif (is_numeric($tableid) &&CheckFunctionAccess("AllowedTodedupFT" . $tableid) == "ok") {
		return(true);
	} else {
		return(false);
	}
}
?>