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

require_once($GLOBALS['PATHTOINTERLEAVE'] . "pdf_inc2.php");

// Security
if (strtoupper($GLOBALS['UC']['HIDECUSTOMERTAB'])=="YES" && !is_administrator()) {
	PrintAD("Access to this page is denied");
} else {

	$date = date("F j, Y, H:i") . "h";



//	StartPDF();
$pdf=new PDF();
	$pdf->Open();
	$pdf->AliasNbPages();
	$pdf->AddPage();
	$pdf->SetFont('Times','',12);
	$pdf->SetFont('Arial','',8);

	qlog(INFO, "SID: " . $_REQUEST['stashid']);
	
	$pdf->Cell(0,0,$pdftitle2,0,1);
	$pdf->SetFont('Arial','',14);
	$pdf->Cell(0,10,$lang[customers],0,1);

	$maxcust = db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "customer");
	
	$a = array();
	$cdate = date('Y-m-d');

	if ($_REQUEST['stashid']) {
		$sql = PopStashValue($_REQUEST['stashid']);
		qlog(INFO, "Query fetched from database: " . $sql);
	} else {
		qlog(INFO, "No stashid found, exporting all.");
		$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "customer ORDER BY custname";
	}

	qlog(INFO, "Executing: " . $sql);
	print $sql;

	$result_customer= mcq($sql,$db);
	while ($pb= mysql_fetch_array($result_customer)) {
			$auth = CheckCustomerAccess($pb['id']);
			if ($auth!= "nok") {
				if ($fst) {
						$pdf->AddPage();
				} else {
						$fst = 1;
				}

				$pb['custname']			= fillout($pb['custname'],30);
				$pb['contact']			= fillout($pb['contact'],15);
				$pb['contact_phone']	= fillout($pb['contact_phone'],11);
				$pb['cust_homepage']	= fillout($pb['cust_homepage'],20);
				$pb['cust_address']		= fillout($pb['cust_address'],20);
				$pb['cust_remarks']		= fillout($pb['cust_remarks'],7);
				$pb['contact_email']	= fillout($pb['contact_email'],20);

				$pdf->Bookmark($pb['custname']);
				$pdf->SetFont('Arial','',10);
				$pdf->SetFillColor(0,0,128);
				$pdf->SetTextColor(255);
				$pdf->Cell(0,4,$lang['customer'] . " : " .          $pb['custname'],1,1,'L',1);
	//			$pdf->Cell(0,6,($list[$po]),1,1,'L',1);
				$pdf->SetFont('Arial','',8);
				$pdf->SetFillColor(0,0,0);
				$pdf->SetTextColor(128,0,0);
				$pdf->Cell(0,4,$lang['contact'] . " : ",0,1);
				$pdf->SetTextColor(0);
				$pdf->Cell(0,4,$pb['contact'],0,1);
				$pdf->SetTextColor(128,0,0);
				$pdf->Cell(0,4,$lang['contacttitle']. " : ",0,1);
				$pdf->SetTextColor(0);
				$pdf->Cell(0,4,$pb['contact_title'],0,1);
				$pdf->SetTextColor(128,0,0);
				$pdf->Cell(0,4,$lang['contactphone'] . " : ",0,1);
				$pdf->SetTextColor(0);
				$pdf->Cell(0,4,$pb['contact_phone'],0,1);
				$pdf->SetTextColor(128,0,0);
				$pdf->Cell(0,4,$lang['contactemail'] . " : ",0,1);
				$pdf->SetTextColor(0);
				$pdf->Cell(0,4,$pb['contact_email'],0,1);
				$pdf->SetTextColor(128,0,0);
				$pdf->Cell(0,4,$lang['customeraddress'] . " : ",0,1);
				$pdf->SetTextColor(0);
				$n = explode("\n",$pb['cust_address']);
				for ($n1=0;$n1<sizeof($n);$n1++) {
					$nt = wordwrap($n[$n1], 120, "HOPS!", 1);
					$nta = explode("HOPS!",$nt);
					for ($i=0;$i<sizeof($nta);$i++) {
						$pdf->Cell(0,4,trim($nta[$i]),0,1);
					}
				}
				//line();
				$pdf->SetTextColor(128,0,0);
				$pdf->Cell(0,4,$lang['custremarks'] . " : ",0,1);
				$pdf->SetTextColor(0);
				$n = explode("\n",$pb['cust_remarks']);
				for ($n1=0;$n1<sizeof($n);$n1++) {
					$nt = wordwrap($n[$n1], 120, "HOPS!", 1);
					$nta = explode("HOPS!",$nt);
					for ($i=0;$i<sizeof($nta);$i++) {
						$pdf->Cell(0,4,trim($nta[$i]),0,1);
					}
				}
				//line();
				$pdf->SetTextColor(128,0,0);
				$pdf->Cell(0,4,$lang['custhomepage'] . " : ",0,1);
				$pdf->SetTextColor(0);
				$pdf->Cell(0,4,$pb['cust_homepage'],0,1);
				// Extra fields list
			$list = GetExtraCustomerFields();
			if (sizeof($list)>1) {
				$pdf->Ln(6);
	//			line();
				$pdf->SetFont('Arial','',8);
				$data = array();
				//$header=array("Field","Value");
				foreach ($list AS $field) {


						$val = GetExtraCustomerFieldValue($pb['id'], $field['id'], false, false);

						line();
						$pdf->SetFont('Arial','',8);
						$pdf->SetFillColor(255,255,255);
						$pdf->SetTextColor(128,0,0);
						$pdf->Cell(0,6,($field['name']),0,1,'L',1);
						$pdf->SetFont('Arial','',8);
						$pdf->SetFillColor(0,0,0);
						$pdf->SetTextColor(0);
						//Have to convert custom fields to multiline
						$n = explode("\n",$val);

						for ($n1=0;$n1<sizeof($n);$n1++) {
							$nt = wordwrap($n[$n1], 120, "HOPS!", 1);
							$nta = explode("HOPS!",$nt);
							for ($i=0;$i<sizeof($nta);$i++) {
								$pdf->Cell(0,4,trim($nta[$i]),0,1);
							}
						}

				}

				unset($header);
				unset($data);
				unset($num);
				unset($list);
			}
		}


		$pdf->Ln(8);

			$sql = "SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles WHERE koppelid=" . $pb['id'] . " AND " . $GLOBALS['TBL_PREFIX'] . "binfiles.type='cust'";
			$result = mcq($sql,$db);
			$num = mysql_fetch_array($result);
//			qlog(INFO, "Number of files attached to this customer: " . $num[0]);
			if ($num[0]>0) {
				line();
				$pdf->SetFont('Arial','',10);
				$pdf->Cell(0,10,$lang['customer'] . " files:",0,1);
				$pdf->SetFont('Arial','',8);
				$data = array();
				$header=array("Creation date",'Size','User','Filename');
				if ($DateFormat=="mm-dd-yyyy") {
					$sql= "SELECT filename,timestamp_last_change,filesize,fileid,username,date_format(timestamp_last_change, '%m-%d-%Y %H:%i') AS dt FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles WHERE koppelid=" . $pb['id'] . " AND type='cust' ORDER BY filename";
				} else {
					$sql= "SELECT filename,timestamp_last_change,filesize,fileid,username,date_format(timestamp_last_change, '%d-%m-%Y %H:%i') AS dt FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles WHERE koppelid=" . $pb['id'] . " AND type='cust' ORDER BY filename";
				}
				$result7= mcq($sql,$db);
				while ($files= mysql_fetch_array($result7)) {
					$ownert = $files[username];
					$url = "http://" . $_SERVER['SERVER_NAME'] . $subdir . "csv.php?fileid=" . $files['fileid'];
					array_push($data,array($files['dt'],round(($files[filesize]/1024)). "K",$ownert,$url,$files['filename']));
					$ftel++;
					$tel++;
				}
					$pdf->FancyTable4colSinglePDF($header,$data);


			}
	}

	if ($_REQUEST['to_file']) {
		$pdf->Output($_REQUEST['to_file']);
	} else {
		$pdf->Output();
	}
	log_msg("Customer PDF export downloaded","");
} // end if access

EndHTML(false);
?>