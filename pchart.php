<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * forgotpassword.php - handles users who lost their password
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */

require_once("initiate.php");

if (!$_REQUEST['GraphType'] && !$_REQUEST['ShowImage']) {
	ShowHeaders();
	AdminTabs("charts");
//	print "<pre>";
//	print_r(unserialize(GetSetting("STATISTICIMAGES")));

	$curimg = unserialize(GetSetting("STATISTICIMAGES"));
	if ($_REQUEST['Fetch']) {
		$fimg = $curimg[$_REQUEST['Fetch']];
	}

//	print "<pre>";
//	print_r($fimg);
//	print "</pre>";

	print "<form id='GraphTypeForm' method='get' action='pchart.php'><div class='showinline'><input type='hidden' name='GraphType' value='custom'>";
//	print "<table border='0' width='100%'><tr><td valign='top'>";
	print "<table width='100%'><tr><td valign='top' class='nwrp'>";
	$name = "By form";
	$addlink = " <a href='pchart.php?add=1&amp;type=form'>[new]</a>";
	print "<img class=\"expand\" title=\"INTERLEAVE" . md5($name) . "div\" style='cursor: pointer' src='images/t_plus.jpg' alt=''>&nbsp;&nbsp;" . $name . " " . $addlink;
	print "<div id='INTERLEAVE". md5($name) . "div' style='display: none;'><ul>";
	foreach ($curimg AS $name => $img) {
		if ($img['sqlquerytograph'] == "" && $img['phpcodetograph'] == "") {
			print "<li><a href='pchart.php?Fetch=" . $name . "'>" . base64_decode($name) . "</a></li>";
		}
	}

	print "</ul></div><br><br>";
	$name = "By SQL Query";
	$addlink = " <a href='pchart.php?add=1&amp;type=sql'>[new]</a>";
	print "<img class=\"expand\" title=\"INTERLEAVE" . md5($name) . "div\" style='cursor: pointer' src='images/t_plus.jpg' alt=''>&nbsp;&nbsp;" . $name . " ". $addlink . "<br>";
	print "<div id='INTERLEAVE". md5($name) . "div' style='display: none;'><ul>";
	foreach ($curimg AS $name => $img) {
		if ($img['sqlquerytograph'] != "") {
			print "<li><a href='pchart.php?Fetch=" . $name . "'>" . base64_decode($name) . "</a></li>";
		}
	}
	print "</ul></div><br>";

	/*
	$addlink = " <a href='pchart.php?add=1&amp;type=code'>[new]</a>";
	$name = "By PHP code";
	print "<img class=\"expand\" title=\"INTERLEAVE" . md5($name) . "div\" style='cursor: pointer' src='images/t_plus.jpg' alt=''>&nbsp;&nbsp;" . $name . " " . $addlink;
	print "<div id='INTERLEAVE". md5($name) . "div' style='display: none;'><ul>";
	foreach ($curimg AS $name => $img) {
		if ($img['phpcodetograph'] != "") {
			print "<li><a href='pchart.php?Fetch=" . $name . "'>" . base64_decode($name) . "</a></li>";
		}
	}
	print "</ul></div><br><br>";
	*/
	$name = "Standard images";
	print "<img class=\"expand\" title=\"INTERLEAVE" . md5($name) . "div\" style='cursor: pointer' src='images/t_plus.jpg' alt=''>&nbsp;&nbsp;" . $name . "";
	print "<div id='INTERLEAVE". md5($name) . "div' style='display: none;'>";



	print "<ul><li><a onclick=\"document.getElementById('TheActualGraph').src='pchart.php?GraphType=YearActivity&amp;Year=" . date('Y') . "';document.getElementById('URLToThisImage').value=document.getElementById('TheActualGraph').src;\">Repository year activity</a></li>";
	print "<li><a onclick=\"document.getElementById('TheActualGraph').src='pchart.php?GraphType=AddedDeletedPerWeek&amp;Year=" . date('Y') . "';document.getElementById('URLToThisImage').value=document.getElementById('TheActualGraph').src;\">Added and deleted entities per week</a></li>";
	print "<li><a onclick=\"document.getElementById('TheActualGraph').src='pchart.php?GraphType=AddedDeletedPerMonth&amp;Year=" . date('Y') . "';document.getElementById('URLToThisImage').value=document.getElementById('TheActualGraph').src;\">Added and deleted entities per month</a></li>";
	print "</ul>";
//	print "<input type='submit' name='DoGD'>";


	print "</div>";

	print "</td><td valign='top'>"; // LEFT FRAME TO RIGHT FRAME


	if (!$_REQUEST['Fetch'] && !$_REQUEST['add']) {
		$stimg = unserialize(GetSetting("STATISTICIMAGES"));
		if ($_REQUEST['DeletePresetImage']) {
			unset($stimg[$_REQUEST['DeletePresetImage']]);
			UpdateSetting("STATISTICIMAGES", serialize($stimg));
		}




		print "<table class='sortable'><thead><tr><td><strong>Name</strong></td><td><strong>#Views</strong></td><td><strong>Last view</strong></td><td><strong>By</strong></td><td><strong>URL</strong></td><td><strong>Delete</strong></td></tr></thead>";
		foreach ($stimg AS $desc => $arr) {
			print "<tr><td>" . base64_decode($desc) . "</td><td>" . $arr['views'] . "</td><td>" . date('Y-m-d H:m:s', $arr['lastview']) . "</td><td>" . GetUserName($arr['lastviewby']) . "</td><td><a href='#' onclick=\"document.getElementById('TheActualGraph').src='pchart.php?ShowImage=" . $desc . "';document.getElementById('URLToThisImage').value=document.getElementById('TheActualGraph').src;\">pchart.php?ShowImage=" . $desc . "</a></td><td><a href='pchart.php?DeletdddddePresetImage=" . $desc . "'><img src='images/delete.gif' alt=''></a></td></tr>";
		}
		print "</table><br>";
		print "<img id='TheActualGraph' src='images/crm.gif' style='border: 2px dotted #808080; padding: 10px; cursor: pointer;' alt=''>";
	}

	if ($_REQUEST['Fetch']) {
		$fimg = $curimg[$_REQUEST['Fetch']];

		if ($fimg['sqlquerytograph'] != "") {
			$_REQUEST['type'] = "sql";
		} elseif ($fimg['phpcodetograph'] != "") {
			$_REQUEST['type'] = "code";
		} else {
			$_REQUEST['type'] = "form";
		}

		$imagename = base64_decode($_REQUEST['Fetch']);

//		print "<pre>";
//		print_r($fimg);
	}

	if ($_REQUEST['type'] != "form") {
		print "<div id='composebyform' style='display:none'>";
	} else {
		print "<div id='composebyform'>";
	}
	print "<table>";
	print "<tr><td><select name='datafield2' id='datafield2_id' onchange='SetGraph();'>";
	$ins = ($fimg['datafield2'] == "general") ? "selected='selected'" : "";
	print "<option " . $ins . " value='general'>Occurrences of [entities]</option>";
	$ins = ($fimg['datafield2'] == "assignee") ? "selected='selected'" : "";
	print "<option " . $ins . " value='assignee'>Occurrences of [" . $lang['assignee'] . "]</option>";
	$ins = ($fimg['datafield2'] == "general") ? "selected='selected'" : "";
	print "<option " . $ins . " value='owner'>Occurrences of [" . $lang['owner'] . "]</option>";
	$ins = ($fimg['datafield2'] == "status") ? "selected='selected'" : "";
	print "<option " . $ins . " value='status'>Occurrences of [" . $lang['status'] . "]</option>";
	$ins = ($fimg['datafield2'] == "priority") ? "selected='selected'" : "";
	print "<option " . $ins . " value='priority'>Occurrences of [" . $lang['priority'] . "]</option>";
	$ins = ($fimg['datafield2'] == "customer") ? "selected='selected'" : "";
	print "<option " . $ins . " value='customer'>Occurrences of [" . $lang['entity'] . "]</option>";

	$ef = GetExtraFields(false, false);
	foreach ($ef AS $field) {
		$ins = ($fimg['datafield2'] == $field['id']) ? "selected='selected'" : "";

		if ($field['fieldtype'] == "numeric" || $field['fieldtype'] == "Computation" ) {
			print "<option " . $ins . " value='" . $field['id'] . "'>Sum of " . $field['name'] . "</option>";
		} elseif ($field['fieldtype'] != "diary" && $field['fieldtype'] != "text area" && $field['fieldtype'] != "text area (rich text)" ) {
			print "<option " . $ins . " value='" . $field['id'] . "'>Occurrences of [" . $field['name'] . "]</option>";
		}
	}
	$list = GetFlexTableDefinitions(false, "one-to-many");
	foreach ($list AS $ft) {


		$ef = GetExtraFlexTableFields($ft['recordid'], false);

		foreach ($ef AS $field) {
			$ins = ($fimg['datafield2'] == "FTF" . $field['id']) ? "selected='selected'" : "";
			if ($field['fieldtype'] == "numeric" || $field['fieldtype'] == "Computation" ) {
				print "<option " . $ins . " value='FTF" . $field['id'] . "'>[" . $ft['tablename'] . "] Sum of " . $field['name'] . "</option>";
			} elseif ($field['fieldtype'] != "diary" && $field['fieldtype'] != "text area" && $field['fieldtype'] != "text area (rich text)" ) {
				print "<option " . $ins . " value='FTF" . $field['id'] . "'>[" . $ft['tablename'] . "] Occurrences of [" . $field['name'] . "]</option>";
			}
		}
	}

	print "</select>&nbsp;" . ReturnDropDownSearchField("datafield2_id") . "</td><td align='center'>&nbsp;&nbsp;plotted against&nbsp;&nbsp;</td><td>";

	print "<select name='datafield1' onchange='SetGraph();' id='datafield1_id' >";
	$ins = ($fimg['datafield1'] == "duedate_month") ? "selected='selected'" : "";
	print "<option " . $ins . " value='duedate_month'>" . $lang['duedate'] . " (m)</option>";
	$ins = ($fimg['datafield1'] == "startdate_month") ? "selected='selected'" : "";
	print "<option " . $ins . " value='startdate_month'>" . $lang['startdate'] . " (m)</option>";
	$ins = ($fimg['datafield1'] == "closedate") ? "selected='selected'" : "";
	print "<option " . $ins . " value='closedate'>" . $lang['closedate'] . " (m)</option>";
	$ins = ($fimg['datafield1'] == "assignee") ? "selected='selected'" : "";
	print "<option " . $ins . " value='assignee'>" . $lang['assignee'] . "</option>";
	$ins = ($fimg['datafield1'] == "owner") ? "selected='selected'" : "";
	print "<option " . $ins . " value='owner'>" . $lang['owner'] . "</option>";
	$ins = ($fimg['datafield1'] == "status") ? "selected='selected'" : "";
	print "<option " . $ins . " value='status'>" . $lang['status'] . "</option>";
	$ins = ($fimg['datafield1'] == "priority") ? "selected='selected'" : "";
	print "<option" . $ins . " value='priority'>" . $lang['priority'] . "</option>";
	$ins = ($fimg['datafield1'] == "customer") ? "selected='selected'" : "";
	print "<option" . $ins . " value='customer'>" . $lang['customer'] . "</option>";
	$ef = GetExtraFields(false, false);
	foreach ($ef AS $field) {
		if ($field['fieldtype'] != "diary" && $field['fieldtype'] != "text area" && $field['fieldtype'] != "text area (rich text)" ) {
			$ins = ($fimg['datafield1'] == $field['id']) ? "selected='selected'" : "";
			print "<option " . $ins . " value='" . $field['id'] . "'>" . $field['name'] . "</option>";
		}
	}
	$list = GetFlexTableDefinitions(false, "one-to-many");
	foreach ($list AS $ft) {
		$ef = GetExtraFlextableFields($ft['recordid'], false);
		foreach ($ef AS $field) {
			$ins = ($fimg['datafield1'] == "FTF" . $field['id']) ? "selected='selected'" : "";
			if ($field['fieldtype'] == "numeric" || $field['fieldtype'] == "Computation" ) {
				print "<option " . $ins . " value='FTF" . $field['id'] . "'>[" . $ft['tablename'] . "] Sum of " . $field['name'] . "</option>";
			} elseif ($field['fieldtype'] != "diary" && $field['fieldtype'] != "text area" && $field['fieldtype'] != "text area (rich text)" ) {
				print "<option " . $ins . " value='FTF" . $field['id'] . "'>[" . $ft['tablename'] . "] Occurrences of [" . $field['name'] . "]</option>";
			}
		}
	}


	print "</select>&nbsp;" . ReturnDropDownSearchField("datafield1_id") . "</td></tr>";


	print "<tr><td>Must contain: <input name='datafield2filter' type='text' onchange='SetGraph();' value='" . htme($fimg['datafield2filter']) . "'></td><td></td><td>Must contain: <input name='datafield1filter' type='text' onchange='SetGraph();' value='" . htme($fimg['datafield1filter']) . "'></td></tr>";
	print "<tr><td colspan='4'><br><em>Use 'CURUSER' as filter value if you want to use the user-id of the user who is viewing the image. Also CURMONTH and CURYEAR can be used.</em></td></tr>";


	print "<tr><td colspan='3'>Extra filter on <select name='extrafilter' onchange='SetGraph();'><option value=''>- none -</option><option value='duedate'>Due date</option>";
	$ef = GetExtraFields();
	foreach ($ef AS $field) {
		$ins = ($fimg['extrafilter'] == $field['id']) ? "selected='selected'" : "";
		print "<option " . $ins . " value='" . $field['id'] . "'>" . $field['name'] . "</option>";
	}

	print "</select> must contain <input name='extrafiltervalue' type='text' onchange='SetGraph();' value='" . htme($fimg['extrafiltervalue']) . "'><br><br></td></tr>";
	print "<tr><td colspan='4'><em>Please note: filter values are case-sensative!</em></td></tr></table>";

	print "</div>";

	if ($_REQUEST['type'] != "sql") {
		print "<div id='composebysql' style='display: none;'>";
	} else {
		print "<div id='composebysql'>";
	}

	print "<table width='100%'>";

	if (is_administrator()) {
		print "<tr><td colspan='4' valign='top'>Query:<br><textarea name='sqlquery' rows='10' cols='80' onchange='SetGraph();'>" . htme($fimg['sqlquerytograph']) . "</textarea><br>(must return 2,3 or 4 columns; name and count, count, count)</td></tr>";
		print "<tr><td colspan='4'>Example:<pre>";
		print "\nSELECT\n CONCAT(MONTHNAME(FROM_UNIXTIME(openepoch)),' ',YEAR(FROM_UNIXTIME(openepoch))) AS tijdstip,\n SUM(IF(EFID2='Feature request',1,0)) AS `Added RFC's`,\n SUM(IF(EFID2='Bug',1,0)) AS `Added bugs`\n FROM " . $GLOBALS['TBL_PREFIX'] . "entity\n WHERE YEAR(FROM_UNIXTIME(openepoch))=2009 OR YEAR(FROM_UNIXTIME(openepoch))=2010 OR YEAR(FROM_UNIXTIME(openepoch))=2008 OR YEAR(FROM_UNIXTIME(openepoch))=2007\n GROUP BY tijdstip\n ORDER BY openepoch;";
		print "</pre>";
		print "</td></tr>";
	}
	print "</table>";

	print "</div>";
	if ($_REQUEST['type'] != "code") {
		print "<div id='composebycode' style='display: none;'>";
	} else {
		print "<div id='composebycode'>";
	}
	?>
			<script type="text/javascript" src="lib/editarea/edit_area/edit_area_full.js"></script>
			<script type="text/javascript">
			editAreaLoader.init({
				id : "phpcodetograph"			// textarea id
				,start_highlight: true	// if start with highlight
				,allow_resize: "both"
				,syntax_selection_allow: "css"
				,word_wrap: true
				,allow_toggle: true
				,language: "en"
				,syntax: "php"
			});
			</script>
			<?php
	print "<textarea id='phpcodetograph' name='phpcodetograph' rows='10' cols='150'>";
	if ($_REQUEST['add']) {
		print "// This code should return an array containing at least 2 and at most 4 columns\n";
		print "// Buildup like : array(\"Horizontal axis value\", \"key\" => \"value\" [,\"key\" => \"value\",\"key\" => \"value\"]);";
	} else {

		print htme($fimg['phpcodetograph']);
	}
	print "</textarea>";
	print "</div>";
	if ($_REQUEST['Fetch'] || $_REQUEST['add']) {

		print "<table width='100%'>";
		print "<tr class='nicerow'><td colspan='4' valign='top'>Image name</td><td><input type='text' name='save_image_name' size='80' onchange='SetGraph();' value='" . htme($imagename) . "'></td></tr>";
		print "<tr class='nicerow'><td colspan='4'>Height x width</td><td><input type='text' size='6' name='height' value='" . htme($fimg['height']) . "' onchange='SetGraph();'>&nbsp;<input type='text' size='6' name='width' value='" . htme($fimg['width']) . "' onchange='SetGraph();'></td></tr>";
		print "<tr class='nicerow'><td colspan='4'>Graph title</td><td><input type='text' name='g_title' value='" . htme($fimg['g_title']) . "' size='75' onchange='SetGraph();'></td></tr>";
		print "<tr class='nicerow'><td colspan='4'>Include deleted entities</td><td><select name='include_deleted' onchange='SetGraph();'>";
		if ($fimg['include_deleted'] == "y") {
			print "<option value='y'>Yes</option><option value='n'>No</option></select></td></tr>";
		} else {
			print "<option value='n'>No</option><option value='y'>Yes</option></select></td></tr>";
		}



		print "<tr class='nicerow'><td colspan='4'>Chart type</td><td><select name='chart_type' onchange='SetGraph();'>";
		$ins = ($fimg['chart_type'] == "bar") ? "selected='selected'" : "";
		print "<option " . $ins . " value='bar'>Bars</option>";
		$ins = ($fimg['chart_type'] == "stackedbar") ? "selected='selected'" : "";
		print "<option " . $ins . " value='stackedbar'>Stacked bars</option>";
		$ins = ($fimg['chart_type'] == "overlaybar") ? "selected='selected'" : "";
		print "<option " . $ins . " value='overlaybar'>Overlay bars</option>";
		$ins = ($fimg['chart_type'] == "line") ? "selected='selected'" : "";
		print "<option " . $ins . " value='line'>Line</option>";
		$ins = ($fimg['chart_type'] == "filledline") ? "selected='selected'" : "";
		print "<option " . $ins . " value='filledline'>Filled line</option>";
		$ins = ($fimg['chart_type'] == "curve") ? "selected='selected'" : "";
		print "<option " . $ins . " value='curve'>Curve</option>";
		$ins = ($fimg['chart_type'] == "filledcurve") ? "selected='selected'" : "";
		print "<option " . $ins . " value='filledcurve'>Filled curve</option>";
		$ins = ($fimg['chart_type'] == "pie") ? "selected='selected'" : "";
		print "<option " . $ins . " value='pie'>Pie chart</option>";
		print "</select></td></tr>";
		print "</table>";





	?>
		<script type="text/javascript">
		<!--
		function SetGraph() {
		}
			function RenderGraph() {
				document.getElementById('TheActualGraph').src='images/movingbar.gif';
				var url = "pchart.php?GraphType=custom&datafield1=" + document.forms['GraphTypeForm'].elements['datafield1'].value + "&datafield2=" + document.forms['GraphTypeForm'].elements['datafield2'].value + "&datafield1filter=" + document.forms['GraphTypeForm'].elements['datafield1filter'].value + "&datafield2filter=" + document.forms['GraphTypeForm'].elements['datafield2filter'].value + "&extrafilter=" + document.forms['GraphTypeForm'].elements['extrafilter'].value + "&extrafiltervalue=" + document.forms['GraphTypeForm'].elements['extrafiltervalue'].value + "&height=" + document.forms['GraphTypeForm'].elements['height'].value + "&width=" + document.forms['GraphTypeForm'].elements['width'].value + "&g_title=" + urlencodejs(document.forms['GraphTypeForm'].elements['g_title'].value) + "&include_deleted=" + document.forms['GraphTypeForm'].elements['include_deleted'].value + "&chart_type=" + document.forms['GraphTypeForm'].elements['chart_type'].value + "&sqlquerytograph=" + document.forms['GraphTypeForm'].elements['sqlquery'].value + "&phpcodetograph=" + document.forms['GraphTypeForm'].elements['phpcodetograph'].value+ '&save_image_name=' + document.forms['GraphTypeForm'].elements['save_image_name'].value;



				document.getElementById('TheActualGraph').src=url;
				if (document.forms['GraphTypeForm'].elements['save_image_name'].value == "" && document.forms['GraphTypeForm'].elements['sqlquery'].value == "")
				{
					document.getElementById('URLToThisImage').value=url;
				} else if (document.forms['GraphTypeForm'].elements['save_image_name'].value != "")
				{
					document.getElementById('URLToThisImage').value='pchart.php?ShowImage=' + encode64(document.forms['GraphTypeForm'].elements['save_image_name'].value);
				} else if (document.forms['GraphTypeForm'].elements['save_image_name'].value == "" && document.forms['GraphTypeForm'].elements['sqlquery'].value != "")
				{
					document.getElementById('URLToThisImage').value='Error: please give this image a name.' + document.forms['GraphTypeForm'].elements['save_image_name'].value;
				} else {
					document.getElementById('URLToThisImage').value=url;
				}


			}
			//-->
			</script>
		<?php
		print "<table width='100%'>";
		print "<tr class='nicerow'><td colspan='4'><strong>Output image (click image to render &amp; save)</strong></td></tr><tr><td>";
		print "<img id='TheActualGraph' src='images/crm.gif' onclick=\"document.getElementById('TheActualGraph').src='images/movingbar.gif';RenderGraph();\" style='border: 2px dotted #808080; padding: 10px; cursor: pointer;' alt=''>";
		print "</td></tr><tr class='nicerow'><td colspan='4'><strong>URL to this image</strong></td></tr><tr><td>";
		print "<input type='text' size='100' id='URLToThisImage'>";
		print "<br>Only users who have 'Management information' clearance can see this graph.</td></tr></table>";
	}
	print "</td></tr></table>";
	print "</div></form>";
	EndHTML();
	exit;

} else {

	$stimg = unserialize(GetSetting("STATISTICIMAGES"));

	if ($_REQUEST['save_image_name'] && is_administrator()) {
			$stimg[base64_encode($_REQUEST['save_image_name'])]['GraphType'] = $_REQUEST['GraphType'];
			$stimg[base64_encode($_REQUEST['save_image_name'])]['datafield1'] = $_REQUEST['datafield1'];
			$stimg[base64_encode($_REQUEST['save_image_name'])]['datafield2'] = $_REQUEST['datafield2'];
			$stimg[base64_encode($_REQUEST['save_image_name'])]['datafield1filter'] = $_REQUEST['datafield1filter'];
			$stimg[base64_encode($_REQUEST['save_image_name'])]['datafield2filter'] = $_REQUEST['datafield1filter'];
			$stimg[base64_encode($_REQUEST['save_image_name'])]['extrafilter'] = $_REQUEST['extrafilter'];
			$stimg[base64_encode($_REQUEST['save_image_name'])]['extrafiltervalue'] = $_REQUEST['extrafiltervalue'];
			$stimg[base64_encode($_REQUEST['save_image_name'])]['height'] = $_REQUEST['height'];
			$stimg[base64_encode($_REQUEST['save_image_name'])]['width'] = $_REQUEST['width'];
			$stimg[base64_encode($_REQUEST['save_image_name'])]['g_title'] = $_REQUEST['g_title'];
			$stimg[base64_encode($_REQUEST['save_image_name'])]['include_deleted'] = $_REQUEST['include_deleted'];
			$stimg[base64_encode($_REQUEST['save_image_name'])]['chart_type'] = $_REQUEST['chart_type'];
			$stimg[base64_encode($_REQUEST['save_image_name'])]['sqlquerytograph'] = $_REQUEST['sqlquerytograph'];
			$stimg[base64_encode($_REQUEST['save_image_name'])]['phpcodetograph'] = $_REQUEST['phpcodetograph'];
			$query = $_REQUEST['sqlquerytograph'];
			$code = $_REQUEST['phpcodetograph'];
			UpdateSetting("STATISTICIMAGES", serialize($stimg));

	} elseif ($_REQUEST['ShowImage']) {
			$_REQUEST['sqlquerytograph'];
			$_REQUEST['GraphType']=            "custom";
			$_REQUEST['datafield1']=           $stimg[$_REQUEST['ShowImage']]['datafield1'];
			$_REQUEST['datafield2']=           $stimg[$_REQUEST['ShowImage']]['datafield2'];
			$_REQUEST['datafield1filter']=     $stimg[$_REQUEST['ShowImage']]['datafield1filter'];
			$_REQUEST['datafield1filter']=     $stimg[$_REQUEST['ShowImage']]['datafield2filter'];
			$_REQUEST['extrafilter']=          $stimg[$_REQUEST['ShowImage']]['extrafilter'];
			$_REQUEST['extrafiltervalue']=     $stimg[$_REQUEST['ShowImage']]['extrafiltervalue'];
			$_REQUEST['height']=               $stimg[$_REQUEST['ShowImage']]['height'];
			$_REQUEST['width']=                $stimg[$_REQUEST['ShowImage']]['width'];
			$_REQUEST['g_title']=              $stimg[$_REQUEST['ShowImage']]['g_title'];
			$_REQUEST['include_deleted']=      $stimg[$_REQUEST['ShowImage']]['include_deleted'];
			$_REQUEST['chart_type']=           $stimg[$_REQUEST['ShowImage']]['chart_type'];
			$query =					       $stimg[$_REQUEST['ShowImage']]['sqlquerytograph'];
			$code =				               $stimg[$_REQUEST['ShowImage']]['phpcodetograph'];

			$stimg[$_REQUEST['ShowImage']]['views']++;
			$stimg[$_REQUEST['ShowImage']]['lastview'] = date('U');
			$stimg[$_REQUEST['ShowImage']]['lastviewby'] = $GLOBALS['USERID'];
			UpdateSetting("STATISTICIMAGES", serialize($stimg));
			$_REQUEST['sqlquerytograph'] = ""; // Security



	} elseif (!is_administrator()) {
		$_REQUEST['sqlquerytograph'] = ""; // Security
	}


	if ($_REQUEST['GraphType'] == "custom") {

		if ($_REQUEST['extrafilter'] == "") $_REQUEST['extrafilter'] = false;
		if ($_REQUEST['extrafiltervalue'] == "") $_REQUEST['extrafiltervalue'] = false;
		if ($_REQUEST['datafield1filter'] == "") $_REQUEST['datafield1filter'] = false;
		if ($_REQUEST['datafield2filter'] == "") $_REQUEST['datafield2filter'] = false;

		CreateAnyEFGraph($_REQUEST['datafield1'],$_REQUEST['datafield2'],$_REQUEST['datafield1filter'],$_REQUEST['datafield2filter'],$_REQUEST['extrafilter'],$_REQUEST['extrafiltervalue'],false, $_REQUEST['height'], $_REQUEST['width'], $_REQUEST['g_title'], $_REQUEST['include_deleted'], $_REQUEST['chart_type'], $query, $code);
	} elseif ($_REQUEST['GraphType'] == "YearActivity") {
		YearActivity($_REQUEST['Year']);
	} elseif ($_REQUEST['GraphType'] == "AddedDeletedPerWeek") {
		AddDeletedPerWeek($_REQUEST['Year']);
	} elseif ($_REQUEST['GraphType'] == "AddedDeletedPerMonth") {
		AddDeletedPerWeek($_REQUEST['Year'], true);
	}

}





function CreateAnyEFGraph($datafield1, $datafield2, $fieldval1=false, $fieldval2=false, $datelimitfield=false, $datelimit=false, $includedeleted=false, $height=false, $width=false, $g_title=false, $include_deleted=false, $chart_type="bar", $query=false, $code=false) {
	global $lang;

	if ($g_title == "[auto]") unset($g_title);

	if ($include_deleted != "y") $sql_and_ins = " AND deleted<>'y'";

	if (strtoupper($fieldval1) == "CURUSER") { $fieldval1 = GetUserName($GLOBALS['USERID']); }
	if (strtoupper($fieldval2) == "CURUSER") { $fieldval2 = GetUserName($GLOBALS['USERID']); }
	if (stristr($fieldval1, "CURYEAR")) { $fieldval1 = str_replace("CURYEAR", date('Y'), $fieldval1); }
	if (stristr($fieldval2,"CURYEAR")) { $fieldval2 = str_replace("CURYEAR", date('Y'), $fieldval2); }
	if (stristr($fieldval1, "CURMONTH")) { $fieldval1 = str_replace("CURMONTH", date('m'), $fieldval1); }
	if (stristr($fieldval2, "CURMONTH")) { $fieldval2 = str_replace("CURMONTH", date('m'), $fieldval1); }

	$sql_ins = "WHERE 1=1 ";
	if ($code == "" && $query == "") {
		if ($datafield1 == "assignee") {
			if ($fieldval1) {
				$sql_ins = "AND FULLNAME LIKE '%" . mres($fieldval1) . "%'";
				$ins = " containing \"" . $fieldval1 . "\"";
			}
			$xvalues = db_GetFlatArray("SELECT FULLNAME FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers " . $sql_ins . " AND name NOT LIKE 'deleted_user%' ORDER BY FULLNAME");
			$fn1 = $lang['assignee'];
		} elseif ($datafield1 == "owner") {
			if ($fieldval1) {
				$sql_ins = "WHERE FULLNAME LIKE '%" . mres($fieldval1) . "%'";
				$ins = " containing \"" . $fieldval1 . "\"";
			}
			$xvalues = db_GetFlatArray("SELECT FULLNAME FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers " . $sql_ins . " AND name NOT LIKE 'deleted_user%' ORDER BY FULLNAME");
			$fn1 = $lang['owner'];
		} elseif ($datafield1 == "status") {
			if ($fieldval1) {
				$sql_ins = "WHERE varname LIKE '%" . mres($fieldval1) . "%'";
				$ins = " containing \"" . $fieldval1 . "\"";
			}
			$xvalues = db_GetFlatArray("SELECT varname FROM " . $GLOBALS['TBL_PREFIX'] . "statusvars " . $sql_ins . " ORDER BY listorder, varname");
			$fn1 = $lang['status'];
		} elseif ($datafield1 == "priority") {
			if ($fieldval1) {
				$sql_ins = "WHERE varname LIKE '%" . mres($fieldval1) . "%'";
				$ins = " containing \"" . $fieldval1 . "\"";
			}
			$xvalues = db_GetFlatArray("SELECT varname FROM " . $GLOBALS['TBL_PREFIX'] . "priorityvars " . $sql_ins . " ORDER BY listorder, varname");
			$fn1 = $lang['priority'];
		} elseif ($datafield1 == "customer") {
			if ($fieldval1) {
				$sql_ins = "WHERE " . $GLOBALS['TBL_PREFIX'] . "customer.custname LIKE '%" . mres($fieldval1) . "%'";
				$ins = " containing \"" . $fieldval1 . "\"";
			}
			$xvalues = db_GetFlatArray("SELECT custname FROM " . $GLOBALS['TBL_PREFIX'] . "customer " . $sql_ins . " ORDER BY custname");
			$fn1 = $lang['customer'];
		} elseif ($datafield1 == "duedate_month") {
			if ($fieldval1) {
				$sql_ins = "WHERE " . $GLOBALS['TBL_PREFIX'] . "entity.duedate LIKE '%" . mres($fieldval1) . "%'";
				$ins = " containing \"" . $fieldval1 . "\"";
			}
			$xvalues = db_GetFlatArray("SELECT DISTINCT(SUBSTR(duedate,4,7)) FROM " . $GLOBALS['TBL_PREFIX'] . "entity " . $sql_ins . " ORDER BY sqldate");
			$fn1 = $lang['duedate'];

		} elseif ($datafield1 == "startdate_month") {
			if ($fieldval1) {
				$sql_ins = "WHERE " . $GLOBALS['TBL_PREFIX'] . "entity.startdate LIKE '%" . mres($fieldval1) . "%'";
				$ins = " containing \"" . $fieldval1 . "\"";
			}
			$xvalues = db_GetFlatArray("SELECT DISTINCT(SUBSTR(startdate,4,7)) FROM " . $GLOBALS['TBL_PREFIX'] . "entity " . $sql_ins . " ORDER BY openepoch");
			$fn1 = $lang['startdate'];
		} elseif ($datafield1 == "closedate") {
			if ($fieldval1) {
				$sql_ins = "WHERE " . $GLOBALS['TBL_PREFIX'] . "entity.closedate LIKE '%" . mres($fieldval1) . "%'";
				$ins = " containing \"" . $fieldval1 . "\"";
			}
			$xvalues = db_GetFlatArray("SELECT DISTINCT(CONCAT(SUBSTR(closedate, 6,2), '-', SUBSTR(closedate,1,4))) FROM " . $GLOBALS['TBL_PREFIX'] . "entity " . $sql_ins . " ORDER BY closedate");
			$fn1 = $lang['closedate'];

		} elseif ($fieldval1 && substr($datafield1,0,3) != "FTF") {
			$ins = " containing \"" . $fieldval1 . "\"";
			$xvalues = db_GetFlatArray("SELECT DISTINCT(EFID" .$datafield1 . ") FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE EFID" . $datafield1 . " LIKE '%" . mres($fieldval1) . "%' ORDER BY EFID" . $datafield1);
			$fn1 = GetExtraFieldName($datafield1);
		} elseif (substr($datafield1,0,3) == "FTF") {

			$tmp = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE id='" . str_replace("FTF","", $datafield1) . "'");
			$field = $tmp['id'];

			$xvalues = db_GetFlatArray("SELECT DISTINCT(EFID" . str_replace("FTF", "", $datafield1) . ") FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $tmp['tabletype'] . " WHERE deleted='n' AND EFID" . str_replace("FTF", "", $datafield1) . " LIKE '%" . mres($fieldval1) . "%' ORDER BY EFID" . str_replace("FTF", "", $datafield1));


			$fn1 = GetExtraFieldName($tmp['name']);

		} else {
			$xvalues = db_GetFlatArray("SELECT DISTINCT(EFID" . $datafield1 . ") FROM " . $GLOBALS['TBL_PREFIX'] . "entity ORDER BY EFID" . $datafield1 . "");
			$fn1 = GetExtraFieldName($datafield1);
		}

	//	qlog(INFO, $xvalues);
		$counter = 0;
		$occurrences = array();
		$summ = array();
		if (is_numeric($datafield2)) {
			$fieldtype = GetExtraFieldType($datafield2);
		} elseif (substr($datafield2,0,3) == "FTF") {
			$fieldtype = GetExtraFieldType(str_replace("FTF", "", $datafield2));
		}

		// qlog(INFO, $xvalues);


		$t = array();
		foreach ($xvalues AS $val) {
	//		qlog(DEBUG, "process $val");

			if ($datafield1 == "assignee") {
				$t = db_GetFlatArray("SELECT DISTINCT(eid) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE assignee='" . mres(GetUserIDByFullname($val)) . "' " . $sql_and_ins . "");
			} elseif ($datafield1 == "owner") {
				$t = db_GetFlatArray("SELECT DISTINCT(eid) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE owner='" . mres(GetUserIDByFullname($val)) . "'" . $sql_and_ins . "");
			} elseif ($datafield1 == "status") {
				$t = db_GetFlatArray("SELECT DISTINCT(eid) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE status='" . mres($val) . "'" . $sql_and_ins . "");
			} elseif ($datafield1 == "priority") {
				$t = db_GetFlatArray("SELECT DISTINCT(eid) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE priority='" . mres($val) . "'" . $sql_and_ins . "");
			} elseif ($datafield1 == "duedate_month") {
				$t = db_GetFlatArray("SELECT DISTINCT(eid) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE SUBSTR(duedate,4,7)='" . mres($val) . "' " . $sql_and_ins . "");
			} elseif ($datafield1 == "startdate_month") {
				$t = db_GetFlatArray("SELECT DISTINCT(eid) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE SUBSTR(startdate,4,7)='" . mres($val) . "' " . $sql_and_ins . "");
			} elseif ($datafield1 == "closedate") {
				$t = db_GetFlatArray("SELECT DISTINCT(eid) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE CONCAT(SUBSTR(closedate, 6,2), '-', SUBSTR(closedate,1,4))='" . mres($val) . "' " . $sql_and_ins . "");
			} elseif ($datafield1 == "customer") {
				$t = db_GetFlatArray("SELECT DISTINCT(eid) FROM " . $GLOBALS['TBL_PREFIX'] . "entity, " . $GLOBALS['TBL_PREFIX'] . "customer WHERE " . $GLOBALS['TBL_PREFIX'] . "entity.CRMcustomer=" . $GLOBALS['TBL_PREFIX'] . "customer.id AND " . $GLOBALS['TBL_PREFIX'] . "customer.custname='" . mres($val) . "' " . $sql_and_ins . "");
			} elseif (substr($datafield1,0,3) == "FTF") {
				$field = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE id='" . mres(str_replace("FTF","", $datafield1)) . "'");

				$tmp = db_GetFlatArray("SELECT DISTINCT(recordid) AS eid FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $field['tabletype'] . " WHERE deleted='n' AND EFID" . str_replace("FTF","",$datafield1) . "='" . mres($val) . "'");

				foreach ($tmp AS $row) {
					$p = db_GetRow("SELECT refer FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $field['tabletype'] . " WHERE EFID" . str_replace("FTF","",$datafield1) . "='" . $row . "' AND deleted='n'");

					if (is_numeric($p['eid'])) {
						//array_push($t, $p['eid']);
						//print "<br>push " . $p['eid'];
					}
				}
	//			print "HIER:";
	//			print_r($t);


			} elseif (is_numeric($datafield1)) {
				$table = GetExtraFieldTableType($datafield1);
				$t = db_GetFlatArray("SELECT DISTINCT(eid) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE EFID" . $datafield1 . "='" . mres($val) . "' ORDER BY EFID" . $datafield1 . " ");

			}

			$occurrences[$counter] = "0";
			$summ[$counter] = "0";

			foreach ($t AS $eid) {
	//		qlog(DEBUG, "PROCESSING " . $eid . " ");

				if (is_numeric($datafield2)) {
					$table = GetExtraFieldTableType($datafield1);
					if ($table == "customer") {
						$id = "id";
					} else {
						$table = "entity";
						$id = "eid";
					}
					$o = db_GetRow("SELECT EFID" . $datafield2 . " AS value FROM " . $GLOBALS['TBL_PREFIX'] . $table . " WHERE " . $id . "='" . mres($eid) . "'");

					// qlog(INFO, "GRAPH: Choosing extra field");
				} elseif (substr($datafield2,0,3) == "FTF") {

					$field = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE id='" . mres(str_replace("FTF","", $datafield2)) . "'");

					$o = db_GetRow("SELECT EFID" . str_replace("FTF", "", $datafield2) . " AS value FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $field['tabletype'] . " WHERE refer='" . mres($eid) . "' AND deleted='n'");


				} elseif ($datafield2 == "assignee") {
					$o = db_GetRow("SELECT assignee AS value FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE eid='" . mres($eid) . "' " . $sql_and_ins);
					$o['value'] = GetUserName($o['value']);
					// qlog(INFO, "GRAPH: Choosing assignee");
				} elseif ($datafield2 == "owner") {
					$o = db_GetRow("SELECT owner AS value FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE eid='" . mres($eid) . "' " . $sql_and_ins);
					$o['value'] = GetUserName($o['value']);
				} elseif ($datafield2 == "status") {
					$o = db_GetRow("SELECT status AS value FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE eid='" . mres($eid) . "' " . $sql_and_ins);
				} elseif ($datafield2 == "priority") {
					$o = db_GetRow("SELECT priority AS value FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE eid='" . mres($eid) . "' " . $sql_and_ins);
				} elseif ($datafield2 == "general") {
					$o = db_GetRow("SELECT eid AS value FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE eid='" . mres($eid) . "' " . $sql_and_ins);

				} else {
					// qlog(INFO, "GRAPH: ERROR: Don't know what to choose!");
				}

				if ($datelimitfield) {
					if ($datelimitfield == "duedate") {
						$tmp = DB_GetRow("SELECT duedate FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE eid='" . mres($eid) . "' " . $sql_and_ins);
						if (strstr($tmp[0], $datelimit)) {
							$pass = true;
							// qlog(INFO, "GRAPH: Including value because " . $datelimit . " is in " . $tmp[0]);
						} else {
							$pass = false;
							// qlog(INFO, "GRAPH: Excluding value because " . $datelimit . " is NOT in " . $tmp[0]);
						}
					} elseif (is_numeric($datelimitfield)) {
						$tmp = GetExtraFieldValue($eid, $datelimitfield, false, true);
						if (strstr($tmp, $datelimit)) {
							$pass = true;
							// qlog(INFO, "GRAPH: Including value because " . $datelimit . " is in " . $tmp);
						} else {
							$pass = false;
							// qlog(INFO, "GRAPH: Excluding value because " . $datelimit . " is NOT in " . $tmp);
						}
					}
				} else {
					$pass = true;
				}

				if ($fieldval2) {
					if (!stristr($o['value'], $fieldval2)) {
						$pass = false;
						// qlog(INFO, "GRAPH: Excluding value because " . $o['value'] . " is NOT " . $fieldval2);
					} else {
						// qlog(INFO, "GRAPH: Including value because " . $o['value'] . " is " . $fieldval2);
					}
				} else {
				//	qlog(INFO, "GRAPH: No fieldval2 found");
				}

				if ($pass) {
					$occurrences[$counter] = $occurrences[$counter] + 1;
					if ($fieldtype == "numeric" || ($fieldtype == "Computation" && is_numeric($o['value'])) || ($fieldtype == "SQL Query" && is_numeric($o['value']))) {
						$summ[$counter] += $o['value'];
						//print "add " . $o['value'] . "<br>";
						$ins6 = "Sum of ";
					} elseif ($o['value']) {
						unset($ins6);
						$use_occur = true;
					}
					// qlog(INFO, "GRAPH: Pass " . $totalpass++ . " add to " . $xvalues[$counter]);
				} else {
					// qlog(INFO, "GRAPH: Pass denied: " . $totalpass++ . " add to " . $xvalues[$counter]);
				}
				$pass = false;
			}
			$counter++;
			// qlog(INFO, "GRAPH: =========================== Process row " . $xvalues[$counter]);
		}
					//exit; // HEIR EXIT

		if ($use_occur) {
			$series1 = $occurrences;
			$ins2 .= " occurrences";
	//		print "using occ";
		} else {
	//		print "using summ";
			$series1 = $summ;
		}
		if ($datelimit) {
			if (is_numeric($datelimitfield)) {
				$df = GetExtraFieldName($datelimitfield);
			} else {
				$df = $datelimitfield;
			}

			$ins .= " filter \"" . $datelimit . "\" on \"" . $df . "\"";
		}

		if ($fieldval2) {
			$ins2 .= " containing \"" . $fieldval2 . "\"";
		}
		if ($datafield2 == "assignee") {
			$fn2 = $lang['assignee'];
		} elseif ($datafield2 == "owner") {
			$fn2 = $lang['owner'];
		} elseif ($datafield2 == "status") {
			$fn2 = $lang['status'];
		} elseif ($datafield2 == "priority") {
			$fn2 = $lang['priority'];
		} elseif ($datafield2 == "general") {
			$fn2 = $lang['entity'];
		} else {
			$fn2 = GetExtraFieldName(str_replace("FTF", "", $datafield2));
		}
		if ($datafield1 == "assignee") {
			$fn1 = $lang['assignee'];
		} elseif ($datafield1 == "owner") {
			$fn1 = $lang['owner'];
		} elseif ($datafield1 == "status") {
			$fn1 = $lang['status'];
		} elseif ($datafield1 == "priority") {
			$fn1 = $lang['priority'];

		} elseif ($datafield1 == "duedate_month") {
			$fn1 = $lang['duedate'];

		} elseif ($datafield1 == "startdate_month") {
			$fn1 = $lang['startdate'];

		} elseif ($datafield1 == "closedate") {
			$fn1 = $lang['closedate'];

		} else {
			$fn1 = GetExtraFieldName($datafield1);
		}
		$series1name = $fn1;
		$series2name = $fn2;

		if (GetExtraFieldType($datafield1) == "Reference to FlexTable") {
			$tmp = GetExtraFields($datafield1, false, true);
			$flextable = $tmp[0]['options'];
		//	qlog(INFO, "GRAPH : flextable is " . $flextable);
			for ($w=0;$w<sizeof($xvalues);$w++) {
				$xvalues[$w] = GetParsedFlexRef($flextable, $xvalues[$w], true);
				// CRM entity dit werkt nog niet goed!
	//			$xvalues[$w] = "flextables are not yet supported" . $xvalues[$w];

			}
		} else {
		//	qlog(INFO, "GRAPH: =------------------------------------- $fn1 -" . GetExtraFieldType($datafield1));
		}



		$yaxis = $xvalues;

		if (!$g_title) {
			$g_title = $ins6 . "\"" . $fn2 . "\"" . $ins2 . " plotted against \"" . $fn1 . "\"" . $ins;
		}

		// qlog(INFO, $yaxis);
		// qlog(INFO, $series1);

	//exit;

	} elseif ($query || $code) {
		$q = $query;

		if (stristr($q, "DELETE ") || stristr($q, "TRUNCATE ") || stristr($q, "DROP ") || stristr($q, "SET ") || stristr($q, "ALTER ") || strtoupper(substr($q, 0, 7)) != "SELECT " || stristr($q, ";;")) {
			print "Error; query is wrong.";
		} else {

			unset($series2name);
			$series1 = array();
			$series2 = array();
			$series3 = array();
			$xaxis = array();
			$yaxis = array();
			$series1name = "";
			$series2name = "";
			$series3name = "";
			if (!$query && $code != "") {
				eval($code);
				$tmp = $result;
	
			} else {

				$tmp = db_GetArray($query);
			}


			foreach ($tmp AS $row) {
				$ic = 0;
				foreach ($row AS $name => $value) {

					if (!is_numeric($name) && $name != "") {
						if ($ic == 0) {
							$yaxis[] = $value;
						} elseif ($ic == 1) {
							$series1[] = $value;
							$series1name = $name;
						} elseif ($ic == 2) {
							$series2[] = $value;
							$series2name = $name;
						} elseif ($ic == 3) {
							$series3[] = $value;
							$series3name = $name;
						}
						$ic++;
					}
				}



			}


		}
		if ($series2name == "") unset($series2);
		if ($series3name == "") unset($series3);


	}

	DrawGraph($xaxis, $yaxis, $series1, $series2, $series3, $series1name, $series2name, $series3name, $g_title, $chart_type, GetExtraFieldName($datafield1), GetExtraFieldName($datafield2), $height, $width);
	EndHTML(false);
}





function AddDeletedPerWeek($year = false, $permonth = false) {
	global $lang;
	if (!$year) $year = date('Y');
	$series1 = array();
	$series2 = array();
	$yaxis = array();

	if ($permonth) {
		$tot = 13;
		$inc = "month";
	} else {
		$tot = 53;
		$inc = "week";
	}

	for ($weeknumber=0;$weeknumber<$tot;$weeknumber++) {
		array_push($yaxis, $weeknumber);
		$sql = "SELECT openepoch,closeepoch FROM " . $GLOBALS['TBL_PREFIX'] . "entity";
		$result= mcq($sql,$db);
		while ($e= mysql_Fetch_array($result)) {

				if ($e['openepoch']<>0) {
					$c_week = date("W", $e['openepoch']);
					$c_year = date("Y", $e['openepoch']);
					$c_month = date("n", $e['openepoch']);
					$t_week = date("W");
					if ($c_week == $weeknumber && $c_year == $year && !$permonth) {
						$thisweek++;
					} elseif ($c_month == $weeknumber && $c_year == $year && $permonth) {
						$thisweek++;
						//print "$c_week == $weeknumber && $c_year == $year <br>";
					}
				}


					if ($e['closeepoch']<>0) {
						$c_week = date("W", $e['closeepoch']);
						$c_year = date("Y", $e['closeepoch']);
						$c_month = date("n", $e['closeepoch']);
						$t_week = date("W");
						if ($c_week == $weeknumber && $c_year == $year && !$permonth) {
							$thisweek2++;
						} elseif ($c_month == $weeknumber && $permonth && $c_year == $year) {
							$thisweek2++;
						//print "$c_week == $weeknumber && $c_year == $year <br>";
						}
					}
			}
		if (!$thisweek2) { $thisweek2=0; }
		if (!$thisweek) { $thisweek=0; }

		array_push($series1,$thisweek);
		array_push($series2,$thisweek2);

		unset($thisweek);
		unset($thisweek2);
	}

	$series1name = "Added";
	$series2name = "Closed";

	$title = $lang['entities'] . " added and closed per " . $inc . " " . $year;
	DrawGraph($xaxis, $yaxis, $series1, $series2, $series3, $series1name, $series2name, $series3name, $title, "line", $inc, "#" . $lang['entities']);

}


function YearActivity($year = false) {

	if (!$year) $year = date('Y');

	$sql= "SELECT date_format(timestamp_last_change, '%Y%m') AS ts, COUNT(*) AS count FROM " . $GLOBALS['TBL_PREFIX'] . "journal WHERE date_format(timestamp_last_change, '%Y')='" . mres($year) . "' GROUP BY ts";

	$title = "Total repository activity: " . $GLOBALS['title'] . " " . $year;

	$series1name = "Journal hits per month";
	$series1 = array();
	$series2 = array();
	$yaxis = array();
	$result = mcq($sql,$db);
	while ($row = mysql_Fetch_array($result)) {
			array_push($series1, $row['count']);
			array_push($yaxis, $row['ts']);
	}

	$series2name = "Uselog hits per month";
	$sql= "SELECT date_format(timestamp_last_change, '%Y%m') AS ts, COUNT(*) AS count FROM " . $GLOBALS['TBL_PREFIX'] . "uselog WHERE date_format(timestamp_last_change, '%Y')='" . mres($year) . "' GROUP BY ts";


	$result = mcq($sql,$db);
	while ($row = mysql_Fetch_array($result)) {
			array_push($series2, $row['count']);
	}

	DrawGraph($xaxis, $yaxis, $series1, $series2, $series3, $series1name, $series2name, $series3name, $title, "line", "Month", "Hits", $_REQUEST['height'], $_REQUEST['width']);

}

?>
