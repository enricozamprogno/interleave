<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 info@interleave.nl
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This function can set a customtab on a certain action
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
require_once("initiate.php");
ShowHeaders();
MustBeAdmin();

AdminTabs("customtabs");
AddBreadCrum("Menu items");


//MainAdminTabs("");
$to_tabs = array("overview","add", "tabs", "customtabs");
$tabbsw["overview"] = array("customtabs.php?ovw=1" => "Current menu items");
//$tabbs["overviewb"] = array("customtabs.php?ovwb=1" => "Current global bookmarks");
$tabbsw["add"] = array("customtabs.php?add=1" => "Add a new menu item");
$tabbsw["tabs"] = array("customtabs.php?tabs=1" => "Main menu tabs");
$tabbsw["customtabs"] = array("customtabs.php?customtabs=1" => "Custom tab menus");

//$tabbs["addb"] = array("customtabs.php?addb=1" => "Add a new global bookmark");
if ($_REQUEST['add']) {
	$navid = "add";
} elseif ($_REQUEST['addb']) {
	$navid = "addb";
} elseif ($_REQUEST['ovwb']) {
	$navid = "overviewb";
} elseif ($_REQUEST['tabs']) {
	$navid = "tabs";
} elseif ($_REQUEST['customtabs']) {
	$navid = "customtabs";
} else {
	$navid = "overview";
}
InterTabs($to_tabs, $tabbsw, $navid);

if ($_REQUEST['customtabs']) {
	if ($_REQUEST['new_menu_name']) {
		mcq("INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "tabmenudefinitions (menu_name) VALUES('" . mres($_REQUEST['new_menu_name']) . "')", $db);
	} elseif ($_REQUEST['deletemenu']) {
		mcq("DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "tabmenudefinitions WHERE id='" . mres($_REQUEST['deletemenu']) . "'", $db);
	} elseif ($_REQUEST['SubmitForm'] == "Save as new menu") {
		mcq("INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "tabmenudefinitions (menu_name) VALUES('" . mres($_REQUEST['new_menu_name']) . "')", $db);
		$_REQUEST['editmenu'] = mysql_insert_id();
		print "Created menu " . $_REQUEST['editmenu'] . "<br><br>";
	}
	print "<h1>Custom tab menus</h1>";



	if ($_REQUEST['editmenu']) {
		if ($_REQUEST['menuname']) {
			$nm = array();
			$ac = 0;
			for ($c=0;$c<64;$c++) {
				if ($_REQUEST['name' . $c] != "") {
					$nm[$ac]['name'] = $_REQUEST['name' . $c];
					$nm[$ac]['link'] = $_REQUEST['link' . $c];
					$nm[$ac]['color'] = $_REQUEST['color' . $c];
					$ac++;
				}
			}
			mcq("UPDATE  " . $GLOBALS['TBL_PREFIX'] . "tabmenudefinitions SET menu_name='" . mres($_REQUEST['menuname']) . "', menu_type='" . mres($_REQUEST['menutype']) . "',menu_array='" . mres(serialize($nm)) . "',header_template='" . mres($_REQUEST['header_template']) . "',footer_template='" . mres($_REQUEST['footer_template']) . "' WHERE id='" . mres($_REQUEST['editmenu']) . "'", $db);
			print "Menu saved.<br><br>";
		}
		$m = $_REQUEST['editmenu'];
		$a = GetCustomTabMenuDefintitions($m);
		print "<form id='menueditform' method='post' action=''><div class='showinline'>";
		print "Edit custom menu " . $a['menu_name'] . "<br><br>";
		if ($a['menu_type'] == "Tabbed" || $a['menu_type'] == "Plain" || $a['menu_type'] == "Blocked") {
			print "Numbers will be added automatically together with accesskey definitions. ALT-1 (IE) or ALT-SHFT-1 (FF) will bring you<br>to the first tab. To display the searchbox use the @SEARCH@ tag, for the repository switcher use @R@ and for trail use @T@. @SEARCH@ is a combination of @SEARCHEID@ and @SEARCHWILD@.<br><br>";
		}
		print "<table class=\"admintable\"><tr><td>Name</td><td> <input type='text' size='30' name='menuname' value='" . htme($a['menu_name']) . "'><input type='hidden' name='editmenu' value='" . $m . "'></td></tr>";
		print "<tr><td>Type</td><td><select name='menutype'>";

		$types = array("Tabbed" => "Tabbed", "Blocked" => "Blocked", "Plain" => "Plain", "Shortcut keys" => "Shortcut keys");
		foreach(GetTemplatesOfType("TEMPLATE_HTML") AS $file) {
			$types["Template::" . $file['templateid']] = "Template::" . $file['templatename'] . " (" . $file['template_subject'] . ")";
		}

		foreach ($types AS $id => $type) {
			if ($a['menu_type'] == $id) {
				$sel = "selected='selected'";
				$template = str_replace("Template::","", $id);
			} else {
				unset($sel);
			}
			print "<option " . $sel . " value='" . $id . "'>" . $type . "</option>";
		}
		print "</select></td></tr>";
		print "<tr><td>Header template</td><td><select name='header_template'>";
		print "<option value=''>None</option>";
		foreach(GetTemplatesOfType("TEMPLATE_HTML") AS $file) {
			if ($a['header_template'] == $file['templateid']) {
				$sel = "selected='selected'";
			} else {
				unset($sel);
			}
			print "<option " . $sel . " value='" . $file['templateid'] . "'>" . htme($file['templatename']) . "</option>";
		}
		print "</select></td></tr>";
		print "<tr><td>Footer template</td><td><select name='footer_template'>";
		print "<option value=''>None</option>";
		foreach(GetTemplatesOfType("TEMPLATE_HTML") AS $file) {
			if ($a['footer_template'] == $file['templateid']) {
				$sel = "selected='selected'";
			} else {
				unset($sel);
			}
			print "<option " . $sel . " value='" . $file['templateid'] . "'>" . htme($file['templatename']) . "</option>";
		}

		
		print "</select></td></tr>";
		
		print "</table><br>";

		$keys = explode(" ", "F1 F2 F3 F4 F5 F6 F7 F8 F9 F10 F11 F12");

		$ma = unserialize($a['menu_array']);
		if ($a['menu_type'] == "Tabbed" || $a['menu_type'] == "Plain" || $a['menu_type'] == "Blocked" || $a['menu_type'] == "Shortcut keys") {
			print "<table class='admintable'>";
			if ($a['menu_type'] == "Shortcut keys") {
				print "<tr><td>Function key</td><td>Link/URL</td></tr>";
				$until = 12;
			} else {
				print "<tr><td>Num</td><td>Name (you can use HTML-markup)</td><td>Link/URL</td><td>Color</td></tr>";
				$until = 25;
			}

			for ($c=0;$c<$until;$c++) {
				$row = $ma[$c];
				if ($a['menu_type'] == "Shortcut keys") {
					print "<tr><td>" . $c . "</td><td>";
					print "<select name='name" . $c . "'>";
					print "<option value=''> - </option>";

					foreach ($keys AS $key) {
						if ($row['name'] == $key) {
							$ins = "selected='selected'";
						} else {
							unset($ins);
						}
						print "<option value='" . $key . "' " . $ins . ">" . $key . "</option>";
					}
//						htme($row['name']) . "'>
					print "</td><td><input size='50' type='text' name='link" . $c . "' value='" . htme($row['link']) . "'></td></tr>";
				} else {
					print "<tr><td>" . $c . "</td><td><input type='text' size='30' name='name" . $c . "' value='" . htme($row['name']) . "'></td><td><input size='50' type='text' name='link" . $c . "' value='" . htme($row['link']) . "'></td><td><input type='text' size='7' name='color" . $c . "' id='JS_color" . $c . "' value='" . htme($row['color']) . "'> <a href='#' onclick=\"popcolorchooser('" . htme($row['name']) . "','JS_color" . $c . "');\" onkeyup=\"popcolorchooser('" .  htme($row['name']) . "','JS_color" . $c . "');\"><img src='images/choose_color.gif' alt=''></a></td></tr>";
				}
			}
			print "</table>";
		} else {
			print "<a href='admin.php?templates=1&amp;editHTMLtemplate=" . $template . "&amp;nav=html' class='plainlink'>Click here to edit the template.</a><br>";
		}
		print "<br><input type='submit' value='Save changes' name='SubmitForm'>&nbsp;&nbsp;<input type='submit' value='Save as new menu' name='SubmitForm'>";
		print "</div></form>";
	} else {

		print "<h2>Using custom tab menus, you can create your own navigation. You can assign custom tab menus to users and groups to give users a tailor made navigation.</h2>";
		$a = GetCustomTabMenuDefintitions();
		// Array contains id, menu_name and menu_array (serialized)
		print "<table class='sortable'>";
		print "<thead><tr><td>id</td><td>Name</td><td>Type</td><td>Items</td><td>Delete</td></tr></thead>";
		foreach ($a AS $menu) {
			unset($tmp);
			unset($tablist);
			unset($notfirst);
			$tmp = unserialize($menu['menu_array']);
			foreach ($tmp AS $tabs) {
				if ($tabs['name'] <> "@SEARCH@")  {
					if ($notfirst) {
						$tablist .= ", ";
					}
					$tablist .= $tabs['name'];
					$notfirst = true;
				}
			}
			print "<tr style='cursor: pointer' onmouseover=\"style.background='#E9E9E9';\" onmouseout=\"style.background='#FBFDFF';\"><td onclick=\"document.location='customtabs.php?customtabs=1&amp;editmenu=" . $menu['id'] . "';\">" . $menu['id'] . "</td><td onclick=\"document.location='customtabs.php?customtabs=1&amp;editmenu=" . $menu['id'] . "';\">" . $menu['menu_name'] . "</td><td onclick=\"document.location='customtabs.php?customtabs=1&amp;editmenu=" . $menu['id'] . "';\">" . $menu['menu_type'] . "</td><td onclick=\"document.location='customtabs.php?customtabs=1&amp;editmenu=" . $menu['id'] . "';\">" . $tablist . "</td><td onclick=\"document.location='customtabs.php?customtabs=1&amp;deletemenu=" . $menu['id'] . "';\"><img src='images/delete.gif' alt=''></td></tr>";
		}
		print "</table>";
		print "<br><form id='AddMenu' method='post' action=''><div class='showinline'><input type='hidden' name='customtabs' value='1'>Create a new menu: <input type='text' name='new_menu_name'> <input type='submit' value='Create' name='SubmitForm'></div></form>";

	}




} elseif ($_REQUEST['tabs']) {
	AddBreadCrum("Menu items");

	if ($_REQUEST['tth_submitted']) {
		$t_a = array();
		$t_b = array();
		for ($p=1;$p<128;$p++) {
			if ($_REQUEST['hdtabs' . $p]) {
				if (substr($_REQUEST['hdtabs' . $p],0,6) <> "ignore") {
					array_push($t_a, $_REQUEST['hdtabs' . $p]);
					//print "Add hdtabs" . $p . " : " . $_REQUEST['hdtabs' . $p] . "<br />";
				} else {
					//print "Ignore hdtabs" . $p . " : " . $_REQUEST['hdtabs' . $p] . "<br />";
				}

			}
			$t_b[str_replace("ignore","",$_REQUEST['hdtabs' . $p])] = $_REQUEST['color' . $p];

		}
	 UpdateSetting("TABSTOHIDE", serialize($t_a));
	 UpdateSetting("TABCOLORS", serialize($t_b));

	 $GLOBALS['TABSTOHIDE'] = $t_a;
	 ClearAllRunningCache();
	}

	print "<table><tr><td>";
	$p=0;
	print "<form id='hidemenuitems' method='post' action=''><div class='showinline'>";
	print "<p>Use this form to hide or unhide navigation tabs. <strong>Warning</strong>: this is not a security option, it will only hide the tabs for all users including admins. When a user knows the link to a page he/she will still be able to access it. Use the user-profile and global settings to really prevent users using pages.</p>";
	print "<table class='crm'>";
	print "<thead><tr><td>Menu item</td><td>Invisible</td><td>Visible</td><td>Font color</td></tr></thead>";
	ob_start(); //suppress output
	include("tabsbar.php");
	ob_end_clean();

	foreach ($tabbs AS $tab => $name) {
		foreach ($name AS $nam => $val) {
			if ($tab <> "logo" && $tab <> "search") {
				print "<!-- $tab -->";
			$p++;
				if (in_array($tabtags["$p+20"], $GLOBALS['TABSTOHIDE'])) {
					$inv = "checked='checked'";
					$vis = "";
				} else {
					$vis = "checked='checked'";
					$inv = "";
				}
				if ($tabtags["$p+20"] <> "search") {

					foreach ($GLOBALS['TABCOLORS'] AS $tabje => $color) {
						if (strstr($val, $tabje)) {
							$colval = $color;
						}
					}
					if (!$colval) $colval = "default";
					if ($tab == "trail") {
						if (in_array("trail", $GLOBALS['TABSTOHIDE'])) {
							$inv = "checked='checked'";
							$vis = "";
						} else {
							$vis = "checked='checked'";
							$inv = "";
						}
						print "<tr><td>Trail</td><td><input type='radio' name='hdtabs" . $p . "' value='trail' " . $inv . "></td><td><input type='radio' name='hdtabs" . $p . "' value='ignoretrail' " . $vis . "></td><td></td></tr>";
					} elseif ($tab == "repos") {
						if (in_array("repos", $GLOBALS['TABSTOHIDE'])) {
							$inv = "checked='checked'";
							$vis = "";
						} else {
							$vis = "checked='checked'";
							$inv = "";
						}
						print "<tr><td>Repository switcher</td><td><input type='radio' name='hdtabs" . $p . "' value='repos' " . $inv . "></td><td><input type='radio' name='hdtabs" . $p . "' value='ignorerepos' " . $vis . "></td><td></td></tr>";
					} else {
						print "<tr><td>" . $val .  "</td><td><input type='radio' name='hdtabs" . $p . "' value='" . htme($tabtags["$p+20"]) . "' " . $inv . "></td><td><input type='radio' name='hdtabs" . $p . "' value='ignore" . htme($tabtags["$p+20"]) . "' " . $vis . "></td><td><input type='text' id='JS_color" . $p . "' name='color" . $p . "' size='10' value='" . $colval . "'><a href='#' onclick=\"popcolorchooser('" . htme(jsencode($val)) . "','JS_color" . $p . "');\" onkeyup=\"popcolorchooser('" .  htme(jsencode($val)) . "','JS_color" . $p . "');\"><img src='images/choose_color.gif' alt=''></a>&nbsp;<a onclick=\"document.forms['hidemenuitems'].elements['color" . $p . "'].value='default'\">[def]</a></td></tr>";
					}
				}
			}

		}

	}
	$p++;
	if (in_array("search", $GLOBALS['TABSTOHIDE'])) {
		$inv = "checked='checked'";
		$vis = "";
	} else {
		$vis = "checked='checked'";
		$inv = "";
	}
	print "<tr><td>Search box</td><td><input type='radio' name='hdtabs" . $p . "' value='" . htme("search") . "' " . $inv . "></td><td><input type='radio' name='hdtabs" . $p . "'  value='ignore" . htme("search") . "' " . $vis . "><td></td></tr>";

	print "<tr><td><br><input type='submit' value='Save'></td></tr>";
	print "</table>";
	print "<input type='hidden' name='tth_submitted' value='1'></div></form>";
	print "</td></tr></table>";
}
if ($_REQUEST['EditedCT']) {
	$tabs = unserialize(GetSetting("PersonalTabs"));
	for ($x=0;$x<sizeof($tabs);$x++) {
		$t = str_replace("CT", "", $_REQUEST['EditedCT']);
		if ($x == $t) {
			$element = $tabs[$x];
			$count = $x;
		}
	}
	$tabs[$count] = array("url" => $_REQUEST['newtaburl'],"name" => $_REQUEST['newtab'],"visible" => $_REQUEST['vf'],"order" => $_REQUEST['loc']);

	$GLOBALS['PersonalTabs'] = FlattenArray($tabs);

	mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value='" . mres(serialize($GLOBALS['PersonalTabs'])) . "' WHERE setting='PersonalTabs'",$db);
	$_REQUEST['ovw'] = 1;

} elseif ($_REQUEST['newtab'] && $_REQUEST['newtaburl']) {
	$tabs = unserialize(GetSetting("PersonalTabs"));

	array_push($tabs,array("url" => $_REQUEST['newtaburl'],"name" => $_REQUEST['newtab'],"visible" => $_REQUEST['vf'],"order" => $_REQUEST['loc']));
	$GLOBALS['PersonalTabs'] = FlattenArray($tabs);
	mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value='" . mres(serialize($GLOBALS['PersonalTabs'])) . "' WHERE setting='PersonalTabs'",$db);
	$_REQUEST['ovw'] = 1;

	unset($_REQUEST['add']);
} elseif ($_REQUEST['deltab'] && $_REQUEST['deltaburl']) {
	$tmparr = unserialize(GetSetting("PersonalTabs"));

	for ($x=0;$x<sizeof($tmparr);$x++) {
		$tmparr = $GLOBALS['PersonalTabs'];
		$tmp = base64_decode($_REQUEST['deltaburl']);
		for ($x=0;$x<sizeof($tmparr);$x++) {
			if ($tmparr[$x]['url'] == $tmp && $_REQUEST['tabid'] == "A" . $x) {
				unset($tmparr[$x]);
			}
		}
	}
	$tmparr = FlattenArray($tmparr);
	$GLOBALS['PersonalTabs'] = $tmparr;
	mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value='" . mres(serialize($GLOBALS['PersonalTabs'])) . "' WHERE setting='PersonalTabs'",$db);
	$_REQUEST['ovw'] = 1;
	unset($_REQUEST['add']);
}

if ($_REQUEST['ovw']) {
	print "<h1>Current custom navigation tabs</h1>";
	print "<table class='crm'><thead><tr><td>Name</td><td>URL</td><td class='nwrp'>Visible for</td><td>Order</td><td>Columns</td><td>Delete</td></tr></thead>";
	$c=100;

	

	$tabs = unserialize(GetSetting("PersonalTabs"));

	if (is_array($tabs)) {


		for ($x=0;$x<sizeof($GLOBALS['PersonalTabs']);$x++) {
			$element = $GLOBALS['PersonalTabs'][$x];


			print "<tr style='cursor: pointer' onmouseover=\"style.background='#E9E9E9';\" onmouseout=\"style.background='#FBFDFF';\"><td class='nwrp' onclick=\"document.location='customtabs.php?edit=CT" . $x . "'\"> $x " . $element['name'] . "</td><td class='nwrp' onclick=\"document.location='customtabs.php?edit=CT" . $x . "'\">" . $element['url'] . "</td><td class='nwrp'>";
			if (is_array(unserialize($element['accarr'])) && sizeof(unserialize($element['accarr'])) != 0) {
				print "Limited ";
			} else {
				print "All users ";
			}
			print "[ <a onclick=\"PopMenuRightsChooser(" . $x . ", '');\">select</a> ]";
			print "</td><td onclick=\"document.location='customtabs.php?edit=CT" . $x . "'\">" . $element['order'] . "</td>";
			if (strstr($element['url'],"ShowEntityList")) {
				print "<td class='nwrp'>[ <a onclick=\"popcustomtabcolumnchooser(" . $x . ", '');\">select</a> ]</td>";
			} else {
				print "<td>n/a</td>";
			}

			print "<td onclick=\"document.location='customtabs.php?edit=CT" . $x . "'\"><a href='customtabs.php?deltab=1&amp;deltaburl=" . base64_encode($element['url']) . "&amp;tabid=A" . $x . "'><img src='images/delete.gif' alt=''></a></td></tr>";
			$c++;
		}
	} else {
		print "<tr><td colspan='2'>No custom navigation tabs defined</td></tr>";
	}
	print "</table>";


} elseif ($_REQUEST['add'] || $_REQUEST['edit']) {
	if ($_REQUEST['edit']) {
			$tabs = unserialize(GetSetting("PersonalTabs"));
			for ($x=0;$x<sizeof($tabs);$x++) {
				$t = str_replace("CT", "", $_REQUEST['edit']);
				if ($x == $t) {
					$element = $tabs[$x];
				}
			}
			$form_ins = "<input type='hidden' name='EditedCT' value='" . $_REQUEST['edit'] . "'>";
			//print_r($element);
	} else {
		unset($element);
	}

	print "<h1>Add a custom navigation tab</h1>";
	print "<h2>With this function you can create a navigation tab pointing to a page within Interleave or external (like your corporate webmail or -intranet).</h2>";
	print "<form id='we' method='get' action=''><div class='showinline'><table>";

	print "<tr><td>Name to appear in tab bar:</td><td class='nwrp'><input type='text' name='newtab' value='" . htme($element['name']) . "' size='50'>" . $form_ins . "</td><td>";

	print "<table width='1' border='0'><tr>";
	print "<td " . PrintToolTipCode('Make text red') ."style='background: red; cursor: pointer' onclick=\"a=document.forms['we'].elements['newtab'].value;document.forms['we'].elements['newtab'].value='<span style=\'color: #ff0000;\'>' + a + '</span>'\">&nbsp;&nbsp;</td>";
	print "<td " . PrintToolTipCode('Make text blue') ."style='background: #3366FF; cursor: pointer' onclick=\"a=document.forms['we'].elements['newtab'].value;document.forms['we'].elements['newtab'].value='<span style='color: #3366FF;'>' + a + '</span>'\">&nbsp;&nbsp;</td>";
	print "<td " . PrintToolTipCode('Make text green') ."style='background: green; cursor: pointer' onclick=\"a=document.forms['we'].elements['newtab'].value;document.forms['we'].elements['newtab'].value='<span style=\'color: #00ff00;\'>' + a + '</span>'\">&nbsp;&nbsp;</td>";
	print "<td " . PrintToolTipCode('Italic') ." style='cursor: pointer' onclick=\"a=document.forms['we'].elements['newtab'].value;document.forms['we'].elements['newtab'].value='<em>' + a + '</em>'\"><em>em</em>&nbsp;</td>";
	print "<td " . PrintToolTipCode('Bold') ." style='cursor: pointer' onclick=\"a=document.forms['we'].elements['newtab'].value;document.forms['we'].elements['newtab'].value='<strong>' + a + '</strong>'\"><strong>strong</strong></td>";
//edit Jeroen 2010-04-13 geen underline meer
	print "<td " . PrintToolTipCode('Clear all markup from name') ." style='cursor: pointer' onclick=\"a=document.forms['we'].elements['newtab'].value;document.forms['we'].elements['newtab'].value=stripHTML(a);\"><strong>*</strong></td>";
	print "</tr></table>";


	print "</td></tr>";

	print "<tr><td><br>order:</td><td><br>";
	print "<select name='loc'>";
	for ($i=0;$i<25;$i++) {
		if ($element['order'] == $i) {
			$ins = "selected='selected'";
		} else {
			unset($ins);
		}
		print "<option " . $ins . " value='" . htme($i) . "'>" . htme($i) . "</option>";
	}
	print "</select>";
	print "<input type='hidden' name='vf' value='[all]'>";
	print "</td></tr>";

/*
	print "<tr><td><br>Visible for:</td><td><br>";
	print "<select name='vf'>";
	if ($element['visible'] == "[all]") {
		$ins = "selected='selected'";
	} else {
		unset($ins);
	}
	print "<option " . $ins . " value='[all]'>[all] (but limited users)</option>";
	if ($element['visible'] == "[admins]") {
		$ins = "selected='selected'";
	} else {
		unset($ins);
	}
	print "<option " . $ins . " value='[admins]'>[admins only]</option>";
	$sql = "SELECT id, name FROM " . $GLOBALS['TBL_PREFIX'] . "userprofiles ORDER BY name";
	$result = mcq($sql,$db);
	while ($row = mysql_fetch_array($result)) {
		if ($row['FULLNAME'] == "") {
			$row['FULLNAME'] = $row['name'];
		}
		print "<option value='profile_" . $row['id'] . "'>[profile] " . $row['FULLNAME'] . "</option>";
	}
	$sql = "SELECT id, name, FULLNAME FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE name NOT LIKE 'deleted_user%' ORDER BY FULLNAME";
	$result = mcq($sql,$db);
	while ($row = mysql_fetch_array($result)) {
		if ($row['FULLNAME'] == "") {
			$row['FULLNAME'] = $row['name'];
		}

		if ($element['visible'] == $row['id']) {
			$ins = "selected='selected'";
		} else {
			unset($ins);
		}
		print "<option " . $ins . " value='" . $row['id'] . "'>" . $row['FULLNAME'] . "</option>";
	}
	print "</select>";
	print "</td></tr>";
	*/
	print "<tr><td>Tab URL:</td><td><textarea name='newtaburl' cols='50' rows='4'>" . $element['url'] . "</textarea></td></tr>";
	print "<tr><td><input type='submit' value='Submit'></td></tr>";
	print "<tr><td colspan='2'>Copy &amp; paste this URL from your browser after having made a selection on the main entity list page<br>or use any other page or one of the defaults:</td></tr>";
	print "</table>";

	PrintLinkjes();

	print "</div></form>";


} elseif ($_REQUEST['edit']) {
	// nothin'
}
EndHTML();
?>