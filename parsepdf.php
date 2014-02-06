<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file handles parsing an HTML template to PDF
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
*/

require_once("initiate.php");

if ($_REQUEST['Obj'] == "to_sub") {
	$_REQUEST['Obj'] = PushStashValue($_REQUEST['data']);
}

if (($_REQUEST['eid'] && $_REQUEST['template']) || ($_REQUEST['stashid'] && $_REQUEST['template']) || $_REQUEST['Obj']) {
  // nothing
} elseif ($_REQUEST['SingleEntity'] == "[QueryResult]") {
	ShowHeaders();
} else {
	$_REQUEST['nonavbar'] = 1;
    ShowHeaders();
}

if ($_REQUEST['footer']) {
	$GLOBALS['PDF_DEFAULTFOOTER'] = $_REQUEST['footer'];
}

if (isset($_REQUEST['debug']) && is_administrator()) {
	$debug = true;
}

// Compatibility
if ($_REQUEST['FlexTable']) $_REQUEST['tid'] == $_REQUEST['FlexTable'];
if ($_REQUEST['FlexTableRecord']) $_REQUEST['eid'] == $_REQUEST['FlexTableRecord'];

$headerlogo = "../../../images/crm.jpg";

// Evaluating errorneous PHP will cause PHP to throw a 500 ISE when display_errors is set to "Off". For now, shut inline PHP off for exported templates
//$GLOBALS['NOINLINEPHPEVAL'] = true;



if ($_REQUEST['SingleEntity'] || $_REQUEST['SingleRecord']) {
	
	$record = $_REQUEST['SingleRecord'];
	if ($record == "") $record = $_REQUEST['SingleEntity']; // deprecated notation
	
	$acc = "";
	$table = ""; 
	if (!$_REQUEST['stashid']>0) {
		if ($_REQUEST['tid'] == "" || $_REQUEST['tid'] == "entity") {
			$acc = CheckEntityAccess($record);
			$table = "entity";
		} elseif ($_REQUEST['tid'] == "customer") {
			$acc = CheckCustomerAccess($record);
			$table = "customer";
		} else {
			$flextableid = str_replace("flextable", "", str_replace("ft", "", $_REQUEST['tid']));
			if (is_numeric($flextableid)) {
				$acc = CheckFlextableRecordAccess($flextableid, $record);
				$table = $flextableid;
			}
		}
	} else {
		if ($_REQUEST['SingleEntity'] == "true") {
			$acc = "nok"; // init
			if ($_REQUEST['tid'] == "" || $_REQUEST['tid'] == "entity") {
				$table = "entity";
				$acc = "ok"; // OK for now, we're working on a set, not a single record
			} elseif ($_REQUEST['tid'] == "customer") {
				$table = "customer";
				$acc = "ok"; // OK for now, we're working on a set, not a single record
			} else {
				$flextableid = str_replace("flextable", "", str_replace("ft", "", $_REQUEST['tid']));
				if (is_numeric($flextableid)) {
					$table = $flextableid;
					$acc = "ok"; // OK for now, we're working on a set, not a single record
				} 
			}
		} else {
			$acc = "nok";
		}
	}
		
	if ($acc == "nok" || $table == "") {
		ShowHeaders(); 
		PrintAD("Access to this record is not allowed (" . $table . " : " . $acc . ")");
	} else {

		print "<table width='100%'>";
		if (is_numeric($_REQUEST['SingleEntity'])) {
			$desc = $_REQUEST['SingleEntity'];
		} else {
			$desc = "{{list}}";
		}
		print "<tr><td colspan='2'><strong>" . $lang['createreport'] . " " . $desc . "</strong><br><br></td></tr>";

		$sql = "SELECT templateid,templatename,timestamp_last_change,username FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_REPORT_PDF' ORDER BY templatename";
		$result = mcq($sql,$db);
		while ($row = mysql_fetch_array($result)) {

			print "<tr><td><a class='arrow' href='#' onclick=\"parent.location='parsepdf.php?eid=" . $_REQUEST['SingleEntity'] . "&amp;template=" . $row['templateid'] . "&amp;stashid=" . $_REQUEST['stashid'] . "&amp;tid=" . $_REQUEST['tid'] . "';parent.$.fancybox.close();\">" . ParseTemplateAll($row['templatename'], $_REQUEST['SingleEntity'], false, "htme")  . "</a></td></tr>";
		}
		
		print "</table>";
	}
	EndHTML();
} elseif ($_REQUEST['Obj']) {

   	global $lang;
	unset($pdf);

	// Convert input to UTF-8

	$obj = PopStashValue($_REQUEST['Obj']);


/*
	$obj['html'] = $htmltoconvert;
	$obj['linktext'] = $linktext;
	$obj['orientation'] = $orientation;
	$obj['header'] = $header;
	$obj['nologo'] = $nologo;
	$obj['noheader'] = $noheader;
	$obj['nofooter'] = $nofooter;
	$obj['filename'] = $filename;
*/
	require_once($GLOBALS['PATHTOINTERLEAVE'] . "lib/tcpdf/config/lang/eng.php");
	require_once($GLOBALS['PATHTOINTERLEAVE'] . "lib/tcpdf/tcpdf.php");
	if ($obj['filename'] == "") {
		$filename = "report";
	} else {
		$filename = $obj['filename'];
	}

	// create new PDF document
	$pdf = new TCPDF($obj['orientation'], PDF_UNIT, PDF_PAGE_FORMAT, true, "UTF-8", false);

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor($GLOBALS['PRODUCT'] . ' : ' . GetUserName($GLOBALS['USERID']));
	$pdf->SetTitle('Automatically generated PDF report');
	$pdf->SetSubject('See http://www.interleave.nl/');
	$pdf->SetKeywords('');

	if (!$obj['nologo']) {
		// set default header data
		$pdf->SetHeaderData("../../../images/crm.jpg", PDF_HEADER_LOGO_WIDTH, $obj['header'], "");
	} else {
		// set default header data
		$pdf->SetHeaderData(false, false, $obj['header'], "");
	}



	// set header and footer fonts
	$pdf->setHeaderFont(Array($obj['fontname'], '', $obj['fontsize']));
	$pdf->setFooterFont(Array($obj['fontname'], '', $obj['fontsize']));



	$pdf->Cell(0,10,"footerText", 0, 0, "L");

	//set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	//set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	//set some language-dependent strings
	$pdf->setLanguageArray($l);

	// ---------------------------------------------------------

	if ($obj['nofooter']) {
		$pdf->setPrintFooter(false);
	}
	if ($obj['noheader']) {
		$pdf->setPrintHeader(false);
	}
	preg_match_all('/(\^\^BC_)(.*)(\^\^ENDBC\^\^)/', $obj['html'], $matches);
	$list_of_tags = $matches[0];	

	foreach ($list_of_tags AS $fulltag) {
	
		$tag = explode("^^", $fulltag);
		/*
		Array
		(
			[0] => 
			[1] => BC_C39E
			[2] => DITISEENTEST
			[3] => H80
			[4] => W30
			[5] => B0
			[6] => ENDBC
			[7] => 
		)
		*/

		$bc = str_replace("BC_", "", $tag[1]);
		$value = $tag[2];
		$height = str_replace("H", "", $tag[3]);
		$width = str_replace("B", "", $tag[4]);
		
		if ($width == "") $width = 30;
		if ($height == "") $height = 40;

		if ($tag[5] == "B0") {
			$border = false;
		} elseif ($tag[5] == "B1") {
			$border = true;
		} elseif ($tag[5] == "") {
			$border = false;
		}
		
		$params = $pdf->serializeTCPDFtagParameters(array($value, $bc, '', '', $height, $width, 0.4, array('position'=>'S', 'border'=>$border, 'padding'=>4, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>true, 'font'=>$obj['fontname'], 'fontsize'=>$obj['fontsize'], 'stretchtext'=>4), 'N'));

		$obj['html'] = str_replace($fulltag, '<tcpdf method="write1DBarcode" params="'.$params.'" />', $obj['html']);;


	}
	$arr = explode("%PAGEBREAK%", $obj['html']);

	foreach ($arr AS $htmlpart) {
		if (trim(strip_tags($htmlpart)) != "") {
			// set font
			$pdf->SetFont($obj['fontname'], '', $obj['fontsize']);

			// add a page
			$pdf->AddPage();

			// output the HTML content
			$pdf->writeHTML($htmlpart, true, 0, true, 0);
			if ($debug) {
				$output .= $htmlpart;
			}
		}
	}
	
	//$filename = str_replace(" ", "_", GetTemplateSubject($_REQUEST['Obj']));
	if ($_REQUEST['eid']) {
		$filename = ParseTemplateAll($filename, $_REQUEST['eid'], false, "htme");
	}
	
	if ($debug) { DA($output); } else { 	 
		//Close and return PDF document
		header("Content-Type: application/pdf");
		header("Content-Disposition: attachment; filename=" . $filename .".pdf");
		header("Window-target: _top");
	
		print $pdf->Output($filename, 'S');
	}

} elseif (($_REQUEST['eid'] && $_REQUEST['template']) || ($_REQUEST['stashid'] && $_REQUEST['template'])) {

	$record = $_REQUEST['eid'];
	$acc = "";
	$table = ""; 
	if (is_numeric($record)) {
		if ($_REQUEST['tid'] == "" || $_REQUEST['tid'] == "entity") {
			$acc = CheckEntityAccess($record);
			$table = "entity";
		} elseif ($_REQUEST['tid'] == "customer") {
			$acc = CheckCustomerAccess($record);
			$table = "customer";
		} else {
			$flextableid = str_replace("flextable", "", str_replace("ft", "", $_REQUEST['tid']));
			if (is_numeric($flextableid)) {
				$acc = CheckFlextableRecordAccess($flextableid, $record);
				$table = $flextableid;
			}
		}
	} elseif (!is_numeric($_REQUEST['eid'])) {
		$acc = "nok"; // init
		if ($_REQUEST['tid'] == "" || $_REQUEST['tid'] == "entity") {
			$table = "entity";
			$acc = "ok"; // OK for now, we're working on a set, not a single record
		} elseif ($_REQUEST['tid'] == "customer") {
			$table = "customer";
			$acc = "ok"; // OK for now, we're working on a set, not a single record
		} else {
			$flextableid = str_replace("flextable", "", str_replace("ft", "", $_REQUEST['tid']));
			if (is_numeric($flextableid)) {
				$table = $flextableid;
				$acc = "ok"; // OK for now, we're working on a set, not a single record
			} 
		}
	} else {
		$acc = "nok";
	}

		
	if ($acc == "nok" || $table == "") {
		ShowHeaders();
		PrintAD("Access to this record is not allowed ($acc : $table)");
		EndHTML();
	} else {

		if ($_REQUEST['template'] == "default") {


				$header = $GLOBALS['PRODUCT'] . " " . $lang['entsum'] . "";
				$def = true;
				qlog(INFO, "PDF : def is true");

		} else {
				$template = ParseTemplateAliases(GetTemplate($_REQUEST['template']));
				

				if ($_REQUEST['FlexTable'] && $_REQUEST['FlexTableRecord']) {
					$template = ParseFlexTableTemplate($_REQUEST['FlexTable'], $_REQUEST['FlexTableRecord'], $template, false, false, false, "htme");
					$t = GetFlexTableDefinitions($_REQUEST['FlexTable']);
					
					if ($t[0]['orientation'] = "one_entity_to_many") {
						//$eid = GetFlextableFieldValue($_REQUEST['FlexTableRecord'], '2147483647', $_REQUEST['FlexTable']);
						$eid = GetExtraFieldValue($_REQUEST['FlexTableRecord'], '2147483647', false, false, false);
					}
					qlog(INFO, "HH Parsed it");
				}

				$header = ParseTemplateLanguageTags(ParseTemplateAll(ParseTemplateAliases(GetTemplateName($_REQUEST['template'])), $_REQUEST['eid'], true, "htme"));

		}

		if ($acc != "nok" || $_REQUEST['eid'] == "true" || $_REQUEST['stashid']) {
			if ($_REQUEST['stashid'] != "") {

				$source_template = $template;
				$template = "";
				$eidstp = array();
				// Fetch SQL query from stash
				$eids = mcq(PopStashValue($_REQUEST['stashid']), $db);
				$done_eids = array();
				while ($row = mysql_fetch_array($eids)) {
					if ($def) {
						if (CheckEntityAccess($row['eid']) != "nok" && !in_array($row['eid'], $done_eids)) {
							array_push($eidstp, $row['eid']);
							qlog(INFO, "PDF : add " . $row['eid']);
						}
					} else {
						if (CheckEntityAccess($row['eid']) != "nok" && !in_array($row['eid'], $done_eids)) {
							$template .= str_replace("&quot;", "'", ParseTemplateAll(ParseTemplateAliases($source_template), $row['eid'], true, "htme"));
							array_push($done_eids, $row['eid']);
						}
					}

				}

			} else {
				if (!$def) {
					$template =  str_replace("&quot;", "'", ParseTemplateAll(ParseTemplateAliases($template), $_REQUEST['eid'], true, "htme"));
				} else {
					$eidstp = array($_REQUEST['eid']);
				}
			}

			if ($debug) {
				print $template;
				EndHTML();
				exit;
			} else {
				 if ($def) {
					$filename = "report";
				} else {
					$filename = GetTemplateSubject($_REQUEST['template']);
					$filename = str_replace(" ", "_", ParseTemplateGeneric($filename));
				}
	
				if ($_REQUEST['FlexTable'] && $_REQUEST['FlexTableRecord']) {
					$filename = ParseFlexTableTemplate($_REQUEST['FlexTable'], $_REQUEST['FlexTableRecord'], $filename, false, true, "dontformatnumbers", "plain");
				}
				if ($_REQUEST['eid']) {
					$filename = ParseTemplateEntity($filename, $_REQUEST['eid'], true, false, false, "plain");
				}
				if (GetEntityCustomer($_REQUEST['eid'])) {
					$filename = ParseTemplateCustomer($filename, GetEntityCustomer($_REQUEST['eid']), false,  "plain", false);
				}

				$AllowUserToEditParsedResultBeforeCreatingPDF = GetAttribute("template", "AllowUserToEditParsedResultBeforeCreatingPDF", $_REQUEST['template']);
				
				if ($AllowUserToEditParsedResultBeforeCreatingPDF == "Yes") {

					$EditBeforeParseHTML = GetAttribute("template", "EditBeforeParseHTML", $_REQUEST['template']);
					$EditBeforeParseButtonText = GetAttribute("template", "EditBeforeParseButtonText", $_REQUEST['template']);
					$HideHeader = GetAttribute("template", "HideHeader", $_REQUEST['template']);
					$HideFooter = GetAttribute("template", "HideFooter", $_REQUEST['template']);

					ShowHeaders();
					print "" . $EditBeforeParseHTML . "";
					print "<form name=\"editPDFb4generate\" id=\"JS_editPDFb4generate\" method=\"post\" action=\"?\">";
					

					print "<textarea id='editor' rows='70' cols='140' name='data' class='mnspc'>" . htme(ReturnTemplateStyleSheet($_REQUEST['template'])) . htme($template) . "</textarea>";
					print make_html_editor("editor", true, false, true);
					print "<input type=\"hidden\" name=\"Obj\" id=\"JS_Obj\" value=\"to_sub\">";
					
					$tmp = GetTemplateShowOnAddList($_REQUEST['template']);
					if ($tmp == "n") { // no logo 
						print "<input type=\"hidden\" name=\"nologo\" id=\"JS_nologo\" value=\"yes\">";
					}
					print "<input type=\"hidden\" name=\"filename\" id=\"JS_filename\" value=\"" . htme($filename) . "\">";
					if ($HideHeader == "Yes") {
						print "<input type=\"hidden\" name=\"noheader\" id=\"JS_noheader\" value=\"yes\">";
					}
					if ($HideFooter == "Yes") {
						print "<input type=\"hidden\" name=\"nofooter\" id=\"JS_nofooter\" value=\"yes\">";
					}

					print "<input type=\"hidden\" name=\"orientation\" id=\"JS_orientation\" value=\"" . htme(GetTemplateOrientation($_REQUEST['template'])) . "\">";
					print "<input type=\"hidden\" name=\"nonavbar\" id=\"JS_nonavbar\" value=\"" . htme($_REQUEST['nonavbar']) . "\">";
					print "<input type=\"submit\" name=\"submitpdf\" id=\"JS_submitpdf\" value=\"" . htme($EditBeforeParseButtonText) . "\">";
					print "</form>";


					EndHTML(true);
				
				} else {

	
					if ($def) {
						qlog(INFO, "PDF : create report DEFAULT");
						qlog(INFO, $eidstp);
		
						$pdf = CreatePDFEntityReport($eidstp);
					} else {
						$tmp = GetTemplateShowOnAddList($_REQUEST['template']);
						if ($tmp == "n") { // No logo
							$nologo = true;
						} else {
							$noologo = false;
						}
		
						$template = ParseTemplateLanguageTags($template);
						$orientation = GetTemplateOrientation($_REQUEST['template']);
						$stylesheet = ReturnTemplateStyleSheet($_REQUEST['template']);
		
						$nt .= "<!DOCTYPE html><html><head>";
						// The stylesheet already has script header & footer
						$nt .= "" . $stylesheet . "";
						$nt .= "</head><body>\n" . $template . "\n</body>\n</html>\n";

						$noheader = false;
						$nofooter = false;

						if (GetAttribute("template", "HideHeader", $_REQUEST['template']) == "Yes" || $_REQUEST['noheader']) $noheader = true;
						if (GetAttribute("template", "HideFooter", $_REQUEST['template']) == "Yes" || $_REQUEST['nofooter']) $nofooter = true;
						
						$pdf = CreatePDFEntityReportBasedOnHTML($nt, $header, false, $nologo, $orientation, $noheader, $nofooter);
		
						
					}
					
					
					

		
					header("Content-Type: application/pdf");
					header("Content-Disposition: attachment; filename=\"" . $filename .".pdf\"");
					header("Window-target: _top");
		
					print $pdf;
		
		
					EndHTML(false);
				}
			}
		} else {
			PrintAD("You don't have access to this entity (" . $_REQUEST['eid'] . ")");
			EndHTML();
		}
		
	}

} else {
    PrintAD("I don't know what to do. Quitting.");
    EndHTML();
}
?>