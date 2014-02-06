<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This file takes care of file delivery; attachments, javascripts,
 * stylesheets, thumbnails and spreadsheets.
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */

$_REQUEST['keeplocked'] = "1";
$GLOBALS['CURFUNC'] = "GenerateExcel::";


if (isset($_REQUEST['GetBaseJS'])) {

	require_once($GLOBALS['PATHTOINTERLEAVE'] . "functions.php");
	$_REQUEST['keeplocked'] = true;
	header("Content-Type: text/javascript; " . $charset_suffix);
	header("Content-Disposition: attachment; filename=\"requested_js.js\"");
	print ReturnBaseJavascript();

} else {

	require_once("initiate.php");
	
	if (isset($_REQUEST['GetCustomerXML'])) {
		if (CheckFunctionAccess("DenyCustomerDownloads") != "ok" && GetAttribute("flextable", "DenyDownloads", $_REQUEST['DlSsFT']) != "Yes" && (CheckFunctionAccess("AllowedToExportXML") == "ok") || is_administrator()) {
			header("Content-Type: text/xml");
			header("Content-Disposition: attachment; filename=\"requested_xml.xml\"");
			XMLTableExport("customer");
			EndHTML(false);
		} else {
			ShowHeaders();
			PrintAD("Downloads from this table are not allowed");
		}
	} elseif (isset($_REQUEST['GetEntityXML'])) {
		if (CheckFunctionAccess("AllowedToExportXML") == "ok" || is_administrator()) {
			header("Content-Type: text/xml");
			header("Content-Disposition: attachment; filename=\"requested_xml.xml\"");
			XMLTableExport("entity");
			EndHTML(false);
		} else {
			ShowHeaders();
			PrintAD("You're not allowed to export XML."); 
		}

			
	} elseif (is_numeric($_REQUEST['GetFlextableXML'])) {
		if (GetAttribute("flextable", "DenyDownloads", $_REQUEST['GetFlextableXML']) != "Yes" && (CheckFunctionAccess("AllowedToExportXML") == "ok" || is_administrator())) {
			header("Content-Type: text/xml");
			header("Content-Disposition: attachment; filename=\"requested_xml.xml\"");
			XMLTableExport($_REQUEST['GetFlextableXML']);
			EndHTML(false);
		} else {
			ShowHeaders();
			PrintAD("Downloads from this table are not allowed");
		}
	} elseif (isset($_REQUEST['DlSs'])) {
		if (CheckFunctionAccess("HideListExportIconsExcelDirect") != "ok" || CheckFunctionAccess("HideListExportIconsExcelCF") != "ok") {
			if ($_REQUEST['EaCSV'] != "") {
				$ttype = "tsv";
			} else {
				$ttype = "excel";
			}
			print ReturnSpreadSheet(PopStashValue($_REQUEST['QiD']), PopStashValue($_REQUEST['CustomColumnLayoutStash']), $ttype, "entity");
			EndHTML(false);
		} else {
			PrintAD("Downloads from this table are not allowed");
		}
		
	} elseif (isset($_REQUEST['DlSsC'])) {
		if (CheckFunctionAccess("DenyCustomerDownloads") != "ok") {
			if ($_REQUEST['EaCSV'] != "") {
				$ttype = "tsv";
			} else {
				$ttype = "excel";
			}
			print ReturnSpreadSheet(PopStashValue($_REQUEST['QiD']), PopStashValue($_REQUEST['CustomColumnLayoutStash']), $ttype, "customer");
			EndHTML(false);
		} else {
			PrintAD("Downloads from this table are not allowed");
		}

		
	} elseif (isset($_REQUEST['DlSsFT'])) {

		if ($_REQUEST['EaCSV'] != "") {
			$ttype = "tsv";
		} else {
			$ttype = "excel";
		}

		if (GetAttribute("flextable", "DenyDownloads", $_REQUEST['DlSsFT']) != "Yes") {
			
			$DownloadRules = GetAttribute("flextable", "DownloadRules", $_REQUEST['DlSsFT']);
			if ($DownloadRules == "") {
				print ReturnSpreadSheet(PopStashValue($_REQUEST['QiD']), PopStashValue($_REQUEST['CustomColumnLayoutStash']), $ttype, "flextable" . $_REQUEST['DlSsFT']);
				EndHTML(false);
			} else {
				$du = explode(",", $DownloadRules);

				// First process Groups
				foreach ($du AS $rule) {
					$el = explode(":", $rule);
					if (substr($el[0],0,1) == "G") { // Group rule
						$group = str_replace("G", "", $el[0]);
						if ($group == $GLOBALS['USERPROFILE']) { // This is about "me"
							$process = true;
							$limit = $el[1];
						}
						
					}
				}
				// 2nd process users (user settings overrule group settings)
				foreach ($du AS $rule) {
					$el = explode(":", $rule);
					if (substr($el[0],0,1) == "U") { // User rule
						$user = str_replace("U", "", $el[0]);
						if ($user == $GLOBALS['USERID']) { // This is about "me"
							$process = true;
							$limit = $el[1];
						}
					}
				}

				if ($limit == "0" && $process) {

					print ReturnSpreadSheet(PopStashValue($_REQUEST['QiD']), PopStashValue($_REQUEST['CustomColumnLayoutStash']),$ttype, "flextable" . $_REQUEST['DlSsFT']);
					EndHTML(false);
				} elseif (is_numeric($limit) && $process) {
					$cnt = count(db_GetFlatArray(PopStashValue($_REQUEST['QiD'])));
					if ($cnt < $limit) {
						print ReturnSpreadSheet(PopStashValue($_REQUEST['QiD']), PopStashValue($_REQUEST['CustomColumnLayoutStash']), $ttype, "flextable" . $_REQUEST['DlSsFT']);
						EndHTML(false);
					} else {
						ShowHeaders();
						PrintAD("Too many records selected; access to download this amount was denied by your administrator. Select less records.");
						EndHTML(true);
					}
				} else {
					ShowHeaders();
					PrintAD("Downloads are denied (by DownloadRules directive)");
					EndHTML(true);
				}
			
			}
			
		} else {
			PrintAD("Downloads from this table are not allowed");
		}
		
	} elseif ($_REQUEST['DisplayBinaryStashValue']) {
		$tmp = PopStashValue($_REQUEST['DisplayBinaryStashValue']); // This is secured
		print $tmp;
		EndHTML(false);

	} elseif ($_REQUEST['ArrayStashID']) {
		
		$charset_suffix = " charset=" . $GLOBALS['CHARACTER-ENCODING'];
		header("Content-Type: application/vnd.ms-excel;" . $charset_suffix);
		header("Content-Disposition: attachment; filename=Flextable-" . $_REQUEST['ExportFlexTable'] . "-export.xlsx" );
		header("Window-target: _top");
		$sheet = PopStashValue($_REQUEST['ArrayStashID']);
		RealExcel($sheet, "array");
		EndHTML(false);

	} elseif ($_GET['tn']) {
		
		if (CheckFileAccess($_GET['tn']) == "ok") {
			header("Content-Type: image/jpeg");
			print GetImageThumbnail($_GET['tn']);
		} else {
			PrintAD("You do not have access to this file.");
		}
		EndHTML(false);

	} elseif ($_GET['minitn']) {

		if (CheckFileAccess($_GET['minitn']) == "ok") {
			header("Content-Type: " . GetFileType($_GET['minitin']));
			print GetMiniThumbnail($_GET['minitn']);
		} else {
			PrintAD("You do not have access to this file.");
		}
		EndHTML(false);

	} elseif (isset($_REQUEST['GetJS'])) {

		$_REQUEST['keeplocked'] = true;
		do_language();
		header("Content-Type: application/x-javascript; " . $charset_suffix);
		header("Content-Disposition: attachment; filename=\"requested_js.js\"");
		print ReturnStandardJavascript();
		print PrintExtraFieldForceJavascript("", false, false, false, $_REQUEST['ent']);
		EndHTML(false);

	} elseif (isset($_REQUEST['ReadPDF'])) {
		$_REQUEST['nonavbar'] = true;
		ShowHeaders();
		if (CheckFileAccess($_REQUEST['ReadPDF']) != "nok") {
			print ShowPDFinJS($_REQUEST['ReadPDF']);
		} else {
			PrintAD("Access to file denied");
		}
		EndHTML(true);

	} elseif (isset($_REQUEST['GetjQueryUiPlacementJS'])) {

		$_REQUEST['keeplocked'] = true;
		header("Content-Type: application/x-javascript; " . $charset_suffix);
		header("Content-Disposition: attachment; filename=\"requested_js.js\"");
		print ReturnJqueryUIElementPlacementCode();
		EndHTML(false);

	} elseif ($_REQUEST['GetCSS']) {

		$_REQUEST['keeplocked'] = true;
		if ($_REQUEST['GetCSS'] == "default") {
			$tmp['content'] = ParseCSS(true);
		} else {
			if (GetTemplateType($_REQUEST['GetCSS']) == "TEMPLATE_CSS") {
				$tmp = GetTemplate($_REQUEST['GetCSS']);
			} else {
				log_msg("ERROR: Non-CSS file requested using GetCSS");
			}
	//		$tmp = db_GetRow("SELECT content FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE " . $GLOBALS['TBL_PREFIX'] . "templates.templatetype='TEMPLATE_CSS' AND " . $GLOBALS['TBL_PREFIX'] . "templates.templateid='" . mres($_REQUEST['GetCSS']) . "'");
		}
		if ($tmp['content']) {
				$charset_suffix = " charset=" . $GLOBALS['CHARACTER-ENCODING'];
				header("Content-Type: text/css; " . $charset_suffix);
				header("Content-Disposition: attachment; filename=\"requested_css.css\"");
				print $tmp['content'];
		}

		$extracss = GetTemplate(GetSetting("SYSWIDECSS"));

		if ($extracss != "") {
			print "\n\n/* Start custom CSS */ \n\n" . $extracss . "\n\n/* End custom CSS */\n\n";
		}

		print CreateStatusAndPriorityCSS();
		EndHTML(false);

	} elseif ($_REQUEST['templateid']) {

		$template = GetTemplate($_REQUEST['templateid']);
		header("Content-Type: application/octet-stream;");
		header("Content-Disposition: attachment; filename=\"" . GetTemplateName($_REQUEST['templateid']) . "\"");
		print $template;
		EndHTML(false);

	} elseif ($_REQUEST['fileid']) {

		$fileid = $_REQUEST['fileid'];

		$acc = CheckFileAccess($fileid);

		$type = GetFileType($fileid);

		if (substr($type, 0, 9) == "TEMPLATE_" && !is_administrator()) {
			$acc = "nok";
			qlog(INFO, "Download access to templates denied");
		}


		if ($acc == "ok" || $acc == "readonly" || is_administrator()) {

			$charset_suffix = " charset=" . $GLOBALS['CHARACTER-ENCODING'];
			// ; " . $charset_suffix);
			header("Content-Type: " . GetFileType($fileid) . "");
			if ($_REQUEST['displayinline'] != 1) {
				header("Content-Disposition: attachment; filename=\"" . GetFileName($fileid) . "\"");
			}

			print GetFileContent($fileid);
			// Push attachment from database
			log_msg("$geg2[filename] from entity $geg2[koppelid] downloaded","");
			journal($geg2['koppelid'],"$geg2[filename] downloaded");
			EndHTML(false);
		} else {
			ShowHeaders();
			PrintAD("access denied, not found or not available");
			EndHTML();
		}

	} else {

		ShowHeaders();
		PrintAD("Don't know what to do.");
		EndHTML(true);

	}
}
?>