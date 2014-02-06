<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 info@interleave.nl
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This is the page that pops up when setting detailed extra field access restrictions
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
print "</div><div id=\"MainAdminContents\">";
if (CheckFunctionAccess("ExtrafieldAdmin") <> "ok" && !is_administrator()) {
	PrintAD("Access to this page/function denied.");
} else {

	$count = 0;

	if ($_REQUEST['submitted'] == "Save/add") {
			$newcolors = array();
	
			for ($i=0;$i<count($_REQUEST['optionvalue']);$i++) {
				if ($_REQUEST['varcolor'][$i] != "") {
					if ($_REQUEST['operand'][$i] == "EQ") {
						$newcolors[$_REQUEST['optionvalue'][$i]] = $_REQUEST['varcolor'][$i];
					} else {
						$newcolors['operand' . $i]['color'] = $_REQUEST['varcolor'][$i];
						$newcolors['operand' . $i]['operand'] = $_REQUEST['operand'][$i];
						$newcolors['operand' . $i]['select'] = $_REQUEST['optionvalue'][$i];
					}
				}
			}
			
			mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "extrafields SET optioncolors='" . mres(serialize($newcolors)) . "' WHERE id='" . mres($_REQUEST['efid']) . "'", $db);
	}



	$options = unserialize(db_GetValue("SELECT optioncolors FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE id='" . mres($_REQUEST['efid']) . "'"));
	$count=0;
	if (!is_array($options)) $options = array();


	if (GetExtraFieldType($_REQUEST['efid']) == "drop-down") {
		$optionslist = unserialize(db_GetValue("SELECT options FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE id='" . mres($_REQUEST['efid']) . "'"));	

		foreach ($optionslist AS $dd) {
			if (!$options[$dd]) {
				$options[$dd] = "";
			}

		}
	}

		
	print "<h1>Assign colors to field values</h1>";
	print "<h2>Field " . $_REQUEST['efid'] . ": " . GetExtraFieldName($_REQUEST['efid']) . "</h2>";
	
	print "<form id='optioncolors' method='post' action=''><div class='showinline'>";
	print "<table class='crm' width='75%'><thead><tr><td><strong>Operand</strong></td><td><strong>Field value</strong></td><td><strong>Color</strong></td></tr></thead>";
	foreach ($options AS $opt => $vals) {
		
			/*
			Array
			(
				[a] => #000000
				[operand1] => Array
					(
						[color] => #009933
						[operand] => LT
						[select] => 1000
					)

				[c] => 
				[d] => 
				[e] => 
			)
			*/
		if (substr($opt,0,7) == "operand") {
			$loc_option = $vals['select'];;
			$loc_color = $vals['color'];
			$loc_operand = $vals['operand'];
		} else {
			$loc_option = $opt;
			$loc_color = $vals;
			$loc_operand = "EQ";
		}
		print "<tr><td>";
		$sel = "";
		print "<select name='operand[]'>";
		$sel = ($loc_operand == "EQ") ? "selected=\"selected\"" : "";
		print "<option value='EQ' " . $sel . ">Equals</option>";
		$sel = ($loc_operand == "GT") ? "selected=\"selected\"" : "";
		print "<option value='GT' " . $sel . ">Greater than / after</option>";
		$sel = ($loc_operand == "LT") ? "selected=\"selected\"" : "";
		print "<option value='LT' " . $sel . ">Lower than / before</option>";
		$sel = ($loc_operand == "HAS") ? "selected=\"selected\"" : "";
		print "<option value='HAS' " . $sel . ">Contains</option>";
		print "</select></td><td>";

		print "<input type='text' name='optionvalue[]' value='" . htme($loc_option) . "'></td>";
		print "<td><input style='background-color: " . $loc_color . ";' type='text' name='varcolor[]' id='JS_varcolor" . $count . "' value='" . $loc_color . "' class=\"ColorPickerField\">&nbsp;";
		print "</td>";
		print "</tr>";
		$count++;
	}
	print "<tr><td>";
	$sel = "";
	print "<select name='operand[]'>";
	print "<option value='EQ'" . $sel . ">Equals</option>";
	print "<option value='GT' " . $sel . ">Greater than / after</option>";
	print "<option value='LT' " . $sel . ">Lower than / before</option>";
	print "<option value='HAS' " . $sel . ">Contains</option>";
	print "</select></td><td>";

	print "<input type='text' name='optionvalue[]' value=''></td>";
	print "<td><input type='text' name='varcolor[]' id='JS_varcolor" . $count . "' value='' class=\"ColorPickerField\">&nbsp;";
	print "</td>";
		print "</tr>";



	/*
	for ($i=0;$i<5;$i++) {
		$count++;
		print "<tr><td><input type='text' name='extraoption[]'></td><td><input type='text' name='varcolor" . $count . "' id='JS_varcolor" . $count . "' value='" . $colors[$opt] . "'>&nbsp;<a href='#' onclick=\"popcolorchooser('" . htme($opt) . "','JS_varcolor" . $count . "')\"><img src='images/choose_color.gif' alt=''></a></td></tr>";
	}*/
	print "</table><br><br>";
	print "<input type='hidden' name='efid' value='" . $_REQUEST['efid'] . "'><input type='hidden' name='tabletype' value='" . $_REQUEST['tabletype'] . "'><input type='submit' name='submitted' value='Save/add'>&nbsp;&nbsp;<input type='button' name='Close' value='Close' onclick='parent.$.fancybox.close();'>";
	print "</div></form>";


}
EndHTML();
?>