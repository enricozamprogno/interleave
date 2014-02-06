<?php
/* ********************************************************************
* Interleave
* Copyright (c) 2001-2012 info@interleave.nl
* Licensed under the GNU GPL. For full terms see http://www.gnu.org/
*
* This file handles attributes of any type
*
* Check http://www.interleave.nl/ for more information
*
* BUILD / RELEASE :: 5.5.1.20121028
*
**********************************************************************
*/require_once("initiate.php");
require("attributedocumentation.php");

$_REQUEST['nonavbar'] = 1;
ShowHeaders();
print "</div><div id=\"MainAdminContents\">";
$largebox = false;

switch ($_REQUEST['ParentReference']) {
	case "module":
		if (!is_administrator()) {
			PrintAD("Access to this page/function denied.");
			$stop = true;
		} else {
			SafeModeInterruptCheck();
			$item = "module";
			$item_plural = "modules";
			$description = "\"" . db_GetValue("SELECT module_description FROM " . $GLOBALS['TBL_PREFIX'] . "modules WHERE mid='" . $_REQUEST['EditAttributes'] . "'") . "\"";
		}
	break;
	case "user":
		if (CheckFunctionAccess("UserAdmin") <> "ok" && !is_administrator()) {
			PrintAD("Access to this page/function denied.");
			$stop = true;
		} else {
			$item = "user";
			$item_plural = "users";
			$description = GetUserName($_REQUEST['EditAttributes']);
		}
	break;
	case "group":
		if (CheckFunctionAccess("UserAdmin") <> "ok" && !is_administrator()) {
			PrintAD("Access to this page/function denied.");
			$stop = true;
		} else {
			$item = "group";
			$item_plural = "groups";
			$description = GetGroupName($_REQUEST['EditAttributes']);
		}
	break;
	case "flextable":
		MustBeAdmin();
		SafeModeInterruptCheck();
		$item = "flextable";
		$item_plural = "flextables";
		$description = GetFlextableNames($_REQUEST['EditAttributes']);
		$description = $description[0];
	break;
	case "extrafield":
		if (CheckFunctionAccess("ExtrafieldAdmin") <> "ok" && !is_administrator()) {
			PrintAD("Access to this page/function denied.");
			$stop = true;
		} else {
			$item = "extrafield";
			$item_plural = "extra fields";
			$description = $_REQUEST['EditAttributes'] . ": \"" . GetExtraFieldName($_REQUEST['EditAttributes']) . "\"";
		}
	break;
	case "template":
		if (CheckFunctionAccess("TemplateAdmin") <> "ok" && !is_administrator()) {
			PrintAD("Access to this page/function denied.");
			$stop = true;
		} else {
			$item = "template";
			$item_plural = "templates";
			$description = $_REQUEST['EditAttributes'] . ": \"" . GetTemplateName($_REQUEST['EditAttributes']). "\"";
		}
	break;
	case "file":
		$item = "file";
		$item_plural = "files";
		$description = $_REQUEST['EditAttributes'] . ": \"" . GetFileName($_REQUEST['EditAttributes']). "\"";
	break;
	case "trigger":
		if (CheckFunctionAccess("TriggerAdmin") <> "ok" && !is_administrator()) {
			PrintAD("Access to this page/function denied.");
			$stop  = true;
		} else {		
			$item = "trigger";
			$item_plural = "triggers";
			$description = $_REQUEST['EditAttributes'];
		}
	break;
	case "system":
		if (!is_administrator()) {
			PrintAD("Access to this page/function denied.");
			$stop  = true;
		} else {		
			$item = "system";
			$item_plural = "system settings";
			$description = $_REQUEST['EditAttributes'];
		}
	break;
	
	default: 
	$error = 1;
	break;
}

if ($error) {

	PrintAD("No parent reference found.");

} elseif ($stop) {

	qlog(INFO, "A user tried to access attributes but isn't allowed to");

} elseif ($_REQUEST['EditAttributes'] || isset($_REQUEST['EditAttribute']) || $_REQUEST['OpenDirect'] != "") {
		
		if ($_REQUEST['OpenDirect']) $_REQUEST['EditAttribute'] = base64_encode($_REQUEST['OpenDirect']);

		$t = GetAttribute($item, "%", $_REQUEST['EditAttributes']);

		$tmp = GetMissingAttributes($item, $_REQUEST['EditAttributes']);
		for ($x=0;$x<=sizeof($tmp);$x++) {
			if (array_key_exists($tmp[$x], $t)) {
				unset($tmp[$x]);
			}
		}
		
		foreach ($atdocs AS $key => $value) {
			if (strstr(base64_decode($_REQUEST['EditAttribute']), $key)) {
				$doc = $key;
			}
		}
		
		if (is_array($atdocs[$doc])) {
			print "<h1>" . $atdocs[$doc][0] . "</h1>";
			print "<p>" . $atdocs[$doc][1] . "</p>";
		} else {
			if ($item == "system") {
				print "<h1>System attributes</h1>";
			} else {
				print "<h1>Attributes for " . $item . " " . $description . " :: " . base64_decode($_REQUEST['EditAttribute']) . "</h1>";
			}
			print "<h2>Attributes are used for saving information about " . $item_plural . " dynamically.</h2>";
		}

		if (isset($_POST['NewAttributeName']) && $_POST['NewAttributeValue']) {
			if ($_POST['NewAttributeName'] == "") {
				$newattr = $_POST['NewAttributeNameSelect'];
			} else {
				$newattr = $_POST['NewAttributeName'];
			}
			SetAttribute($item, $newattr, $_POST['NewAttributeValue'], $_REQUEST['EditAttributes']);

		}
		
		if ($_REQUEST['DeleteAttribute']) {
			SetAttribute($item, base64_decode($_REQUEST['DeleteAttribute']), "", $_REQUEST['EditAttributes']);
		}
		if (isset($_REQUEST['DestroySaveActions'])) {
			$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "attributes WHERE attribute LIKE 'SaveAction%' AND identifier='" . mres($_REQUEST['ParentReference']) . "' AND entity='" . mres($_REQUEST['EditAttributes']) . "'";
			mcq($sql, $db);
		}
		

						
		print "<form name='AddAttribute' method='post' action='attribute.php?nonavbar=1&amp;EditAttributes=" . $_REQUEST['EditAttributes'] . "&amp;ParentReference=" . $item . "'>";
		if (!$_REQUEST['EditAttribute']) {
			print "<strong>Add a new attribute</strong>";
		}
		print "<table width='100%'>";
		if (!$_REQUEST['EditAttribute']) {
			print "<tr><td>Name</td><td>";
		}
		
		

		if (count($tmp) > 0 && !$_REQUEST['DeleteAttribute'] && !$_REQUEST['EditAttribute']) {
			print "<div id='NewAttrSel' class='showinline'>";
			print "<select name='NewAttributeNameSelect' id='JS_NewAttributeNameSelect'>";
			foreach ($tmp AS $at) {
				print "<option value='" . htme($at) . "'>" . htme($at) . "</option>";
			}
			print "</select></div>";

			print "&nbsp;&nbsp;<div id='AddPlus' class='showinline'><img class='pointer' src='images/icon-add.gif' alt='' onclick=\"hideLayer('AddPlus');hideLayer('NewAttrSel');showLayer('NewAttrMan');\"></div>";

			print "<div id='NewAttrMan' style='display: none;' class='showinline'>";
			print "<input type='text' size='50' name='NewAttributeName' id='JS_NewAttributeName' value='" . htme($tmp_new_attr) . "'>";	
			print "</div>";
		} elseif ($_REQUEST['EditAttribute'] != "") {
			if ($_REQUEST['NewAttributeValue']) {
				SetAttribute($item, base64_decode($_REQUEST['EditAttribute']), $_REQUEST['NewAttributeValue'], $_REQUEST['EditAttributes']);
				
				//print "<input type='text' size='50' name='NewAttributeName' id='JS_NewAttributeName' value=''>";	
				
				if ($_REQUEST['close_on_next_load'] == 1) {
					 if (substr(base64_decode($_REQUEST['EditAttribute']), strlen(base64_decode($_REQUEST['EditAttribute'])) -3,3) == "PHP") {
						$result = ValidatePHPSyntax($_REQUEST['NewAttributeValue']);
						if (stristr($result, "parse error")) {
							$_REQUEST['OpenDirect'] = 1;
							print '<script type="text/javascript">alert("This code generates a parse error.");</script>';
						} else {
							print '<script type="text/javascript">parent.$.fancybox.close();</script>';
						}
					 } else {
						print '<script type="text/javascript">alert("' . base64_decode($_REQUEST['EditAttribute']) . '");parent.$.fancybox.close();</script>';
					 }
				} else {
					unset($_REQUEST['EditAttribute']);
				}
			}
			if ($_REQUEST['EditAttribute']) {
				$tmp_new_val = GetAttribute($item, base64_decode($_REQUEST['EditAttribute']), $_REQUEST['EditAttributes']);
				print "<input type='hidden' size='50' name='DisabledNewAttributeName' id='JS_NewAttributeName' value='" . htme(base64_decode($_REQUEST['EditAttribute'])) . "'>";	
				print "<input type='hidden' name='EditAttribute' value='" . htme($_REQUEST['EditAttribute']) . "'>";	
				print "<input type='hidden' name='ParentReference' value='" . htme($item) . "'>";	
				print "<input type='hidden' name='EditAttributes' value='" . htme($_REQUEST['EditAttributes']) . "'>";	
			}			

		} else {
			print "<input type='text' size='50' name='NewAttributeName' id='JS_NewAttributeName' value=''>";	
		}

		if (!$_REQUEST['EditAttribute']) {
			print"</td></tr>";
			print "<tr><td>Value:</td><td>";
		} else {
			print "<tr><td>";
		}
		$arr = GetAttributeAllowedOptions($item, base64_decode($_REQUEST['EditAttribute']));
		if (is_array($arr)) {
			print "<select name='NewAttributeValue' id='JS_NewAttributeValue'>";
			foreach ($arr AS $option) {
				$ins = "";
				if ($tmp_new_val == $option) {
					$ins = " selected=\"selected\"";
				} 
				print "<option value=\"" . htme($option) . "\" " . $ins . ">" . htme($option) . "</option>";
			}
			print "</select>";
		} else {
			if (substr(base64_decode($_REQUEST['EditAttribute']), strlen(base64_decode($_REQUEST['EditAttribute'])) -4,4) == "HTML") {
				print "<textarea name='NewAttributeValue' id='JS_NewAttributeValue' cols=80 rows=5>" . htme($tmp_new_val) . "</textarea>";
				print make_html_editor("JS_NewAttributeValue", false, false, true, "300", "95%");

			} elseif ($largebox || stristr(base64_decode($_REQUEST['EditAttribute']), "text")) {
				print "<textarea name='NewAttributeValue' id='JS_NewAttributeValue' cols=80 rows=5>" . htme($tmp_new_val) . "</textarea>";
			} elseif (substr(base64_decode($_REQUEST['EditAttribute']), strlen(base64_decode($_REQUEST['EditAttribute'])) -3,3) == "PHP") {
					print "</td></tr><tr><td colspan=\"2\">";
					if ($tmp_new_val != "{{none}}" && $tmp_new_val != "") {
						print ValidatePHPSyntax($tmp_new_val) . "<br>";
					}
					?>
					<script type="text/javascript" src="lib/editarea/edit_area/edit_area_full.js"></script>
					<script type="text/javascript">
					editAreaLoader.init({
						id : "JS_NewAttributeValue"			// textarea id
						,start_highlight: true	// if start with highlight
						,allow_resize: "both"
						,syntax_selection_allow: "css,html,js,php"
						,word_wrap: true
						,allow_toggle: true
						,language: "en"
						,syntax: "php"
						,font_size: "9"
						,font_family: "monospace"

					});
					</script>
					<?php
					print "<textarea id='JS_NewAttributeValue' rows='15' cols='100' name='NewAttributeValue' class='mnspc'>" . htme($tmp_new_val) . "</textarea>";
			} elseif (stristr(base64_decode($_REQUEST['EditAttribute']), "numof")) {
				print "<input type='text' name='NewAttributeValue' size='5' id='JS_NewAttributeValue' value='" . htme($tmp_new_val) . "'>";

			} else {
				print "<input type='text' name='NewAttributeValue' size='50' id='JS_NewAttributeValue' value='" . htme($tmp_new_val) . "'>";
			}
		}
		if ($_REQUEST['OpenDirect']) {
			print "<input type='hidden' name='close_on_next_load' value='1'>";
		}
		print "&nbsp;<input type='submit' value='Go!'></td></tr>";
		print "</table><br>";
		if (!isset($_REQUEST['EditAttribute'])) {
			$t = GetAttribute($item, "%", $_REQUEST['EditAttributes']);

			$tmp = GetMissingAttributes($item, $_REQUEST['EditAttributes']);
			for ($x=0;$x<=sizeof($tmp);$x++) {
				if (array_key_exists($tmp[$x], $t)) {
					unset($tmp[$x]);
				}
			}

			$showdeleteSAlink = false;

			print "<table class='crm' ><thead><tr><td>Attribute name</td><td>Value</td><td>Description</td><td>Delete</td></tr></thead>";
			foreach ($t AS $field => $value) {
				$toshow = "";
				if ($tmp = unserialize($value)) {
					$nf = false;
					foreach ($tmp AS $key => $val) {
						$toshow .= ($nf) ? ", " : "";
						if ($val) {
							$toshow .= $val;
						} else {
							$toshow .= $key;
						}
						$nf = true;

					}
				} else {
					$toshow = $value;
				}
				$href1 = "";
				$href2 = "";

				if ($field == "LastLogin" || $field == "LastLogout" || $field == "LastActivity") {
					$toshow = TransFormDate(date('d-m-Y', $toshow)) . " " . date('H:i', $toshow);
				}
				if (!$showdeleteSAlink && substr($field, 0, 10) == "SaveAction") {
					$showdeleteSAlink = true;
				}
				$doc = "";
				foreach ($atdocs AS $key => $value) {
					if (strstr($field, $key)) {
						$doc = $atdocs[$key][1];
					} 
				}
				if (!is_array($tmp)) {
					$href1 = " style=\"cursor: pointer\" onmouseover=\"HL(this)\" onmouseout=\"UL(this)\" onclick=\"document.location='?EditAttribute=" . htme(base64_encode($field)) . "&amp;ParentReference=" . htme($item) . "&amp;EditAttributes=" . htme($_REQUEST['EditAttributes']) . "'\"";
					//$href2 = "</a>";
				}
				print "<tr " . $href1 . " ><td>" . $field . "</td><td>" .  fillout(htme($toshow),40,true) .   "</td><td>" .  $doc .  "</td><td><a href='attribute.php?nonavbar=1&amp;EditAttributes=" . $_REQUEST['EditAttributes'] . "&amp;DeleteAttribute=" . base64_encode($field) . "&amp;ParentReference=" . $item . "'><img src='images/delete.gif'></a></td></tr>";
			}

			print "</table>";
		}
		if ($showdeleteSAlink) {
			print "<br><a href=\"?DestroySaveActions&amp;EditAttributes=" . $_REQUEST['EditAttributes'] . "&amp;ParentReference=" . $item . "\">Delete all SaveAction attributes</a>";
		}
		print "</form>";

}
print "</div>";
EndHTML();

?>