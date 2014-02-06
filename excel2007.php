<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file generates Excel 2007 files
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */

/*

SUPPORTED LAYOUT OPERATORS

To be included like [[operator]] in a cell. Global operators (like orientation) can be put in any cell in the sheet to which they should apply.

Tag				Example					Description

fontsize		[[fontsize16]]			Set the font size to 16px
font			[[fontVerdana]]			Set the font face to Verdana
bgcolor			[[bgcolor#00ff00]]		Set the background color to #00ff00
fgcolor			[[fgcolor#0000ff]]		Set the foreground (text) color to #0000ff
wrapped			[[wrapped]]				Wrap the cell contents when it's wider than the column width
wrap			[[wrap]]				Alias for [[wrapped]]
borders			[[borders]]				Set cell border (all sides)
border-top		[[border-top]]			Set cell top border
border-bottom	[[border-bottom]]		Set cell bottom border
border-left		[[border-left]]			Set cell left border
border-right	[[border-right]]		Set cell right border
standing		[[standing]]			Rotate cell contents 90 degrees
bold			[[bold]]				Set cell font weight bold
vmerge			[[vmerge5]]				Merge current cell with next 4 cells below
merge			[[merge3]]				Merge current cell with next 2 cells to the right
width			[[width90]]				Set column width to 90 Excel-units. Disables Auto-sizing of the column's width
right-align		[[right-align]]			Align text in cell right
left-align		[[left-align]]			Align text in cell left
center			[[center]]				Center text in cell
sheetname		[[sheetnameWorksheet1]]	Set the worksheet name
nextsheet		[[nextsheet]]			Close current sheet, start new sheet
autofilter		[[autofilter]]			Enable autofilter on the header row
autosize		[[autosize]]			Enable autosize for the current column
a4				[[a4]]					Set page size to A4
a3				[[a3]]					Set page size to A3
fittopage		[[fittopage]]			Fit whole sheet on 1 page when printing
fittowidth		[[fittowidth]]			Fit width to one page when printing
fittoheight		[[fittoheight]]			Fit height to one page when printing

These tags need to be included in the (or a) cell value. 

*/

require_once("initiate.php");

set_include_path(get_include_path() . PATH_SEPARATOR . 'lib/phpexcel/Classes/');
error_reporting(E_NONE);

/** Include path **/
set_include_path(get_include_path() . PATH_SEPARATOR . 'lib/phpexcel/Classes/');

/** PHPExcel */
include 'PHPExcel.php';

/** PHPExcel_Writer_Excel2007 */
include 'PHPExcel/Writer/Excel2007.php';

function RealExcel2007($excel,$CSVseparator="@@@@REALEXCEL@@@@", $format = "xlsx") {
	// HANDLES CSV OUTPUT SEPARATED BY @@@REALEXCEL@@@@ ASSUMES FIRST LINE IS LEGEND
	// $WrappedCellsEachLine must contain array with horiz. fields list (numbers) of cells
	// that must be wrapped

	qlog(INFO, "Writing Excel 2007 file");

	if (strstr($excel[1], "@@@@REALEXCEL@@@@")) $CSVseparator = "@@@@REALEXCEL@@@@";

	$rows = ExcelRows(); // Array with rows (A .. AA .. ZZ)
	$sheet = 0;	
	if ($_REQUEST['fntu'] != "") {
		$filename = base64_decode($_REQUEST['fntu']);
	} else {
		$filename = str_replace(" ", "_", $GLOBALS['title']) . "-" . $GLOBALS['PRODUCT'] . "-export-" . date("m-j-Y-Hi") . "h";
	}
	if ($_REQUEST['fmtu'] != "") {
		$format_t = base64_decode($_REQUEST['fmtu']);
		if ($format_t == "xlsx" || $format_t == "xls" || $format_t == "pdf") {
			$format = $format_t;
		}
	} 
	$objPHPExcel = new PHPExcel();

	$objPHPExcel->getProperties()->setCreator("" . $GLOBALS['PRODUCT'] . " user " . $GLOBALS['USERNAME']);
	$objPHPExcel->getProperties()->setLastModifiedBy("" . $GLOBALS['PRODUCT'] . "");
	$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Document");
	$objPHPExcel->getProperties()->setSubject("" . $GLOBALS['PRODUCT'] . " Excel2007 Export");
	$objPHPExcel->getProperties()->setDescription("Interleave Open Source Business Process Management - http://www.interleave.nl");
	$objPHPExcel->getProperties()->setKeywords("" . $GLOBALS['PRODUCT'] . "-export-" . date("m-j-Y-Hi") . "h");
	$objPHPExcel->getProperties()->setCategory("" . $GLOBALS['PRODUCT'] . " Export " . date("m-j-Y-Hi") . "h");

	$default_size = GetSetting("DefaultExcel2007FontSize");
	$default_font = GetSetting("DefaultExcel2007Font");
	if ($default_size == "") $size = 11;
	if ($default_font == "") $font = "Tahoma";

	$objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setName($default_font);
	$objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize($default_size); 

	$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(false);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(0);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

	// $objPHPExcel->getActiveSheet()->setAutoFilter('A1:C9');

	// Add some data
	$objPHPExcel->setActiveSheetIndex(0);


	// Create header in format_bold
	if ($CSVseparator != "array") {
		$header = split($CSVseparator,$excel[0]);
	} else {
		$header = $excel[0];
	}
	for ($i=0;$i<sizeof($header);$i++) {
		if (trim($header[$i]) == "") {
			unset($header[$i]);	
		} else {
			qlog(INFO, "VALID HEADER: " . $header[$i]);
		}
	}
	
	qlog(INFO, "Set auto-resize....");
	// Set all columns to auto-resize by default
	$scol = 'A';
	foreach ($header AS $dummy) {
		$scol++;
	}
	$scol++;
	for ($col = 'A'; $col != $scol; $col++) {
		$objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
	}
	$sheetCorrection = 0;
	$summs = array();

	qlog(INFO, "Array contains " . sizeof($excel) . " rows including header");
	for ($i=0;$i<sizeof($excel);$i++) {
			qlog(INFO, "Processing row $i (mem: " . memory_get_usage() . ")");
			if ($CSVseparator != "array") {
				$line = explode($CSVseparator,$excel[$i]);	// get current line and array' it
			} else {
				$line = $excel[$i];
				$line[] = "";
			}

			for ($u=0;$u<sizeof($line) -1;$u++) {
					//if ($line[$u]) {

						$styleArray = array();
						$styleArray['font'] = array();

						$cell = ($rows[$u]) . (($i+1) - $sheetCorrection);
						$wrapped = false;

						$style_set = false;

						preg_match_all('/\[\[[A-Za-z0-9_#-]+\]\]/', $line[$u], $matches);
						$list_of_tags = $matches[0];
						foreach ($list_of_tags AS $tag) {
							// Strip operators
							$tag_content = str_replace("[[", "", str_replace("]]", "", $tag));

							// Strip tag from value
							$line[$u] = str_replace($tag, "", $line[$u]);
							
							// Check per possible tag
							if (substr($tag_content, 0, 8) == "fontsize") {
								$fontsize = str_replace("fontsize", "", $tag_content);
								$styleArray['font']['size'] = $fontsize;
								$style_set = true;
							} elseif (substr($tag_content, 0, 4) == "font") {
								$font = str_replace("font", "", $tag_content);
								$styleArray['font']['name'] = $font;
								$style_set = true;
							} elseif (substr($tag_content, 0, 7) == "bgcolor") {
								$bgcolor= strtoupper(str_replace("bgcolor#", "", $tag_content));
								$objPHPExcel->getActiveSheet()->getStyle($cell)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
								$objPHPExcel->getActiveSheet()->getStyle($cell)->getFill()->getStartColor()->setARGB('FF' . $bgcolor);
							} elseif (substr($tag_content, 0, 7) == "fgcolor") {
								$fgcolor= str_replace("fgcolor#", "", $tag_content);
								$objPHPExcel->getActiveSheet()->getStyle($cell)->getFont()->getColor()->setARGB('FF' . $fgcolor);
							} elseif ($tag_content == "wrapped" || $tag_content== "wrap") {
								$objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setWrapText(true);
							} elseif ($tag_content == "borders") {
								$objPHPExcel->getActiveSheet()->getStyle($cell)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
								$objPHPExcel->getActiveSheet()->getStyle($cell)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
								$objPHPExcel->getActiveSheet()->getStyle($cell)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
								$objPHPExcel->getActiveSheet()->getStyle($cell)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
							} elseif ($tag_content == "border-top") {
								$objPHPExcel->getActiveSheet()->getStyle($cell)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
							} elseif ($tag_content == "border-bottom") {
								$objPHPExcel->getActiveSheet()->getStyle($cell)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
							} elseif ($tag_content == "border-left") {
								$objPHPExcel->getActiveSheet()->getStyle($cell)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
							} elseif ($tag_content == "border-right") {
								$objPHPExcel->getActiveSheet()->getStyle($cell)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
							} elseif ($tag_content == "standing") {
								$objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setTextRotation(90);
							} elseif ($tag_content == "bold") {
								$styleArray['font']['bold'] = true;
								$style_set = true;
							} elseif (substr($tag_content, 0, 6) == "vmerge") {
								$num_merge = str_replace("vmerge", "", $tag_content);
								$objPHPExcel->getActiveSheet()->mergeCells($cell . ":" . $rows[$u] . ($i + $num_merge));
							} elseif (substr($tag_content, 0, 5) == "merge") {
								$num_merge = str_replace("merge", "", $tag_content);
								$num_merge--;
								$objPHPExcel->getActiveSheet()->mergeCells($cell . ":" . $rows[($u + $num_merge)] . ($i+1));
							} elseif (substr($tag_content, 0, 5) == "width") {
								$width = str_replace("width", "", $tag_content);
								// Disable AutoSize
								$objPHPExcel->getActiveSheet()->getColumnDimension($rows[$u])->setAutoSize(false);
								// Set width
								$objPHPExcel->getActiveSheet()->getColumnDimension($rows[$u])->setWidth($width);
							} elseif ($tag_content == "right-align") {
								$objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
							} elseif ($tag_content == "left-align") {
								$objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
							} elseif ($tag_content == "center") {
								$objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
							} elseif ($tag_content == "autofilter") {
								// Set autofilter on first row
								$objPHPExcel->getActiveSheet()->setAutoFilter('A1:' . $rows[$i] . (sizeof($header) - 1));
							} elseif ($tag_content == "nextsheet") {
								$new_sheet = true;
							} elseif ($tag_content == "autosize") {
								$objPHPExcel->getActiveSheet()->getColumnDimension($rows[$u])->setAutoSize(true);
							} elseif (substr($tag_content, 0, 9) == "sheetname") {
								$sheetname = str_replace("sheetname", "", $tag_content);
								$objPHPExcel->getActiveSheet()->setTitle($sheetname);
							} elseif ($tag_content == "landscape") {
								$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
							} elseif ($tag_content == "a4") {
								$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
							} elseif ($tag_content == "a3") {
								$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A3);
							} elseif ($tag_content == "fittopage") {
								$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
							} elseif ($tag_content == "fittowidth") {
								$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
							} elseif ($tag_content == "fittoheight") {
								$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(1);
							}



						}

						if ($style_set) {
							$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($styleArray);
						}

						// backwards compatibility


						if (strstr($line[$u], "@@@@HEXCOLOR")) {
							// Cell value contains color code
							$tmp = explode("@@@@HEXCOLOR", $line[$u]);
							$color = trim(str_replace("#", "", $tmp[1]));
							$value = $tmp[2];
							$line[$u] = $value;

							$objPHPExcel->getActiveSheet()->getStyle($cell)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
							$objPHPExcel->getActiveSheet()->getStyle($cell)->getFill()->getStartColor()->setARGB('FF' . $color);
						}



						if (strstr($line[$u], "@@@@WRAPPED@@@@")) {
							$line[$u] = str_replace("@@@@WRAPPED@@@@", "", $line[$u]);
							$objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setWrapText(true);
						} 
						if (strstr($line[$u], "@@@@BORDERS@@@@")) {
							$line[$u] = str_replace("@@@@BORDERS@@@@", "", $line[$u]);
							$objPHPExcel->getActiveSheet()->getStyle($cell)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
							$objPHPExcel->getActiveSheet()->getStyle($cell)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
							$objPHPExcel->getActiveSheet()->getStyle($cell)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
							$objPHPExcel->getActiveSheet()->getStyle($cell)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

						}
						if (strstr($line[$u], "@@@@STANDING@@@@")) {
							$line[$u] = str_replace("@@@@STANDING@@@@", "", $line[$u]);
							$objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setTextRotation(90);
						} else {
							unset($standing);
						}

						if (is_numeric($line[$u])) {
							$objPHPExcel->getActiveSheet()->getCell($cell)->setValueExplicit($line[$u], PHPExcel_Cell_DataType::TYPE_NUMERIC);
						} elseif (IsFormattedDate($line[$u])) {
							
							$date = date('U', NLDate2Epoch(FormattedDateToNLDate($line[$u]))) + 86300;

							$objPHPExcel->getActiveSheet()->setCellValue($cell, $date);
							$objPHPExcel->getActiveSheet()->setCellValue($cell, PHPExcel_Shared_Date::PHPToExcel($date));

							if ($GLOBALS['UC']['DateFormat'] == "mm-dd-yyyy") {
								$objPHPExcel->getActiveSheet()->getStyle($cell)->getNumberFormat()->setFormatCode('mm-dd-yyyy');
							} elseif ($GLOBALS['UC']['DateFormat'] == "dd-mm-yyyy") {
								$objPHPExcel->getActiveSheet()->getStyle($cell)->getNumberFormat()->setFormatCode('dd-mm-yyyy');
							} elseif ($GLOBALS['UC']['DateFormat'] == "yyyy-mm-dd") {
								$objPHPExcel->getActiveSheet()->getStyle($cell)->getNumberFormat()->setFormatCode('yyyy-mm-dd');
							} else {
								log_msg("ERROR::Dateformat not correct! (excel2007)");
							}
							unset($date);

						} else {
							$objPHPExcel->getActiveSheet()->setCellValue($cell, $line[$u]);
						}

						$objPHPExcel->getActiveSheet()->getRowDimension($u)->setRowHeight(-1);

						unset($color);
						unset($wrapped);
						
						if ($new_sheet) {
							$sheet++;
							$objPHPExcel->createSheet();  // Create a new sheet and set it as the active sheet
							unset($new_sheet);

							$objPHPExcel->setActiveSheetIndex($sheet);
							$objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setName($default_font);
							$objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize($default_size); 
							
							qlog(INFO, "Set auto-resize....");
							// Set all columns to auto-resize by default
							$scol = 'A';
							foreach ($header AS $dummy) {
								$scol++;
							}
							$scol++;
							for ($col = 'A'; $col != $scol; $col++) {
								$objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
							}
							// Correct the row-counter!
							$sheetCorrection = ($i+1);
						}

					//}
			}
	}





	


	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
	

	switch ($format) {
		case "xlsx": 
			// Save Excel 2007 file
			$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
			$filename .= ".xlsx";
			header('Content-Disposition: attachment;filename="' . $filename . '"');
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		break;
		case "pdf":
			$objWriter = new PHPExcel_Writer_PDF($objPHPExcel);
			$objWriter->writeAllSheets();
			$filename .= ".pdf";
			header('Content-Disposition: attachment;filename="' . $filename . '"');
			header('Content-Type: application/pdf');

			break;
		case "xls": 
			$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
			$filename .= ".xls";
			header('Content-Disposition: attachment;filename="' . $filename . '"');
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			break;
		
	}
	
	
	header('Cache-Control: max-age=0');

	qlog(INFO, "Start save....");
	$objWriter->save('php://output');
	
	qlog(INFO, "Excel2007 sheet generated - quit");

}


function ParseCellTags($phpObjExcel, $cell, $cellvalue) {
}
?>