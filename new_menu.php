<?php
/* ********************************************************************
* Interleave
* Copyright (c) 2001-2012 info@interleave.nl
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
require_once("initiate.php");



$epoch = date('U');

$menu = array();

if ($GLOBALS['UC']['USEDASHBOARDASENTRY'] == "n") {
	$menu[0]['name'] = $lang['main'];
	$menu[0]['url'] = "index.php?" . $epoch;
} else {
	$menu[0]['name'] = $lang['dashboard'];
	$menu[0]['url'] = "dashboard.php?tab=1&" . $epoch;
}

$menu[0]['options'] = array();
$p = 0;
$menu[0]['options'][$p]['url'] = "index.php?UserMessage=true&" . $epoch;
$menu[0]['options'][$p]['name'] = $lang['messageinbox'];
$p++;
$menu[0]['options'][$p]['url'] = "index.php?logout=1&" . $epoch;
$menu[0]['options'][$p]['name'] = $lang['logout'];
$p++;


$x = 1;
$t=0;

if (is_array($GLOBALS['PersonalTabs'])) {
	foreach ($GLOBALS['PersonalTabs'] AS $element) {
		$t++;
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


			if ($element['visible'] == "[all]" || ($element['visible'] == "[admins]" && is_administrator()) || ($element['visible'] == 	$GLOBALS['USERID']) || ($GLOBALS['UC']['USERPROFILE']<>"" && ($GLOBALS['UC']['USERPROFILE'] == $profile))) {
				if ($element['location'] <> "entity_menu") {
					$ack .= "<a accesskey='" . $x . "' href='" . $element['url'] . "&PersonalTabsTSN=x&filter_id=PT" . md5($element['name']) . "&$epoch'>__</a>";
					//print '["' . $element['name'] . '","' . $x . ' ' . $element['url'] . '",1,0,0],';
					$menu[$x]['name'] = $element['name'];
					$menu[$x]['url'] = $element['url'];
					$x++;
				}

			}
		}
	}
}
if (strtoupper($GLOBALS['UC']['HIDEADDTAB'])<>"YES") {
	$menu[$x]['name'] = $lang['add'];
	$menu[$x]['url'] = "edit.php?e=_new_";
	$menu[$x]['options'] = array();
	$u =0;
	if (is_array($GLOBALS['UC']['ADDFORMLIST'])) {
		foreach ($GLOBALS['UC']['ADDFORMLIST'] AS $form) {
			if ($form <> "default" && CheckIfFormMainBePrintedOnAddList($form)) {
				$subj = GetTemplateSubject($form);
				if ($subj) {
					qlog(INFO, "Added " . $subj . " to tab list!");
					$menu[$x]['options'][$u]['url'] = "edit.php?e=_new_&ftu=" . $form . "&SetCustTo=" . $_REQUEST['SetCustTo'];
					$menu[$x]['options'][$u]['name'] = htme(GetTemplateSubject($form));
					$u++;
				}
			}
		}
	}
	$x++;
}

if (strtoupper($GLOBALS['UC']['HIDEENTITYTAB'])<>"YES") {
			$menu[$x]['name'] = $lang['entities'];
			$menu[$x]['url'] = "index.php?ShowEntityList=1&" . $epoch;

			$l = 0;

			$menu[$x]['options'] = array();



			if (strtoupper($GLOBALS['EnableCustInsert'])=="YES") {

				$sql= "SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity," . $GLOBALS['TBL_PREFIX'] . "customer WHERE " . $GLOBALS['TBL_PREFIX'] . "customer.id=" . $GLOBALS['TBL_PREFIX'] . "entity.CRMcustomer AND " . $GLOBALS['TBL_PREFIX'] . "entity.owner='2147483647' AND " . $GLOBALS['TBL_PREFIX'] . "entity.assignee='2147483647' AND deleted<>'y'";
				$result= mcq($sql,$db);
				$e= mysql_fetch_array($result);

				$menu[$x]['options'][$l]['url'] = "index.php?ShowEntityList=1&" . $epoch;
				$menu[$x]['options'][$l]['name'] = $lang['entities'];
				$l++;
				$menu[$x]['options'][$l]['url'] = "index.php?ShowEntityList=1&filter=custinsert&" . $epoch;
				$menu[$x]['options'][$l]['name'] = $lang['viewinsertedentities'] . " (" . $e[0] . ")";
				$l++;
				$menu[$x]['options'][$l]['url'] = "SPACER";
				$menu[$x]['options'][$l]['name'] = "SPACER";
				$l++;
			}
				
			if (strtoupper($GLOBALS['ShowDeletedViewOption'])=="YES") {

				$menu[$x]['options'][$l]['url'] = "index.php?ShowEntityList=1&filter=viewdel&" . $epoch;
				$menu[$x]['options'][$l]['name'] = $lang['delentities'];
				$l++;
				$menu[$x]['options'][$l]['url'] = "SPACER";
				$menu[$x]['options'][$l]['name'] = "SPACER";
				$l++;
			}

			// Print recent edited entities, last 10

			$sql = "SELECT " . $GLOBALS['TBL_PREFIX'] . "entity.eid AS id, " . $GLOBALS['TBL_PREFIX'] . "entity.category AS cat,tp FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE lasteditby='" . mres($GLOBALS['USERID']) . "' AND deleted<>'yes' AND " . $GLOBALS['TBL_PREFIX'] . "entity.deleted='n' ORDER BY tp DESC LIMIT 10";;
			$result= mcq($sql,$db);
			while ($recent= mysql_fetch_array($result)) {
				if (CheckEntityAccess($recent['id']) == "ok" || CheckEntityAccess($recent['id']) == "readonly") {
					$menu[$x]['options'][$l]['url'] = "edit.php?e=" . $recent['id'] . "&" . $epoch;
					if (strlen($recent['cat']) > 20) {
						$cat = substr($recent['cat'],0,20) . " ...";
					} else {
						$cat = $recent['cat'];
					}
					$menu[$x]['options'][$l]['name'] = $recent['id'] . ": " . $cat;
					$l++;
				}
			}



			if (is_array($GLOBALS['PersonalTabs'])) {
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


						if ($element['visible'] == "[all]" || ($element['visible'] == "[admins]" && is_administrator()) || ($element['visible'] == 	$GLOBALS['USERID']) || ($GLOBALS['UC']['USERPROFILE']<>"" && ($GLOBALS['UC']['USERPROFILE'] == $profile))) {
							if ($element['location'] == "entity_menu") {
								$menu[$x]['options'][$l]['url'] = $element['url'];
								$menu[$x]['options'][$l]['name'] = $element['name'];
								$l++;
							}

						}
					}
				}
			} // end of personaltabs if


			$x++;
}

if (strtoupper($GLOBALS['UC']['HIDECUSTOMERTAB'])<>"YES") {
			$menu[$x]['name'] = $lang['customers'];
			$menu[$x]['url'] = "";
			$menu[$x]['options'] = array();
			$menu[$x]['options'][0]['url'] = "customers.php?add=0&" . $epoch;
			$menu[$x]['options'][0]['name'] = $lang['search'];
			$menu[$x]['options'][1]['url'] = "SPACER";
			$menu[$x]['options'][1]['name'] = "SPACER";
			$menu[$x]['options'][2]['url'] = "customers.php?add=1&" . $epoch;
			$menu[$x]['options'][2]['name'] = $lang['add'];
			$x++;
			//$ack .= "<a accesskey='" . $x . "' href='customers.php?tab=" . ($x + 20) . "&$epoch'>__</a>";
}


if (strtoupper($GLOBALS['UC']['HIDESUMMARYTAB'])<>"YES") {
			$menu[$x]['name'] = "Reporting";
			$menu[$x]['url'] = "summary.php?" . $epoch;
			$menu[$x]['options'] = array();

			$menu[$x]['options'][$h]['url'] = "summary.php?" . $epoch;
			$menu[$x]['options'][$h]['name'] = $lang['summary'];
			unset($h);
			$h++;


				$ss = GetSavedSearches();

				if (sizeof($ss)>0 && is_array($ss)) {

//					$menu[$x]['options'][0]['url'] = "summary.php?" . $epoch;
//					$menu[$x]['options'][0]['name'] = $lang['search'];
					$menu[$x]['options'][0]['url'] = "SPACER" . $epoch;
					$menu[$x]['options'][0]['name'] = "SPACER";
					$h=1;
					foreach ($ss AS $key => $element) {
						$menu[$x]['options'][$h]['url'] = htme($element);
						$menu[$x]['options'][$h]['name'] = $key;
						$h++;
					}
					$menu[$x]['options'][$h]['url'] = "SPACER" . $epoch;
					$menu[$x]['options'][$h]['name'] = "SPACER";
					$h++;
				}

			if (strtoupper($GLOBALS['EnableEntityReporting'])=="YES") {
				$menu[$x]['options'][$h]['url'] = "entityreport.php?" . $epoch;
				$menu[$x]['options'][$h]['name'] = $lang['createreports'];
				$h++;
			}

			$menu[$x]['options'][$h]['url'] = "stats.php?" . $epoch;
			$menu[$x]['options'][$h]['name'] = $lang['maninfo'];
			$h++;
			$x++;
		//		$ack .= "<a accesskey='" . $x ."' 	href='summary.php?tab=" . ($x + 20) . "&$epoch'>__</a>";
}


if (is_administrator()) {
			$menu[$x]['name'] = "Administration";
			$menu[$x]['url'] = "admin.php?info=1&" . $epoch;
			$menu[$x]['options'] = array();
			$menu[$x]['options'][$h]['url'] = "admin.php?info=1&" . $epoch;
			$menu[$x]['options'][$h]['name'] = "Main administration page";
			$h++;
			$menu[$x]['options'][$h]['url'] = "SPACER" . $epoch;
			$menu[$x]['options'][$h]['name'] = "SPACER";
			$h++;
			$menu[$x]['options'][$h]['url'] = "extrafields.php?tabletype=entity&ti=1&" . $epoch;
			$menu[$x]['options'][$h]['name'] = "Extra fields";
			$h++;
			$menu[$x]['options'][$h]['url'] = "flextable.php?TableAdmin=true&navid=ft&" . $epoch;
			$menu[$x]['options'][$h]['name'] = "Flextables";
			$h++;
			$menu[$x]['options'][$h]['url'] = "trigger.php?trig=1&" . $epoch;
			$menu[$x]['options'][$h]['name'] = "Event triggers / workflow";
			$h++;
			$menu[$x]['options'][$h]['url'] = "useradmin.php?password=&adduser=1&userman=1&cur=1&" . $epoch;
			$menu[$x]['options'][$h]['name'] = "Users & profiles";
			$h++;
			$menu[$x]['options'][$h]['url'] = "admin.php?templates=1&" . $epoch;
			$menu[$x]['options'][$h]['name'] = "Templates";
			$h++;
			$menu[$x]['options'][$h]['url'] = "SPACER" . $epoch;
			$menu[$x]['options'][$h]['name'] = "SPACER";
			$h++;
			$menu[$x]['options'][$h]['url'] = "admin.php?syscon=1&" . $epoch;
			$menu[$x]['options'][$h]['name'] = "System configuration menu";
			$h++;
			$menu[$x]['options'][$h]['url'] = "admin.php?datman=1&" . $epoch;
			$menu[$x]['options'][$h]['name'] = "Data management menu";
			$h++;
			$menu[$x]['options'][$h]['url'] = "admin.php?ieb=1&" . $epoch;
			$menu[$x]['options'][$h]['name'] = "Import/export menu";
			$h++;
			$menu[$x]['options'][$h]['url'] = "SPACER" . $epoch;
			$menu[$x]['options'][$h]['name'] = "SPACER";
			$h++;
			$menu[$x]['options'][$h]['url'] = "customtabs.php?ovw=1&" . $epoch;
			$menu[$x]['options'][$h]['name'] = "Menu items";
			$h++;
			$menu[$x]['options'][$h]['url'] = "admin.php?failoverman=1&" . $epoch;
			$menu[$x]['options'][$h]['name'] = "Fail over status";
			$h++;
			$menu[$x]['options'][$h]['url'] = "admin.php?actions=1&" . $epoch;
			$menu[$x]['options'][$h]['name'] = "Actions";
			$h++;
			$menu[$x]['options'][$h]['url'] = "publish.php?" . $epoch;
			$menu[$x]['options'][$h]['name'] = "Published pages";
			$h++;
			$menu[$x]['options'][$h]['url'] = "pchart.php?" . $epoch;
			$menu[$x]['options'][$h]['name'] = "Charts";
			$h++;

			$menu[$x]['options'][$h]['url'] = "modules.php?" . $epoch;
			$menu[$x]['options'][$h]['name'] = "Modules";
			$h++;
			$menu[$x]['options'][$h]['url'] = "admin.php?docbox=1&" . $epoch;
			$menu[$x]['options'][$h]['name'] = "Documentation";
			$h++;


			$menu[$x]['options'][$h]['url'] = "SPACER" . $epoch;
			$menu[$x]['options'][$h]['name'] = "SPACER";
			$h++;
			$menu[$x]['options'][$h]['url'] = "admin.php?sysval=1" . $epoch;
			$menu[$x]['options'][$h]['name'] = "Global system values";
			$x++;


}


if (strtoupper($GLOBALS['EnableRepositorySwitcher'])=="YES" || (strtoupper($GLOBALS['EnableRepositorySwitcher'])=="ADMIN" && is_administrator())) {
		$menu[$x]['name'] = "Repository";
		$menu[$x]['url'] = "summary.php?" . $epoch;

		$menu[$x]['options'] = array();
		$ot = 0;

		$sql = "SELECT password FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE name='" . mres($GLOBALS['USERNAME']) . "'";
		$result= mcq($sql,$db);
		$result1= mysql_fetch_array($result);
		$curpassword = $result1[password];

		$county = 0;
		$GLOBALS['ONLY_LOCAL'] = true;
		if (sizeof($pass)>0) {
				for ($r=0;$r<64;$r++) {
					if ($host[$r]) {
						if ($db = DB_Connect($r, false)) {

								$tbl = $table_prefix[$r];
								if ($tbl=="") $tbl="CRM";
								$sql = "SELECT password FROM " . $tbl . "loginusers WHERE name='" . mres($GLOBALS['USERNAME']) . "'";

								$result = mysql_query($sql);
								$result1= mysql_fetch_array($result);
								$foreignpassword = $result1[password];

								if ($curpassword==$foreignpassword && $GLOBALS['USERNAME']) {
									$sql = "SELECT id FROM " . $tbl . "loginusers WHERE name='" . mres($GLOBALS['USERNAME']) . "'";
									$result= mcq($sql,$db);
									$id= mysql_fetch_array($result);

									$sql = "SELECT value FROM " . $tbl . "settings WHERE setting='title'";
									$result = mcq($sql,$db);
									$result = mysql_fetch_array($result);

									$menu[$x]['options'][$ot]['url'] = "index.php?swrepos=1&switchreposto=a-" . $r . "&req_url=" . $req_url . "&" . $epoch;
									$menu[$x]['options'][$ot]['name'] = "<img src='images/crmlogosmall.gif' alt=''> " . $result['value'];

									$county++;
									$ot++;
								}


						}
					}
					}

			$outp .= "</table>";

			DB_Connect($GLOBALS['ORIGINAL_REPOSITORY'], false);

		} else {
			PrintAD("Configuration not loaded / found / available");
		}

		unset($GLOBALS['ONLY_LOCAL']);

			if ($county>1) {
				$html .= $outp;
			} else {
				$dontprint = true;
			}

		$evt_ins = "&nbsp;<div id='reposswitcher'><img src='images/repos.jpg' alt=''></div>";
		$evt_ins .= "<div id='reposswitchercontent'>" . $html . "</div>";

		if ($dontprint) {
			unset($evt_ins);
		}

		unset($html);
		unset($outp);
		//$evt_ins = $html;
		$x++;

}




	$menu[$x]['name'] = mres("&nbsp;#:<form id='direct' method='post' action=''><div class='showinline'><input type='text' size='3' name='e' onchange=\"document.forms['direct'].submit();\" onfocus=\"document.forms['direct'].elements['e'].value=''\"></div></form>&nbsp;<form id='direct2' action='summary.php' method='get'><img src='images/searchbox.png' alt='' class='search_img'><input class='search_input' type='search' name='sta' onchange=\"document.forms['direct2'].submit();\" onfocus=\"document.forms['direct2'].elements['sta'].value=''\"></form>");




$menu[$x]['url'] = "nolink";
$x++;

$menu[$x]['name'] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='http://www.interleave.nl' onclick=\"window.open(this.href); return false;\"><img src='images/crmlogosmall.gif' alt=''></a>";
$menu[$x]['url'] = "nolink";
$x++;

print "<table width='75%'><tr><td>";



PrintRotzooi();


$y = 0;
foreach($menu AS $element) {
	$y++;
	if ($first) {
		print ",";
	} else {
		$first = true;
	}
	if ($element['url'] == "nolink") {
		print '["' . $element['name'] . '","",0,0,0]';
	} elseif (is_array($element['options'])) {
		print '["' . $element['name'] . '&nbsp;&nbsp;&nbsp;","' . $element['url'] . '",1,0,1]';
	} else {
		print '["' . $element['name'] . '&nbsp;&nbsp;&nbsp;","' . $element['url'] . '",1,0,0]';
	}

}
print "];\n";


unset($t);
foreach($menu AS $element) {
		unset($first);
		$t++;
		if (is_array($element['options'])) {
			print "HM_Array1_"  . $t . " = [\n";
			print "[],\n";
			foreach ($element['options'] AS $link) {
				if ($first) {
					print ",";
				} else {
					$first = true;
				}
				if ($link['name'] == "SPACER") {
					print '["<img src=\"images/lijn.gif\" width=\"205\" height=\"1\" alt=\"\">","",0,0,0]' . "\n";
				} elseif ($link['url'] == "nolink") {
					print '["' . htme($link['name']) .'","' . '' . '",0,0,0]' . "\n";
				} else {
					print '["' . htme($link['name']) .'","' . $link['url'] . '",1,0,0]' . "\n";
				}
			}
			print "];\n";
		}
}
?>

			document.write("<scr" + "ipt src='js/HM_Script"+ HM_BrowserString +".js' type='text/javascript'><\/scr" + "ipt>");

		-->
		</script>
<div id="accesskeysdiv" style="display: none;">
	<a accesskey='a' onclick="location.href='admin.php?info=1&amp;SkipMainNavigation';">__</a>
	<a accesskey='s' onclick="location.href='admin.php?tab=99&sysval=1&amp;SkipMainNavigation';">__</a>
	<a accesskey='h' onclick="location.href='index.php?shortkeys=1&amp;SkipMainNavigation';">__</a>
	<a accesskey='d' onclick="location.href='admin.php?checkdb=1&amp;web=1&amp;SkipMainNavigation';">__</a>
	<a accesskey='c' onclick="location.href='calendar.php?nav=1&amp;SkipMainNavigation';">__</a>
	<a accesskey='p' onclick="location.href='admin.php?fysdelete=1&amp;SkipMainNavigation';">__</a>
	<a accesskey='l' onclick="location.href='dictedit.php?tab=99&packman=1&amp;SkipMainNavigation';">__</a>
	<a accesskey='u' onclick="location.href='useradmin.php?password=&amp;adduser=1&amp;userman=1&amp;cur=1&amp;SkipMainNavigation';">__</a>
	<a accesskey='m' onclick="newWindow = window.open('docs_examples/CRM-CTT_Interleave_Adminmanual.pdf','Manual','width=640,height=630,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');">__</a>
	<a accesskey='p' onclick="location.href='admin.php?fysdelete=1&amp;SkipMainNavigation';">__</a>
	<a accesskey='e' onclick="location.href='extrafields.php?&amp;SkipMainNavigation';">__</a>
	<a accesskey='r' onclick="location.href='admin.php?reposman=1&amp;resman=1&amp;manageres=1&amp;SkipMainNavigation';">__</a>
	<a accesskey='f' onclick="location.href='flextable.php?TableAdmin=true&amp;navid=ft&amp;SkipMainNavigation';">__</a>
	<a accesskey='t' onclick="location.href='trigger.php?&amp;SkipMainNavigation';">__</a>
	<a accesskey='n' onclick="location.href='customers.php?add=1.php';">&nbsp;newcust</a>
</div>
<?php
print "<br><table width='90%'><tr><td>&nbsp;&nbsp;&nbsp;</td><td>";

function PrintRotzooi() {
	?>
		<table width="100%" border="0"><tr><td style="height: 18px;"></td></tr></table>
		<script  type="text/javascript">
		<!--
		if(window.event + "" == "undefined") event = null;
		function HM_f_PopUp(){return false};
		function HM_f_PopDown(){return false};
		popUp = HM_f_PopUp;
		popDown = HM_f_PopDown;
		//-->
		</script>
		<script type="text/javascript">
		<!--
		function HM_f_CenterMenu(topmenuid) {
			var MinimumPixelLeft = 0;
			var TheMenu = HM_DOM ? document.getElementById(topmenuid) : window[topmenuid];
			var TheMenuWidth = HM_DOM ? parseInt(TheMenu.style.width) : HM_IE4 ? TheMenu.style.pixelWidth : TheMenu.clip.width;
			var TheWindowWidth = HM_IE ? (HM_DOM ? HM_IEcanvas.clientWidth : document.body.clientWidth) : window.innerWidth;
			return Math.max(parseInt((TheWindowWidth-TheMenuWidth) / 2),MinimumPixelLeft);
		}

		HM_PG_MenuWidth = 150;
		HM_PG_FontFamily = "<?php echo $GLOBALS['DFT_FONT'];?>";
		HM_PG_FontSize = 10;
		HM_PG_FontBold = 0;
		HM_PG_FontItalic = 0;
		HM_PG_FontColor = "<?php echo $GLOBALS['DFT_FOREGROUND_COLOR'];?>";
		HM_PG_FontColorOver = "white";
		HM_PG_BGColor = "#DDDDDD";
		HM_PG_BGColorOver = "#FFCCCC";
		HM_PG_ItemPadding = 3;

		HM_PG_BorderWidth = 2;
		HM_PG_BorderColor = "#000000";
		HM_PG_BorderStyle = "solid";
		HM_PG_SeparatorSize = 1;
		HM_PG_SeparatorColor = "#d0ff00";

		HM_PG_ImageSrc = "images/HM_More_black_right.gif";
		HM_PG_ImageSrcLeft = "images/HM_More_black_left.gif";
		HM_PG_ImageSrcOver = "images/HM_More_white_right.gif";
		HM_PG_ImageSrcLeftOver = "images/HM_More_white_left.gif";
		HM_PG_ImageSize = 5;
		HM_PG_ImageHorizSpace = 0;
		HM_PG_ImageVertSpace = 2;

		HM_PG_KeepHilite = 0;//true;
		HM_PG_ClickStart = 0;
		HM_PG_ClickKill = 1//false;
		HM_PG_ChildOverlap = 1;
		HM_PG_ChildOffset = -1;
		HM_PG_ChildPerCentOver = null;
		HM_PG_TopSecondsVisible = .5;
		HM_PG_StatusDisplayBuild = 0;
		HM_PG_StatusDisplayLink = 1;
		HM_PG_UponDisplay = null;
		HM_PG_UponHide = null;
		HM_PG_RightToLeft = 0;

		HM_PG_CreateTopOnly = 0;
		HM_PG_ShowLinkCursor = 1;
		HM_PG_NSFontOver = true;

		HM_PG_ScrollEnabled = true;
		HM_PG_ScrollBarHeight = 14;
		HM_PG_ScrollBarColor = "lightgrey";
		HM_PG_ScrollImgSrcTop = "HM_More_black_top.gif";
		HM_PG_ScrollImgSrcBot = "HM_More_black_bot.gif";
		HM_PG_ScrollImgWidth = 9;
		HM_PG_ScrollImgHeight = 5;


		HM_DOM = (document.getElementById) ? true : false;
		HM_NS4 = (document.layers) ? true : false;
		HM_IE = (document.all) ? true : false;
		HM_IE4 = HM_IE && !HM_DOM;
		HM_Mac = (navigator.appVersion.indexOf("Mac") != -1);
		HM_IE4M = HM_IE4 && HM_Mac;
		HM_Opera = (navigator.userAgent.indexOf("Opera")!=-1);
		HM_Konqueror = (navigator.userAgent.indexOf("Konqueror")!=-1);

		HM_IsMenu = !HM_Opera && !HM_Konqueror && !HM_IE4M && (HM_DOM || HM_NS4 || HM_IE4);

		HM_BrowserString = HM_NS4 ? "NS4" : HM_DOM ? "DOM" : "IE4";

		HM_Array1 = [
		[400,         // menu_width
		10,		      // left_position
		15,           // top_position
		"<?php echo $GLOBALS['DFT_FOREGROUND_COLOR'];?>",    // font_color
		"#000000",    // mouseover_font_color
		"#FFFFFF",    // background_color
		"#BFC7D9",    // mouseover_background_color
		"#BFC7D9",    // border_color
		"#FFFFFF",    // separator_color
		1,            // top_is_permanent
		1,            // top_is_horizontal
		0,            // tree_is_horizontal
		1,            // position_under
		0,            // top_more_images_visible
		1,            // tree_more_images_visible
		"null",       // evaluate_upon_tree_show
		"null",       // evaluate_upon_tree_hide
		,							// right_to_left
		,							// display_on_click
		true,					// top_is_variable_width
		true,					// tree_is_variable_width
		],

			<?php
}
?>
