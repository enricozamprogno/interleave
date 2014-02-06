<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This script takes care of the administration and publication
 * of pages which are published unsecure.
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */


if ($_REQUEST['fID'] && $_REQUEST['FrDb']) {
	$GLOBALS['PUBLISHING'] = true;
	//	require_once("initiate.php");
	if ($GLOBALS['CONFIGFILE'] == "") {
		$GLOBALS['CONFIGFILE'] = $GLOBALS['PATHTOINTERLEAVE'] . "config/config.inc.php";
	}
	unset($_COOKIE);
	require($GLOBALS['CONFIGFILE']);
	require_once($GLOBALS['PATHTOINTERLEAVE'] . "functions.php");

	qlog(INFO, "SetUID: " . GetUserName($row['as_user']));

	if (SwitchToRepos(str_replace("A","",($_REQUEST['FrDb']))/142324)) {
		$row = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "publishedpages WHERE id=" . ($_REQUEST['fID']/667344));

//		PrintHTMLHeader();
		PrintHeaderJavascript();
		print $GLOBALS['doctype'];
		$u = $row['as_user'];
		$GLOBALS['USERID'] = $row['as_user'];
		$_REQUEST['nonavbar'] = true;
		InitUser();
		PublishForm($_REQUEST['fID']/667344);
//		EndHTML();
	} else {
		print "Connection error";
	}

	exit;
} elseif ($_REQUEST['FrDb'] && $_REQUEST['pID']) {
	$GLOBALS['PUBLISHING'] = true;
	//require_once("initiate.php");


	if ($GLOBALS['CONFIGFILE'] == "") {
		$GLOBALS['CONFIGFILE'] = $GLOBALS['PATHTOINTERLEAVE'] . "config/config.inc.php";
	}
	
	require($GLOBALS['CONFIGFILE']);
	require_once($GLOBALS['PATHTOINTERLEAVE'] . "functions.php");
	require_once($GLOBALS['PATHTOINTERLEAVE'] . "config/config-vars.php");
	$GLOBALS['starttime'] = microtime_float();

	if (SwitchToRepos(str_replace("A","",($_REQUEST['FrDb']))/142324)) {

		qlog(INFO, "Running externally unsecured published page");
	//	log_msg("Running externally unsecured published page");

		$row = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "publishedpages WHERE id=" . ($_REQUEST['pID']/667344));
		$u = $row['as_user'];

		$GLOBALS['USERID'] = $u;
		InitUser();


		qlog(INFO, "SetUID: " . GetUserName($row['as_user']));

		if ($row['type'] == "page") {

			if (is_numeric($_REQUEST['eID'])) {

				qlog(INFO, "Publishing page \"" . GetEntityCategory($_REQUEST['eID']) . "\" for customer \"" . GetCustomerName(GetEntityCustomer($_REQUEST['eID'])) . "\" (page desc: " . $row['description'] . ")");

				$cache = db_GetRow("SELECT content FROM " . $GLOBALS['TBL_PREFIX'] . "publishedpagescache WHERE eid=" . $_REQUEST['eID'] . " AND formid=" . $row['template'] . " AND pageid=" . $_REQUEST['pID']/667344 . " AND userid=" . $u);

				if ($cache['content'] && !$_REQUEST['AttID']) {
					journal($_REQUEST['eID'], "This entity page was picked from cache and published to " . $_SERVER['REMOTE_ADDR']);
					print $cache['content'];
					log_msg("PP:" . $row['id'] . ":C");
					qlog(INFO, "CACHE: Published entity " . $_REQUEST['eID'] . " using template " . $row['template']);
				} else {

					if (CheckEntityAccess($_REQUEST['eID']) == "ok" || CheckEntityAccess($_REQUEST['eID']) == "readonly") {
						if (!$_REQUEST['AttID']) {
							journal($_REQUEST['eID'], "This entity was parsed using template " . $row['template'] . " and published to " . $_SERVER['REMOTE_ADDR']);
							$outp = ParseTemplateEntity(GetTemplate($row['template']), $_REQUEST['eID']);
							$outp = ParseTemplateCustomer($outp, GetEntityCustomer($_REQUEST['eID']));
							$outp = ParseTemplateGeneric($outp);
							mcq("INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "publishedpagescache(eid,formid,pageid,content,userid) VALUES(" . $_REQUEST['eID'] . "," . $row['template'] . "," . ($_REQUEST['pID']/667344) . ",'" . mres($outp) . "'," . $u .")", $db);
							print html_compress($outp);
							log_msg("PP:" . $row['id'] . ":P");
							qlog(INFO, "Published entity " . $_REQUEST['eID'] . " using template " . $row['template'] . " (parsed)");
						} else { // Attachment publishing
							journal($_REQUEST['eID'], "This entity was parsed using template " . $row['template'] . " and published to " . $_SERVER['REMOTE_ADDR']);
							log_msg("PP:" . $row['id'] . ":P");
							qlog(INFO, "Published attachment " . $_REQUEST['AttID'] . " from entity " . $_REQUEST['eID'] . "");
							$list = GetFileListArray($_REQUEST['eID']);
							$ok = false;
							foreach ($list AS $filearray) {
								if ($filearray['fileid'] == $_REQUEST['AttID']) {
									$ok = true;
								}
							}
							if ($ok) {
								header("Content-Type: " . $list['filetype']);
								header("Content-Disposition: inline; filename=" . $list['filename']);
								print GetFileContent($_REQUEST['AttID']);
							} else {
								print "Error:: file " . $_REQUEST['AttID'] . " doesn't belong to entity " . $_REQUEST['eID'];
								qlog(ERROR, "ERROR:: file " . $_REQUEST['AttID'] . " doesn't belong to entity " . $_REQUEST['eID']);
							}
						}
					}
				}
				EndHTML(false);
				exit;
			} else {
				qlog(INFO, "Publisher called with non-numeric entity id!");
				print "Publisher called with non-numeric entity id!";
			}
		} elseif ($row['type'] == "report") {

			$template = GetTemplate($row['template']);
			$done = array();
//			print $row['report_query'];
			$cache_code = md5($row['report_query']);
			$cache = db_GetRow("SELECT content FROM " . $GLOBALS['TBL_PREFIX'] . "publishedpagescache WHERE reportmd5='" . $cache_code . "'");
			if (!$cache) {
				$res = mcq($row['report_query'], $db);
				while ($row2 = mysql_fetch_array($res)) {
					// Process each found eid

						$cache = db_GetRow("SELECT content FROM " . $GLOBALS['TBL_PREFIX'] . "publishedpagescache WHERE eid=" . $row2['eid'] . " AND formid=" . $row['template'] . " AND pageid=" . $_REQUEST['pID']/667344 . " AND userid=" . $u);

						qlog(INFO, "Publishing report \"" . GetEntityCategory($row2['eid']) . "\" for customer \"" . GetCustomerName(GetEntityCustomer($row2['eid'])) . "\" (report desc: " . $row['description'] . ")");

						if ($cache['content']) {
							$result_to_print .= $cache['content'];
							log_msg("PR:" . $row['id'] . ":C");
							journal($row2['eid'], "This entity page was picked from cache and published to " . $_SERVER['REMOTE_ADDR'] . " (as part of a report)");
							qlog(INFO, "Published report using template " . $row['template'] . " (from cache)");
						} else {


							if (CheckEntityAccess($row2['eid']) != "nok") {

								$outp = ParseTemplateEntity($template, $row2['eid'], false, false, false, "htme");
								$outp = ParseTemplateCustomer($outp, GetEntityCustomer($row2['eid']), false, "htme");
								$outp = ParseTemplateGeneric($outp, "htme");
								journal($row2['eid'], "This entity was parsed using template " . $row['template'] . " and published to " . $_SERVER['REMOTE_ADDR'] . " (as part of a report)");
								$result_to_print .= $outp;
								mcq("INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "publishedpagescache(eid,formid,pageid,content,userid) VALUES(" . $row2['eid'] . "," . $row['template'] . "," . ($_REQUEST['pID']/667344) . ",'" . mres($outp) . "'," . $u .")", $db);
								unset($outp);
								log_msg("PR:" . $row['id'] . ":P");
								qlog(INFO, "Published report using template " . $row['template'] . " (parsed)");
							}
						}
				}

				qlog(INFO, "Flushing output ...");
				print html_compress($result_to_print);
				ob_flush();
				mcq("INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "publishedpagescache(content,reportmd5) VALUES('" . mres(html_compress($result_to_print)) . "','" . $cache_code . "')", $db);
				qlog(INFO, "Entire report put into cache (code: " . $cache_code . ")");
			} else {
				$result_to_print = $cache['content'];
				qlog(INFO, "Entire report came from cache (code: " . $cache_code . ")");
				qlog(INFO, "Flushing output ...");
				print $result_to_print;
				ob_flush();

			}

			exit;


		}

	} else {
		print "Interleave : UNABLE TO CONNECT TO DATABASE";
		exit;
	}

} else {

	require_once("initiate.php");

	ShowHeaders();

	AdminTabs("publish");
	MustBeAdmin();
	AddBreadCrum("Published pages");

	$subdir = str_replace("publish.php","",$_SERVER['SCRIPT_NAME']);

	if ($_SERVER['HTTPS']=="on") {
		$http = "https://";
	} else {
		$http = "http://";
	}

	$string = $http . $_SERVER['SERVER_NAME'] . $subdir;


/*
	mysql> explain CRM publishedpages;
	+---------------+--------------+------+-----+---------+----------------+
	| Field         | Type         | Null | Key | Default | Extra          |
	+---------------+--------------+------+-----+---------+----------------+
	| id            | int(11)      | NO   | PRI | NULL    | auto_increment |
	| repository    | smallint(6)  | NO   |     |         |                |
	| visible_from  | mediumint(9) | NO   |     |         |                |
	| visible_until | mediumint(9) | NO   |     |         |                |
	| as_user       | smallint(6)  | NO   |     |         |                |
	| description   | varchar(255) | YES  |     | NULL    |                |
	| template      | mediumint(9) | NO   |     |         |                |
	+---------------+--------------+------+-----+---------+----------------+
*/
	if (!$_REQUEST['ppnav']) {
		$_REQUEST['ppnav'] = "pages";
		$fs_title = "Published pages & forms";
	}
	if ($_REQUEST['EditPage']) {
		$_REQUEST['ppnav'] = "newpage";
		$fs_title = "Add/edit page or form";
	}
	if ($_REQUEST['Reports']) {
		$_REQUEST['ppnav'] = "reports";
		$fs_title = "Published reports";
	}
	if ($_REQUEST['EditReport']) {
		$_REQUEST['ppnav'] = "newreport";
		$fs_title = "Add/edit report";
	}
	if ($_REQUEST['Statistics']) {
		$_REQUEST['ppnav'] = "statistics";
		$fs_title = "Statistics";
	}

	$to_tabs = array("pages","newpage","reports", "newreport", "statistics");
	$tabbs["pages"] = array("publish.php?" => "Published pages &amp; forms");
	$tabbs["newpage"] = array("publish.php?EditPage=new" => "Add/edit page or form");
	$tabbs["reports"] = array("publish.php?Reports=1" => "Published reports");
	$tabbs["newreport"] = array("publish.php?EditReport=new" => "Add/edit report");
	$tabbs["statistics"] = array("publish.php?Statistics=True" => "Statistics");



	InterTabs($to_tabs, $tabbs, $_REQUEST['ppnav']);

	print "<h1>" . htme($fs_title) . "</h1>";

	if ($_REQUEST['Statistics'] == "True") {
		$sql = "SELECT qs, COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "uselog WHERE qs LIKE 'PR:%' OR qs LIKE 'PP:%' GROUP BY qs";
		$done = array();
		print "<strong>Statistics about published pages and reports</strong><br /><br />";
		print "<table class='sortable' width='100%;'>";
		print "<tr><td><strong>Type</strong></td><td><strong>Page/report description</strong></td><td><strong>Times served</strong></td><td><strong>Parse hits</strong></td><td><strong>Cache hits</strong></td><td>% cache</td></tr>";
		$res = mcq($sql, $db);
		while ($row = mysql_fetch_array($res)) {
				$tr = explode(":", $row[0]);
				if (!in_array($tr[1], $done)) {
					array_push($done, $tr[1]);

					if ($tr[0] == "PP") print "<tr><td>Published page</td><td>";
					if ($tr[0] == "PR") print "<tr><td>Published report</td><td>";

					$desc = db_GetRow("SELECT description FROM " . $GLOBALS['TBL_PREFIX'] . "publishedpages WHERE id='" . $tr[1] . "'");
					$cached = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "uselog WHERE qs='" . $tr[0] . ":" . $tr[1] . ":C'");
					$parsed = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "uselog WHERE qs='" . $tr[0] . ":" . $tr[1] . ":P'");
					print $desc[0];
					print "</td><td>" . ($cached[0] + $parsed[0]) . "</td><td>" . $parsed[0] . "</td><td>" . $cached[0] . "</td><td>";
					$total = $parsed[0] + $cached[0];
					$pc1 = ($total / 100);
					$bla = $cached[0] / $pc1;
					print round($bla) . "%</td>";
					print "</tr>";
				}
		}
		print "</table>";

		print "<table>";
		print "<tr><td colspan='6'><br /><strong>Statistics per entity</strong><br /><br />";
		print "<table class='sortable' width='100%;'>";
		print "<tr><td><strong>Entity</strong></td><td><strong>Entity category</strong></td><td><strong>Times served</strong></td><td><strong>Parse hits</strong></td><td><strong>Cache hits</strong></td><td>% cache</td></tr>";
		$sql = "select distinct(eid) from " . $GLOBALS['TBL_PREFIX'] . "journal WHERE message LIKE 'This entity was parsed using template%' ORDER BY eid";
		$res = mcq($sql, $db);
		while ($row = mysql_fetch_array($res)) {

			$parse = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "journal WHERE message LIKE 'This entity was parsed using template%' AND eid=" . $row['eid']);
			$cache = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "journal WHERE message LIKE 'This entity page was picked from cache and published %' AND eid=" . $row['eid']);

			print "<tr><td>" . $row['eid'] . "</td><td>" . htme(GetEntityCategory($row['eid'])) . "</td><td>" . ($parse[0]+$cache[0]) . "</td><td>" . $parse[0] . "</td><td>" . $cache[0] . "</td><td>";
			$total = $parse[0] + $cache[0];
			$pc1 = ($total / 100);
			$bla = $cache[0] / $pc1;
			print round($bla) . "%";

			print "</td></tr>";

		}

		print "</table>";
		print "<br />Legend:<ul><li><strong>Parse hit</strong>: a hit for which the template was actually parsed (and the result was cached)</li>";
		print "<li><strong>Cache hit</strong>: a hit for which the content was grabbed from cache (which is much, much faster)</li></ul>";

		print "</td></tr></table>";

	} elseif (!$_REQUEST['EditPage'] && !$_REQUEST['EditReport'] && !$_REQUEST['Reports']) {

		if ($_REQUEST['PP_Delete']) {
			mcq("DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "publishedpages WHERE id=" . $_REQUEST['PP_Delete'], $db);
			log_msg("Published page " . $_REQUEST['PP_Delete'] . " was deleted");
		}



		print "<p>In this section you can create pages which will be parsed based on an HTML-template which can <br />be published to your corporate intranet or to the internet. These pages will be shown read-only,<br />and no Interleave headers or log-in screens will be shown. This is particularely useful for publishing<br />information about entities to the public embedded in existing intra- or internet pages.<br /><br />";
		print "<strong>Important</strong>; pages you define here are templates! In other words; you define templates here which may <br />be seen by outside users. The content of the result is determined by the enitity id (EID) you submit with it. <br /><br />";
		print "To publish an entity-attachment, add &quot;&amp;AttID=[filenum]&quot; to the URL.<br /><br />";
		print "The 'run as user'-user must be able to open the enity at least read-only or it will not be shown.</p>";
		print "<table class='sortable'>";
		print "<tr><td>id</td><td>Description</td><td>Run as user</td><td>Type</td><td>Template</td><td>Web link</td><td></td><td></td></tr>";

		$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "publishedpages WHERE type='page' OR type='form' ORDER BY id DESC";
		$res = mcq($sql, $db);
		while ($row = mysql_fetch_array($res)) {

			if ($row['type'] == "page") {
				$url = "publish.php?FrDb=A" . (142324*$GLOBALS['repository']) . "&amp;pID=" . (667344*$row['id']) . "&amp;eID=[EID]";
			} elseif ($row['type'] == "form") {
				$url = "publish.php?FrDb=A" . (142324*$GLOBALS['repository']) . "&amp;fID=" . (667344*$row['id']) . "";
			}

			print "<tr onmouseout=\"style.background='#FBFDFF';\" onmouseover=\"style.background='#E9E9E9';\" style='cursor:pointer' onclick=\"document.location='publish.php?EditPage=" . $row['id'] . "';\"><td>" . $row['id'] . "</td><td>" . $row['description'] . "</td><td>" . htme(GetUserName($row['as_user']))  . "</td><td>" . $row['type'] . "</td><td>" . htme(GetTemplateName($row['template'])) . "</td><td>" . $url . "</td><td><a href='publish.php?PP_Delete=" . $row['id'] . "'><img src='images/delete.gif' alt='' /></a></td><td><a href='" . $string . $url . "' onclick=\"window.open(this.href); return false;\"><img src='images/fullscreen_maximize.gif' alt='' /></a></td></tr>";
		}
		print "</table>";


	} elseif ($_REQUEST['EditPage']) {

		if ($_REQUEST['SavePage']) {

			if (GetTemplateType($_REQUEST['PP_Template']) == "TEMPLATE_HTML") {
				$type = "page";
			} elseif (GetTemplateType($_REQUEST['PP_Template']) == "TEMPLATE_HTML_FORM") {
				$type = "form";
			}

			if ($_REQUEST['SavePage'] == "new") {
				// new page
				$sql = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "publishedpages(description, as_user, template,type) VALUES('" . $_REQUEST['PP_Description'] . "'," . $_REQUEST['PP_RunAsUser'] . ",'" . $_REQUEST['PP_Template'] . "','" . $type . "')";
				mcq($sql, $db);
				$_REQUEST['EditPage'] = mysql_insert_id();
				log_msg("Published page " . $_REQUEST['EditPage'] . " added.");
			} else {
				// existing page
				$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "publishedpages SET description='" . $_REQUEST['PP_Description'] . "', as_user=" . $_REQUEST['PP_RunAsUser'] . ", template=" . $_REQUEST['PP_Template'] . ", type='" . $type . "' WHERE id=" . $_REQUEST['SavePage'];
				mcq($sql, $db);
			}

		}

		print "<h2>Editing published page " . $_REQUEST['EditPage'] . "</h2>";
		print "<form id='EditOrNEwPublishedPage' method='post' action=''><div class='showinline'>";
		if ($_REQUEST['EditPage'] == "new") {
			print "<input type='hidden' name='EditPage' value='new' />";
			print "<input type='hidden' name='SavePage' value='new' />";
		} else {
			print "<input type='hidden' name='EditPage' value='" . $_REQUEST['EditPage'] . "' />";
			print "<input type='hidden' name='SavePage' value='" . $_REQUEST['EditPage'] . "' />";
		}

		print "<table class='nicetable'>";
		print "<tr><td>id</td><td>Description</td><td>Run as user</td><td>Template</td></tr>";
		if ($_REQUEST['EditPage'] <> "new") {
			$row = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "publishedpages WHERE id=" . $_REQUEST['EditPage']);
		} else {
			$row = "";
		}
		print "<tr><td>" . $_REQUEST['EditPage'] . "</td><td><input type='text' size='50' name='PP_Description' value='" . $row['description'] . "' /></td><td><select name='PP_RunAsUser'>";
		$t = GetUserList();
		foreach ($t AS $user_row) {
			if ($user_row['id'] == $row['as_user']) {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='" . $user_row['id'] . "'>" . htme(GetUserName($user_row['id'])) . "</option>";
		}
		print "</select></td>";
		print "<td><select name='PP_Template'>";
		print "<option value=''>--- HTML templates ---</option>";
		$sql = "SELECT templateid,templatename,templatetype FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype = 'TEMPLATE_HTML'";
		$result_to_print = mcq($sql,$db);
		while ($row_f = mysql_fetch_array($result_to_print)) {

			if ($row['template'] == $row_f['templateid']) {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}

			print "<option " . $ins . " value='" . $row_f['templateid'] . "'>" . htme($row_f['templatename']) . "</option>";
		}
		print "<option value=''>----- HTML forms -----</option>";
		$sql = "SELECT templateid,templatename,templatetype FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype = 'TEMPLATE_HTML_FORM'";
		$result_to_print = mcq($sql,$db);
		while ($row_f = mysql_fetch_array($result_to_print)) {

			if ($row['template'] == $row_f['templateid']) {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}

			print "<option " . $ins . " value='" . $row_f['templateid'] . "'>" . $row_f['templatename'] . "</option>";
		}
		print "</select></td></tr>";
		print "</table>";
		print "<br /><input type='submit' name='PP_SaveButton' value='Save' />";
		print "</div></form>";

	} elseif (!$_REQUEST['EditReport'] && $_REQUEST['Reports']) {

		if ($_REQUEST['PP_Delete']) {
			mcq("DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "publishedpages WHERE id=" . $_REQUEST['PP_Delete'] . " AND type='report'", $db);
			log_msg("Published page " . $_REQUEST['PP_Delete'] . " was deleted");
		}



		print "<p>In this section you can create reports which will be parsed based on an HTML-template which can <br />be published to your corporate intranet or to the internet. These pages will be shown read-only,<br />and no Interleave headers or log-in screens will be shown. This is particularely useful for publishing<br />information about entities to the public embedded in existing intra- or internet pages.<br /><br />";
		print "<strong>Important</strong>; reports you define here are templates! In other words; you define templates here which may <br />be seen by outside users. The content of the result is determined by the filter query.<br /><br />";
		print "The rights of the 'run as user'-user will eventually determine if an entity is shown. The HTML-reports can be<br />chosen from any of the installed 'plain HTML reports'.</p>";
		print "<table class='sortable'>";
		print "<tr><td>id</td><td>Description</td><td>Run as user</td><td>Template</td><td>Web link</td><td></td><td></td></tr>";

		$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "publishedpages WHERE type='report' ORDER BY id DESC";
		$res = mcq($sql, $db);
		while ($row = mysql_fetch_array($res)) {
			print "<tr onmouseout=\"style.background='#FBFDFF';\" onmouseover=\"style.background='#E9E9E9';\" style='cursor:pointer' onclick=\"document.location='publish.php?EditReport=" . $row['id'] . "';\"><td>" . $row['id'] . "</td><td>" . $row['description'] . "</td><td>" . GetUserName($row['as_user'])  . "</td><td>" . GetTemplateName($row['template']) . "</td><td>publish.php?FrDb=A" . (142324*$GLOBALS['repository']) . "&amp;pID=" . (667344*$row['id']) . "</td><td><a href='publish.php?PP_Delete=" . $row['id'] . "'><img src='images/delete.gif' alt='' /></a></td><td><a href='" . $string . "publish.php?FrDb=A" . (142324*$GLOBALS['repository']) . "&amp;pID=" . (667344*$row['id']) . "&amp;eID=[EID]' onclick=\"window.open(this.href); return false;\"><img src='images/fullscreen_maximize.gif' alt='' /></a></td></tr>";
		}
		print "</table>";


	} elseif ($_REQUEST['EditReport']) {

		if ($_REQUEST['SaveReport']) {

			if ($_REQUEST['SaveReport'] == "new") {
				// new page
				$sql = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "publishedpages(description, as_user, template, type, report_query) VALUES('" . $_REQUEST['PP_Description'] . "'," . $_REQUEST['PP_RunAsUser'] . "," . $_REQUEST['PP_Template'] . ",'report','" . mres(base64_decode($_REQUEST['PP_Query'])) . "')";
				mcq($sql, $db);
				$_REQUEST['EditReport'] = mysql_insert_id();
				log_msg("Published page " . $_REQUEST['EditReport'] . " added.");
			} else {
				// existing page
				$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "publishedpages SET description='" . $_REQUEST['PP_Description'] . "', as_user=" . $_REQUEST['PP_RunAsUser'] . ", template=" . $_REQUEST['PP_Template'] . ", `report_query`='" . mres(base64_decode($_REQUEST['PP_Query'])) . "' WHERE id=" . $_REQUEST['SaveReport'] . " AND `type`='report'";
				mcq($sql, $db);
			}

		}

		print "<h2>Editing published report " . $_REQUEST['EditReport'] . "</h2>";
		print "<form id='EditOrNEwPublishedReport' method='post' action=''><div class='showinline'>";
		if ($_REQUEST['EditReport'] == "new") {
			print "<input type='hidden' name='EditReport' value='new' />";
			print "<input type='hidden' name='SaveReport' value='new' />";
		} else {
			print "<input type='hidden' name='EditReport' value='" . $_REQUEST['EditReport'] . "' />";
			print "<input type='hidden' name='SaveReport' value='" . $_REQUEST['EditReport'] . "' />";
		}

		print "<table class='nicetable'>";
		print "<tr><td>id</td><td>Description</td><td>Run as user</td><td>Template</td><td>Filter</td></tr>";
		if ($_REQUEST['EditReport'] <> "new") {
			$row = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "publishedpages WHERE id=" . $_REQUEST['EditReport']);
		} else {
			$row = "";
		}
		print "<tr><td>" . $_REQUEST['EditReport'] . "</td><td><input type='text' size='50' name='PP_Description' value='" . htme($row['description']) . "' /></td><td><select name='PP_RunAsUser'>";
		$t = GetUserList();
		foreach ($t AS $user_row) {
			if ($user_row['id'] == $row['as_user']) {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='" . $user_row['id'] . "'>" . htme(GetUserName($user_row['id'])) . "</option>";
		}
		print "</select></td>";
		print "<td><select name='PP_Template'>";
		$sql = "SELECT templateid,templatename,templatetype FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype = 'TEMPLATE_HTML'";
		$result_to_print = mcq($sql,$db);
		while ($row_f = mysql_fetch_array($result_to_print)) {

			if ($row['template'] == $row_f['templateid']) {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}

			print "<option " . $ins . " value='" . $row_f['templateid'] . "'>" . htme($row_f['templatename']) . "</option>";
		}
		print "</select></td><td>";
		print "<select name='PP_Query'>";
		print "<option value='" . base64_encode("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE deleted<>'y' ORDER BY " . $GLOBALS['TBL_PREFIX'] . "entity.sqldate,eid DESC") . "'>[no limit]</option>";
		$t = GetStatusses();
		foreach ($t AS $record) {
			$q = "SELECT eid FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE status='" . $record . "' AND deleted<>'y' ORDER BY " . $GLOBALS['TBL_PREFIX'] . "entity.sqldate DESC,eid DESC";
			if ($row['report_query'] == $q) {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}

			print "<option " . $ins . " value='" . base64_encode($q) . "'>" . $lang['status'] . " : " . htme($record) . "</option>";
		}
		$t = GetPriorities();
		foreach ($t AS $record) {
			$q = "SELECT eid FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE priority='" . $record . "' AND deleted<>'y' ORDER BY " . $GLOBALS['TBL_PREFIX'] . "entity.sqldate DESC,eid DESC";
			if ($row['report_query'] == $q) {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='" . base64_encode($q) . "'>" . $lang['priority'] . " : " . htme($record) . "</option>";
		}
		$t = LoadCustomerCache();
		foreach ($t AS $cust) {
			$q = "SELECT eid FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE CRMcustomer='" . $cust['id'] . "' AND deleted<>'y' ORDER BY " . $GLOBALS['TBL_PREFIX'] . "entity.sqldate DESC,eid DESC";
			if ($row['report_query'] == $q) {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option " . $ins . " value='" . base64_encode($q) . "'>" . $lang['customer'] . " : " . htme($cust['custname']) . "</option>";
		}
		$t = GetExtraFields();
		foreach ($t AS $field) {
			if ($field['fieldtype'] == "drop-down") {
				$options = unserialize($field['options']);
				foreach ($options AS $option) {
					$q = "SELECT " . $GLOBALS['TBL_PREFIX'] . "entity.eid FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE " . $GLOBALS['TBL_PREFIX'] . "entity.EFID" . $field['id'] . "='" . $option. "' AND " . $GLOBALS['TBL_PREFIX'] . "entity.deleted<>'y' ORDER BY " . $GLOBALS['TBL_PREFIX'] . "entity.sqldate DESC,eid DESC";

					if ($row['report_query'] == $q) {
						$ins = "selected='selected'";
					} else {
						unset($ins);
					}

					print "<option " . $ins . " value='" . base64_encode($q) . "'>[" . htme($field['name']) . "] having value [" . htme($option) . "]</option>";
				}

			}
		}
		print "</select></td>";
		print "</tr>";
		print "</table>";
		print "<br /><input type='submit' name='PP_SaveButton' value='Save' />";
		print "</div></form>";
	}


}

EndHTML();
?>