<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file does several things :)
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
$_GET['SkipMainNavigation'] = true;
require_once("initiate.php");
 // This script handles editing of extra fields
ShowHeaders();


if ($_REQUEST['editextrafield'])
{
?>
	<script type="text/javascript" src="lib/editarea/edit_area/edit_area_full.js"></script>
	<script type="text/javascript">
	editAreaLoader.init({
		id : "computationTA"			// textarea id
		,start_highlight: true	// if start with highlight
		,allow_resize: "both"
		,allow_toggle: true
		,language: "en"
		,syntax: "php"
	});
	</script>
<?php
}
if (CheckFunctionAccess("ExtrafieldAdmin") == "nok")
{
	PrintAD("Access to this page/function denied.");
}
else
{
	AddBreadCrum("Extra field definitions");

	if (strstr($_REQUEST['tabletype'], "ft_"))
	{
		qlog(INFO, "This page is about a flextable");
		$flextable = true;
		$_REQUEST['tabletype'] = str_replace("ft_", "", $_REQUEST['tabletype']);
		$tabletype = $_REQUEST['tabletype'];
	} else {
		$tabletype = $_REQUEST['tabletype'];	
	}

	AdminTabs("ef");

	$flextables = GetFlexTableDefinitions();

	$availtables = array("Entity" => "extrafields.php?tabletype=entity&amp;ti=newentity","Customer" => "extrafields.php?tabletype=customer&amp;ti=customer", "User" => "extrafields.php?tabletype=user&amp;ti=user", "Group" => "extrafields.php?tabletype=group&amp;ti=group");

	foreach ($flextables AS $ft)
	{	
		
		if (GetAttribute("flextable", "ViewOnTable", $ft['recordid']) == "") {
			$availtables[$ft['tablename']] = "extrafields.php?tabletype=ft_" . $ft['recordid'] . "&amp;ti=" . urlencode($ft['tablename']);
		}
	}

	foreach ($flextables AS $ft)
	{
		if (GetAttribute("flextable", "ViewOnTable", $ft['recordid']) == "") {
			$availtables[$ft['tablename']] = "extrafields.php?tabletype=ft_" . $ft['recordid'] . "&amp;ti=new" . urlencode($ft['tablename']);
		}
	}



	if ($_REQUEST['editextrafield'] <> "new" && !$_REQUEST['ti'])
	{
		unset($navid);
	}

	print "<table style='width: 100%;'><tr><td valign='top' style='width: 1%;' class='nwrp'>";
	print "<div class='light-small'>Field name, id &amp; remarks:<br><form id='fsform1' method='post' action=''><div class='showinline'><img src='images/searchbox.png' alt='' class='search_img'><input class='search_input' type='search' onchange=\"document.forms['fsform1'].submit();\" name='fssearch' value='" . htme($_REQUEST['fssearch']) . "'>&nbsp;<input type='submit' name='Go' value='Go'></div></form></div>";
	print "<br><div class='light-small'>Field type &amp; options:<br><form id='fsform2' method='post' action=''><div class='showinline'><img src='images/searchbox.png' alt='' class='search_img'><input class='search_input' type='search' onchange=\"document.forms['fsform2'].submit();\" name='fssearchtype' value='" . htme($_REQUEST['fssearchtype']) . "'>&nbsp;<input type='submit' name='Go' value='Go'></div></form></div>";
	print "<div id=\"importef-efpage\"  class='light-small'><a href=\"admin.php?ImportExtraFields\">Import EF definitions</a></div>";


	if ($_REQUEST['fssearch'] != "" || $_REQUEST['fssearchtype'] != "")
	{
		if ($_REQUEST['fssearchtype'] != "")
		{
			$sres= db_GetArray("SELECT id, name, tabletype, tablename, fieldtype FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields LEFT JOIN " . $GLOBALS['TBL_PREFIX'] . "flextabledefs ON (tabletype=recordid) WHERE (fieldtype LIKE '%" . mres($_REQUEST['fssearchtype']) . "%' OR options LIKE '%" . mres($_REQUEST['fssearchtype']) . "%') AND deleted!='y'");

		}
		else
		{
			$sres= db_GetArray("SELECT id, name, tabletype, tablename, fieldtype FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields LEFT JOIN " . $GLOBALS['TBL_PREFIX'] . "flextabledefs ON (tabletype=recordid) WHERE (name LIKE '%" . mres($_REQUEST['fssearch']) . "%' OR remarks LIKE '%" . mres($_REQUEST['fssearch']) . "%' OR id='" . mres($_REQUEST['fssearch']) . "')  AND deleted!='y'");

		}
		if (count($sres) == 1 && !$_REQUEST['editextrafield'])
		{
			$_REQUEST['editextrafield'] = $sres[0]['id'];
			if ($sres[0]['tablename'])
			{
				$_REQUEST['tabletype'] = $sres[0]['tablename'];
			}
			else
			{
				$_REQUEST['tabletype'] = $sres[0]['tabletype'];
			}
		}
		elseif (count($sres) == 0)
		{
			print "<div class='light-small'>No fields found matching criteria</div>";
		}
	}


	$sresdiv = db_GetArray("SELECT id, name, tabletype, tablename, fieldtype, ordering FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields LEFT JOIN " . $GLOBALS['TBL_PREFIX'] . "flextabledefs ON (tabletype=recordid OR recordid IS NULL) WHERE deleted!='y' ORDER BY `ordering`");


	print "<ul class='normal'>";
	foreach ($availtables AS $name => $link)
	{
		print "<li>";
		$ftname = db_GetRow("SELECT recordid FROM " . $GLOBALS['TBL_PREFIX'] . "flextabledefs WHERE tablename='" . mres($name) . "'");
		if (($_REQUEST['tabletype'] == strtolower($name) || ($_REQUEST['tabletype'] == $ftname[0] && $ftname[0] != "")) && (!$_REQUEST['fssearch'] && !$_REQUEST['fssearchtype']))
		{
			$disp1 = "none";
			$disp2 = "inline";
		}
		else
		{
			$disp1 = "inline";
			$disp2 = "none";
		}

		print "<img class=\"expand\" title=\"f" . md5($name) . "div\" style='display: inline; cursor: pointer' src='images/t_plus.jpg' alt=''>&nbsp;&nbsp;<a href='" . $link . "'>" . htme($name) . "</a> <a href='" . $link . "&amp;editextrafield=new'>[new]</a>";
		if (count($sres) > 0)
		{
			foreach ($sres AS $res)
			{
				if ($res['tabletype'] == strtolower($name) || $res['tablename'] == $name)
				{
					if (!$nf)
					{
						print "<ul>";
					}
					$nf = true;
					if ($res['id'] == $_REQUEST['editextrafield'])
					{
							print "<li>" . htme($res['name']) . "</li>";
					}
					else
					{
						$link = "extrafields.php?editextrafield=" . $res['id'] . "&amp;tabletype=" . $res['tabletype'] . "&amp;fssearch=" . htme($_REQUEST['fssearch']) . "&amp;fssearchtype=" . htme($_REQUEST['fssearchtype']);
						print "<li>" . $res['id'] . ": <a " . PrintToolTipCode("Fieldtype: " . $res['fieldtype']) . " href='" . $link . "'>" . str_ireplace($_REQUEST['fssearch'], "<span class='underln'>" . $_REQUEST['fssearch'] . "</span>", $res['name']) . "</a></li>";
					}

				}
			}
			if ($nf)
			{
				unset($nf);
				print "</ul>";
			}
		}

		if (count($sresdiv) > 0)
		{
			foreach ($sresdiv AS $res)
			{

				if ($res['tabletype'] == strtolower($name) || $res['tablename'] == $name)
				{
					
					if ($_REQUEST['tabletype'] == $res['tabletype'] && (!$_REQUEST['fssearch'] && !$_REQUEST['fssearchtype']))
					{
						$disp = "block";
					}
					else
					{
						$disp = "none";
					}
					if (!$nf)
					{
						print "<div id='f". md5($name) . "div' style='display: " . $disp . ";'><ul>";
					}
					$nf = true;
					if ($res['id'] == $_REQUEST['editextrafield'])
					{
							print "<li>" . $res['name'] . "</li>";
					}
					else
					{
						$link = "extrafields.php?editextrafield=" . $res['id'] . "&amp;tabletype=" . $res['tabletype'] . "&amp;fssearch=" . htme($_REQUEST['fssearch']) . "&amp;fssearchtype=" . htme($_REQUEST['fssearchtype']);
						print "<li>" . $res['id'] . ": <a " . PrintToolTipCode("Fieldtype: " . $res['fieldtype']) . " href='" . $link . "'>" . $res['name'] . "</a></li>";
					}
				} else {
					
				}
			}
			if ($nf)
			{
				unset($nf);
				print "</ul></div>";
			}

			print "</li>";
		}
	}
	print "</ul></td><td valign='top'>";

	if ($_REQUEST['UpdateTemplates'])
	{
		$from = "#" . strtoupper(str_replace(" ", "_" , $_REQUEST['OldName'])) . "#";
		$to = "#" . strtoupper(str_replace(" ", "_" , $_REQUEST['NewName'])) . "#";
		FindAndReplaceInAllTemplates($from, $to);
		print "All template and module references to " . $from . " were changed to " . $to . ".<br>";
		$from = "@" . strtoupper(str_replace(" ", "_" , $_REQUEST['OldName'])) . "@";
		$to = "@" . strtoupper(str_replace(" ", "_" , $_REQUEST['NewName'])) . "@";
		FindAndReplaceInAllTemplates($from, $to);
		print "All template and module references to " . $from . " were changed to " . $to . ".<br>";
		EndHTML();
		exit;
	}

	$fieldtypes = array("", "diary", "drop-down","drop-down (multiselect)","drop-down (populate by code)","drop-down (populate by code multiselect)","checkbox","drop-down based on customer list of values","textbox","Computation (ajax autorefresh)", "text area","text area (rich text)","numeric","mail","hyperlink","comment","date","date/time","Booking calendar","Calendar planning group","VAT drop-down","List of values","Button","SQL Query","SQL Query (multiselect)","Computation","Reference to FlexTable","Reference to FlexTable (multiselect)","List of all active customers", "Customer contacts", "User-list of all CRM-CTT users","User-list of administrative CRM-CTT users", "List of all groups");

	natcasesort($fieldtypes);

	$profs = GetUserProfiles();
	foreach ($profs AS $profilearray)
	{
		array_push($fieldtypes, array("User-list of all users in group " . $profilearray[1], "Users of profile " . $profilearray[0]));
	}

	if (is_numeric($_REQUEST['tabletype']))
	{
		$t = GetExtraFlexTableFields($_REQUEST['tabletype']);
		foreach ($t AS $efield)
		{
			if (!$efield['copied'])
			{
				array_push($fieldtypes, array("Property copy of field " . $efield['name'], "[copyfield" . $efield['id'] . "]"));
			}
		}
	}
	elseif ($_REQUEST['tabletype'] == "entity")
	{
		$t = GetExtraFields();
		foreach ($t AS $efield)
		{
			if (!$efield['copied'])
			{
				array_push($fieldtypes, array("Property copy of field " . $efield['name'], "[copyfield" . $efield['id'] . "]"));
			}
		}
	}
	elseif ($_REQUEST['tabletype'] == "customer")
	{
		$t = GetExtraCustomerFields($_REQUEST['tabletype']);
		foreach ($t AS $efield)
		{
			if (!$efield['copied'])
			{
				array_push($fieldtypes, array("Property copy of field " . $efield['name'], "[copyfield" . $efield['id'] . "]"));
			}
		}
	}


	if ($_REQUEST['tabletype'])
	{

		if (is_numeric($_REQUEST['tabletype']))
		{
			$tname = GetFlexTableNames($_REQUEST['tabletype']);
			$tname = $tname[0];
		}
		else
		{
			$tname = $_REQUEST['tabletype'];
		}

		if ($_REQUEST['editextrafield'])
		{
			$tid = $_REQUEST['editextrafield'];
			$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafieldconditions WHERE efid='" . mres($tid) . "'";
			$t = db_GetArray($sql);

			if ($_REQUEST['editextrafield']<>"new") {
				if (sizeof($t) > 0)
				{
					$conditionstext .= "<tr><td>Conditions on show/hide</td><td><a class='arrow' href='javascript:PopExtrafieldConditionsChooser(" . $_REQUEST['editextrafield'] . ");'>select</a>&nbsp;&nbsp;<span class='noway'>[set]</span></td></tr>";
				}
				else
				{
					$conditionstext = "<tr><td>Conditions</td><td><a class='arrow' href='javascript:PopExtrafieldConditionsChooser(" . $_REQUEST['editextrafield'] . ");'>select</a>&nbsp;&nbsp;[none set]</td></tr>";
				}
			} else {
				$conditionstext = "<tr><td>Conditions</td><td>[none set, save first]</td></tr>";
			}
			$tid = $_REQUEST['editextrafield'];
			$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafieldrequiredconditions WHERE efid='" . mres($tid) . "'";
			$t = db_GetArray($sql);

			if ($_REQUEST['editextrafield']<>"new") {
				if (sizeof($t) > 0)
				{
					$requiredconditionstext .= "<tr><td>Conditions on requirement</td><td><a class='arrow' href='javascript:PopExtrafieldRequiredConditionsChooser(" . $_REQUEST['editextrafield'] . ");'>select</a> ]&nbsp;&nbsp;<span class='noway'>[set]</span></td></tr>";
				}
				else
				{
					$requiredconditionstext = "<tr><td>Conditions on requirement</td><td><a class='arrow' href='javascript:PopExtrafieldRequiredConditionsChooser(" . $_REQUEST['editextrafield'] . ");'>select</a>&nbsp;&nbsp;[none set]</td></tr>";
				}
			} else {
				$requiredconditionstext = "<tr><td>Conditions on requirement</td><td>[none set, save first]</td></tr>";
			}
		}


	if ($_REQUEST['submitted'] && $_REQUEST['tabletype'])
	{
		$arr1 = explode("\n", trim($_REQUEST['newoption']));
		$arr = array();

		foreach ($arr1 AS $arrfield)
		{
			if ($arrfield <> "")
			{
				array_push($arr, trim($arrfield));
			}
		}

		if ($arr[0] != "" && $arr[1] == "")
		{
			$arr = split(";", $arr[0]);
		}
		if ($_REQUEST['newtype'] == "drop-down based on customer list of values")
		{
			$options = $_REQUEST['options'];
		}
		else
		{
			$options = serialize($arr);
		}
		if ($_REQUEST['newtype'] == "textbox" || $_REQUEST['newtype'] == "numeric" || $_REQUEST['newtype'] == "mail" || $_REQUEST['newtype'] == "hyperlink" || $_REQUEST['newtype'] == "text area") {

			$Placeholder = GetAttribute("extrafield", "Placeholder", $_REQUEST['editextrafield']);
			
			if ($Placeholder == "") {
				SetAttribute("extrafield", "Placeholder", "{{none}}", $_REQUEST['editextrafield']);
			}
			$MaximumLength = GetAttribute("extrafield", "MaximumLength", $_REQUEST['editextrafield']);

			if ($MaximumLength == "") {
				SetAttribute("extrafield", "MaximumLength", "{{none}}", $_REQUEST['editextrafield']);
			}
		}
		if ($_REQUEST['newtype'] == "numeric") {

			$MaximumValue = GetAttribute("extrafield", "MaximumValue", $_REQUEST['editextrafield']);
			if ($MaximumValue == "") {
				SetAttribute("extrafield", "MaximumValue", "{{none}}", $_REQUEST['editextrafield']);
			}

			$MinimumValue = GetAttribute("extrafield", "MinimumValue", $_REQUEST['editextrafield']);
			if ($MinimumValue == "") {
				SetAttribute("extrafield", "MinimumValue", "{{none}}", $_REQUEST['editextrafield']);
			}

		}
		$CustomValidationFunctionPHP = GetAttribute("extrafield", "CustomValidationFunctionPHP", $_REQUEST['editextrafield']);
		if ($CustomValidationFunctionPHP == "") {
			SetAttribute("extrafield", "CustomValidationFunctionPHP", "{{none}}", $_REQUEST['editextrafield']);
		}
		$MustBeUnique = GetAttribute("extrafield", "MustBeUnique", $_REQUEST['editextrafield']);
		if ($MustBeUnique == "") {
			SetAttribute("extrafield", "MustBeUnique", "No", $_REQUEST['editextrafield'], array('No', 'Yes (whole table)', 'Yes (within refer)'));
		}
		$IgnoreUniqueValues = GetAttribute("extrafield", "IgnoreUniqueValues-CommaSeprated", $_REQUEST['editextrafield']);
		if ($IgnoreUniqueValues == "") {
			SetAttribute("extrafield", "IgnoreUniqueValues-CommaSeprated", "{{none}}", $_REQUEST['editextrafield']);
		}
		$IgnoreValueChangesWhenRecalculating = GetAttribute("extrafield", "IgnoreValueChangesWhenRecalculating", $_REQUEST['editextrafield']);
		if ($IgnoreValueChangesWhenRecalculating == "") {
			SetAttribute("extrafield", "IgnoreValueChangesWhenRecalculating", "No", $_REQUEST['editextrafield'], array('No','Yes'));
		}
		
		if ($_REQUEST['tabletype'] == "user") {
			$UserIsAllowedToEditExtraField = GetAttribute("extrafield", "UserIsAllowedToEditExtraField", $_REQUEST['editextrafield']);
			if ($UserIsAllowedToEditExtraField == "") {
				SetAttribute("extrafield", "UserIsAllowedToEditExtraField", "No", $_REQUEST['editextrafield'], array('No', 'Yes'));
			}
		}

		if ($_REQUEST['newtype'] == "Reference to FlexTable" || $_REQUEST['newtype'] == "Reference to FlexTable (multiselect)" || $_REQUEST['newtype'] == "List of all active customers") {
			
			
			$ExtraSelectCondition = GetAttribute("extrafield", "ExtraSelectCondition", $_REQUEST['editextrafield']);
			if ($ExtraSelectCondition == "") {
				SetAttribute("extrafield", "ExtraSelectCondition", "None", $_REQUEST['editextrafield']);
			}
			$SelectFromFlextableLinkText = GetAttribute("extrafield", "SelectFromFlextableLinkText", $_REQUEST['editextrafield']);
			if ($SelectFromFlextableLinkText == "") {
				SetAttribute("extrafield", "SelectFromFlextableLinkText", "[select]", $_REQUEST['editextrafield']);
			}

			$ShowSelectFromTablePopupIcon = GetAttribute("extrafield", "ShowSelectFromTablePopupIcon", $_REQUEST['editextrafield']);
			if ($ShowSelectFromTablePopupIcon == "") {
				SetAttribute("extrafield", "ShowSelectFromTablePopupIcon", "Yes", $_REQUEST['editextrafield'], array('No', 'Yes'));
			}

			if ($_REQUEST['newtype'] == "Reference to FlexTable" || $_REQUEST['newtype'] == "Reference to FlexTable (multiselect)") {

				$options = $_REQUEST['FlexTableReference'];
				$BlindReferenceFieldLayout = GetAttribute("extrafield", "BlindReferenceFieldLayout", $_REQUEST['editextrafield']);
				if ($BlindReferenceFieldLayout == "") {
					SetAttribute("extrafield", "BlindReferenceFieldLayout", "-- set blind reference field layout in extra field attributes --", $_REQUEST['editextrafield']);
				}
			}

		}

		if ($_REQUEST['newtype'] == "drop-down" || $_REQUEST['newtype'] == "VAT drop-down" || $_REQUEST['newtype'] == "drop-down (multiselect)")
		{
			unset($option);
			if (sizeof($arr)==0)
			{
				$dontgo = true;
			}
		}

		if ($_REQUEST['newtype'] == "text area")
		{
			$_REQUEST['options'] = $_REQUEST['boxsize1'] . ":" . $_REQUEST['boxsize2'] . ":" . $_REQUEST['ShowTimeDateInputClockThingy'];
			$options = $_REQUEST['options'];
		}

		if ($_REQUEST['newtype'] == "text area (rich text)")
		{
			$_REQUEST['options'] = $_REQUEST['boxsize3'] . ":" . $_REQUEST['boxsize4'] . ":" . $_REQUEST['ShowTimeDateInputClockThingy'];
			$options = $_REQUEST['options'];
		}

		if ($_REQUEST['newtype'] == "Computation" || $_REQUEST['newtype'] == "Computation (ajax autorefresh)")
		{
			$options = $_REQUEST['computation'];
		}

		if ($_REQUEST['newtype'] == "drop-down (populate by code)")
		{
			$_REQUEST['newtype'] = "drop-down";
			$options = "%POPULATE_BY_CODE%" . $_REQUEST['computation'];
			$RepopulateByAjax = GetAttribute("extrafield", "RepopulateByAjax", $_REQUEST['editextrafield']);
			if ($RepopulateByAjax == "") {
				SetAttribute("extrafield", "RepopulateByAjax", "Yes", $_REQUEST['editextrafield'], array("Yes", "No"));
			}

		}

		if ($_REQUEST['newtype'] == "drop-down (populate by code multiselect)")
		{
			$_REQUEST['newtype'] = "drop-down (multiselect)";
			$options = "%POPULATE_BY_CODE%" . $_REQUEST['computation'];
		}

		if ($_REQUEST['newtype'] == "comment")
		{
			$options = $_REQUEST['newHTMLtemplate'];
			$RefreshByAjaxOnChangeOfField = GetAttribute("extrafield", "RefreshByAjaxOnChangeOfField", $_REQUEST['editextrafield']);
			if ($RefreshByAjaxOnChangeOfField == "") {
				SetAttribute("extrafield", "RefreshByAjaxOnChangeOfField", "No", $_REQUEST['editextrafield']);
			}

		}
		if ($_REQUEST['newtype'] == "diary")
		{
			$AutoUpdateDiaryField = GetAttribute("extrafield", "AutoUpdateDiaryField", $_REQUEST['editextrafield']);
			if ($AutoUpdateDiaryField == "") {
				SetAttribute("extrafield", "AutoUpdateDiaryField", "Yes", $_REQUEST['editextrafield'], array("Yes", "No"));
			}
		}

		if ($_REQUEST['newhidden'] == "")
		{
			$_REQUEST['newhidden'] = "n";
		}

		if ($_REQUEST['newtype'] == "checkbox")
		{
			$_REQUEST['defaultval'] = $_REQUEST['cb_unchecked'];
			$options = $_REQUEST['cb_checked'];
		}

		if ($_REQUEST['SQLQUERY'] && ($_REQUEST['newtype'] == "SQL Query" || $_REQUEST['newtype'] == "SQL Query (multiselect)"))
		{
			$options = serialize(array($_REQUEST['SQLQUERYDB'],$_REQUEST['SQLQUERY']));
		}

		if ($_REQUEST['newtype'] == "Booking calendar")
		{
			$options = $_REQUEST['planoptions'];
		}

		if ($_REQUEST['newtype'] == "Calendar planning group")
		{
			$options = serialize($_REQUEST['planoptions']);
		}

		$israw = "n";
		if ($_REQUEST['IsRawHTML'] == "IsRawHTML")
		{
			$israw = "y";
		}

		if ($_REQUEST['defaultval'] && $_REQUEST['newtype'] == "drop-down (multiselect)")
		{
			$tmp = $_REQUEST['defaultval'];
			$_REQUEST['defaultval'] = array();
			array_push($_REQUEST['defaultval'], base64_encode($tmp));
			$_REQUEST['defaultval'] = serialize($_REQUEST['defaultval']);
		}

		if ($_REQUEST['limitddtowidth'] == "" && isset($_REQUEST['limitddtowidth']))
		{
			$_REQUEST['limitddtowidth'] = "0";
		}

		if ($_REQUEST['showasradio'] != "y") $_REQUEST['showasradio'] = "n";

		if ($_REQUEST['newtype'] == "Reference to FlexTable")
		{
			if ($_REQUEST['dontshowFTpopup'] != "y") $_REQUEST['dontshowFTpopup'] = "n";
			$_REQUEST['sortwhendisplayed'] = $_REQUEST['dontshowFTpopup'];
		}
		
		if ($_REQUEST['excludefromfilters'] != "y") { $_REQUEST['excludefromfilters'] = "n"; }
		if ($_REQUEST['underwaterfield'] != "y") { $_REQUEST['underwaterfield'] = "n"; }
		if ($_REQUEST['sum_column'] != "yes") { $_REQUEST['sum_column'] = "no"; }

		

		

		if ($dontgo <> true)
		{
			if ($_REQUEST['editextrafield'] == "new")
			{
				if ($_REQUEST['newtype'] == "Computation" || $_REQUEST['newtype'] == "SQL Query" || $_REQUEST['newtype'] == "SQL Query (multiselect)")
				{
					SafeModeInterruptCheck();
				}

				if ($_REQUEST['newtype'] != "") {

					$sql = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "extrafields(options,name,ordering,fieldtype,hidden,tabletype,forcing,excludefromfilters,defaultval,sort,storetype,size,showsearchbox,limitddtowidth,allowuserstoaddoptions,remarks,lastchangeby,timestamp_last_change,showasradio,displaylistname,underwaterfield,sum_column,number_format) VALUES ('" . mres($options) . "','" . mres($_REQUEST['newname']) . "','" . mres($_REQUEST['neworder']) . "','" . mres($_REQUEST['newtype']) . "','" . mres($_REQUEST['newhidden']) . "','" . mres($_REQUEST['tabletype']) . "','" . mres($_REQUEST['forcing']) . "','" . mres($_REQUEST['excludefromfilters']) . "','" . mres($_REQUEST['defaultval']) . "','" . mres($_REQUEST['sortwhendisplayed']) . "','" . mres($_REQUEST['storetype']) . "','" . mres($_REQUEST['boxsize']) . "','" . mres($_REQUEST['showsearchbox']) . "','" . mres($_REQUEST['limitddtowidth']) . "','" . mres($_REQUEST['allowuserstoaddoptions']) . "','" . mres($_REQUEST['fieldremarks']) . "','" . mres($GLOBALS['USERID']) . "', NOW(), '" . mres($_REQUEST['showasradio']) . "','" . mres($_REQUEST['newdisplaylistname']) . "','" . mres($_REQUEST['underwaterfield']) . "','" . mres($_REQUEST['sum_column']) . "','" . mres($_REQUEST['number_format']) . "')";
					mcq($sql,$db);
					$new_field_name = mysql_insert_id();
					$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "extrafields SET ordering='" . mres($new_field_name*10*1.234) . "' WHERE id=" . $new_field_name;
					mcq($sql,$db);

					
					if (is_numeric($_REQUEST['tabletype']))
					{
						$table = $GLOBALS['TBL_PREFIX'] . "flextable" . $_REQUEST['tabletype'];

						if ($_REQUEST['newtype'] == "date/time") {
							mcq("ALTER TABLE " . $table . " ADD `EFID" . $new_field_name . "` DATETIME NOT NULL", $db);
						} else {
							mcq("ALTER TABLE " . $table . " ADD `EFID" . $new_field_name . "` LONGTEXT NOT NULL", $db);
						}
						if ($_REQUEST['defaultval'])
						{
							mcq("UPDATE " . $table . " SET EFID" . $new_field_name . "='" . mres($_REQUEST['defaultval']) . "'", $db);
						}
						ConvertDDEFToENUM($new_field_name);
					}
					elseif ($_REQUEST['tabletype'] == "customer")
					{
						$table = $GLOBALS['TBL_PREFIX'] . "customer";

						if ($_REQUEST['newtype'] == "date/time") {
							mcq("ALTER TABLE " . $table . " ADD `EFID" . $new_field_name . "` DATETIME NOT NULL", $db);
						} else {
							mcq("ALTER TABLE " . $table . " ADD `EFID" . $new_field_name . "` LONGTEXT NOT NULL", $db);
						}

						if ($_REQUEST['defaultval'])
						{
							mcq("UPDATE " . $table . " SET EFID" . $new_field_name . "='" . mres($_REQUEST['defaultval']) . "'", $db);
						}
						ConvertDDEFToENUM($new_field_name);

					} elseif ($_REQUEST['tabletype'] == "entity")	{
						
						$table = $GLOBALS['TBL_PREFIX'] . "entity";

						if ($_REQUEST['newtype'] == "date/time") {
							mcq("ALTER TABLE " . $table . " ADD `EFID" . $new_field_name . "` DATETIME NOT NULL", $db);
						} else {
							mcq("ALTER TABLE " . $table . " ADD `EFID" . $new_field_name . "` LONGTEXT NOT NULL", $db);
						}

						if ($_REQUEST['defaultval'])
						{
							mcq("UPDATE " . $table . " SET EFID" . $new_field_name . "='" . mres($_REQUEST['defaultval']) . "'", $db);
						}
						ConvertDDEFToENUM($new_field_name);
					} elseif ($_REQUEST['tabletype'] == "user") {
						
						$table = $GLOBALS['TBL_PREFIX'] . "loginusers";

						if ($_REQUEST['newtype'] == "date/time") {
							mcq("ALTER TABLE " . $table . " ADD `EFID" . $new_field_name . "` DATETIME NOT NULL", $db);
						} else {
							mcq("ALTER TABLE " . $table . " ADD `EFID" . $new_field_name . "` LONGTEXT NOT NULL", $db);
						}

						if ($_REQUEST['defaultval'])
						{
							mcq("UPDATE " . $table . " SET EFID" . $new_field_name . "='" . mres($_REQUEST['defaultval']) . "'", $db);
						}
						ConvertDDEFToENUM($new_field_name);
					} elseif ($_REQUEST['tabletype'] == "group") {
						
						$table = $GLOBALS['TBL_PREFIX'] . "userprofiles";

						if ($_REQUEST['newtype'] == "date/time") {
							mcq("ALTER TABLE " . $table . " ADD `EFID" . $new_field_name . "` DATETIME NOT NULL", $db);
						} else {
							mcq("ALTER TABLE " . $table . " ADD `EFID" . $new_field_name . "` LONGTEXT NOT NULL", $db);
						}

						if ($_REQUEST['defaultval'])
						{
							mcq("UPDATE " . $table . " SET EFID" . $new_field_name . "='" . mres($_REQUEST['defaultval']) . "'", $db);
						}
						ConvertDDEFToENUM($new_field_name);
					}

					else
					{
						PrintAD("Wrong table type. Cowardly quitting.");
						exit;
					}

					RebuildViews();
				} else {
					PrintAD("You didn't select a field type. Field not saved.");
				}
			}
			else
			{
				if ($_REQUEST['newtype'] == "Computation"|| $_REQUEST['newtype'] == "SQL Query" || $_REQUEST['newtype'] == "SQL Query (multiselect)")
				{
					SafeModeInterruptCheck();
				}
				if ($_REQUEST['tabletype'] == "entity")
				{
					$t = GetExtraFields($_REQUEST['editextrafield'], true, false, true);
					if (trim($t[0]['name']) != trim($_REQUEST['newname']) && trim($t[0]['name']) != "")
					{
						print "<img src='images/error.gif' alt=''> Warning: the name of the field changed. Click <a href='extrafields.php?UpdateTemplates=1&amp;OldName=" . htme($t[0]['name']) . "&amp;NewName=" . htme($_REQUEST['newname']) . "' class='plainlink'>here</a> to update all template tags using this alias.<br>";
					}
				}
				$ef = db_GetRow("SELECT * FROM ". $GLOBALS['TBL_PREFIX'] . "extrafields WHERE id='" . mres($_REQUEST['editextrafield']) . "'");



				if ($ef['fieldtype'] == "drop-down" && $_REQUEST['newtype'] != "drop-down") {

					// It WAS a drop down, but not anymore, convert to LONGTEXT

					if ($ef['tabletype'] == "entity") {
						$table = $GLOBALS['TBL_PREFIX'] . "entity";
					} elseif ($ef['tabletype'] == "customer") {
						$table = $GLOBALS['TBL_PREFIX'] . "customer";
					} elseif ($ef['tabletype'] == "user") {
						$table = $GLOBALS['TBL_PREFIX'] . "loginusers";
					} elseif ($ef['tabletype'] == "group") {
						$table = $GLOBALS['TBL_PREFIX'] . "userprofiles";

					} elseif (is_numeric($ef['tabletype'])) {
						$table = $GLOBALS['TBL_PREFIX'] . "flextable" . $ef['tabletype'];
					}
					// Nasty, can give errors, should check if it exists first but this statement has no support for IF EXISTS
					mcq("ALTER TABLE " . $table . " DROP KEY `EFID" . $ef['id'] . "`", $db);
					mcq("ALTER TABLE " . $table . " CHANGE `EFID" . $ef['id'] . "` `EFID" . $ef['id'] . "` LONGTEXT NOT NULL", $db);
					//print "ALTER TABLE " . $table . " CHANGE `EFID" . $ef['id'] . "` `EFID" . $ef['id'] . "` LONGTEXT NOT NULL";
				}


				$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "extrafields SET options='" . mres($options) . "', name='" . mres($_REQUEST['newname']) . "', ordering='" . mres($_REQUEST['neworder']) . "', fieldtype='" . mres($_REQUEST['newtype']) . "', hidden='" . mres($_REQUEST['newhidden']) . "', forcing='" . mres($_REQUEST['forcing']) . "',excludefromfilters='" . mres($_REQUEST['excludefromfilters']) . "', defaultval='" . mres($_REQUEST['defaultval']) . "', sort='" . mres($_REQUEST['sortwhendisplayed']) . "', storetype='" . mres($_REQUEST['storetype']) . "', size='" . mres($_REQUEST['boxsize']) . "', showsearchbox='" . mres($_REQUEST['showsearchbox']) . "', limitddtowidth='" . mres($_REQUEST['limitddtowidth']) . "',allowuserstoaddoptions='" . mres($_REQUEST['allowuserstoaddoptions']) . "',showasradio='" . mres($_REQUEST['showasradio']) . "',israwhtml='" . $israw . "', remarks='" . mres($_REQUEST['fieldremarks']) . "',displaylistname='" . mres($_REQUEST['newdisplaylistname']) . "',lastchangeby='" . mres($GLOBALS['USERID']) . "',underwaterfield='" . mres($_REQUEST['underwaterfield']) . "', timestamp_last_change=NOW(), sum_column='" . mres($_REQUEST['sum_column']) . "',number_format='" . mres($_REQUEST['number_format']) . "' WHERE id='" . mres($_REQUEST['editextrafield']) . "'";
				mcq($sql,$db);

				// Convert drop-down fields and values to ENUMs
				ConvertDDEFToENUM($_REQUEST['editextrafield']);				
				
				// When nescessary, expire concerded form cache

				if ($_REQUEST['tabletype'] == "entity")
				{
					$sql = "SELECT " . $GLOBALS['TBL_PREFIX'] . "templates.templateid FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE (content LIKE '%EFID" . mres($_REQUEST['editextrafield']) . "%' OR content LIKE '%ALL_EXTRA_FIELDS%' OR content LIKE '%#" . mres(strtoupper(str_replace(" ", "_", GetExtraFieldName($_REQUEST['editextrafield'])))) . "#%')";
					$res = mcq($sql, $db);
					while ($row = mysql_fetch_array($res))
					{
						ExpireFormCacheByForm($row[0]);
//						print "<br>Form cache of form " . $row[0] . " expired.";
						qlog(INFO, "Form cache of form " . $row[0] . " expired");
					}

				}
				else
				{
					qlog(INFO, "Expiring form cache not needed.");
				}

				if ($_REQUEST['defaultval'])
				{
					// A default value was added, so we have to create this field for all entities or customers. If we don't, stats will suffer.
					if (is_numeric($_REQUEST['tabletype']))
					{
						$table = $GLOBALS['TBL_PREFIX'] . "flextable" . $_REQUEST['tabletype'];
						mcq("UPDATE " . $table . " SET EFID" . $_REQUEST['editextrafield'] . "='" . mres($_REQUEST['defaultval']) . "' WHERE EFID" . $_REQUEST['editextrafield'] . " = ''", $db);
						print "Default values created<br>";
					}
					elseif ($_REQUEST['tabletype'] == "customer")
					{
						$table = $GLOBALS['TBL_PREFIX'] . "customer";
						mcq("UPDATE " . $table . " SET EFID" . $_REQUEST['editextrafield'] . "='" . mres($_REQUEST['defaultval']) . "' WHERE EFID" . $_REQUEST['editextrafield'] . " = ''", $db);
						print "Default values created<br>";
					}
					elseif ($_REQUEST['tabletype'] == "entity")
					{
						$table = $GLOBALS['TBL_PREFIX'] . "entity";
						mcq("UPDATE " . $table . " SET EFID" . $_REQUEST['editextrafield']. "='" . mres($_REQUEST['defaultval']) . "' WHERE EFID" . $_REQUEST['editextrafield'] . " = ''", $db);
						print "Default values created<br>";
					}
					elseif ($_REQUEST['tabletype'] == "user")
					{
						$table = $GLOBALS['TBL_PREFIX'] . "loginusers";
						mcq("UPDATE " . $table . " SET EFID" . $_REQUEST['editextrafield']. "='" . mres($_REQUEST['defaultval']) . "' WHERE EFID" . $_REQUEST['editextrafield'] . " = ''", $db);
						print "Default values created<br>";
					}
					elseif ($_REQUEST['tabletype'] == "group")
					{
						$table = $GLOBALS['TBL_PREFIX'] . "userprofiles";
						mcq("UPDATE " . $table . " SET EFID" . $_REQUEST['editextrafield']. "='" . mres($_REQUEST['defaultval']) . "' WHERE EFID" . $_REQUEST['editextrafield'] . " = ''", $db);
						print "Default values created<br>";
					}
					else
					{
						PrintAD("Wrong table type. Cowardly quitting.");
						exit;
					}
				}
				RebuildViews();
			}
		}
		else
		{
			?>
				<script type="text/javascript">
				<!--
					alert('A drop-down box must have options! Field is not saved.');
				//-->
				</script>
			<?php
		}


		if ($_REQUEST['req_url'])
		{
			?>
			<script type="text/javascript">
			<!--
				document.location='<?php echo base64_decode($_REQUEST['req_url']);?>';
			//-->
			</script>
			<?php
		}
	}
	elseif ($_REQUEST['delfield'])
	{
		$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "extrafields SET deleted='y' WHERE id='" . mres($_REQUEST['delfield']) . "'";
		mcq($sql,$db);

		if ($_REQUEST['tabletype'] == "entity")
		{
			$sql = "SELECT " . $GLOBALS['TBL_PREFIX'] . "templates.templateid FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE (content LIKE '%EFID" . mres($_REQUEST['delfield']) . "%' OR content LIKE '%ALL_EXTRA_FIELDS%' OR content LIKE '%#" . mres(strtoupper(str_replace(" ", "_", GetExtraFieldName($_REQUEST['delfield'])))) . "#%')";
			$res = mcq($sql, $db);
			while ($row = mysql_fetch_array($res))
			{
				ExpireFormCacheByForm($row[0]);
				//print "<br>Form cache of form " . $row[0] . " expired.";
				qlog(INFO, "Form cache of form " . $row[0] . " expired");
			}

//			$sql = "SELECT " . $GLOBALS['TBL_PREFIX'] . "binfiles.fileid FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles, " . $GLOBALS['TBL_PREFIX'] . "blobs WHERE " . $GLOBALS['TBL_PREFIX'] . "binfiles.fileid=" . $GLOBALS['TBL_PREFIX'] . "blobs.fileid AND filetype='TEMPLATE_HTML_FORM' AND (" . $GLOBALS['TBL_PREFIX'] . "blobs.content LIKE '%EFID" . mres($_REQUEST['delfield']) . "%' OR " . $GLOBALS['TBL_PREFIX'] . "blobs.content LIKE '%ALL_EXTRA_FIELDS%')";
//			$res = mcq($sql, $db);
//			while ($row = mysql_fetch_array($res))
//			{
//			ExpireFormCacheByForm($row[0]);
//			print "<br>Form cache of form " . $row[0] . " expired.";
//			qlog(INFO, "Form cache of form " . $row[0] . " expired");
//			}
		}
		else
		{
			qlog(INFO, "Expiring form cache not needed.");
		}
	}
	elseif ($_REQUEST['undelfield'])
	{
		$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "extrafields SET deleted='n' WHERE id='" . mres($_REQUEST['undelfield']) . "'";
		mcq($sql,$db);
	}
	elseif ($_REQUEST['deldatabyname'])
	{
		$sql = PopStashValue($_REQUEST['deldatabyname']);
		mcq($sql,$db);
	}

	if ($_REQUEST['editextrafield'])
	{
		$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE id='" . mres($_REQUEST['editextrafield']) . "'";
		$result = mcq($sql,$db);
		$row = mysql_fetch_array($result);
		if ($row['fieldtype'] == "Computation" || $row['fieldtype'] == "SQL Query" || $_REQUEST['newtype'] == "SQL Query (multiselect)")
		{
			SafeModeInterruptCheck();
		}
		if ($row['fieldtype'] == "Button") {
			$ShowButtonInList = GetAttribute("extrafield", "ShowButtonInList", $_REQUEST['editextrafield']);
			if ($ShowButtonInList == "") {
				SetAttribute("extrafield", "ShowButtonInList", "No", $_REQUEST['editextrafield'], array("No", "Yes"));
			}
			$HideButtonWhenNotClickable = GetAttribute("extrafield", "HideButtonWhenNotClickable", $_REQUEST['editextrafield']);
			if ($HideButtonWhenNotClickable == "") {
				SetAttribute("extrafield", "HideButtonWhenNotClickable", "Yes", $_REQUEST['editextrafield'], array("Yes", "No"));
			}
			$ButtonClickConfirmMessage = GetAttribute("extrafield", "ButtonClickConfirmMessage", $_REQUEST['editextrafield']);
			if ($ButtonClickConfirmMessage == "") {
				SetAttribute("extrafield", "ButtonClickConfirmMessage", "{{none}}", $_REQUEST['editextrafield']);
			}
			$BackToListAfterSave = GetAttribute("extrafield", "BackToListAfterSave", $_REQUEST['editextrafield']);
			if ($BackToListAfterSave == "") {
				SetAttribute("extrafield", "BackToListAfterSave", "{[default}}", $_REQUEST['editextrafield'], array("{{default}}", "No", "Yes"));
			}

		} else {
			SetAttribute("extrafield", "ShowButtonInList", "", $_REQUEST['editextrafield'], array("No", "Yes"));
			SetAttribute("extrafield", "HideButtonWhenNotClickable", "", $_REQUEST['editextrafield'], array("Yes", "No"));
			SetAttribute("extrafield", "ButtonClickConfirmMessage", "", $_REQUEST['editextrafield']);
		}
		if ($row['fieldtype'] == "Computation" || $row['fieldtype'] == "Computation (ajax autorefresh)") {
			$ComputationOutputType = GetAttribute("extrafield", "ComputationOutputType", $_REQUEST['editextrafield']);
			if ($ComputationOutputType == "") {
				SetAttribute("extrafield", "ComputationOutputType", "Numeric", $_REQUEST['editextrafield'], array("Numeric", "String", "Date"));
			}
		} else {
			SetAttribute("extrafield", "ComputationOutputType", "", $_REQUEST['editextrafield'], array("Numeric", "String", "Date"));
		}	
		if ($row['fieldtype'] == "date") {
			$UsePlanningCalendar = GetAttribute("extrafield", "UsePlanningCalendar", $_REQUEST['editextrafield']);
			if ($UsePlanningCalendar == "") {
				SetAttribute("extrafield", "UsePlanningCalendar", "No", $_REQUEST['editextrafield'], array("No", "Yes"));
			}
			$UsePlanningCalendarMatchOnFields = GetAttribute("extrafield", "UsePlanningCalendarMatchOnFields", $_REQUEST['editextrafield']);
			if ($UsePlanningCalendarMatchOnFields == "") {
				SetAttribute("extrafield", "UsePlanningCalendarMatchOnFields", "{{none}}", $_REQUEST['editextrafield']);
			}
			$UsePlanningCalendarDescription = GetAttribute("extrafield", "UsePlanningCalendarDescription", $_REQUEST['editextrafield']);
			if ($UsePlanningCalendarDescription == "") {
				SetAttribute("extrafield", "UsePlanningCalendarDescription", "@RECORDID@ -- No UsePlanningCalendarDescription attribute set!", $_REQUEST['editextrafield']);
			}
			$UsePlanningCalendarView = GetAttribute("extrafield", "UsePlanningCalendarView", $_REQUEST['editextrafield']);
			if ($UsePlanningCalendarView == "") {
				SetAttribute("extrafield", "UsePlanningCalendarView", "Month", $_REQUEST['editextrafield'], array("Month", "Week"));
			}

			
		}

		if (strstr($row['options'], "%POPULATE_BY_CODE%") && $row['fieldtype'] == "drop-down")
		{
			$row['fieldtype'] = "drop-down (populate by code)";
			$row['options'] = str_replace("%POPULATE_BY_CODE%", "", $row['options']);
		}
		elseif (strstr($row['options'], "%POPULATE_BY_CODE%") && $row['fieldtype'] == "drop-down (multiselect)")
		{
			$row['fieldtype'] = "drop-down (populate by code multiselect)";
			$row['options'] = str_replace("%POPULATE_BY_CODE%", "", $row['options']);
		}

		

		if ($_REQUEST['editextrafield'] <> "new") {
			print "<h1>Editing field " . $tname . " :: " . $row['id'] . " :: " . $row['name'] . "</h1>";
			print "<h2>Last change: " . $row['timestamp_last_change'] . " by " . GetUserName($row['lastchangeby']) . ".</h2>";
		} else {
			print "<h1>New field for table " . $tname . "</h1>";
			print "<h2>Create a new field</h2>";
		}
		$dellink = "extrafields.php?tabletype=" . $_REQUEST['tabletype'] . "&amp;delfield=" . $_REQUEST['editextrafield'];

		print "<form id='EditField' method='post' action=''><div class='showinline'>";
		print "<input type='hidden' name='req_url' value='" . htme($_REQUEST['req_url']) . "'>";
		print "<table class='interleave-table-space'>";
		print "<tr><td>" . AttributeLink("extrafield", $row['id']) . "</td><td style='text-align: right'><input type='button' value='Cancel' onclick=\"document.location='" . $loc . "'\">&nbsp;<input type='button' value='Delete field' onclick=\"document.location='" . $dellink . "'\">&nbsp;<input name='submitted' type='submit' value='Save changes'>";

		print "<tr class=''><td>Field name</td><td " . PrintToolTipCode('A name for your field. Can be anything you want.') .">";
		print "<input type='text' name='newname' onkeyup='CreateNewAliasName();' size='60' value=\"" . htme($row['name']) . "\">";
				
		print "</td></tr>";
		print "<tr><td>Field display name in lists</td><td " . PrintToolTipCode('A more readably name for your field. Not required') .">";

		print "<input type='text' name='newdisplaylistname' size='60' value=\"" . htme($row['displaylistname']) . "\"></td></tr>";

		$fieldtag = "#EFID" . $row['id'] . "#";
		print "<tr><td>Tag &amp; alias</td><td><input type='text' name='AliasImmutable' size='8' readonly='readonly' value='" . htme($fieldtag) . "'>&nbsp;<input type='text' name='AliasImmutable' size='60' readonly='readonly' value='#" . htme(strtoupper(str_replace(" ", "_", $row['name']))) . "#'></td></tr>";
		
		print "<tr class=''><td>Type</td><td  " . PrintToolTipCode('The type of the field') .">";

		if ($row['fieldtype'] == "date/time") {
			print "Date/time (immutable) <input type='hidden' name='newtype' id='JS_newtype' value='date/time'>";
		} else {

			
			print "<select " . $ro_ins . " name='newtype' id='JS_newtype' onchange='SetDivOpen();'>";
			foreach ($fieldtypes AS $type)
			{
				if (is_array($type))
				{
					$value = $type[1];
					$tmp = $type[0];
					unset($type);
					$type = $tmp;
				}
				else
				{
					$value = $type;
				}

				if ($row['fieldtype'] == $type || $row['fieldtype'] == $value)
				{
					$ins = "selected='selected'";
				}
				else
				{
					unset($ins);
				}
				if ($_REQUEST['tabletype'] <> "entity" && $type=="drop-down based on customer list of values")
				{
				}
				else
				{
					print "<option " . $ins . " value='" . htme($value) . "'>" . strtolower($type) . "</option>";
				}
			}
			print "</select>";
		}
		//print ReturnDropDownSearchField("JS_newtype");
		print "</td></tr>";
		print "<tr><td>&nbsp;</td><td >&nbsp;";


		// Button DIV
		print "<div id='dd_button' class='HideElement'><table style='width: 100%;'>";
		print "<tr><td>After creating a button, use <a class='plainlink' href='trigger.php?add=buttons'>a button trigger</a> to set actions to this button!<br><br>With buttons you can combine the advanced access rights possibilities of an<br>extra field with a form element. Other triggers will also go off when appropriate,<br>but the button trigger will always go last.<br><br>A button will <strong>always</strong> save the entity!</td></tr>";
		print "</table></div>";


		print "<div id='flextableoptions' class='HideElement'><table style='width: 100%;'>";
		print "<tr><td>Refer to FlexTable:</td><td><select name='FlexTableReference'>";
		print "<option></option>"; //Jeroen 2010-04-19 minimaal 1 option required by xhtml
		$shown = array();
		foreach (GetFlexTableDefinitions() AS $ft)
		{
			if ($ft['orientation'] == "many_entities_to_one")
			{
				qlog(INFO, "Processing flextable " . $ft['tablename']);
				if (($ft['refers_to'] == "entity" && $_REQUEST['tabletype'] == "entity") || ($ft['refers_to'] == "customer" && $_REQUEST['tabletype'] == "customer") || ($_REQUEST['tabletype'] != "entity" && $_REQUEST['tabletype'] != "customer"))
				{
					$ins = "";
					if ($row['options'] == $ft['recordid'])
					{
						$ins = "selected='selected'";
					}
					print "<option " . $ins . " value='" . $ft['recordid'] . "'>" . $ft['recordid'] . ": " . htme($ft['tablename']) . "</option>";
					array_push($shown, $ft['recordid']);
				}
			}

		}
		foreach (GetFlexTableDefinitions() AS $ft) {
				qlog(INFO, "Processing flextable " . $ft['tablename']);
				if ($ft['refers_to'] != $_REQUEST['tabletype']) { // && !in_array($ft['recordid'], $shown)
					$ins = "";
					if ($row['options'] == $ft['recordid'])
					{
						$ins = "selected='selected'";
					}
					print "<option " . $ins . " value='" . $ft['recordid'] . "'>" . $ft['recordid'] . ": " . htme($ft['tablename']) . " | BLIND REFERENCE</option>";
				}
		}

		print "</select></td></tr>";
		$t = "";
		if ($row['sort'] == "y")
		{
			$t = "checked='checked'";
		}
		print "<tr><td>Don't show drop-down, just show textbox: &nbsp; <input $t type='checkbox' name='dontshowFTpopup' value='y'></td></tr>";
		print "</table>";
		print "</div>";
		print "<div id='ddoptions_LOV' class='HideElement'><table style='width: 100%;'>";
		print "<tr><td >Which customer field: (must be of type 'list of values')<br><br></td></tr>";
		print "<tr><td ><select name='options'>";
		print "<option></option>"; //Jeroen 2010-04-19 minimaal 1 option required by xhtml
		$res = mcq("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE tabletype='customer' AND fieldtype='List of values'", $db);
		while ($row2 = mysql_fetch_array($res))
		{
			$tins = "";
			if ($row['options'] == $row2['id'])
			{
				$tins = "selected='selected'";
			}
			print "<option " . $tins . " value='" . $row2['id'] . "'>" . htme($row2['name']) . "</option>";
		}
		print "</select></td></tr>";
		print "</table></div>";

		print "<div id='ddoptions_CHECKBOX' class='HideElement'><table style='width: 100%;'>";

		if ($row['options'] == "") $row['options'] = "Yes";

		print "<tr><td><a onclick=\"PopEFDDColorChooser(" . $row['id'] . ",'" . $tabletype . "');\" class='arrow'>Assign colors to values</a></td></tr>";
		print "<tr><td >Value when checked</td><td><input type='text' name='cb_checked' value='" . htme($row['options']) . "'></td></tr>";
		print "<tr><td >Value when unchecked</td><td><input type='text' name='cb_unchecked' value='" . htme($row['defaultval']) . "'></td></tr>";

		print "</table></div>";

		if ($row['size'] == "")
		{
			if ($row['fieldtype'] == "numeric")
			{
				$row['size'] = 8;
			}
			else
			{
				$row['size'] = 50;
			}
		}

		print "<div id='sizeoptions' class='HideElement'><table style='width: 50%;'>";
		print "<tr><td >Size (in characters):&nbsp;&nbsp; <input size='2' type='text' name='boxsize' value='" . htme($row['size']) . "'></td></tr>";
		print "</table></div>";
		print "<div id='numericsumcolumnoptions' class='HideElement'></div><table style='width: 50%;'>";
		if ($row['sum_column'] == "yes") {
			$checked = "checked = 'checked'";
		} else {
			$checked = "";
		}
		print "<tr><td><input size='2' " . $checked . " type='checkbox' name='sum_column' value='yes'>&nbsp;Add up this column below the list</td></tr>";
		$checked = "";
		print "<tr><td>Number format:&nbsp;&nbsp; <select name='number_format'>";

		$options = array(	
							
							"don't format"	=> "Don't format",
							"normal"		=> "2 decimals (default)", 
							"currency"		=> "Currency (without sign)",
							"currency EUR"	=> "Currency &euro;",
							"currency DOL"	=> "Currency $",
							"currency BRP"	=> "Currency &pound;",
							"currency YEN"	=> "Currency &yen;",
							"no decimals"	=> "No decimals",
							"1 decimal"		=> "1 decimal",
							"3 decimals"		=> "3 decimals",
							"4 decimals"		=> "4 decimals",
							"5 decimals"		=> "5 decimals"

						);
		
		foreach ($options AS $value => $disp) {
			if ($row['number_format'] == $value) {
				$checked = " selected = 'selected'";
			}  else {
				$checked = "";
			}
			print "<option value='" . htme($value) . "' " . $checked . ">" . ($disp) . "</option>";
		}

		print "</select></td></tr>";

		print "</table>";
		print "<div id='planning-options' class='HideElement'><table style='width: 50%;'>";
		print "<tr><td>Display method</td><td><select name='planoptions'>";
		print "<option value='icon/popup'>Icon/popup</option>";
		if ($row['options'] == "inline")
		{
			print "<option value='inline' selected='selected'>In-line</option>";
		}
		else
		{
			print "<option value='inline'>In-line</option>";
		}
		print "</select></td></tr>";
		
		//uitgezetd door Jeroen 2010-09-26 omdat je sort niet meer uit kan zetten voor dropdown velden omdat hij door onderstaande veld altijd weer op yes komt te staan
		/*
		print "<tr><td>Show hours total in lists instead of list of bookings</td><td>";
		$t = "";
		if ($row['sort'] == 'y')
		{
			$t = "checked='checked'";
		}
		print "<input " . $t . " type='checkbox' name='sortwhendisplayed' value='y'></td></tr>";
		*/
		print "</table></div>";
		print "<div id='planning-group-options' class='HideElement'><table style='width: 50%;'>";
		print "<tr><td>Booking calendars to show in this group: </td></tr>";
		$list = db_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE tabletype='" . mres($tabletype) . "'");
		print "<tr><td >";
		$cur = unserialize($row['options']);

		foreach ($list AS $rij)
		{
			if ($rij['fieldtype'] == "Booking calendar")
			{
				$ins = "";
				if (in_array($rij['id'], $cur))
				{
					$ins = "checked='checked'";
				}
				print "<input " . $ins . " type='checkbox' value='" . $rij['id'] . "' name='planoptions[]'> " . htme($rij['name']) . "<br>";
			}
		}
		print "</td></tr>";
		
		//uitgezetd door Jeroen 2010-09-26 omdat je sort niet meer uit kan zetten voor dropdown velden omdat hij door onderstaande veld altijd weer op yes komt te staan
		/*print "<tr><td>Show hours total in lists instead of list of bookings</td><td>";
		$t = "";
		if ($row['sort'] == 'y')
		{
			$t = "checked='checked'";
		}
		print "<input $t type='checkbox' name='sortwhendisplayed' value='y'></td></tr>";*/
		print "</table></div>";
		print "<div id='sqlquery' class='HideElement'><table style='width: 50%;'>";
		$t = unserialize($row['options']);
		print "<tr><td>Database name:<br><input type='text' name='SQLQUERYDB' size='50' value='" . htme($t[0]) . "'><br>SQL Query: <textarea name='SQLQUERY' cols='100' rows='10'>" . htme($t[1]) . "</textarea><br><br>If the database is not on the same server, use DBNAME@HOSTNAME. The user specified in your config/config.inc.php must also have the rights to use the database you provide here.</p>";
		print "If your query returns 1 column, that column will be used for both value and text. When it returns two columns, the 1st column will be used as value, the second as text.</td></tr>";
		print "</table></div>";
		print "<div id='computation' class='HideElement'><table style='width: 50%;'>";
		$t = unserialize($row['options']);
		if ($row['options'] == "" || $row['options'] == "Yes")
		{
			$row['options'] = "\$result = ;";
		}
		
		if ($row['fieldtype'] == "Computation" || strtolower($row['fieldtype']) == "computation (ajax autorefresh)") {
			
			$fn = tempnam("", "INTLV_MODPARSE");
			$fp = fopen($fn, "w");
			fputs($fp, "<?php " . $row['options'] . "\n\n?>\n");
			$result = `php -l $fn`;
			unlink($fn);
			
			$result = str_replace("\nParse error", "Parse error", $result);
			$result = str_replace("in " . $fn, "", $result);
			$result = str_replace("Errors parsing " . $fn . "\n", "", $result);

			if ($result) {
					print "<tr><td ><h1>" . nl2br($result) . "</h1></td></tr>";
					if (trim($result) != "No syntax errors detected" && $result != "") {
						?>
						<script type="text/javascript">
						<!--
							alert('This code generates a parse error.');
						//-->
						</script>
						<?php
					}
			}
		}
		print "<tr><td >Compute: (standard PHP syntax)<br><textarea name='computation' id='computationTA' cols='150' rows='25'>" . htme($row['options']) . "</textarea></td></tr>";

		print "</table></div>";

		print "<div id='sizeoptionstextarea' class='HideElement'><table style='width: 50%;'>";
		$sa = explode(":", $row['options']);

		if ($sa[0] == "Yes" || $sa[0] == "\$result = ;")
		{
			$sa[0] = "";
		}

		print "<tr><td >Number of columns (in characters)</td><td " . PrintToolTipCode('The width of of the box in characters') ."><input size='2' type='text' name='boxsize1' value='" . htme($sa[0]) . "'></td></tr>";
		print "<tr><td >Number of rows (in characters)</td><td " . PrintToolTipCode('The height of the box in characters') ."><input size='2' type='text' name='boxsize2' value='" . htme($sa[1]) . "'></td></tr>";
		$ins1 = "";
		$ins2 = "";
		if ($sa[2] == "y")
		{
			$ins2 = "selected='selected'";
		}
		else
		{
			$ins1 = "selected='selected'";
		}
		print "<tr><td >Show insert date/time clock <img src='images/timedate.gif' alt=''></td><td><select name='ShowTimeDateInputClockThingy'><option value='y' " . $ins2 . ">Yes</option><option value='n' " . $ins1 . ">No</option></select></td></tr>";

		print "</table></div>";
		print "<div id='sizeoptionstextarea_rt' class='HideElement'><table style='width: 50%;'>";
		$sa = explode(":", $row['options']);
		if ($sa[0] > 100)
		{
			 $sa[0] = 100;
		}
		if (!is_numeric($sa[0])) $sa[0] = "100";
		if (!is_numeric($sa[1])) $sa[1] = "400";

		print "<tr><td >Width </td><td " . PrintToolTipCode('The width of the box relative to the width of the frame in which it is displayed.') ."><input size='2' type='text' name='boxsize3' value='" . htme($sa[0]) . "'> %</td></tr>";
		print "<tr><td >Height </td><td " . PrintToolTipCode('The height of the box in pixels. A normal value would be 400.') ."><input size='2' type='text' name='boxsize4' value='" . htme($sa[1]) . "'> pixels</td></tr>";

		print "</table></div>";
		print "<div id='ddoptions' class='HideElement'><table style='width: 100%;'>";

		print "<tr><td >Enter your options here (put every option on a new line): ";

		if (is_numeric($tabletype))
		{
			$tabletype2 = "flextable" . $tabletype;
			$tablename = "flextable" . $tabletype;
		}
		else
		{
			if ($tabletype == "customer") {
				$tabletype2 = "cust";
				$tablename = "customer";
			} elseif ($tabletype == "entity") {
				$tabletype2 = $tabletype;
				$tablename = "entity";
			} elseif ($tabletype == "user") {
				$tabletype2 = $tabletype;
				$tablename = "loginusers";
			} elseif ($tabletype == "group") {
				$tabletype2 = $tabletype;
				$tablename = "userprofiles";
			}
		}
		if ($tablename && $row['id'])
		{
			$count = db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . $tablename );

			if ($count < 100000) {
				$vals = db_GetFlatArray("SELECT DISTINCT(EFID" . $row['id'] . ") FROM " . $GLOBALS['TBL_PREFIX'] . $tablename . " ORDER BY EFID" . $row['id']. "");
			} else {
				$htmlisn = "[table too large - " . $count . " rows]";
			}
		}

		print "<input type='hidden' name='curvals' value='"; 
		$t = 0;
		$tmp = "";
		foreach ($vals AS $val)
		{
			$tmp .= htme($val) . "";
			$t++;
		}
		if ($htmlisn) {
			print $htmlisn;
		} elseif ($t>250)
		{
			print "[too many values: " . $t . " - max is 250]";
		}
		else
		{
			print $tmp;
		}

		print "'>";

		print "</td></tr>";
		print "<tr><td><textarea rows='10' cols='40' name='newoption'>";
		$arr = unserialize($row['options']);
		if (is_array($arr))
		{
			foreach ($arr AS $option)
			{
				$i++;
				print $option . "\n";
			}
		}

		print "</textarea><br><a onclick=\"document.forms['EditField'].elements['newoption'].value=document.forms['EditField'].elements['curvals'].value;\" class='plainlink'>Populate this based on the current values</a>";
		
		$t = "";
		if ($row['fieldtype'] != "drop-down (multiselect)")
		{

			if ($row['allowuserstoaddoptions'] == 'y')
			{
				$t = "checked='checked'";
			}
			if ($row['showasradio'] == 'y')
			{
				$t1 = "checked='checked'";
			}

			print "<tr " . PrintToolTipCode("When this option is enabled, a + icon will be printed behind the field. When this icon is clicked by the user, he/she can add values to this list. The new value is inserted in the drop-down field and selected, and also saved in the extra field properties.") . "><td><input " . $t . " type='checkbox' name='allowuserstoaddoptions' value='y'>&nbsp;Allow users to add values to this list</td></tr>";

			print "<tr title=\"When this option is enabled, a list of radio buttons will be shown instead of a drop-down field.\"><td><input " . $t1 . " type='checkbox' name='showasradio' value='y'>&nbsp;Display as set of radio buttons</td></tr>";

		}
		unset($option);
		$t = "";
		if ($row['sort'] == 'y')
		{
			$t = "checked='checked'";
		}
		if ($row['fieldtype'] != "drop-down (multiselect)")
		{
			print "<tr><td> <input " . $t . " type='checkbox' name='sortwhendisplayed' value='y'>&nbsp;Sort list alphabetically in forms</td></tr>";
		}
		print "</table></div>";
		// Show search box DIV

		print "<div id='dd_searchboxdiv' class='HideElement'><table style='width: 100%;'>";
		if ($row['showsearchbox'] == 'y')
		{
			$tt = "checked='checked'";
		}
		if ($row['fieldtype'] != "drop-down (multiselect)" && $row['fieldtype'] != "drop-down (populate by code multiselect)" && $row['fieldtype'] != "Reference to FlexTable (multiselect)" && $row['fieldtype'] != "SQL Query (multiselect)")
		{
			print "<tr " . PrintToolTipCode("With is option, users can filter the list of options by typing a few characters in the fast-search box. Very nifty feature.") . "><td><input $tt type='checkbox' name='showsearchbox' value='y'>&nbsp;Show a little fast-search box next to this drop-down field</td></tr>";
			print "<tr><td>Limit the width of this drop-down box to <input type='text' style='width: 24px' name='limitddtowidth' value='" . htme($row['limitddtowidth']) . "'> pixels (will auto-size when clicked).</td></tr>";
		}
		else
		{
			print "<tr><td><input $tt type='checkbox' name='showsearchbox' value='y'>&nbsp;Display as set of checkboxes</td></tr>";
		}
		print "</table>";
		print "</div>";
		print "<table><tr><td><a onclick=\"PopEFDDColorChooser(" . $row['id'] . ",'" . $tabletype . "');\" class='arrow'>Assign colors to values</a></td></tr></table>";

		print "<div id='commentoptions' class='HideElement'><table style='width: 50%;'>";
		print "<tr><td >Select a template to display on this extra field location: <br><br><select name='newHTMLtemplate'>";
		$sql = "SELECT templateid, templatename, template_subject FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML'";
		$result= mcq($sql,$db);
		while ($row2= mysql_fetch_array($result))
		{
			$tins = "";
			if ($row['options'] == $row2['templateid'])
			{
				$tins = "selected='selected'";
			}

			print "<option " . $tins . " value='" . $row2['templateid'] . "'>" . $row2['templatename'];
			if ($row2['template_subject'] <> "")
			{
				print " (" . $row2['template_subject'] . ")";
			}
			print "</option>";
		}
		print "</select></td></tr>";
		if ($_REQUEST['tabletype'] == "customer")
		{
			print "<tr><td>You can use customer tags in your template (not entity tags).</td></tr>";
		}
		else
		{
			print "<tr><td>You can use entity tags as well as customer tags in your template.</td></tr>";
		}
		print "<tr><td><a onclick=\"window.open(this.href); return false;\" class='plainlink' href='admin.php?templates=1'>Edit RTF and HTML templates in new window</a></td></tr>";
		print "</table></div>";

		print "</td></tr>";
		if ($row['hidden'] == "y")
		{
			$ins = "selected='selected'";
		}
		elseif ($row['hidden'] == "a")
		{
			$inst = "selected='selected'";
		}
		else
		{
			unset($ins);
		}
		if ($_REQUEST['editextrafield']<>"new")
		{
			print "<tr class=''><td>Order</td><td  " . PrintToolTipCode('This field sets the order in which the fields are processed') . "><input type='text' name='neworder' size='10' value='" . htme($row['ordering']) . "'></td></tr>";
		}

		if ($_REQUEST['editextrafield']<>"new" && !strstr($row['fieldtype'], "[copyfield"))
		{
			if (is_array(GetExtraFieldAccessRestrictions($_REQUEST['editextrafield'])))
			{
				print "<tr class=''><td>Detailed access restrictions</td><td  " . PrintToolTipCode('Specific access rights are set for this field.') . ">";
				print "<a class=\"arrow\" href='javascript:PopRightsChooser(" . $_REQUEST['editextrafield'] . ");'>select</a>&nbsp;&nbsp;<span class='noway'>[restrictions apply]</span>";
			}
			else
			{
				print "<tr class=''><td>Detailed access restrictions</td><td  " . PrintToolTipCode('No specific access rights are set for this field.') . ">";
				print "<a class=\"arrow\" href='javascript:PopRightsChooser(" . $_REQUEST['editextrafield'] . ");'>select</a>&nbsp;&nbsp;[none set]";
			}
			
		} elseif (strstr($row['fieldtype'], "[copyfield")) {
		} else {
			print "<tr class=''><td>Detailed access restrictions</td><td  " . PrintToolTipCode('No specific access rights are set for this field.') . ">";
			print "[none set, save first]";
		}

		print "</td></tr>";
		print "<tr><td>Custom validation</td><td>";
		print "" . AttributeLink("extrafield", $_REQUEST['editextrafield'], "select", "CustomValidationFunctionPHP") . "&nbsp;&nbsp;";

		if ($_REQUEST['editextrafield']  == "new") {
			print "[none set, save first]";
		} else {
			$code = GetAttribute("extrafield", "CustomValidationFunctionPHP", $_REQUEST['editextrafield']);
			if ($code == "" || $code == "{{none}}") {
				print "[none set]";
			} else {
				print "<span class='noway'>[set]</span>";
			}
			
		}
		print $conditionstext;
		print $requiredconditionstext;

		if ($row['storetype'] == "default")
		{
			$ins = "selected='selected'";
		}
		elseif ($row['storetype'] == "3rd_table")
		{
			$inst = "selected='selected'";
		}
		elseif ($row['storetype'] == "3rd_table_popup")
		{
			$inst2 = "selected='selected'";
		}
		/*print "<tr><td>Store type:</td><td >";
		print "<select name='storetype'>";
		print "	<option value='default' $ins>Normal, single field</option>";
		print "	<option value='3rd_table' $inst>Table wich can contain multiple values (presented in-line)</option>";
		print "	<option value='3rd_table_popup' $inst2>Table wich can contain multiple values (presented in popup window)</option>";
		print "</td></tr>";
		*/

		if (is_array(unserialize($row['defaultval'])))
		{
			$row['defaultval'] = unserialize($row['defaultval']);
			$row['defaultval'] = base64_decode($row['defaultval'][0]);
		}
		$ins = "";
		if ($row['israwhtml'] == "y")
		{
			$ins = "checked='checked'";
		}
		print "<tr class=''><td>Contains raw HTML</td><td  " . PrintToolTipCode('If the field contains HTML which must be parsed by the browser, check this box.') ."><input type='checkbox' name='IsRawHTML' value='IsRawHTML' " . $ins . "></td></tr>";

		print "<tr><td>Default value</td><td  " . PrintToolTipCode('The value of this field, when nothing has been entered by a user.') ."><input type='text' name='defaultval' value='" . htme($row['defaultval']) . "'></td></tr>";
		$ins = "";
		if ($row['forcing'] == "y")
		{
			$ins = "checked='checked'";
		}
		if (!strstr($row['fieldtype'], "[copyfield") && !strstr($row['fieldtype'], "Booking"))
		{
			print "<tr class=''><td>Required (user will be forced to enter or select a value)</td><td  " . PrintToolTipCode('When checked, the user will have to give this field a value before saving the entity.') ."><input type='checkbox' name='forcing' value='y'" . $ins . ">";
	
			print "</td></tr>";

//			PopExtrafieldRequiredConditionsChooser(i)
		}
		elseif (!strstr($row['fieldtype'], "Reference to") && !strstr($row['fieldtype'], "Booking"))
		{
			print "<tr class=''><td ><br><br>The field properties will be copied from another field.</td></tr>";
		}
		$ins = "";
		if ($row['excludefromfilters'] != "n")
		{
			$ins = "checked='checked'";
		}
		print "<tr class=''><td>Never create list filter about this field</td><td  " . PrintToolTipCode('When checked, the system will never generate a list filter for this field, regardless of user-, profile- or system wide settings') ."><input type='checkbox' name='excludefromfilters' value='y'" . $ins . "></td></tr>";
		$ins = "";
		if ($row['underwaterfield'] == "y")
		{
			$ins = "checked='checked'";
		}

		print "<tr class=''><td>Hide this field from forms and lists</td><td  " . PrintToolTipCode('When checked, the system will never show this field to users.') ."><input type='checkbox' name='underwaterfield' value='y'" . $ins . "></td></tr>";
		if (is_numeric($row['id']))
		{
			print "<tr class=''><td>Access restrictions depending on this field</td><td>";

			$tmp = db_GetArray("SELECT name, CLLEVEL, id FROM " . $GLOBALS['TBL_PREFIX'] . "userprofiles WHERE CLLEVEL LIKE '%EFID" . $row['id'] . "%'");
			foreach ($tmp AS $cl)
			{
				$ar = unserialize($cl[1]);
				foreach ($ar AS $acr)
				{
					if (strstr($acr, "EFID" . $row['id'] . "|B|"))
					{
						$val = base64_decode(str_replace("EFID" . $row['id'] . "|B|", "", $acr));
						print "<a href='useradmin.php?EditGroup=" . $cl['id'] . "' class='plainlink'>Access denied for profile " . $cl[0] . " when this field has value \"" . $val . "\"</a><br>";
						$acp = true;
					}
					if (strstr($acr, "EFID" . $row['id'] . "|V|"))
					{
						$val = base64_decode(str_replace("EFID" . $row['id'] . "|V|", "", $acr));
						print "<a href='useradmin.php?EditGroup=" . $cl['id'] . "' class='plainlink'>Read-only for profile " . $cl[0] . " when this field has value \"" . $val . "\"</a><br>";
						$acp = true;
					}
				}
			}
			$tmp = db_GetArray("SELECT name, CLLEVEL, id FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE CLLEVEL LIKE '%EFID" . $row['id'] . "%'");
			foreach ($tmp AS $cl)
			{
				$ar = unserialize($cl[1]);
				foreach ($ar AS $acr)
				{
					if (strstr($acr, "EFID" . $row['id'] . "|B|"))
					{
						$val = base64_decode(str_replace("EFID" . $row['id'] . "|B|", "", $acr));
						print "<a href='useradmin.php?EditUser=" . $cl['id'] . "' class='plainlink'>Access denied for user " . $cl[0] . " when field has value \"" . $val . "\"</a><br>";
						$acp = true;
					}
					if (strstr($acr, "EFID" . $row['id'] . "|V|"))
					{
						$val = base64_decode(str_replace("EFID" . $row['id'] . "|V|", "", $acr));
						print "<a href='useradmin.php?EditUser=" . $cl['id'] . "' class='plainlink'>Read-only for user " . $cl[0] . " when field has value \"" . $val . "\"</a><br>";
						$acp = true;
					}
				}
			}

			$tmp = db_GetArray("SELECT recordid, tablename, access_controlled_by_field, access_denied_method FROM " . $GLOBALS['TBL_PREFIX'] . "flextabledefs WHERE access_controlled_by_field=" . $row['id']);
			foreach ($tmp AS $ft)
			{
				if ($ft['access_denied_method'] == "readonly")
				{
					print "<a href='flextable.php?EditFlexTable=" . $ft['recordid'] . "' class='plainlink'>Flextable \"" . $ft['tablename'] . "\" record access (others are read-only)</a><br>";
				}
				else
				{
					print "<a href='flextable.php?EditFlexTable=" . $ft['recordid'] . "' class='plainlink'>Flextable \"" . $ft['tablename'] . "\" record access (others are denied)</a><br>";
				}
				$acp = true;
			}
			if (!$acp)
			{
				print "No access restrictions depending on this field found";
			}
			print "</td></tr>";
			print "<tr class=''><td>Triggers</td><td>";
			$tmp = DB_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "triggers WHERE onchange='ButtonPress" . $row['id'] . "' OR onchange LIKE '%EFID" . $row['id'] . "%' OR onchange LIKE '%EFID " . $row['id'] . "%' OR action LIKE '%EFID" . $row['id'] . "%'");

			$t = "";
			foreach ($tmp AS $rij)
			{
					$rij['to_value'] = "To value: " . str_replace("@SE@", "[any change]", $rij['to_value']);
					print "<a onclick=\"popWidewindowWithBars('trigger.php?closeafter=true&amp;nonavbar=1&amp;add=" . $rij['onchange'] . "&amp;fetch=" . $rij['tid'] . "&amp;req_url=" . base64_encode($_SERVER['REQUEST_URI']) . "');\" class='plainlink'>" . $rij['onchange'] . " :: " . $rij['to_value'] . " :: " . $rij['action'] . "</a><br>";
					$donesome = true;
			}
			if (!$donesome)
			{
					print "No triggers attached to this field found";
			}
			print "</td></tr>";

			print "<tr class=''><td>Other elements using this field</td><td>";
			print ReturnListOfFieldOccurrences($row['id']);
			print "</td></tr>";

			
		}
		print "<tr class=''><td>Remarks";

		if ($_REQUEST['tabletype'] == "entity")
		{
			print "<input type='hidden' name='ti' value='1'>";
		}
		else
		{
			print "<input type='hidden' name='ti' value='2'>";
		}

		print "</td><td><textarea cols='70' rows='4' name='fieldremarks'>" . $row['remarks'] . "</textarea></td></tr>";

		if (!$_REQUEST['req_url'])
		{
			$loc = "extrafields.php?tabletype=" . $_REQUEST['tabletype'];
		}
		else
		{
			$loc = base64_decode($_REQUEST['req_url']);
		}

		

		print "<tr class=''><td colspan='2' align='right'><input type='hidden' name='editextrafield' value='" . htme($_REQUEST['editextrafield']) . "'><input type='hidden' name='tabletype' value='" . htme($_REQUEST['tabletype']) . "'><input type='button' value='Cancel' onclick=\"document.location='" . $loc . "'\">&nbsp;<input type='button' value='Delete field' onclick=\"document.location='" . $dellink . "'\">&nbsp;<input name='submitted' type='submit' value='Save changes'></td></tr>";

		print "</table>";
		print "</div></form>";
	}
	else
	{
		if (is_numeric($_REQUEST['tabletype'])) {
			$tablename = "flextable" . str_replace("ft_", "", $_REQUEST['tabletype']);
		} elseif ($_REQUEST['tabletype'] == "customer")	{
			$tablename = "customer";
		} elseif ($_REQUEST['tabletype'] == "user") {
			 $tablename = "loginusers";
		} elseif ($_REQUEST['tabletype'] == "group") {
			$tablename = "userprofiles";
		} else {
			$tablename = "entity";
		}

	//	print "<br><br><strong><span class='noway'>Inline edit mode</span>:</strong><br><br>";

	//	print "<br><br>&nbsp;&nbsp;";
		if ($GLOBALS['ef_inline_edit'] <> "yes")
		{
			print "<div " . PrintToolTipCode("When enabled, this function will print a small icon behind each extra field in a form which links directly to the edit extra field page of that field. This is very useful when working on your extra fields. It will only be visible to you - you wont bother other users.") . "><a onclick=\"setCookie('ef_inline_edit','yes');alert('You are now in inline edit mode.');\" class='plainlink'>Go to inline edit mode</a></div>";
		}
		else
		{
			print "<div><a onclick=\"setCookie('ef_inline_edit','');alert('You left inline edit mode. Refresh the screen or click a link to hide the links.');\" class='plainlink'>Leave inline edit mode</a></div>";
		}

		print "<table style='width: 100%;' class='sortable' id='extrafieldjes'>";
		print "<thead><tr><td>ID</td><td>Order</td><td>Name</td><td>Field type</td><td>Occurences</td>";
		print "<td " . PrintToolTipCode("Detailed Access Restrictions") . ">Access</td><td>Conditions</td><td>Required</td><td>Delete</td>";
		print "</tr></thead>";

		$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE tabletype='" . $tabletype . "' AND deleted<>'y' ORDER BY ordering";

		$result = mcq($sql,$db);
		while ($row = mysql_fetch_array($result))
		{
			if (strstr($row['fieldtype'], "[copyfield"))
			{
				$mouseout = "onmouseover=\"style.background='#FFFFCC';\" onmouseout=\"style.background='#E8E8E8';\"";
				$style = " background-color: #E8E8E8;";
			}
			else
			{
				$mouseout = "onmouseover=\"style.background='#E9E9E9';\" onmouseout=\"style.background='#FFFFFF';\"";
				unset($style);
			}

			print "<tr " . $mouseout . " style='cursor: pointer; " . $style . "'>";
			print "<td onclick='document.location=\"extrafields.php?editextrafield=" . $row['id'] . "&amp;tabletype=" . $_REQUEST['tabletype'] . "\";'>" . $row['id'] . "</td><td onclick='document.location=\"extrafields.php?editextrafield=" . $row['id'] . "&amp;tabletype=" . $_REQUEST['tabletype'] . "\";' >" . $row['ordering'] . "</td><td onclick='document.location=\"extrafields.php?editextrafield=" . $row['id'] . "&amp;tabletype=" . $_REQUEST['tabletype'] . "\";' ><strong>";
			if ($row['fieldtype'] == "comment")
			{
				print $row['name'] . " [" . GetTemplateName($row['options']) . "]";
			}
			else
			{
				print $row['name'];
			}
			print "</strong></td><td onclick='document.location=\"extrafields.php?editextrafield=" . $row['id'] . "&amp;tabletype=" . $_REQUEST['tabletype'] . "\";' >" . strtolower($row['fieldtype']) . "</td><td>";
			$sql2 = "SELECT COUNT(EFID" . $row['id'] . ") AS count FROM " . $GLOBALS['TBL_PREFIX'] . $tablename . " WHERE EFID" . $row['id'] . "!=''";
			$result2 = mcq($sql2,$db);
			$row2 = mysql_fetch_array($result2);
			print $row2['count'];
			print "</td>";

			if (is_array(GetExtraFieldAccessRestrictions($row['id'])))
			{
				$txt = "<td style='background-color: #FFFFCC;' " . PrintToolTipCode('Specific access rights are set for this field.') . "><a href='javascript:PopRightsChooser(" . $row['id'] . ");'><img src='images/entity_popup3.gif' alt=''></a> Apply";
			}
			else
			{
				$txt = "<td><a href='javascript:PopRightsChooser(" . $row['id'] . ");'><img src='images/entity_popup3.gif' alt=''></a> None set";
			}
			$txt .= "</td>";
			$tmp = GetExtraFieldConditions($row['id']);
			if (is_array($tmp[0]))
			{
				$txt .= "<td style='background-color: #FFFFCC;' " . PrintToolTipCode('Specific conditions are set for this field.') . "><a href='javascript:PopExtrafieldConditionsChooser(" . $row['id'] . ");'><img src='images/entity_popup3.gif' alt=''></a> Apply";
			}
			else
			{
				$txt .= "<td><a href='javascript:PopExtrafieldConditionsChooser(" . $row['id'] . ");'><img src='images/entity_popup3.gif' alt=''></a> None set";
			}
			$txt .= "</td>";

	//		PopExtrafieldConditionsChooser(" . $_REQUEST['editextrafield'] . ");
			if ($row['forcing'] == "y")
			{
				$txt2 = "<td style='background-color: #FFFFCC;' onclick='document.location=\"extrafields.php?editextrafield=" . $row['id'] . "&amp;tabletype=" . $_REQUEST['tabletype'] . "\";' >Yes</td>";
			}
			else
			{
				$txt2 = "<td onclick='document.location=\"extrafields.php?editextrafield=" . $row['id'] . "&amp;tabletype=" . $_REQUEST['tabletype'] . "\";' >No</td>";
			}
			print $txt;

			//print "<td>" . strtoupper(str_replace(" ", "_", strip_tags($row['name']))) . "</td>";

			print $txt2;
			print "<td><a href='extrafields.php?tabletype=" . $_REQUEST['tabletype'] . "&amp;delfield=" . $row['id'] ."&amp;ti=" . urlencode($_REQUEST['ti']) . "'><img src='images/delete.gif' alt=''></a></td></tr>";
		}
		print "</table>";
		} // end if req tabletype
	}
	print "</td></tr></table>";
} // end if access
EndHTML();

function ReturnListOfFieldOccurrences($fid) {
	$f = db_GetRow("SELECT id,name,tabletype,fieldtype,deleted FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE id=" . $fid);
	
	$del = array();

	$ret = "";

	$found = "";
	$alias = ("" . strtoupper(str_replace(" ", "_", $f['name'])) . "");
	$name = "EFID" . $f['id'] . "";
	$id = $f['id'];
	$ft = $f['fieldtype'];
	
	if ($f['deleted'] == "y") {
		$delopm = " (deleted logically)";
	} else {
		$delopm = "";
	}
	
	
	$tt = $f['tabletype'];
	if (is_numeric($tt)) {
		$tt = "flextable" . $tt;
	}
	
		
	$tmp = db_GetArray("SELECT templateid,templatename FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE (content LIKE '%@" . mres($alias) . "@%' OR content LIKE '%@" . $name . "@%' OR content LIKE '%#" . mres($alias) . "#%' OR content LIKE '%#" . $name . "#%')");
	
	foreach ($tmp AS $template) {
		$ret .=  "<li>Found in template <a href='admin.php?templates=1&editHTMLtemplate=" . $template['templateid'] . "&nav=all'>" . $template['templateid'] . " : " . $template['templatename'] . "</a></li>";
		$found = true;
	}

	$tmp = db_GetArray("SELECT id,name FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE (options LIKE '%" . mres($alias) . "@' OR options LIKE '%" . $name . "%' OR fieldtype='[copyfield" . $id . "]') AND id!='" . $id . "'");
	
	foreach ($tmp AS $template) {
		$ret .=  "<li>Found in extra field options <a href='extrafields.php?editextrafield=" . $template['id'] . "'>" . $template['id'] . " : " . $template['name'] . "</a></li>";
		$found = true;
	}

/*

	$tmp = db_GetArray("SELECT tid,onchange FROM " . $GLOBALS['TBL_PREFIX'] . "triggers WHERE (onchange LIKE '%" . mres($alias) . "%' OR onchange LIKE '%" . $name . "%' OR to_value LIKE '%" . mres($alias) . "%' OR to_value LIKE '%" . $name . "%')");
	
	foreach ($tmp AS $template) {
		$ret .=  "<li>Found in trigger " . $template['tid'] . " : " . $template['onchange'] . "</li>";
		$found = true;
	}
*/	
	$tmp = db_GetArray("SELECT conid FROM " . $GLOBALS['TBL_PREFIX'] . "extrafieldconditions WHERE field ='" . $name . "'");
	
	foreach ($tmp AS $template) {
		$ret .=  "<li>Found in extra field condition " . $template['conid'] . "</li>";
		$found = true;
	}
	$tmp = db_GetArray("SELECT conid,triggerid FROM " . $GLOBALS['TBL_PREFIX'] . "triggerconditions WHERE field ='" . $name . "'");
	foreach ($tmp AS $template) {
		$ret .=  "<li>Found in trigger condition " . $template['conid'] . " beloning to trigger " . $template['triggerid'] . "</li>";
		$found = true;
	}
	$tmp = db_GetArray("SELECT mid,module_name FROM " . $GLOBALS['TBL_PREFIX'] . "modules WHERE (module_code LIKE '%" . mres($alias) . "%' OR module_code LIKE '%" . $name . "%')");
	
	foreach ($tmp AS $template) {
		$ret .=  "<li>Found in module <a href='modules.php?action=edit&mid=" . $template['mid'] . "'>" . $template['mid'] . " : " . $template['module_name'] . "</a></li>";
		$found = true;
	}
	if (!$found) {
		$ret = "No reference to this field found in modules, templates, triggers, other extra fields and conditions.";
	} else {
		$ret = "<ul style='margin-left: -22px;'>" . $ret . "</ul>";
	}

	
	return($ret);
}
?>