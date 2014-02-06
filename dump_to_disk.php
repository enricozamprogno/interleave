<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file exports all contents of Interleave to disk
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
 print "Discontinued";
 exit;
require_once("initiate.php");

$EnableRepositorySwitcherOverrule="n";

if ($_REQUEST['wait']) {
			require("config/config-vars.php");

			// This is the code for the search-window with the animated gif
			// in it which appears only when searching for random text strings
			// when the SearchAttachments directive is set to 'Yes'
			print $GLOBALS['doctype'];
			print $GLOBALS['htmlopentag'];
			?>
			<head>
			<title>Exporting........................................................</title>
			</head>
			<body>
			<div>
			<table width='100%'><tr><td><center>

			<?php
				echo base64_decode($_REQUEST['opm']);
				?>
			<br><br>
			<img src='images/movingbar.gif' alt=''><br><br>
			Currently processing <?php echo $_REQUEST['start'];?> - <?php echo ($_REQUEST['start']+100);?> of <?php echo $_REQUEST['end'];?><br>
			</center></td></tr></table>
			</div>
			</body></html>
			<?php
			exit;
}
ShowHeaders();

AdminTabs();
MainAdminTabs("ieb");
SafeModeInterruptCheck();

$include=1;
require_once($GLOBALS['PATHTOINTERLEAVE'] . "sumpdf.php");

MustBeAdmin();

		print "<fieldset><legend>&nbsp;<img src='images/crmlogosmall.gif' alt=''>&nbsp;&nbsp; Dump all Interleave content to disk&nbsp;</legend>";

if (!$_REQUEST['step']) {
		print "<br>This procedure will export <strong>all</strong> entities (including files and customer information except for customer attachments) to a file/directory structure on the disk on the server.<br><br>";
		print "To be able to do this, you must create a directory called 'export' in your Interleave installation directory, and make sure your webserver has permission to write in this directory. <br><br>";
		print "Click <a class='arrow' href='dump_to_disk.php?step=1'>here</a> when you've done so.<br>";

} elseif ($_REQUEST['step']==1) {

		if (is_writeable("export/")) {

			print "Directory export/ found and is writeable.<br><br>";
			print "Click <a class='arrow' href='dump_to_disk.php?step=2'>here</a> to start the export.<br>";
			print "<br><strong>Interleave will export in batches; you will see the page refreshing several<br>times, depending on the size of your repository. This behaviour is normal.</strong><br>";

		} else {

			print "<img src='images/error.gif' alt=''>&nbsp;Directory export/ is not writeable, or totally not there. Click <a class='arrow' href='dump_to_disk.php?step=1'>here</a> to try again.<br>";

		}



} elseif ($_REQUEST['step']==2) {

		print "<pre>";
		print "Creating directory structure...\n";

		@mkdir("export/" . $GLOBALS['repository_nr']);

		$eids = array();

		$sql="SELECT DISTINCT(eid) FROM " . $GLOBALS['TBL_PREFIX'] . "entity";
		$output = mcq($sql,$db);
		$num = 0;
		while ($row = mysql_fetch_array($output)) {

			array_push($eids,$row['eid']);

			if (!is_dir(getcwd() . "/export/" . $GLOBALS['repository_nr'] . "/" . $row['eid'])) {
				@mkdir(getcwd() . "/export/" . $GLOBALS['repository_nr'] . "/" . $row['eid']);
				$num++;
			}
		}

		print $num . " directories created.\n";
		print "</pre>Click <a class='arrow' href='dump_to_disk.php?step=3&GeenPlaatjes=1'>here</a> to continue <br>";




}  elseif ($_REQUEST['step']==3) {
		$eids_total = array();
		$eids = array();

		$sql="SELECT DISTINCT(eid) FROM " . $GLOBALS['TBL_PREFIX'] . "entity";
		$output = mcq($sql,$db);
		while ($row = mysql_fetch_array($output)) {
			array_push($eids_total,$row['eid']);
		}

		$max = sizeof($eids_total);

		if (!$_REQUEST['start']) {
			$_REQUEST['start'] = 0;
		}

		$opm = base64_encode("Generating PDF summaries ...");

		?>
		<script type="text/javascript">
		<!--
			statusWin = window.open('dump_to_disk.php?wait=1&start=<?php echo $_REQUEST['start'];?>&end=<?php echo $max;?>&opm=<?php echo $opm;?>', 'statusWin' ,'width=400,height=130,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');
		//-->
		</script>

		<?php

		print "<pre>";

		print "\015\012\015\012Dumping entity contents in PDF format. This will take quite some time.";

		$subdir = str_replace("dump_to_disk.php","",$_SERVER['SCRIPT_NAME']);

		$sql="SELECT DISTINCT(eid) FROM " . $GLOBALS['TBL_PREFIX'] . "entity LIMIT " . $_REQUEST['start'] .",100";
		$output = mcq($sql,$db);
		while ($row = mysql_fetch_array($output)) {
			array_push($eids,$row['eid']);
		}

		foreach($eids as $eid) {
			$file = "export/" . $GLOBALS['repository_nr'] . "/" . $eid . "/EntityContents-" . $eid . ".pdf";
			StartPDF();
			$NoImageInclude=$GeenPlaatjes;
			CreateIndividualPDFSummary($eid);
			$date = date("F j, Y, H:i") . "h";
			$pdf->Output($file);
		}

		print "</pre>";

		$newstart = $_REQUEST['start'] + 100;

		if ($_REQUEST['start']<sizeof($eids_total)) {
			?>
			<script type="text/javascript">
			<!--
				document.location='dump_to_disk.php?step=3&GeenPlaatjes=<?php echo $_REQUEST['GeenPlaatjes'];?>&start=<?php echo $newstart;?>';
			//-->
			</script>
			<?php
		} else {
			?>
			<script type="text/javascript">
			<!--
				document.location='dump_to_disk.php?step=4';
			//-->
			</script>
			<?php
		}


} elseif ($_REQUEST['step']==4) {
	?>
	<script type="text/javascript">
	<!--
		statusWin = window.open('dump_to_disk.php?wait=1', 'statusWin' ,'width=400,height=130,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');
		statusWin.close();
		//-->
	</script>
	<?php

	print "Done creating PDF summaries.<br><br>";
	print "Click <a class='arrow' href='dump_to_disk.php?step=5'>here</a> to start the file export.<br>";
	?>
			<script type="text/javascript">
			<!--
				document.location='dump_to_disk.php?step=5';
			//-->
			</script>
			<?php

} elseif ($_REQUEST['step']==5) {

		$eids_total = array();
		$eids = array();

		$sql="SELECT DISTINCT(eid) FROM " . $GLOBALS['TBL_PREFIX'] . "entity";
		$output = mcq($sql,$db);
		while ($row = mysql_fetch_array($output)) {
			array_push($eids_total,$row['eid']);
		}

		$max = sizeof($eids_total);
		if (!$_REQUEST['start']) {
			$_REQUEST['start'] = 0;
		}

		$opm = base64_encode("Copying files from database to disk ...");


		?>
		<script type="text/javascript">
		<!--
			statusWin = window.open('dump_to_disk.php?wait=1&start=<?php echo $_REQUEST['start'];?>&end=<?php echo $max;?>&opm=<?php echo $opm;?>', 'statusWin' ,'width=400,height=130,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');
		//-->
		</script>

		<?php


		$sql="SELECT DISTINCT(eid) FROM " . $GLOBALS['TBL_PREFIX'] . "entity LIMIT " . $_REQUEST['start'] .",100";
		$output = mcq($sql,$db);
		while ($row = mysql_fetch_array($output)) {
			array_push($eids,$row['eid']);
		}

		print "<pre>";

		foreach($eids as $eid) {
			$sql = "SELECT filename, content FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles," . $GLOBALS['TBL_PREFIX'] . "blobs WHERE " . $GLOBALS['TBL_PREFIX'] . "binfiles.fileid=" . $GLOBALS['TBL_PREFIX'] . "blobs.fileid AND koppelid=" . $eid . " AND type='entity'";
			$result = mcq($sql,$db);
			print "Check $eid\015\012";
			while($row = mysql_fetch_array($result)) {
				$fp = fopen("export/" . $GLOBALS['repository_nr'] . "/" . $eid . "/" . $row['fileid'] . "-" . $row['filename'],"w");
				fputs($fp,$row['content']);
				fclose($fp);
			}

		}

		print "</pre>";

		$newstart = $_REQUEST['start'] + 100;

		if ($_REQUEST['start']<sizeof($eids_total)) {
			?>
			<script type="text/javascript">
			<!--
				document.location='dump_to_disk.php?step=5&start=<?php echo $newstart;?>';
			//-->
			</script>
			<?php
		} else {
			?>
			<script type="text/javascript">
			<!--
				document.location='dump_to_disk.php?step=6';
			//-->
			</script>
			<?php
		}




} elseif ($_REQUEST['step']==6) {
		?>
		<script type="text/javascript">
		<!--
			statusWin = window.open('dump_to_disk.php?wait=1', 'statusWin' ,'width=400,height=130,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');
			statusWin.close();
			//-->
		</script>
		<?php
		print "Done exporting files.<br><br>";
		print "Click <a class='arrow' href='dump_to_disk.php?step=7'>here</a> to create the root index file.<br>";
		?>
			<script type="text/javascript">
			<!--
				document.location='dump_to_disk.php?step=7';
			//-->
			</script>
			<?php

} elseif ($_REQUEST['step']==7) {

		$date = date("F j, Y, H:i") . "h";

		$txtfile = "Interleave Directory index created $date\015\012\015\012";

		$sql="SELECT eid,category,owner,assignee FROM " . $GLOBALS['TBL_PREFIX'] . "entity ORDER BY eid";
		$output = mcq($sql,$db);
		while ($row = mysql_fetch_array($output)) {
			$txtfile .= $row['eid'] . "/ - " . GetUsername($row['owner']) . " - " . GetUserName($row['assignee']) . " - " . $row['category'] . "\015\012";
		}
		$txtfile .= "\015\012 End of root index file\015\012";

		$fp = fopen("export/" . $GLOBALS['repository_nr'] . "/index.txt","w");
		fputs($fp,$txtfile);
		fclose($fp);

		print "Done creating index file.<br><br>";
		print "Click <a class='arrow' href='dump_to_disk.php?step=8'>here</a> to create the customer information.<br>";
		?>
			<script type="text/javascript">
			<!--
				document.location='dump_to_disk.php?step=8';
			//-->
			</script>
			<?php

} elseif ($_REQUEST['step']==8) {

		$date = date("F j, Y, H:i") . "h";

		print "<pre>";

		$sql="SELECT eid,CRMcustomer FROM " . $GLOBALS['TBL_PREFIX'] . "entity ORDER BY eid";
		$output = mcq($sql,$db);
		while ($row = mysql_fetch_array($output)) {
			$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "customer WHERE id=" . $row['CRMcustomer'];
			$result= mcq($sql,$db);
			$pb= mysql_fetch_array($result);
			$txtfile = "Interleave " . $lang['customer'] . " info " . $date . "\015\012\015\012";
			$txtfile .= $lang[customer] . " : " . $pb[custname] . "\015\012";
			$txtfile .= $lang[contact] . " : " . $pb[contact] . "\015\012";
			$txtfile .= $lang[contacttitle] . " : " . $pb[contact_title] . "\015\012";
			$txtfile .= $lang[contactphone] . " : " . $pb[contact_phone] . "\015\012";
			$txtfile .= $lang[contactemail] . " : " . $pb[contact_email] . "\015\012";
			$txtfile .= $lang[customeraddress] . " : " . str_replace("\n","\015\012",$pb[cust_address])  . "\015\012";

			$txtfile .= "\015\012";
			$txtfile .= "\015\012 End of " . $lang['customer'] . " file\015\012";

			$fp = fopen("export/" . $GLOBALS['repository_nr'] . "/" . $row['eid'] . "/customer.txt","w");
			//print "export/" . $GLOBALS['repository_nr'] . "/" . $row['eid'] . "/customer.txt\n";
			fputs($fp,$txtfile);
			fclose($fp);
			unset($txtfile);
			$num++;
		}

		?>
			<script type="text/javascript">
			<!--
				document.location='dump_to_disk.php?step=9';
			//-->
			</script>
			<?php

} elseif ($_REQUEST['step']==9) {

		// dump customer data

		$date = date("F j, Y, H:i") . "h";

		print "<pre>";

		$sql="SELECT id FROM " . $GLOBALS['TBL_PREFIX'] . "customer ORDER BY id";
		$output = mcq($sql,$db);
		$custpdf = 0;
		$custfiles = 0;
		while ($row = mysql_fetch_array($output)) {

			$path = "export/" . $GLOBALS['repository_nr'] . "/customer-" . $row['id'];
			mkdir($path);
			$filename = $path . "/Interleave-CustomerExport-" . $row['id'] . ".pdf";

			$stashid = PushStashValue("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "customer WHERE id=" . $row['id']);

			$custpdf++;

			?>
			<script type="text/javascript">
			<!--
				statusWin = window.open('customers.php?pdf=1&stashid=<?php echo $stashid;?>&to_file=<?php echo $filename;?>', 'statusWin' ,'width=400,height=130,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');
				//-->
			</script>
			<?php

			$sql= "SELECT filename,content FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles," . $GLOBALS['TBL_PREFIX'] . "blobs WHERE " . $GLOBALS['TBL_PREFIX'] . "binfiles.fileid=" . $GLOBALS['TBL_PREFIX'] . "blobs.fileid AND koppelid=" . $row['id'] . " AND type='cust' ORDER BY filename,timestamp_last_change";
			$result= mcq($sql,$db);

			while ($row2 = mysql_fetch_array($result)) {
				$fp = fopen($path . "/" . $row2['fileid'] . "-" . $row2['filename'],"w");
				fputs($fp,$row2['content']);
				fclose($fp);
				$custfiles++;
			}

		}

		?>
			<script type="text/javascript">
			<!--
				statusWin = window.open('index.php', 'statusWin' ,'width=400,height=130,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');
				statusWin.close();

				document.location='dump_to_disk.php?step=10&custpdf=<?php echo $custpdf;?>&custfiles=<?php echo $custfiles;?>';
			//-->
			</script>
			<?php


} elseif ($_REQUEST['step']==10) {

		// try to rename the directories to more readable names
		print "<pre>";
		$sql="SELECT eid,category FROM " . $GLOBALS['TBL_PREFIX'] . "entity ORDER BY eid";
		$output = mcq($sql,$db);
		while ($row = mysql_fetch_array($output)) {

			$cat = $row['category'];
			$cat = str_replace("\n","",$cat);
			$cat = str_replace("'","",$cat);
			$cat = str_replace("\"","",$cat);
			$cat = str_replace("&","",$cat);
			$cat = str_replace("~","",$cat);
			$cat = str_replace("`","",$cat);
			$cat = str_replace("#","",$cat);
			$cat = str_replace("$","",$cat);
			$cat = str_replace("%","",$cat);
			$cat = str_replace("^","",$cat);
			$cat = str_replace("\*","",$cat);
			$cat = str_replace(";","",$cat);
			$cat = str_replace(":","",$cat);
			$cat = str_replace("<","",$cat);
			$cat = str_replace(">","",$cat);
			$cat = str_replace("\?","",$cat);
			$cat = str_replace("/","",$cat);
			$cat = str_replace("\\","",$cat);
			$cat = str_replace(".","",$cat);
			$cat = str_replace("|","",$cat);
			$cat = str_replace("{","",$cat);
			$cat = str_replace("}","",$cat);
			$cat = str_replace("[","",$cat);
			$cat = str_replace("]","",$cat);
			$cat = str_replace("=","",$cat);
			$cat = str_replace("+","",$cat);

			@rename("export/" . $GLOBALS['repository_nr'] . "/" . $row['eid'],"export/" . $GLOBALS['repository_nr'] . "/" . $row['eid'] . " - " . $cat);
		}

		$sql="SELECT count(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity";
		$output = mcq($sql,$db);
		$eids_total = array();
		$row = mysql_fetch_array($output);
		$max = $row[0];

		$sql="SELECT count(*) FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles";
		$output = mcq($sql,$db);
		$eids_total = array();
		$row = mysql_fetch_array($output);
		$maxfiles = $row[0];




		print "$max directories created\n";
		print "$max PDF files created\n";
		print "$maxfiles files copied from database\n";
		print "$max customer information files created\n";
		print "$custpdf customer PDF exports created\n";
		print "$custfiles customer attachments exported\n";
		print "1 root index file created\n";
		print "</pre>";
		print "Interleave Export done. Please make sure to move the export for it is now world-readable.<br><br>";
}



print "</fieldset>";

EndHTML();