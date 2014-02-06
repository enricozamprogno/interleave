<?php

if (empty($_SERVER['SHELL']) && !substr($argv[2], 0, 4) == "cfg=") {
	die('shells only please');
}

$GLOBALS['TBL_PREFIX'] = "CRM";


//ini_set("error_reporting", "E_ALL");
$createprintconfig = false;
$source = array();
$target = array();

if (is_array($argv)) {
	foreach ($argv AS $ar) {
		
		
		if (substr($ar, 0, 4) == "Sdb=") {
			$source['db'] = str_replace("Sdb=", "", $ar);
		}
		if (substr($ar, 0, 6) == "Shost=") {
			$source['host'] = str_replace("Shost=", "", $ar);
		}
		if (substr($ar, 0, 6) == "Suser=") {
			$source['user']= str_replace("Suser=", "", $ar);
		}
		if (substr($ar, 0, 6) == "Spass=") {
			$source['pass'] = str_replace("Spass=", "", $ar);
		}
		
		
		if (substr($ar, 0, 4) == "Tdb=") {
			$target['db'] = str_replace("Tdb=", "", $ar);
		}
		if (substr($ar, 0, 6) == "Thost=") {
			$target['host'] = str_replace("Thost=", "", $ar);
		}
		if (substr($ar, 0, 6) == "Tuser=") {
			$target['user']= str_replace("Tuser=", "", $ar);
		}
		if (substr($ar, 0, 6) == "Tpass=") {
			$target['pass'] = str_replace("Tpass=", "", $ar);
		}
		

	}
}


if ($source['host'] == "" || $source['user'] == "" || $source['pass'] == "" || $source['db'] == "") {
	print "Usage: dbsettingsdiff.pgp Sdb=db_name Shost=db_host Suser=db_user Spass=db_pass {Tdb=db_name Thost=db_host Tuser=db_user Tpass=db_pass}\n";
	exit(1);
} else {

	require_once("functions.php");
	
	if ($target['host'] != "" || $target['user'] != "" || $target['pass'] != "" || $target['db'] != "" ) {
		print "# " . date('r') . " Initiate connection to production database " . "mysql://" . $target['host'] . "/" . $target['db'] . "\n";
		if (!$db = mysql_connect($target['host'], $target['user'], $target['pass'])) {
			print "# " . date('r') . " Connection to mysql://" . $target['host'] . "/" . $target['db'] . " failed. (connect)\n";
			exit(1);
		} else {
			if (!@mysql_select_db($target['db'], $db)) {
				print "# " . date('r') . " Connection to mysql://" . $target['host'] . "/" . $target['db'] . " failed. (select)\n";
				exit(1);
			} else {
				mcq("SET NAMES UTF8", $db);
				// Always run this. 
				$GLOBALS['DBVERSION'] = GetSetting("DBVERSION");
				if ($GLOBALS['DBVERSION'] > "5.5.0.4") IntermediateDatabaseUpgrade();

				$existingprint = CreateFingerPrint();
				print "# " . date('r') . " Production fingerprint created (roughly " . strlen(serialize($existingprint)) . " bytes)\n";
			}
			
		}
	} else {
		$existingprint = "";
	}

	
	
	

	if (!$db = mysql_connect($source['host'], $source['user'], $source['pass'])) {
		print "# " . date('r') . " Connection to mysql://" . $source['host'] . "/" . $source['db'] . " failed. (connect)\n";
		exit(1);	
	} else {
		if (!@mysql_select_db($source['db'], $db)) {
			print "# " . date('r') . " Connection to mysql://" . $source['host'] . "/" . $source['db'] . " failed. (select)\n";
			exit(1);
		} else {
			
			$GLOBALS['title'] = GetSetting("TITLE");
			mcq("SET NAMES UTF8", $db);

			// Always run this. 
			$GLOBALS['DBVERSION'] = GetSetting("DBVERSION");
			if ($GLOBALS['DBVERSION'] > "5.5.0.4") IntermediateDatabaseUpgrade();

			print "# " . date('r') . " Connected to " . $GLOBALS['title'] . "\n";
		

			
			print "# " . date('r') . " Creating master fingerprint of database " . "mysql://" . $source['host'] . "/" . $source['db'] . "\n";
			$curprint = CreateFingerPrint();
			
			print "# " . date('r') . " Master fingerprint created (roughly " . strlen(serialize($curprint)) . " bytes)\n";
			
			if ($existingprint == "") {
				print serialize($curprint);
			} else {
				$queries = CompareFingerPrints($curprint, $existingprint);
				$some = false;
				$tp = "";
				$stop = false;
				foreach ($queries AS $q) {
					$tp .= $q . ";\n";
					$some = true;
					if (strstr($q, "WHERE setting='DBVERSION'")) {
						//$stop = true;
						//print "# " . date('r') . " Cannot compare because versions are not equal.\n";
						print "# " . date('r') . " WARNING :: Versions are not equal! Are you sure you want this?.\n";


					} 
				}
			
				if ($some && !$stop) {
					print $tp;
					print "TRUNCATE TABLE " . $GLOBALS['TBL_PREFIX'] . "cache;\n";
					print "TRUNCATE TABLE " . $GLOBALS['TBL_PREFIX'] . "accesscache;\n";
					print "TRUNCATE TABLE " . $GLOBALS['TBL_PREFIX'] . "entityformcache;\n";
					print "TRUNCATE TABLE " . $GLOBALS['TBL_PREFIX'] . "publishedpagescache;\n";
					print "TRUNCATE TABLE " . $GLOBALS['TBL_PREFIX'] . "entitylocks;\n";
				}
			}
		}
	}
}

function CompareFingerPrints($cp, $ep) {

	// Definition of ID's to ignore (mostly settings)

	$ignore = array();

	$ignore[$GLOBALS['TBL_PREFIX'] . "settings"] = array();
	$ignore[$GLOBALS['TBL_PREFIX'] . "settings"][] = "title";
	$ignore[$GLOBALS['TBL_PREFIX'] . "settings"][] = "DBVERSION";
	$ignore[$GLOBALS['TBL_PREFIX'] . "settings"][] = "BASEURL";
	$ignore[$GLOBALS['TBL_PREFIX'] . "settings"][] = "SUBTITLE";
	$ignore[$GLOBALS['TBL_PREFIX'] . "settings"][] = "TimestampLastHousekeeping";
	$ignore[$GLOBALS['TBL_PREFIX'] . "settings"][] = "TimestampLastDuedateCron";
	$ignore[$GLOBALS['TBL_PREFIX'] . "settings"][] = "LastIncrementalSync";
	$ignore[$GLOBALS['TBL_PREFIX'] . "settings"][] = "BODY_URGENTMESSAGE";
	$ignore[$GLOBALS['TBL_PREFIX'] . "settings"][] = "Logon message";
	$ignore[$GLOBALS['TBL_PREFIX'] . "settings"][] = "SAFE_MODE";
	$ignore[$GLOBALS['TBL_PREFIX'] . "settings"][] = "admemail";
	$ignore[$GLOBALS['TBL_PREFIX'] . "settings"][] = "SYNC_DISABLED_UNTIL";
	$ignore[$GLOBALS['TBL_PREFIX'] . "settings"][] = "LASTLETTERNUMBER";	
	$ignore[$GLOBALS['TBL_PREFIX'] . "settings"][] = "SMTPserver";
	$ignore[$GLOBALS['TBL_PREFIX'] . "settings"][] = "ShowMinimalErrorMessages";

	// Only if editing of system wide selections by users is allowed
	$cnt = db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE CLLEVEL LIKE '%AllowedToEditSystemWideSelections%'");
	$cnt += db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "userprofiles WHERE CLLEVEL LIKE '%AllowedToEditSystemWideSelections%'");
	if ($cnt > 0) {
		print "# " . date('r') . " WARNING :: Skipping system wide selections because changing them is allowed by $cnt users or groups.\n";
		$ignore[$GLOBALS['TBL_PREFIX'] . "attributes"][] = "1systemSavedCustomerListSelections";
	}





	$adds = 0;
	$updates = 0;
	$deletes = 0;
	$tables = Tables();
	// check count
	$queries = array();
	$firstqueries = array();
	$dontaltertable = array();

	foreach ($tables AS $table) {	
//		print $table[0] . "\n\n";
		$fields = array();
		$qu = "";
		$first_done = false;
		$sql = "EXPLAIN " . $table[0];
		$res = mcq($sql, $db);
		while ($row2 = mysql_fetch_array($res)) {
			if (!$first_done) {
				$first_done = true;
			} else {
				$qu .= ",";
			}
			$qu .= "`" . $row2['Field'] . "`";
			array_push($fields, $row2['Field']);
		}
		if (is_array($cp[$table[0]]['hash'])) {
			$localids = array();



			foreach ($cp[$table[0]]['hash'] AS $id => $ephash) { // cycle through id's in table
					$localids[] = $id;

					$str = "";
					$first_done = "";
					$nomatch = false;

					$updatefields = array();
		
					$currow_array_values = unserialize($ephash);
					$oldrow_array_values = unserialize($ep[$table[0]]['hash'][$id]);

					foreach ($currow_array_values AS $key => $value) {
						if ($oldrow_array_values[$key] != $value) {
							$nomatch = true;
							if (!is_numeric($key)) {
								array_push($updatefields, $key);
							}

						}

					}

					if (!@array_key_exists($id, $ep[$table[0]]['hash'])) {
						//print "# " . date('r') . " Record " . $id . " exists in new but not in old table\n";
						$currow = db_GetRow("SELECT * FROM " . $table[0] . " WHERE " . $table[1] . "='" . mres($id) . "'");
						

						$str = "INSERT INTO " . $table[0] . "(" . $qu . ") VALUES (";
						$first_done=false;
						foreach($fields AS $field) {
							if (!$first_done) {
								$first_done = true;
							} else {
								$str .= ", ";
							}
							$str .= "'" . mres($currow[$field]) . "'";
						}
						$str .= ")";
						if ($first_done) {
							array_push($queries, $str);
							$adds++;
						
						
						}



						if (substr($table[0], 3, 13) == "flextabledefs" && $first_done) {
							if (GetAttribute("flextable", "ViewOnTable", $currow['recordid']) != "") {
								array_push($firstqueries, "CREATE VIEW " . $GLOBALS['TBL_PREFIX'] . "flextable" . $currow['recordid'] . " AS SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . GetAttribute("flextable", "ViewOnTable", $currow['recordid']) . "");
							} elseif (GetAttribute("flextable", "ViewOnTable", $currow['recordid']) == "") {
								$tc = db_GetArray("SHOW CREATE TABLE " . $GLOBALS['TBL_PREFIX'] . "flextable" . $currow['recordid'] . "");
								array_push($firstqueries, str_replace("\n", "", $tc[0][1]));
								array_push($dontaltertable, $currow['recordid']);
								
							}
						}

						if (substr($table[0], 3, 11) == "extrafields" && $first_done && !$table_create && !in_array($currow['tabletype'], $dontaltertable)) {

							$new_field_name = $currow['id'];

							if (is_numeric($currow['tabletype'])) {

								$tableconcerning = $GLOBALS['TBL_PREFIX'] . "flextable" . $currow['tabletype'];
								$adds++;
								if ($currow['fieldtype'] == "date/time") {
									array_push($queries, "ALTER TABLE " . $tableconcerning . " ADD `EFID" . $new_field_name . "` DATETIME NOT NULL");
								} else {
									array_push($queries, "ALTER TABLE " . $tableconcerning . " ADD `EFID" . $new_field_name . "` LONGTEXT NOT NULL");
								}
								
							} elseif ($currow['tabletype'] == "customer") {

								$tableconcerning = $GLOBALS['TBL_PREFIX'] . "customer";
								$adds++;
								if ($currow['fieldtype'] == "date/time") {
									array_push($queries, "ALTER TABLE " . $tableconcerning . " ADD `EFID" . $new_field_name . "` DATETIME NOT NULL");
								} else {
									array_push($queries, "ALTER TABLE " . $tableconcerning . " ADD `EFID" . $new_field_name . "` LONGTEXT NOT NULL");
								}

							} elseif ($currow['tabletype'] == "entity") {

								$tableconcerning = $GLOBALS['TBL_PREFIX'] . "entity";
								$adds++;
								if ($currow['fieldtype'] == "date/time") {
									array_push($queries, "ALTER TABLE " . $tableconcerning . " ADD `EFID" . $new_field_name . "` DATETIME NOT NULL");
								} else {
									array_push($queries, "ALTER TABLE " . $tableconcerning . " ADD `EFID" . $new_field_name . "` LONGTEXT NOT NULL");
								}
							} elseif ($currow['tabletype'] == "group") {

								$tableconcerning = $GLOBALS['TBL_PREFIX'] . "userprofiles";
								$adds++;
								if ($currow['fieldtype'] == "date/time") {
									array_push($queries, "ALTER TABLE " . $tableconcerning . " ADD `EFID" . $new_field_name . "` DATETIME NOT NULL");
								} else {
									array_push($queries, "ALTER TABLE " . $tableconcerning . " ADD `EFID" . $new_field_name . "` LONGTEXT NOT NULL");
								}
							} elseif ($currow['tabletype'] == "user") {

								$tableconcerning = $GLOBALS['TBL_PREFIX'] . "loginusers";
								$adds++;
								if ($currow['fieldtype'] == "date/time") {
									array_push($queries, "ALTER TABLE " . $tableconcerning . " ADD `EFID" . $new_field_name . "` DATETIME NOT NULL");
								} else {
									array_push($queries, "ALTER TABLE " . $tableconcerning . " ADD `EFID" . $new_field_name . "` LONGTEXT NOT NULL");
								}

							}
							

						}
						

					} elseif ($nomatch) {
						
						if ($id != "") {
					
							$currow = db_GetRow("SELECT * FROM " . $table[0] . " WHERE " . $table[1] . "='" . mres($id) . "'");

							$str = "UPDATE " . $table[0] . " SET ";
							$first_done=false;
							$change_type = false;
							foreach($updatefields AS $field) {
								if ($field != "timestamp_last_change" && $field != "module_last_run_date" && $field != "module_last_run_result") {

									if ($field == "options" && substr($table[0], 3, 11) == "extrafields" && db_GetValue("SELECT allowuserstoaddoptions FROM " . $table[0] . " WHERE " . $table[1] . "='" . mres($id) . "'") == 'y') {
										print "# " . date('r') . " WARNING :: Skipping options of field " . $id . " (" . GetExtraFieldName($id) . ") because it can contain user-added options\n";
									} else {
										if (!$first_done) {
											$first_done = true;
										} else {
											$str .= ", ";
										}
										$str .= "`" . $field . "`='" . mres($currow[$field]) . "'";

										if ($field == "fieldtype" || $field == "options") {
											$change_type = true;
										}
									}
								}
							
							}
							$str .= " WHERE " . $table[1] . "='" . mres($id) . "'";
							if ($first_done) {
								if (is_array($ignore[$table[0]]) && in_array($id, $ignore[$table[0]])) {
									//$str = "# {{AUTO-IGNORE}} " . $str;
									$str = "";
								} else {
									array_push($queries, $str);
									$updates++;
								}
								
							}
							if (substr($table[0], 3, 11) == "extrafields" && $first_done && $change_type) {

								$new_field_name = $currow['id'];

								if (is_numeric($currow['tabletype'])) {

									$tableconcerning = $GLOBALS['TBL_PREFIX'] . "flextable" . $currow['tabletype'];
									$updates++;
									if ($currow['fieldtype'] == "date/time") {
										array_push($queries, "ALTER TABLE " . $tableconcerning . " CHANGE `EFID" . $new_field_name . "` `EFID" . $new_field_name . "` DATETIME NOT NULL");
									} else {
										array_push($queries, "ALTER TABLE " . $tableconcerning . " CHANGE `EFID" . $new_field_name . "` `EFID" . $new_field_name . "` LONGTEXT NOT NULL");
									}
									
								} elseif ($currow['tabletype'] == "customer") {

									$tableconcerning = $GLOBALS['TBL_PREFIX'] . "customer";
									$updates++;
									if ($currow['fieldtype'] == "date/time") {
										array_push($queries, "ALTER TABLE " . $tableconcerning . " CHANGE `EFID" . $new_field_name . "` `EFID" . $new_field_name . "` DATETIME NOT NULL");
									} else {
										array_push($queries, "ALTER TABLE " . $tableconcerning . " CHANGE `EFID" . $new_field_name . "` `EFID" . $new_field_name . "` LONGTEXT NOT NULL");
									}

								} elseif ($currow['tabletype'] == "entity") {

									$tableconcerning = $GLOBALS['TBL_PREFIX'] . "entity";
									$updates++;
									if ($currow['fieldtype'] == "date/time") {
										array_push($queries, "ALTER TABLE " . $tableconcerning . " CHANGE `EFID" . $new_field_name . "` `EFID" . $new_field_name . "` DATETIME NOT NULL");
									} else {
										array_push($queries, "ALTER TABLE " . $tableconcerning . " CHANGE `EFID" . $new_field_name . "` `EFID" . $new_field_name . "` LONGTEXT NOT NULL");
									}
								} elseif ($currow['tabletype'] == "user") {

									$tableconcerning = $GLOBALS['TBL_PREFIX'] . "loginusers";
									$updates++;
									if ($currow['fieldtype'] == "date/time") {
										array_push($queries, "ALTER TABLE " . $tableconcerning . " CHANGE `EFID" . $new_field_name . "` `EFID" . $new_field_name . "` DATETIME NOT NULL");
									} else {
										array_push($queries, "ALTER TABLE " . $tableconcerning . " CHANGE `EFID" . $new_field_name . "` `EFID" . $new_field_name . "` LONGTEXT NOT NULL");
									}
								} elseif ($currow['tabletype'] == "group") {

									$tableconcerning = $GLOBALS['TBL_PREFIX'] . "userprofiles";
									$updates++;
									if ($currow['fieldtype'] == "date/time") {
										array_push($queries, "ALTER TABLE " . $tableconcerning . " CHANGE `EFID" . $new_field_name . "` `EFID" . $new_field_name . "` DATETIME NOT NULL");
									} else {
										array_push($queries, "ALTER TABLE " . $tableconcerning . " CHANGE `EFID" . $new_field_name . "` `EFID" . $new_field_name . "` LONGTEXT NOT NULL");
									}
								} else {
									print "# " . $currow['tabletype'];
								}
							}
							

						//	print "# " . date('r') . " Record " . $id . " exists in new AND in old table but is NOT EQUAL\n";
						} else {
						//	print "# " . date('r') . " Record " . $id . " exists in new AND in old table and they are the same\015";
						}
					}



				}

				if (is_array($ep[$table[0]]['hash'])) {
					foreach ($ep[$table[0]]['hash'] AS $id => $ephash) { // cycle through id's in table the other way around
							
							$nf = false;
							if (!@array_key_exists($id, $cp[$table[0]]['hash'])) {
								$sql = "DELETE FROM " . $table[0] . " WHERE " . $table[1] . " = '" . mres($id) . "'";
								$queries[] = $sql;
								$deletes++;
							}

					}
				}
			} else {// end if is_array
				print "# " . date('r') . " WARNING :: table ". $table[0] . " not found in database (probably empty)\n";
			}





	}

	foreach ($dontaltertable AS $table) {
		for ($i=0;$i<count($queries);$i++) {
			if (substr($queries[$i], 0, (24 + strlen($table))) == "ALTER TABLE CRMflextable" . $table) {
				$queries[$i] = "# " . $queries[$i];
			}
		}
		
	}


	print "# " . date('r') . " Additions: " . $adds . ", updates: " . $updates . ", deletes: " . $deletes . "\n\n";
	if ($deletes > 0 || $updates > 0 || $adds > 0)  {
		print "SET NAMES UTF8;\n";
	}
	return(array_merge($firstqueries, $queries));
}

function LoadFingerPrint($file_location) {	
	return(file_get_contents($file_location));
}

function CreateFingerPrint() {

	$tables = Tables();

	foreach ($tables AS $table) {
		$arr[$table[0]] = GetFingerprint($table[0], $table[1], $table[2]);
	}
	
	return($arr);
}


function GetFingerprint($table, $id) {
	$return = array();

	if (substr($table, 3, 10) == "attributes") {
		$sql_ins = " WHERE identifier != 'user' AND attribute NOT LIKE '%RunCount' AND attribute NOT LIKE '%UseCount' AND attribute NOT LIKE 'SaveAction%'";
		$extra_ins = ",CONCAT(entity,identifier,attribute) ";
	}
	
	$c1 = db_GetRow("SELECT COUNT(*) FROM " . $table . $sql_ins);
	$return['count'] = $c1[0];
	
	

	$fp1 = db_GetArray("SELECT *" . $extra_ins . " FROM " . $table . $sql_ins);
	foreach ($fp1 AS $row) {
		$tohash = "";

//		foreach ($row AS $field) {
//			$tohash .= $field;
//		}
		$return['hash'][$row[$id]] = serialize($row);
	}
	return($return);
}
function Tables() {
	$tables = array();

	array_push($tables, array($GLOBALS['TBL_PREFIX'] . "extrafieldconditions","conid", ""));
	array_push($tables, array($GLOBALS['TBL_PREFIX'] . "extrafieldrequiredconditions","conid", ""));
	array_push($tables, array($GLOBALS['TBL_PREFIX'] . "extrafields","id", ""));
	array_push($tables, array($GLOBALS['TBL_PREFIX'] . "flextabledefs","recordid", "recordid"));
	array_push($tables, array($GLOBALS['TBL_PREFIX'] . "languages","id", ""));
	array_push($tables, array($GLOBALS['TBL_PREFIX'] . "modules","mid", ""));
	array_push($tables, array($GLOBALS['TBL_PREFIX'] . "priorityvars","id", ""));
	array_push($tables, array($GLOBALS['TBL_PREFIX'] . "publishedpages","id", ""));
	array_push($tables, array($GLOBALS['TBL_PREFIX'] . "settings","setting", ""));
	array_push($tables, array($GLOBALS['TBL_PREFIX'] . "statusvars","id", ""));
	array_push($tables, array($GLOBALS['TBL_PREFIX'] . "tabmenudefinitions","id", ""));
	array_push($tables, array($GLOBALS['TBL_PREFIX'] . "triggerconditions","conid", ""));
	array_push($tables, array($GLOBALS['TBL_PREFIX'] . "triggers","tid", ""));
	array_push($tables, array($GLOBALS['TBL_PREFIX'] . "userprofiles","id", ""));
	array_push($tables, array($GLOBALS['TBL_PREFIX'] . "templates","templateid", ""));
	array_push($tables, array($GLOBALS['TBL_PREFIX'] . "attributes","CONCAT(entity,identifier,attribute)", ""));
//	array_push($tables, array($GLOBALS['TBL_PREFIX'] . "configsnapshots","id", ""));	
	return($tables);
}
?>