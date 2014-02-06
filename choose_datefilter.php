<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 info@interleave.nl
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This is the page that pops up when setting detailed module access restrictions
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
require_once("initiate.php");
$_REQUEST['nonavbar'] = 1;
ShowHeaders();




$filter = GetLastUserFilter();

// Build entity fields list

$efl = CreateDateFieldsList();

// Save filter array

if ($_REQUEST['submitted'] == 1) {
	$fa = array();
	foreach ($efl AS $field => $name) {
		if (isset($_REQUEST['REL_BEFORE_' . $field]) && $_REQUEST['REL_BEFORE_' . $field] != "") {
			$fa['REL_BEFORE_' . $field] = $_REQUEST['REL_BEFORE_' . $field];
		} elseif (isset($_REQUEST['BEFORE_' . $field]) && $_REQUEST['BEFORE_' . $field] != "") {
			$fa['BEFORE_' . $field] = $_REQUEST['BEFORE_' . $field];
		}
		if (isset($_REQUEST['REL_AFTER_' . $field]) && $_REQUEST['REL_AFTER_' . $field] != "") {
			$fa['REL_AFTER_' . $field] = $_REQUEST['REL_AFTER_' . $field];
		} elseif (isset($_REQUEST['AFTER_' . $field]) && $_REQUEST['AFTER_' . $field] != "") {
			$fa['AFTER_' . $field] = $_REQUEST['AFTER_' . $field];
		}
		if (isset($_REQUEST['SHOWEMPTY_' . $field]) && $_REQUEST['SHOWEMPTY_' . $field] == "yes") {
			$fa['SHOWEMPTY_' . $field] = "yes";
		} else {
			$fa['SHOWEMPTY_' . $field] = "no";
		}

	}

	$filter['datefilter'] = $fa;

	SetUserFilter($filter);
	?>
			<script type="text/javascript">
		<!--
			parent.refresh_<?php echo $_REQUEST['ParentAjaxHandler'];?>();
			parent.$.fancybox.close();
		//-->
		</script>
	<?php
} else {
	$tmp = GetLastUserFilter();
	$fa = $tmp['datefilter'];

}

$relative_array = GetRelativeDateArray();

print "<form id=\"DateFilterForm\" method=\"post\" action=\"\"><div class='showinline'>";
print "<table class='nicetable'>";

print "<tr><td><strong>" . $lang['datefield'] . "</strong></td><td><strong>" . $lang['mustbeafter'] . "</strong></td><td><strong>" . $lang['mustbebefore'] . "</strong></td><td><strong>" . $lang['showwhenempty'] . "</strong></td>";
print "<td><strong>Min/max</strong></td></tr>";
foreach ($efl AS $row => $name) {

	// Select current minimum and maximum values in the database (excluding deleted values)
	$field = $row;
	if (is_numeric($field)) {
		$type = GetExtraFieldTableType($field);
		if ($type == "") {
			$type = "entity";
		}
		$dbfield = $GLOBALS['TBL_PREFIX'] . $type . ".EFID" . $field;
		if (is_numeric($type)) {
			$table = $GLOBALS['TBL_PREFIX'] . "flextable" . $type;
		} else {
			$table = $GLOBALS['TBL_PREFIX'] . $type;
		}
	} else {
		$dbfield = $GLOBALS['TBL_PREFIX'] . "entity." . $field;
		$table = $GLOBALS['TBL_PREFIX'] . "entity";
	}
	if (strstr($field, "epoch")) {
		$min = db_GetRow("SELECT MIN(" . $dbfield . ") FROM " . $table . " WHERE " . $dbfield . "!=''");
		$max = db_GetRow("SELECT MAX(" . $dbfield . ") FROM " . $table . " WHERE " . $dbfield . "!=''");
	} else {
		$min = db_GetRow("SELECT MIN(UNIX_TIMESTAMP(CONCAT(SUBSTR(" . $dbfield . ",7,4), SUBSTR(" . $dbfield . ",4,2), SUBSTR(" . $dbfield . ", 1,2)))) FROM " . $table . " WHERE " . $dbfield . " != ''");
		$max = db_GetRow("SELECT MAX(UNIX_TIMESTAMP(CONCAT(SUBSTR(" . $dbfield . ",7,4), SUBSTR(" . $dbfield . ",4,2), SUBSTR(" . $dbfield . ", 1,2)))) FROM " . $table . " WHERE " . $dbfield . " != ''");
	}


	print "<tr><td>" . $name . "</td>";
	print "<td><input type=\"hidden\" size=\"10\" name=\"AFTER_" . $row . "\" value=\"" . $fa['AFTER_' . $row] . "\"><input type=\"text\" size=\"10\" name=\"AFTER_" . $row . "HF\" value=\"" . TransformDate($fa['AFTER_' . $row]) . "\" onchange=\"document.forms['DateFilterForm'].elements['AFTER_" . $row . "'].value=AdjustDateFromPreferenceFormatToDutchFormat(document.forms['DateFilterForm'].elements['AFTER_" . $row . "HF'].value);\"><a href='#' onclick=\"popcalendarSelect(\"forms['DateFilterForm'].elements['AFTER_" . $row ."']\");\"> <img src='images/calendar_icon.gif' " . PrintToolTipCode("Calendar") . " style='cursor: pointer;' alt=''></a>";

	print "<br>- or -<br>";
	print "<select name='REL_AFTER_" . $row . "'>";
	print "<option value=''> - </option>";
	foreach ($relative_array AS $tag => $desc) {
		if ($fa['REL_AFTER_' . $row] == $tag) {
			$ins = "selected='selected'";
		} else {
			unset($ins);
		}
		print "<option " . $ins . " value='" . htme($tag) . "'>" . htme($desc) . "</option>";
	}
	print "</select>";
	print "</td>";

	print "<td><input type=\"hidden\" size=\"10\" name=\"BEFORE_" . $row . "\" value=\"" . $fa['BEFORE_' . $row] . "\"><input type=\"text\" size=\"10\" name=\"BEFORE_" . $row . "HF\" value=\"" . TransformDate($fa['BEFORE_' . $row]) . "\" onchange=\"document.forms['DateFilterForm'].elements['BEFORE_" . $row . "'].value=AdjustDateFromPreferenceFormatToDutchFormat(document.forms['DateFilterForm'].elements['BEFORE_" . $row . "HF'].value);\"><a href='#' onclick=\"popcalendarSelect(\"forms['DateFilterForm'].elements['BEFORE_" . $row ."']\");\"> <img src='images/calendar_icon.gif' " . PrintToolTipCode("Calendar") . " style='cursor: pointer;' alt=''></a>";
	print "<br>- or -<br>";
	print "<select name='REL_BEFORE_" . $row . "'>";
	print "<option value=''> - </option>";
	foreach ($relative_array AS $tag => $desc) {
		if ($fa['REL_BEFORE_' . $row] == $tag) {
			$ins = "selected='selected'";
		} else {
			unset($ins);
		}

		print "<option " . $ins . " value='" . htme($tag) . "'>" . htme($desc) . "</option>";
	}

	print "</select>";
	print "</td>";
	if ($fa['SHOWEMPTY_' . $row] == "yes") {
		$ins = "checked='checked'";
	} else {
		$ins = "";
	}

	print "<td><input " . $ins . " type=\"checkbox\" size=\"10\" name=\"SHOWEMPTY_" . $row . "\" value=\"yes\"></td>";
	print "<td>Min: &nbsp;" . TransformDate(date('d-m-Y', $min[0])) . "<br>";
	print "Max: " . TransformDate(date('d-m-Y', $max[0])) . "</td>";
	print "</tr>";
}
print "<tr><td colspan='5'>";
print "<input type='hidden' name='ParentAjaxHandler' value='" . $_REQUEST['ParentAjaxHandler'] . "'><input type='hidden' name='submitted' value='1'><input type=\"submit\" name=\"SubmitButton\" value=\"" . $lang['save'] . "\">";
print "</tr>";
print "</table>";
print "</div></form>";
// may also be empty checkbox

EndHTML();
?>