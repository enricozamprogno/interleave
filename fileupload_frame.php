<?php

/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is the core of Interleave. It is always needed. It contains only functions.
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
require_once("initiate.php");


$_REQUEST['keeplocked'] = true;
$_REQUEST['nonavbar'] = 1;
//ShowHeaders();
PrintHTMLHeader();
DisplayCSS();
PrintHeaderJavascript();
print "</head><body class=\"fileuploadbox\"><div id=\"MainFileUploadBoxContents\">";
$eid = $_REQUEST['eid'];
$flextableid = $_REQUEST['flextableid'];
$cust = $_REQUEST['Cust'];

$acc = false;

if ($flextableid) {
	if ($_REQUEST['flextablerecord']) {
		$eid = $_REQUEST['flextablerecord'];
	}
	if (CheckFlexTableAccess($flextableid) == "ok") {
		$acc = IsValidFlexTableRecord($eid, $flextableid);
	}

} elseif ($cust) {
	if (CheckCustomerAccess($eid) == "ok") {
		$acc = IsValidCID($eid);
	}
} elseif ($eid == 0 && is_administrator()) {

	$acc = true;

} elseif ($eid) {
	if (CheckEntityAccess($eid) == "ok") {
		$acc = IsValidEID($eid);
	} else {
		$cl = GetClearanceLevel();
		if (in_array("CommentsAdd", $cl) && CheckEntityAccess($eid) == "readonly") {
			$acc = IsValidEID($eid);
			$limited = true;
			}
	}
} else {
	PrintAD("Invalid input");
	EndHTML();
	exit;
}




if ($acc) {
	if ($_POST['newfolder'] != "") {
		if ($_REQUEST['flextableid']) {
			$t = CheckFlexTableAccess($_REQUEST['flextableid']);
			$type = "flextable" . $_REQUEST['flextableid'];
		} elseif ($_REQUEST['Cust']) {
			$t = CheckCustomerAccess($eid);
			$type = "cust";
		} elseif ($eid == 0 && is_administrator()) {
			$t = "ok";
			$type = "entity";
		} elseif (CheckEntityAccess($eid) == "readonly"){
			$cl = GetClearanceLevel();
			if (in_array("CommentsAdd", $cl)) {
				$t = "ok";
				$type = "entity";
				$limited = true;
			}

		} else {
			$t = CheckEntityAccess($eid);
			$type = "entity";
		}
		if ($t == "ok") {
			$num = CreateFolder($eid, $type, $_POST['newfolder'], $_REQUEST['folder']);
			if (!is_numeric($_REQUEST['folder'])) {
				$_REQUEST['folder'] = PushStashValue(array($num));
			} else {
				$path = PopStashValue($_REQUEST['folder']);
				$path[] = $num;
				UpdateStashValue($_REQUEST['folder'], $path);
			}

		}
	}

	$always_in_sql = " AND folder='" . $_REQUEST['folder'] . "'";
	if ($_FILES['userfile']['tmp_name']) {
		//  A file was attached

		//print_r($_FILES);
		//print mb_detect_encoding(file_get_contents($tmpfile));
		//exit;

		// Read contents of uploaded file into variable
		//print "Attching file " . $_FILES['userfile']['name'] . " to entity " . $eid;

		if (count($_FILES['userfile']['name']) > 1) $multiple = true;

		for ($tel=0;$tel<sizeof($_FILES['userfile']['name']);$tel++) {

			$tmpfile = $_FILES['userfile']['tmp_name'][$tel];
			$size    = $_FILES['userfile']['size'][$tel];
			$type    = $_FILES['userfile']['type'][$tel];
			$name    = $_FILES['userfile']['name'][$tel];
			
			if (is_numeric($flextableid)) {
				$ttype = "flextable" . $flextableid;
			} elseif ($cust) {
				$ttype = "cust";
			} else {
				$ttype = "entity";
			}

			if ($name != "") {


				// If there are multiple files, new versions of cannot be detected in the form, so we do it here
				if ($multiple) {
					$_REQUEST['NewOrNewVersionOf'] = db_GetValue("SELECT fileid FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles WHERE filename='" . mres($name) . "' AND koppelid='" . $eid . "' AND type='" . $ttype . "' AND (version_belonging_to=0 OR version_belonging_to=fileid)" . $always_in_sql);

				} elseif ($_REQUEST['NewOrNewVersionOf'] == "") {
					$_REQUEST['NewOrNewVersionOf'] = 0;
				}

				// The AttachFile function itself is authenticated, no need to check security here
				$x = AttachFile($eid,$name,$tmpfile, $ttype,$type,false,false,true, $_REQUEST['folder']);
		
				ExtractIndexData($x, false);


				if ($_REQUEST['NewOrNewVersionOf'] <> 0 && $GLOBALS['ENABLEFILEVERSIONING'] == "Yes") {
					// This is not an ordinary new file, it's a new version of an existing file!
					// The new file will have 'version_belonging_to' = 0, but the version must be set and all earlier
					// files must be made child of this one.
					$t = db_GetRow("SELECT version_no FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles WHERE fileid='" . mres($_REQUEST['NewOrNewVersionOf']) . "'");
					$newversion = $t[0] + 1;
					mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "binfiles SET version_belonging_to=" . $x . ", timestamp_last_change=timestamp_last_change WHERE version_belonging_to='" . mres($_REQUEST['NewOrNewVersionOf']) . "'", $db);
					mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "binfiles SET version_belonging_to=" . $x . ", timestamp_last_change=timestamp_last_change WHERE fileid='" . mres($_REQUEST['NewOrNewVersionOf']) . "'", $db);
					mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "binfiles SET version_no=" . $newversion . ", version_belonging_to=0, timestamp_last_change=timestamp_last_change WHERE fileid=" . $x, $db);
			
				} else {
					
				}
				
			}				
		}



		if ($_REQUEST['divid']) {
			$ret .= 'parent.refresh_' . $_REQUEST['divid'] . '("&folder=' . htme($_REQUEST['folder']) . '");';
		}
		?>
			<script type="text/javascript">
			<!--
				<?php echo $ret;?>
			//-->
			</script>
		<?php
		exit;
	} else {
		qlog(INFO, "No file received!");
	}

	unset($t);
	if ($_REQUEST['flextableid']) {
		$t = CheckFlexTableAccess($_REQUEST['flextableid']);
		$type = "flextable" . $_REQUEST['flextableid'];
	} elseif ($_REQUEST['Cust']) {
		$t = CheckCustomerAccess($eid);
		$type = "cust";
	} elseif ($eid == 0 && is_administrator()) {
		$t = "ok";
		$type = "entity";
	} elseif (CheckEntityAccess($eid) == "readonly"){
		$cl = GetClearanceLevel();
		if (in_array("CommentsAdd", $cl)) {
			$t = "ok";
			$type = "entity";
			$limited = true;
		}

	} else {
		$t = CheckEntityAccess($eid);
		$type = "entity";
	}
	if ($t == "readonly") {
		$roins = "disabled='disabled'";
	} elseif ($t == "nok") {
		PrintAD("You don't have access to this information (fu)");
		EndHTML(false);
		exit;
	} elseif ($t == "ok") {
		// nothing
	} else {
		PrintAD("Could not determine access right, defaulting to 'no access'");
		EndHTML(false);
		exit;
	}

	
	$filebox = "<div class='showinline nwrp'><form id='UploadFile' method='post' action='fileupload_frame.php' enctype='multipart/form-data'>";
	$filebox .= "<input type='hidden' name='VersionTypeChanged' value='no'><input type='hidden' name='folder' value='" . htme($_REQUEST['folder']) . "'><input type='hidden' name='eid' value='" . $eid . "'><input type='hidden' name='divid' value='" . $_REQUEST['divid']. "'><input id='JS_userfile' name='userfile[]' type='file' multiple='multiple' $roins ";

	if ($GLOBALS['ENABLEFILEVERSIONING'] == "Yes" && $e<>"_new_"  && !$limited) {
		$onchange .= "checkifversionexists();";
	}
	$onc = " onchange=\"parent.document.getElementById('WaitImageDiv').style.visibility='visible';" . $onchange . "document.forms['UploadFile'].submit();\"";
	$filebox .= $onc . ">";

	if ($GLOBALS['ENABLEFILEVERSIONING'] == "Yes" && $e<>"_new_" && !$limited) {
		$filebox .= "&nbsp;<select name='NewOrNewVersionOf' style='width: 150px;'  onmouseover=\"this.style.width='auto';this.focus();\" onchange=\"document.forms['UploadFile'].elements['VersionTypeChanged'].value='yes'\"><option value='0' id='newfile'>Auto choose</option><option value='0' id='newfile'>New file</option>";
		$listoffiles = db_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles WHERE type='" . $type . "' AND koppelid='" . mres($eid) . "'" . $always_in_sql );
		foreach ($listoffiles AS $file) {
			if ($file['version_belonging_to'] == 0) {
				$filebox .= "<option value='" . $file['fileid'] . "' id='" . htme($file['filename']) . "'>" . $lang['newversionof'] . " " . fillout(htme($file['filename']),30) . "</option>";
			}
		}
		$filebox .= "</select>";
	} else {
		$filebox .= " &nbsp;";
	}
	if (GetSetting("AllowFoldersInFilelists-EXPIRIMENTAL") == "Yes") {
		$filebox .= " Add folder: <input type=\"text\" name=\"newfolder\" id=\"JS_newfolder\" size=\"15\" " . str_replace("checkifversionexists();", "", $onc) . ">";
	}

	if (is_numeric($flextableid)) {
		$filebox .= "<input type='hidden' name='flextableid' value='" . $flextableid . "'>";
	} elseif ($cust) {
		$filebox .= "<input type='hidden' name='Cust' value='true'>";
	}

	//$filebox .= "<input type='submit' value='Upload'></form></div>";

	print $filebox;
 
	?>
			<script type="text/javascript">
				function checkifversionexists() {
					if (document.forms['UploadFile'].elements['VersionTypeChanged'].value == 'no') {
						OrigVal = document.getElementById('JS_userfile').value;
						NewVal = OrigVal.replace("/","\\");
						NewValArray = NewVal.split("\\");
						var curOption = document.getElementById(NewValArray[NewValArray.length-1]);
						if (curOption)
						{
							curOption.selected = true;
							document.forms['UploadFile'].elements['NewOrNewVersionOf'].style.background = '#FFFF99';
						
						} else {
							var curOption = document.getElementById('newfile');
							curOption.selected = true;
							document.forms['UploadFile'].elements['NewOrNewVersionOf'].style.background = '#FFFFFF';
						}
					}
			}
			</script>
	<?php
} else {
	print "[no valid record found: " . $eid . " (ft:" . $flextableid . ") ]";
}
EndHTML();