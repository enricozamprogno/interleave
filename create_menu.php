<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 info@interleave.nl
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * Allows the admin to change the columns shown in the main list
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */

if (!$GLOBALS['INITIATED']) {
	exit;
}
require_once("tabs_inc.php");
if ($GLOBALS['UC']['MENUTOUSE'] == "default" || $GLOBALS['UC']['MENUTOUSE'] == "") {

	require_once($GLOBALS['PATHTOINTERLEAVE'] . "tabsbar.php");

} else {

	if (!$_REQUEST['CT']) $_REQUEST['CT'] = "NoTabSelected";

	$a = GetCustomTabMenuDefintitions($GLOBALS['UC']['MENUTOUSE']);
	if ($a['header_template'] > 0) {
		print ParseTemplateCleanUp(ParseTemplateDashboard(ParseTemplateGeneric(EvaluateTemplatePHP(GetTemplate($a['header_template'])))));
	}

	if (strstr($a['menu_type'], "Template::")) {

		$template_id = trim(str_replace("Template::","", $a['menu_type']));

		print html_compress(ParseTemplateMenu($template_id));

		if (is_administrator()) {

			// Shortkeys for admins
			
			$ack .= "<a accesskey='a' href='admin.php?info=1&amp;SkipMainNavigation'>__</a>";
			$ack .= "<a accesskey='s' href='admin.php?tab=99&amp;sysval=1&amp;SkipMainNavigation'>__</a>";
			$ack .= "<a accesskey='d' href='admin.php?checkdb=1&amp;web=1&amp;SkipMainNavigation'>__</a>";
			$ack .= "<a accesskey='p' href='admin.php?fysdelete=1&amp;SkipMainNavigation'>__</a>";
			$ack .= "<a accesskey='l' href='dictedit.php?tab=99&amp;packman=1&amp;SkipMainNavigation'>__</a>";
			$ack .= "<a accesskey='u' href='useradmin.php?password=&amp;adduser=1&amp;userman=1&amp;cur=1&amp;SkipMainNavigation'>__</a>";
			$ack .= "<a accesskey='p' href='admin.php?fysdelete=1&amp;SkipMainNavigation'>__</a>";
			$ack .= "<a accesskey='e' href='extrafields.php?&amp;SkipMainNavigation'>__</a>";
			$ack .= "<a accesskey='r' href='admin.php?reposman=1&amp;resman=1&amp;manageres=1&amp;SkipMainNavigation'>__</a>";
			$ack .= "<a accesskey='f' href='flextable.php?TableAdmin=true&amp;navid=ft&amp;SkipMainNavigation'>__</a>";
			$ack .= "<a accesskey='t' href='trigger.php?&amp;SkipMainNavigation'>__</a>";

		}
		$ack .= "<a accesskey='m' onclick=\"newWindow = window.open('docs_examples/CRM-CTT_Interleave_Adminmanual.pdf','Manual','width=640,height=630,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');\">__</a>";
		$ack .= "<a accesskey='c' href='calendar.php?nav=1'>__</a>";
		$ack .= "<a accesskey='n' href='customers.php?add=1.php'>&nbsp;newcust</a>";
		$ack .= "<a accesskey='h' href='index.php?shortkeys=1'>__</a>";

		print '<div id="AccessKeysDiv" style="display: none;">';
		print $ack;
		print '</div>';
	} else {


		$m = unserialize($a['menu_array']);

		for ($t=0;$t<sizeof($m);$t++) {
			$m[$t]['id'] = "T" . $t;
		}



		$i = sizeof($m) + 1;

	/*	if ($a['menu_type'] == "Tabbed") {
			$m[$i]['name'] = "<img id='MainBarInterleaveLogo' src='images/crm_small_grey2.gif' alt=''>";
			$m[$i]['link'] = "http://www.interleave.nl/index.php";
			$m[$i]['color'] = "#FF0000";
			$m[$i]['id'] = "logo";
		} else {
			$m[$i]['name'] = "<img id='MainBarInterleaveLogo' src='images/crm_small_grey2.gif' alt=''>";
			$m[$i]['link'] = "http://www.interleave.nl/index.php";
			$m[$i]['color'] = "#FF0000";
			$m[$i]['id'] = "logo";
		}
	*/	
		$DeModules = GetModules();

		$i=0;
		$visnum = 1;
		$menutab = array();
		$tabstoshow = array();
		require($GLOBALS['CONFIGFILE']);
		foreach ($m AS $item) {
			$menutab[$item['id']] = array();
			

			foreach ($DeModules AS $module)
			{
			    if (strstr($item['name'], "@MODULE" . $module['mid'] . "@"))
			    {
				$item['name'] = str_replace("@MODULE" . $module['mid'] . "@", RunModule($module['mid'], false, true), $item['name']);
			    }
			}




			if (substr($item['link'], strlen($item['link']) - 4, 4) == ".php") {
				$item['link'] .= "?";
			}
			if ($item['color'] == "") $item['color'] = "#000000";

				if (strstr($item['name'], "@SEARCH@") || strstr($item['name'], "@R@") || strstr($item['name'], "@T@") || strstr($item['name'], "@SEARCHWILD@") || strstr($item['name'], "@SEARCHEID@")) {
					$searcheid = "#:<div id=\"direct_entity_submit\" class=\"showinline\"><form id='direct' method='get' action='edit.php'><div class='showinline'><input type='text' size='5' name='e' onchange=\"document.forms['direct'].submit();\" onfocus=\"document.forms['direct'].elements['e'].value=''\"></div></form></div>";
					$search = true;
					$searchwild = '<form action="summary.php" method="get"><img src="images/searchbox.png" alt="" class="search_img"><input type="search" class="search_input" name="sta" id="JS_sta" onchange="this.form.submit()"></form>';

					$sf = "" . $searcheid . "&nbsp;" . $searchwild;

					$item['name'] = str_replace("@SEARCH@", $sf, $item['name']);
					$item['name'] = str_replace("@SEARCHEID@", $searcheid, $item['name']);
					$item['name'] = str_replace("@SEARCHWILD@", $searchwild, $item['name']);
					$item['name'] = str_replace("@T@", CreateTrail(true), $item['name']);
					$item['name'] = str_replace("@R@", CreateReposSwitcher(true), $item['name']);

				}

			if ($item['id'] != "logo" && !$search) {
				//$item['name'] = $visnum . ". " . $item['name'];
				$ack .= "<a accesskey='" . $visnum . "' href='" . htme($item['link']). "&amp;CT=" . htme($item['id']) . "'>__</a>";
				$visnum++;
			}
			unset($search);
			$jero_str = $item['name'];
			//dit is slordig, maar er kunnen div's of form's in customtabs staan en daar moeten geen <span> tags om heen
			if ((!stristr($jero_str, "<form")) && (!stristr($jero_str, "<div")))
			{
			    $jero_str =  "<span style='color: " . $item['color'] . "'>" . $jero_str . "</span>";
			}
			$menutab[$item['id']][$item['link']] = $jero_str;
			array_push($tabstoshow, $item['id']);
			$i++;
		}
		$a = GetCustomTabMenuDefintitions($GLOBALS['UC']['MENUTOUSE']);

		if ($a['menu_type'] == "Tabbed") {
			foreach ($menutab AS $tabName => $tabX) {
				foreach ($tabX AS $link => $text) {
					if (strstr($_SERVER['REQUEST_URI'], $link)) {
						$selected_tab = $tabName;
					}
				}
			}

			
			if (1==1) {
				print PlainMainNav($tabstoshow, $menutab, $selected_tab);
			} else {
				print '<br>';
				tabs($tabstoshow, $menutab, $selected_tab);
			}

		} else	if ($a['menu_type'] == "Blocked") {
			DisplayPlainMenuWithLines($menutab, $_REQUEST['CT']);
		} elseif ($a['menu_type'] == "Plain") {
			DisplayPlainMenu($menutab, $_REQUEST['CT']);
		}

		if (is_administrator()) {
			$ack .= "<a accesskey='a' href='admin.php?info=1&amp;SkipMainNavigation'>__</a>";
			$ack .= "<a accesskey='s' href='admin.php?tab=99&amp;sysval=1&amp;SkipMainNavigation'>__</a>";

			$ack .= "<a accesskey='d' href='admin.php?checkdb=1&amp;web=1&amp;SkipMainNavigation'>__</a>";

			$ack .= "<a accesskey='p' href='admin.php?fysdelete=1&amp;SkipMainNavigation'>__</a>";
			$ack .= "<a accesskey='l' href='dictedit.php?tab=99&amp;packman=1&amp;SkipMainNavigation'>__</a>";
			$ack .= "<a accesskey='u' href='useradmin.php?password=&amp;adduser=1&amp;userman=1&amp;cur=1&amp;SkipMainNavigation'>__</a>";

			$ack .= "<a accesskey='p' href='admin.php?fysdelete=1&amp;SkipMainNavigation'>__</a>";
			$ack .= "<a accesskey='e' href='extrafields.php?tabletype=entity&amp;ti=1'>__</a>";
			$ack .= "<a accesskey='r' href='admin.php?reposman=1&amp;resman=1&amp;manageres=1&amp;SkipMainNavigation'>__</a>";
			$ack .= "<a accesskey='f' href='flextable.php?TableAdmin=true&amp;navid=ft&amp;SkipMainNavigation'>__</a>";
			$ack .= "<a accesskey='t' href='trigger.php?&amp;SkipMainNavigation'>__</a>";

		}
		$ack .= "<a accesskey='m' href='#' onclick=\"newWindow = window.open('docs_examples/CRM-CTT_Interleave_Adminmanual.pdf','Manual','width=640,height=630,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');\">__</a>";
		$ack .= "<a accesskey='c' href='calendar.php?nav=1&amp;SkipMainNavigation'>__</a>";
		$ack .= "<a accesskey='n' href='customers.php?add=1.php'>&nbsp;newcust</a>";
		$ack .= "<a accesskey='h' href='index.php?shortkeys=1&amp;SkipMainNavigation'>__</a>";

		print '<div id="AccessKeysDiv" style="display: none;">';
		print $ack;
		print '</div>';
	}

	if ($a['footer_template'] > 0) {
		print ParseTemplateCleanUp(ParseTemplateDashboard(ParseTemplateGeneric(EvaluateTemplatePHP(GetTemplate($a['footer_template'])))));
	}
}

?>