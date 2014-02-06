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
if (!$GLOBALS['INITIATED']) {
	exit;
}

require_once($GLOBALS['PATHTOINTERLEAVE'] . "tabs_inc.php");

if (!is_array($GLOBALS['PersonalTabs']) && $GLOBALS['PersonalTabs'] != "") {
	$GLOBALS['PersonalTabs'] = unserialize($GLOBALS['PersonalTabs']);
}

$lang = $GLOBALS['lang'];

//print "<br>";
if (!$tab) {
	$tab = $_REQUEST['tab'];
}
if (!$tab) {
	$tab = "100";
}
if($GLOBALS['navtype'] == "PULLDOWN") {
	require($GLOBALS['PATHTOINTERLEAVE'] . "new_menu.php");
} else {


	// Initialize tab contents
	$tabbs = array();
	$tabtags = array();
	$x = 1;
	if (strtoupper($GLOBALS['HIDEMAINTAB'])<>"YES" || is_administrator()) {

		if ($GLOBALS['UC']['USEDASHBOARDASENTRY'] == "n") {
			$col_1 = "";
			foreach ($GLOBALS['TABCOLORS'] AS $tabje => $color) {
				if (substr($lang['main'],strlen($tabje) - strlen($lang['main']),strlen($lang['main'])) == $tabje && $color != "" && $color != "default") {
					$col_1 = " style='color:" . $color . "'";
				}

			}
			$tabbs["$x+20"] = array(($back_end_url . "index.php?") => "<span class='tabsbar' " . $col_1 .  ">" . $lang['main'] . "</span>");
			$ack .= "<a accesskey='" . $x . "' href='index.php?'>__</a>";
			$tabtags["$x+20"] = $lang['main'];

		} else {
			$col_1 = "";
			foreach ($GLOBALS['TABCOLORS'] AS $tabje => $color) {
				if (substr($lang['dashboard'],strlen($tabje) - strlen($lang['dashboard']),strlen($lang['dashboard'])) == $tabje && $color != "" && $color != "default") {
					$col_1 = " style='color:" . $color . "'";
				}

			}
			if (GetTemplateSubject($GLOBALS['UC']['DASHBOARDTEMPLATE']) <> "Default Dashboard Template" && GetTemplateSubject($GLOBALS['UC']['DASHBOARDTEMPLATE']) <> "Interleave default dashboard template" && GetTemplateSubject($GLOBALS['UC']['DASHBOARDTEMPLATE']) <> "Default Interleave Dashboard Template") {
				$lang['dashboard'] = GetTemplateSubject($GLOBALS['UC']['DASHBOARDTEMPLATE']);
			}
			$tabbs["$x+20"] = array(($back_end_url . "dashboard.php?") => "<span class='tabsbar'" . $col_1 . ">" . $lang['dashboard'] . "</span>");
			$ack .= "<a accesskey='" . $x . "' href='dashboard.php?'>__</a>";
			$tabtags["$x+20"] = $lang['dashboard'];
			unset($col_1);
		}
	} else {
		$x--;
	}
	$lock = "<img src='images/lock.png' alt=''>";

	if (is_array($GLOBALS['PersonalTabs'])) {

		$tabcounter=0;
		foreach ($GLOBALS['PersonalTabs'] AS $element) {


			if (is_array($element)) {

				if (strstr($element['url'],"ExternalLink::")) {
					$httplink = str_replace("ExternalLink::","",$element['url']);
					$element['url'] = "index.php?if_l=" . base64_encode($httplink);
				}
				if (strstr($element['url'],"Template::")) {
					$httplink = str_replace("Template::","",$element['url']);
					$element['url'] = "index.php?if_t=" . base64_encode($httplink);
				}
				if (strstr($element['visible'],"profile_")) {
					$profilename = GetUserProfiles(str_replace("profile_","",$element['visible']));
					$profile = str_replace("profile_","",$element['visible']);
				} else {
					$profile = "abcdefg";
				}
				$col_1 = "";
				foreach ($GLOBALS['TABCOLORS'] AS $tabje => $color) {
					if ($tabje <> "") {
						if (strstr($element['name'], $tabje) && $color != "" && $color != "default") {
							$col_1 = " style='color:" . $color . "'";
						}
					}
				}
				$t = 0;

				$access = unserialize($element['accarr']);

				$prof = "P" . $GLOBALS['UC']['USERPROFILE'];
				$usid = "U" . $GLOBALS['USERID'];

				if (in_array($prof, $access) || in_array($usid, $access) || !is_array($access) || sizeof($access) == 0) {
					//	if ($element['ColumnsToShow']) {
							$element['url'] .= "&CustomColumnLayout=Tab" . $tabcounter;
						//} else {
					//		$element['url'] .= "&CustomColumnLayout=None";
					//	}
						$x++;
						$t++;
						$tabbs["$x+20"] = array(($element['url'] . "&PersonalTabsTSN=x&filter_id=PT" . md5($element['name'])) => "<span class='tabsbar' " . $col_1 . ">" . $element['name'] . "</span>");
						$ack .= "<a accesskey='" . $x . "' href='" . htme($element['url']) . "&amp;PersonalTabsTSN=x'>__</a>";
						$tabtags["$x+20"] = $element['name'];
				}
			}
		$tabcounter++;
		}
	} 

	if (strtoupper($GLOBALS['UC']['HIDEADDTAB'])<>"YES" || is_administrator()) {
			foreach ($GLOBALS['TABCOLORS'] AS $tabje => $color) {
				if (substr($lang['add'],strlen($tabje) - strlen($lang['add']),strlen($lang['add'])) == $tabje && $color != "" && $color != "default") {
					$col_1 = "<span style='color: " . $color . ";'>";
					$col_2 = "</span>";
				}
			}
			$x++;
			$y = $x + 20;
			$tabbs["$x+20"] = array(($back_end_url . "edit.php?e=_new_&") => $col_1 . "" .  $lang['add'] . "" . $col_2);
			$ack .= "<a accesskey='" . $x . "' href='edit.php?e=_new_&amp;'>__</a>";
			$tabtags["$x+20"] = $lang['add'];
			unset($col_1);
			unset($col_2);

	}
	if (strtoupper($GLOBALS['UC']['HIDEENTITYTAB'])<>"YES" || is_administrator()) {
			foreach ($GLOBALS['TABCOLORS'] AS $tabje => $color) {
				if (substr($lang['entities'],strlen($tabje) - strlen($lang['entities']),strlen($lang['entities'])) == $tabje && $color != "" && $color != "default") {
					$col_1 = "<span style='color: " . $color . ";'>";
					$col_2 = "</span>";
				}
			}

			$x++;
			$tabbs["$x+20"] = array(($back_end_url . "index.php?ShowEntityList=1&") => $col_1 . $lang['entities'] . "" . $col_2);
			$ack .= "<a accesskey='" . $x . "' href='index.php?ShowEntityList=1&amp;'>__</a>";
			$tabtags["$x+20"] = $lang['entities'];
			unset($col_1);
			unset($col_2);
	}
	if (strtoupper($GLOBALS['UC']['HIDECUSTOMERTAB'])<>"YES" || is_administrator()) {
			foreach ($GLOBALS['TABCOLORS'] AS $tabje => $color) {
				if (substr($lang['customers'],strlen($tabje) - strlen($lang['customers']),strlen($lang['customers'])) == $tabje && $color != "" && $color != "default") {
					$col_1 = "<span style='color: " . $color . ";'>";
					$col_2 = "</span>";
				}
			}
			$x++;
			$tabbs["$x+20"] = array(($back_end_url . "index.php?ShowCustomerList") => $col_1 . $lang['customers'] . "" . $col_2);
			$ack .= "<a accesskey='" . $x . "' href='index.php?ShowCustomerList'>__</a>";
			$tabtags["$x+20"] = $lang['customers'];
			unset($col_1);
			unset($col_2);

	}
	
	if (strtoupper($GLOBALS['UC']['HIDESUMMARYTAB'])<>"YES" || is_administrator()) {
			foreach ($GLOBALS['TABCOLORS'] AS $tabje => $color) {
				if (substr($lang['summary'],strlen($tabje) - strlen($lang['summary']),strlen($lang['summary'])) == $tabje && $color != "" && $color != "default") {
					$col_1 = "<span style='color: " . $color . ";'>";
					$col_2 = "</span>";
				}
			}
			$x++;
			$tabbs["$x+20"] = array(($back_end_url . "summary.php?") => $col_1 . $lang['summary'] . "" . $col_2);
			$ack .= "<a accesskey='" . $x ."' 	href='summary.php?'>__</a>";
			$tabtags["$x+20"] = $lang['summary'];
			unset($col_1);
			unset($col_2);

	}

	//if (strtoupper($EnableCustInsert)=="YES") {
		$cl = GetClearanceLevel();
		if (!in_array("UITGEZET-NoOwnNoAssign", $cl)) {
			foreach ($GLOBALS['TABCOLORS'] AS $tabje => $color) {
				if ($tabje <> "") {
					if (strstr($lang['viewinsertedentities'],$tabje) && $color != "" && $color != "default") {
						$col_1 = "<span style='color: " . $color . ";'>";
						$col_2 = "</span>";
					}
				}
			}

			$sql= "SELECT eid FROM " . $GLOBALS['TBL_PREFIX'] . "entity," . $GLOBALS['TBL_PREFIX'] . "customer WHERE " . $GLOBALS['TBL_PREFIX'] . "customer.id=" . $GLOBALS['TBL_PREFIX'] . "entity.CRMcustomer AND " . $GLOBALS['TBL_PREFIX'] . "entity.owner='2147483647' AND " . $GLOBALS['TBL_PREFIX'] . "entity.assignee='2147483647' AND deleted<>'y'";
			$result= mcq($sql,$db);
			$count=0;
			while ($t = mysql_fetch_array($result)) {
				$y = CheckEntityAccess($t['eid']);
				if ($y == "ok" || $y == "readonly") {
					$count++;
				}
			}




			if ($count > 0) {
				$x++;
				$tabbs["$x+20"] = array(($back_end_url . "index.php?ShowEntityList=1&filter=custinsert") => $col_1 . $lang['viewinsertedentities'] . " (" . $count . ")" . "" . $col_2);

				$ack .= "<a accesskey='" . $x . "' href='index.php?ShowEntityList=1&amp;filter=custinsert&amp;'>__</a>";
				$tabtags["$x+20"] = $lang['viewinsertedentities'];
				unset($col_1);
				unset($col_2);
			}
		}

	if (strtoupper($GLOBALS['ShowDeletedViewOption'])=="YES") {

			foreach ($GLOBALS['TABCOLORS'] AS $tabje => $color) {
				if (substr($lang['delentities'],strlen($tabje) - strlen($lang['delentities']),strlen($lang['delentities'])) == $tabje && $color != "" && $color != "default") {
					$col_1 = "<span style='color: " . $color . ";'>";
					$col_2 = "</span>";
				}
			}
			$x++;
			$tabbs["$x+20"] = array(($back_end_url . "index.php?ShowEntityList=1&filter=viewdel") => $col_1 . $lang['delentities'] . "" . $col_2);

			$ack .= "<a accesskey='" . $x . "' href='index.php?ShowEntityList=1&amp;filter=viewdel&amp;'>__</a>";
			$tabtags["$x+20"] = $lang['delentities'];
			unset($col_1);
			unset($col_2);
	}


	$x++;
	foreach ($GLOBALS['TABCOLORS'] AS $tabje => $color) {
		if (substr($lang['logout'],strlen($tabje) - strlen($lang['logout']),strlen($lang['logout'])) == $tabje && $color != "" && $color != "default") {
			$col_1 = "<span style='color: " . $color . ";'>";
			$col_2 = "</span>";
		}
	}
	$tabbs["$x+20"] = array(($back_end_url . "index.php?logout=1") => $col_1 . "" .  "" . $lang['logout'] . "" . $col_2);
	$tabtags["$x+20"] = $lang['logout'];

	$ack .= "<a accesskey='0' href='index.php?logout=1'>__</a>";
	if (strtoupper($GLOBALS['UC']['HIDESUMMARYTAB'])<>"YES" || is_administrator()) {

			$sf = "&nbsp;<form id='direct2' action='summary.php' method='get'><img src='images/searchbox.png' alt='' class='search_img'><input class='search_input' type='search' name='sta' onchange=\"document.forms['direct2'].submit();\" onfocus=\"document.forms['direct2'].elements['sta'].value=''\"></form>";
			$search = true;



	}

	$tabbs["search"] = array(($back_end_url . "") => "<div id=\"direct_entity_submit\" class=\"showinline\">&nbsp;#:<form id='direct' action='edit.php?' method='post'><div class='showinline'><input type='text' size='3' name='e' onchange=\"document.forms['direct'].submit()\" onfocus=\"document.forms['direct'].elements['e'].value=''\"></div></form></div>" . $sf);
	//. $evt_ins_2 . $evt_ins);
	$tabtags["search"] = "search";

	//$tabbs["trail"] = array(($back_end_url . "") => CreateTrail(true));
	//$tabtags["trail"] = "trail";

	$tabbs["repos"] = array(("") => "" . CreateReposSwitcher(true));
	$tabtags["repos"] = "repos";


	//$tabbs["logo"] = array(($back_end_url . "") => "</span></a><a href='http://www.interleave.nl' onclick=\"window.open(this.href); return false;\"><img id='MainBarInterleaveLogo' src='images/crm_small_grey2.gif' alt=''></a><a href='#'><span>");

	if (is_administrator()) {
		$ack .= "<a accesskey='a' href='admin.php?info=1&amp;SkipMainNavigation'>__</a>";
		$ack .= "<a accesskey='s' href='admin.php?sysval=1&amp;SkipMainNavigation'>__</a>";

		$ack .= "<a accesskey='d' href='admin.php?checkdb=1&amp;web=1&amp;SkipMainNavigation'>__</a>";

		$ack .= "<a accesskey='p' href='admin.php?fysdelete=1&amp;SkipMainNavigation'>__</a>";
		$ack .= "<a accesskey='l' href='dictedit.php?packman=1&amp;SkipMainNavigation'>__</a>";
		$ack .= "<a accesskey='u' href='useradmin.php?password=&amp;adduser=1&amp;userman=1&amp;cur=1&amp;SkipMainNavigation'>__</a>";

		$ack .= "<a accesskey='p' href='admin.php?fysdelete=1&amp;SkipMainNavigation'>__</a>";
		$ack .= "<a accesskey='e' href='extrafields.php?SkipMainNavigation'>__</a>";
		$ack .= "<a accesskey='r' href='admin.php?reposman=1&amp;resman=1&amp;manageres=1&amp;SkipMainNavigation'>__</a>";
		$ack .= "<a accesskey='f' href='flextable.php?TableAdmin=true&amp;navid=ft&amp;SkipMainNavigation'>__</a>";
		$ack .= "<a accesskey='t' href='trigger.php?&amp;SkipMainNavigation'>__</a>";

	}
	$ack .= "<a accesskey='m' href='#' onclick=\"newWindow = window.open('docs_examples/CRM-CTT_Interleave_Adminmanual.pdf','Manual','width=640,height=630,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');\">__</a>";
	$ack .= "<a accesskey='c' href='calendar.php?nav=1'>__</a>";
	$ack .= "<a accesskey='n' href='customers.php?add=1.php'>&nbsp;newcust</a>";
	$ack .= "<a accesskey='h' href='index.php?shortkeys=1'>__</a>";
	print '<div id="shortkeydiv" style="display: none;">';

	print $ack;
	print '</div>';
		// So far for the normal menu items

	$to_tabs = array();

	if (is_array(unserialize($GLOBALS['TABSTOHIDE']))) {
		$GLOBALS['TABSTOHIDE'] = unserialize($GLOBALS['TABSTOHIDE']);
	}

	for ($t=1;$t<$x+1;$t++) {
		if (!in_array($tabtags[$t . "+20"], $GLOBALS['TABSTOHIDE'])) {
			array_push($to_tabs,"$t+20");
			
		} else {
			qlog(INFO, "Not displaying tab $t+20 (" . $tabtags[$t . "+20"] . ") - it is hidden");
		}
	}
	if (!in_array("trail", $GLOBALS['TABSTOHIDE'])) {
		array_push($to_tabs, "trail");
	}
	if (!in_array("repos", $GLOBALS['TABSTOHIDE'])) {
		include($GLOBALS['CONFIGFILE']);
		if ((strtoupper($GLOBALS['EnableRepositorySwitcher'])=="YES" || (strtoupper($GLOBALS['EnableRepositorySwitcher'])=="ADMIN" && is_administrator())) && sizeof($host) > 1) {
			array_push($to_tabs, "repos");
		}
	}

	if (!in_array("search", $GLOBALS['TABSTOHIDE'])) {
		array_push($to_tabs, "search");

		
	} else {
		qlog(INFO, "Not displaying tab search - it is hidden");
	}

	array_push($to_tabs, "logo");

	$tab .= " ";
	$tab = trim($tab);

	foreach ($tabbs AS $tabname => $tabX) {
		foreach ($tabX AS $link => $text) {
			if (strstr($_SERVER['REQUEST_URI'], $link)) {
				$tab = $tabname;
			}
		}
	}
	
	if (1==1) {
		print PlainMainNav($to_tabs, $tabbs, $tab);
	} else {
		print '<br>';
		tabs($to_tabs, $tabbs, $tab);
	}
}
//print "<a accesskey=\"h\" href='index.php'>__</a>";
