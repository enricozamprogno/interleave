<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
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
// Summary using PDF
require_once("initiate.php");

if (strtoupper($BlockAllCSVDownloads)=="YES") {
		MustBeAdminUser();
		qlog(INFO, "Access denied");
} else {
		qlog(INFO, "Access granted");
}
$pdf_filename = "Interleave_PDF_Entity_Summary_" . $_REQUEST['pdf'] . "_.pdf";
$GLOBALS['CURFUNC'] = "SumPDF::";


$date = date("F j, Y, H:i") . "h";
$pdf_title2 = $pdf_title;

$pdf_title = "Interleave $lang[entsum]";
$pdf_title_link = "http://www.interleave.nl";

require_once($GLOBALS['PATHTOINTERLEAVE'] . "pdf_inc2.php");

$tc=1;

$GLOBALS['CURFUNC'] = "SumPDF::";

// $pdf should contain a comma separated list of all to entities to be printed
if (!$include) {
	uselogger("PDF Summary downloaded","");
	$GLOBALS['CURFUNC'] = "SumPDF::";
	qlog(INFO, "PDF Summary requested");
}
if ($enc) {
	$pdf = base64_decode($pdf);
}
if ($stashid) {
	$res = mcq(PopStashValue($stashid), $db);
	while ($row = mysql_fetch_array($res)) {
		$pdf .= $row['eid'] . ",";
	}
}
qlog(INFO, "PDF is $pdf");

if ($file) {
	unset($pdf);
	$file = base64_decode($file);
	$fp = fopen($file,"r");
	while (!feof($fp)) {
		$pdf .= fread($fp,filesize($file));
	}
	fclose($fp);
	unlink($file);
	qlog(INFO, "PDF is $pdf");
}


if (!$include) {

	$pdfa = split(",",$pdf);
	sort($pdfa);

	if ($print) {
		$NoImageInclude=1;
		StartPrintPDF();
	} else {
		StartPDF();
	}



	$pdfa2 = array();
	foreach($pdfa AS $element) {
		if (CheckEntityAccess($element) <> "nok") {
			array_push($pdfa2,$element);
		} else {
			qlog(INFO, "Access to entity $element was denied in PDF export");
		}
	}
	// Print table of contents when there is more than one summary to report
	if (sizeof($pdfa2)>1) {
		toc($pdfa2);
	}
}

if (!$include) {
	CreatePDF($pdfa2);
}
// Useless, only for logging:
ClearCacheArrays();

qlog(INFO, "DONE");
if (!$include) {
    EndHTML(false);
}
?>